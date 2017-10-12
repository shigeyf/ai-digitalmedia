
function substr_yymmdd(s) {
    return (s.length>8) ? s.substring(0, 8) : s;
}

function execSearch(content_id, lang) {
	var keyword = $("#q").val();
	var lang = $("#lang").val();
    execCustomParamsSearch(content_id, lang, keyword);
}

//function execSearch(content_id, lang)
function execCustomParamsSearch(content_id, lang, keyword)
{
    // assigned given lang value to #lang
    $("#lang").val(lang);
    $("#q").val(keyword);

	var searchAPI = "/api/azuresearch-caption-api.php?content_id=" + content_id + "&lang=" + lang;
    if (keyword != '' ) {
	    searchAPI = searchAPI + "&search=" + encodeURIComponent(keyword);
    }

    $.ajax({
        url: searchAPI,
        type: "GET",
        success: function (data) {
			$( "#colcontainer2" ).html('');
			$( "#colcontainer2" ).append('<table>');
            var caption_counter = 0;
			for (var item in data.value)
			{
				var caption_id = data.value[item].id;
                var caption_text = data.value[item].caption_text;
                if ( '@search.highlights' in data.value[item] ){
				    caption_text = data.value[item]['@search.highlights'].caption_text;
                }
				var begin_sec = data.value[item].begin_sec;
				var begin_str = data.value[item].begin_str;
                begin_str = substr_yymmdd(begin_str);
				var end_str = data.value[item].end_str;
                end_str = substr_yymmdd(end_str);
                var bgcolor_class = ( caption_counter % 2 ==0 ) ? "bgcolor-odd" : "bgcolor-even";
		    	$( "#colcontainer2" ).append(
                        "<tr id=\"" + caption_id + "\">\n");
                $( "#colcontainer2" ).append(
                        "<td class=\"timecol " + bgcolor_class + "\">[<a href=\"#\" id=\"dummy\" onclick=\"restart(" + begin_sec + ");\">"
                         + begin_str + " - " +  end_str + "</a>]</td>\n");
                $( "#colcontainer2" ).append(
                        "<td class=\"clickme " + bgcolor_class + "\" id=\"" + caption_id + "\">" + caption_text + "</td>\n");
                $( "#colcontainer2" ).append("</tr>\n");
                caption_counter++;
			}
            $( "#colcontainer2" ).append('</table>');
        }
    });

}
