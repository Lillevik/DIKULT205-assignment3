/**
 * This file handles...
 */


function get_url_parameters() {
    result = {};

    var uri = document.location.href;

    if (uri.indexOf("?") < 0)
        return result;
    query_string = uri.substring(uri.indexOf("?"));
    if (query_string.indexOf("#") >= 0)
        query_string = query_string.substring(0, query_string.indexOf("#"));


    query_pattern = /(?:[?&])([^&=]+)(?:=)([^&]*)/g;
    decode = function(str) {return decodeURIComponent(str.replace(/\+/g, " "));};
    while(match = query_pattern.exec(query_string))
        result[decode(match[1])] = decode(match[2]);
    return result;
}


window.onload = function(){
    const params = get_url_parameters();
    var working = false;
    const form = document.getElementById('comment-form');
    const commentList = document.getElementById('comments-list');
    form.addEventListener('submit', function(e){
        e.preventDefault();

        var commentText = document.getElementById('comment-field').value;
        if(!working){
            working = true;
            $.ajax({
                type: "POST",
                data: {commentField:commentText, key:params.key},
                url: host_url + "comment",
                success: function(data){
                    working = false;
                    if(data == '1'){
                        var commentElement = document.createElement('li');
                        commentElement.innerHTML = commentText;
                        commentList.prepend(commentElement);
                    }else if(data == '2'){
                        alert('You need to login to comment on posts.');
                    }
                    console.log(data)
                },
                error:function(data){
                    console.log(data);
                }});
        }

    })
};


