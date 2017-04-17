<?php
/**
 * Created by PhpStorm.
 * User: goat
 * Date: 11/04/2017
 * Time: 00:29
 */


if ($_SERVER['REQUEST_METHOD'] == 'POST'){
    session_start();
    $post_id = $_POST['data'];
    if(isset($_SESSION['logged_in'])){
        require '../../dbHandling.php';
        insert_favourite($post_id, $_SESSION['id']);
        echo 'Success';
    }else{
        echo "Access denied";
    }
}else{
    echo 'Method not allowed';
}

?>