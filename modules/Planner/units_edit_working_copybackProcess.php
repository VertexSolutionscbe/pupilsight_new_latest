<?php
/*
Pupilsight, Flexible & Open School System
*/

include '../../pupilsight.php';

$pupilsightSchoolYearID = $_GET['pupilsightSchoolYearID'];
$pupilsightCourseID = $_GET['pupilsightCourseID'];
$pupilsightCourseClassID = $_GET['pupilsightCourseClassID'];
$pupilsightUnitID = $_GET['pupilsightUnitID'];
$pupilsightUnitBlockID = $_GET['pupilsightUnitBlockID'];
$pupilsightUnitClassBlockID = $_GET['pupilsightUnitClassBlockID'];
$pupilsightUnitClassID = $_GET['pupilsightUnitClassID'];

$URL = $_SESSION[$guid]['absoluteURL']."/index.php?q=/modules/Planner/units_edit_working_copyback.php&pupilsightSchoolYearID=$pupilsightSchoolYearID&pupilsightCourseID=$pupilsightCourseID&pupilsightCourseClassID=$pupilsightCourseClassID&pupilsightUnitID=$pupilsightUnitID&pupilsightUnitBlockID=$pupilsightUnitBlockID&pupilsightUnitClassBlockID=$pupilsightUnitClassBlockID&pupilsightUnitClassID=$pupilsightUnitClassID";

if (isActionAccessible($guid, $connection2, '/modules/Planner/units_edit_working_copyback.php') == false) {
    $URL .= '&return=error0';
    header("Location: {$URL}");
} else {
    $highestAction = getHighestGroupedAction($guid, '/modules/Planner/units_edit_working_copyback.php', $connection2);
    if ($highestAction == false) {
        $URL .= '&return=error0';
        header("Location: {$URL}");
    } else {
        //Proceed!
        //Validate Inputs
        if ($pupilsightSchoolYearID == '' or $pupilsightCourseID == '' or $pupilsightUnitID == '' or $pupilsightCourseClassID == '' or $pupilsightUnitClassID == '') {
            $URL .= '&copyReturn=error3';
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
                $URL .= '&copyReturn=error4';
                header("Location: {$URL}");
            } else {
                //Check existence of specified unit/class
                try {
                    $data = array('pupilsightUnitID' => $pupilsightUnitID, 'pupilsightCourseID' => $pupilsightCourseID, 'pupilsightUnitBlockID' => $pupilsightUnitBlockID, 'pupilsightUnitClassBlockID' => $pupilsightUnitClassBlockID);
                    $sql = 'SELECT pupilsightUnitClassBlock.* FROM pupilsightUnit JOIN pupilsightCourse ON (pupilsightUnit.pupilsightCourseID=pupilsightCourse.pupilsightCourseID) JOIN pupilsightUnitBlock ON (pupilsightUnitBlock.pupilsightUnitID=pupilsightUnit.pupilsightUnitID) JOIN pupilsightUnitClassBlock ON (pupilsightUnitClassBlock.pupilsightUnitBlockID=pupilsightUnitBlock.pupilsightUnitBlockID) WHERE pupilsightUnitClassBlockID=:pupilsightUnitClassBlockID AND pupilsightUnitBlock.pupilsightUnitBlockID=:pupilsightUnitBlockID AND pupilsightUnit.pupilsightUnitID=:pupilsightUnitID AND pupilsightUnit.pupilsightCourseID=:pupilsightCourseID';
                    $result = $connection2->prepare($sql);
                    $result->execute($data);
                } catch (PDOException $e) {
                    $URL .= '&return=error2';
                    header("Location: {$URL}");
                    exit();
                }

                if ($result->rowCount() != 1) {
                    $URL .= '&copyReturn=error4';
                    header("Location: {$URL}");
                } else {
                    $row = $result->fetch();
                    $partialFail = false;

                    try {
                        $data = array('title' => $row['title'], 'type' => $row['type'], 'length' => $row['length'], 'contents' => $row['contents'], 'teachersNotes' => $row['teachersNotes'], 'pupilsightUnitBlockID' => $pupilsightUnitBlockID, 'pupilsightUnitID' => $pupilsightUnitID);
                        $sql = 'UPDATE pupilsightUnitBlock SET title=:title, type=:type, length=:length, contents=:contents, teachersNotes=:teachersNotes WHERE pupilsightUnitBlockID=:pupilsightUnitBlockID AND pupilsightUnitID=:pupilsightUnitID';
                        $result = $connection2->prepare($sql);
                        $result->execute($data);
                    } catch (PDOException $e) {
                        $partialFail = true;
                    }

                    $working = $_POST['working'];
                    if ($working == 'Y') {
                        try {
                            $data = array('title' => $row['title'], 'type' => $row['type'], 'length' => $row['length'], 'contents' => $row['contents'], 'teachersNotes' => $row['teachersNotes'], 'pupilsightUnitBlockID' => $pupilsightUnitBlockID);
                            $sql = 'UPDATE pupilsightUnitClassBlock SET title=:title, type=:type, length=:length, contents=:contents, teachersNotes=:teachersNotes WHERE pupilsightUnitBlockID=:pupilsightUnitBlockID';
                            $result = $connection2->prepare($sql);
                            $result->execute($data);
                        } catch (PDOException $e) {
                            $partialFail = true;
                        }
                    }

                    //RETURN
                    if ($partialFail == true) {
                        $URL .= '&copyReturn=error6';
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
