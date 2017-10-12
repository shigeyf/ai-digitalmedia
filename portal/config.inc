<?php

function GET_CONFIG() {
    $c = array();
    $c['docdb_host']= getenv('CosmosdbServiceHost');
    $c['docdb_master_key'] =getenv('CosmosdbMasterKey');
    $c['docdb_db_content'] = 'asset';
    $c['docdb_coll_content'] = 'meta';
    $c['azsearch_service_name'] = getenv('AzureSearchServiceName');
    $c['azsearch_api_key'] = getenv('AzureSearchApiKey');
    $c['azsearch_index_name'] = 'caption';
    $c['azsearch_api_version'] = '2016-09-01';
    return $c;
}

?>

