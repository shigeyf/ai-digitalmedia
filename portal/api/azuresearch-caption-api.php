<?php
require dirname(__FILE__) . '/../config.php';

$proejct_config = GET_CONFIG();
$azsearch_service_name=$proejct_config['azsearch_service_name'];
$azsearch_api_key=$proejct_config['azsearch_api_key'];
$azsearch_index_name = $proejct_config['azsearch_index_name'];
$azsearch_api_version = $proejct_config['azsearch_api_version'];

$req=$_REQUEST;
$params = array();
if( is_array($req) ) {
    foreach( $req as $name => $value) {
        $params[$name] = $value;
    }
}
if (!array_key_exists('content_id', $params)) {
    print "Error!";
    exit(1);
}

$lang='en';
if (array_key_exists('lang', $params)) {
    $lang=$params['lang'];
} 

$AZURESEARCH_URL_BASE= sprintf( "https://%s.search.windows.net/indexes/%s-%s/docs",
                    $azsearch_service_name, $azsearch_index_name, strtolower($lang));

$url = $AZURESEARCH_URL_BASE . '?'
            . '$top=1000&$select=id,content_id,begin_sec,begin_str,end_str,caption_text'
            . '&$count=true&$orderby=begin_sec&highlight=caption_text&api-version=' . $azsearch_api_version 
            . '&$filter=content_id%20eq%20%27' . $params['content_id'] . '%27';
if (array_key_exists('search', $params)) {
    $url = $url . '&search=' . urlencode($params['search']);
}

$opts = array(
   'http'=>array(
       'method'=>"GET",
       'header'=>"Accept: application/json\r\n" .
           "api-key: $azsearch_api_key\r\n",
       'timeout' =>10
   )
);

$context = stream_context_create($opts);
$data = file_get_contents($url, false, $context);

if ($data  === false) {
    print "Error!";
    exit(1);
}
else 
{
    header('Content-Length: '.strlen($data));
    header('Content-Type: application/json; odata.metadata=minimal');
    header('Access-Control-Allow-Origin: *');
    print $data;
}
?>
