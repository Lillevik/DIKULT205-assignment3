<?php

/*
 * This file handles deleting of user favourites.
 */

if ($_SERVER['REQUEST_METHOD'] == 'POST'){
    session_start();
    if(isset($_SESSION['logged_in'])){
        require '../../dbHandling.php';
        $post_id = $_POST['data'];
        if(delete_favourite_or_like($post_id, $_SESSION['id'], 'favourites')){
            echo 'Success';
        }else{
            echo 'error';
        }
    }else{
        echo "Access denied";
    }
}else{
    echo 'Method not allowed';
}

?>