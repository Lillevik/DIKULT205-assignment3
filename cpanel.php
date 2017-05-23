<?php
/**
 * Created by PhpStorm.
 * User: goat
 * Date: 10/05/2017
 * Time: 16:12
 */

include 'functions.php';
session_start();
$rank = (isset($_SESSION['rank']) ? $_SESSION['rank'] : '');
if($rank != 'admin'){
    echo 'You are not authorized to view this page.';
    exit();
}
?>

<!DOCTYPE html>
<html>
    <head>
        <?php echo_metadata() ?>
    </head>
    <body>
        <header>
            <?php get_navigation() ?>
        </header>
        <main>
            <p>Nothing to show here at this time.</p>
            <p>However, as an administrator you are able to edit or delete anyone's posts by clicking the edit icon
               in the bottom right corner of a post.</p>
        </main>
    </body>
</html>
