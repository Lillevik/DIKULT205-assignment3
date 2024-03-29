<?php
/**
 * User: goat
 * Date: 08/04/2017
 * Time: 12:00
 */
include 'dbHandling.php';
include 'image_resizing.php';
session_start();
check_user_logged_in();

if ($_SERVER['REQUEST_METHOD'] == 'POST'){
    $domain = get_domain();
    $user_id =$_SESSION['id'];
    $err_arr = Array();
    $infoMsg = null;

    if($_FILES['inputFile']['size'] != 0){
        $target_dir = "./avatars/";
        $target_file = $target_dir . basename($_FILES["inputFile"]['name']);
        $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
        $filestring = random_string(6);
        $filename = $filestring . "." . $imageFileType;
        $uploadOk = 1;


        // Check if image file is an actual image or fake image
        $fileContent = exif_imagetype(($_FILES["inputFile"]["tmp_name"]));

        if($fileContent == IMAGETYPE_GIF or $fileContent == IMAGETYPE_JPEG or $fileContent == IMAGETYPE_PNG){
            $image = true;
            $uploadOk = 1;
        } else {
            array_push($err_arr,"<p class='error-message'>File is not an image.</p>");
            $uploadOk = 0;
        }


        // Check file size
        if ($_FILES["inputFile"]["size"] > 6291456) {
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



        // Check if $uploadOk is set to 0 by an error
        if ($uploadOk == 0) {
            if(!isset($err_msg)){
                array_push($err_arr, "<p class='error-message'>Sorry, your avatar was not updated.</p>");
            }

        // if everything is ok, try to upload file
        } else {

            if (move_uploaded_file($_FILES["inputFile"]["tmp_name"], $target_dir . $filename)) {
                $avatarImage = $filestring . "80." . $imageFileType;
                $resize_ok = resize_crop_image(80, 80,$target_dir . $filename, $target_dir . $avatarImage, 100);
                if(!$resize_ok[0]){
                    $err_arr[] = $resize_ok[1];
                    unlink($target_dir . $filename);
                }else{
                    insert_new_avatar($filestring, "." . $imageFileType, $user_id);
                    update_user_avatar($user_id, $avatarImage);
                    $_SESSION['avatar'] = $avatarImage;
                    unlink($target_dir . $filename);
                    header('Location: ./avatar.php?upload=success');
                    exit();
                }
            } else {
                array_push($err_arr, "<p class='error-message'>Sorry, there was an error uploading your file.</p>");
            }
        }
    }else if(isset($_POST['radioImage'])){
        update_user_avatar($user_id, $_POST['radioImage']);
        $_SESSION['avatar'] = $_POST['radioImage'];
        header('Location: ./avatar.php?selected=true');
        exit();
    }else if(isset($_POST['removeAvatar'])){
        update_user_avatar($user_id, null);
        $_SESSION['avatar'] = null;
        header('Location: ./avatar.php?rmd=true');
        exit();
    }
}
if(isset($_GET['upload'])){
    $infoMsg = 'Your uploaded avatar was added and selected';
}else if(isset($_GET['selected'])){
    $infoMsg = 'Your selected avatar was updated.';
}else if(isset($_GET['rmd'])){
    $infoMsg = "Your avatar was removed.";
}


?>
<!DOCTYPE html>
<html>
    <head>
        <?php echo_metadata() ?>
        <link rel="stylesheet" href="css/menu.css">
        <link rel="stylesheet" href="css/avatar.css">
    </head>
    <body>
        <header>
            <?php get_navigation() ?>
        </header>
        <main>
            <h1>Profile</h1>
            <section>
                <h3>Select avatar</h3>
                <?php
                if(isset($err_arr)) {
                    foreach ($err_arr as $msg) {
                        echo "<p class='err-msg'>$msg</p>";
                    }
                }
                ?>
                <h4><?php echo (isset($infoMsg)? $infoMsg : null)?></h4>
                <img src="<?php echo ($_SESSION['avatar'] != null ? './avatars/' . $_SESSION['avatar'] : './images/profile.png')?>" id="avatar-preview">
                <form action="avatar.php" method="post" id="choose-avatar-form" enctype="multipart/form-data">
                    <label for="inputFile">Jpg, png or gif files</label>
                    <input id="inputFile" type="file" name="inputFile">
                    <label for="userImage">Or choose from your other avatars</label>
                    <div>
                    <?php
                        $avatars = get_user_avatars($_SESSION['id']);
                        foreach ($avatars as $ava){
                            $filename = $ava->key . "80" . $ava->extension;
                            echo
                            "<label class='recent-avatar-label'>
                                <input type='radio' name='radioImage' class='recentAva' value='$filename'>
                                <img src='./avatars/$filename' class='recent-avatar'>
                             </label>";
                        }
                    ?>


                    <label for="removeAvatar">Or remove avatar
                        <input type="radio" name="removeAvatar" id="removeAvatar">
                    </label>
                    <p id="info-message">This functionality is not all done yet, but it's working for now.</p>
                    <ol type="1" id="info-list">
                        <li>If you select a file, the file will be uploaded and selected.</li>
                        <li>If you dont select a file, but a previous avatar, the previous avatar is selected</li>
                        <li>If you dont select a new or a previous avatar,
                            but you choose to remove your current, the avatar will be removed
                        </li>
                    </ol>
                    <input type="submit" value="Save" id="saveButton">
                </form>

            </section>
        </main>
    </body>
</html>
