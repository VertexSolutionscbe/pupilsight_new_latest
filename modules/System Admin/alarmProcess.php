<?php
/*
Pupilsight, Flexible & Open School System
*/

include '../../pupilsight.php';

$URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_POST['address']).'/alarm.php';

if (isActionAccessible($guid, $connection2, '/modules/System Admin/alarm.php') == false) {
    $URL .= '&return=error0';
    header("Location: {$URL}");
} else {
    //Proceed!
    $alarm = $_POST['alarm'];
    $attachmentCurrent = $_POST['attachmentCurrent'];
    $alarmCurrent = $_POST['alarmCurrent'];

    //Validate Inputs
    if ($alarm != 'None' and $alarm != 'General' and $alarm != 'Lockdown' and $alarm != 'Custom' and $alarmCurrent != '') {
        $URL .= '&return=error3';
        header("Location: {$URL}");
    } else {
        $fail = false;

        //DEAL WITH CUSTOM SOUND SETTING
        $time = time();
        //Move attached file, if there is one
        if (!empty($_FILES['file']['tmp_name'])) {
            $fileUploader = new Pupilsight\FileUploader($pdo, $pupilsight->session);
                
            $file = (isset($_FILES['file']))? $_FILES['file'] : null;

            // Upload the file, return the /uploads relative path
            $attachment = $fileUploader->uploadFromPost($file, 'alarmSound');
                
            if (empty($attachment)) {
                $URL .= '&return=error1';
                header("Location: {$URL}");
                exit;
            }
        } else {
            $attachment = $attachmentCurrent;
        }

        //Write setting to database
        try {
            $data = array('value' => $attachment);
            $sql = "UPDATE pupilsightSetting SET value=:value WHERE scope='System Admin' AND name='customAlarmSound'";
            $result = $connection2->prepare($sql);
            $result->execute($data);
        } catch (PDOException $e) {
            $fail = true;
        }

        //DEAL WITH ALARM SETTING
        //Write setting to database
        try {
            $data = array('alarm' => $alarm);
            $sql = "UPDATE pupilsightSetting SET value=:alarm WHERE scope='System' AND name='alarm'";
            $result = $connection2->prepare($sql);
            $result->execute($data);
        } catch (PDOException $e) {
            $fail = true;
        }

        //Check for existing alarm
        $checkFail = false;
        try {
            $data = array();
            $sql = "SELECT * FROM pupilsightAlarm WHERE status='Current'";
            $result = $connection2->prepare($sql);
            $result->execute($data);
        } catch (PDOException $e) {
            $checkFail = true;
        }

        //Alarm is being turned on, so insert new record
        if ($alarm == 'General' or $alarm == 'Lockdown' or $alarm == 'Custom') {
            if ($checkFail == true) {
                $fail = true;
            } else {
                if ($result->rowCount() == 0) {
                    //Write alarm to database
                    try {
                        $data = array('type' => $alarm, 'pupilsightPersonID' => $_SESSION[$guid]['pupilsightPersonID'], 'timestampStart' => date('Y-m-d H:i:s'));
                        $sql = "INSERT INTO pupilsightAlarm SET type=:type, status='Current', pupilsightPersonID=:pupilsightPersonID, timestampStart=:timestampStart";
                        $result = $connection2->prepare($sql);
                        $result->execute($data);
                    } catch (PDOException $e) {
                        $fail = true;
                    }
                } else {
                    $row = $result->fetch();
                    try {
                        $data = array('type' => $alarm, 'pupilsightAlarmID' => $row['pupilsightAlarmID']);
                        $sql = 'UPDATE pupilsightAlarm SET type=:type WHERE pupilsightAlarmID=:pupilsightAlarmID';
                        $result = $connection2->prepare($sql);
                        $result->execute($data);
                    } catch (PDOException $e) {
                        $fail = true;
                    }
                }
            }
        } elseif ($alarmCurrent != $alarm) {
            if ($result->rowCount() == 1) {
                $row = $result->fetch();
                try {
                    $data = array('timestampEnd' => date('Y-m-d H:i:s'), 'pupilsightAlarmID' => $row['pupilsightAlarmID']);
                    $sql = "UPDATE pupilsightAlarm SET status='Past', timestampEnd=:timestampEnd WHERE pupilsightAlarmID=:pupilsightAlarmID";
                    $result = $connection2->prepare($sql);
                    $result->execute($data);
                } catch (PDOException $e) {
                    $fail = true;
                }
            } else {
                $fail = true;
            }
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
