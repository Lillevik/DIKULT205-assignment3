<?php
/*
 *  This file handles inserting of new comments.
 */

if($_SERVER['REQUEST_METHOD'] == 'POST'){
    require '../../dbHandling.php';
    session_start();
    if(isset($_SESSION['logged_in'])){
        $key = $_POST['post_key'];
        $message = strip_tags($_POST['commentField']);
        $parent_id = $_POST['parent_id'];
        $insertResult = insert_comment($key, $message, $parent_id);
        if($insertResult[0]){
            header('Content-Type: application/json');
            $commentArr = Array();
            $commentArr['comment'] = $insertResult[1];
            //Encode the array containing the posts
            $json = json_encode($commentArr);

            //Echo an indented json string to be used on the client side.
            echo indent($json);
         }else{
            echo 0;
         }
     }else{
        echo 2;
     }
}