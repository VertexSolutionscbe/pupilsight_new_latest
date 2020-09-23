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
    $settings = $_POST['settings'] ?? [];

    //Write to database
    $data = array('value' => serialize($settings));
    $sql = "UPDATE pupilsightSetting SET value=:value WHERE scope='User Admin' AND name='personalDataUpdaterRequiredFields'";

    $updated = $pdo->update($sql, $data);

    if (!$updated) {
        $URL .= '&return=error2';
        header("Location: {$URL}");
    } else {
        $URL .= '&return=success0';
        header("Location: {$URL}");
    }
}
