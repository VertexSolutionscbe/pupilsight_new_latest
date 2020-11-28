<?php
include_once '../vendor/autoload.php';
include_once 'w2f/adminLib.php';
include '../pupilsight.php';
include $_SERVER["DOCUMENT_ROOT"] . '/pdf_convert.php';
$adminlib = new adminlib();


$aid = $_GET['aid'];
//$pid = $_GET['pid'];
$sid = $_GET['sid'];

//error_reporting(E_ALL);
$studentId = explode(',', $sid);

$sqlchk = 'SELECT pupilsightProgramID, pupilsightYearGroupID FROM pupilsightStudentEnrolment WHERE pupilsightPersonID = '.$sid.' ';
$pro = database::doSelectOne($sqlchk);

$sqlpt = "SELECT path, filename FROM pupilsightDocTemplate WHERE pupilsightSchoolYearID = ".$aid." AND pupilsightProgramID = ".$pro['pupilsightProgramID']." AND type = 'Conduct Certificate' AND FIND_IN_SET('".$pro['pupilsightYearGroupID']."', classIds) ";

$valuept = database::doSelectOne($sqlpt);

$file = $valuept['path'];



if (!empty($file)) {
    $arr = array();
    $files = array();
    foreach ($studentId as $aid) {
        try {
            //print_r($file);
            chmod($file, 0777);
            $phpword = new \PhpOffice\PhpWord\TemplateProcessor($file);

            $sqla = "SELECT a.*, d.name AS class, e.name AS section,c.name as program ,f.name as academic, b.cdt as created_at, d.pupilsightYearGroupID, e.pupilsightRollGroupID,c.pupilsightProgramID ,f.pupilsightSchoolYearID, parent1.officialName as fatherName, parent1.email as fatherEmail, parent1.phone1 as fatherPhone, parent2.officialName as motherName, parent2.email as motherEmail, parent2.phone1 as motherPhone FROM pupilsightPerson AS a 
            LEFT JOIN pupilsightStudentEnrolment AS b ON a.pupilsightPersonID=b.pupilsightPersonID 
            LEFT JOIN pupilsightProgram AS c ON b.pupilsightProgramID=c.pupilsightProgramID 
            LEFT JOIN pupilsightYearGroup AS d ON b.pupilsightYearGroupID=d.pupilsightYearGroupID 
            LEFT JOIN pupilsightRollGroup AS e ON b.pupilsightRollGroupID=e.pupilsightRollGroupID 
            LEFT JOIN pupilsightSchoolYear AS f ON b.pupilsightSchoolYearID=f.pupilsightSchoolYearID 
            
            LEFT JOIN pupilsightFamilyChild AS child ON child.pupilsightPersonID=a.pupilsightPersonID 
            LEFT JOIN pupilsightFamilyAdult AS adult1 ON adult1.pupilsightFamilyID=child.pupilsightFamilyID AND adult1.contactPriority=1 
            LEFT JOIN pupilsightPerson as parent1 ON parent1.pupilsightPersonID=adult1.pupilsightPersonID AND parent1.status='Full' 
            LEFT JOIN pupilsightFamilyAdult as adult2 ON adult2.pupilsightFamilyID=child.pupilsightFamilyID AND adult2.contactPriority=2 
            LEFT JOIN pupilsightPerson as parent2 ON parent2.pupilsightPersonID=adult2.pupilsightPersonID AND parent2.status='Full' 
            
            WHERE a.pupilsightPersonID = " . $aid . " ";
            $applicationData = database::doSelectOne($sqla);
            // echo '<pre>';
            // print_r($applicationData);
            // echo '</pre>';
            // die();

            if (!empty($applicationData['officialName'])) {
                $fname = $applicationData['officialName'];
            } else {
                $fname = $aid;
            }

            $date = date('d-m-Y', strtotime($applicationData['created_at']));

            // $sql = "SELECT id, formatval FROM fn_fee_series WHERE pupilsightSchoolYearID = " . $applicationData['pupilsightSchoolYearID'] . " AND pupilsightProgramID = ".$applicationData['pupilsightProgramID']." ";
            // $result = database::doSelectOne($sql);

            // if (!empty($result['formatval'])) {
            //     $seriesId = $result['id'];
            //     $invformat = explode('$', $result['formatval']);
            //     $iformat = '';
            //     $orderwise = 0;
            //     foreach ($invformat as $inv) {
            //         if ($inv == '{AB}') {
            //             $sqlfort = 'SELECT id, no_of_digit, last_no FROM fn_fee_series_number_format WHERE fn_fee_series_id=' . $seriesId . ' AND type= "numberwise"';
            //             $formatvalues = database::doSelectOne($sqlfort);


            //             $str_length = $formatvalues['no_of_digit'];

            //             $iformat .= str_pad($formatvalues['last_no'], $str_length, '0', STR_PAD_LEFT);

            //             $lastnoadd = $formatvalues['last_no'] + 1;

            //             $lastno = str_pad($lastnoadd, $str_length, '0', STR_PAD_LEFT);

            //             $sql1 = "UPDATE fn_fee_series_number_format SET last_no= " . $lastno . " WHERE fn_fee_series_id= " . $seriesId . " AND type= 'numberwise'  ";
            //             $result1 = database::doUpdate($sql1);
            //         } else {
            //             $iformat .= $inv;
            //         }
            //         $orderwise++;
            //     }
            //     $tc_id = $iformat;
            // } else {
            //     $tc_id = '';
            // }


        try {
               
            //$phpword->setValue('tc_no', $tc_id);
            try {
                    $phpword->setValue('application_no', $fname);
            } catch (Exception $ex) {
            }

            try {
                $phpword->setValue('application_date', $date);
            } catch (Exception $ex) {
            }

            try {
                $phpword->setValue('student_name', $applicationData['officialName']);
            } catch (Exception $ex) {
            }

            try {
                $phpword->setValue('program', $applicationData['program']);
            } catch (Exception $ex) {
            }

            try {
                $phpword->setValue('class', $applicationData['class']);
            } catch (Exception $ex) {
            }

            try {
                $phpword->setValue('section', $applicationData['section']);
            } catch (Exception $ex) {
            }

            try {
                $phpword->setValue('academic', $applicationData['academic']);
            } catch (Exception $ex) {
            }
            try {
                $phpword->setValue('dob', $applicationData['dob']);
            } catch (Exception $ex) {
            }
            try {
                $phpword->setValue('father_name', $applicationData['fatherName']);
            } catch (Exception $ex) {
            }

            try {
                $phpword->setValue('father_email', $applicationData['fatherEmail']);
            } catch (Exception $ex) {
            }

            try {
                $phpword->setValue('father_phone', $applicationData['fatherPhone']);
            } catch (Exception $ex) {
            }

            try {
                $phpword->setValue('mother_name', $applicationData['motherName']);
            } catch (Exception $ex) {
            }

            try {
                $phpword->setValue('mother_email', $applicationData['motherEmail']);
            } catch (Exception $ex) {
            }

            try {
                $phpword->setValue('mother_phone', $applicationData['motherPhone']);
            } catch (Exception $ex) {
            }

            // $sq = "INSERT INTO pupilsightStudentTcTaken SET  pupilsightSchoolYearID = " . $applicationData['pupilsightSchoolYearID'] . ", pupilsightProgramID=" . $applicationData['pupilsightProgramID'] . ", pupilsightYearGroupID='" . $applicationData['pupilsightYearGroupID'] . "', pupilsightPersonID=" . $aid . " , pupilsightRollGroupID=" . $applicationData['pupilsightRollGroupID'] . " , pupilsightStudentTcTakenID= '". $tc_id."' ";
            // $connection2->query($sq);
            
            // $squ = "UPDATE pupilsightStudentEnrolment SET  pupilsightProgramID='', pupilsightYearGroupID='' , pupilsightRollGroupID='' WHERE pupilsightPersonID=" . $aid . "";
	        // $connection2->query($squ);
        } catch (Exception $ex) {
            echo $ex;
            die();
        }
           


            // foreach ($arrHeader as $k => $ah) {
            //     if (array_key_exists($ah, $arr)) {
            //         if ($ah == 'file-upload' || $ah == 'image_upload') {
            //             $attrValue = $arr[$ah];
            //             try {
            //                 $imgVal = array("path" => $attrValue, "width" => 100, "height" => 100);
            //                 $phpword->setImageValue($ah, $imgVal);
            //                 //$phpword->setImageValue($ah, $attrValue);
            //             } catch (Exception $ex) {
            //             }
            //         } else {
            //             try {
            //                 $pv = str_replace('&', ' and ', $arr[$ah]);
            //                 $phpword->setValue($ah, $pv);
            //             } catch (Exception $ex) {
            //             }
            //         }
            //     } else {
            //         try {
            //             $phpword->setValue($ah, '');
            //         } catch (Exception $ex) {
            //         }
            //     }
            // }
            // echo '<pre>';
            // print_r($newarr);
            // echo '</pre>';
            // die();
           

            $fname = trim(str_replace("/", "_", $fname));

            $savedocsx = $_SERVER["DOCUMENT_ROOT"] . "/public/student_tc/" . $fname . ".docx";
            $phpword->saveAs($savedocsx);

            // header("Content-Disposition: attachment; filename=" . $fname . ".docx");
            // readfile($savedocsx);
            // unlink($savedocsx);

            $fileName = $fname . ".docx";
            $dirPath = $_SERVER["DOCUMENT_ROOT"] . "/public/student_tc/";

            if (file_exists($dirPath . $fileName)) {
                convert($fileName, $dirPath, $dirPath, FALSE, TRUE);
            } else {
                //echo "file not fund.";
            }

            $pdfFilename = $_SERVER["DOCUMENT_ROOT"] . "/public/student_tc/" . $fname . ".pdf";

            header("Content-Disposition: attachment; filename=" . $fname . ".pdf");
            readfile($pdfFilename);
            unlink($savedocsx);
        } catch (Exception $ex) {
            echo $ex;
            die();
        }
    }
}
