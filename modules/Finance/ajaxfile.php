<?php
include '../../pupilsight.php';

function createZipAndDownload($files, $filesPath, $zipFileName)
{
    // Create instance of ZipArchive. and open the zip folder.
    $zip = new ZipArchive();
    if ($zip->open($zipFileName, ZipArchive::CREATE) !== TRUE) {
        exit("cannot open <$zipFileName>\n");
    }

    // Adding every attachments files into the ZIP.
    foreach ($files as $file) {
        $zip->addFile($filesPath . $file, $file);
    }
    $zip->close();
    

    // Download the created zip file
    header("Content-type: application/zip");
    header('Content-Disposition: attachment; filename = "'.$zipFileName.'"');
    header("Pragma: no-cache");
    header("Expires: 0");
    readfile($zipFileName);
    unlink($zipFileName);
    exit;
}

$collectionId = $_GET['id'];
// die();
$sq = "select GROUP_CONCAT(filename) as tid  FROM fn_fees_collection  where id IN (".$collectionId.") ";
$result = $connection2->query($sq);
$rowdata = $result->fetch();

// echo $sq1 = "select GROUP_CONCAT(a.receipt_number,'-',b.officialName SEPARATOR ',') AS zipname  FROM fn_fees_collection AS a LEFT JOIN pupilsightPerson AS b ON a.pupilsightPersonID = b.pupilsightPersonID where a.id IN (".$collectionId.") LIMIT 0,3";
// $result1 = $connection2->query($sq1);
// $rowdata1 = $result1->fetch();

// echo $zipnames = $rowdata1['zipname'];

$fname = str_replace(",",".pdf,",$rowdata['tid']).'.pdf';
$files = explode(',', $fname);

// echo '<pre>';
// print_r($files);
// echo '</pre>';

// Files which need to be added into zip

// Directory of files
$filesPath = $_SERVER["DOCUMENT_ROOT"]."/public/receipts/";

// Name of creating zip file
$zipName = 'Receipts.zip';
echo createZipAndDownload($files, $filesPath, $zipName);