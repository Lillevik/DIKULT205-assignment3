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
<!DOCTYPE html>
<html>
    <head>
        <?php echo_metadata()?>
        <link rel="stylesheet" href="./css/passwordReset.css">
    </head>
    <body>
        <header>
            <?php get_navigation() ?>
        </header>
        <?php
        if(!isset($_GET['token'])){
            echo '
            <form action="./forgotPassword.php?forgot=true" method="post" class="password-form">
                <p style="width: 90%;margin: 10px auto auto;">Enter your email address to recieve a password reset link.</p>
                <label for="emailField" class="inputlabel">Email-address<span class="required" style="color:red;">*</span></label>
                <input type="text" id="emailField" class="input-field" name="email" placeholder="someone@example.com">
                <input type="submit" value="Send" class="submit-button">
                ' . (isset($_GET['success']) ? '<p class="infoMessage">An email to reset the password should have been sent, if the email exists in our system.</p>' : '') . '
                '. (isset($_GET['invalidToken']) ? '<p class="infoMessage">This token is expired or invalid.</p>' : '' ). '
                '. (isset($_GET['resetSuccess']) ? '<p class="infoMessage">Password was successfully changed!</p>' : '' ). '
            </form>';
        }else{
            echo
            '<form action="./forgotPassword.php?token='.$_GET['token'].'" method="post" class="password-form">
                <label for="password" class="inputlabel">Password</label>
                <input type="password" id="password" placeholder="New password" name="password" class="input-field">
                <label for="repeatpassword" class="inputlabel">Repeat password</label>
                <input type="password" id="repeatpassword" placeholder="Repeat password" name="repeatpassword" class="input-field">
                '. (isset($_GET['resetSuccess']) ? '<p class="infoMessage">Passwords are not matching!</p>' : '' ). '
                <input type="submit" value="Reset" class="submit-button">
            </form>';
        }

        ?>
    </body>
</html>
