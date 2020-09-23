<?php
/*
Pupilsight, Flexible & Open School System
*/

include '../../pupilsight.php';

$enableEffort = getSettingByScope($connection2, 'Markbook', 'enableEffort');
$enableRubrics = getSettingByScope($connection2, 'Markbook', 'enableRubrics');

$pupilsightCourseClassID = $_GET['pupilsightCourseClassID'];
$pupilsightMarkbookColumnID = $_GET['pupilsightMarkbookColumnID'];
$URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_GET['address'])."/markbook_edit_edit.php&pupilsightMarkbookColumnID=$pupilsightMarkbookColumnID&pupilsightCourseClassID=$pupilsightCourseClassID";

if (isActionAccessible($guid, $connection2, '/modules/Markbook/markbook_edit_edit.php') == false) {
    $URL .= '&return=error0';
    header("Location: {$URL}");
} else {
    $highestAction = getHighestGroupedAction($guid, $_GET['address'], $connection2);
    if ($highestAction == false) {
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
                    $sql = 'SELECT * FROM pupilsightMarkbookColumn WHERE pupilsightMarkbookColumnID=:pupilsightMarkbookColumnID AND pupilsightCourseClassID=:pupilsightCourseClassID';
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
                    $attachment = $_POST['attachment'];
                    $pupilsightPersonIDLastEdit = $_SESSION[$guid]['pupilsightPersonID'];

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
                        $URL .= '&return=error3';
                        header("Location: {$URL}");
                    } else {
                        //Write to database
                        try {
                            $data = array('pupilsightUnitID' => $pupilsightUnitID, 'pupilsightPlannerEntryID' => $pupilsightPlannerEntryID, 'pupilsightCourseClassID' => $pupilsightCourseClassID, 'name' => $name, 'description' => $description, 'type' => $type, 'date' => $date, 'attainment' => $attainment, 'pupilsightScaleIDAttainment' => $pupilsightScaleIDAttainment, 'attainmentWeighting' => $attainmentWeighting, 'attainmentRaw' => $attainmentRaw, 'attainmentRawMax' => $attainmentRawMax, 'effort' => $effort, 'pupilsightScaleIDEffort' => $pupilsightScaleIDEffort, 'pupilsightRubricIDAttainment' => $pupilsightRubricIDAttainment, 'pupilsightRubricIDEffort' => $pupilsightRubricIDEffort, 'comment' => $comment, 'uploadedResponse' => $uploadedResponse, 'completeDate' => $completeDate, 'complete' => $complete, 'viewableStudents' => $viewableStudents, 'viewableParents' => $viewableParents, 'attachment' => $attachment, 'pupilsightPersonIDLastEdit' => $pupilsightPersonIDLastEdit, 'pupilsightSchoolYearTermID' => $pupilsightSchoolYearTermID, 'pupilsightMarkbookColumnID' => $pupilsightMarkbookColumnID);
                            $sql = 'UPDATE pupilsightMarkbookColumn SET pupilsightUnitID=:pupilsightUnitID, pupilsightPlannerEntryID=:pupilsightPlannerEntryID, pupilsightCourseClassID=:pupilsightCourseClassID, name=:name, description=:description, type=:type, date=:date, attainment=:attainment, pupilsightScaleIDAttainment=:pupilsightScaleIDAttainment, attainmentWeighting=:attainmentWeighting, attainmentRaw=:attainmentRaw, attainmentRawMax=:attainmentRawMax, effort=:effort, pupilsightScaleIDEffort=:pupilsightScaleIDEffort, pupilsightRubricIDAttainment=:pupilsightRubricIDAttainment, pupilsightRubricIDEffort=:pupilsightRubricIDEffort, comment=:comment, uploadedResponse=:uploadedResponse, completeDate=:completeDate, complete=:complete, viewableStudents=:viewableStudents, viewableParents=:viewableParents, attachment=:attachment, pupilsightPersonIDLastEdit=:pupilsightPersonIDLastEdit, pupilsightSchoolYearTermID=:pupilsightSchoolYearTermID WHERE pupilsightMarkbookColumnID=:pupilsightMarkbookColumnID';
                            $result = $connection2->prepare($sql);
                            $result->execute($data);
                        } catch (PDOException $e) {
                            $URL .= '&return=error2';
                            header("Location: {$URL}");
                            exit();
                        }

                        $URL .= '&return=success0';
                        header("Location: {$URL}");
                    }
                }
            }
        }
    }
}
