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

function resize_image_width($temp, $target_dir, $resized_name, $maxWidth, $minWidth, $newHeight = 80){
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

/**
 * This function resisizes and crops images used
 * for the avatar page.
 *
 * @param $max_width
 * @param $max_height
 * @param $source_file
 * @param $dst_dir
 * @param int $quality
 * @param int $minWidth
 * @param int $minHeight
 * @return array
 */
function resize_crop_image($max_width, $max_height, $source_file, $dst_dir, $quality = 80, $minWidth = 80, $minHeight = 80){
    $imgsize = getimagesize($source_file);
    $width = $imgsize[0];
    $height = $imgsize[1];
    $mime = $imgsize['mime'];
    if($width < $minWidth or $height < $minWidth) {
        return [false, "Image must be larger than " . $minWidth . "px in width and " . $minHeight . "px in height. Your image is " . $width . "px x " . $height . "px."];
    }else {
        switch ($mime) {
            case 'image/gif':
                $image_create = "imagecreatefromgif";
                $image = "imagegif";
                break;

            case 'image/png':
                $image_create = "imagecreatefrompng";
                $image = "imagepng";
                $quality = 7;
                break;

            case 'image/jpeg':
                $image_create = "imagecreatefromjpeg";
                $image = "imagejpeg";
                $quality = 80;
                break;

            default:
                return [false, "File is not an image"];
                break;
        }

        $dst_img = imagecreatetruecolor($max_width, $max_height);
        $src_img = $image_create($source_file);

        $width_new = $height * $max_width / $max_height;
        $height_new = $width * $max_height / $max_width;
        //if the new width is greater than the actual width of the image, then the height is too large and the rest cut off, or vice versa
        if ($width_new > $width) {
            //cut point by height
            $h_point = (($height - $height_new) / 2);
            //copy image
            imagecopyresampled($dst_img, $src_img, 0, 0, 0, $h_point, $max_width, $max_height, $width, $height_new);
        } else {
            //cut point by width
            $w_point = (($width - $width_new) / 2);
            imagecopyresampled($dst_img, $src_img, 0, 0, $w_point, 0, $max_width, $max_height, $width_new, $height);
        }

        $image($dst_img, $dst_dir, $quality);

        if ($dst_img) imagedestroy($dst_img);
        if ($src_img) imagedestroy($src_img);
        return [true];
    }
}




