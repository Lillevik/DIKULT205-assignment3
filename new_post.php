<?php
/**
 * Created by PhpStorm.
 * User: goat
 * Date: 14/01/17
 * Time: 17:01
 */

session_start();
include 'functions.php';

check_user_logged_in();

include 'dbHandling.php';
include 'image_resizing.php';

//header("Location: /success?upload=true");
if ($_SERVER['REQUEST_METHOD'] == 'POST'){




    $err_arr = Array();
    $title = (isset($_POST['title']) ? $_POST['title'] : '');
    $description = (isset($_POST['description']) ? $_POST['description'] : '');
    $tags = (isset($_POST['tags']) ? $_POST['tags'] : Array());
    $numberOfTags = $tags.length;
    if($numberOfTags > 1 AND $numberOfTags <= 5){
        
    }

    $target_dir = "uploadsfolder/";
    $target_file = $target_dir . basename($_FILES["file-upload"]['name']);
    $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
    $filestring = random_string(6);
    $filename = $filestring . "." . $imageFileType;
    $cropped_name = $filestring . "c." . $imageFileType;
    $uploadOk = 1;


    // Check if image file is an actual image or fake image
    if(@is_array(getimagesize($_FILES["file-upload"]["tmp_name"]))){
        $image = true;
        $uploadOk = 1;
    } else {
        array_push($err_arr,"<p class='error-message'>File is not an image.</p>");
        $uploadOk = 0;
    }


    // Check file size
    if ($_FILES["file-upload"]["size"] > 6291456) {
        array_push($err_arr,"<p class='error-message'>Sorry, your file is too large. Max filesize is 6 megabytes.</p>");
        $uploadOk = 0;
    }

    // Allow certain file formats
    if (strtolower($imageFileType) != "jpg" && strtolower($imageFileType) != "png" && strtolower($imageFileType) != "jpeg"
        && strtolower($imageFileType) != "gif" && $image = true) {
        array_push($err_arr, "<p class='error-message'>Sorry, only JPG, JPEG, PNG & GIF files are allowed. Your file is of .$imageFileType filetype.</p>");
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

    //Resize file
    $resize_ok = null;
    try{
        $resize_ok = resize_image_to_400($_FILES["file-upload"]["tmp_name"], $target_dir, $cropped_name);
        resize_image_width($_FILES["file-upload"]["tmp_name"], $target_dir, $filestring . "800." . $imageFileType, 800, 100);
    }catch (Exception $e){
        array_push($err_arr, "<p class='error-message'>Sorry, there was an error handling the file.</p>");
    }

    if($resize_ok[0]){
        $uploadOk = 1;
    }else{
        array_push($err_arr, $resize_ok[1]);
        $uploadOk = 0;
    }

    // Check if $uploadOk is set to 0 by an error
    if ($uploadOk == 0) {
        if(!isset($err_msg)){
            array_push($err_arr, "<p class='error-message'>Sorry, your file was not uploaded.</p>");
        }


    // if everything is ok, try to upload file
    } else {

        if (move_uploaded_file($_FILES["file-upload"]["tmp_name"], $target_dir . $filename)) {
                insert_new_image($title, $description,$filename, $_SESSION['id']);
                header('Location:./edit_post.php?post=' . $filestring . '&uploadSuccess=true');
                exit();
        } else {
            array_push($err_arr, "<p class='error-message'>Sorry, there was an error uploading your file.</p>");
        }
    }
}
?>
<!DOCTYPE html>
<html>
    <head>
        <link rel="stylesheet" href="css/menu.css">
        <link rel="stylesheet" href="css/newpost.css">
        <script type="text/javascript" src="js/jquery.js"></script>
        <script src="http://cloud.tinymce.com/stable/tinymce.min.js?apiKey=vnl5crhqxbrkolf8f9f8yce1ni48ud128eyl9624aw0r22n9"></script>
        <script>tinymce.init({ selector:'#description' });</script>
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
                        echo $msg;
                    }
                }
                ?>
                <label for="title" class="inputlabel">Title</label><input type="text" id="title" name="title" placeholder="Title">
                <img id="preview" src="#" alt="Your image is displayed here" title="Your image">
                <label for="description" class="inputlabel">Description</label><textarea id="description" name="description" placeholder="Description..."></textarea>
                <label for="file-upload" class="custom-file-upload">
                    <div id="file-label-contents">
                        <img src="images/upload.png" id="upload-icon" alt="Upload icon">
                        <i style="margin-right:10px;">Select image file</i>
                    </div>
                </label>
                <input id="file-upload" type="file" name="file-upload"/>
                <div id="tags-container">
                </div>
                <p>Select between 1 to 5 tags for your post. An accurate title and use of relevant
                tags helps others explore your post.</p>

                <input type="checkbox" name="tags[]" value="funny" id="funny">
                <label for="funny" class="tag-label">Funny</label>

                <input type="checkbox" name="tags[]" value="art" id="art">
                <label for="art" class="tag-label">Art</label>

                <input type="checkbox" name="tags[]" value="nature" id="nature">
                <label for="nature" class="tag-label">nature</label>

                <input type="checkbox" name="tags[]" value="politics" id="politics">
                <label for="politics" class="tag-label">Politics</label>

                <input type="checkbox" name="tags[]" value="sports" id="sports">
                <label for="sports" class="tag-label">Sports</label>

                <input type="checkbox" name="tags[]" value="hobbies" id="hobbies">
                <label for="hobbies" class="tag-label">Hobbies</label>

                <input type="checkbox" name="tags[]" value="work" id="work">
                <label for="work" class="tag-label">Work</label>

                <input type="checkbox" name="tags[]" value="education" id="education">
                <label for="education" class="tag-label">Education</label>

                <input type="checkbox" name="tags[]" value="events" id="events">
                <label for="events" class="tag-label">Events</label>

                <input type="checkbox" name="tags[]" value="travel" id="travel">
                <label for="travel" class="tag-label">Travel</label>

                <input type="checkbox" name="tags[]" value="gaming" id="gaming">
                <label for="gaming" class="tag-label">Gaming</label>

                <input type="checkbox" name="tags[]" value="other" id="other">
                <label for="other" class="tag-label">Other</label>

                
                <input type="submit" value="Publish">

            </form>

            <script type="text/javascript">


          

            </script>

            <script type="text/javascript">
                function readURL(input) {
                    if (input.files && input.files[0]) {
                        var reader = new FileReader();

                        reader.onload = function (e) {
                            var image = $('#preview');
                            image.attr('src', e.target.result);
                            image.css({'display': 'block'});
                            $('#title').css({'margin':'10px auto'})
                        };
                        reader.readAsDataURL(input.files[0]);
                    }
                }

                function checkboxlimit(checkgroup, limit){
                    var checkgroup=checkgroup
                    var limit=limit
                    for (var i=0; i<checkgroup.length; i++){
                        checkgroup[i].onclick=function(){
                        var checkedcount=0
                        for (var i=0; i<checkgroup.length; i++)
                            checkedcount+=(checkgroup[i].checked)? 1 : 0
                        if (checkedcount>limit){
                            alert("You can only select a maximum of "+limit+" tags for your post.")
                            this.checked=false
                            }
                        }
                    }
                }

                checkboxlimit(document.getElementById('post-form').tags, 5)



                $("#file-upload").change(function(){
                    readURL(this);
                    var filename = $('input[type=file]').val().replace(/C:\\fakepath\\/i, '');
                    if(filename != ""){
                        if(filename.substr('.jpeg' | '.jpg' | '.gif' | '.png')){
                            {
                                $('.file_text').text(filename);
                            }
                        }else{
                            this.style('display','none');
                            $('.file_text').text("File is not an image.");
                        }
                    }
                });
            </script>
        </main>
    </body>
</html>