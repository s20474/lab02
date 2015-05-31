<?php

header("Content-type: image/png");

$im_final = imagecreatetruecolor(500, 500);

$img_list = ["images/byk.jpg", "images/chomik.jpg", "images/kaczka.jpg", "images/maly mastiff.jpg"];
shuffle($img_list);
/*
foreach($img_list as $image_name){
    $image = imagecreatefromjpeg($image_name);
}
*/
$min_length = 50;
$i = 1;

foreach($img_list as $image_name){
    $max_length = 450;
    $length = rand($min_length, $max_length);
    $min_length = ($min_length < (500 - $length)) ? 500 -$length : $min_length;

    $image = imagecreatefromjpeg($image_name);
    $image = imagescale($image, $length);

    switch($i){
        case 1:
            imagecopymerge($im_final, $image, 0, 0, 0, 0, $length, $length, 50);
            break;
        case 2:
            imagecopymerge($im_final, $image, 500 - $length, 0, 0, 0, $length, $length, 50);
            break;
        case 3:
            imagecopymerge($im_final, $image, 0, 500 - $length, 0, 0, $length, $length, 50);
            break;
        case 4:
            imagecopymerge($im_final, $image, 500 - $length, 500 - $length, 0, 0, $length, $length, 50);
            break;
        default;
    }
    $i++;
}

$stamp = imagecreatetruecolor(125, 50);
imagefilledrectangle($stamp, 0, 0, 124, 49, 0xFF0000);
imagestring($stamp, 5, 10, 15, 'wsnhid 20474', 0xFFFFFF);

imagecopymerge($im_final, $stamp, 350, 400, 0, 0, 125, 50, 20);

imagepng($im_final);
imagedestroy($im_final);