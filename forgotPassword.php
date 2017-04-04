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
    if(isset($_GET['forgot'])){
        insert_recovery_token($email);
        header('Location: ./forgotPassword.php?success=true');
        exit();
    }else if(isset($_GET['token'])){
        if(token_exists($_GET['token'])){
            $pass = (isset($_POST['password'])? $_POST['password'] : '');
            $repeat = (isset($_POST['repeatpassword']) ? $_POST['repeatpassword']:'');
            if($pass == $repeat){
                reset_password($pass, $_GET['token']);
                header('Location: ./forgotPassword.php?resetSuccess=true');
                exit();
            }else{
                header('Location: ./forgotPassword.php?token='. $_GET['token'] .'match=false');
                exit();
            }
        }else{
            header('Location: ./forgotPassword.php?invalidToken=true');
            exit();
        }
    }
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
        <?php
        if(!isset($_GET['token'])){
            echo '
            <form action="./forgotPassword.php?forgot=true" method="post">
                <label for="emailField">Email-address</label><br>
                <input type="text" id="emailField" name="email" placeholder="someone@example.com"><br>
                <input type="submit">
                '. (isset($_GET['success']) ? '<p>If the email is in our system, we have sent a link to reset it.</p>' : '') . '
                '. (isset($_GET['invalidToken']) ? '<p>This token is expired or invalid.</p>' : '' ). '
                '. (isset($_GET['resetSuccess']) ? '<p>Password was successfully changed!</p>' : '' ). '
            </form>';
        }else{
            echo
            '<form action="./forgotPassword.php?token='.$_GET['token'].'" method="post">
                <input type="password" placeholder="New password" name="password">
                <input type="password" placeholder="Repeat password" name="repeatpassword">
                '. (isset($_GET['resetSuccess']) ? '<p>Passwords are not matching!</p>' : '' ). '
                <input type="submit">
            </form>';
        }

        ?>
    </body>
</html>
