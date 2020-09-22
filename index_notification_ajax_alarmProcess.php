<?php
/*
Pupilsight, Flexible & Open School System
*/

//Pupilsight system-wide includes
include './pupilsight.php';

$pupilsightAlarmID = $_GET['pupilsightAlarmID'];
$URL = $_SESSION[$guid]['absoluteURL'].'/index.php';

//Proceed!
if ($pupilsightAlarmID == '') {
    header("Location: {$URL}");
} else {
    //Check alarm
    try {
        $data = array('pupilsightAlarmID' => $pupilsightAlarmID);
        $sql = 'SELECT * FROM pupilsightAlarm WHERE pupilsightAlarmID=:pupilsightAlarmID';
        $result = $connection2->prepare($sql);
        $result->execute($data);
    } catch (PDOException $e) {
    }

    if ($result->rowCount() == 1) {
        $row = $result->fetch();

        //Check confirmation of alarm
        try {
            $dataConfirm = array('pupilsightAlarmID' => $row['pupilsightAlarmID'], 'pupilsightPersonID' => $_SESSION[$guid]['pupilsightPersonID']);
            $sqlConfirm = 'SELECT * FROM pupilsightAlarmConfirm WHERE pupilsightAlarmID=:pupilsightAlarmID AND pupilsightPersonID=:pupilsightPersonID';
            $resultConfirm = $connection2->prepare($sqlConfirm);
            $resultConfirm->execute($dataConfirm);
        } catch (PDOException $e) {
        }

        if ($resultConfirm->rowCount() == 0) {
            //Insert confirmation
            try {
                $dataConfirm = array('pupilsightAlarmID' => $row['pupilsightAlarmID'], 'pupilsightPersonID' => $_SESSION[$guid]['pupilsightPersonID'], 'timestamp' => date('Y-m-d H:i:s'));
                $sqlConfirm = 'INSERT INTO pupilsightAlarmConfirm SET pupilsightAlarmID=:pupilsightAlarmID, pupilsightPersonID=:pupilsightPersonID, timestamp=:timestamp';
                $resultConfirm = $connection2->prepare($sqlConfirm);
                $resultConfirm->execute($dataConfirm);
            } catch (PDOException $e) {
            }
        }
    }

    //Success 0
    header("Location: {$URL}");
}
