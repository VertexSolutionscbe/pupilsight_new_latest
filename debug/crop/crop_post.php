<?php
if (isset($_POST["porpin"])) {
    $len = count($_POST["porpin"]);
    $i = 0;
    while ($i < $len) {
        $rawimg = $_POST["porpin"][$i];
        $fileName = $_SERVER['DOCUMENT_ROOT'] . "/debug/crop/photo/category_" . rand(1000, 1000000) . ".jpg";
        addBase64JPG($rawimg, $fileName);
        $i++;
    }
}
//file name with full path ext .jpg
function addBase64JPG($rawdata, $fileName)
{
    try {
        $data       = str_replace('data:image/jpeg;base64,', '', $rawdata);
        $data       = str_replace('data:image/jpg;base64,', '', $data);
        $data       = str_replace(' ', '+', $data);
        $data       = base64_decode($data);
        $source_img = imagecreatefromstring($data);
        imagejpeg($source_img, $fileName, 90); //for more quality increase value 90 to 100
        imagedestroy($source_img);
    } catch (Exception $e) {
        print_r($e->getMessage());
    }
}
