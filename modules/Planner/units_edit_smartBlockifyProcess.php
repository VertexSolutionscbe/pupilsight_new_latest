<?php
/*
Pupilsight, Flexible & Open School System
*/

include '../../pupilsight.php';

$pupilsightSchoolYearID = $_POST['pupilsightSchoolYearID'];
$pupilsightCourseClassID = $_POST['pupilsightCourseClassID'];
$pupilsightCourseID = $_POST['pupilsightCourseID'];
$pupilsightUnitID = $_POST['pupilsightUnitID'];
$pupilsightUnitClassID = $_POST['pupilsightUnitClassID'];
$URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_POST['address'])."/units_edit_smartBlockify.php&pupilsightUnitID=$pupilsightUnitID&pupilsightCourseID=$pupilsightCourseID&pupilsightCourseClassID=$pupilsightCourseClassID&pupilsightSchoolYearID=$pupilsightSchoolYearID";
$URLCopy = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_POST['address'])."/units_edit.php&pupilsightSchoolYearID=$pupilsightSchoolYearID&pupilsightCourseID=$pupilsightCourseID&pupilsightUnitID=$pupilsightUnitID";

if (isActionAccessible($guid, $connection2, '/modules/Planner/units_edit_smartBlockify.php') == false) {
    $URL .= '&return=error0';
    header("Location: {$URL}");
} else {
    $highestAction = getHighestGroupedAction($guid, $_POST['address'], $connection2);
    if ($highestAction == false) {
        $URL .= "&return=error0$params";
        header("Location: {$URL}");
    } else {
        //Proceed!
        if ($pupilsightSchoolYearID == '' or $pupilsightCourseID == '' or $pupilsightCourseClassID == '' or $pupilsightUnitID == '' or $pupilsightUnitClassID == '') {
            $URL .= '&return=error1';
            header("Location: {$URL}");
        } else {
            //Check access to specified course
            try {
                if ($highestAction == 'Unit Planner_all') {
                    $data = array('pupilsightSchoolYearID' => $pupilsightSchoolYearID, 'pupilsightCourseID' => $pupilsightCourseID);
                    $sql = 'SELECT * FROM pupilsightCourse WHERE pupilsightSchoolYearID=:pupilsightSchoolYearID AND pupilsightCourseID=:pupilsightCourseID';
                } elseif ($highestAction == 'Unit Planner_learningAreas') {
                    $data = array('pupilsightSchoolYearID' => $pupilsightSchoolYearID, 'pupilsightCourseID' => $pupilsightCourseID, 'pupilsightPersonID' => $_SESSION[$guid]['pupilsightPersonID']);
                    $sql = "SELECT pupilsightCourseID, pupilsightCourse.name, pupilsightCourse.nameShort FROM pupilsightCourse JOIN pupilsightDepartment ON (pupilsightCourse.pupilsightDepartmentID=pupilsightDepartment.pupilsightDepartmentID) JOIN pupilsightDepartmentStaff ON (pupilsightDepartmentStaff.pupilsightDepartmentID=pupilsightDepartment.pupilsightDepartmentID) WHERE pupilsightDepartmentStaff.pupilsightPersonID=:pupilsightPersonID AND (role='Coordinator' OR role='Assistant Coordinator' OR role='Teacher (Curriculum)') AND pupilsightSchoolYearID=:pupilsightSchoolYearID AND pupilsightCourseID=:pupilsightCourseID ORDER BY pupilsightCourse.nameShort";
                }
                $result = $connection2->prepare($sql);
                $result->execute($data);
            } catch (PDOException $e) {
                $URL .= '&return=error2';
                header("Location: {$URL}");
                exit();
            }
            if ($result->rowCount() != 1) {
                $URL .= '&return=error3';
                header("Location: {$URL}");
            } else {
                //Check existence of specified unit
                try {
                    $data = array('pupilsightUnitID' => $pupilsightUnitID, 'pupilsightCourseID' => $pupilsightCourseID);
                    $sql = 'SELECT * FROM pupilsightUnit WHERE pupilsightUnitID=:pupilsightUnitID AND pupilsightCourseID=:pupilsightCourseID';
                    $result = $connection2->prepare($sql);
                    $result->execute($data);
                } catch (PDOException $e) {
                    $URL .= '&return=error2';
                    header("Location: {$URL}");
                    exit();
                }
                if ($result->rowCount() != 1) {
                    $URL .= '&return=error3';
                    header("Location: {$URL}");
                } else {
                    //Ready to let loose with the real logic
                    //GET ALL LESSONS IN UNIT, IN ORDER
                    try {
                        $data = array('pupilsightUnitID' => $pupilsightUnitID, 'pupilsightCourseClassID' => $pupilsightCourseClassID);
                        $sql = 'SELECT pupilsightPlannerEntryID, name, description, teachersNotes, timeStart, timeEnd, date FROM pupilsightPlannerEntry WHERE pupilsightUnitID=:pupilsightUnitID AND pupilsightCourseClassID=:pupilsightCourseClassID ORDER BY date';
                        $result = $connection2->prepare($sql);
                        $result->execute($data);
                    } catch (PDOException $e) {
                        $URL .= '&return=error2';
                        header("Location: {$URL}");
                        exit();
                    }

                    $sequenceNumber = 9999;
                    $partialFail = false;
                    while ($row = $result->fetch()) {
                        $blockFail = false;
                        ++$sequenceNumber;
                        $length = (strtotime($row['date'].' '.$row['timeEnd']) - strtotime($row['date'].' '.$row['timeStart'])) / 60;

                        //MAKE NEW BLOCK
                        try {
                            $dataBlock = array('pupilsightUnitID' => $pupilsightUnitID, 'title' => $row['name'], 'type' => '', 'length' => $length, 'contents' => $row['description'], 'teachersNotes' => $row['teachersNotes'], 'sequenceNumber' => $sequenceNumber);
                            $sqlBlock = 'INSERT INTO pupilsightUnitBlock SET pupilsightUnitID=:pupilsightUnitID, title=:title, type=:type, length=:length, contents=:contents, teachersNotes=:teachersNotes, sequenceNumber=:sequenceNumber';
                            $resultBlock = $connection2->prepare($sqlBlock);
                            $resultBlock->execute($dataBlock);
                        } catch (PDOException $e) {
                            $partialFail = true;
                            $blockFail = true;
                        }

                        if ($blockFail == false) {
                            //TURN MASTER BLOCK INTO A WORKING BLOCK, ATTACHING IT TO LESSON
                            $pupilsightUnitBlockID = $connection2->lastInsertID();
                            $blockFail2 = false;
                            try {
                                $dataBlock2 = array('pupilsightUnitClassID' => $pupilsightUnitClassID, 'pupilsightPlannerEntryID' => $row['pupilsightPlannerEntryID'], 'pupilsightUnitBlockID' => $pupilsightUnitBlockID, 'title' => $row['name'], 'type' => '', 'length' => $length, 'contents' => $row['description'], 'teachersNotes' => $row['teachersNotes'], 'sequenceNumber' => 1);
                                $sqlBlock2 = 'INSERT INTO pupilsightUnitClassBlock SET pupilsightUnitClassID=:pupilsightUnitClassID, pupilsightPlannerEntryID=:pupilsightPlannerEntryID, pupilsightUnitBlockID=:pupilsightUnitBlockID, title=:title, type=:type, length=:length, contents=:contents, teachersNotes=:teachersNotes, sequenceNumber=:sequenceNumber';
                                $resultBlock2 = $connection2->prepare($sqlBlock2);
                                $resultBlock2->execute($dataBlock2);
                            } catch (PDOException $e) {
                                $partialFail = true;
                                $blockFail2 = true;
                            }

                            if ($blockFail2 == false) {
                                //REWRITE LESSON TO REMOVE description AND teachersNotes
                                try {
                                    $dataRewrite = array('pupilsightPlannerEntryID' => $row['pupilsightPlannerEntryID']);
                                    $sqlRewrite = "UPDATE pupilsightPlannerEntry SET description='', teachersNotes='' WHERE pupilsightPlannerEntryID=:pupilsightPlannerEntryID";
                                    $resultRewrite = $connection2->prepare($sqlRewrite);
                                    $resultRewrite->execute($dataRewrite);
                                } catch (PDOException $e) {
                                    $partialFail = true;
                                }
                            }
                        }
                    }

                    if ($partialFail == true) {
                        $URL .= '&copyReturn=error6';
                        header("Location: {$URL}");
                    } else {
                        $URLCopy = $URLCopy.'&copyReturn=success1';
                        header("Location: {$URLCopy}");
                    }
                }
            }
        }
    }
}
