<?php
/*
Pupilsight, Flexible & Open School System
*/

include '../../pupilsight.php';

$pupilsightCourseClassID = $_GET['pupilsightCourseClassID'];
$URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_GET['address'])."/internalAssessment_manage_add.php&pupilsightCourseClassID=$pupilsightCourseClassID";

if (isActionAccessible($guid, $connection2, '/modules/Formal Assessment/internalAssessment_manage_add.php') == false) {
    $URL .= '&return=error0';
    header("Location: {$URL}");
} else {
    if (empty($_POST)) {
        $URL .= '&return=error3';
        header("Location: {$URL}");
    } else {
        //Proceed!
        //Validate Inputs
        $pupilsightCourseClassIDMulti = null;
        if (isset($_POST['pupilsightCourseClassIDMulti'])) {
            $pupilsightCourseClassIDMulti = $_POST['pupilsightCourseClassIDMulti'];
        }
        $name = $_POST['name'];
        $description = $_POST['description'];
        $type = $_POST['type'];
        //Sort out attainment
        $attainment = $_POST['attainment'];
        if ($attainment == 'N') {
            $pupilsightScaleIDAttainment = null;
        } else {
            if ($_POST['pupilsightScaleIDAttainment'] == '') {
                $pupilsightScaleIDAttainment = null;
            } else {
                $pupilsightScaleIDAttainment = $_POST['pupilsightScaleIDAttainment'];
            }
        }
        //Sort out effort
        $effort = $_POST['effort'];
        if ($effort == 'N') {
            $pupilsightScaleIDEffort = null;
        } else {
            if ($_POST['pupilsightScaleIDEffort'] == '') {
                $pupilsightScaleIDEffort = null;
            } else {
                $pupilsightScaleIDEffort = $_POST['pupilsightScaleIDEffort'];
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
        $pupilsightPersonIDCreator = $_SESSION[$guid]['pupilsightPersonID'];
        $pupilsightPersonIDLastEdit = $_SESSION[$guid]['pupilsightPersonID'];

        $fileUploader = new Pupilsight\FileUploader($pdo, $pupilsight->session);
        $fileUploader->getFileExtensions();
        
        //Lock markbook column table
        try {
            $sqlLock = 'LOCK TABLES pupilsightInternalAssessmentColumn WRITE';
            $resultLock = $connection2->query($sqlLock);
        } catch (PDOException $e) {
            $URL .= '&return=error2';
            header("Location: {$URL}");
            exit();
        }

        //Get next groupingID
        try {
            $sqlGrouping = 'SELECT DISTINCT groupingID FROM pupilsightInternalAssessmentColumn WHERE NOT groupingID IS NULL ORDER BY groupingID DESC';
            $resultGrouping = $connection2->query($sqlGrouping);
        } catch (PDOException $e) {
            $URL .= '&return=error2';
            header("Location: {$URL}");
            exit();
        }

        $rowGrouping = $resultGrouping->fetch();
        if (is_null($rowGrouping['groupingID'])) {
            $groupingID = 1;
        } else {
            $groupingID = ($rowGrouping['groupingID'] + 1);
        }

        $time = time();
        //Move attached file, if there is one
        if (!empty($_FILES['file']['tmp_name'])) {   
            $file = (isset($_FILES['file']))? $_FILES['file'] : null;

            // Upload the file, return the /uploads relative path
            $attachment = $fileUploader->uploadFromPost($file, $name);
                    
            if (empty($attachment)) {
                $partialFail = true;
            }
        } else {
            $attachment = '';
        }

        if (is_array($pupilsightCourseClassIDMulti) == false or is_numeric($groupingID) == false or $groupingID < 1 or $name == '' or $description == '' or $type == '' or $viewableStudents == '' or $viewableParents == '') {
            $URL .= '&return=error1';
            header("Location: {$URL}");
        } else {
            $partialFail = false;

            foreach ($pupilsightCourseClassIDMulti as $pupilsightCourseClassIDSingle) {
                //Write to database
                try {
                    $data = array('groupingID' => $groupingID, 'pupilsightCourseClassID' => $pupilsightCourseClassIDSingle, 'name' => $name, 'description' => $description, 'type' => $type, 'attainment' => $attainment, 'pupilsightScaleIDAttainment' => $pupilsightScaleIDAttainment, 'effort' => $effort, 'pupilsightScaleIDEffort' => $pupilsightScaleIDEffort, 'comment' => $comment, 'uploadedResponse' => $uploadedResponse, 'completeDate' => $completeDate, 'complete' => $complete, 'viewableStudents' => $viewableStudents, 'viewableParents' => $viewableParents, 'attachment' => $attachment, 'pupilsightPersonIDCreator' => $pupilsightPersonIDCreator, 'pupilsightPersonIDLastEdit' => $pupilsightPersonIDLastEdit);
                    $sql = 'INSERT INTO pupilsightInternalAssessmentColumn SET groupingID=:groupingID, pupilsightCourseClassID=:pupilsightCourseClassID, name=:name, description=:description, type=:type, attainment=:attainment, pupilsightScaleIDAttainment=:pupilsightScaleIDAttainment, effort=:effort, pupilsightScaleIDEffort=:pupilsightScaleIDEffort, comment=:comment, uploadedResponse=:uploadedResponse, completeDate=:completeDate, complete=:complete, viewableStudents=:viewableStudents, viewableParents=:viewableParents, attachment=:attachment, pupilsightPersonIDCreator=:pupilsightPersonIDCreator, pupilsightPersonIDLastEdit=:pupilsightPersonIDLastEdit';
                    $result = $connection2->prepare($sql);
                    $result->execute($data);
                } catch (PDOException $e) {
                    echo $e->getMessage();
                    exit();
                    $partialFail = true;
                }
            }

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
                $URL .= '&return=success0';
                header("Location: {$URL}");
            }
        }
    }
}
