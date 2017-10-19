<?php

require_once dirname(__FILE__) . '/api/azure-documentdb-php-sdk/vendor/autoload.php';
require_once dirname(__FILE__) . '/config.inc';


init_services();

function init_services() {
    $c = GET_CONFIG();

    $docdb_host=$c['docdb_host'];
    $docdb_master_key=$c['docdb_master_key'];
    $docdb_db_content = $c['docdb_db_content'];
    $docdb_coll_content = $c['docdb_coll_content'];

    $azsearch_service_name=$c['azsearch_service_name'];
    $azsearch_api_key=$c['azsearch_api_key'];
    $azsearch_index_name_prefix = $c['azsearch_index_name'];
    $azsearch_api_version = $c['azsearch_api_version'];

    if (!init_cosmosdb($docdb_host, $docdb_master_key, $docdb_db_content, $docdb_coll_content)) {
        print 'Error init_cosmosdb!\n';
        return false;
    }
    if (!init_azuresearch($azsearch_service_name, $azsearch_api_key,
                 $azsearch_api_version, $azsearch_index_name_prefix)) {
        print 'Error init_azuresearch!\n';
        return false;
    }
    return true;
}

function init_cosmosdb($host, $master_key, $db_name, $coll_name) {
    $client = new \DreamFactory\DocumentDb\Client($host, $master_key);
    $db = new \DreamFactory\DocumentDb\Resources\Database($client);
    $db_get_ret=$db->get($db_name);
    if (!array_key_exists('id', $db_get_ret) ) {
        if ( array_key_exists('code', $db_get_ret) && $db_get_ret['code'] == 'NotFound' ) {
            $db_create_ret = $db->create(['id'=>$db_name]);
            if ( !array_key_exists('id', $db_create_ret) || $db_create_ret['id'] != $db_name ) {
                return false;
            }
        }
    }
    $coll = new \DreamFactory\DocumentDb\Resources\Collection($client, $db_name);
    $coll_get_ret=$coll->get($coll_name);
    if (!array_key_exists('id', $coll_get_ret) ) {
        if ( array_key_exists('code', $coll_get_ret) && $coll_get_ret['code'] == 'NotFound' ) {
            $coll_create_ret = $coll->create(['id'=>$coll_name]);
            if ( !array_key_exists('id', $coll_create_ret) || $coll_create_ret['id'] != $coll_name ) {
                return false;
            }
        }
    }
    return true;
}

function init_azuresearch($service_name, $api_key, $api_version, $index_name_prefix ) {
    // Get index list
    $index_names  = get_index_list($service_name, $api_key, $api_version);
    if (!is_array($index_names)) {
        print 'Error init_azuresearch:: Get Index List!\n';
        return false;
    }
    // Create an index for Content only if it doesn't exist
    $INDEX_NAME_CONTENT='content';
    if (!in_array('content', $index_names)) {
        $post_body ="{
\"name\": \"content\",
    \"fields\": [
        {\"name\":\"id\", \"type\":\"Edm.String\", \"key\":true, \"retrievable\":true, \"searchable\":false, \"filterable\":false, \"sortable\":false, \"facetable\":false},
        {\"name\":\"content_id\", \"type\":\"Edm.String\", \"retrievable\":true, \"searchable\":false, \"filterable\":true, \"sortable\":false, \"facetable\":false},
        {\"name\":\"content_text\", \"type\":\"Edm.String\", \"retrievable\":true, \"searchable\":true, \"filterable\":false, \"sortable\":false, \"facetable\":false, \"analyzer\":\"en.microsoft\"}
    ],
    \"corsOptions\": {
        \"allowedOrigins\": [\"*\"],
        \"maxAgeInSeconds\": 300
    }
}";
        if (!create_index_schema($service_name, $api_key, $api_version, $post_body))  {
            print 'Error init_azuresearch:: Create Index Schema: content!\n';
            return false;
        
        }
    }
    // Create indexes for Captions
    $CAPTION_LANGS =array('en', 'hi', 'ja', 'zh-Hans');
    foreach ($CAPTION_LANGS as $lang) {
        $lower_lang = strtolower($lang);
        $index_name = sprintf("%s-%s", $index_name_prefix, $lower_lang);
        // Create index only if it doesn't exists
        if (!in_array($index_name, $index_names)) {
            $post_body ="{
    \"name\": \"$index_name\",
    \"fields\": [
        {\"name\":\"id\", \"type\":\"Edm.String\", \"key\":true, \"retrievable\":true, \"searchable\":false, \"filterable\":false, \"sortable\":false, \"facetable\":false},
        {\"name\":\"content_id\", \"type\":\"Edm.String\", \"retrievable\":true, \"searchable\":false, \"filterable\":true, \"sortable\":false, \"facetable\":false},
        {\"name\":\"begin_sec\", \"type\":\"Edm.Int32\", \"retrievable\":true, \"searchable\":false, \"filterable\":false, \"sortable\":true, \"facetable\":false},
        {\"name\":\"begin_str\", \"type\":\"Edm.String\", \"retrievable\":true, \"searchable\":false, \"filterable\":false, \"sortable\":false, \"facetable\":false},
        {\"name\":\"end_str\", \"type\":\"Edm.String\", \"retrievable\":true, \"searchable\":false, \"filterable\":false, \"sortable\":false, \"facetable\":false},
        {\"name\":\"caption_text\", \"type\":\"Edm.String\", \"retrievable\":true, \"searchable\":true, \"filterable\":false, \"sortable\":false, \"facetable\":false, \"analyzer\":\"$lower_lang.lucene\"}
       ],
         \"corsOptions\": {
            \"allowedOrigins\": [\"*\"],
            \"maxAgeInSeconds\": 300
        }
}";

            if (!create_index_schema($service_name, $api_key, $api_version, $post_body))  {
                print "Error init_azuresearch:: Create Index Schema: $index_name!\n";
                return false;
            }
        }
    }
    return true;
}

function get_index_list ($service_name, $api_key, $api_version) {
    $AZURESEARCH_URL_BASE= sprintf( "https://%s.search.windows.net/indexes", $service_name);
    $url = $AZURESEARCH_URL_BASE . '?api-version=' . $api_version;
    $opts = array(
        'http'=>array(
            'method'=>"GET",
            'header'=>"Accept: application/json\r\n" .
                "api-key: $api_key\r\n",
            'timeout' =>10
        )
    );
    $context = stream_context_create($opts);
    $data = file_get_contents($url, false, $context);
    if ($data  === false) {
        return null;
    } 
    $arr=json_decode($data,true); // return as assoc array
    $ret_arr = array();
    if (!array_key_exists('value', $arr)) {
        return null;
    }
    foreach($arr['value'] as $i) {
        array_push($ret_arr, $i['name']);
    } 
    return $ret_arr;
}

function create_index_schema ($service_name, $api_key, $api_version, $post_body ) {

    $AZURESEARCH_URL_BASE= sprintf( "https://%s.search.windows.net/indexes", $service_name);
    $url = $AZURESEARCH_URL_BASE . '?api-version=' . $api_version;
    $header = array(
        "Content-Type: application/json; charset=UTF-8",
        "Api-Key: ". $api_key,
        "Accept': application/json",
        "Accept-Charset: UTF-8"
    );
    $opts = array(
        "http" => array(
            "method"  => "POST",
            "header"  => implode("\r\n", $header),
            "content" => $post_body
        )
    );

    $context = stream_context_create($opts);
    $data = file_get_contents($url, false, $context);
    var_dump($data);
    if ($data  === false) {
        return null;
    } 
    return true;
}


?>

