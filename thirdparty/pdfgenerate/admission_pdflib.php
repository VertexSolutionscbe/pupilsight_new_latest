<?php

include $_SERVER["DOCUMENT_ROOT"] . "/pupilsight.php";
require_once $_SERVER["DOCUMENT_ROOT"] . "/pdflib.php";

$pdflib = new PDFLib();

$cid = $_GET["cid"];
$submissionId = $_GET["submissionId"];
//$cid = 9;
//$submissionId = 94;
error_reporting(E_ALL);
$applicantId = explode(",", $submissionId);

$sqlpt =
    "SELECT template_path, template_filename FROM campaign WHERE id = '" .
    $cid .
    "' ";
//echo $sqlpt;
$result = $connection2->query($sqlpt);
$valuept = $result->fetch();

//print_r($valuept);

$file = $valuept["template_path"];

$sqlf =
    "Select a.academic_id, b.form_fields FROM campaign AS a LEFT JOIN wp_fluentform_forms AS b ON a.form_id = b.id WHERE a.id = ' " .
    $cid .
    " ' ";
$resultf = $connection2->query($sqlf);
$fluent = $resultf->fetch();

$string = $fluent["form_fields"];
$academic_id = $fluent["academic_id"];
try {
    $string = preg_replace("/[\r\n]+/", " ", $string);
    $json = utf8_encode($string);
    $field = json_decode($json);
} catch (Exception $ex) {
    print_r($ex);
}

$fields = [];
$arrHeader = [];
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

// echo '<pre>';
// print_r($arrHeader);
// echo '</pre>';
// die();

if (!empty($file)) {
    $formData = [];
    $files = [];
    foreach ($applicantId as $aid) {
        try {
            //print_r($file);
            chmod($file, 0777);

            $sqla =
                "select application_id, created_at, pupilsightYearGroupID, pupilsightProgramID FROM wp_fluentform_submissions  where id = " .
                $aid .
                " ";
            $resulta = $connection2->query($sqla);
            $applicationData = $resulta->fetch();

            $pupilsightProgramID = $applicationData["pupilsightProgramID"];
            $classID = $applicationData["pupilsightYearGroupID"];
            $className = "";
            if (!empty($classID)) {
                $sqlc =
                    "select name FROM pupilsightYearGroup  where pupilsightYearGroupID = '" .
                    $classID .
                    "' ";
                $resultc = $connection2->query($sqlc);
                $clsdata = $resultc->fetch();

                $className = $clsdata["name"];
                //echo $sqlc;
            }

            $progName = "";
            if (!empty($pupilsightProgramID)) {
                $sqlp =
                    "select name FROM pupilsightProgram  where pupilsightProgramID = '" .
                    $pupilsightProgramID .
                    "' ";
                $resultp = $connection2->query($sqlp);
                $progdata = $resultp->fetch();

                $progName = $progdata["name"];
                //echo $sqlc;
            }

            $sql =
                "select field_name, field_value FROM wp_fluentform_entry_details  where submission_id = " .
                $aid .
                " ";
            $results = $connection2->query($sql);
            $rowdata = $results->fetchAll();
            $k = 0;
            $imgData = [];
            foreach ($rowdata as $key => $value) {
                try {
                    if ($value["field_name"] == "student_photo") {
                        $sql =
                            'SELECT * FROM campaign_configure_image_template WHERE campaign_id = "' .
                            $cid .
                            '" AND field_name = "student_photo" AND template_type = "Online" ';
                        $result = $connection2->query($sql);
                        $imgStuData = $result->fetch();
                        if (!empty($imgStuData)) {
                            $imgData[$k]["pageno"] = $imgStuData["page_no"];
                            $imgData[$k]["src"] = $value["field_value"];
                            $imgData[$k]["x"] = $imgStuData["x"];
                            $imgData[$k]["y"] = $imgStuData["y"];
                            $imgData[$k]["width"] = $imgStuData["width"];
                            $imgData[$k]["height"] = $imgStuData["height"];
                        }
                        $k++;
                    } elseif ($value["field_name"] == "father_photo") {
                        $sql =
                            'SELECT * FROM campaign_configure_image_template WHERE campaign_id = "' .
                            $cid .
                            '" AND field_name = "father_photo" AND template_type = "Online" ';
                        $result = $connection2->query($sql);
                        $imgStuData = $result->fetch();
                        if (!empty($imgStuData)) {
                            $imgData[$k]["pageno"] = $imgStuData["page_no"];
                            $imgData[$k]["src"] = $value["field_value"];
                            $imgData[$k]["x"] = $imgStuData["x"];
                            $imgData[$k]["y"] = $imgStuData["y"];
                            $imgData[$k]["width"] = $imgStuData["width"];
                            $imgData[$k]["height"] = $imgStuData["height"];
                        }
                        $k++;
                    } elseif ($value["field_name"] == "mother_photo") {
                        $sql =
                            'SELECT * FROM campaign_configure_image_template WHERE campaign_id = "' .
                            $cid .
                            '" AND field_name = "mother_photo" AND template_type = "Online" ';
                        $result = $connection2->query($sql);
                        $imgStuData = $result->fetch();
                        if (!empty($imgStuData)) {
                            $imgData[$k]["pageno"] = $imgStuData["page_no"];
                            $imgData[$k]["src"] = $value["field_value"];
                            $imgData[$k]["x"] = $imgStuData["x"];
                            $imgData[$k]["y"] = $imgStuData["y"];
                            $imgData[$k]["width"] = $imgStuData["width"];
                            $imgData[$k]["height"] = $imgStuData["height"];
                        }
                        $k++;
                    } elseif ($value["field_name"] == "guardian_photo") {
                        $sql =
                            'SELECT * FROM campaign_configure_image_template WHERE campaign_id = "' .
                            $cid .
                            '" AND field_name = "guardian_photo" AND template_type = "Online" ';
                        $result = $connection2->query($sql);
                        $imgStuData = $result->fetch();
                        if (!empty($imgStuData)) {
                            $imgData[$k]["pageno"] = $imgStuData["page_no"];
                            $imgData[$k]["src"] = $value["field_value"];
                            $imgData[$k]["x"] = $imgStuData["x"];
                            $imgData[$k]["y"] = $imgStuData["y"];
                            $imgData[$k]["width"] = $imgStuData["width"];
                            $imgData[$k]["height"] = $imgStuData["height"];
                        }
                        $k++;
                    } else {
                        try {
                            $formData[$value["field_name"]] =
                                $value["field_value"];
                        } catch (Exception $ex) {
                        }
                    }
                } catch (Exception $ex) {
                }
            }

            if (!empty($applicationData["application_id"])) {
                $fname = $applicationData["application_id"];
            } else {
                $fname = $aid;
            }

            $date = date("d-m-Y", strtotime($applicationData["created_at"]));
            $formData["application_no"] = $fname;
            $formData["application_date"] = $date;

            $formData["program_name"] = $progName;
            $formData["class_name"] = $className;

            $fname = trim(str_replace("/", "_", $fname));

            $templateFileName = $file;
            $outFileName =
                $_SERVER["DOCUMENT_ROOT"] .
                "/thirdparty/pdfgenerate/files/" .
                $fname .
                ".pdf";
            $pdflib->generate(
                $templateFileName,
                $outFileName,
                $formData,
                $imgData,
                true
            );
            $pdflib->download();

            // echo '<pre>';
            // print_r($imgData);
            // echo '</pre>';
            // die();
        } catch (Exception $ex) {
        }
    }
} else {
    echo "File Empty";
}
