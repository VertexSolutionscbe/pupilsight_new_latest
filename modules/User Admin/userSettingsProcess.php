<?php
/*
Pupilsight, Flexible & Open School System
*/

include '../../pupilsight.php';

$URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_POST['address']).'/userSettings.php';

if (isActionAccessible($guid, $connection2, '/modules/User Admin/userSettings.php') == false) {
    $URL .= '&return=error0';
    header("Location: {$URL}");
} else {
    //Proceed!
    $ethnicity = $_POST['ethnicity'];
    $religions = $_POST['religions'];
    $nationality = $_POST['nationality'];
    $residencyStatus = $_POST['residencyStatus'];
    $departureReasons = $_POST['departureReasons'];
    $privacy = $_POST['privacy'];
    $privacyBlurb = (isset($_POST['privacyBlurb'])) ? $_POST['privacyBlurb'] : null;
    $privacyOptions = (isset($_POST['privacyOptions'])) ? $_POST['privacyOptions'] : null;
    $uniqueEmailAddress = (isset($_POST['uniqueEmailAddress'])) ? $_POST['uniqueEmailAddress'] : 'N';
    $personalBackground = $_POST['personalBackground'];
    $dayTypeOptions = $_POST['dayTypeOptions'];
    $dayTypeText = $_POST['dayTypeText'];

    //Write to database
    $fail = false;

    try {
        $data = array('value' => $ethnicity);
        $sql = "UPDATE pupilsightSetting SET value=:value WHERE scope='User Admin' AND name='ethnicity'";
        $result = $connection2->prepare($sql);
        $result->execute($data);
    } catch (PDOException $e) {
        $fail = true;
    }

    try {
        $data = array('value' => $religions);
        $sql = "UPDATE pupilsightSetting SET value=:value WHERE scope='User Admin' AND name='religions'";
        $result = $connection2->prepare($sql);
        $result->execute($data);
    } catch (PDOException $e) {
        $fail = true;
    }

    try {
        $data = array('value' => $nationality);
        $sql = "UPDATE pupilsightSetting SET value=:value WHERE scope='User Admin' AND name='nationality'";
        $result = $connection2->prepare($sql);
        $result->execute($data);
    } catch (PDOException $e) {
        $fail = true;
    }

    try {
        $data = array('value' => $departureReasons);
        $sql = "UPDATE pupilsightSetting SET value=:value WHERE scope='User Admin' AND name='departureReasons'";
        $result = $connection2->prepare($sql);
        $result->execute($data);
    } catch (PDOException $e) {
        $fail = true;
    }

    try {
        $data = array('value' => $residencyStatus);
        $sql = "UPDATE pupilsightSetting SET value=:value WHERE scope='User Admin' AND name='residencyStatus'";
        $result = $connection2->prepare($sql);
        $result->execute($data);
    } catch (PDOException $e) {
        $fail = true;
    }

    try {
        $data = array('value' => $privacy);
        $sql = "UPDATE pupilsightSetting SET value=:value WHERE scope='User Admin' AND name='privacy'";
        $result = $connection2->prepare($sql);
        $result->execute($data);
    } catch (PDOException $e) {
        $fail = true;
    }

    try {
        $data = array('value' => $privacyBlurb);
        $sql = "UPDATE pupilsightSetting SET value=:value WHERE scope='User Admin' AND name='privacyBlurb'";
        $result = $connection2->prepare($sql);
        $result->execute($data);
    } catch (PDOException $e) {
        $fail = true;
    }

    try {
        $data = array('value' => $privacyOptions);
        $sql = "UPDATE pupilsightSetting SET value=:value WHERE scope='User Admin' AND name='privacyOptions'";
        $result = $connection2->prepare($sql);
        $result->execute($data);
    } catch (PDOException $e) {
        $fail = true;
    }

    try {
        $data = array('value' => $uniqueEmailAddress);
        $sql = "UPDATE pupilsightSetting SET value=:value WHERE scope='User Admin' AND name='uniqueEmailAddress'";
        $result = $connection2->prepare($sql);
        $result->execute($data);
    } catch (PDOException $e) {
        $fail = true;
    }

    try {
        $data = array('value' => $personalBackground);
        $sql = "UPDATE pupilsightSetting SET value=:value WHERE scope='User Admin' AND name='personalBackground'";
        $result = $connection2->prepare($sql);
        $result->execute($data);
    } catch (PDOException $e) {
        $fail = true;
    }

    try {
        $data = array('value' => $dayTypeOptions);
        $sql = "UPDATE pupilsightSetting SET value=:value WHERE scope='User Admin' AND name='dayTypeOptions'";
        $result = $connection2->prepare($sql);
        $result->execute($data);
    } catch (PDOException $e) {
        $fail = true;
    }

    try {
        $data = array('value' => $dayTypeText);
        $sql = "UPDATE pupilsightSetting SET value=:value WHERE scope='User Admin' AND name='dayTypeText'";
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
