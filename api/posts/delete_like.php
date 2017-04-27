<?php
if ($_SERVER['REQUEST_METHOD'] == 'POST'){
    session_start();
    if(isset($_SESSION['logged_in'])){
        require '../../dbHandling.php';
        $post_id = $_POST['data'];
        delete_favourite_or_like($post_id, $_SESSION['id'], "likes");
        echo 'Success';
    }else{
        echo "Access denied";
    }
}else{
    echo 'Method not allowed';
}