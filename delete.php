<?php
/**
 * Created by PhpStorm.
 * User: goat
 * Date: 21/01/17
 * Time: 20:11
 */

require 'dbHandling.php';
require 'functions.php';
session_start();
check_user_logged_in();
if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    $post_id = (isset($_GET['post']) ? $_GET['post'] : null);
    $post_array = get_post_info($post_id);

    if (!$post_array) {
        echo 'This post does not exist.';
    } else {
        $id = $post_array['id'];
        $title = $post_array['title'];
        $description = $post_array['description'];
        $filename = $post_array['filename'];
        $user_id = $post_array['user_id'];

        if($_SESSION['id'] != $user_id){
            echo "This is not your post, shame on you...";
            exit();
        }
    }
}else if($_SERVER['REQUEST_METHOD'] == 'POST'){
    header('Location: success.php?delete=true');
    $post_id = (isset($_GET['post']) ? $_GET['post'] : null);
    $post_array = get_post_info($post_id);
    delete_post($post_id);
    exit();
}else{
    echo "Method not allowed";
}
?>

<!DOCTYPE html>
<html>
    <head>
        <link rel="stylesheet" href="./css/delete.css" type="text/css">
    </head>
    <body>

        <form action="delete.php?post=<?php echo $id ?>" method="POST">
            <img id="preview-image" src="./uploadsfolder/<?php echo $filename;?>"
            <label for="submit">Are you sure you wish to delete this post?</label>
            <input type="submit" name="submit" id="submit" value="Yes">
            <a href="edit_post.php?post=<?php echo $id ?>"><input type="button" value="No"></a>
        </form>
    </body>
</html>
