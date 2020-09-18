<?php
/*
Pupilsight, Flexible & Open School System
*/

include '../../pupilsight.php';

$URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/System Admin/alarm.php';

if (isActionAccessible($guid, $connection2, '/modules/System Admin/alarm.php') == false) {
    $URL .= '&return=error0';
    header("Location: {$URL}");
} else {
    //Proceed!
    $pupilsightAlarmID = '';
    if (isset($_GET['pupilsightAlarmID'])) {
        $pupilsightAlarmID = $_GET['pupilsightAlarmID'];
    }

    //Validate Inputs
    if ($pupilsightAlarmID == '') {
        $URL .= '&return=error3';
        header("Location: {$URL}");
    } else {
        $fail = false;

        //DEAL WITH ALARM SETTING
        //Write setting to database
        try {
            $data = array();
            $sql = "UPDATE pupilsightSetting SET value='None' WHERE scope='System' AND name='alarm'";
            $result = $connection2->prepare($sql);
            $result->execute($data);
        } catch (PDOException $e) {
            $fail = true;
        }

        //Deal with alarm record
        try {
            $data = array('timestampEnd' => date('Y-m-d H:i:s'), 'pupilsightAlarmID' => $pupilsightAlarmID);
            $sql = "UPDATE pupilsightAlarm SET status='Past', timestampEnd=:timestampEnd WHERE pupilsightAlarmID=:pupilsightAlarmID";
            $result = $connection2->prepare($sql);
            $result->execute($data);
        } catch (PDOException $e) {
            $fail = true;
        }

        if ($fail == true) {
            $URL .= '&return=error2';
            header("Location: {$URL}");
        } else {
            getSystemSettings($guid, $connection2);
            $URL .= '&return=success0';
            header("Location: {$URL}");
        }
    }
}
