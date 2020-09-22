<?php
/*
Pupilsight, Flexible & Open School System
*/

include '../../pupilsight.php';

$enableEffort = getSettingByScope($connection2, 'Markbook', 'enableEffort');
$enableRubrics = getSettingByScope($connection2, 'Markbook', 'enableRubrics');
$enableModifiedAssessment = getSettingByScope($connection2, 'Markbook', 'enableModifiedAssessment');

$pupilsightCourseClassID = $_GET['pupilsightCourseClassID'];
$pupilsightMarkbookColumnID = $_GET['pupilsightMarkbookColumnID'];
$URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_GET['address'])."/markbook_edit_data.php&pupilsightMarkbookColumnID=$pupilsightMarkbookColumnID&pupilsightCourseClassID=$pupilsightCourseClassID";

$personalisedWarnings = getSettingByScope($connection2, 'Markbook', 'personalisedWarnings');

if (isActionAccessible($guid, $connection2, '/modules/Markbook/markbook_edit_data.php') == false) {
    $URL .= '&return=error0';
    header("Location: {$URL}");
} else {
    if (empty($_POST)) {
        $URL .= '&return=warning1';
        header("Location: {$URL}");
    } else {
        //Proceed!
        //Check if school year specified
        if ($pupilsightMarkbookColumnID == '' or $pupilsightCourseClassID == '') {
            $URL .= '&return=error1';
            header("Location: {$URL}");
        } else {
            try {
                $data = array('pupilsightMarkbookColumnID' => $pupilsightMarkbookColumnID, 'pupilsightCourseClassID' => $pupilsightCourseClassID);
                $sql = 'SELECT pupilsightMarkbookColumn.*, pupilsightScaleIDTarget FROM pupilsightMarkbookColumn JOIN pupilsightCourseClass ON (pupilsightMarkbookColumn.pupilsightCourseClassID=pupilsightCourseClass.pupilsightCourseClassID) WHERE pupilsightMarkbookColumnID=:pupilsightMarkbookColumnID AND pupilsightMarkbookColumn.pupilsightCourseClassID=:pupilsightCourseClassID';
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
                $name = $row['name' ];
                $count = $_POST['count'];
                $partialFail = false;
                $attainment = $row['attainment'];
                $pupilsightScaleIDAttainment = $row['pupilsightScaleIDAttainment'];
                if ($enableEffort != 'Y') {
                    $effort = 'N';
                    $pupilsightScaleIDEffort = null;
                }
                else {
                    $effort = $row['effort'];
                    $pupilsightScaleIDEffort = $row['pupilsightScaleIDEffort'];
                }
                $comment = $row['comment'];
                $uploadedResponse = $row['uploadedResponse'];
                $pupilsightScaleIDAttainment = $row['pupilsightScaleIDAttainment'];
                $pupilsightScaleIDTarget = $row['pupilsightScaleIDTarget'];

                for ($i = 1;$i <= $count;++$i) {
                    $pupilsightPersonIDStudent = $_POST["$i-pupilsightPersonID"];
                    //Modified Assessment
                    if ($enableModifiedAssessment != 'Y') {
                        $modifiedAssessment = NULL;
                    }
                    else {
                        if (isset($_POST["$i-modifiedAssessmentEligible"])) { //Checkbox exists
                            if (isset($_POST["$i-modifiedAssessment"])) {
                                $modifiedAssessment = 'Y';
                            }
                            else {
                                $modifiedAssessment = 'N';
                            }
                        }
                        else { //Checkbox does not exist
                            $modifiedAssessment = NULL;
                        }                        
                    }
                    //Attainment
                    if ($attainment == 'N') {
                        $attainmentValue = null;
                        $attainmentValueRaw = null;
                        $attainmentDescriptor = null;
                        $attainmentConcern = null;
                    } elseif ($pupilsightScaleIDAttainment == '') {
                        $attainmentValue = '';
                        $attainmentValueRaw = '';
                        $attainmentDescriptor = '';
                        $attainmentConcern = '';
                    } else {
                        $attainmentValue = (isset($_POST["$i-attainmentValue"]))? $_POST["$i-attainmentValue"] : null;
                        $attainmentValueRaw = (isset($_POST["$i-attainmentValueRaw"]))? $_POST["$i-attainmentValueRaw"] : null;
                    }
                    //Effort
                    if ($effort == 'N') {
                        $effortValue = null;
                        $effortDescriptor = null;
                        $effortConcern = null;
                    } elseif ($pupilsightScaleIDEffort == '') {
                        $effortValue = '';
                        $effortDescriptor = '';
                        $effortConcern = '';
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
                        //Check for target grade
                        try {
                            $dataTarget = array('pupilsightCourseClassID' => $pupilsightCourseClassID, 'pupilsightPersonIDStudent' => $pupilsightPersonIDStudent);
                            $sqlTarget = 'SELECT * FROM pupilsightMarkbookTarget JOIN pupilsightScaleGrade ON (pupilsightMarkbookTarget.pupilsightScaleGradeID=pupilsightScaleGrade.pupilsightScaleGradeID) WHERE pupilsightCourseClassID=:pupilsightCourseClassID AND pupilsightPersonIDStudent=:pupilsightPersonIDStudent';
                            $resultTarget = $connection2->prepare($sqlTarget);
                            $resultTarget->execute($dataTarget);
                        } catch (PDOException $e) {
                            $partialFail = true;
                        }

                        //With personal warnings
                        if ($personalisedWarnings == 'Y' && $resultTarget->rowCount() == 1 && $attainmentValue != '' && $pupilsightScaleIDAttainment == $pupilsightScaleIDTarget) {
                            $attainmentConcern = 'N';
                            $attainmentDescriptor = '';
                            $rowTarget = $resultTarget->fetch();

                            //Get details of attainment grade (sequenceNumber)
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
                                $target = $rowTarget['sequenceNumber'];
                                $attainmentSequence = $rowScale['sequenceNumber'];

                                //Test against target grade and set values accordingly
                                //Below target
                                if ($attainmentSequence > $target) {
                                    $attainmentConcern = 'Y';
                                    $attainmentDescriptor = sprintf(__('Below personalised target of %1$s'), $rowTarget['value']);
                                }
                                //Above target
                                elseif ($attainmentSequence <= $target) {
                                    $attainmentConcern = 'P';
                                    $attainmentDescriptor = sprintf(__('Equal to or above personalised target of %1$s'), $rowTarget['value']);
                                }
                            }
                        }
                        //Without personal warnings
                        else {
                            $attainmentConcern = 'N';
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

                                if ($lowestAcceptableAttainment != '' and $sequence != '' and $attainmentValue != '') {
                                    if ($sequence > $lowestAcceptableAttainment) {
                                        $attainmentConcern = 'Y';
                                    }
                                }
                            }
                        }
                    }

                    //SET AND CALCULATE FOR EFFORT
                    if ($effort == 'Y' and $pupilsightScaleIDEffort != '') {
                        $effortConcern = 'N';
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

                            if ($lowestAcceptableEffort != '' and $sequence != '' and $effortValue != '') {
                                if ($sequence > $lowestAcceptableEffort) {
                                    $effortConcern = 'Y';
                                }
                            }
                        }
                    }

                    //Move attached file, if there is one
                    if ($uploadedResponse == 'Y') {
                        //Move attached image  file, if there is one
                        if (!empty($_FILES['response'.$i]['tmp_name'])) {
                            $fileUploader = new Pupilsight\FileUploader($pdo, $pupilsight->session);

                            $file = (isset($_FILES['response'.$i]))? $_FILES['response'.$i] : null;

                            // Upload the file, return the /uploads relative path
                            $attachment = $fileUploader->uploadFromPost($file, $name."_Uploaded Response");

                            if (empty($attachment)) {
                                $partialFail = true;
                            }
                        } else {
                            $attachment = (isset($_POST["attachment$i"]))? $_POST["attachment$i"] : '';
                        }
                    } else {
                        $attachment = null;
                    }

                    $selectFail = false;
                    try {
                        $data = array('pupilsightMarkbookColumnID' => $pupilsightMarkbookColumnID, 'pupilsightPersonIDStudent' => $pupilsightPersonIDStudent);
                        $sql = 'SELECT * FROM pupilsightMarkbookEntry WHERE pupilsightMarkbookColumnID=:pupilsightMarkbookColumnID AND pupilsightPersonIDStudent=:pupilsightPersonIDStudent';
                        $result = $connection2->prepare($sql);
                        $result->execute($data);
                    } catch (PDOException $e) {
                        $partialFail = true;
                        $selectFail = true;
                    }
                    if (!($selectFail)) {
                        if ($result->rowCount() < 1) {
                            try {
                                $data = array('pupilsightMarkbookColumnID' => $pupilsightMarkbookColumnID, 'pupilsightPersonIDStudent' => $pupilsightPersonIDStudent, 'modifiedAssessment' => $modifiedAssessment, 'attainmentValue' => $attainmentValue, 'attainmentValueRaw' => $attainmentValueRaw, 'attainmentDescriptor' => $attainmentDescriptor, 'attainmentConcern' => $attainmentConcern, 'effortValue' => $effortValue, 'effortDescriptor' => $effortDescriptor, 'effortConcern' => $effortConcern, 'comment' => $commentValue, 'pupilsightPersonIDLastEdit' => $pupilsightPersonIDLastEdit, 'attachment' => $attachment);
                                $sql = 'INSERT INTO pupilsightMarkbookEntry SET pupilsightMarkbookColumnID=:pupilsightMarkbookColumnID, pupilsightPersonIDStudent=:pupilsightPersonIDStudent, modifiedAssessment=:modifiedAssessment, attainmentValue=:attainmentValue, attainmentValueRaw=:attainmentValueRaw, attainmentDescriptor=:attainmentDescriptor, attainmentConcern=:attainmentConcern, effortValue=:effortValue, effortDescriptor=:effortDescriptor, effortConcern=:effortConcern, comment=:comment, pupilsightPersonIDLastEdit=:pupilsightPersonIDLastEdit, response=:attachment';
                                $result = $connection2->prepare($sql);
                                $result->execute($data);
                            } catch (PDOException $e) {
                                $partialFail = true;
                            }
                        } else {
                            $row = $result->fetch();
                            //Update
                            try {
                                $data = array('pupilsightMarkbookColumnID' => $pupilsightMarkbookColumnID, 'pupilsightPersonIDStudent' => $pupilsightPersonIDStudent, 'modifiedAssessment' => $modifiedAssessment, 'attainmentValue' => $attainmentValue, 'attainmentValueRaw' => $attainmentValueRaw, 'attainmentDescriptor' => $attainmentDescriptor, 'attainmentConcern' => $attainmentConcern, 'effortValue' => $effortValue, 'effortDescriptor' => $effortDescriptor, 'effortConcern' => $effortConcern, 'comment' => $commentValue, 'pupilsightPersonIDLastEdit' => $pupilsightPersonIDLastEdit, 'attachment' => $attachment, 'pupilsightMarkbookEntryID' => $row['pupilsightMarkbookEntryID']);
                                $sql = 'UPDATE pupilsightMarkbookEntry SET pupilsightMarkbookColumnID=:pupilsightMarkbookColumnID, pupilsightPersonIDStudent=:pupilsightPersonIDStudent, modifiedAssessment=:modifiedAssessment, attainmentValue=:attainmentValue, attainmentValueRaw=:attainmentValueRaw, attainmentDescriptor=:attainmentDescriptor, attainmentConcern=:attainmentConcern, effortValue=:effortValue, effortDescriptor=:effortDescriptor, effortConcern=:effortConcern, comment=:comment, pupilsightPersonIDLastEdit=:pupilsightPersonIDLastEdit, response=:attachment WHERE pupilsightMarkbookEntryID=:pupilsightMarkbookEntryID';
                                $result = $connection2->prepare($sql);
                                $result->execute($data);
                            } catch (PDOException $e) {
                                $partialFail = true;
                            }
                        }
                    }
                }

                //Update column
                $completeDate = $_POST['completeDate'];
                if ($completeDate == '') {
                    $completeDate = null;
                    $complete = 'N';
                } else {
                    $completeDate = dateConvert($guid, $completeDate);
                    $complete = 'Y';
                }
                try {
                    $data = array('completeDate' => $completeDate, 'complete' => $complete, 'pupilsightMarkbookColumnID' => $pupilsightMarkbookColumnID);
                    $sql = 'UPDATE pupilsightMarkbookColumn SET completeDate=:completeDate, complete=:complete WHERE pupilsightMarkbookColumnID=:pupilsightMarkbookColumnID';
                    $result = $connection2->prepare($sql);
                    $result->execute($data);
                } catch (PDOException $e) {
                    $partialFail = true;
                }

                //Return!
                if ($partialFail == true) {
                    $URL .= '&return=error3';
                    header("Location: {$URL}");
                } else {
                    $URL .= '&return=success0';
                    header("Location: {$URL}");
                }
            }
        }
    }
}
