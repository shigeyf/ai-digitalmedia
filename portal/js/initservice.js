
function initService()
{
    var initAPI ="/api/service-init-api.php";
    var request_ok = true;
    $.ajax({
        url: initAPI,
        beforeSend: function (request) {
            request.setRequestHeader("Accept", "application/json");
        },
        type: "POST",
        success: function (data) {
            var code = data.code;
            if (code != 'OK') {
                $( "#infomsg" ).html('');
                $( "#infomsg" ).append("<div class=\"alert alert-danger alert-dismissable\"><a href=\"#\" class=\"close\" data-dismiss=\"alert\" aria-label=\"close\">×</a><strong>Error!</strong>: Service initialization failure! Please try again later</div>");
                request_ok = false;
            } else {
                $( "#infomsg" ).html('');
                $( "#infomsg" ).append("<div class=\"alert alert-success alert-dismissable\" id=\"myAlert\"><a href=\"#\" class=\"close\" data-dismiss=\"alert\" aria-label=\"close\">×</a><strong>Success!</strong>: Now you're ready for processing video contents. <strong><a href=\"/\">Continue</a></strong></div>");
                $( "#buttondiv" ).html('');
            }
        },
        error: function(xhr, textStatus, errorThrown) {
           request_ok = false;
           $( "#infomsg" ).html('');
           $( "#infomsg" ).append("<div class=\"alert alert-danger alert-dismissable\"><a href=\"#\" class=\"close\" data-dismiss=\"alert\" aria-label=\"close\">×</a><strong>Error!</strong>: Service initialization failure! Please try again later</div>");
        }
    });
    return request_ok;
}
