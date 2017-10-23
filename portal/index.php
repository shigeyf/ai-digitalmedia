<?php

require dirname(__FILE__) . '/api/azure-documentdb-php-sdk/vendor/autoload.php';
require dirname(__FILE__) . '/config.php';

$proejct_config = GET_CONFIG();
$docdb_host=$proejct_config['docdb_host'];
$docdb_master_key=$proejct_config['docdb_master_key'];
$docdb_db_content = $proejct_config['docdb_db_content'];
$docdb_coll_content = $proejct_config['docdb_coll_content'];

$client = new \DreamFactory\DocumentDb\Client($docdb_host, $docdb_master_key);
// Getting content information from content id
$contentdb = new \DreamFactory\DocumentDb\Resources\Document($client, $docdb_db_content, $docdb_coll_content);
$contentdb->setHeaders(['x-ms-max-item-count: 2000']);
$res = $contentdb->getlist();
$http_code = get_http_code($res);
$contents_arr = array();
$service_init_need = false;

switch ($http_code) {
    case 200:
        $contents_arr = $res['Documents'];
        break;
    case 404:
        $service_init_need = true;
        break;
    default:
        print "ERROR: Loading Content!";
        exit;
}

function get_http_code($res) {
    if( is_array($res) and array_key_exists('_curl_info', $res)) {
        $curl_info=$res['_curl_info'];
        if ( array_key_exists('http_code', $curl_info) ) {
            return $curl_info['http_code'];
        }
    }
    return 500;
}

function array_has_value_of( $key, $arr) {
    if( array_key_exists($key, $arr) ){
        $v = $arr[$key];
        if( isset($v) && !empty($v) ) {
            return true;
        }
    }
    return false;
}
 
?>

<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">

    <title>Azure Media & AI Demo</title>

    <!-- Bootstrap core CSS -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
    <!-- Custom styles for this template -->
    <link href="/css/design-index.css" rel="stylesheet">
 
  </head>
  <body>

    <nav class="navbar navbar-inverse navbar-fixed-top">
      <div class="container">
        <ul class="nav navbar-nav">
            <li class="active"><a href="">Azure Media & AI Demo</a></li>
        </ul>
      </div>
    </nav>

<div class="container"> <!-- column2 start -->
<div class="row">
    <div class="col-md-1" style="text-align:center"><img src='img/linux_penguin_logo.png' height=50><br><div style="width:30px"></div></div>
    <div class="col-md-1" style="text-align:center"><img src='img/docker_logo.png' height=50><br><div style="width:30px"></div></div>
    <div class="col-md-1" style="text-align:center"><img src='img/azure_logo.png' height=50><br><div style="width:30px" ></div></div>
</div>        
<?php 
if ($service_init_need) {
    include_once(dirname(__FILE__) . '/tmpl/init_button.php');
}
else {
    include_once(dirname(__FILE__) . '/tmpl/content_search_form.php');
    include_once(dirname(__FILE__) . '/tmpl/content_list.php');
}
?>    
</div><!-- /.container -->

    <!-- Bootstrap core JavaScript
    ================================================== -->
    <!-- Placed at the end of the document so the pages load faster -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
    <script src="/js/topsearch.js"></script>
  </body>
</html>
