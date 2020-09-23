<?php
/*
Pupilsight, Flexible & Open School System
*/

include '../../pupilsight.php';

$URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_POST['address']).'/dashboardSettings.php';

if (isActionAccessible($guid, $connection2, '/modules/School Admin/dashboardSettings.php') == false) {
    $URL .= '&return=error0';
    header("Location: {$URL}");
} else {
    //Proceed!
    $staffDashboardDefaultTab = $_POST['staffDashboardDefaultTab'];
    $studentDashboardDefaultTab = $_POST['studentDashboardDefaultTab'];
    $parentDashboardDefaultTab = $_POST['parentDashboardDefaultTab'];

    //Write to database
    $fail = false;

    try {
        $data = array('value' => $staffDashboardDefaultTab);
        $sql = "UPDATE pupilsightSetting SET value=:value WHERE scope='School Admin' AND name='staffDashboardDefaultTab'";
        $result = $connection2->prepare($sql);
        $result->execute($data);
    } catch (PDOException $e) {
        $fail = true;
    }

    try {
        $data = array('value' => $studentDashboardDefaultTab);
        $sql = "UPDATE pupilsightSetting SET value=:value WHERE scope='School Admin' AND name='studentDashboardDefaultTab'";
        $result = $connection2->prepare($sql);
        $result->execute($data);
    } catch (PDOException $e) {
        $fail = true;
    }

    try {
        $data = array('value' => $parentDashboardDefaultTab);
        $sql = "UPDATE pupilsightSetting SET value=:value WHERE scope='School Admin' AND name='parentDashboardDefaultTab'";
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
