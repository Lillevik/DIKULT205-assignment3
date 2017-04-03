<?php
/**
 * Created by PhpStorm.
 * User: goat
 * Date: 18/01/17
 * Time: 22:57
 */
if ($_SERVER['REQUEST_METHOD'] == 'POST'){
    session_start();
    if(isset($_SESSION['logged_in'])){
        require '../../../dbHandling.php';
        $post_id = $_POST['data'];
        delete_like($post_id, $_SESSION['id']);
        echo 'Success';
    }else{
        echo "Access denied";
    }
}else{
    echo 'Method not allowed';
}