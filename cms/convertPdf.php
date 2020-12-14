<?php
include_once '../vendor/autoload.php';
include_once 'w2f/adminLib.php';
include '../pupilsight.php';
include $_SERVER["DOCUMENT_ROOT"] . '/pdf_convert.php';
$adminlib = new adminlib();


$id = $_GET['id'];


$fileName = $id . ".docx";
$dirPath = $_SERVER["DOCUMENT_ROOT"] . "/public/receipts/";


if (file_exists($dirPath . $fileName)) {
    convert($fileName, $dirPath, $dirPath, FALSE, TRUE);
} else {
    //echo "file not fund.";
}

$pdfFilename = $_SERVER["DOCUMENT_ROOT"] . "/public/receipts/" . $id . ".pdf";

header("Content-Disposition: attachment; filename=" . $id . ".pdf");
readfile($pdfFilename);
unlink($savedocsx);
  