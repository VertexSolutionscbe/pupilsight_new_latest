<?php
/*
Pupilsight, Flexible & Open School System
*/

//Pupilsight system-wide includes
include '../../pupilsight.php';

//Module includes
include './moduleFunctions.php';

$pupilsightPlannerEntryID = $_GET['pupilsightPlannerEntryID'];
$pupilsightPlannerEntryHomeworkID = $_GET['pupilsightPlannerEntryHomeworkID'];
$date = $_GET['date'];
$pupilsightCourseClassID = $_GET['pupilsightCourseClassID'];
$viewBy = $_GET['viewBy'];
$subView = $_GET['subView'];
$search = null;
if (isset($_POST['search'])) {
    $search = $_POST['search'];
}
$URL = $_SESSION[$guid]['absoluteURL']."/index.php?q=/modules/Planner/planner_view_full.php&date=$date&viewBy=$viewBy&subView=$subView&pupilsightCourseClassID=$pupilsightCourseClassID&pupilsightPlannerEntryID=$pupilsightPlannerEntryID&search=$search";

if (isActionAccessible($guid, $connection2, '/modules/Planner/planner_view_full.php') == false) {
    $URL .= '&return=error0';
    header("Location: {$URL}");
} else {
    //Proceed!
    //Check if planner specified
    if ($pupilsightPlannerEntryID == '' or $pupilsightPlannerEntryHomeworkID == '') {
        $URL .= '&return=error1';
        header("Location: {$URL}");
    } else {
        try {
            $data = array('pupilsightPlannerEntryID' => $pupilsightPlannerEntryID);
            $sql = 'SELECT * FROM pupilsightPlannerEntry WHERE pupilsightPlannerEntryID=:pupilsightPlannerEntryID';
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
            //INSERT
            try {
                $data = array('pupilsightPlannerEntryHomeworkID' => $pupilsightPlannerEntryHomeworkID, 'pupilsightPersonID' => $_SESSION[$guid]['pupilsightPersonID']);
                $sql = 'DELETE FROM pupilsightPlannerEntryHomework WHERE pupilsightPlannerEntryHomeworkID=:pupilsightPlannerEntryHomeworkID AND pupilsightPersonID=:pupilsightPersonID';
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
