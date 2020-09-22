<?php
/*
Pupilsight, Flexible & Open School System
*/

//Pupilsight system-wide includes
include '../../pupilsight.php';

//Module includes
include './moduleFunctions.php';

$pupilsightAttendanceLogPersonID = isset($_GET['pupilsightAttendanceLogPersonID'])? $_GET['pupilsightAttendanceLogPersonID'] : '';
$pupilsightPersonID = isset($_GET['pupilsightPersonID'])? $_GET['pupilsightPersonID'] : '';
$currentDate = isset($_GET['currentDate'])? $_GET['currentDate'] : '';

$URL = $_SESSION[$guid]['absoluteURL']."/index.php?q=/modules/Attendance/attendance_take_byPerson.php&pupilsightPersonID=$pupilsightPersonID&currentDate=$currentDate";

if (isActionAccessible($guid, $connection2, '/modules/Attendance/attendance_take_byPerson_delete.php') == false) {
    $URL .= '&return=error0';
    header("Location: {$URL}");
}
else if ($pupilsightAttendanceLogPersonID == '' or $pupilsightPersonID == '' or $currentDate == '') {
    $URL .= '&return=error1';
    header("Location: {$URL}");
} else {
    //Proceed!
    try {
        $data = array('pupilsightPersonID' => $pupilsightPersonID, 'pupilsightAttendanceLogPersonID' => $pupilsightAttendanceLogPersonID);
        $sql = "DELETE FROM pupilsightAttendanceLogPerson WHERE pupilsightPersonID=:pupilsightPersonID AND pupilsightAttendanceLogPersonID=:pupilsightAttendanceLogPersonID";
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
