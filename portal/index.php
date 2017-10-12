<?php

require dirname(__FILE__) . '/api/azure-documentdb-php-sdk/vendor/autoload.php';
require dirname(__FILE__) . '/config.inc';

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
if ($http_code != 200 ){
    print "ERROR: Loading Content!";
    exit;
}
$contents_arr = $res['Documents'];

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

    <title>GBB Demo</title>

    <!-- Bootstrap core CSS -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
    <!-- Custom styles for this template -->
    <link href="/css/design-index.css" rel="stylesheet">
 
  </head>
  <body>

    <nav class="navbar navbar-inverse navbar-fixed-top">
      <div class="container">
        <ul class="nav navbar-nav">
            <li class="active"><a href="">GBB Demo CMS</a></li>
        </ul>
      </div>
    </nav>

<div class="container"> <!-- column2 start -->
<div class="row">
    <div class="col-md-1" style="text-align:center"><img src='img/linux_penguin_logo.png' height=50><br><div style="width:30px"></div></div>
    <div class="col-md-1" style="text-align:center"><img src='img/docker_logo.png' height=50><br><div style="width:30px"></div></div>
    <div class="col-md-1" style="text-align:center"><img src='img/azure_logo.png' height=50><br><div style="width:30px" ></div></div>
</div>        
<div class="row">
    <div class="input-group" style="padding:20px;">
        <input type="text" class="form-control" placeholder="Search for..." id="q"  onkeydown = "if (event.keyCode == 13) execTopSearch();" >
        <span class="input-group-btn">
        <button class="btn btn-default" type="button" onclick="execTopSearch();">Go!</button>
        </span>
    </div><!-- /input-group -->
</div>        

<div class="row">
<div id="mediaContainer">
<?php
    $content_index = 1; 
    foreach ($contents_arr as $content) {
        $content_id = $content["id"];
        if (!array_has_value_of("name", $content) && !array_has_value_of("asset_name", $content)) {
            continue;
        }
        $name = (array_has_value_of("name", $content)) ? $content["name"] : $content["asset_name"];
        $thumbnail_url = $content["thumbnail_url"];
        $s = sprintf("<div class=\"col-md-4\" style=\"text-align:center\"><a href=\"video/%s\"><img src=\"%s\" height=200><br><div style=\"height:100px\"><b>%s</b></a></div></div>",
                    $content_id,
                    $thumbnail_url,
                    $name);
        echo $s;
        $content_index++;
    }
?>
</div><!-- //mediaContainer -->
</div><!-- //row -->

</div><!-- /.container -->

    <!-- Bootstrap core JavaScript
    ================================================== -->
    <!-- Placed at the end of the document so the pages load faster -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
    <script src="/js/topsearch.js"></script>
  </body>
</html>
