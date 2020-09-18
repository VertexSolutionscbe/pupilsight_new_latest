<?php
/*
Pupilsight, Flexible & Open School System
*/

include '../../pupilsight.php';

$enableEffort = getSettingByScope($connection2, 'Markbook', 'enableEffort');
$enableRubrics = getSettingByScope($connection2, 'Markbook', 'enableRubrics');

$pupilsightCourseClassID = $_GET['pupilsightCourseClassID'];
$URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_GET['address'])."/markbook_edit_add.php&pupilsightCourseClassID=$pupilsightCourseClassID";

if (isActionAccessible($guid, $connection2, '/modules/Markbook/markbook_edit_add.php') == false) {
    $URL .= '&return=error0';
    header("Location: {$URL}");
} else {
    if (empty($_POST)) {
        $URL .= '&return=warning1';
        header("Location: {$URL}");
    } else {
        //Proceed!
        //Validate Inputs
        $pupilsightUnitID = $_POST['pupilsightUnitID'];
        $pupilsightPlannerEntryID = null;
        if (isset($_POST['pupilsightPlannerEntryID'])) {
            if ($_POST['pupilsightPlannerEntryID'] != '') {
                $pupilsightPlannerEntryID = $_POST['pupilsightPlannerEntryID'];
            }
        }
        $name = $_POST['name'];
        $description = $_POST['description'];
        $type = $_POST['type'];
        $date = (!empty($_POST['date']))? dateConvert($guid, $_POST['date']) : date('Y-m-d');
        $pupilsightSchoolYearTermID = (!empty($_POST['pupilsightSchoolYearTermID']))? $_POST['pupilsightSchoolYearTermID'] : null;

        // Grab the appropriate term ID if the date is provided and the term ID is not
        if (empty($pupilsightSchoolYearTermID) && !empty($date)) {
            try {
                $dataTerm = array('pupilsightSchoolYearID' => $_SESSION[$guid]['pupilsightSchoolYearID'], 'date' => $date);
                $sqlTerm = "SELECT pupilsightSchoolYearTermID FROM pupilsightSchoolYearTerm WHERE pupilsightSchoolYearID=:pupilsightSchoolYearID AND :date BETWEEN firstDay AND lastDay";
                $resultTerm = $connection2->prepare($sqlTerm);
                $resultTerm->execute($dataTerm);
            } catch (PDOException $e) {
                $URL .= '&return=error2';
                header("Location: {$URL}");
                exit();
            }
            if ($resultTerm->rowCount() > 0) {
                $pupilsightSchoolYearTermID = $resultTerm->fetchColumn(0);
            }
        }

        //Sort out attainment
        $attainment = $_POST['attainment'];
        $attainmentWeighting = 1;
        $attainmentRaw = 'N';
        $attainmentRawMax = null;
        if ($attainment == 'N') {
            $pupilsightScaleIDAttainment = null;
            $pupilsightRubricIDAttainment = null;
        } else {
            if ($_POST['pupilsightScaleIDAttainment'] == '') {
                $pupilsightScaleIDAttainment = null;
            } else {
                $pupilsightScaleIDAttainment = $_POST['pupilsightScaleIDAttainment'];
                if (isset($_POST['attainmentWeighting'])) {
                    if (is_numeric($_POST['attainmentWeighting']) && $_POST['attainmentWeighting'] > 0) {
                        $attainmentWeighting = $_POST['attainmentWeighting'];
                    }
                }
                if (isset($_POST['attainmentRawMax'])) {
                    if (is_numeric($_POST['attainmentRawMax']) && $_POST['attainmentRawMax'] > 0) {
                        $attainmentRawMax = $_POST['attainmentRawMax'];
                        $attainmentRaw = 'Y';
                    }
                }
            }
            if ($enableRubrics != 'Y') {
                $pupilsightRubricIDAttainment = null;
            }
            else {
                if ($_POST['pupilsightRubricIDAttainment'] == '') {
                    $pupilsightRubricIDAttainment = null;
                } else {
                    $pupilsightRubricIDAttainment = $_POST['pupilsightRubricIDAttainment'];
                }
            }
        }
        //Sort out effort
        if ($enableEffort != 'Y') {
            $effort = 'N';
        }
        else {
            $effort = $_POST['effort'];
        }
        if ($effort == 'N') {
            $pupilsightScaleIDEffort = null;
            $pupilsightRubricIDEffort = null;
        } else {
            if ($_POST['pupilsightScaleIDEffort'] == '') {
                $pupilsightScaleIDEffort = null;
            } else {
                $pupilsightScaleIDEffort = $_POST['pupilsightScaleIDEffort'];
            }
            if ($enableRubrics != 'Y') {
                $pupilsightRubricIDEffort = null;
            }
            else {
                if ($_POST['pupilsightRubricIDEffort'] == '') {
                    $pupilsightRubricIDEffort = null;
                } else {
                    $pupilsightRubricIDEffort = $_POST['pupilsightRubricIDEffort'];
                }
            }
        }
        $comment = $_POST['comment'];
        $uploadedResponse = $_POST['uploadedResponse'];
        $completeDate = $_POST['completeDate'];
        if ($completeDate == '') {
            $completeDate = null;
            $complete = 'N';
        } else {
            $completeDate = dateConvert($guid, $completeDate);
            $complete = 'Y';
        }
        $viewableStudents = $_POST['viewableStudents'];
        $viewableParents = $_POST['viewableParents'];
        $attachment = '';
        $pupilsightPersonIDCreator = $_SESSION[$guid]['pupilsightPersonID'];
        $pupilsightPersonIDLastEdit = $_SESSION[$guid]['pupilsightPersonID'];

        $sequenceNumber = null;

        // Build the initial column counts for this class
        try {
            $dataSequence = array('pupilsightCourseClassID' => $pupilsightCourseClassID);
            $sqlSequence = 'SELECT max(sequenceNumber) as max FROM pupilsightMarkbookColumn WHERE pupilsightCourseClassID=:pupilsightCourseClassID';
            $resultSequence = $connection2->prepare($sqlSequence);
            $resultSequence->execute($dataSequence);
        } catch (PDOException $e) {
            $URL .= '&return=error2';
            header("Location: {$URL}");
            exit();
        }

        if ($resultSequence && $resultSequence->rowCount() > 0) {
            $sequenceNumber = $resultSequence->fetchColumn() + 1;
        }

        //Lock markbook column table
        try {
            $sqlLock = 'LOCK TABLES pupilsightMarkbookColumn WRITE, pupilsightFileExtension READ';
            $resultLock = $connection2->query($sqlLock);
        } catch (PDOException $e) {
            $URL .= '&return=error2';
            header("Location: {$URL}");
            exit();
        }

        //Get next autoincrement
        try {
            $sqlAI = "SHOW TABLE STATUS LIKE 'pupilsightMarkbookColumn'";
            $resultAI = $connection2->query($sqlAI);
        } catch (PDOException $e) {
            $URL .= '&return=error2';
            header("Location: {$URL}");
            exit();
        }

        $rowAI = $resultAI->fetch();
        $AI = str_pad($rowAI['Auto_increment'], 10, '0', STR_PAD_LEFT);

        $partialFail = false;

        //Move attached image  file, if there is one
        if (!empty($_FILES['file']['tmp_name'])) {
            $fileUploader = new Pupilsight\FileUploader($pdo, $pupilsight->session);

            $file = (isset($_FILES['file']))? $_FILES['file'] : null;

            // Upload the file, return the /uploads relative path
            $attachment = $fileUploader->uploadFromPost($file, $name);

            if (empty($attachment)) {
                $partialFail = true;
            }
        }

        if ($name == '' or $description == '' or $type == '' or $date == '' or $viewableStudents == '' or $viewableParents == '') {
            $URL .= '&return=error1';
            header("Location: {$URL}");
        } else {
            //Write to database
            try {
                $data = array('pupilsightUnitID' => $pupilsightUnitID, 'pupilsightPlannerEntryID' => $pupilsightPlannerEntryID, 'pupilsightCourseClassID' => $pupilsightCourseClassID, 'name' => $name, 'description' => $description, 'type' => $type, 'date' => $date, 'sequenceNumber' => $sequenceNumber, 'attainment' => $attainment, 'pupilsightScaleIDAttainment' => $pupilsightScaleIDAttainment, 'attainmentWeighting' => $attainmentWeighting, 'attainmentRaw' => $attainmentRaw, 'attainmentRawMax' => $attainmentRawMax, 'effort' => $effort, 'pupilsightScaleIDEffort' => $pupilsightScaleIDEffort, 'pupilsightRubricIDAttainment' => $pupilsightRubricIDAttainment, 'pupilsightRubricIDEffort' => $pupilsightRubricIDEffort, 'comment' => $comment, 'uploadedResponse' => $uploadedResponse, 'completeDate' => $completeDate, 'complete' => $complete, 'viewableStudents' => $viewableStudents, 'viewableParents' => $viewableParents, 'attachment' => $attachment, 'pupilsightPersonIDCreator' => $pupilsightPersonIDCreator, 'pupilsightPersonIDLastEdit' => $pupilsightPersonIDLastEdit, 'pupilsightSchoolYearTermID' => $pupilsightSchoolYearTermID);
                $sql = 'INSERT INTO pupilsightMarkbookColumn SET pupilsightUnitID=:pupilsightUnitID, pupilsightPlannerEntryID=:pupilsightPlannerEntryID, pupilsightCourseClassID=:pupilsightCourseClassID, name=:name, description=:description, type=:type, date=:date, sequenceNumber=:sequenceNumber, attainment=:attainment, pupilsightScaleIDAttainment=:pupilsightScaleIDAttainment, attainmentWeighting=:attainmentWeighting, attainmentRaw=:attainmentRaw, attainmentRawMax=:attainmentRawMax, effort=:effort, pupilsightScaleIDEffort=:pupilsightScaleIDEffort, pupilsightRubricIDAttainment=:pupilsightRubricIDAttainment, pupilsightRubricIDEffort=:pupilsightRubricIDEffort, comment=:comment, uploadedResponse=:uploadedResponse, completeDate=:completeDate, complete=:complete, viewableStudents=:viewableStudents, viewableParents=:viewableParents, attachment=:attachment, pupilsightPersonIDCreator=:pupilsightPersonIDCreator, pupilsightPersonIDLastEdit=:pupilsightPersonIDLastEdit, pupilsightSchoolYearTermID=:pupilsightSchoolYearTermID';
                $result = $connection2->prepare($sql);
                $result->execute($data);
            } catch (PDOException $e) {
                $URL .= '&return=error2';
                header("Location: {$URL}");
                exit();
            }

            //Last insert ID
            $AI = str_pad($connection2->lastInsertID(), 10, '0', STR_PAD_LEFT);

            //Unlock module table
            try {
                $sql = 'UNLOCK TABLES';
                $result = $connection2->query($sql);
            } catch (PDOException $e) {
            }

            if ($partialFail == true) {
                $URL .= '&return=warning1';
                header("Location: {$URL}");
            } else {
                $URL .= "&return=success0&editID=$AI";
                header("Location: {$URL}");
            }
        }
    }
}
