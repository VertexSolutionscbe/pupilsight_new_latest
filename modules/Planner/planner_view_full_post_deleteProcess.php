<?php
/*
Pupilsight, Flexible & Open School System
*/

//Pupilsight system-wide includes
include '../../pupilsight.php';

//Module includes
include './moduleFunctions.php';

$pupilsightPlannerEntryID = $_GET['pupilsightPlannerEntryID'];
$pupilsightPlannerEntryDiscussID = $_GET['pupilsightPlannerEntryDiscussID'];
$date = $_GET['date'];
$pupilsightCourseClassID = $_GET['pupilsightCourseClassID'];
$viewBy = $_GET['viewBy'];
$subView = $_GET['subView'];
$URL = $_SESSION[$guid]['absoluteURL']."/index.php?q=/modules/Planner/planner_view_full.php&pupilsightPlannerEntryID=$pupilsightPlannerEntryID&search=".$_GET['search']."&date=$date&viewBy=$viewBy&subView=$subView&pupilsightCourseClassID=$pupilsightCourseClassID";

if (isActionAccessible($guid, $connection2, '/modules/Planner/planner_view_full.php') == false) {
    $URL .= '&return=error0';
    header("Location: {$URL}");
} else {
    //Proceed!
    //Check if planner specified
    if ($pupilsightPlannerEntryID == '' or $pupilsightPlannerEntryDiscussID == '') {
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
                $data = array('pupilsightPlannerEntryDiscussID' => $pupilsightPlannerEntryDiscussID);
                $sql = 'DELETE FROM pupilsightPlannerEntryDiscuss WHERE pupilsightPlannerEntryDiscussID=:pupilsightPlannerEntryDiscussID';
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
