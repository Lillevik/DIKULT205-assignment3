<?php
/**
 * Created by PhpStorm.
 * User: goat
 * Date: 14/01/17
 * Time: 16:56
 */
session_start();
include 'functions.php';
check_user_logged_in();
?>

<html>

<head>
    <link rel="stylesheet" href="css/menu.css">
</head>

<body>
    <header>
        <?php get_navigation(); ?>
    </header>
    <p>Hello, <?php echo $_SESSION['username'] ?></p>
    <p>The profile page is currently under construction.</p>
    <?php
    mail($_SESSION['email'],'Password reset',
        '<DOCTYPE html>
        <html>
            <body>Hello, ' . $_SESSION["username"] . '. Here is a <a href="https://dikult205.k.uib.no/NSJ17/assignment3/forgotPassword.php?token=abcd12345">link</a> to <b>reset</b> your email.</body>
        </html>'
        ,"Content-Type: text/html; charset=UTF-8\r\n");
    ?>

    <main>
        <section id=""></section>
    </main>

<footer>

</footer>
</body>
</html>
