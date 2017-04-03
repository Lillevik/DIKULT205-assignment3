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

    <main>
        <section id=""></section>
    </main>

<footer>

</footer>
</body>
</html>
