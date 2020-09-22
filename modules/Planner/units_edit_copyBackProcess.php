<?php
/*
Pupilsight, Flexible & Open School System
*/

include '../../pupilsight.php';

$pupilsightSchoolYearID = $_POST['pupilsightSchoolYearID'];
$pupilsightCourseClassID = $_POST['pupilsightCourseClassID'];
$pupilsightCourseID = $_POST['pupilsightCourseID'];
$pupilsightUnitID = $_POST['pupilsightUnitID'];
$URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_POST['address'])."/units_edit_copyBack.php&pupilsightUnitID=$pupilsightUnitID&pupilsightCourseID=$pupilsightCourseID&pupilsightCourseClassID=$pupilsightCourseClassID&pupilsightSchoolYearID=$pupilsightSchoolYearID";
$URLCopy = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_POST['address'])."/units_edit.php&pupilsightSchoolYearID=$pupilsightSchoolYearID&pupilsightCourseID=$pupilsightCourseID&pupilsightUnitID=$pupilsightUnitID";

if (isActionAccessible($guid, $connection2, '/modules/Planner/units_edit_copyBack.php') == false) {
    $URL .= '&return=error0';
    header("Location: {$URL}");
} else {
    $highestAction = getHighestGroupedAction($guid, $_POST['address'], $connection2);
    if ($highestAction == false) {
        $URL .= "&return=error0$params";
        header("Location: {$URL}");
    } else {
        //Proceed!
        if ($pupilsightSchoolYearID == '' or $pupilsightCourseID == '' or $pupilsightCourseClassID == '' or $pupilsightUnitID == '') {
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
                    //Write to database
                    try {
                        $data = array('pupilsightUnitID' => $pupilsightUnitID);
                        $sql = 'DELETE FROM pupilsightUnitBlock WHERE pupilsightUnitID=:pupilsightUnitID';
                        $result = $connection2->prepare($sql);
                        $result->execute($data);
                    } catch (PDOException $e) {
                        $URL .= '&return=error2';
                        header("Location: {$URL}");
                        exit();
                    }

                    try {
                        $dataBlocks = array('pupilsightUnitID' => $pupilsightUnitID, 'pupilsightCourseClassID' => $pupilsightCourseClassID, 'pupilsightCourseClassID' => $pupilsightCourseClassID);
                        $sqlBlocks = 'SELECT * FROM pupilsightUnitClass JOIN pupilsightUnitClassBlock ON (pupilsightUnitClassBlock.pupilsightUnitClassID=pupilsightUnitClass.pupilsightUnitClassID) JOIN pupilsightPlannerEntry ON (pupilsightPlannerEntry.pupilsightPlannerEntryID=pupilsightUnitClassBlock.pupilsightPlannerEntryID) WHERE pupilsightUnitClass.pupilsightUnitID=:pupilsightUnitID AND pupilsightUnitClass.pupilsightCourseClassID=:pupilsightCourseClassID AND pupilsightPlannerEntry.pupilsightCourseClassID=:pupilsightCourseClassID ORDER BY sequenceNumber';
                        $resultBlocks = $connection2->prepare($sqlBlocks);
                        $resultBlocks->execute($dataBlocks);
                    } catch (PDOException $e) {
                        $URL .= '&return=error2';
                        header("Location: {$URL}");
                        exit();
                    }

                    $partialFail = false;
                    while ($rowBlocks = $resultBlocks->fetch()) {
                        try {
                            $dataBlock = array('pupilsightUnitID' => $pupilsightUnitID, 'title' => $rowBlocks['title'], 'type' => $rowBlocks['type'], 'length' => $rowBlocks['length'], 'contents' => $rowBlocks['contents'], 'sequenceNumber' => $rowBlocks['sequenceNumber']);
                            $sqlBlock = 'INSERT INTO pupilsightUnitBlock SET pupilsightUnitID=:pupilsightUnitID, title=:title, type=:type, length=:length, contents=:contents, sequenceNumber=:sequenceNumber';
                            $resultBlock = $connection2->prepare($sqlBlock);
                            $resultBlock->execute($dataBlock);
                        } catch (PDOException $e) {
                            $partialFail = true;
                        }
                    }

                    if ($partialFail == true) {
                        $URL .= '&copyReturn=error6';
                        header("Location: {$URL}");
                    } else {
                        $URLCopy = $URLCopy.'&return=success2';
                        header("Location: {$URLCopy}");
                    }
                }
            }
        }
    }
}
