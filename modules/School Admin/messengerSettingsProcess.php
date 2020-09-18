<?php
/*
Pupilsight, Flexible & Open School System
*/

include '../../pupilsight.php';

$URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_POST['address']).'/messengerSettings.php';

if (isActionAccessible($guid, $connection2, '/modules/School Admin/messengerSettings.php') == false) {
    $URL .= '&return=error0';
    header("Location: {$URL}");
} else {
    //Proceed!
    $messageBubbleWidthType = $_POST['messageBubbleWidthType'];
    $messageBubbleBGColor = $_POST['messageBubbleBGColor'];
    $messageBubbleAutoHide = $_POST['messageBubbleAutoHide'];
    $enableHomeScreenWidget = $_POST['enableHomeScreenWidget'];
    $messageBcc = $_POST['messageBcc'];
    $smsCopy = $_POST['smsCopy'];
    //Write to database
    $fail = false;

    try {
        $data = array('value' => $messageBubbleWidthType);
        $sql = "UPDATE pupilsightSetting SET value=:value WHERE scope='Messenger' AND name='messageBubbleWidthType'";
        $result = $connection2->prepare($sql);
        $result->execute($data);
    } catch (PDOException $e) {
        $fail = true;
    }

    try {
        $data = array('value' => $messageBubbleBGColor);
        $sql = "UPDATE pupilsightSetting SET value=:value WHERE scope='Messenger' AND name='messageBubbleBGColor'";
        $result = $connection2->prepare($sql);
        $result->execute($data);
    } catch (PDOException $e) {
        $fail = true;
    }

    try {
        $data = array('value' => $messageBubbleAutoHide);
        $sql = "UPDATE pupilsightSetting SET value=:value WHERE scope='Messenger' AND name='messageBubbleAutoHide'";
        $result = $connection2->prepare($sql);
        $result->execute($data);
    } catch (PDOException $e) {
        $fail = true;
    }

    try {
        $data = array('value' => $enableHomeScreenWidget);
        $sql = "UPDATE pupilsightSetting SET value=:value WHERE scope='Messenger' AND name='enableHomeScreenWidget'";
        $result = $connection2->prepare($sql);
        $result->execute($data);
    } catch (PDOException $e) {
        $fail = true;
    }

    try {
        $data = array('value' => $messageBcc);
        $sql = "UPDATE pupilsightSetting SET value=:value WHERE scope='Messenger' AND name='messageBcc'";
        $result = $connection2->prepare($sql);
        $result->execute($data);
    } catch (PDOException $e) {
        $fail = true;
    }

    try {
        $data = array('value' => $smsCopy);
        $sql = "UPDATE pupilsightSetting SET value=:value WHERE scope='Messenger' AND name='smsCopy'";
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
