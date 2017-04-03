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
 */
function like_post(element) {

    //Check if another process is already running
    if(!working){
        //School server
        //var host_url = "https://dikult205.k.uib.no/NSJ17/assignment3/api/posts/";

        //Localhost
        var host_url = "http://localhost:8888/api/posts/";

        //var host_url = "http://localhost/api/posts/";

        //Starts the working process
        working = true;

        //The post id
        var id = element.getAttribute("id");

        //The current like count
        var likesCount = $('.' + id);

        if (element.getAttribute("src") == './images/like.png'){
            $.ajax({
                type: "POST",
                data: {data:id},
                url: host_url + "add_like",
                success: function(data){
                    working = false;
                    if(data == 'Success'){
                        likesCount.text(parseInt(likesCount.text()) +1);
                        element.setAttribute('src', './images/liked.png');
                    }else if(data == 'Access denied'){
                        alert('You need to login to like posts.')
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
                        element.setAttribute('src', './images/like.png');
                    }else if(data == 'Access denied'){
                        alert('You need to login to like posts.')
                    }

                }
            });
        }
    }

}


$(document).ready(function () {
    $(".likes_number").hover(function() {
        var id = this.className.split(/\s+/)[0];
        var element = this;
        var likes_window = $('#likes-window');
        likes_window.empty();
        $.ajax({
            'type': 'get',
            'url': '/api/posts/get_likes.php?post=' + id,
            success: function (data) {
                var y = $(window).height() - element.offsetTop - element.offsetHeight;
                var x = element.offsetLeft;


                likes_window[0].style.bottom = (y) + 'px';
                likes_window[0].style.left = (x) + 'px';
                var name_list = data['usernames'];
                for(var i = 0; i<name_list.length;i++){
                    likes_window.append('<li class="like-name">' + name_list[i].toString() +  + '</li>');
                }
                likes_window.css({'display':'block'});

            }
        }

        , function () {
                likes_window.css('display','none');
        });

    });




});
