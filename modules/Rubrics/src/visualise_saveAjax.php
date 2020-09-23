<?php
/*
Pupilsight, Flexible & Open School System
 */

require_once "../../../pupilsight.php";

$fileUploader = new Pupilsight\FileUploader($pdo, $pupilsight->session);
$uploadsFolder = $fileUploader->getUploadsFolderByDate();

$img = (!empty($_POST['img'])) ? $_POST['img'] : null;
$pupilsightPersonID = (!empty($_POST['pupilsightPersonID'])) ? str_pad($_POST['pupilsightPersonID'], 10, '0', STR_PAD_LEFT) : null;
$uploadsFolder = (!empty($_POST['path'])) ? $_POST['path'] : $uploadsFolder;

list($type, $img) = explode(';', $img);
list(, $img)      = explode(',', $img);
$img = base64_decode($img);

$destinationFolder = $pupilsight->session->get('absolutePath').'/'.$uploadsFolder;

if (is_dir($destinationFolder) == false) {
    mkdir($destinationFolder, 0755, true);
}

$fp = fopen($destinationFolder.'/rubric_visualisation_'.$pupilsightPersonID.'.png', 'w');
fwrite($fp, $img);
fclose($fp);
