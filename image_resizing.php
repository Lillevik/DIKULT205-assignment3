<?php
/**
 * Created by PhpStorm.
 * User: goat
 * Date: 18/01/17
 * Time: 10:26
 */

include 'simpleImage.php';

function resize_image_to_400($temp, $target_dir, $cropped_name){
    $image = new SimpleImage();
    $image->load($temp);
    $width = $image->getWidth();
    $height = $image->getHeight();
    if($image->getWidth() > 450) {
        $image->resizeToWidth(450);
        $image->save($target_dir . $cropped_name);
        return [true];
    }
    else if($width >= 300 and $height >= 200){
        $image->save($target_dir . $cropped_name);
        return [true];
    }
    else if($width < 300 OR $height < 200){
        return [false, "Image must be larger than 300px in width and 200px in height. Your image is " .$image->getWidth()."px x " . $image->getHeight()."px."];
    }
}

function resize_image_width($temp, $target_dir, $resized_name, $maxWidth, $minWidth){
    $image = new SimpleImage();
    $image->load($temp);
    $width = $image->getWidth();
    if($width < $minWidth){
        return [false, "Image must be larger than 80px in width and 80px in height. Your image is " .$image->getWidth()."px x " . $image->getHeight()."px."];
    }else if($width > $maxWidth){
        $image->resizeToWidth($maxWidth);
        $image->save($target_dir . $resized_name);
        return [true];
    }
}



