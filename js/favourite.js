/**
 * Created by goat on 11/04/2017.
 */

var workingOnFavourite = false;
function favourite_post(element, id) {

    //Check if another process is already running
    if(!workingOnFavourite){

        //Localhost
        var host_url = domain + "/api/posts/";



        if (element.classList.contains('fa-star-o')){
            $.ajax({
                type: "POST",
                data: {data:id},
                url: host_url + "add_favourite",
                success: function(data){

                    workingOnFavourite = false;
                    if(data === 'Success'){
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

