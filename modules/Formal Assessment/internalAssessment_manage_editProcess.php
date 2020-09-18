<?php
/*
Pupilsight, Flexible & Open School System
*/

include '../../pupilsight.php';

$pupilsightCourseClassID = $_GET['pupilsightCourseClassID'];
$pupilsightInternalAssessmentColumnID = $_GET['pupilsightInternalAssessmentColumnID'];
$URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_GET['address'])."/internalAssessment_manage_edit.php&pupilsightInternalAssessmentColumnID=$pupilsightInternalAssessmentColumnID&pupilsightCourseClassID=$pupilsightCourseClassID";

if (isActionAccessible($guid, $connection2, '/modules/Formal Assessment/internalAssessment_manage_edit.php') == false) {
    $URL .= '&return=error0';
    header("Location: {$URL}");
} else {
    if (empty($_POST)) {
        $URL .= '&return=error3';
        header("Location: {$URL}");
    } else {
        //Proceed!
        //Check if school year specified
        if ($pupilsightInternalAssessmentColumnID == '' or $pupilsightCourseClassID == '') {
            $URL .= '&return=error1';
            header("Location: {$URL}");
        } else {
            try {
                $data = array('pupilsightInternalAssessmentColumnID' => $pupilsightInternalAssessmentColumnID, 'pupilsightCourseClassID' => $pupilsightCourseClassID);
                $sql = 'SELECT * FROM pupilsightInternalAssessmentColumn WHERE pupilsightInternalAssessmentColumnID=:pupilsightInternalAssessmentColumnID AND pupilsightCourseClassID=:pupilsightCourseClassID';
                $result = $connection2->prepare($sql);
                $result->execute($data);
            } catch (PDOException $e) {
                $URL .= '&return=error2';
                header("Location: {$URL}");
                exit();
            }

            if ($result->rowCount() != 1) {
                $URL .= '&return=error2';
                header("Location: {$URL}");
            } else {
                $row = $result->fetch();
                $partialFail = false;
                
                //Validate Inputs
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
                $pupilsightPersonIDLastEdit = $_SESSION[$guid]['pupilsightPersonID'];

                $time = time();
                //Move attached file, if there is one
                if (!empty($_FILES['file']['tmp_name'])) {
                    $fileUploader = new Pupilsight\FileUploader($pdo, $pupilsight->session);
                
                    $file = (isset($_FILES['file']))? $_FILES['file'] : null;

                    // Upload the file, return the /uploads relative path
                    $attachment = $fileUploader->uploadFromPost($file, $name);

                    if (empty($attachment)) {
                        $partialFail = true;
                    }
                } else {
                    $attachment = isset($_POST['attachment'])? $_POST['attachment'] : '';
                }

                if ($name == '' or $description == '' or $type == '' or $viewableStudents == '' or $viewableParents == '') {
                    $URL .= '&return=error1';
                    header("Location: {$URL}");
                } else {
                    //Write to database
                    try {
                        $data = array('pupilsightCourseClassID' => $pupilsightCourseClassID, 'name' => $name, 'description' => $description, 'type' => $type, 'attainment' => $attainment, 'pupilsightScaleIDAttainment' => $pupilsightScaleIDAttainment, 'effort' => $effort, 'pupilsightScaleIDEffort' => $pupilsightScaleIDEffort, 'comment' => $comment, 'uploadedResponse' => $uploadedResponse, 'completeDate' => $completeDate, 'complete' => $complete, 'viewableStudents' => $viewableStudents, 'viewableParents' => $viewableParents, 'attachment' => $attachment, 'pupilsightPersonIDLastEdit' => $pupilsightPersonIDLastEdit, 'pupilsightInternalAssessmentColumnID' => $pupilsightInternalAssessmentColumnID);
                        $sql = 'UPDATE pupilsightInternalAssessmentColumn SET pupilsightCourseClassID=:pupilsightCourseClassID, name=:name, description=:description, type=:type, attainment=:attainment, pupilsightScaleIDAttainment=:pupilsightScaleIDAttainment, effort=:effort, pupilsightScaleIDEffort=:pupilsightScaleIDEffort, comment=:comment, uploadedResponse=:uploadedResponse, completeDate=:completeDate, complete=:complete, viewableStudents=:viewableStudents, viewableParents=:viewableParents, attachment=:attachment, pupilsightPersonIDLastEdit=:pupilsightPersonIDLastEdit WHERE pupilsightInternalAssessmentColumnID=:pupilsightInternalAssessmentColumnID';
                        $result = $connection2->prepare($sql);
                        $result->execute($data);
                    } catch (PDOException $e) {
                        $URL .= '&return=error2';
                        header("Location: {$URL}");
                        exit();
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
    }
}
