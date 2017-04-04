<?php
/**
 * Created by PhpStorm.
 * Date: 14/01/17
 * Time: 02:34
 */
if($_SERVER["REQUEST_METHOD"] == "POST") {
    include 'dbHandling.php';
    $email = (isset($_POST['email']) ? $_POST['email'] : '');
    $password = (isset($_POST['password']) ? $_POST['password'] : '');

    if(!empty($email) and !empty($password)){
        if(validate_login($email, $password)){
            header('Location: ./');
            exit();
        }else{
            $wrongPass = true;
        }
    }else{
        $required = true;
    }
}


?>

<head>
    <link rel="stylesheet" href="css/login.css">
</head>
<body>
    <main>
        <a href="./" id="backtofrontpage">To the frontpage</a>
        <form action="./login.php" method="POST" id="loginform">
            <label for="email" class="inputlabel">Username or email<input type="text" class="writteninput" id="email" name="email" placeholder="Username or email" title="Enter username or email" value="<?php echo (isset($email) ? $email : '');?>"></label>
            <label for="password" class="inputlabel">Password<input type="password" class="writteninput" id="password" name="password" placeholder="Password" title="Enter password here"></label>
            <label for="submit">Need an <a href="./register.php">account?</a></label><input type="submit" id="submit" value="Login">
            <?php if (isset($wrongPass)){
                    echo '<p id="wronginfo">Wrong username or password.</p>';
                }else if(isset($required)){
                    echo '<p id="wronginfo">Please enter a username and password.</p>';
                }
                echo (isset($_GET['logout']) ? '<p id="info">Successfully logged out</p>' : null);
                echo (isset($_GET['access']) ? '<p id="wronginfo">You need to login first.</p>' : null);
            ?>
            <p><a href="./forgotPassword.php">Forgot password?</a></p>
        </form>
    </main>
</body>
