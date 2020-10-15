<?php
include '../../pupilsight.php';

include $_SERVER["DOCUMENT_ROOT"] . '/pdf_convert.php';

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
    header('Content-Disposition: attachment; filename = "' . $zipFileName . '"');
    header("Pragma: no-cache");
    header("Expires: 0");
    readfile($zipFileName);
    unlink($zipFileName);

    foreach ($files as $file) {
        unlink($_SERVER["DOCUMENT_ROOT"] . "/public/applicationpdf/" . $file);
    }
    exit;
}
$cid = $_GET['cid'];
$submissionId = $_GET['id'];
$applicantId = explode(',', $submissionId);

$sqlpt = "SELECT template_path, template_filename FROM campaign WHERE id = " . $cid . " ";
$resultpt = $connection2->query($sqlpt);
$valuept = $resultpt->fetch();
$file = $valuept['template_path'];

$sqlf = 'Select b.form_fields FROM campaign AS a LEFT JOIN wp_fluentform_forms AS b ON a.form_id = b.id WHERE a.id = ' . $cid . ' ';
$resultvalf = $connection2->query($sqlf);
$fluent = $resultvalf->fetch();
$field = json_decode($fluent['form_fields']);
$fields = array();

$arrHeader = array();
foreach ($field as $fe) {
    foreach ($fe as $f) {
        if (!empty($f->attributes)) {
            $arrHeader[] = $f->attributes->name;
        }
    }
}

if (!empty($file)) {
    $arr = array();
    $files = array();
    foreach ($applicantId as $aid) {
        $phpword = new \PhpOffice\PhpWord\TemplateProcessor($file);

        $sqla = "select application_id FROM wp_fluentform_submissions  where id = " . $aid . " ";
        $resulta = $connection2->query($sqla);
        $applicationData = $resulta->fetch();

        $sql = "select field_name, field_value FROM wp_fluentform_entry_details  where submission_id = " . $aid . " ";
        $result = $connection2->query($sql);
        $rowdata = $result->fetchAll();
        foreach ($rowdata as $key => $value) {
            $arr[$value['field_name']] = $value['field_value'];
        }

        foreach ($arrHeader as $k => $ah) {
            if (array_key_exists($ah, $arr)) {
                if ($ah == 'file-upload' || $ah == 'image-upload') {
                    $attrValue = $arr[$ah];
                    $phpword->setImageValue($ah, $attrValue);
                } else {
                    $phpword->setValue($ah, $arr[$ah]);
                }
            } else {
                $phpword->setValue($ah, '');
            }
        }
        // echo '<pre>';
        // print_r($newarr);
        // echo '</pre>';
        // die();
        if (!empty($applicationData['application_id'])) {
            $fname = $applicationData['application_id'];
        } else {
            $fname = $aid;
        }

        $savedocsx = $_SERVER["DOCUMENT_ROOT"] . "/public/applicationpdf/" . $fname . ".docx";
        $files_word[] = $fname . ".docx";
        $files[] = $fname . ".pdf";
        $phpword->saveAs($savedocsx);
    }

    // echo '<pre>';
    //         print_r($files);
    //         echo '</pre>';
    //         die();

    // Files which need to be added into zip

    // Directory of files
    $filesPath = $_SERVER["DOCUMENT_ROOT"] . "/public/applicationpdf/";

    // Name of creating zip file
    $zipName = 'ApplicationForm.zip';

    convertBulk($filesPath, TRUE);
    //echo convert("291.docx", $filesPath, $filesPath, TRUE, TRUE);

    echo createZipAndDownload($files, $filesPath, $zipName);
}
