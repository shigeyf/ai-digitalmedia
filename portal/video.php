<?php
require dirname(__FILE__) . '/api/azure-documentdb-php-sdk/vendor/autoload.php';
require dirname(__FILE__) . '/config.php';

$proejct_config = GET_CONFIG();
$docdb_host=$proejct_config['docdb_host'];
$docdb_master_key=$proejct_config['docdb_master_key'];
$docdb_db_content = $proejct_config['docdb_db_content'];
$docdb_coll_content = $proejct_config['docdb_coll_content'];

$lang_name_map = array(
        "en" => "English", 
        "zh-Hans" => "Chinese", 
        "hi" => "Hindi", 
        "ja" => "Japanese"
     );

$req=$_REQUEST;
$params = array();
if( is_array($req) ) {
    foreach( $req as $name => $value) {
        $params[$name] = $value;
    }
}
if ( !array_key_exists('cid', $params) ) {
    print "Error!";
    exit;
}

$content_id = $params['cid'];
//$_content_id = "nb:cid:UUID:66a56dd2-d65c-4f4e-9dbc-2208c9e00846";

$client = new \DreamFactory\DocumentDb\Client($docdb_host, $docdb_master_key);

// Getting content information from content id
$contentdb = new \DreamFactory\DocumentDb\Resources\Document($client, $docdb_db_content, $docdb_coll_content);
$res = $contentdb->query('SELECT * FROM c WHERE c.id = @id', [['name' => '@id', 'value' => $content_id]]);
#var_dump($res);

$http_code = get_http_code($res);
if ($http_code != 200 ){
    print "ERROR: Loading Content!";
    exit;
}
$contents_arr = $res['Documents'];
$c = $contents_arr[0];
$content_name = get_value('name', $c, $c['asset_name']);
$content_lang = get_value('lang',$c, "en");
$content_url =  get_value('manifest_url',$c, "");
$caption_webvtt_url =  get_value('webvtt_url',$c, "");
$subtitle_urls =  get_value('subtitle_urls',$c, array());
$content_note = get_value('note',$c, "");

//var_dump($subtitle_urls);
//echo "num=" . count($subtitle_urls);
function get_value($key, $arr, $default_value) {
    if (array_key_exists($key, $arr)) {
        return $arr[$key];
    } 
    return $default_value;
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
    <link href="/css/design.css" rel="stylesheet">
    <script type="text/javascript">

    function restart(value) {
        myPlayer.currentTime(value);
    }
    </script>

    <!-- Azure Media Player -->
    <link href="//amp.azure.net/libs/amp/latest/skins/amp-default/azuremediaplayer.min.css" rel="stylesheet">
    <script src="//amp.azure.net/libs/amp/latest/azuremediaplayer.min.js"></script>

  </head>

  <body onLoad='execSearch("<?php echo $content_id; ?>")'>    
    <nav class="navbar navbar-inverse navbar-fixed-top">
      <div class="container">
        <ul class="nav navbar-nav">
            <li><a href="/">TOP</a></li>
            <li class="active"><a href="">VIDEO</a></li>
        </ul>
      </div>
    </nav>

    <br><br><br>

    <div class="container">
        <div class="row">
        </div>
    </div><!-- /.container -->

    <div class="colmask doublepage">
        <div class="colleft">
            <div class="col1">
                <!-- Column 1 start -->
                <center><h3>Media Content</h3></center>
                <div>
                <!-- Adaptive Bitrate Streaming by Azure Media Services  -->
                    <video id="azuremediaplayer1" class="azuremediaplayer amp-default-skin amp-big-play-centered" tabindex="0"> </video>
                    <script>
                    var myOptions = {
                         autoplay: true,
                         controls: true,
                         width: "600",
                         height: "375",
                         poster: ""
                    };
                    var myPlayer = amp("azuremediaplayer1", myOptions);
                    myPlayer.src(
                        [
                            { src: "<?php echo $content_url; ?>", type: "application/vnd.ms-sstr+xml" },
                        ],
                        [
                            { src: "<?php echo $caption_webvtt_url;?>", srclang: "en", kind: "subtitles", label: "english" },
                        <?php
                            foreach ($subtitle_urls as $s) {
                                if (array_key_exists($s['lang'], $lang_name_map)) {
                                    echo sprintf("{ src: \"%s\", srclang: \"%s\", kind: \"subtitles\", label: \"%s\" },\n",$s['webvtt_url'],$s['lang'], $lang_name_map[$s['lang']]); 
                                }
                            }
                        ?> 
                        ]
                    );

                    var setTrack1 = function (language, kind) {
                        for (var i = 0; i < myPlayer.textTracks_.length ; i++) {
                            if (myPlayer.textTracks_.tracks_[i].mode != "disabled") {
                                myPlayer.textTracks_.tracks_[i].mode = "disabled";
                            }
                        }
                        for (var i = 0; i < myPlayer.textTracks_.length ; i++) {
                            if (myPlayer.textTracks_.tracks_[i].language == language && myPlayer.textTracks_.tracks_[i].kind == kind) {
                                myPlayer.textTracks_.tracks_[i].mode = "showing";
                                break;
                            }
                        }
                    }

                    myPlayer.addEventListener(amp.eventName.play, restart);
                    </script>

                    <h4>Content Information:</h4>
                    <ul>
                    <li><b>Title</b>: <?php echo $content_name; ?></li>
                    <li><b>Note</b>: <?php echo $content_note; ?></li>
                    </ul>

                </div> <!-- //Video -->
            </div><!-- //column1  -->
            <div class="col2"> <!-- column2 start -->
                <center><h3>Captions Timeline</h3></center>
                <div class="form-group">       
                    <div>
                        <button class="btn btn-info" type="button" id="button-en" onclick='execCustomParamsSearch("<?php echo $content_id; ?>", "en", "");'>English</button>
                        <?php
                            foreach ($subtitle_urls as $s) {
                                if (array_key_exists($s['lang'], $lang_name_map)) {
                                    echo sprintf("<button class=\"btn btn-info\" type=\"button\" id=\"button-%s\" onclick='execCustomParamsSearch(\"%s\", \"%s\", \"\");'>%s</button>", $s['lang'], $content_id, $s['lang'], $lang_name_map[$s['lang']]);
                                }
                            }
                        ?>
                    </div>
                    <div class="input-group" style="padding:5px 30px 10px 0;">
                        <input type="text" class="form-control" placeholder="Search for...in closed captions" id="q"  onkeydown = 'if (event.keyCode == 13) execSearch("<?php echo $content_id; ?>");' >
                        <input type="hidden" name="lang" id="lang" value="en"> 
                         <span class="input-group-btn">
                        <button class="btn btn-default" type="button" onclick='execSearch("<?php echo $content_id; ?>");'>Go!</button>
                        </span>
                    </div><!-- /input-group -->
                </div>

            	<div class="scrollbox">
                    <div id="colcontainer2">
                    </div> <!-- //colcontainer2 -->
            	</div> <!-- //scrollbox -->
            </div> <!-- //column2 end -->
            <br>
            <br>
            <br>
            <br>
        </div> <!-- //colleft -->
    </div> <!--//colmask -->

    <!-- Bootstrap core JavaScript
    ================================================== -->
    <!-- Placed at the end of the document so the pages load faster -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
    <script src="/js/captionsearch.js"></script>
	<!--<script src="api.js"></script>-->
  </body>
</html>
