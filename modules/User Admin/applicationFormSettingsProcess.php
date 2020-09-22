<?php
/*
Pupilsight, Flexible & Open School System
*/

include '../../pupilsight.php';

$URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_POST['address']).'/applicationFormSettings.php';

if (isActionAccessible($guid, $connection2, '/modules/User Admin/applicationFormSettings.php') == false) {
    $URL .= '&return=error0';
    header("Location: {$URL}");
} else {
    //Proceed!
    $introduction = $_POST['introduction'];
    $applicationFormSENText = (isset($_POST['applicationFormSENText']))? $_POST['applicationFormSENText'] : '';
    $applicationFormRefereeLink = $_POST['applicationFormRefereeLink'];
    $postscript = $_POST['postscript'];
    $scholarships = (isset($_POST['scholarships']))? $_POST['scholarships'] : '';
    $agreement = $_POST['agreement'];
    $applicationFee = $_POST['applicationFee'];
    $publicApplications = $_POST['publicApplications'];
    $milestones = $_POST['milestones'];
    $howDidYouHear = $_POST['howDidYouHear'];
    $requiredDocuments = $_POST['requiredDocuments'];
    $internalDocuments = $_POST['internalDocuments'];
    $requiredDocumentsText = $_POST['requiredDocumentsText'];
    $requiredDocumentsCompulsory = $_POST['requiredDocumentsCompulsory'];
    $notificationStudentMessage = $_POST['notificationStudentMessage'];
    $notificationStudentDefault = $_POST['notificationStudentDefault'];
    $notificationParentsMessage = $_POST['notificationParentsMessage'];
    $notificationParentsDefault = $_POST['notificationParentsDefault'];
    $languageOptionsActive = $_POST['languageOptionsActive'];
    $languageOptionsBlurb = (isset($_POST['languageOptionsBlurb'])) ? $_POST['languageOptionsBlurb'] : null;
    $languageOptionsLanguageList = (isset($_POST['languageOptionsLanguageList'])) ? $_POST['languageOptionsLanguageList'] : null;
    $studentDefaultEmail = $_POST['studentDefaultEmail'];
    $studentDefaultWebsite = $_POST['studentDefaultWebsite'];
    $autoHouseAssign = $_POST['autoHouseAssign'];
    $usernameFormat = $_POST['usernameFormat'];

    $studentContactActive = (isset($_POST['studentContactActive']))? $_POST['studentContactActive'] : '';
    $senOptionsActive = (isset($_POST['senOptionsActive']))? $_POST['senOptionsActive'] : '';
    $scholarshipOptionsActive = (isset($_POST['scholarshipOptionsActive']))? $_POST['scholarshipOptionsActive'] : '';
    $paymentOptionsActive = (isset($_POST['paymentOptionsActive']))? $_POST['paymentOptionsActive'] : '';

    $enableLimitedYearsOfEntry = (isset($_POST['enableLimitedYearsOfEntry']))? $_POST['enableLimitedYearsOfEntry'] : 'N';
    $availableYearsOfEntry = (isset($_POST['availableYearsOfEntry']))? $_POST['availableYearsOfEntry'] : '';
    $availableYearsOfEntry = (is_array($availableYearsOfEntry))? implode(',', $availableYearsOfEntry) : $availableYearsOfEntry;

    //Write to database
    $fail = false;

    try {
        $data = array('value' => $introduction);
        $sql = "UPDATE pupilsightSetting SET value=:value WHERE scope='Application Form' AND name='introduction'";
        $result = $connection2->prepare($sql);
        $result->execute($data);
    } catch (PDOException $e) {
        $fail = true;
    }

    if ($senOptionsActive == 'Y') {
        try {
            $data = array('value' => $applicationFormSENText);
            $sql = "UPDATE pupilsightSetting SET value=:value WHERE scope='Students' AND name='applicationFormSENText'";
            $result = $connection2->prepare($sql);
            $result->execute($data);
        } catch (PDOException $e) {
            $fail = true;
        }
    }

    try {
        $data = array('value' => $applicationFormRefereeLink);
        $sql = "UPDATE pupilsightSetting SET value=:value WHERE scope='Students' AND name='applicationFormRefereeLink'";
        $result = $connection2->prepare($sql);
        $result->execute($data);
    } catch (PDOException $e) {
        $fail = true;
    }

    try {
        $data = array('value' => $postscript);
        $sql = "UPDATE pupilsightSetting SET value=:value WHERE scope='Application Form' AND name='postscript'";
        $result = $connection2->prepare($sql);
        $result->execute($data);
    } catch (PDOException $e) {
        $fail = true;
    }

    if ($scholarshipOptionsActive == 'Y') {
        try {
            $data = array('value' => $scholarships);
            $sql = "UPDATE pupilsightSetting SET value=:value WHERE scope='Application Form' AND name='scholarships'";
            $result = $connection2->prepare($sql);
            $result->execute($data);
        } catch (PDOException $e) {
            $fail = true;
        }
    }

    try {
        $data = array('value' => $agreement);
        $sql = "UPDATE pupilsightSetting SET value=:value WHERE scope='Application Form' AND name='agreement'";
        $result = $connection2->prepare($sql);
        $result->execute($data);
    } catch (PDOException $e) {
        $fail = true;
    }

    try {
        $data = array('value' => $applicationFee);
        $sql = "UPDATE pupilsightSetting SET value=:value WHERE scope='Application Form' AND name='applicationFee'";
        $result = $connection2->prepare($sql);
        $result->execute($data);
    } catch (PDOException $e) {
        $fail = true;
    }

    try {
        $data = array('value' => $publicApplications);
        $sql = "UPDATE pupilsightSetting SET value=:value WHERE scope='Application Form' AND name='publicApplications'";
        $result = $connection2->prepare($sql);
        $result->execute($data);
    } catch (PDOException $e) {
        $fail = true;
    }

    try {
        $data = array('value' => $milestones);
        $sql = "UPDATE pupilsightSetting SET value=:value WHERE scope='Application Form' AND name='milestones'";
        $result = $connection2->prepare($sql);
        $result->execute($data);
    } catch (PDOException $e) {
        $fail = true;
    }

    try {
        $data = array('value' => $howDidYouHear);
        $sql = "UPDATE pupilsightSetting SET value=:value WHERE scope='Application Form' AND name='howDidYouHear'";
        $result = $connection2->prepare($sql);
        $result->execute($data);
    } catch (PDOException $e) {
        $fail = true;
    }

    try {
        $data = array('value' => $requiredDocuments);
        $sql = "UPDATE pupilsightSetting SET value=:value WHERE scope='Application Form' AND name='requiredDocuments'";
        $result = $connection2->prepare($sql);
        $result->execute($data);
    } catch (PDOException $e) {
        $fail = true;
    }

    try {
        $data = array('value' => $internalDocuments);
        $sql = "UPDATE pupilsightSetting SET value=:value WHERE scope='Application Form' AND name='internalDocuments'";
        $result = $connection2->prepare($sql);
        $result->execute($data);
    } catch (PDOException $e) {
        $fail = true;
    }

    try {
        $data = array('value' => $requiredDocumentsText);
        $sql = "UPDATE pupilsightSetting SET value=:value WHERE scope='Application Form' AND name='requiredDocumentsText'";
        $result = $connection2->prepare($sql);
        $result->execute($data);
    } catch (PDOException $e) {
        $fail = true;
    }

    try {
        $data = array('value' => $requiredDocumentsCompulsory);
        $sql = "UPDATE pupilsightSetting SET value=:value WHERE scope='Application Form' AND name='requiredDocumentsCompulsory'";
        $result = $connection2->prepare($sql);
        $result->execute($data);
    } catch (PDOException $e) {
        $fail = true;
    }

    try {
        $data = array('value' => $notificationStudentMessage);
        $sql = "UPDATE pupilsightSetting SET value=:value WHERE scope='Application Form' AND name='notificationStudentMessage'";
        $result = $connection2->prepare($sql);
        $result->execute($data);
    } catch (PDOException $e) {
        $fail = true;
    }

    try {
        $data = array('value' => $notificationStudentDefault);
        $sql = "UPDATE pupilsightSetting SET value=:value WHERE scope='Application Form' AND name='notificationStudentDefault'";
        $result = $connection2->prepare($sql);
        $result->execute($data);
    } catch (PDOException $e) {
        $fail = true;
    }

    try {
        $data = array('value' => $notificationParentsMessage);
        $sql = "UPDATE pupilsightSetting SET value=:value WHERE scope='Application Form' AND name='notificationParentsMessage'";
        $result = $connection2->prepare($sql);
        $result->execute($data);
    } catch (PDOException $e) {
        $fail = true;
    }

    try {
        $data = array('value' => $notificationParentsDefault);
        $sql = "UPDATE pupilsightSetting SET value=:value WHERE scope='Application Form' AND name='notificationParentsDefault'";
        $result = $connection2->prepare($sql);
        $result->execute($data);
    } catch (PDOException $e) {
        $fail = true;
    }

    try {
        $data = array('value' => $languageOptionsActive);
        $sql = "UPDATE pupilsightSetting SET value=:value WHERE scope='Application Form' AND name='languageOptionsActive'";
        $result = $connection2->prepare($sql);
        $result->execute($data);
    } catch (PDOException $e) {
        $fail = true;
    }

    try {
        $data = array('value' => $languageOptionsBlurb);
        $sql = "UPDATE pupilsightSetting SET value=:value WHERE scope='Application Form' AND name='languageOptionsBlurb'";
        $result = $connection2->prepare($sql);
        $result->execute($data);
    } catch (PDOException $e) {
        $fail = true;
    }

    try {
        $data = array('value' => $languageOptionsLanguageList);
        $sql = "UPDATE pupilsightSetting SET value=:value WHERE scope='Application Form' AND name='languageOptionsLanguageList'";
        $result = $connection2->prepare($sql);
        $result->execute($data);
    } catch (PDOException $e) {
        $fail = true;
    }

    try {
        $data = array('value' => $studentDefaultEmail);
        $sql = "UPDATE pupilsightSetting SET value=:value WHERE scope='Application Form' AND name='studentDefaultEmail'";
        $result = $connection2->prepare($sql);
        $result->execute($data);
    } catch (PDOException $e) {
        $fail = true;
    }

    try {
        $data = array('value' => $studentDefaultWebsite);
        $sql = "UPDATE pupilsightSetting SET value=:value WHERE scope='Application Form' AND name='studentDefaultWebsite'";
        $result = $connection2->prepare($sql);
        $result->execute($data);
    } catch (PDOException $e) {
        $fail = true;
    }

    try {
        $data = array('value' => $autoHouseAssign);
        $sql = "UPDATE pupilsightSetting SET value=:value WHERE scope='Application Form' AND name='autoHouseAssign'";
        $result = $connection2->prepare($sql);
        $result->execute($data);
    } catch (PDOException $e) {
        $fail = true;
    }

    try {
        $data = array('value' => $usernameFormat);
        $sql = "UPDATE pupilsightSetting SET value=:value WHERE scope='Application Form' AND name='usernameFormat'";
        $result = $connection2->prepare($sql);
        $result->execute($data);
    } catch (PDOException $e) {
        $fail = true;
    }

    try {
        $data = array('value' => $senOptionsActive);
        $sql = "UPDATE pupilsightSetting SET value=:value WHERE scope='Application Form' AND name='senOptionsActive'";
        $result = $connection2->prepare($sql);
        $result->execute($data);
    } catch (PDOException $e) {
        $fail = true;
    }

    try {
        $data = array('value' => $scholarshipOptionsActive);
        $sql = "UPDATE pupilsightSetting SET value=:value WHERE scope='Application Form' AND name='scholarshipOptionsActive'";
        $result = $connection2->prepare($sql);
        $result->execute($data);
    } catch (PDOException $e) {
        $fail = true;
    }

    try {
        $data = array('value' => $paymentOptionsActive);
        $sql = "UPDATE pupilsightSetting SET value=:value WHERE scope='Application Form' AND name='paymentOptionsActive'";
        $result = $connection2->prepare($sql);
        $result->execute($data);
    } catch (PDOException $e) {
        $fail = true;
    }

    try {
        $data = array('value' => $enableLimitedYearsOfEntry);
        $sql = "UPDATE pupilsightSetting SET value=:value WHERE scope='Application Form' AND name='enableLimitedYearsOfEntry'";
        $result = $connection2->prepare($sql);
        $result->execute($data);
    } catch (PDOException $e) {
        $fail = true;
    }

    try {
        $data = array('value' => $availableYearsOfEntry);
        $sql = "UPDATE pupilsightSetting SET value=:value WHERE scope='Application Form' AND name='availableYearsOfEntry'";
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
