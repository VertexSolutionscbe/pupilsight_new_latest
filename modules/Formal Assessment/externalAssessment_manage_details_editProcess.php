<?php
/*
Pupilsight, Flexible & Open School System
*/

include '../../pupilsight.php';

$pupilsightPersonID = $_POST['pupilsightPersonID'];
$pupilsightExternalAssessmentStudentID = $_POST['pupilsightExternalAssessmentStudentID'];
$search = $_GET['search'];
$allStudents = '';
if (isset($_GET['allStudents'])) {
    $allStudents = $_GET['allStudents'];
}

if ($pupilsightPersonID == '') { echo 'Fatal error loading this page!';
} else {
    $URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_POST['address'])."/externalAssessment_manage_details_edit.php&pupilsightPersonID=$pupilsightPersonID&pupilsightExternalAssessmentStudentID=$pupilsightExternalAssessmentStudentID&search=$search&allStudents=$allStudents";

    if (isActionAccessible($guid, $connection2, '/modules/Formal Assessment/externalAssessment_manage_details_edit.php') == false) {
        $URL .= '&return=error0';
        header("Location: {$URL}");
    } else {
        //Proceed!
        //Check if tt specified
        if ($pupilsightExternalAssessmentStudentID == '') {
            $URL .= '&return=error1';
            header("Location: {$URL}");
        } else {
            try {
                $data = array('pupilsightExternalAssessmentStudentID' => $pupilsightExternalAssessmentStudentID);
                $sql = 'SELECT * FROM pupilsightExternalAssessmentStudent WHERE pupilsightExternalAssessmentStudentID=:pupilsightExternalAssessmentStudentID';
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

                //Validate Inputs
                $count = 0;
                if (is_numeric($_POST['count'])) {
                    $count = $_POST['count'];
                }
                $date = dateConvert($guid, $_POST['date']);

                $attachment = isset($_POST['attachment'])? $_POST['attachment'] : $row['attachment'];
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

                if ($date == '') {
                    $URL .= '&return=error1';
                    header("Location: {$URL}");
                } else {
                    //Scan through fields
                    $partialFail = false;
                    for ($i = 0; $i < $count; ++$i) {
                        $pupilsightExternalAssessmentStudentEntryID = @$_POST[$i.'-pupilsightExternalAssessmentStudentEntryID'];
                        if (isset($_POST[$i.'-pupilsightScaleGradeID']) == false) {
                            $pupilsightScaleGradeID = null;
                        } else {
                            if ($_POST[$i.'-pupilsightScaleGradeID'] == '') {
                                $pupilsightScaleGradeID = null;
                            } else {
                                $pupilsightScaleGradeID = $_POST[$i.'-pupilsightScaleGradeID'];
                            }
                        }
                        if ($pupilsightExternalAssessmentStudentEntryID != '') {
                            try {
                                $data = array('pupilsightScaleGradeID' => $pupilsightScaleGradeID, 'pupilsightExternalAssessmentStudentEntryID' => $pupilsightExternalAssessmentStudentEntryID);
                                $sql = 'UPDATE pupilsightExternalAssessmentStudentEntry SET pupilsightScaleGradeID=:pupilsightScaleGradeID WHERE pupilsightExternalAssessmentStudentEntryID=:pupilsightExternalAssessmentStudentEntryID';
                                $result = $connection2->prepare($sql);
                                $result->execute($data);
                            } catch (PDOException $e) {
                                $partialFail = true;
                            }
                        }
                    }

                    //Write to database
                    try {
                        $data = array('date' => $date, 'attachment' => $attachment, 'pupilsightExternalAssessmentStudentID' => $pupilsightExternalAssessmentStudentID);
                        $sql = 'UPDATE pupilsightExternalAssessmentStudent SET date=:date, attachment=:attachment WHERE pupilsightExternalAssessmentStudentID=:pupilsightExternalAssessmentStudentID';
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
                        $URL .= "&return=success0";
                        header("Location: {$URL}");
                    }
                }
            }
        }
    }
}
