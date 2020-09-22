<?php
/*
Pupilsight, Flexible & Open School System
*/

include '../../pupilsight.php';

$URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_POST['address']).'/staffApplicationFormSettings.php';

if (isActionAccessible($guid, $connection2, '/modules/User Admin/staffApplicationFormSettings.php') == false) {
    $URL .= '&return=error0';
    header("Location: {$URL}");
} else {
    //Proceed!
    $staffApplicationFormIntroduction = $_POST['staffApplicationFormIntroduction'];
    $staffApplicationFormQuestions = $_POST['staffApplicationFormQuestions'];
    $staffApplicationFormPostscript = $_POST['staffApplicationFormPostscript'];
    $staffApplicationFormAgreement = $_POST['staffApplicationFormAgreement'];
    $staffApplicationFormPublicApplications = $_POST['staffApplicationFormPublicApplications'];
    $staffApplicationFormMilestones = $_POST['staffApplicationFormMilestones'];
    $staffApplicationFormRequiredDocuments = $_POST['staffApplicationFormRequiredDocuments'];
    $staffApplicationFormRequiredDocumentsText = $_POST['staffApplicationFormRequiredDocumentsText'];
    $staffApplicationFormRequiredDocumentsCompulsory = $_POST['staffApplicationFormRequiredDocumentsCompulsory'];
    $staffApplicationFormNotificationMessage = $_POST['staffApplicationFormNotificationMessage'];
    $staffApplicationFormNotificationDefault = $_POST['staffApplicationFormNotificationDefault'];
    $staffApplicationFormDefaultEmail = $_POST['staffApplicationFormDefaultEmail'];
    $staffApplicationFormDefaultWebsite = $_POST['staffApplicationFormDefaultWebsite'];
    $staffApplicationFormUsernameFormat = $_POST['staffApplicationFormUsernameFormat'];
    //Deal with reference links
    $refereeLinks=array() ;
    if (isset($_POST['refereeLinks']) AND isset($_POST['types'])) {
        for ($i=0; $i<count($_POST['types']); $i++) {
            $refereeLinks[$_POST['types'][$i]] = (isset($_POST['refereeLinks'][$i]))? $_POST['refereeLinks'][$i] : '';
        }
        $applicationFormRefereeLink = serialize($refereeLinks) ;
    }

    //Write to database
    $fail = false;

    try {
        $data = array('value' => $staffApplicationFormIntroduction);
        $sql = "UPDATE pupilsightSetting SET value=:value WHERE scope='Staff' AND name='staffApplicationFormIntroduction'";
        $result = $connection2->prepare($sql);
        $result->execute($data);
    } catch (PDOException $e) {
        $fail = true;
    }

    try {
        $data = array('value' => $staffApplicationFormQuestions);
        $sql = "UPDATE pupilsightSetting SET value=:value WHERE scope='Staff' AND name='staffApplicationFormQuestions'";
        $result = $connection2->prepare($sql);
        $result->execute($data);
    } catch (PDOException $e) {
        $fail = true;
    }

    try {
        $data = array('value' => $applicationFormRefereeLink);
        $sql = "UPDATE pupilsightSetting SET value=:value WHERE scope='Staff' AND name='applicationFormRefereeLink'";
        $result = $connection2->prepare($sql);
        $result->execute($data);
    } catch (PDOException $e) {
        $fail = true;
    }

    try {
        $data = array('value' => $staffApplicationFormPostscript);
        $sql = "UPDATE pupilsightSetting SET value=:value WHERE scope='Staff' AND name='staffApplicationFormPostscript'";
        $result = $connection2->prepare($sql);
        $result->execute($data);
    } catch (PDOException $e) {
        $fail = true;
    }

    try {
        $data = array('value' => $staffApplicationFormAgreement);
        $sql = "UPDATE pupilsightSetting SET value=:value WHERE scope='Staff' AND name='staffApplicationFormAgreement'";
        $result = $connection2->prepare($sql);
        $result->execute($data);
    } catch (PDOException $e) {
        $fail = true;
    }


    try {
        $data = array('value' => $staffApplicationFormPublicApplications);
        $sql = "UPDATE pupilsightSetting SET value=:value WHERE scope='Staff Application Form' AND name='staffApplicationFormPublicApplications'";
        $result = $connection2->prepare($sql);
        $result->execute($data);
    } catch (PDOException $e) {
        $fail = true;
    }

    try {
        $data = array('value' => $staffApplicationFormMilestones);
        $sql = "UPDATE pupilsightSetting SET value=:value WHERE scope='Staff' AND name='staffApplicationFormMilestones'";
        $result = $connection2->prepare($sql);
        $result->execute($data);
    } catch (PDOException $e) {
        $fail = true;
    }

    try {
        $data = array('value' => $staffApplicationFormRequiredDocuments);
        $sql = "UPDATE pupilsightSetting SET value=:value WHERE scope='Staff' AND name='staffApplicationFormRequiredDocuments'";
        $result = $connection2->prepare($sql);
        $result->execute($data);
    } catch (PDOException $e) {
        $fail = true;
    }

    try {
        $data = array('value' => $staffApplicationFormRequiredDocumentsText);
        $sql = "UPDATE pupilsightSetting SET value=:value WHERE scope='Staff' AND name='staffApplicationFormRequiredDocumentsText'";
        $result = $connection2->prepare($sql);
        $result->execute($data);
    } catch (PDOException $e) {
        $fail = true;
    }

    try {
        $data = array('value' => $staffApplicationFormRequiredDocumentsCompulsory);
        $sql = "UPDATE pupilsightSetting SET value=:value WHERE scope='Staff' AND name='staffApplicationFormRequiredDocumentsCompulsory'";
        $result = $connection2->prepare($sql);
        $result->execute($data);
    } catch (PDOException $e) {
        $fail = true;
    }

    try {
        $data = array('value' => $staffApplicationFormNotificationMessage);
        $sql = "UPDATE pupilsightSetting SET value=:value WHERE scope='Staff' AND name='staffApplicationFormNotificationMessage'";
        $result = $connection2->prepare($sql);
        $result->execute($data);
    } catch (PDOException $e) {
        $fail = true;
    }

    try {
        $data = array('value' => $staffApplicationFormNotificationDefault);
        $sql = "UPDATE pupilsightSetting SET value=:value WHERE scope='Staff' AND name='staffApplicationFormNotificationDefault'";
        $result = $connection2->prepare($sql);
        $result->execute($data);
    } catch (PDOException $e) {
        $fail = true;
    }

    try {
        $data = array('value' => $staffApplicationFormDefaultEmail);
        $sql = "UPDATE pupilsightSetting SET value=:value WHERE scope='Staff' AND name='staffApplicationFormDefaultEmail'";
        $result = $connection2->prepare($sql);
        $result->execute($data);
    } catch (PDOException $e) {
        $fail = true;
    }

    try {
        $data = array('value' => $staffApplicationFormDefaultWebsite);
        $sql = "UPDATE pupilsightSetting SET value=:value WHERE scope='Staff' AND name='staffApplicationFormDefaultWebsite'";
        $result = $connection2->prepare($sql);
        $result->execute($data);
    } catch (PDOException $e) {
        $fail = true;
    }

    try {
        $data = array('value' => $staffApplicationFormUsernameFormat);
        $sql = "UPDATE pupilsightSetting SET value=:value WHERE scope='Staff' AND name='staffApplicationFormUsernameFormat'";
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
