/**
 * Created by goat on 18/01/17.
 */


//Variable for the like process
var working = false;


/**
 * This function checks if a post has already been liked
 * and sends an ajax request to the server in order to add
 * or delete a 'like' in the database.
 * @param element - The post to like or unlike.
 * @param id
 */
function like_post(element, id) {

    //Check if another process is already running
    if(!working){


        //Localhost
        var host_url = domain + "/api/posts/";

        //Starts the working process
        working = true;


        //The current like count
        var likesCount = $('#likes_count_' + id);

        if (element.classList.contains('fa-heart-o')){
            $.ajax({
                type: "POST",
                data: {data:id},
                url: host_url + "add_like",
                success: function(data){
                    working = false;
                    if(data === 'Success'){
                        likesCount.text(parseInt(likesCount.text()) +1);
                        element.classList.remove('fa-heart-o');
                        element.classList.add('fa-heart');
                    }else if(data === 'Access denied'){
                        alert('You need to login to like posts.')
                    }else if(data === 'error'){
                        alert('An error occurred.')
                    }
                }
            });

        }else{
            $.ajax({
                type: "POST",
                data: {data:id},
                url: host_url + "delete_like",
                success: function(data){
                    working = false;
                    if(data == 'Success'){
                        likesCount.text(parseInt(likesCount.text()) - 1);
                        element.classList.remove('fa-heart');
                        element.classList.add('fa-heart-o');
                        element.classList.add('shadow');
                        setTimeout(function(){
                            element.classList.remove('shadow');
                        }, 200)
                    }else if(data == 'Access denied'){
                        alert('You need to login to like posts.')
                    }else if(data === 'error'){
                        alert('An error occurred.')
                    }

                }
            });
        }
    }
}

/*
 var id = this.className.split(/\s+/)[0];
 var element = this;
 if(likes_window !== null){
 likes_window = $('div');
 likes_window.attr('id', 'likes-window')
 }else{
 likes_window = $('div');
 likes_window.attr('id', 'likes-window')
 }
 var likes_window = $('#likes-window');
 */


$(document).ready(function () {
    $(".display_likes").hover(function() {
        var likes_container = $($(this).find('.user_likes')[0]);
        var id = likes_container.attr('id');
        var element = this;
        var likes_window = $('#likes-window');
        likes_window.empty();

        if(likes_container.html() === ""){
            $.ajax({
                'type': 'get',
                'url': domain + '/api/posts/get_likes.php?post=' + id,
                success: function (data) {
                    if(data.usernames.length > 0){
                        var names_list = $('<ul></ul>');
                        names_list.addClass('tooltiptext');
                        for(var i = 0;i < data.usernames.length;i++){
                            var name_element = $('<li></li>');
                            name_element.addClass('username');
                            name_element.html(data.usernames[i]);
                            likes_container.append(name_element);
                        }
                    }else{
                        likes_container.append("<li>No likes.</li>")
                    }

                },
                error:function(){
                    console.log('error')
                }
            });
        }
    });

});
