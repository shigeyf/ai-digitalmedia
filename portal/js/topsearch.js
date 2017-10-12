
function execTopSearch()
{
	var keyword = $("#q").val();
	var searchAPI = "/api/azuresearch-content-api.php";
    if (keyword != '' ) {
	    searchAPI = searchAPI + "?search=" + encodeURIComponent(keyword);
    }

    $.ajax({
        url: searchAPI,
        type: "GET",
        success: function (data) {
            $( "#mediaContainer" ).html('');
            var caption_counter = 0;
			for (var item in data.value)
			{
				var content_id = data.value[item].content_id;
				var name = data.value[item].name;
                var thumbnail_url = data.value[item].thumbnail_url;
                $( "#mediaContainer" ).append( '<div class="col-md-4" style="text-align:center"><a href="video/' + content_id + '"><img src=' + thumbnail_url + ' height=200><br><div style="height:100px"><b>' + name + '</b></a></div></div>' );
			}
        }
    });
}
