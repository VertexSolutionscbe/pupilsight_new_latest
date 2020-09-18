<?php
/*
Pupilsight, Flexible & Open School System
*/

//Pupilsight system-wide includes
include '../../pupilsight.php';

//Module includes
include './moduleFunctions.php';

$pupilsightPlannerEntryID = $_GET['pupilsightPlannerEntryID'];
$params = '';
if (isset($_GET['date'])) {
    $params = $params.'&date='.$_GET['date'];
}
if (isset($_GET['viewBy'])) {
    $params = $params.'&viewBy='.$_GET['viewBy'];
}
if (isset($_GET['pupilsightCourseClassID'])) {
    $params = $params.'&pupilsightCourseClassID='.$_GET['pupilsightCourseClassID'];
}
$URL = $_SESSION[$guid]['absoluteURL']."/index.php?q=/modules/Planner/planner_view_full.php&pupilsightPlannerEntryID=$pupilsightPlannerEntryID$params";

if (isActionAccessible($guid, $connection2, '/modules/Planner/planner_view_full.php') == false) {
    $URL .= '&return=error0';
    header("Location: {$URL}");
} else {
    //Proceed!
    //Check if planner specified
    if ($pupilsightPlannerEntryID == '') {
        $URL .= '&return=error1';
        header("Location: {$URL}");
    } else {
        try {
            $data = array('pupilsightPlannerEntryID' => $pupilsightPlannerEntryID, 'pupilsightPersonID' => $_SESSION[$guid]['pupilsightPersonID']);
            $sql = "SELECT * FROM pupilsightPlannerEntry JOIN pupilsightCourseClass ON (pupilsightPlannerEntry.pupilsightCourseClassID=pupilsightCourseClass.pupilsightCourseClassID) JOIN pupilsightCourseClassPerson ON (pupilsightCourseClassPerson.pupilsightCourseClassID=pupilsightCourseClass.pupilsightCourseClassID) WHERE pupilsightPlannerEntryID=:pupilsightPlannerEntryID AND pupilsightPersonID=:pupilsightPersonID AND role='Student'";
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
            //Get variables
            $homework = $_POST['homework'];
            if ($_POST['homework'] == 'Yes') {
                $homework = 'Y';
                //Attempt to prevent XSS attack
                $homeworkDetails = $_POST['homeworkDetails'];
                $homeworkDetails = tinymceStyleStripTags($homeworkDetails, $connection2);
                if ($_POST['homeworkDueDateTime'] != '') {
                    $homeworkDueDateTime = $_POST['homeworkDueDateTime'].':59';
                } else {
                    $homeworkDueDateTime = '21:00:00';
                }
                if ($_POST['homeworkDueDate'] != '') {
                    $homeworkDueDate = dateConvert($guid, $_POST['homeworkDueDate']).' '.$homeworkDueDateTime;
                }
            } else {
                $homework = 'N';
                $homeworkDueDate = null;
                $homeworkDetails = '';
            }

            if ($homework == 'N') { //IF HOMEWORK NO, DELETE ANY RECORDS
                try {
                    $data = array('pupilsightPlannerEntryID' => $pupilsightPlannerEntryID, 'pupilsightPersonID' => $_SESSION[$guid]['pupilsightPersonID']);
                    $sql = 'DELETE FROM pupilsightPlannerEntryStudentHomework WHERE pupilsightPlannerEntryID=:pupilsightPlannerEntryID AND pupilsightPersonID=:pupilsightPersonID';
                    $result = $connection2->prepare($sql);
                    $result->execute($data);
                } catch (PDOException $e) {
                    $URL .= '&return=error2';
                    header("Location: {$URL}");
                    exit();
                }

                $URL .= '&return=success0';
                header("Location: {$URL}");
            } else { //IF HOMEWORK YES, DEAL WITH RECORDS
                //Check for record
                try {
                    $data = array('pupilsightPlannerEntryID' => $pupilsightPlannerEntryID, 'pupilsightPersonID' => $_SESSION[$guid]['pupilsightPersonID']);
                    $sql = 'SELECT * FROM pupilsightPlannerEntryStudentHomework WHERE pupilsightPlannerEntryID=:pupilsightPlannerEntryID AND pupilsightPersonID=:pupilsightPersonID';
                    $result = $connection2->prepare($sql);
                    $result->execute($data);
                } catch (PDOException $e) {
                    $URL .= '&return=error2';
                    header("Location: {$URL}");
                    exit();
                }

                if ($result->rowCount() > 1) { //Error!
                            $URL .= '&return=error2';
                    header("Location: {$URL}");
                    exit();
                }
                if ($result->rowCount() == 1) { //Exists, so update
                    try {
                        $data = array('pupilsightPlannerEntryID' => $pupilsightPlannerEntryID, 'pupilsightPersonID' => $_SESSION[$guid]['pupilsightPersonID'], 'homeworkDueDateTime' => $homeworkDueDate, 'homeworkDetails' => $homeworkDetails);
                        $sql = 'UPDATE pupilsightPlannerEntryStudentHomework SET homeworkDueDateTime=:homeworkDueDateTime, homeworkDetails=:homeworkDetails WHERE pupilsightPlannerEntryID=:pupilsightPlannerEntryID AND pupilsightPersonID=:pupilsightPersonID';
                        $result = $connection2->prepare($sql);
                        $result->execute($data);
                    } catch (PDOException $e) {
                        $URL .= '&return=error2';
                        header("Location: {$URL}");
                        exit();
                    }

                    $URL .= '&return=success0';
                    header("Location: {$URL}");
                } else { //Does not exist, so create
                    //Write to database
                    try {
                        $data = array('pupilsightPlannerEntryID' => $pupilsightPlannerEntryID, 'pupilsightPersonID' => $_SESSION[$guid]['pupilsightPersonID'], 'homeworkDueDateTime' => $homeworkDueDate, 'homeworkDetails' => $homeworkDetails);
                        $sql = 'INSERT INTO pupilsightPlannerEntryStudentHomework SET pupilsightPlannerEntryID=:pupilsightPlannerEntryID, pupilsightPersonID=:pupilsightPersonID, homeworkDueDateTime=:homeworkDueDateTime, homeworkDetails=:homeworkDetails';
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
