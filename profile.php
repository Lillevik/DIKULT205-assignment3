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
check_user_logged_in();
?>

<html>

<head>
    <?php echo_metadata() ?>
    <link rel="stylesheet" href="./css/index.css">
</head>

<body>
    <header>
        <?php get_navigation(); ?>
    </header>
    <main>
        <section id="personal-posts">
            <?php
                $posts = get_personal_posts();
                if(count($posts) > 0) {
                    foreach ($posts as $post) {
                        $cropped_image = $post->post_key . $post->extension;
                        echo
                        '<section class="post-wrapper">
                            <h1 class="post-title">' . $post->title . '</h1>
                            <img class="post-image" src="./uploadsfolder/' . $cropped_image . '">' .
                            '<section class="details">
                                <p class="post-description">' . $post->description . '</p><hr>' .
                                '<time class="date">Added:' . date("d/m/Y", strtotime($post->added)) . '</time>' .
                                '<p class="likes"><span class="' . $post->id . ' likes_number">' . $post->likes . '</span> likes</p>' .
                            '</section>' . '
                        </section>';
                    }
                }
            ?>
        </section>
    </main>

<footer>

</footer>
</body>
</html>
