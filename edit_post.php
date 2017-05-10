<?php
/**
 * Created by PhpStorm.
 * User: goat
 * Date: 21/01/17
 * Time: 14:46
 */

require_once 'dbHandling.php';
require_once 'functions.php';

if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    session_start();
    check_user_logged_in();
    $post_id_or_key = (isset($_GET['post']) ? $_GET['post'] : null);
    $post_array = get_post_info($post_id_or_key);
    $uploadSuccess = (isset($_GET['uploadSuccess']) ? true:false);
    if (!$post_array) {
        echo 'This post does not exist.';
        exit();
    } else {
        $id = $post_array['id'];
        $title = $post_array['title'];
        $description = strip_tags($post_array['description']);
        $filename = $post_array['post_key'] . $post_array['extension'];
        $user_id = $post_array['user_id'];
        $editSuccess = (isset($_GET['success']) ? true:false);
        if($_SESSION['rank'] != 'admin'){
            if($_SESSION['id'] != $user_id){
                echo "This is not your post, shame on you...";
                exit();
            }
        }
    }
}else if($_SERVER['REQUEST_METHOD'] == 'POST'){
    //Get the raw post contents
    $raw_title = (isset($_POST['title']) ? $_POST['title'] : '');
    $raw_description = (isset($_POST['description']) ? $_POST['description'] : '');

    //Remove script tags and add newline
    $title = strip_tags($raw_title);
    $description = strip_tags($raw_description);


    $nsfw = (isset($_POST['nsfw']) ? true : false);
    $post_id_or_key = (isset($_GET['post']) ? $_GET['post'] : null);
    $tags = $_POST['tags'];
    update_post($title, $description, $post_id_or_key, $tags, $nsfw);
    header('Location: edit_post.php?post=' . $post_id_or_key . "&success=true");
    exit();
}

?>

<!DOCTYPE html>
<html>
    <head>
        <?php echo_metadata() ?>
        <link rel="stylesheet" href="css/newpost.css">
        <script type="text/javascript" src="js/jquery.js"></script>
        <script type="text/javascript" src="js/new_post.js"></script>
        <style>
            #preview{
                display: block;
            }
        </style>
    </head>

    <body>
        <header>
            <?php get_navigation(); ?>
        </header>
        <form action="edit_post.php?post=<?php echo $id ?>" id="post-form" method="POST" enctype="multipart/form-data">
            <?php
            if($uploadSuccess){
                echo '<p>Post was successful.</p>';
                echo '<p>Permalink: <a href="./post.php?key='.$post_array['post_key'].'">here</a></p>';
            }else if($editSuccess){
                echo '<p>Edit was successful.</p>';
                echo '<p>Permalink: <a href="./post.php?key='.$post_array['post_key'].'">here</a></p>';
            }
            ?>

            <label for="title" class="inputlabel">Title</label>
            <input type="text" id="title" name="title" placeholder="Enter a title for your post..." value="<?php echo (isset($title)? $title: "") ?>" maxlength="100">
            <div id="title-chars"><span id="number-of-title-chars"><?php echo (isset($title)? strlen($title): "") ?></span> of 100 characters</div>
            <img id="preview" src="./uploadsfolder/<?php echo $filename ?>" alt="Your image is displayed here" title="Your image">
            <label for="description" class="inputlabel">Description</label>
            <textarea id="description" name="description" placeholder="Enter a short describing text for your post..." maxlength="300"><?php echo (isset($description)? $description : "") ?></textarea>
            <div id="description-chars"><span id="number-of-description-chars"><?php echo (isset($description)? strlen($description) : "") ?></span> of 300 characters.</div>
            <label for="nsfw" title="This wil blur your post so other users can choose to view it.">Not safe for work?</label>
            <input type="checkbox" name="nsfw" id="nsfw" value="nsfw">
            <p class="info">Select between 1 to 5 tags for your post. An accurate title and use of relevant
                tags helps others explore your post.</p>
            <?php echo_tags(get_post_tags($post_id_or_key))?>
            <section id="buttons">
                <a href="./delete.php?post=<?php echo $id ?>"><button type="button" id="delete-button">Delete</button></a>
                <input type="submit" class="sub-form-button" value="Update">
            </section>
        </form>
        <?php echo_footer() ?>
    </body>
</html>