<?php
require dirname(__FILE__) . '/azure-documentdb-php-sdk/vendor/autoload.php';
$project_coonfig_file = dirname(__FILE__)."/../project.json";
$json_s = file_get_contents($project_coonfig_file);
$proejct_config = json_decode($json_s, true);

$azsearch_service_name=$proejct_config['azsearch_service_name'];
$azsearch_api_key=$proejct_config['azsearch_api_key'];
$azsearch_api_version = $proejct_config['azsearch_api_version'];

$docdb_host=$proejct_config['docdb_host'];
$docdb_master_key=$proejct_config['docdb_master_key'];
$docdb_db_caption = $proejct_config['docdb_db_caption'];
$docdb_coll_caption = $proejct_config['docdb_coll_caption'];
$docdb_db_content = $proejct_config['docdb_db_content'];
$docdb_coll_content = $proejct_config['docdb_coll_content'];

$req=$_REQUEST;
$params = array();
if( is_array($req) ) {
    foreach( $req as $name => $value) {
        $params[$name] = $value;
    }
}

$azsearch_index_name = "content";
$AZURESEARCH_URL_BASE= sprintf( "https://%s.search.windows.net/indexes/%s/docs",
                    $azsearch_service_name, $azsearch_index_name );

$url = $AZURESEARCH_URL_BASE . '?'
            . '$top=1000&$select=id,content_id'
            . '&$count=true&api-version=' . $azsearch_api_version;
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

$search_res=json_decode($data,true); // return as assoc array
//var_dump($search_res);
$contents_arr = $search_res["value"];
//var_dump($contents_arr);
$content_ids = array();
foreach ($contents_arr as $c) {
    if (array_key_exists('content_id', $c) ) {
        array_push($content_ids, $c['content_id']);
    } 
}

$ret_value_array =array();
if (count($content_ids) > 0) {
    $client = new \DreamFactory\DocumentDb\Client($docdb_host, $docdb_master_key);
    $contentdb = new \DreamFactory\DocumentDb\Resources\Document($client, $docdb_db_content, $docdb_coll_content);
//$res = $contentdb->query('SELECT * FROM c WHERE c.id = @id', [['name' => '@id', 'value' => $content_id],]);
    $querystr = sprintf( "SELECT * FROM c WHERE c.id IN ('%s')", implode("','",$content_ids) );
    //echo $querystr;
    $cosmos_res = $contentdb->query($querystr);
    //var_dump($cosmos_res);
    $docs_arr = $cosmos_res["Documents"];
    foreach ($docs_arr as $d) {
        if( array_key_exists('thumbnail_url', $d) && array_key_exists('name', $d) ) {
            array_push(
                $ret_value_array, 
                array(
                    'content_id' => $d['id'],
                    'name' => $d['name'],
                    'thumbnail_url' => $d['thumbnail_url']
                )
            ); 
        } 
    }
}
// Making return value as the same JSON format as Azure Search API response format
$ret_array =array(
    '@odata.count' => count($ret_value_array),
    'value' => $ret_value_array
);
$ret_data = json_encode($ret_array);
//var_dump($ret_array);

header('Content-Length: '.strlen($ret_data));
header('Content-Type: application/json; odata.metadata=minimal');
header('Access-Control-Allow-Origin: *');
print $ret_data;

?>
