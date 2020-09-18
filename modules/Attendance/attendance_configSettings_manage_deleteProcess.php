<?php
/*
Pupilsight, Flexible & Open School System
*/

include '../../pupilsight.php';

$id = $_GET['id'];

$URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_POST['address']).'/attendance_configSettings.php';

if (isActionAccessible($guid, $connection2, '/modules/Attendance/attendance_configSettings_manage_delete.php') == false) {
    $URL .= '&return=error0';
    header("Location: {$URL}");
} else {
    //Proceed!
    //Check if school year specified
    if ($id == '') {
        $URL .= '&return=error1';
        header("Location: {$URL}");
    } else {
        try {
            $data = array('id' => $id);
            $sql = 'SELECT * FROM attn_settings WHERE id=:id';
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
            //Write to database
            try {
                $data = array('id' => $id);
                $sql = 'DELETE FROM attn_settings WHERE id=:id';
                $result = $connection2->prepare($sql);
                $result->execute($data);

                $data2 = array('attn_settings_id' => $id);
                $sql2 = 'DELETE FROM attn_session_settings WHERE attn_settings_id=:attn_settings_id';
                $result2 = $connection2->prepare($sql2);
                $result2->execute($data2);
            } catch (PDOException $e) {
                $URL .= '&return=error2';
                header("Location: {$URL}");
                exit();
            }

            $URL = $URL.'&return=success0';
            header("Location: {$URL}");
        }
    }
}
