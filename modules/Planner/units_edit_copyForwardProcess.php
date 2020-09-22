<?php
/*
Pupilsight, Flexible & Open School System
*/

include '../../pupilsight.php';

$pupilsightSchoolYearID = $_POST['pupilsightSchoolYearID'];
$pupilsightCourseClassID = $_POST['pupilsightCourseClassID'];
$pupilsightCourseID = $_POST['pupilsightCourseID'];
$pupilsightUnitID = $_POST['pupilsightUnitID'];
$pupilsightSchoolYearIDCopyTo = $_POST['pupilsightSchoolYearIDCopyTo'];
$pupilsightCourseIDTarget = $_POST['pupilsightCourseIDTarget'];
$nameTarget = $_POST['nameTarget'];

$URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_POST['address'])."/units_edit_copyForward.php&pupilsightUnitID=$pupilsightUnitID&pupilsightCourseID=$pupilsightCourseID&pupilsightCourseClassID=$pupilsightCourseClassID&pupilsightSchoolYearID=$pupilsightSchoolYearID";

if (isActionAccessible($guid, $connection2, '/modules/Planner/units_edit_copyForward.php') == false) {
    $URL .= '&copyForwardReturn=error0';
    header("Location: {$URL}");
} else {
    $highestAction = getHighestGroupedAction($guid, $_POST['address'], $connection2);
    if ($highestAction == false) {
        $URL .= "&copyForwardReturn=error0$params";
        header("Location: {$URL}");
    } else {
        //Proceed!
        if ($pupilsightSchoolYearID == '' or $pupilsightCourseID == '' or $pupilsightCourseClassID == '' or $pupilsightUnitID == '' or $pupilsightSchoolYearIDCopyTo == '' or $pupilsightCourseIDTarget == '' or $nameTarget == '') {
            $URL .= '&copyForwardReturn=error3';
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
                $URL .= '&copyForwardReturn=error2';
                header("Location: {$URL}");
                exit();
            }
            if ($result->rowCount() != 1) {
                $URL .= '&copyForwardReturn=error4';
                header("Location: {$URL}");
            } else {
                //Check existence of specified unit
                try {
                    $data = array('pupilsightUnitID' => $pupilsightUnitID, 'pupilsightCourseID' => $pupilsightCourseID);
                    $sql = 'SELECT * FROM pupilsightUnit WHERE pupilsightUnitID=:pupilsightUnitID AND pupilsightCourseID=:pupilsightCourseID';
                    $result = $connection2->prepare($sql);
                    $result->execute($data);
                } catch (PDOException $e) {
                    $URL .= '&copyForwardReturn=error2';
                    header("Location: {$URL}");
                    exit();
                }
                if ($result->rowCount() != 1) {
                    $URL .= '&copyForwardReturn=error4';
                    header("Location: {$URL}");
                } else {
                    //Write to database
                    $row = $result->fetch();
                    $partialFail = false;

                    //Create new unit
                    try {
                        $data = array('pupilsightCourseID' => $pupilsightCourseIDTarget, 'name' => $nameTarget, 'description' => $row['description'], 'attachment' => $row['attachment'], 'details' => $row['details'], 'pupilsightPersonIDCreator' => $_SESSION[$guid]['pupilsightPersonID'], 'pupilsightPersonIDLastEdit' => $_SESSION[$guid]['pupilsightPersonID']);
                        $sql = 'INSERT INTO pupilsightUnit SET pupilsightCourseID=:pupilsightCourseID, name=:name, description=:description, attachment=:attachment, details=:details, pupilsightPersonIDCreator=:pupilsightPersonIDCreator, pupilsightPersonIDLastEdit=:pupilsightPersonIDLastEdit';
                        $result = $connection2->prepare($sql);
                        $result->execute($data);
                    } catch (PDOException $e) {
                        $URL .= '&copyForwardReturn=error2';
                        header("Location: {$URL}");
                        exit();
                    }

                    //Get new unit ID
                    $gibbinUnitIDNew = $connection2->lastInsertID();

                    if ($gibbinUnitIDNew == '') {
                        $partialFail = true;
                    } else {
                        //Read blocks from old unit
                        try {
                            $dataBlocks = array('pupilsightUnitID' => $pupilsightUnitID, 'pupilsightCourseClassID' => $pupilsightCourseClassID, 'pupilsightCourseClassID' => $pupilsightCourseClassID);
                            $sqlBlocks = 'SELECT * FROM pupilsightUnitClass JOIN pupilsightUnitClassBlock ON (pupilsightUnitClassBlock.pupilsightUnitClassID=pupilsightUnitClass.pupilsightUnitClassID) JOIN pupilsightPlannerEntry ON (pupilsightPlannerEntry.pupilsightPlannerEntryID=pupilsightUnitClassBlock.pupilsightPlannerEntryID) WHERE pupilsightUnitClass.pupilsightUnitID=:pupilsightUnitID AND pupilsightUnitClass.pupilsightCourseClassID=:pupilsightCourseClassID AND pupilsightPlannerEntry.pupilsightCourseClassID=:pupilsightCourseClassID ORDER BY sequenceNumber';
                            $resultBlocks = $connection2->prepare($sqlBlocks);
                            $resultBlocks->execute($dataBlocks);
                        } catch (PDOException $e) {
                            $partialFail = true;
                        }

                        //Write blocks to new unit
                        while ($rowBlocks = $resultBlocks->fetch()) {
                            try {
                                $dataBlock = array('pupilsightUnitID' => $gibbinUnitIDNew, 'title' => $rowBlocks['title'], 'type' => $rowBlocks['type'], 'length' => $rowBlocks['length'], 'contents' => $rowBlocks['contents'], 'sequenceNumber' => $rowBlocks['sequenceNumber']);
                                $sqlBlock = 'INSERT INTO pupilsightUnitBlock SET pupilsightUnitID=:pupilsightUnitID, title=:title, type=:type, length=:length, contents=:contents, sequenceNumber=:sequenceNumber';
                                $resultBlock = $connection2->prepare($sqlBlock);
                                $resultBlock->execute($dataBlock);
                            } catch (PDOException $e) {
                                $partialFail = true;
                            }
                        }

                        //Read outcomes from old unit
                        try {
                            $dataOutcomes = array('pupilsightUnitID' => $pupilsightUnitID);
                            $sqlOutcomes = 'SELECT * FROM pupilsightUnitOutcome WHERE pupilsightUnitID=:pupilsightUnitID';
                            $resultOutcomes = $connection2->prepare($sqlOutcomes);
                            $resultOutcomes->execute($dataOutcomes);
                        } catch (PDOException $e) {
                            $partialFail = true;
                        }

                        //Write outcomes to new unit
                        if ($resultOutcomes->rowCount() > 0) {
                            while ($rowOutcomes = $resultOutcomes->fetch()) {
                                //Write to database
                                try {
                                    $dataCopy = array('pupilsightUnitID' => $gibbinUnitIDNew, 'pupilsightOutcomeID' => $rowOutcomes['pupilsightOutcomeID'], 'sequenceNumber' => $rowOutcomes['sequenceNumber'], 'content' => $rowOutcomes['content']);
                                    $sqlCopy = 'INSERT INTO pupilsightUnitOutcome SET pupilsightUnitID=:pupilsightUnitID, pupilsightOutcomeID=:pupilsightOutcomeID, sequenceNumber=:sequenceNumber, content=:content';
                                    $resultCopy = $connection2->prepare($sqlCopy);
                                    $resultCopy->execute($dataCopy);
                                } catch (PDOException $e) {
                                    $partialFail = true;
                                }
                            }
                        }
                    }

                    if ($partialFail == true) {
                        $URL .= '&copyForwardReturn=error6';
                        header("Location: {$URL}");
                    } else {
                        $URLCopy = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_POST['address'])."/units_edit.php&pupilsightSchoolYearID=$pupilsightSchoolYearID&pupilsightCourseID=$pupilsightCourseIDTarget&pupilsightUnitID=$gibbinUnitIDNew";
                        $URLCopy = $URLCopy.'&return=success0';
                        header("Location: {$URLCopy}");
                    }
                }
            }
        }
    }
}
