<?php
/*
Pupilsight, Flexible & Open School System
*/

include '../../pupilsight.php';

$URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_POST['address']).'/publicRegistrationSettings.php';

if (isActionAccessible($guid, $connection2, '/modules/User Admin/publicRegistrationSettings.php') == false) {
    $URL .= '&return=error0';
    header("Location: {$URL}");
} else {
    //Proceed!
    $enablePublicRegistration = $_POST['enablePublicRegistration'];
    $publicRegistrationMinimumAge = $_POST['publicRegistrationMinimumAge'];
    $publicRegistrationDefaultStatus = $_POST['publicRegistrationDefaultStatus'];
    $publicRegistrationDefaultRole = $_POST['publicRegistrationDefaultRole'];
    $publicRegistrationIntro = $_POST['publicRegistrationIntro'];
    $publicRegistrationPrivacyStatement = $_POST['publicRegistrationPrivacyStatement'];
    $publicRegistrationAgreement = $_POST['publicRegistrationAgreement'];
    $publicRegistrationPostscript = $_POST['publicRegistrationPostscript'];

    //Write to database
    $fail = false;

    try {
        $data = array('value' => $enablePublicRegistration);
        $sql = "UPDATE pupilsightSetting SET value=:value WHERE scope='User Admin' AND name='enablePublicRegistration'";
        $result = $connection2->prepare($sql);
        $result->execute($data);
    } catch (PDOException $e) {
        $fail = true;
    }

    try {
        $data = array('value' => $publicRegistrationMinimumAge);
        $sql = "UPDATE pupilsightSetting SET value=:value WHERE scope='User Admin' AND name='publicRegistrationMinimumAge'";
        $result = $connection2->prepare($sql);
        $result->execute($data);
    } catch (PDOException $e) {
        $fail = true;
    }

    try {
        $data = array('value' => $publicRegistrationDefaultStatus);
        $sql = "UPDATE pupilsightSetting SET value=:value WHERE scope='User Admin' AND name='publicRegistrationDefaultStatus'";
        $result = $connection2->prepare($sql);
        $result->execute($data);
    } catch (PDOException $e) {
        $fail = true;
    }

    try {
        $data = array('value' => $publicRegistrationDefaultRole);
        $sql = "UPDATE pupilsightSetting SET value=:value WHERE scope='User Admin' AND name='publicRegistrationDefaultRole'";
        $result = $connection2->prepare($sql);
        $result->execute($data);
    } catch (PDOException $e) {
        $fail = true;
    }

    try {
        $data = array('value' => $publicRegistrationIntro);
        $sql = "UPDATE pupilsightSetting SET value=:value WHERE scope='User Admin' AND name='publicRegistrationIntro'";
        $result = $connection2->prepare($sql);
        $result->execute($data);
    } catch (PDOException $e) {
        $fail = true;
    }

    try {
        $data = array('value' => $publicRegistrationPrivacyStatement);
        $sql = "UPDATE pupilsightSetting SET value=:value WHERE scope='User Admin' AND name='publicRegistrationPrivacyStatement'";
        $result = $connection2->prepare($sql);
        $result->execute($data);
    } catch (PDOException $e) {
        $fail = true;
    }

    try {
        $data = array('value' => $publicRegistrationAgreement);
        $sql = "UPDATE pupilsightSetting SET value=:value WHERE scope='User Admin' AND name='publicRegistrationAgreement'";
        $result = $connection2->prepare($sql);
        $result->execute($data);
    } catch (PDOException $e) {
        $fail = true;
    }

    try {
        $data = array('value' => $publicRegistrationPostscript);
        $sql = "UPDATE pupilsightSetting SET value=:value WHERE scope='User Admin' AND name='publicRegistrationPostscript'";
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
