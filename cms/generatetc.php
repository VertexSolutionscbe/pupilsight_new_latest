<?php
include_once '../vendor/autoload.php';
include_once 'w2f/adminLib.php';
include '../pupilsight.php';
include $_SERVER["DOCUMENT_ROOT"] . '/pdf_convert.php';
$adminlib = new adminlib();

session_start();
$aid = $_GET['aid'];
$pid = $_GET['pid'];
$sid = $_GET['sid'];

//error_reporting(E_ALL);
$studentId = explode(',', $sid);
$sqlchk = 'SELECT pupilsightProgramID FROM pupilsightStudentEnrolment WHERE pupilsightPersonID = '.$sid.' ';
$pro = database::doSelectOne($sqlchk);

$sqlpt = "SELECT path, filename FROM pupilsightDocTemplate WHERE pupilsightSchoolYearID = ".$aid." AND pupilsightProgramID = ".$pro['pupilsightProgramID']." ";

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

            $sqla = "SELECT a.*, d.name AS class, e.name AS section,c.name as program ,f.name as academic, b.cdt as created_at, d.pupilsightYearGroupID, e.pupilsightRollGroupID,c.pupilsightProgramID ,f.pupilsightSchoolYearID FROM pupilsightPerson AS a 
            LEFT JOIN pupilsightStudentEnrolment AS b ON a.pupilsightPersonID=b.pupilsightPersonID 
            LEFT JOIN pupilsightProgram AS c ON b.pupilsightProgramID=c.pupilsightProgramID 
            LEFT JOIN pupilsightYearGroup AS d ON b.pupilsightYearGroupID=d.pupilsightYearGroupID 
            LEFT JOIN pupilsightRollGroup AS e ON b.pupilsightRollGroupID=e.pupilsightRollGroupID 
            LEFT JOIN pupilsightSchoolYear AS f ON b.pupilsightSchoolYearID=f.pupilsightSchoolYearID 
            -- LEFT JOIN pupilsightStudentEnrolment AS g ON a.pupilsightPersonID=b.pupilsightPersonID 
            -- LEFT JOIN pupilsightStudentEnrolment AS h ON a.pupilsightPersonID=b.pupilsightPersonID 
            -- LEFT JOIN pupilsightStudentEnrolment AS i ON a.pupilsightPersonID=b.pupilsightPersonID   
            WHERE a.pupilsightPersonID = " . $aid . " ";
            $applicationData = database::doSelectOne($sqla);
            // print_r($applicationData);
            // die();

            if (!empty($applicationData['officialName'])) {
                $fname = $applicationData['officialName'];
            } else {
                $fname = $aid;
            }

            $date = date('d-m-Y', strtotime($applicationData['created_at']));
        try {
               
            $phpword->setValue('application_no', $fname);
            $phpword->setValue('application_date', $date);
            $phpword->setValue('student_name', $applicationData['officialName']);
            $phpword->setValue('program', $applicationData['program']);
            $phpword->setValue('class', $applicationData['class']);
            $phpword->setValue('section', $applicationData['section']);
            $phpword->setValue('academic', $applicationData['academic']);
            $phpword->setValue('dob', $applicationData['dob']);

            $sq = "INSERT INTO pupilsightStudentTcTaken SET  pupilsightSchoolYearID = " . $applicationData['pupilsightSchoolYearID'] . ", pupilsightProgramID=" . $applicationData['pupilsightProgramID'] . ", pupilsightYearGroupID='" . $applicationData['pupilsightYearGroupID'] . "', pupilsightPersonID=" . $aid . " , pupilsightRollGroupID=" . $applicationData['pupilsightRollGroupID'] . " , pupilsightStudentTcTakenID=" . $applicationData['pupilsightSchoolYearID'] . "";
            $connection2->query($sq);
            
            $squ = "UPDATE pupilsightStudentEnrolment SET  pupilsightProgramID='', pupilsightYearGroupID='' , pupilsightRollGroupID='' WHERE pupilsightPersonID=" . $aid . "";
	        $connection2->query($squ);
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
