<?php
/*
Pupilsight, Flexible & Open School System
*/

include '../../pupilsight.php';

$pupilsightCourseClassID = $_GET['pupilsightCourseClassID'];
$pupilsightInternalAssessmentColumnID = $_GET['pupilsightInternalAssessmentColumnID'];
$URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_GET['address'])."/internalAssessment_write_data.php&pupilsightInternalAssessmentColumnID=$pupilsightInternalAssessmentColumnID&pupilsightCourseClassID=$pupilsightCourseClassID";

if (isActionAccessible($guid, $connection2, '/modules/Formal Assessment/internalAssessment_write_data.php') == false) {
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
                $attachmentCurrent = isset($_POST['attachment'])? $_POST['attachment'] : '';
                $name = $row['name'];
                $count = $_POST['count'];
                $partialFail = false;
                $attainment = $row['attainment'];
                $pupilsightScaleIDAttainment = $row['pupilsightScaleIDAttainment'];
                $effort = $row['effort'];
                $pupilsightScaleIDEffort = $row['pupilsightScaleIDEffort'];
                $comment = $row['comment'];
                $uploadedResponse = $row['uploadedResponse'];

                for ($i = 1;$i <= $count;++$i) {
                    $pupilsightPersonIDStudent = $_POST["$i-pupilsightPersonID"];
                    //Attainment
                    if ($attainment == 'N') {
                        $attainmentValue = null;
                        $attainmentDescriptor = null;
                    } elseif ($pupilsightScaleIDAttainment == '') {
                        $attainmentValue = '';
                        $attainmentDescriptor = '';
                    } else {
                        $attainmentValue = $_POST["$i-attainmentValue"];
                    }
                    //Effort
                    if ($effort == 'N') {
                        $effortValue = null;
                        $effortDescriptor = null;
                    } elseif ($pupilsightScaleIDEffort == '') {
                        $effortValue = '';
                        $effortDescriptor = '';
                    } else {
                        $effortValue = $_POST["$i-effortValue"];
                    }
                    //Comment
                    if ($comment != 'Y') {
                        $commentValue = null;
                    } else {
                        $commentValue = $_POST["comment$i"];
                    }
                    $pupilsightPersonIDLastEdit = $_SESSION[$guid]['pupilsightPersonID'];

                    //SET AND CALCULATE FOR ATTAINMENT
                    if ($attainment == 'Y' and $pupilsightScaleIDAttainment != '') {
                        //Without personal warnings
                        $attainmentDescriptor = '';
                        if ($attainmentValue != '') {
                            $lowestAcceptableAttainment = $_POST['lowestAcceptableAttainment'];
                            $scaleAttainment = $_POST['scaleAttainment'];
                            try {
                                $dataScale = array('attainmentValue' => $attainmentValue, 'scaleAttainment' => $scaleAttainment);
                                $sqlScale = 'SELECT * FROM pupilsightScaleGrade JOIN pupilsightScale ON (pupilsightScaleGrade.pupilsightScaleID=pupilsightScale.pupilsightScaleID) WHERE value=:attainmentValue AND pupilsightScaleGrade.pupilsightScaleID=:scaleAttainment';
                                $resultScale = $connection2->prepare($sqlScale);
                                $resultScale->execute($dataScale);
                            } catch (PDOException $e) {
                                $partialFail = true;
                            }
                            if ($resultScale->rowCount() != 1) {
                                $partialFail = true;
                            } else {
                                $rowScale = $resultScale->fetch();
                                $sequence = $rowScale['sequenceNumber'];
                                $attainmentDescriptor = $rowScale['descriptor'];
                            }
                        }
                    }

                    //SET AND CALCULATE FOR EFFORT
                    if ($effort == 'Y' and $pupilsightScaleIDEffort != '') {
                        $effortDescriptor = '';
                        if ($effortValue != '') {
                            $lowestAcceptableEffort = $_POST['lowestAcceptableEffort'];
                            $scaleEffort = $_POST['scaleEffort'];
                            try {
                                $dataScale = array('effortValue' => $effortValue, 'scaleEffort' => $scaleEffort);
                                $sqlScale = 'SELECT * FROM pupilsightScaleGrade JOIN pupilsightScale ON (pupilsightScaleGrade.pupilsightScaleID=pupilsightScale.pupilsightScaleID) WHERE value=:effortValue AND pupilsightScaleGrade.pupilsightScaleID=:scaleEffort';
                                $resultScale = $connection2->prepare($sqlScale);
                                $resultScale->execute($dataScale);
                            } catch (PDOException $e) {
                                $partialFail = true;
                            }
                            if ($resultScale->rowCount() != 1) {
                                $partialFail = true;
                            } else {
                                $rowScale = $resultScale->fetch();
                                $sequence = $rowScale['sequenceNumber'];
                                $effortDescriptor = $rowScale['descriptor'];
                            }
                        }
                    }

                    $time = time();

                    $attachment = isset($_POST["attachment$i"])? $_POST["attachment$i"] : '';

                    //Move attached file, if there is one
                    if ($uploadedResponse == 'Y') {
                        if (!empty($_FILES["response$i"]['tmp_name'])) {
                            $fileUploader = new Pupilsight\FileUploader($pdo, $pupilsight->session);
                
                            $file = (isset($_FILES["response$i"]))? $_FILES["response$i"] : null;

                            // Upload the file, return the /uploads relative path
                            $attachment = $fileUploader->uploadFromPost($file, $name.'_Uploaded Response');

                            if (empty($attachment)) {
                                $partialFail = true;
                            }
                        }
                    }

                    $selectFail = false;
                    try {
                        $data = array('pupilsightInternalAssessmentColumnID' => $pupilsightInternalAssessmentColumnID, 'pupilsightPersonIDStudent' => $pupilsightPersonIDStudent);
                        $sql = 'SELECT * FROM pupilsightInternalAssessmentEntry WHERE pupilsightInternalAssessmentColumnID=:pupilsightInternalAssessmentColumnID AND pupilsightPersonIDStudent=:pupilsightPersonIDStudent';
                        $result = $connection2->prepare($sql);
                        $result->execute($data);
                    } catch (PDOException $e) {
                        $partialFail = true;
                        $selectFail = true;
                    }
                    if (!($selectFail)) {
                        if ($result->rowCount() < 1) {
                            try {
                                $data = array('pupilsightInternalAssessmentColumnID' => $pupilsightInternalAssessmentColumnID, 'pupilsightPersonIDStudent' => $pupilsightPersonIDStudent, 'attainmentValue' => $attainmentValue, 'attainmentDescriptor' => $attainmentDescriptor, 'effortValue' => $effortValue, 'effortDescriptor' => $effortDescriptor, 'comment' => $commentValue, 'attachment' => $attachment, 'pupilsightPersonIDLastEdit' => $pupilsightPersonIDLastEdit);
                                $sql = 'INSERT INTO pupilsightInternalAssessmentEntry SET pupilsightInternalAssessmentColumnID=:pupilsightInternalAssessmentColumnID, pupilsightPersonIDStudent=:pupilsightPersonIDStudent, attainmentValue=:attainmentValue, attainmentDescriptor=:attainmentDescriptor, effortValue=:effortValue, effortDescriptor=:effortDescriptor, comment=:comment, response=:attachment, pupilsightPersonIDLastEdit=:pupilsightPersonIDLastEdit';
                                $result = $connection2->prepare($sql);
                                $result->execute($data);
                            } catch (PDOException $e) {
                                $partialFail = true;
                            }
                        } else {
                            $row = $result->fetch();
                            //Update
                            try {
                                $data = array('pupilsightInternalAssessmentColumnID' => $pupilsightInternalAssessmentColumnID, 'pupilsightPersonIDStudent' => $pupilsightPersonIDStudent, 'attainmentValue' => $attainmentValue, 'attainmentDescriptor' => $attainmentDescriptor, 'comment' => $commentValue, 'attachment' => $attachment, 'effortValue' => $effortValue, 'effortDescriptor' => $effortDescriptor, 'pupilsightPersonIDLastEdit' => $pupilsightPersonIDLastEdit, 'pupilsightInternalAssessmentEntryID' => $row['pupilsightInternalAssessmentEntryID']);
                                $sql = 'UPDATE pupilsightInternalAssessmentEntry SET pupilsightInternalAssessmentColumnID=:pupilsightInternalAssessmentColumnID, pupilsightPersonIDStudent=:pupilsightPersonIDStudent, attainmentValue=:attainmentValue, attainmentDescriptor=:attainmentDescriptor, effortValue=:effortValue, effortDescriptor=:effortDescriptor, comment=:comment, response=:attachment, pupilsightPersonIDLastEdit=:pupilsightPersonIDLastEdit WHERE pupilsightInternalAssessmentEntryID=:pupilsightInternalAssessmentEntryID';
                                $result = $connection2->prepare($sql);
                                $result->execute($data);
                            } catch (PDOException $e) {
                                $partialFail = true;
                            }
                        }
                    }
                }

                //Update column
                $description = $_POST['description'];
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
                    $attachment = $attachmentCurrent;
                }
                $completeDate = $_POST['completeDate'];
                if ($completeDate == '') {
                    $completeDate = null;
                    $complete = 'N';
                } else {
                    $completeDate = dateConvert($guid, $completeDate);
                    $complete = 'Y';
                }
                try {
                    $data = array('attachment' => $attachment, 'description' => $description, 'completeDate' => $completeDate, 'complete' => $complete, 'pupilsightInternalAssessmentColumnID' => $pupilsightInternalAssessmentColumnID);
                    $sql = 'UPDATE pupilsightInternalAssessmentColumn SET attachment=:attachment, description=:description, completeDate=:completeDate, complete=:complete WHERE pupilsightInternalAssessmentColumnID=:pupilsightInternalAssessmentColumnID';
                    $result = $connection2->prepare($sql);
                    $result->execute($data);
                } catch (PDOException $e) {
                    $partialFail = true;
                }

                //Return!
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
