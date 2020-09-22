<?php
/*
Pupilsight, Flexible & Open School System
*/

include '../../pupilsight.php';

$URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_POST['address']).'/dataUpdaterSettings.php';

if (isActionAccessible($guid, $connection2, '/modules/User Admin/dataUpdaterSettings.php') == false) {
    $URL .= '&return=error0';
    header("Location: {$URL}");
} else {
    //Proceed!
   

    //Write to database
    $fail = false;

    $requiredUpdates = (isset($_POST['requiredUpdates'])) ? $_POST['requiredUpdates']  : 'N';
    try {
        $data = array('value' => $requiredUpdates);
        $sql = "UPDATE pupilsightSetting SET value=:value WHERE scope='Data Updater' AND name='requiredUpdates'";
        $result = $connection2->prepare($sql);
        $result->execute($data);
    } catch (PDOException $e) {
        $fail = true;
    }

    $requiredUpdatesByType = (isset($_POST['requiredUpdatesByType'])) ? implode(',', $_POST['requiredUpdatesByType'])  : '';
    try {
        $data = array('value' => $requiredUpdatesByType);
        $sql = "UPDATE pupilsightSetting SET value=:value WHERE scope='Data Updater' AND name='requiredUpdatesByType'";
        $result = $connection2->prepare($sql);
        $result->execute($data);
    } catch (PDOException $e) {
        $fail = true;
    }

    $cutoffDate = (isset($_POST['cutoffDate'])) ? dateConvert($guid, $_POST['cutoffDate'])  : NULL;
    try {
        $data = array('value' => $cutoffDate);
        $sql = "UPDATE pupilsightSetting SET value=:value WHERE scope='Data Updater' AND name='cutoffDate'";
        $result = $connection2->prepare($sql);
        $result->execute($data);
    } catch (PDOException $e) {
        $fail = true;
    }

    $redirectByRoleCategory = (isset($_POST['redirectByRoleCategory'])) ? implode(',', $_POST['redirectByRoleCategory']) : '';
    try {
        $data = array('value' => $redirectByRoleCategory);
        $sql = "UPDATE pupilsightSetting SET value=:value WHERE scope='Data Updater' AND name='redirectByRoleCategory'";
        $result = $connection2->prepare($sql);
        $result->execute($data);
    } catch (PDOException $e) {
        $fail = true;
    }

    if ($fail == true) {
        $URL .= '&return=error2';
        header("Location: {$URL}");
    } else {
        //Success 0
        getSystemSettings($guid, $connection2);
        $URL .= '&return=success0';
        header("Location: {$URL}");
    }
}
