<?php
/*
Pupilsight, Flexible & Open School System
*/

include '../../pupilsight.php';

$count = 0;
if (is_numeric($_POST['count'])) {
    $count = $_POST['count'];
}
$pupilsightPersonID = $_POST['pupilsightPersonID'];
$pupilsightExternalAssessmentID = $_POST['pupilsightExternalAssessmentID'];
$date = dateConvert($guid, $_POST['date']);
$search = $_GET['search'];
$allStudents = '';
if (isset($_GET['allStudents'])) {
    $allStudents = $_GET['allStudents'];
}

$URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_POST['address'])."/externalAssessment_manage_details_add.php&pupilsightExternalAssessmentID=$pupilsightExternalAssessmentID&pupilsightPersonID=$pupilsightPersonID&step=2&search=$search&allStudents=$allStudents";

if (isActionAccessible($guid, $connection2, '/modules/Formal Assessment/externalAssessment_manage_details_add.php') == false) {
    $URL .= '&return=error0';
    header("Location: {$URL}");
} else {
    //Proceed!
    //Validate Inputs
    if ($pupilsightPersonID == '' or $pupilsightExternalAssessmentID == '' or $date == '') {
        $URL .= '&return=error1';
        header("Location: {$URL}");
    } else {
        //Lock markbook column table
        try {
            $sqlLock = 'LOCK TABLES pupilsightExternalAssessmentStudent WRITE, pupilsightExternalAssessmentStudentEntry WRITE, pupilsightFileExtension READ';
            $resultLock = $connection2->query($sqlLock);
        } catch (PDOException $e) {
            $URL .= '&return=error2';
            header("Location: {$URL}");
            exit();
        }

        //Get next autoincrement
        try {
            $sqlAI = "SHOW TABLE STATUS LIKE 'pupilsightExternalAssessmentStudent'";
            $resultAI = $connection2->query($sqlAI);
        } catch (PDOException $e) {
            $URL .= '&return=error3';
            header("Location: {$URL}");
            exit();
        }

        $rowAI = $resultAI->fetch();
        $AI = str_pad($rowAI['Auto_increment'], 14, '0', STR_PAD_LEFT);

        $attachment = '';
        //Move attached image  file, if there is one
        if (!empty($_FILES['file']['tmp_name'])) {
            $fileUploader = new Pupilsight\FileUploader($pdo, $pupilsight->session);

            $file = (isset($_FILES['file']))? $_FILES['file'] : null;

            // Upload the file, return the /uploads relative path
            $attachment = $fileUploader->uploadFromPost($file, 'externalAssessmentUpload');

            if (empty($attachment)) {
                $partialFail = true;
            }
        }

        //Scan through fields
        $partialFail = false;
        for ($i = 0; $i < $count; ++$i) {
            $pupilsightExternalAssessmentFieldID = @$_POST[$i.'-pupilsightExternalAssessmentFieldID'];
            if (isset($_POST[$i.'-pupilsightScaleGradeID']) == false) {
                $pupilsightScaleGradeID = null;
            } else {
                if ($_POST[$i.'-pupilsightScaleGradeID'] == '') {
                    $pupilsightScaleGradeID = null;
                } else {
                    $pupilsightScaleGradeID = $_POST[$i.'-pupilsightScaleGradeID'];
                }
            }

            if ($pupilsightExternalAssessmentFieldID != '') {
                try {
                    $data = array('AI' => $AI, 'pupilsightExternalAssessmentFieldID' => $pupilsightExternalAssessmentFieldID, 'pupilsightScaleGradeID' => $pupilsightScaleGradeID);
                    $sql = 'INSERT INTO pupilsightExternalAssessmentStudentEntry SET pupilsightExternalAssessmentStudentID=:AI, pupilsightExternalAssessmentFieldID=:pupilsightExternalAssessmentFieldID, pupilsightScaleGradeID=:pupilsightScaleGradeID';
                    $result = $connection2->prepare($sql);
                    $result->execute($data);
                } catch (PDOException $e) {
                    $partialFail = true;
                }
            }
        }

        //Write to database
        try {
            $data = array('pupilsightExternalAssessmentID' => $pupilsightExternalAssessmentID, 'pupilsightPersonID' => $pupilsightPersonID, 'date' => $date, 'attachment' => $attachment);
            $sql = 'INSERT INTO pupilsightExternalAssessmentStudent SET pupilsightExternalAssessmentID=:pupilsightExternalAssessmentID, pupilsightPersonID=:pupilsightPersonID, date=:date, attachment=:attachment';
            $result = $connection2->prepare($sql);
            $result->execute($data);
        } catch (PDOException $e) {
            $URL .= '&return=error2';
            header("Location: {$URL}");
            exit();
        }

        //Last insert ID
        $AI = str_pad($connection2->lastInsertID(), 12, '0', STR_PAD_LEFT);

        //Unlock module table
        try {
            $sql = 'UNLOCK TABLES';
            $result = $connection2->query($sql);
        } catch (PDOException $e) {
        }

        if ($partialFail == true) {
            $URL .= "&return=error1&editID=$AI";
            header("Location: {$URL}");
        } else {
            $URL .= "&return=success0&editID=$AI";
            header("Location: {$URL}");
        }
    }
}
