<?php
/**
 * Created by PhpStorm.
 * User: goat
 * Date: 23/01/17
 * Time: 19:58
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