<?php
/*
Pupilsight, Flexible & Open School System
*/

include '../../pupilsight.php';

$viewBy = $_GET['viewBy'];
$subView = $_GET['subView'];
if ($viewBy != 'date' and $viewBy != 'class') {
    $viewBy = 'date';
}
$pupilsightCourseClassID = null;
if (isset($_POST['pupilsightCourseClassID'])) {
    $pupilsightCourseClassID = $_POST['pupilsightCourseClassID'];
}
$date = null;
if (isset($_POST['date'])) {
    $date = dateConvert($guid, $_POST['date']);
}
$pupilsightCourseClassIDFilter = null;
if (isset($_GET['pupilsightCourseClassIDFilter'])) {
    $pupilsightCourseClassIDFilter = $_GET['pupilsightCourseClassIDFilter'];
}

$URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_GET['address'])."/planner_deadlines.php&pupilsightCourseClassIDFilter=$pupilsightCourseClassIDFilter";

//Params to pass back (viewBy + date or classID)
if ($viewBy == 'date') {
    $params = "&viewBy=$viewBy&date=$date";
} else {
    $params = "&viewBy=$viewBy&pupilsightCourseClassID=$pupilsightCourseClassID&subView=$subView";
}

if (isActionAccessible($guid, $connection2, '/modules/Planner/planner_deadlines.php') == false) { echo 'gere';
    $URL .= "&return=error0$params";
    header("Location: {$URL}");
} else {
    $category = getRoleCategory($_SESSION[$guid]['pupilsightRoleIDCurrent'], $connection2);
    if ($category != 'Student') {
        $URL .= "&return=error0$params";
        header("Location: {$URL}");
    } else {
        //Check for existing completion
        $completionArray = array();
        try {
            $dataCompletion = array('pupilsightSchoolYearID' => $_SESSION[$guid]['pupilsightSchoolYearID'], 'pupilsightPersonID' => $_SESSION[$guid]['pupilsightPersonID'], 'pupilsightSchoolYearID2' => $_SESSION[$guid]['pupilsightSchoolYearID'], 'pupilsightPersonID2' => $_SESSION[$guid]['pupilsightPersonID']);
            $sqlCompletion = "
			(SELECT 'teacherRecorded' AS type, pupilsightPlannerEntryStudentTracker.pupilsightPlannerEntryID FROM pupilsightPlannerEntryStudentTracker JOIN pupilsightPlannerEntry ON (pupilsightPlannerEntryStudentTracker.pupilsightPlannerEntryID=pupilsightPlannerEntry.pupilsightPlannerEntryID) JOIN pupilsightCourseClass ON (pupilsightPlannerEntry.pupilsightCourseClassID=pupilsightCourseClass.pupilsightCourseClassID) JOIN pupilsightCourse ON (pupilsightCourseClass.pupilsightCourseID=pupilsightCourse.pupilsightCourseID) WHERE pupilsightSchoolYearID=:pupilsightSchoolYearID AND pupilsightPersonID=:pupilsightPersonID AND homeworkComplete='Y')
			UNION
			(SELECT 'studentRecorded' AS type, pupilsightPlannerEntry.pupilsightPlannerEntryID FROM pupilsightPlannerEntryStudentHomework JOIN pupilsightPlannerEntry ON (pupilsightPlannerEntryStudentHomework.pupilsightPlannerEntryID=pupilsightPlannerEntry.pupilsightPlannerEntryID) JOIN pupilsightCourseClass ON (pupilsightPlannerEntry.pupilsightCourseClassID=pupilsightCourseClass.pupilsightCourseClassID) JOIN pupilsightCourse ON (pupilsightCourseClass.pupilsightCourseID=pupilsightCourse.pupilsightCourseID) WHERE pupilsightSchoolYearID=:pupilsightSchoolYearID2 AND pupilsightPersonID=:pupilsightPersonID2 AND homeworkComplete='Y')
			ORDER BY pupilsightPlannerEntryID, type
			";
            $resultCompletion = $connection2->prepare($sqlCompletion);
            $resultCompletion->execute($dataCompletion);
        } catch (PDOException $e) {
            $URL .= "&return=error2$params";
            header("Location: {$URL}");
            exit();
        }

        while ($rowCompletion = $resultCompletion->fetch()) {
            if (isset($rowCompletion['pupilsightPlannerEntryID'])) {
                $completionArray[$rowCompletion['pupilsightPlannerEntryID']] = 'Y';
            }
        }

        $partialFail = false;

        //Insert new records
        foreach ($_POST['count'] as $count) {
            if (isset($_POST["complete-$count"])) {
                if ($_POST["complete-$count"] == 'on') {
                    if (isset($completionArray[$_POST["pupilsightPlannerEntryID-$count"]]) == false) {
                        if (@$_POST["completeType-$count"] == 'teacherRecorded') { //Teacher recorded
                            try {
                                $data = array('pupilsightPlannerEntryID' => $_POST["pupilsightPlannerEntryID-$count"], 'pupilsightPersonID' => $_SESSION[$guid]['pupilsightPersonID']);
                                $sql = "INSERT INTO pupilsightPlannerEntryStudentTracker SET pupilsightPlannerEntryID=:pupilsightPlannerEntryID, pupilsightPersonID=:pupilsightPersonID, homeworkComplete='Y'";
                                $result = $connection2->prepare($sql);
                                $result->execute($data);
                            } catch (PDOException $e) {
                                $partialFail = true;
                            }
                        } else { //Student recorded
                            try {
                                $data = array('pupilsightPlannerEntryID' => $_POST["pupilsightPlannerEntryID-$count"], 'pupilsightPersonID' => $_SESSION[$guid]['pupilsightPersonID']);
                                $sql = "UPDATE pupilsightPlannerEntryStudentHomework SET homeworkComplete='Y' WHERE pupilsightPlannerEntryID=:pupilsightPlannerEntryID AND pupilsightPersonID=:pupilsightPersonID";
                                $result = $connection2->prepare($sql);
                                $result->execute($data);
                            } catch (PDOException $e) {
                                $partialFail = true;
                            }
                        }
                    }
                }
            }
        }

        //Turn unchecked records off
        foreach ($_POST['count'] as $count) {
            if (isset($completionArray[$_POST["pupilsightPlannerEntryID-$count"]])) {
                if ($completionArray[$_POST["pupilsightPlannerEntryID-$count"]] == 'Y') {
                    if (isset($_POST["complete-$count"]) == false) {
                        if (@$_POST["completeType-$count"] == 'teacherRecorded') { //Teacher recorded
                            try {
                                $data = array('pupilsightPlannerEntryID' => $_POST["pupilsightPlannerEntryID-$count"], 'pupilsightPersonID' => $_SESSION[$guid]['pupilsightPersonID']);
                                $sql = "UPDATE pupilsightPlannerEntryStudentTracker SET homeworkComplete='N' WHERE pupilsightPlannerEntryID=:pupilsightPlannerEntryID AND pupilsightPersonID=:pupilsightPersonID";
                                $result = $connection2->prepare($sql);
                                $result->execute($data);
                            } catch (PDOException $e) {
                                $partialFail = true;
                            }
                        } else { //Student recorded
                            try {
                                $data = array('pupilsightPlannerEntryID' => $_POST["pupilsightPlannerEntryID-$count"], 'pupilsightPersonID' => $_SESSION[$guid]['pupilsightPersonID']);
                                $sql = "UPDATE pupilsightPlannerEntryStudentHomework SET homeworkComplete='N' WHERE pupilsightPlannerEntryID=:pupilsightPlannerEntryID AND pupilsightPersonID=:pupilsightPersonID";
                                $result = $connection2->prepare($sql);
                                $result->execute($data);
                            } catch (PDOException $e) {
                                $partialFail = true;
                            }
                        }
                    }
                }
            }
        }

        if ($partialFail == true) {
            $URL .= "&return=warning1$params";
            header("Location: {$URL}");
        } else {
            $URL .= "&return=success0$params";
            header("Location: {$URL}");
        }
    }
}
