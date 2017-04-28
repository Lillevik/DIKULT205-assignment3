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
        $description = $post_array['description'];
        $filename = $post_array['post_key'] . $post_array['extension'];
        $user_id = $post_array['user_id'];

        if($_SESSION['id'] != $user_id){
            echo "This is not your post, shame on you...";
            exit();
        }
    }
}else if($_SERVER['REQUEST_METHOD'] == 'POST'){
    $title = $_POST['title'];
    $description = (isset($_POST['description']) ? $_POST['description'] : null);
    $post_id_or_key = (isset($_GET['post']) ? $_GET['post'] : null);
    $tags = $_POST['tags'];
    update_post($title, $description, $post_id_or_key, $tags);
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
        <script src="https://cloud.tinymce.com/stable/tinymce.min.js?apiKey=vnl5crhqxbrkolf8f9f8yce1ni48ud128eyl9624aw0r22n9"></script>
        <script>tinymce.init({ selector:'#description' });</script>
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
            }
            ?>
            <?php echo (isset($_GET['success']) ? "<p class='success-message'>Successfully updated the post.</p>" : null);?>
            <label for="title" class="inputlabel">Title</label><input type="text" id="title" name="title" placeholder="Title" value="<?php echo $title ?>">
            <img id="preview" src="./uploadsfolder/<?php echo $filename ?>" alt="Your image is displayed here" title="Your image">
            <label for="description" class="inputlabel">Description</label><textarea id="description" name="description" placeholder="Description..."><?php echo $description ?></textarea>

            <?php echo_tags(get_post_tags($post_id_or_key))?>
            <section id="buttons">
                <a href="./delete.php?post=<?php echo $id ?>"><button type="button" id="delete-button">Delete</button></a>
                <input type="submit" class="sub-form-button" value="Update">
            </section>
        </form>
        <?php echo_footer() ?>
    </body>
</html>