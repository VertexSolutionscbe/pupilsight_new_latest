<?php
set_time_limit(0);
function getDomain()
{
    if (isset($_SERVER['HTTPS'])) {
        $protocol = ($_SERVER['HTTPS'] && $_SERVER['HTTPS'] != "off") ? "https" : "http";
    } else {
        $protocol = 'http';
    }
    //return $protocol . "://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
    return $protocol . "://" . $_SERVER['HTTP_HOST'];
}
//$baseurl = getDomain().'/pupilsight';
$baseurl = getDomain();

function getPhyLink($weblink, $baseurl)
{
    $flink = str_replace($baseurl, "", $weblink);
    $filelink =  $_SERVER['DOCUMENT_ROOT'] . $flink;
    return $filelink;
}

//$link = "https://sjbhs.pupilpod.net/wp/wp-content/uploads/fluentform/ff-0f4e2beaeb75e182fbfaa317098b1cd5-ff-Collin-Xth-pre-board-marks.pdf";


//echo getPhyLink($link, $baseurl);
//die();
include $_SERVER['DOCUMENT_ROOT'] . '/pupilsight.php';
require_once($_SERVER['DOCUMENT_ROOT'] . '/pdflib.php');

$pdflib = new PDFLib();

//$formid = $_GET['formid'];
$attchmentField = "file-upload";
$attchmentField1 = "cbse_pre_board_reportcard";
$attchmentField2 = "Icse_pre_board_reportcard";

$cid = 19;
$formid = 21;
$photo = FALSE;

error_reporting(E_ALL);


//get pdf template
/*
$sqlpt = "SELECT template_path, template_filename FROM campaign WHERE id = '" . $cid . "' ";
//echo $sqlpt;
$result = $connection2->query($sqlpt);
$valuept = $result->fetch();

//print_r($valuept);

$file = $valuept['template_path'];
*/

$file = "/var/www/sjbhs/public/thirdparty/pdfgenerate/bulk/bulk_template.pdf";
$sqlf = "SELECT id FROM wp_fluentform_submissions where form_id='" . $formid . "' and date(created_at)>'2021-04-28' ";
$resultf = $connection2->query($sqlf);
$applications = $resultf->fetchAll();

// echo '<pre>';
// print_r($arrHeader);
// echo '</pre>';
// die();

if (!empty($file)) {
    try {
        $len = count($applications);
        $i = 0;
        chmod($file, 0777);
        $zip = new ZipArchive();
        $zipFileName = "admission_bulk_left.zip";
        if ($zip->open($zipFileName, ZipArchive::CREATE) !== TRUE) {
            exit("cannot open <$zipFileName>\n");
        }
        //$len = 2;
        while ($i < $len) {
            $aid = $applications[$i]["id"];

            $formData = array();
            $files = array();

            try {

                $sqla = "select application_id, created_at, pupilsightYearGroupID FROM wp_fluentform_submissions  where id = " . $aid . " ";
                $resulta = $connection2->query($sqla);
                $applicationData = $resulta->fetch();

                $classID = $applicationData['pupilsightYearGroupID'];
                $className = '';
                if (!empty($classID)) {
                    $sqlc = "select name FROM pupilsightYearGroup  where pupilsightYearGroupID = '" . $classID . "' ";
                    $resultc = $connection2->query($sqlc);
                    $clsdata = $resultc->fetch();

                    $className = $clsdata['name'];
                    //echo $sqlc;
                }

                $sql = "select field_name, field_value FROM wp_fluentform_entry_details  where submission_id = " . $aid . " ";
                $results = $connection2->query($sql);
                $rowdata = $results->fetchAll();
                $k = 0;
                $imgData = array();
                $attachFilePath = "";
                $leftattachFilePath = "";

                foreach ($rowdata as $key => $value) {
                    try {
                        if ($value['field_name'] == $attchmentField) {
                            $attachFilePath = $value['field_value'];
                        }
                        if (empty($attachFilePath)) {
                            if ($value['field_name'] == $attchmentField1) {
                                $attachFilePath = $value['field_value'];
                            }
                            if (empty($attachFilePath)) {
                                if ($value['field_name'] == $attchmentField2) {
                                    $attachFilePath = $value['field_value'];
                                }
                            }
                        }

                        if ($photo) {
                            if ($value['field_name'] == 'student_photo') {
                                $sql = 'SELECT * FROM campaign_configure_image_template WHERE campaign_id = "' . $cid . '" AND field_name = "student_photo" AND template_type = "Online" ';
                                $result = $connection2->query($sql);
                                $imgStuData = $result->fetch();
                                if (!empty($imgStuData)) {
                                    $imgData[$k]['pageno'] = $imgStuData['page_no'];
                                    $imgData[$k]['src'] = $value['field_value'];
                                    $imgData[$k]['x'] = $imgStuData['x'];
                                    $imgData[$k]['y'] = $imgStuData['y'];
                                    $imgData[$k]['width'] = $imgStuData['width'];
                                    $imgData[$k]['height'] = $imgStuData['height'];
                                }
                                $k++;
                            } else if ($value['field_name'] == 'father_photo') {
                                $sql = 'SELECT * FROM campaign_configure_image_template WHERE campaign_id = "' . $cid . '" AND field_name = "father_photo" AND template_type = "Online" ';
                                $result = $connection2->query($sql);
                                $imgStuData = $result->fetch();
                                if (!empty($imgStuData)) {
                                    $imgData[$k]['pageno'] = $imgStuData['page_no'];
                                    $imgData[$k]['src'] = $value['field_value'];
                                    $imgData[$k]['x'] = $imgStuData['x'];
                                    $imgData[$k]['y'] = $imgStuData['y'];
                                    $imgData[$k]['width'] = $imgStuData['width'];
                                    $imgData[$k]['height'] = $imgStuData['height'];
                                }
                                $k++;
                            } else if ($value['field_name'] == 'mother_photo') {
                                $sql = 'SELECT * FROM campaign_configure_image_template WHERE campaign_id = "' . $cid . '" AND field_name = "mother_photo" AND template_type = "Online" ';
                                $result = $connection2->query($sql);
                                $imgStuData = $result->fetch();
                                if (!empty($imgStuData)) {
                                    $imgData[$k]['pageno'] = $imgStuData['page_no'];
                                    $imgData[$k]['src'] = $value['field_value'];
                                    $imgData[$k]['x'] = $imgStuData['x'];
                                    $imgData[$k]['y'] = $imgStuData['y'];
                                    $imgData[$k]['width'] = $imgStuData['width'];
                                    $imgData[$k]['height'] = $imgStuData['height'];
                                }
                                $k++;
                            } else if ($value['field_name'] == 'guardian_photo') {
                                $sql = 'SELECT * FROM campaign_configure_image_template WHERE campaign_id = "' . $cid . '" AND field_name = "guardian_photo" AND template_type = "Online" ';
                                $result = $connection2->query($sql);
                                $imgStuData = $result->fetch();
                                if (!empty($imgStuData)) {
                                    $imgData[$k]['pageno'] = $imgStuData['page_no'];
                                    $imgData[$k]['src'] = $value['field_value'];
                                    $imgData[$k]['x'] = $imgStuData['x'];
                                    $imgData[$k]['y'] = $imgStuData['y'];
                                    $imgData[$k]['width'] = $imgStuData['width'];
                                    $imgData[$k]['height'] = $imgStuData['height'];
                                }
                                $k++;
                            } else {
                                try {
                                    $formData[$value['field_name']] = $value['field_value'];
                                } catch (Exception $ex) {
                                    echo "<br>Filed Value " . $ex->getMessage();
                                }
                            }
                        } else {
                            try {
                                $formData[$value['field_name']] = $value['field_value'];
                            } catch (Exception $ex) {
                                echo "<br>Filed Value " . $ex->getMessage();
                            }
                        }
                    } catch (Exception $ex) {
                        echo "<br>Form Field Value " . $ex->getMessage();
                    }
                }

                if (!empty($applicationData['application_id'])) {
                    $fname = $applicationData['application_id'];
                } else {
                    $fname = $aid;
                }

                $date = date('d-m-Y', strtotime($applicationData['created_at']));
                $formData['application_no'] = $fname;
                $formData['application_date'] = $date;

                $fname = trim(str_replace("/", "_", $fname));

                //$templateFileName = $file;
                //echo "Template File Name : " . $templateFileName;

                $outFileName = $_SERVER['DOCUMENT_ROOT'] . '/thirdparty/pdfgenerate/bulk/' . $fname . '.pdf';
                $pdflib->generate($templateFileName, $outFileName, $formData, $imgData, TRUE);
                //$pdflib->download();
                $zip->addFile($outFileName, $fname . '.pdf');
                if ($attachFilePath) {
                    $afp = getPhyLink($attachFilePath, $baseurl);
                    if (file_exists($afp)) {
                        $zip->addFile($afp, $fname . '_attachment.pdf');
                    } else {
                        echo "<br>File not found" . $afp;
                    }
                }
                /*
                if ($leftattachFilePath) {
                    $afp = getPhyLink($leftattachFilePath, $baseurl);
                    echo "<br>filepath : " . $afp;
                    if (file_exists($afp)) {
                        $zip->addFile($afp, $fname . '_attachment.pdf');
                    } else {
                        echo "<br>File not found" . $afp;
                    }
                }*/
            } catch (Exception $ex) {
                echo "Internal Error" . $ex->getMessage();
            }
            /*if ($i > 0) {
                break;
            }*/
            $i++;
        }

        $zip->close();
        /*
        header("Content-type: application/zip");
        header("Content-Disposition: attachment; filename = $zipFileName");
        header("Pragma: no-cache");
        header("Expires: 0");
        readfile($zipFileName);
        */
        //unlink($_SERVER['DOCUMENT_ROOT'] . "/" . $zipFileName);

        $pdflib->deleteSource();
    } catch (Exception $ex) {
        echo "Global Exception: " . $ex->getMessage();
    }
} else {
    echo "File Empty";
}