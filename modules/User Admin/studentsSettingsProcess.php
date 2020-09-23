<?php
/*
Pupilsight, Flexible & Open School System
*/

include '../../pupilsight.php';

$URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_POST['address']).'/studentsSettings.php';

if (isActionAccessible($guid, $connection2, '/modules/User Admin/studentsSettings.php') == false) {
    $URL .= '&return=error0';
    header("Location: {$URL}");
} else {
    //Proceed!
    $enableStudentNotes = $_POST['enableStudentNotes'];
    $noteCreationNotification = 'Tutors';
    if ($_POST['noteCreationNotification'] == 'Tutors & Teachers')
        $noteCreationNotification = 'Tutors & Teachers';
    $academicAlertLowThreshold = $_POST['academicAlertLowThreshold'];
    $academicAlertMediumThreshold = $_POST['academicAlertMediumThreshold'];
    $academicAlertHighThreshold = $_POST['academicAlertHighThreshold'];
    $behaviourAlertLowThreshold = $_POST['behaviourAlertLowThreshold'];
    $behaviourAlertMediumThreshold = $_POST['behaviourAlertMediumThreshold'];
    $behaviourAlertHighThreshold = $_POST['behaviourAlertHighThreshold'];
    $extendedBriefProfile = $_POST['extendedBriefProfile'];
    $studentAgreementOptions = '';
    foreach (explode(',', $_POST['studentAgreementOptions']) as $agreement) {
        $studentAgreementOptions .= trim($agreement).',';
    }
    $studentAgreementOptions = substr($studentAgreementOptions, 0, -1);

    //Write to database
    $fail = false;

    try {
        $data = array('value' => $enableStudentNotes);
        $sql = "UPDATE pupilsightSetting SET value=:value WHERE scope='Students' AND name='enableStudentNotes'";
        $result = $connection2->prepare($sql);
        $result->execute($data);
    } catch (PDOException $e) {
        $fail = true;
    }

    try {
        $data = array('value' => $noteCreationNotification);
        $sql = "UPDATE pupilsightSetting SET value=:value WHERE scope='Students' AND name='noteCreationNotification'";
        $result = $connection2->prepare($sql);
        $result->execute($data);
    } catch (PDOException $e) {
        $fail = true;
    }

    try {
        $data = array('value' => $academicAlertLowThreshold);
        $sql = "UPDATE pupilsightSetting SET value=:value WHERE scope='Students' AND name='academicAlertLowThreshold'";
        $result = $connection2->prepare($sql);
        $result->execute($data);
    } catch (PDOException $e) {
        $fail = true;
    }

    try {
        $data = array('value' => $academicAlertMediumThreshold);
        $sql = "UPDATE pupilsightSetting SET value=:value WHERE scope='Students' AND name='academicAlertMediumThreshold'";
        $result = $connection2->prepare($sql);
        $result->execute($data);
    } catch (PDOException $e) {
        $fail = true;
    }

    try {
        $data = array('value' => $academicAlertHighThreshold);
        $sql = "UPDATE pupilsightSetting SET value=:value WHERE scope='Students' AND name='academicAlertHighThreshold'";
        $result = $connection2->prepare($sql);
        $result->execute($data);
    } catch (PDOException $e) {
        $fail = true;
    }

    try {
        $data = array('value' => $behaviourAlertLowThreshold);
        $sql = "UPDATE pupilsightSetting SET value=:value WHERE scope='Students' AND name='behaviourAlertLowThreshold'";
        $result = $connection2->prepare($sql);
        $result->execute($data);
    } catch (PDOException $e) {
        $fail = true;
    }

    try {
        $data = array('value' => $behaviourAlertMediumThreshold);
        $sql = "UPDATE pupilsightSetting SET value=:value WHERE scope='Students' AND name='behaviourAlertMediumThreshold'";
        $result = $connection2->prepare($sql);
        $result->execute($data);
    } catch (PDOException $e) {
        $fail = true;
    }

    try {
        $data = array('value' => $behaviourAlertHighThreshold);
        $sql = "UPDATE pupilsightSetting SET value=:value WHERE scope='Students' AND name='behaviourAlertHighThreshold'";
        $result = $connection2->prepare($sql);
        $result->execute($data);
    } catch (PDOException $e) {
        $fail = true;
    }

    try {
        $data = array('value' => $extendedBriefProfile);
        $sql = "UPDATE pupilsightSetting SET value=:value WHERE scope='Students' AND name='extendedBriefProfile'";
        $result = $connection2->prepare($sql);
        $result->execute($data);
    } catch (PDOException $e) {
        $fail = true;
    }

    try {
        $data = array('value' => $studentAgreementOptions);
        $sql = "UPDATE pupilsightSetting SET value=:value WHERE scope='School Admin' AND name='studentAgreementOptions'";
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
