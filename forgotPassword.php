<?php
/**
 * Created by PhpStorm.
 * User: goat
 * Date: 03/04/2017
 * Time: 23:36
 */
include 'functions.php';
include 'dbHandling.php';

if($_SERVER['REQUEST_METHOD'] == 'POST'){
    $email = (isset($_POST['email']) ? $_POST['email']:'');
    mail($email,'Hello, ' . 'username',
        '<DOCTYPE html>
        <html>
            <body>Here is a <a href="localhost:8888/forgotPassword.php?token=abcd12345">link</a> to <b>reset</b> your email.</body>
        </html>'
        ,"Content-Type: text/html; charset=UTF-8\r\n");
}
?>

<html>
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link rel="stylesheet" href="./css/menu.css">
    </head>
    <body>
        <header>
            <?php get_navigation() ?>
        </header>

        <form action="./forgotPassword.php" method="post">
            <label for="emailField">Email-address</label><br>
            <input type="text" id="emailField" name="email" placeholder="someone@example.com"><br>
            <input type="submit">
            <?php echo (isset($success) ? 'We have sent an email to the address.':null)?>
            <?php echo (isset($acc_info) ? $acc_info:null)?>
        </form>
    </body>
</html>
