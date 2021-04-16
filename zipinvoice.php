<?php
function createZipAndDownload($files, $filesPath, $zipFileName)
{
    // Create instance of ZipArchive. and open the zip folder.
    $zip = new \ZipArchive();
    if ($zip->open($zipFileName, \ZipArchive::CREATE) !== TRUE) {
        exit("cannot open <$zipFileName>\n");
    }

    // Adding every attachments files into the ZIP.
    foreach ($files as $file) {
        $zip->addFile($filesPath . $file, $file);
    }
    $zip->close();

    // Download the created zip file
    header("Content-type: application/zip");
    header("Content-Disposition: attachment; filename = $zipFileName");
    header("Pragma: no-cache");
    header("Expires: 0");
    readfile("$zipFileName");
    unlink($zipFileName);

    $folder_path = $filesPath;
    $files = glob($folder_path . '/*');
    foreach ($files as $file) {
        if (is_file($file))
            unlink($file);
    }
    exit;
}

$filesPath = $_SERVER["DOCUMENT_ROOT"] . "/public/invoice_receipts/";
$files = array_diff(scandir($filesPath), array('..', '.'));


// Name of creating zip file
$zipName = $_GET['zipname'] . '.zip';
if (!empty($files)) {
    echo createZipAndDownload($files, $filesPath, $zipName);
}
