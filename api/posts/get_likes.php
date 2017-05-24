<?php
/**
 * Created by PhpStorm.
 * User: goat
 * Date: 23/01/17
 * Time: 19:58
 */

/*
 * This file gets all the users who have likes a post and
 * returns them in json format.
 */

if ($_SERVER['REQUEST_METHOD'] == 'GET'){
    session_start();
    header('Content-Type: application/json');
    $post_id = $_GET['post'];
    require '../../dbHandling.php';
    echo indent(get_post_likes($post_id));

}else{
    echo 'Method not allowed';
}