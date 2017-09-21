# -*- coding: utf-8 -*-

"""
Azure Functions Queue Trigger 
- Get Webvtt from Queue, parse it into caption items, post them into Azure Search to make them searchable
"""
import os
import sys
import re
import json
if sys.version_info[0] == 3:
    text_type = str
    import http.client as httplib
    import urllib.request as urllib2
else:
    text_type = unicode
    import httplib
    import urllib2

#AZURE_SEARCH_SERVICE_NAME='gbbdemosearch'
#AZURE_SEARCH_ADMIN_KEY='04AEE09F4C50C0863000AD280FCB0523'
#AZURE_SEARCH_API_VER='2016-09-01'
#AZURE_SEARCH_INDEX_PREFIX='caption'

### App Settings ###
# AzureSearchServiceName :  gbbdemosearch
# AzureSearchAdminKey : 04AEE09F4C50C0863000AD280FCB0523
# AzureSearchApiVer: 2016-09-01
# AzureSearchIndexPrefix: caption
AZURE_SEARCH_SERVICE_NAME=os.environ['AzureSearchServiceName']
AZURE_SEARCH_ADMIN_KEY=os.environ['AzureSearchAdminKey']
AZURE_SEARCH_API_VER=os.environ['AzureSearchApiVer']
AZURE_SEARCH_INDEX_PREFIX=os.environ['AzureSearchIndexPrefix']

SUPPORT_LANGS = ['en','hi','ja', 'zh-Hans']

def errorlog(s):
    #sys.stderr.write("{}\n".format(s))
    print("ERROR: {}\n".format(s))

# Convert SS:SS:SS.SSS, SS:SS:SS, or SS:SS -> # of sec
def timefmt2sec(s):
    r = re.compile("([0-9:]*)\.([0-9]*)")
    o = r.findall(s)
    if len(o) == 1 and len(o[0]) ==2:
        s = o[0][0]
    arr=s.split(':')
    if len(arr) == 3:
        return int(arr[0])* 3600 + int(arr[1])*60 + int(arr[2])
    elif len(arr) == 2:
        return int(arr[0])*60 + int(arr[1])
    else:
        return 0

class azure_search_client:
    def __init__(self, api_url, api_key, api_version):
        self.api_url=api_url
        self.api_key=api_key
        self.api_version=api_version
        self.headers={
            'Content-Type': "application/json; charset=UTF-8",
            'Api-Key': self.api_key,
            'Accept': "application/json", 'Accept-Charset':"UTF-8"
        }

    def add_documents(self,index_name, documents, merge):
        #raise ConfigError, 'no index_name' if index_name.empty?
        #raise ConfigError, 'no documents' if documents.empty?
        action = 'mergeOrUpload' if merge else 'upload'
        for document in documents:
            document['@search.action'] = action
        
        # Create JSON string for request body
        reqobjects={}
        reqobjects['value'] = documents
        req_body = json.dumps(reqobjects)
        # HTTP request to Azure search REST API
        conn = httplib.HTTPSConnection(self.api_url)
        conn.request("POST",
                "/indexes/{0}/docs/index?api-version={1}".format(index_name, self.api_version),
                req_body, self.headers)
        response = conn.getresponse()
        print("status: {} {}".format(response.status, response.reason))
        data = response.read()
        print("data: {}".format(data))
        conn.close()


def process_content_feeding(content_id, webvtt_url, index_name):
    documents = []

    client=azure_search_client( 
        "{0}.search.windows.net".format(AZURE_SEARCH_SERVICE_NAME),
        AZURE_SEARCH_ADMIN_KEY,
        AZURE_SEARCH_API_VER)

    f = urllib2.urlopen(webvtt_url)
    data = f.read().decode('utf-8')
    document_id = content_id[12:]
    document = {
        "id" : document_id,
        "content_id": content_id,
        "content_text": data
    }
    documents.append(document)
    client.add_documents(index_name, documents, 'upload')
    f.close


def process_caption_feeding(content_id, webvtt_url, index_name):
    documents = []
    line_index =0
    doc_index=0
    c = 0 

    client=azure_search_client( 
        "{0}.search.windows.net".format(AZURE_SEARCH_SERVICE_NAME),
        AZURE_SEARCH_ADMIN_KEY,
        AZURE_SEARCH_API_VER)

    f = urllib2.urlopen(webvtt_url)
    data = f.read().decode('utf-8')
    lines = data.split("\n")
    ## NOT procees webvtt that doesn't have more than 2 lines
    if len(lines) < 2:
        return
    ## Skip 1st 2 lines 
    line_index += 2

    while line_index < len(lines):
        line = lines[line_index]
        print ("line: {}".format(line))
        ## Extract xxx and yyy from either xxx.xxx -> yyy.yyy
        r = re.compile("([0-9:.]*) --> ([0-9:.]*)")
        o = r.findall(line)
        if len(o) == 1 and len(o[0]) == 2:
            begin_str=o[0][0]
            end_str= o[0][1]
        else:
            print("WARNING: invalid time range line format: {}".format(line))
            continue
        ## text
        line_index += 1
        line = lines[line_index]
        text = line
        # Empty line
        line_index += 1
        # Document ID
        document_id = "{0}-{1}".format(content_id[12:],str(doc_index))
        document = {
            "id" : document_id,
            "content_id": content_id,
            "begin_sec": timefmt2sec(begin_str),
            "begin_str": begin_str,
            "end_str": end_str,
            "caption_text": text
            }
        print ("docment: content_id:{} begin_sec:{} begin_str:{} end_str:{} caption_text:{}"
                .format(content_id,timefmt2sec(begin_str),begin_str,end_str,text.encode('utf-8')))
        documents.append(document)
        c +=1
        doc_index +=1
        if (c > 999):
            client.add_documents(index_name, documents, 'upload')
            c =0
            documents = []
        line_index += 1

    f.close
    if (len(documents) > 0):
        client.add_documents(index_name, documents, 'upload')

def functions_main():
    ### This is a function's starting point
    
    # GET WEBVTT FROM COSMOSDB using Azure Function Queue & CosmosDB
    # bindling mechanism. Mechanism behind the binding is that 
    # when an assetid data comes in <queue>, queue trigger does:
    #
    #   (1) Pop the assetid from <queue>
    #   (2) Get contents data stored in CosmosDB by issuing a query: SELECT * from c where c.id = <assetid>
    #   (3) The content data obtained above is stored as ENV value with key name 'inputDocument'
    #
    cosmosdb_data = open(os.environ['inputDocument']).read()
    metas=json.loads(cosmosdb_data)
    if len(metas) < 1:
        errorlog("No meta obtained via Azure Function Queue & CosmosDB binding")
        sys.exit(0)
    meta = metas[0]
    if not 'webvtt_url' in meta:
        errorlog("Content doesn't containe webvtt_url: assetid={}".format(meta['id']))
        sys.exit(0)
    webvtt_url = meta['webvtt_url']
    print("url->{}".format(meta['webvtt_url']))
    subtitle_urls = meta['subtitle_urls']
    lang = "en"
    index_name = "content"
    # FEEDING FOR CONTENT INDEX
    process_content_feeding(meta['id'], webvtt_url, index_name)

    index_name = "{}-{}".format(AZURE_SEARCH_INDEX_PREFIX, lang)
    # FEEDING FOR CAPTION(english) INDEX
    process_caption_feeding(meta['id'], webvtt_url, index_name)
     
    # FEEDING FOR CAPTION(other supportable langs) INDEX
    # Loop subtitle_urls
    for subtitle_url in subtitle_urls:
        if not subtitle_url["lang"] in SUPPORT_LANGS:
            continue
        sub_lang = subtitle_url["lang"].lower()
        sub_webvtt_url = subtitle_url["webvtt_url"]
        if sub_lang != lang:
            sub_index_name = "{}-{}".format(AZURE_SEARCH_INDEX_PREFIX, sub_lang)
            process_caption_feeding(meta['id'], sub_webvtt_url, sub_index_name)


functions_main()

#if __name__ == "__main__":
#    #webvtt_url = "http://gbbdemoams.streaming.mediaservices.windows.net/11b7ed07-fe61-4bd0-8e1c-e4f7d3e1763d/Build2017_CognitiveServicesDemo_NoPlaceToHyde_aud_SpReco.vtt"
#    #content_id = "nb:cid:UUID:66a56dd2-d65c-4f4e-9dbc-2208c9e00846"
#    #index_name = "caption-en"
#    webvtt_url = "http://gbbdemoams.streaming.mediaservices.windows.net/b7e28e15-d1d6-458f-9388-b0d03dea2afd/subtitle-hi.vtt"
#    content_id = "nb:cid:UUID:66a56dd2-d65c-4f4e-9dbc-2208c9e00846"
#    index_name = "caption-hi"
#    process_caption_feeding(content_id, webvtt_url, index_name)

