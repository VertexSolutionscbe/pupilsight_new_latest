<?php
$cid = $_GET['cid'];
$submissionId = $_GET['submissionId'];

header("Content-Disposition: attachment; filename=test.pdf");
$link = "http://christacademy.pupilpod.net/cms/ajaxfile.php?cid=" . $cid . "&submissionId=" . $submissionId;
readfile($link);
die();

include '../../pupilsight.php';

include $_SERVER["DOCUMENT_ROOT"] . '/pdf_convert.php';

function createSingleDownload($files, $filesPath)
{
    try {
        foreach ($files as $file) {
            if (file_exists($filesPath . $file)) {
                header("Content-Disposition: attachment; filename=" . $file);
                readfile($filesPath . $file);
                unlink($filesPath . $file);
            }
        }
    } catch (Exception $ex) {
        print_r($ex);
    }
}

function createZipAndDownload($files, $filesPath, $zipFileName)
{
    // Create instance of ZipArchive. and open the zip folder.
    $zip = new ZipArchive();
    if ($zip->open($zipFileName, ZipArchive::CREATE) !== TRUE) {
        exit("cannot open <$zipFileName>\n");
    }

    // Adding every attachments files into the ZIP.
    foreach ($files as $file) {
        if (file_exists($filesPath . $file)) {
            $zip->addFile($filesPath . $file, $file);
        }
    }
    $zip->close();


    // Download the created zip file
    header("Content-type: application/zip");
    header('Content-Disposition: attachment; filename = "' . $zipFileName . '"');
    header("Pragma: no-cache");
    header("Expires: 0");
    readfile($zipFileName);
    unlink($zipFileName);
    /*
    foreach ($files as $file) {
        unlink($_SERVER["DOCUMENT_ROOT"] . "/public/applicationpdf/" . $file);
    }*/
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

try {
    $field = json_decode($fluent['form_fields']);
} catch (Exception $ex) {
    $string = preg_replace("/[\r\n]+/", " ", $fluent['form_fields']);
    $json = utf8_encode($string);
    $field = json_decode($json);
}

if (empty($field)) {
    try {
        $string = preg_replace("/[\r\n]+/", " ", $fluent['form_fields']);
        $json = utf8_encode($string);
        $field = json_decode($json);
    } catch (Exception $ex) {
    }
}


$fields = array();

$arrHeader = array();
foreach ($field as $fe) {
    foreach ($fe as $f) {
        if (!empty($f->attributes)) {
            $arrHeader[] = $f->attributes->name;
        }
    }
}

error_reporting(-1);
$debugFlag = TRUE;
if (!empty($file)) {
    $arr = array();
    $files = array();
    foreach ($applicantId as $aid) {
        try {
            chmod($file, 0777);
            $phpword = new \PhpOffice\PhpWord\TemplateProcessor($file);

            $sqla = "select application_id FROM wp_fluentform_submissions  where id = " . $aid . " ";
            $resulta = $connection2->query($sqla);
            $applicationData = $resulta->fetch();

            $sql = "select field_name, field_value FROM wp_fluentform_entry_details  where submission_id = " . $aid . " ";
            $result = $connection2->query($sql);
            $rowdata = $result->fetchAll();

            foreach ($rowdata as $key => $value) {
                try {
                    $arr[$value['field_name']] = $value['field_value'];
                } catch (Exception $ex) {
                }
            }

            foreach ($arrHeader as $k => $ah) {
                if (array_key_exists($ah, $arr)) {
                    $attrValue = $arr[$ah];

                    if ($ah == 'file-upload' || $ah == 'image-upload') {
                        try {
                            $phpword->setImageValue($ah, $attrValue);
                        } catch (Exception $ex) {
                            if ($debugFlag) {
                                echo "setImageValue:";
                                print_r($ex);
                            }
                        }
                    } else {
                        try {
                            $phpword->setValue($ah, $attrValue);
                        } catch (Exception $ex) {
                            if ($debugFlag) {
                                echo "setValue:";
                                print_r($ex);
                            }
                        }
                    }
                } else {
                    try {
                        $phpword->setValue($ah, '');
                    } catch (Exception $ex) {
                        if ($debugFlag) {
                            echo "setValue:Blank:";
                            print_r($ex);
                        }
                    }
                }
            }


            // die();
            if (!empty($applicationData['application_id'])) {
                $fname = $applicationData['application_id'];
            } else {
                $fname = $aid;
            }


            $date = date('Y-m-d');
            $phpword->setValue('application_no', $fname);
            $phpword->setValue('application_date', $date);

            $fname = trim(str_replace("/", "_", $fname));

            $savedocsx = $_SERVER["DOCUMENT_ROOT"] . "/public/applicationpdf/parent/" . $fname . ".docx";
            $phpword->saveAs($savedocsx);

            $fileName = $fname . ".docx";
            $dirPath = $_SERVER["DOCUMENT_ROOT"] . "/public/applicationpdf/parent/";

            if (file_exists($dirPath . $fileName)) {
                //echo "converting pdf.." . $dirPath . $fileName;
                convert($fileName, $dirPath, $dirPath, FALSE, TRUE);
            } else {
                //echo "file not fund.";
            }

            $pdfFilename = $_SERVER["DOCUMENT_ROOT"] . "/public/applicationpdf/parent/" . $fname . ".pdf";

            header("Content-Disposition: attachment; filename=" . $fname . ".pdf");
            readfile($pdfFilename);

            /*
            $date = date('Y-m-d');
            $phpword->setValue('application_no', $fname);
            $phpword->setValue('application_date', $date);

            $fname = trim(str_replace("/", "_", $fname));
            // chmod($_SERVER["DOCUMENT_ROOT"] . "/public/applicationpdf/", 0777);
            $savedocsx = $_SERVER["DOCUMENT_ROOT"] . "/public/applicationpdf/" . $fname . ".docx";
            $phpword->saveAs($savedocsx);
            if ($debugFlag) {
                echo "fileName: " . $savedocsx;
            }

            $files_word[] = $fname . ".docx";
            $files[] = $fname . ".pdf";

            $filesPath = $_SERVER["DOCUMENT_ROOT"] . "/public/applicationpdf/";

            convert($fname . ".docx", $filesPath, $filesPath, FALSE, TRUE);

            $pdfFilename = $_SERVER["DOCUMENT_ROOT"] . "/public/applicationpdf/" . $fname . ".pdf";
            header("Content-Disposition: attachment; filename=" . $fname . ".pdf");
            readfile($pdfFilename);*/
        } catch (Exception $ex) {
            if ($debugFlag) {
                echo "All:";
                print_r($ex);
            }
        }
    }

    // echo '<pre>';
    //         print_r($files);
    //         echo '</pre>';
    //         die();

    // Files which need to be added into zip

    // Directory of files
    //$filesPath = $_SERVER["DOCUMENT_ROOT"] . "/public/applicationpdf/";

    // Name of creating zip file
    //$zipName = 'ApplicationForm.zip';

    //convertBulk($filesPath, FALSE); //convert pdf

    //createSingleDownload($files, $filesPath);
    //echo createZipAndDownload($files, $filesPath, $zipName);
}
