<?php
/**
 * Created by PhpStorm.
 * User: goat
 * Date: 18/01/17
 * Time: 10:26
 */

include 'simpleImage.php';

function resize_image($temp, $target_dir, $cropped_name){
    $image = new SimpleImage();
    $image->load($temp);
    $width = $image->getWidth();
    $height = $image->getHeight();
    if($image->getWidth() > 450) {
        $image->resizeToWidth(450);
        $image->save($target_dir . $cropped_name);
        return [true];
    }
    else if($width > 300 and $width < 450){
        $image->save($target_dir . $cropped_name);
        return [true];
    }
    else if($width < 300 OR $height < 200){
        return [false, "<p class='error-message'>Image must be larger than 300px in width and 200px in height. Your image is " .$image->getWidth()."px x " . $image->getHeight()."px.</p>"];
    }
}



