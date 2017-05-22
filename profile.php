<?php
/**
 * Created by PhpStorm.
 * User: goat
 * Date: 14/01/17
 * Time: 16:56
 */
session_start();
include 'functions.php';
include 'dbHandling.php';

$username = (isset($_GET['user'])?$_GET['user']:null);
if(isset($username) && !empty($username)){
    $posts_info = get_profile_info($username);
    ($posts_info = false ? null: $posts_info);
}else if(!isset($username) && isset($_SESSION['username'])){
    $username = $_SESSION['username'];
    $posts_info = $posts_info = get_profile_info($username);
}else{
    $posts_info = null;
}
?>

<!DOCTYPE html>
<html>

<head>
    <?php echo_metadata() ?>
    <link rel="stylesheet" href="./css/index.css">
    <link rel="stylesheet" href="./css/profile.css">
    <script type="text/javascript" src="./js/jquery.js"></script>
    <script type="text/javascript" src="./js/config.js"></script>
    <script type="text/javascript" src="./js/likes.js"></script>
    <script type="text/javascript" src="./js/favourite.js"></script>
    <script type="text/javascript" src="./js/script.js"></script>
</head>

<body>
    <header>
        <?php get_navigation(); ?>
    </header>
    <main>
        <?php

        if(isset($posts_info) && $posts_info != false ){
            echo
        "<section id='profile-info'>
            <section id='profile-info-header'>
                <img id='profile-image' src='{$posts_info['avatar']}'>
                <h3 id=\"profile-name\">$username</h3>
            </section>
            <section id=\"profile-details\">
                <p class=\"profile-detail\">
                    Posts: <span id=\"total-posts\">{$posts_info['total_posts']}</span>
                </p>
                <p class=\"profile-detail\">
                    Likes: <span id=\"total-likes\">{$posts_info['total_likes']}</span>
                </p>
            </section>";
            if(isset($_SESSION['id']) AND $_SESSION['username'] == $username) {
                echo "<details>
                        <summary>
                            Your favourites
                        </summary>
                        <ul>";
                            echo_user_favourites();
                echo   "</ul>
                      </details>";

            }
            echo "</section>" . PHP_EOL;
        }else{
            if(isset($_SESSION['username'])){
                echo "<p>No user found, click <a href='./profile.php'>here</a> for your own profile.</p>";
            }else{
                echo "<p>No user found, <a href='./login.php'>login</a> to check your own profile.</p>";
            }

        }

        ?>

        <section id="personal-posts">
            <?php echo (isset($posts_info) && $posts_info != false ? echo_posts($posts_info['posts']):"") ?>
        </section>
    </main>

<footer>

</footer>
</body>
</html>
