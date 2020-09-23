<?php
/*
Pupilsight, Flexible & Open School System
*/

include '../../pupilsight.php';

$pupilsightSchoolYearID = $_GET['pupilsightSchoolYearID'];
$pupilsightCourseID = $_GET['pupilsightCourseID'];
$pupilsightCourseClassID = $_GET['pupilsightCourseClassID'];
$pupilsightUnitID = $_GET['pupilsightUnitID'];
$pupilsightUnitClassID = $_GET['pupilsightUnitClassID'];
$orders = $_POST['order'];

$URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_GET['address'])."/units_edit_working.php&pupilsightSchoolYearID=$pupilsightSchoolYearID&pupilsightCourseID=$pupilsightCourseID&pupilsightUnitID=$pupilsightUnitID&pupilsightCourseClassID=$pupilsightCourseClassID&pupilsightUnitClassID=$pupilsightUnitClassID";

if (isActionAccessible($guid, $connection2, '/modules/Planner/units_edit_working.php') == false) {
    $URL .= '&return=error0';
    header("Location: {$URL}");
} else {
    $highestAction = getHighestGroupedAction($guid, $_GET['address'], $connection2);
    if ($highestAction == false) {
        $URL .= "&return=error0$params";
        header("Location: {$URL}");
    } else {
        //Proceed!
        //Validate Inputs
        if ($pupilsightSchoolYearID == '' or $pupilsightCourseID == '' or $pupilsightUnitID == '' or $orders == '') {
            $URL .= '&return=error3';
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
                    $sql = 'SELECT pupilsightCourse.nameShort AS courseName, pupilsightUnit.* FROM pupilsightUnit JOIN pupilsightCourse ON (pupilsightUnit.pupilsightCourseID=pupilsightCourse.pupilsightCourseID) WHERE pupilsightUnitID=:pupilsightUnitID AND pupilsightUnit.pupilsightCourseID=:pupilsightCourseID';
                    $result = $connection2->prepare($sql);
                    $result->execute($data);
                } catch (PDOException $e) {
                    $URL .= '&deployReturn=error2';
                    header("Location: {$URL}");
                    exit();
                }

                if ($result->rowCount() != 1) {
                    $URL .= '&return=error3';
                    header("Location: {$URL}");
                } else {
                    $row = $result->fetch();

                    //Remove all blocks
                    try {
                        $data = array('pupilsightUnitClassID' => $pupilsightUnitClassID);
                        $sql = 'DELETE FROM pupilsightUnitClassBlock WHERE pupilsightUnitClassID=:pupilsightUnitClassID';
                        $result = $connection2->prepare($sql);
                        $result->execute($data);
                    } catch (PDOException $e) {
                        $URL .= '&return=error2';
                        header("Location: {$URL}");
                        exit();
                    }

                    $partialFail = false;

                    $lessonCount = 0;
                    $lessonDescriptions = array();
                    $sequenceNumber = 0;
                    foreach ($orders as $order) {
                        //It is a lesson, get pupilsightPlannerID
                        if (strpos($order, 'lessonHeader-') !== false) {
                            $AI = $_POST["pupilsightPlannerEntryID$lessonCount"];
                            $lessonDescriptions[$_POST['pupilsightPlannerEntryID'.$lessonCount]][0] = $_POST['pupilsightPlannerEntryID'.$lessonCount];
                            $lessonDescriptions[$_POST['pupilsightPlannerEntryID'.$lessonCount]][1] = '';
                            ++$lessonCount;
                        }
                        //It is a block, so add it to the last added lesson
                        else {
                            $titles = $_POST['title'.$order];
                            $lessonDescriptions[$_POST['pupilsightPlannerEntryID'.($lessonCount - 1)]][1] .= $_POST['title'.$order].', ';
                            $types = $_POST['type'.$order];
                            $lengths = $_POST['length'.$order];
                            $completes = null;
                            if (isset($_POST['complete'.$order])) {
                                $completes = $_POST['complete'.$order];
                            }
                            if ($completes == 'on') {
                                $completes = 'Y';
                            } else {
                                $completes = 'N';
                            }
                            $contents = $_POST['contents'.$order];
                            $teachersNotes = $_POST['teachersNotes'.$order];
                            $pupilsightUnitBlockID = $_POST['pupilsightUnitBlockID'.$order];

                            try {
                                $data = array('pupilsightUnitClassID' => $pupilsightUnitClassID, 'pupilsightPlannerEntryID' => $AI, 'pupilsightUnitBlockID' => $pupilsightUnitBlockID, 'title' => $titles, 'type' => $types, 'length' => $lengths, 'complete' => $completes, 'contents' => $contents, 'teachersNotes' => $teachersNotes, 'sequenceNumber' => $sequenceNumber);
                                $sql = 'INSERT INTO pupilsightUnitClassBlock SET pupilsightUnitClassID=:pupilsightUnitClassID, pupilsightPlannerEntryID=:pupilsightPlannerEntryID, pupilsightUnitBlockID=:pupilsightUnitBlockID, title=:title, type=:type, length=:length, complete=:complete, contents=:contents, teachersNotes=:teachersNotes, sequenceNumber=:sequenceNumber';
                                $result = $connection2->prepare($sql);
                                $result->execute($data);
                            } catch (PDOException $e) {
                                $partialFail = true;
                            }
                            ++$sequenceNumber;
                        }
                    }

                    //Update lesson description
                    foreach ($lessonDescriptions as $lessonDescription) {
                        $lessonDescription[1] = substr($lessonDescription[1], 0, -2);
                        if (strlen($lessonDescription[1]) > 75) {
                            $lessonDescription[1] = substr($lessonDescription[1], 0, 72).'...';
                        }
                        try {
                            $data = array('summary' => $lessonDescription[1], 'pupilsightPlannerEntryID' => $lessonDescription[0]);
                            $sql = 'UPDATE pupilsightPlannerEntry SET summary=:summary WHERE pupilsightPlannerEntryID=:pupilsightPlannerEntryID';
                            $result = $connection2->prepare($sql);
                            $result->execute($data);
                        } catch (PDOException $e) {
                            $partialFail = true;
                        }
                    }

                    //RETURN
                    if ($partialFail == true) {
                        $URL .= '&updateReturn=error6';
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
