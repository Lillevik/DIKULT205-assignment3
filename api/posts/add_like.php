<?php

if ($_SERVER['REQUEST_METHOD'] == 'POST'){
    session_start();
    $post_id = $_POST['data'];
    if(isset($_SESSION['logged_in'])){
        require '../../dbHandling.php';
        insert_like($post_id, $_SESSION['id']);
        echo 'Success';
    }else{
        echo "Access denied";
    }
}else{
    echo 'Method not allowed';
}