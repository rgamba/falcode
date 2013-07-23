Mod.ExampleAjax = {};

/**
 * This function will send the ajax request to the server
 */
Mod.ExampleAjax.sendRequest = function(){
    // Get the plain response from the server
    $.get(Sys.path.http + 'examples.ajax_request',function(data){
        $("#plain_response").html(data);
    },"html");

    // Parse the response as a JSON object and create a table with the data
    $.get(Sys.path.http + 'examples.ajax_request',function(data){
        html = '<table class="ajax">';
        for(var i in data){
            html += '<tr>' +
                '<td>'+ data[i].name +'</td>' +
                '<td>'+ data[i].phone +'</td>' +
                '</tr>';
        }
        html += '</table>';
        $("#parsed_response").html(html);
    },"json");
}

/**
 * On doc ready call the sendRequest() function
 */
$(function(){
    Mod.ExampleAjax.sendRequest();
})