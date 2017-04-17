/**
 * Created by goat on 11/04/2017.
 */

var workingOnFavourite = false;
function favourite_post(element) {

    //Check if another process is already running
    if(!workingOnFavourite){

        //Localhost
        var host_url = domain + "/api/posts/";

        //The post id
        var id = element.getAttribute("id");

        //The current like count
        var favouriteCount = $('#favourite_count_' + id);

        if (element.classList.contains('fa-star-o')){
            $.ajax({
                type: "POST",
                data: {data:id},
                url: host_url + "add_favourite",
                success: function(data){
                    console.log(data);

                    workingOnFavourite = false;
                    if(data === 'Success'){
                        favouriteCount.text(parseInt(favouriteCount.text()) + 1);
                        element.classList.remove('fa-star-o');
                        element.classList.add('fa-star');
                    }else if(data === 'Access denied'){
                        alert('You need to login to favourite posts.')
                    }
                }
            });

        }else{
            $.ajax({
                type: "POST",
                data: {data:id},
                url: host_url + "delete_favourite",
                success: function(data){
                    workingOnFavourite = false;
                    if(data === 'Success'){
                        favouriteCount.text(parseInt(favouriteCount.text()) - 1);
                        element.classList.remove('fa-star');
                        element.classList.add('fa-star-o');
                    }else if(data === 'Access denied'){
                        alert('You need to login to favourite posts.')
                    }

                }
            });
        }
    }
}

