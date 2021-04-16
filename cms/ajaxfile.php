<?php
include_once '../vendor/autoload.php';
include_once 'w2f/adminLib.php';
include $_SERVER["DOCUMENT_ROOT"] . '/pdf_convert.php';
$adminlib = new adminlib();
session_start();

$cid = $_GET['cid'];
$submissionId = $_GET['submissionId'];
//$cid = 9;
//$submissionId = 94;
error_reporting(E_ALL);
$applicantId = explode(',', $submissionId);

$sqlpt = "SELECT template_path, template_filename FROM campaign WHERE id = '" . $cid . "' ";
//echo $sqlpt;
$valuept = database::doSelectOne($sqlpt);

//print_r($valuept);

$file = $valuept['template_path'];

$sqlf = "Select a.academic_id, b.form_fields FROM campaign AS a LEFT JOIN wp_fluentform_forms AS b ON a.form_id = b.id WHERE a.id = ' " . $cid . " ' ";

$fluent = database::doSelectOne($sqlf);
$string = $fluent['form_fields'];
$academic_id = $fluent['academic_id'];
try {
    $string = preg_replace("/[\r\n]+/", " ", $string);
    $json = utf8_encode($string);
    $field = json_decode($json);
} catch (Exception $ex) {
    print_r($ex);
}

$fields = array();
$arrHeader = array();
foreach ($field as $fe) {
    foreach ($fe as $f) {
        if (!empty($f->attributes)) {
            $arrHeader[] = $f->attributes->name;
        }
        if (!empty($f->columns)) {
            foreach ($f->columns as $cf) {
                foreach ($cf as $cff) {
                    foreach ($cff as $ctf) {
                        if (!empty($ctf->attributes)) {
                            $arrHeader[] = $ctf->attributes->name;
                        }
                    }
                }
            }
        }
    }
}

if (!empty($file)) {
    $arr = array();
    $files = array();
    foreach ($applicantId as $aid) {
        try {
            //print_r($file);
            chmod($file, 0777);
            $phpword = new \PhpOffice\PhpWord\TemplateProcessor($file);

            $sqla = "select application_id, created_at, pupilsightYearGroupID FROM wp_fluentform_submissions  where id = " . $aid . " ";
            $applicationData = database::doSelectOne($sqla);


            $classID = $applicationData['pupilsightYearGroupID'];
            $className = '';
            if (!empty($classID)) {
                $sqlc = "select name FROM pupilsightYearGroup  where pupilsightYearGroupID = '" . $classID . "' ";
                $clsdata = database::doSelectOne($sqlc);
                $className = $clsdata['name'];
                //echo $sqlc;
            }

            $sql = "select field_name, field_value FROM wp_fluentform_entry_details  where submission_id = " . $aid . " ";
            $rowdata = database::doSelect($sql);

            foreach ($rowdata as $key => $value) {
                try {
                    $arr[$value['field_name']] = $value['field_value'];
                } catch (Exception $ex) {
                }
            }

            foreach ($arrHeader as $k => $ah) {
                if (array_key_exists($ah, $arr)) {
                    if ($ah == 'file-upload' || $ah == 'image_upload') {
                        $attrValue = $arr[$ah];
                        try {
                            $imgVal = array("path" => $attrValue, "width" => 100, "height" => 100);
                            $phpword->setImageValue($ah, $imgVal);
                            //$phpword->setImageValue($ah, $attrValue);
                        } catch (Exception $ex) {
                        }
                    } else {
                        try {
                            $pv = htmlspecialchars($arr[$ah]);
                            $phpword->setValue($ah, $pv);
                        } catch (Exception $ex) {
                        }
                    }
                } else {
                    try {
                        $phpword->setValue($ah, '');
                    } catch (Exception $ex) {
                    }
                }
            }

            try {
                if (!empty($className)) {
                    $phpword->setValue('class_section', htmlspecialchars($className));
                }
            } catch (Exception $ex) {
                //print_r($ex);
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

            $date = date('d-m-Y', strtotime($applicationData['created_at']));
            $phpword->setValue('application_no', $fname);
            $phpword->setValue('application_date', $date);

            $fname = trim(str_replace("/", "_", $fname));

            $savedocsx = $_SERVER["DOCUMENT_ROOT"] . "/public/applicationpdf/parent/" . $fname . ".docx";
            echo "docx path : " . $savedocsx;
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
            echo "pdfFilename: " . $pdfFilename;

            header("Content-Disposition: attachment; filename=" . $fname . ".pdf");
            readfile($pdfFilename);
            unlink($savedocsx);
        } catch (Exception $ex) {
        }
    }
} else {
    echo "File Empty";
}
