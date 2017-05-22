<?php
$returnUrl = (isset($_GET["returnUrl"])? $_GET["returnUrl"]:"./");
$logout = false;
if(isset($_GET['logout'])){
    $logout = true;
    session_start();
    session_unset();
}
if($_SERVER["REQUEST_METHOD"] == "POST") {
    include 'dbHandling.php';
    $email = (isset($_POST['email']) ? $_POST['email'] : '');
    $password = (isset($_POST['password']) ? $_POST['password'] : '');
    if(!empty($email) and !empty($password)){
        if(validate_login($email, $password)){
            header("Location: $returnUrl");
            exit();
        }else{
            header("Location:./login.php?wrongPass=true&returnUrl=$returnUrl&email=$email");
            exit();
        }
    }else{
        header("Location:./login.php?required=true&returnUrl=$returnUrl&email=$email");
        exit();
    }
}else if($_SERVER["REQUEST_METHOD"] == "GET"){
    require "functions.php";
    $email = (isset($_GET['email']) ? $_GET['email'] : '');
}

$wrongPass = (isset($_GET['wrongPass'])?$_GET['wrongPass']:null);
$required = (isset($_GET['required'])?$_GET['required']:null);

?>
<!DOCTYPE html>
<body>
    <head>
        <?php echo_metadata() ?>
        <link rel="stylesheet" href="css/login.css">
    </head>
    <header>
        <?php get_navigation() ?>
    </header>
    <main>
        <form action="./login.php?returnUrl=<?php echo $returnUrl?>" method="POST" id="loginform">
            <label for="email" class="inputlabel">Username or email<input type="text" class="writteninput" id="email" name="email" placeholder="Username or email" title="Enter username or email" value="<?php echo (!empty($email) ? $email : '')?>"></label>
            <label for="password" class="inputlabel">Password<input type="password" class="writteninput" id="password" name="password" placeholder="Password" title="Enter password here"></label>
            <label for="submit">Need an <a href="./register.php">account?</a></label><input type="submit" id="submit" value="Login">
            <?php if (isset($wrongPass)){
                    echo '<p id="wronginfo">Wrong username or password.</p>';
                }else if(isset($required)){
                    echo '<p id="wronginfo">Please enter a username and password.</p>';
                }
                echo ($logout ? '<p id="info">Successfully logged out.</p>' : null);
                echo (isset($_GET['access']) ? '<p id="wronginfo">You need to login first.</p>' : null);
            ?>
            <p id="forgotPass">
                <a href="./forgotPassword.php">Forgot password?</a>
            </p>
        </form>
    </main>
</body>
