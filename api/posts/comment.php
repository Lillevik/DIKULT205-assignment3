<?php
/**
 * Created by PhpStorm.
 * User: goat
 * Date: 06/04/2017
 * Time: 18:12
 */

if($_SERVER['REQUEST_METHOD'] == 'POST'){
    require '../../dbHandling.php';
    session_start();
    if(isset($_SESSION['logged_in'])){
        $key = $_POST['key'];
        $message = $_POST['commentField'];
        if(insert_comment($key, $message)){
            echo 1;
        }else{
            echo 0;
        }
    }else{
        echo 2;
    }


}