<?php
/**
 * Created by PhpStorm.
 * User: goat
 * Date: 22/02/2017
 * Time: 10:15
 */

include '../../../dbHandling.php';
header('Content-Type: application/json');
try{
    get_fresh_posts(10);
}catch (Exception $e){
    echo "Not working!";
}
