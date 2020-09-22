<?php
/*
Pupilsight, Flexible & Open School System
*/

//Pupilsight system-wide includes
include '../../pupilsight.php';

//Module includes
include './moduleFunctions.php';

$pupilsightAttendanceLogPersonID = isset($_POST['pupilsightAttendanceLogPersonID'])? $_POST['pupilsightAttendanceLogPersonID'] : '';
$pupilsightPersonID = isset($_POST['pupilsightPersonID'])? $_POST['pupilsightPersonID'] : '';
$currentDate = isset($_POST['currentDate'])? $_POST['currentDate'] : dateConvertBack($guid, date('Y-m-d'));

$URL = $_SESSION[$guid]['absoluteURL']."/index.php?q=/modules/Attendance/attendance_take_byPerson.php&pupilsightPersonID=$pupilsightPersonID&currentDate=$currentDate";

if (isActionAccessible($guid, $connection2, '/modules/Attendance/attendance_take_byPerson_edit.php') == false) {
    $URL .= '&return=error0';
    header("Location: {$URL}");
}
else if ($pupilsightAttendanceLogPersonID == '' or $pupilsightPersonID == '' or $currentDate == '') {
    $URL .= '&return=error1';
    header("Location: {$URL}");
} else {
    //Proceed!
    
    $type = isset($_POST['type'])? $_POST['type'] : '';
    $reason = isset($_POST['reason'])? $_POST['reason'] : '';
    $comment = isset($_POST['comment'])? $_POST['comment'] : '';

    // Get attendance codes
    try {
        $dataCode = array( 'name' => $type );
        $sqlCode = "SELECT direction FROM pupilsightAttendanceCode WHERE active = 'Y' AND name=:name LIMIT 1";
        $resultCode = $connection2->prepare($sqlCode);
        $resultCode->execute($dataCode);
    } catch (PDOException $e) {
        echo "<div class='alert alert-danger'>".$e->getMessage().'</div>';
    }

    if ($resultCode->rowCount() != 1) {
        $URL .= '&return=error1';
        header("Location: {$URL}");
        die();
    }

    $attendanceCode = $resultCode->fetch();
    $direction = $attendanceCode['direction'];

    //Check if values specified
    if ($type == '' || $direction == '') {
        $URL .= '&return=error1';
        header("Location: {$URL}");
    } else {

        //UPDATE
        try {
            $data = array('pupilsightPersonID' => $pupilsightPersonID, 'pupilsightAttendanceLogPersonID' => $pupilsightAttendanceLogPersonID, 'type' => $type, 'reason' => $reason, 'comment' => $comment, 'direction' => $direction, 'pupilsightPersonIDTaker' => $_SESSION[$guid]['pupilsightPersonID'] );
            $sql = 'UPDATE pupilsightAttendanceLogPerson SET pupilsightAttendanceCodeID=(SELECT pupilsightAttendanceCodeID FROM pupilsightAttendanceCode WHERE name=:type), type=:type, reason=:reason, comment=:comment, direction=:direction, pupilsightPersonIDTaker=:pupilsightPersonIDTaker, timestampTaken=NOW() WHERE pupilsightPersonID=:pupilsightPersonID AND pupilsightAttendanceLogPersonID=:pupilsightAttendanceLogPersonID';
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
