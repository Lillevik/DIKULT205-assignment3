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
$comments = get_post_comments($post['post_key']);

//A key to the previous post
$previousKey = $posts['previous'];

//A key to the next post
$nextKey = $posts['next'];

//An image url
$url = $post['post_key'] . $post['extension'];

?>
<!DOCTYPE html>
<html>
<head>
    <?php echo_metadata($post['title']) ?>
    <link rel="stylesheet" href="./css/singlePost.css">
    <script src="./js/jquery.js"></script>
    <script src="js/config.js"></script>
    <script src="./js/post.js"></script>
    <script src="./js/likes.js"></script>
    <script src="./js/favourite.js"></script>
</head>
<body>
    <header>
        <?php get_navigation() ?>
    </header>
    <main>

        <section id="post-wrapper">

            <p class="next">
                <?php echo ($previousKey != 'Empty' ? "<a id='previous' href='./post.php?key=$previousKey'><- prev</a>":'')?>
                <?php echo ($nextKey != 'Empty' ? "<a id='next' href='./post.php?key=$nextKey'>next -></a>":'')?>
            </p>
            <h1 class="post-title"><?php echo $post['title']?></h1>
            <div id="img-wrapper">
                <img id="post-image" class="<?php echo ($post['nsfw']?'':'')?>" src="./uploadsfolder/<?php echo $url?>">
            </div>
            <section id="description">
                <?php echo nl2br($post['description']) ?>
            </section>
            <section id="tag-section">
                <?php echo_post_tags($post['id']) ?>
            </section>
            <section id="react-section">
                <i id="<?php echo $post['id']?>" class="like-icon fa fa-heart<?php echo (isset($post['liked']) ? '' : '-o') ?>" aria-hidden="true" onclick="like_post(this,<?php echo $post['id'] ?>)">
                    <p class="count-p"><span id="likes_count_<?php echo $post['id'] ?>"><?php echo $post['likes'] ?></span> <span id="likes_window" class="<?php echo $post['id'] ?>"></span>likes</p>
                </i>
                <i id="<?php echo $post['id']?>" class="favo-icon fa fa-star<?php echo (isset($post['is_favourite']) ? '' : '-o') ?> icon" aria-hidden="true" onclick="favourite_post(this,<?php echo $post['id'] ?>)">
                    <p class="count-p"><span id="favourite_count_<?php echo $post['id'] ?>"><?php echo $post['favourites']?></span> favorites</p>
                </i>
            </section>

            <form id="comment-form" action="./post.php?key=<?php echo $post['post_key'] ?>" method="post">
                <textarea id="comment-field" name="comment-field" placeholder="Comment here.."></textarea>
                <input type="submit" id="sub-button" name="sub-button" value="Submit">
            </form>

            <ul id="comments-list">
                <?php
                    //If there are comments, echo them
                    if($comments){
                        echo_comment_tree($comments);
                    }
                ?>
            </ul>
        </section>


    </main>
</body>
</html>
