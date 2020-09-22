<?php
/*
Pupilsight, Flexible & Open School System
*/

//Pupilsight system-wide includes
include '../../pupilsight.php';

//Module includes
include './moduleFunctions.php';

$pupilsightPersonID = $_GET['pupilsightPersonID'];
$pupilsightAttendanceLogPersonID = $_GET['pupilsightAttendanceLogPersonID'];
$URL = $_SESSION[$guid]['absoluteURL']."/index.php?q=/modules/Attendance/attendance_future_byPerson.php&pupilsightPersonID=$pupilsightPersonID";

if (isActionAccessible($guid, $connection2, '/modules/Attendance/attendance_future_byPerson.php') == false) {
    $URL .= '&return=error0';
    header("Location: {$URL}");
} else {
    //Proceed!
    //Check if planner specified
    if ($pupilsightPersonID == '' or $pupilsightAttendanceLogPersonID == '') {
        $URL .= '&return=error1';
        header("Location: {$URL}");
    } else {
        //UPDATE
        try {
            $data = array('pupilsightPersonID' => $pupilsightPersonID, 'pupilsightAttendanceLogPersonID' => $pupilsightAttendanceLogPersonID);
            $sql = 'DELETE FROM pupilsightAttendanceLogPerson WHERE pupilsightPersonID=:pupilsightPersonID AND pupilsightAttendanceLogPersonID=:pupilsightAttendanceLogPersonID';
            $result = $connection2->prepare($sql);
            $result->execute($data);
        } catch (PDOException $e) {
            $URL .= '&return=error2';
            header("Location: {$URL}");
            exit();
        }

        //Success 0
        $URL .= '&return=success0';
        header("Location: {$URL}");
    }
}
