<?php
/*
Pupilsight, Flexible & Open School System
*/

include '../../pupilsight.php';

$pupilsightAttendanceCodeID = (isset($_GET['pupilsightAttendanceCodeID']))? $_GET['pupilsightAttendanceCodeID'] : NULL;
$URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_POST['address']).'/attendanceSettings_manage_delete.php&pupilsightAttendanceCodeID='.$pupilsightAttendanceCodeID;
$URLDelete = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_POST['address']).'/attendanceSettings.php';

if (isActionAccessible($guid, $connection2, '/modules/School Admin/attendanceSettings_manage_delete.php') == false) {
    $URL .= '&return=error0';
    header("Location: {$URL}");
} else {
    //Proceed!
    //Check if school year specified
    if ($pupilsightAttendanceCodeID == '') {
        $URL .= '&return=error1';
        header("Location: {$URL}");
    } else {
        try {
            $data = array('pupilsightAttendanceCodeID' => $pupilsightAttendanceCodeID);
            $sql = 'SELECT type FROM pupilsightAttendanceCode WHERE pupilsightAttendanceCodeID=:pupilsightAttendanceCodeID';
            $result = $connection2->prepare($sql);
            $result->execute($data);
        } catch (PDOException $e) {
            $URL .= '&return=error2';
            header("Location: {$URL}");
            exit();
        }

        $row = $result->fetch();

        if ($result->rowCount() != 1 || $row['type'] == 'Core') {
            $URL .= '&return=error2';
            header("Location: {$URL}");
        } else {
            //Write to database
            try {
                $data = array('pupilsightAttendanceCodeID' => $pupilsightAttendanceCodeID);
                $sql = 'DELETE FROM pupilsightAttendanceCode WHERE pupilsightAttendanceCodeID=:pupilsightAttendanceCodeID';
                $result = $connection2->prepare($sql);
                $result->execute($data);
            } catch (PDOException $e) {
                $URL .= '&return=error2';
                header("Location: {$URL}");
                exit();
            }

            $URLDelete = $URLDelete.'&return=success0';
            header("Location: {$URLDelete}");
        }
    }
}
