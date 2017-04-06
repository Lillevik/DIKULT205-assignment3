<?php
/**
 * Created by PhpStorm.
 * User: goat
 * Date: 05/04/2017
 * Time: 10:23
 */
require 'dbHandling.php';

session_start();
$posts = get_posts_before_and_after($_GET['key']);

//An object of the current post
$post = $posts['current'];

//An array of comments
$comments = get_post_comments($post->post_key);

//A key to the previous post
$previousKey = $posts['previous'];

//A key to the next post
$nextKey = $posts['next'];

//An image url
$url = $post->post_key . $post->extension;

?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <link rel="stylesheet" href="./css/singlePost.css">
    <link rel="stylesheet" href="./css/menu.css">
    <script src="https://use.fontawesome.com/db87107c26.js"></script>
    <script src="./js/jquery.js"></script>
    <script src="./config.js"></script>
    <script src="./js/post.js"></script>
    <title><?php echo $post->title ?></title>

</head>
<body>
    <header>
        <?php get_navigation() ?>
    </header>
    <main>

        <section id="post-wrapper">
            <h1 class="post-title"><?php echo $post->title?></h1>
            <p>
                <?php echo ($previousKey != 'Empty' ? "<a id='previous' href='./post.php?key=$previousKey'><- prev</a>":'')?>
                <?php echo ($nextKey != 'Empty' ? "<a id='next' href='./post.php?key=$nextKey'>next -></a>":'')?>
            </p>
            <div id="img-wrapper">
                <img id="post-image" src="./uploadsfolder/<?php echo $url?>">
            </div>
            <section id="description">
                <?php echo $post->description?>
            </section>
            <form id="comment-form" action="./post.php?key=<?php echo $post->post_key?>" method="post">
                <textarea id="comment-field" name="comment-field" placeholder="Comment here.."></textarea>
                <input type="submit" id="sub-button" name="sub-button">
            </form>

            <ul id="comments-list">
                <?php
                    if($comments){
                        foreach($comments as $comment){
                            echo '<li class="comment">'.$comment->text.'</li>';
                        }
                    }

                ?>

            </ul>
        </section>


    </main>
</body>
</html>
