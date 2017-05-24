<?php

/*
 * This file handles inserting of new likes.
 */

if ($_SERVER['REQUEST_METHOD'] == 'POST'){
    session_start();
    $post_id = $_POST['data'];
    if(isset($_SESSION['logged_in'])){
        require '../../dbHandling.php';
        if(insert_favourite_or_like($post_id, $_SESSION['id'], 'likes')){
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