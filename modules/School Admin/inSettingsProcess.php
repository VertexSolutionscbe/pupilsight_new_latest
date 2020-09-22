<?php
/*
Pupilsight, Flexible & Open School System
*/

include '../../pupilsight.php';

$URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_POST['address']).'/inSettings.php';

if (isActionAccessible($guid, $connection2, '/modules/School Admin/inSettings.php') == false) {
    $URL .= '&return=error0';
    header("Location: {$URL}");
} else {
    //Proceed!
    $targetsTemplate = $_POST['targetsTemplate'];
    $teachingStrategiesTemplate = $_POST['teachingStrategiesTemplate'];
    $notesReviewTemplate = $_POST['notesReviewTemplate'];

    //Write to database
    $fail = false;

    try {
        $data = array('value' => $targetsTemplate);
        $sql = "UPDATE pupilsightSetting SET value=:value WHERE scope='Individual Needs' AND name='targetsTemplate'";
        $result = $connection2->prepare($sql);
        $result->execute($data);
    } catch (PDOException $e) {
        $fail = true;
    }

    try {
        $data = array('value' => $teachingStrategiesTemplate);
        $sql = "UPDATE pupilsightSetting SET value=:value WHERE scope='Individual Needs' AND name='teachingStrategiesTemplate'";
        $result = $connection2->prepare($sql);
        $result->execute($data);
    } catch (PDOException $e) {
        $fail = true;
    }

    try {
        $data = array('value' => $notesReviewTemplate);
        $sql = "UPDATE pupilsightSetting SET value=:value WHERE scope='Individual Needs' AND name='notesReviewTemplate'";
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
