/**
 * Created by goat on 11/04/2017.
 * This file handles the ajax requests to
 * insert new favourites into the database
 * or deleting existing ones,
 * using the post api.
 */


var workingOnFavourite = false;
function favourite_post(element, id) {

    //Check if another process is already running
    if(!workingOnFavourite){

        //Localhost or server
        var host_url = domain + "/api/posts/";

        var favouriteCount = $('#favourite_count_' + id);




        if (element.classList.contains('fa-star-o')){
            $.ajax({
                type: "POST",
                data: {data:id},
                url: host_url + "add_favourite",
                success: function(data){

                    workingOnFavourite = false;
                    if(data === 'Success'){
                        favouriteCount.text(parseInt(favouriteCount.text()) +1);
                        element.classList.remove('fa-star-o');
                        element.classList.add('fa-star');
                    }else if(data === 'Access denied'){
                        alert('You need to login to favourite posts.')
                    }else if(data === 'error'){
                        alert('An error occurred.')
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
                    }else if(data === 'error'){
                        alert('An error occurred.')
                    }

                }
            });
        }
    }
}

