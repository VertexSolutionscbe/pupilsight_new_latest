<?php
/*
Pupilsight, Flexible & Open School System
*/

include '../../pupilsight.php';

$URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_POST['address']).'/activitySettings.php';

if (isActionAccessible($guid, $connection2, '/modules/School Admin/activitySettings.php') == false) {
    $URL .= '&return=error0';
    header("Location: {$URL}");
} else {
    //Proceed!
    $dateType = $_POST['dateType'];
    if ($dateType == 'Term') {
        $maxPerTerm = $_POST['maxPerTerm'];
    } else {
        $maxPerTerm = 0;
    }
    $access = $_POST['access'];
    $payment = $_POST['payment'];
    $enrolmentType = $_POST['enrolmentType'];
    $backupChoice = $_POST['backupChoice'];
    $activityTypes = '';
    foreach (explode(',', $_POST['activityTypes']) as $type) {
        $activityTypes .= trim($type).',';
    }
    $activityTypes = substr($activityTypes, 0, -1);
    $disableExternalProviderSignup = $_POST['disableExternalProviderSignup'];
    $hideExternalProviderCost = $_POST['hideExternalProviderCost'];

    //Validate Inputs
    if ($dateType == '' or $access == '' or $payment == '' or $enrolmentType == '' or $backupChoice == '' or $disableExternalProviderSignup == '' or $hideExternalProviderCost == '') {
        $URL .= '&return=error3';
        header("Location: {$URL}");
    } else {
        //Write to database
        $fail = false;

        try {
            $data = array('value' => $dateType);
            $sql = "UPDATE pupilsightSetting SET value=:value WHERE scope='Activities' AND name='dateType'";
            $result = $connection2->prepare($sql);
            $result->execute($data);
        } catch (PDOException $e) {
            $fail = true;
        }

        try {
            $data = array('value' => $maxPerTerm);
            $sql = "UPDATE pupilsightSetting SET value=:value WHERE scope='Activities' AND name='maxPerTerm'";
            $result = $connection2->prepare($sql);
            $result->execute($data);
        } catch (PDOException $e) {
            $fail = true;
        }

        try {
            $data = array('value' => $access);
            $sql = "UPDATE pupilsightSetting SET value=:value WHERE scope='Activities' AND name='access'";
            $result = $connection2->prepare($sql);
            $result->execute($data);
        } catch (PDOException $e) {
            $fail = true;
        }

        try {
            $data = array('value' => $payment);
            $sql = "UPDATE pupilsightSetting SET value=:value WHERE scope='Activities' AND name='payment'";
            $result = $connection2->prepare($sql);
            $result->execute($data);
        } catch (PDOException $e) {
            $fail = true;
        }

        try {
            $data = array('value' => $enrolmentType);
            $sql = "UPDATE pupilsightSetting SET value=:value WHERE scope='Activities' AND name='enrolmentType'";
            $result = $connection2->prepare($sql);
            $result->execute($data);
        } catch (PDOException $e) {
            $fail = true;
        }

        try {
            $data = array('value' => $backupChoice);
            $sql = "UPDATE pupilsightSetting SET value=:value WHERE scope='Activities' AND name='backupChoice'";
            $result = $connection2->prepare($sql);
            $result->execute($data);
        } catch (PDOException $e) {
            $fail = true;
        }

        try {
            $data = array('value' => $activityTypes);
            $sql = "UPDATE pupilsightSetting SET value=:value WHERE scope='Activities' AND name='activityTypes'";
            $result = $connection2->prepare($sql);
            $result->execute($data);
        } catch (PDOException $e) {
            $fail = true;
        }

        try {
            $data = array('value' => $disableExternalProviderSignup);
            $sql = "UPDATE pupilsightSetting SET value=:value WHERE scope='Activities' AND name='disableExternalProviderSignup'";
            $result = $connection2->prepare($sql);
            $result->execute($data);
        } catch (PDOException $e) {
            $fail = true;
        }

        try {
            $data = array('value' => $hideExternalProviderCost);
            $sql = "UPDATE pupilsightSetting SET value=:value WHERE scope='Activities' AND name='hideExternalProviderCost'";
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
