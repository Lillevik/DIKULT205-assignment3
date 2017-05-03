<?php
/**
 * Created by PhpStorm.
 * User: goat
 * Date: 14/01/17
 * Time: 17:01
 */

session_start();
include 'functions.php';
$url = $_SERVER['REQUEST_URI'];
check_user_logged_in($url);

include 'dbHandling.php';
include 'image_resizing.php';

//header("Location: /success?upload=true");
if ($_SERVER['REQUEST_METHOD'] == 'POST'){

    $err_arr = Array();
    $title = (isset($_POST['title']) ? strip_tags($_POST['title']) : '');
    $description = (isset($_POST['description']) ? strip_tags($_POST['description']) : '');
    $tags = (isset($_POST['tags']) ? $_POST['tags'] : Array());
    $numberOfTags = count($tags);
    if($numberOfTags > 5){
        $err_arr[] = "You can have a maximum of 5 tags.";
    }else if($numberOfTags == 0){
        $err_arr[] = "You must have at least one tag.";
    }

    $titleLength = strlen($title);
    if(empty($title)){
        $err_arr[] = "The title is required.";
    }else if($titleLength > 100){
        $err_arr[] = "The title can be no longer than 100 characters.";
    }else if($titleLength < 2){
        $err_arr[] = "The title must be longer than 2 characters.";
    }

    $descriptionLength = strlen($description);
    if($descriptionLength > 300){
        $err_arr[] = "The description can be no longer than 300 characters.";
    }


    if(!count($err_arr)) {


        $target_dir = "uploadsfolder/";
        $target_file = $target_dir . basename($_FILES["file-upload"]['name']);
        $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
        $filestring = random_string(6);
        $filename = $filestring . "." . $imageFileType;
        $cropped_name = $filestring . "c." . $imageFileType;
        $uploadOk = 1;


        $fileContent = exif_imagetype(($_FILES["file-upload"]["tmp_name"]));

        if($fileContent == IMAGETYPE_GIF or $fileContent == IMAGETYPE_JPEG or $fileContent == IMAGETYPE_PNG){
            $image = true;
            $uploadOk = 1;
        } else {
            array_push($err_arr, "File is not an image.");
            $uploadOk = 0;
        }


        // Check file size
        if ($_FILES["file-upload"]["size"] > 6291456) {
            array_push($err_arr, "Sorry, your file is too large. Max filesize is 6 megabytes.");
            $uploadOk = 0;
        }

        // Allow certain file formats
        if (strtolower($imageFileType) != "jpg" && strtolower($imageFileType) != "png" && strtolower($imageFileType) != "jpeg"
            && strtolower($imageFileType) != "gif" && $image = true
        ) {
            array_push($err_arr, "Sorry, only JPG, JPEG, PNG & GIF files are allowed. Your file is of .$imageFileType filetype.");
            $uploadOk = 0;
        }

        // Check if file already exists
        if (file_exists($target_dir . $filename)) {
            $finished = false;
            while (!$finished) {
                $filename = random_string(6) . "." . strtolower($imageFileType);
                if (!file_exists($target_dir . $filename)) {
                    $finished = true;
                }
            }
        }

        if($uploadOk != 0) {
            //Resize file
            $resize_ok = null;
            try {

                $resize_ok = resize_image_to_400($_FILES["file-upload"]["tmp_name"], $target_dir, $cropped_name);
                resize_image_width($_FILES["file-upload"]["tmp_name"], $target_dir, $filestring . "800." . $imageFileType, 800, 100);
            } catch (Exception $e) {
                array_push($err_arr, "Sorry, there was an error handling the file.");
            }

            if ($resize_ok[0]) {
                $uploadOk = 1;
            } else {
                array_push($err_arr, $resize_ok[1]);
                $uploadOk = 0;
            }
        }

        // Check if $uploadOk is set to 0 by an error
        if ($uploadOk == 0) {
            if (!isset($err_msg)) {
                array_push($err_arr, "Sorry, your file was not uploaded.");
            }


            // if everything is ok, try to upload file
        } else {

            if (move_uploaded_file($_FILES["file-upload"]["tmp_name"], $target_dir . $filename)) {
                insert_new_post($title, $description, $filename, $_SESSION['id'], $tags);
                header('Location:./edit_post.php?post=' . $filestring . '&uploadSuccess=true');
                exit();
            } else {
                array_push($err_arr, "Sorry, there was an error uploading your file.");
            }
        }
    }
}
?>
<!DOCTYPE html>
<html>
    <head>
        <?php echo_metadata()?>
        <link rel="stylesheet" href="css/newpost.css">
        <script type="text/javascript" src="js/jquery.js"></script>
        <script type="text/javascript" src="js/new_post.js"></script>
    </head>

    <body>
        <header>
            <?php get_navigation(); ?>
        </header>
        <main>
            <form action="new_post.php" method="POST" enctype="multipart/form-data" id="post-form">
                <?php
                if(isset($err_arr)){
                    foreach($err_arr as $msg){
                        echo "<p class='error-message'>$msg</p>";
                    }
                }
                ?>
                <label for="title" class="inputlabel">Title</label>
                <input type="text" id="title" name="title" placeholder="Enter a title for your post..." value="<?php echo (isset($title)? $title: "") ?>" maxlength="100">
                <div id="title-chars"><span id="number-of-title-chars">0</span> of 100 characters</div>
                <img id="preview" src="#" alt="Your image is displayed here" title="Your image">
                <label for="description" class="inputlabel">Description</label>
                <textarea id="description" name="description" placeholder="Enter a short describing text for your post..." maxlength="300"><?php echo (isset($description)? $description : "") ?></textarea>
                <div id="description-chars"><span id="number-of-description-chars">0</span> of 300 characters.</div>
                <label for="file-upload" class="custom-file-upload">
                    <div id="file-label-contents">
                        <img src="images/upload.png" id="upload-icon" alt="Upload icon">
                        <i style="margin-right:10px;">Select image file</i>
                    </div>
                </label>
                <input id="file-upload" type="file" name="file-upload"/>
                <p>Select between 1 to 5 tags for your post. An accurate title and use of relevant
                tags helps others explore your post.</p>

                <?php
                    echo_tags();
                ?>

                <section id="buttons">
                    <input type="submit" class="sub-form-button" value="Publish">
                </section>
            </form>
        </main>
        <?php echo_footer() ?>
    </body>
</html>