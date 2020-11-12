<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Services\Format;

include '../../pupilsight.php';
include '../../config.php';

//Module includes
include './moduleFunctions.php';

$URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_POST['address']).'/systemSettings.php';

if (isActionAccessible($guid, $connection2, '/modules/System Admin/systemSettings.php') == false) {
    $URL .= '&return=error0';
    header("Location: {$URL}");
    exit;
} else {
    //Proceed!
    $absoluteURL = $_POST['absoluteURL'];
    $absolutePath = $_POST['absolutePath'];
    $systemName = $_POST['systemName'];
    $pupilsightSchoolYearID = $_POST['pupilsightSchoolYearID'];
    $smsCredits = $_POST['smsCredits'];
    $indexText = $_POST['indexText'];
    $organisationName = $_POST['organisationName'];
    $organisationNameShort = $_POST['organisationNameShort'];
    $organisationEmail = $_POST['organisationEmail'];
    $organisationLogo = $_POST['organisationLogo'];
    $organisationBackground = $_POST['organisationBackground'];
    $organisationAdministrator = $_POST['organisationAdministrator'];
    $organisationDBA = $_POST['organisationDBA'];
    $organisationHR = $_POST['organisationHR'];
    $organisationAdmissions = $_POST['organisationAdmissions'];
    $pagination = $_POST['pagination'];
    $timezone = $_POST['timezone'];
    $country = $_POST['country'];
    $firstDayOfTheWeek = $_POST['firstDayOfTheWeek'];
    $analytics = $_POST['analytics'];
    $emailLink = $_POST['emailLink'];
    $webLink = $_POST['webLink'];
    $defaultAssessmentScale = $_POST['defaultAssessmentScale'];
    $installType = $_POST['installType'];
    $statsCollection = $_POST['statsCollection'];
    $passwordPolicyMinLength = $_POST['passwordPolicyMinLength'];
    $passwordPolicyAlpha = $_POST['passwordPolicyAlpha'];
    $passwordPolicyNumeric = $_POST['passwordPolicyNumeric'];
    $passwordPolicyNonAlphaNumeric = $_POST['passwordPolicyNonAlphaNumeric'];
    $sessionDuration = $_POST['sessionDuration'];
    $currency = $_POST['currency'];
    $pupilsighteduComOrganisationName = $_POST['pupilsighteduComOrganisationName'];
    $pupilsighteduComOrganisationKey = $_POST['pupilsighteduComOrganisationKey'];

    //Validate Inputs
    if ($absoluteURL == '' or $systemName == '' or $organisationLogo == '' or $indexText == '' or $organisationName == '' or $organisationNameShort == '' or $organisationEmail == '' or $organisationAdministrator == '' or $organisationDBA == '' or $organisationHR == '' or $organisationAdmissions == '' or $pagination == '' or (!(is_numeric($pagination))) or $timezone == '' or $installType == '' or $statsCollection == '' or $passwordPolicyMinLength == '' or $passwordPolicyAlpha == '' or $passwordPolicyNumeric == '' or $passwordPolicyNonAlphaNumeric == '' or $firstDayOfTheWeek == '' or ($firstDayOfTheWeek != 'Monday' and $firstDayOfTheWeek != 'Sunday') or $currency == '') {
        $URL .= '&return=error3';
        header("Location: {$URL}");
        exit;
    } else {
        //Write to database
        $fail = false;
        $partialFail = false;

        try {
            $data = array('absoluteURL' => $absoluteURL);
            $sql = "UPDATE pupilsightSetting SET value=:absoluteURL WHERE scope='System' AND name='absoluteURL'";
            $result = $connection2->prepare($sql);
            $result->execute($data);
        } catch (PDOException $e) {
            $fail = true;
        }

        try {
            $data = array('absolutePath' => $absolutePath);
            $sql = "UPDATE pupilsightSetting SET value=:absolutePath WHERE scope='System' AND name='absolutePath'";
            $result = $connection2->prepare($sql);
            $result->execute($data);
        } catch (PDOException $e) {
            $fail = true;
        }

        try {
            $data = array('systemName' => $systemName);
            $sql = "UPDATE pupilsightSetting SET value=:systemName WHERE scope='System' AND name='systemName'";
            $result = $connection2->prepare($sql);
            $result->execute($data);
        } catch (PDOException $e) {
            $fail = true;
        }
        if(!empty($smsCredits)){
            $totalSmsCredits=$_POST['totalSmsCredits'];
            $total=$totalSmsCredits+$smsCredits;
        try {
            $data = array('smsCredits' => $total);
            $sql = "UPDATE pupilsightSetting SET value=:smsCredits WHERE scope='System' AND name='smsCredits'";
            $result = $connection2->prepare($sql);
            $result->execute($data);
                $credit_history = array('pupilsightSchoolYearID' => $pupilsightSchoolYearID,'credits' => $smsCredits);
                $sql_insert = 'INSERT INTO smsCreditsHistory SET  pupilsightSchoolYearID=:pupilsightSchoolYearID, credits=:credits';
                $resultInsert = $connection2->prepare($sql_insert);
                $res=$resultInsert->execute($credit_history);
        } catch (PDOException $e) {
            $fail = true;
        }

        }

        try {
            $data = array('pupilsightSchoolYearID' => $pupilsightSchoolYearID);
            $sql = "UPDATE pupilsightSetting SET value=:pupilsightSchoolYearID WHERE scope='System' AND name='pupilsightSchoolYearID'";
            $result = $connection2->prepare($sql);
            $result->execute($data);
        } catch (PDOException $e) {
            $fail = true;
        }

        try {
            $data = array('indexText' => $indexText);
            $sql = "UPDATE pupilsightSetting SET value=:indexText WHERE scope='System' AND name='indexText'";
            $result = $connection2->prepare($sql);
            $result->execute($data);
        } catch (PDOException $e) {
            $fail = true;
        }

        try {
            $data = array('organisationName' => $organisationName);
            $sql = "UPDATE pupilsightSetting SET value=:organisationName WHERE scope='System' AND name='organisationName'";
            $result = $connection2->prepare($sql);
            $result->execute($data);
        } catch (PDOException $e) {
            $fail = true;
        }

        try {
            $data = array('organisationNameShort' => $organisationNameShort);
            $sql = "UPDATE pupilsightSetting SET value=:organisationNameShort WHERE scope='System' AND name='organisationNameShort'";
            $result = $connection2->prepare($sql);
            $result->execute($data);
        } catch (PDOException $e) {
            $fail = true;
        }

        try {
            $data = array('organisationLogo' => $organisationLogo);
            $sql = "UPDATE pupilsightSetting SET value=:organisationLogo WHERE scope='System' AND name='organisationLogo'";
            $result = $connection2->prepare($sql);
            $result->execute($data);
        } catch (PDOException $e) {
            $fail = true;
        }

        try {
            $data = array('organisationBackground' => $organisationBackground);
            $sql = "UPDATE pupilsightSetting SET value=:organisationBackground WHERE scope='System' AND name='organisationBackground'";
            $result = $connection2->prepare($sql);
            $result->execute($data);
        } catch (PDOException $e) {
            $fail = true;
        }

        $pupilsight->session->set('organisationBackground', $organisationBackground);

        try {
            $data = array('organisationEmail' => $organisationEmail);
            $sql = "UPDATE pupilsightSetting SET value=:organisationEmail WHERE scope='System' AND name='organisationEmail'";
            $result = $connection2->prepare($sql);
            $result->execute($data);
        } catch (PDOException $e) {
            $fail = true;
        }

        //ADMINISTRATORS
        try {
            $data = array('organisationAdministrator' => $organisationAdministrator);
            $sql = "UPDATE pupilsightSetting SET value=:organisationAdministrator WHERE scope='System' AND name='organisationAdministrator'";
            $result = $connection2->prepare($sql);
            $result->execute($data);
        } catch (PDOException $e) {
            $fail = true;
        }
        //Update session variables
        try {
            // $data = array('pupilsightPersonID' => $organisationAdministrator);
            // $sql = 'SELECT surname, preferredName, email FROM pupilsightPerson WHERE pupilsightPersonID=:pupilsightPersonID';
            // $result = $connection2->prepare($sql);
            // $result->execute($data);

            $data = array('name' => 'organisationName');
            $sql = 'SELECT value FROM pupilsightSetting WHERE name=:name';
            $result = $connection2->prepare($sql);
            $result->execute($data);

            $data1 = array('name' => 'organisationEmail');
            $sql1 = 'SELECT value FROM pupilsightSetting WHERE name=:name';
            $result1 = $connection2->prepare($sql1);
            $result1->execute($data1);
        } catch (PDOException $e) {
            $fail = true;
        }
        if ($result->rowCount() != 1) {
            $fail = true;
        } else {
            $row = $result->fetch();
            $row1 = $result1->fetch();
            // $_SESSION[$guid]['organisationAdministratorName'] = Format::name('', $row['preferredName'], $row['surname'], 'Staff', false, true);
            // $_SESSION[$guid]['organisationAdministratorEmail'] = $row['email'];
            $_SESSION[$guid]['organisationAdministratorName'] = $row['value'];
            $_SESSION[$guid]['organisationAdministratorEmail'] = $row1['value'];
        }

        try {
            $data = array('organisationDBA' => $organisationDBA);
            $sql = "UPDATE pupilsightSetting SET value=:organisationDBA WHERE scope='System' AND name='organisationDBA'";
            $result = $connection2->prepare($sql);
            $result->execute($data);
        } catch (PDOException $e) {
            $fail = true;
        }
        //Update session variables
        try {
            $data = array('pupilsightPersonID' => $organisationDBA);
            $sql = 'SELECT surname, preferredName, email FROM pupilsightPerson WHERE pupilsightPersonID=:pupilsightPersonID';
            $result = $connection2->prepare($sql);
            $result->execute($data);
        } catch (PDOException $e) {
            $fail = true;
        }
        if ($result->rowCount() != 1) {
            $fail = true;
        } else {
            $row = $result->fetch();
            $_SESSION[$guid]['organisationDBAName'] = Format::name('', $row['preferredName'], $row['surname'], 'Staff', false, true);
            $_SESSION[$guid]['organisationDBAEmail'] = $row['email'];
        }

        try {
            $data = array('organisationHR' => $organisationHR);
            $sql = "UPDATE pupilsightSetting SET value=:organisationHR WHERE scope='System' AND name='organisationHR'";
            $result = $connection2->prepare($sql);
            $result->execute($data);
        } catch (PDOException $e) {
            $fail = true;
        }
        //Update session variables
        try {
            $data = array('pupilsightPersonID' => $organisationHR);
            $sql = 'SELECT surname, preferredName, email FROM pupilsightPerson WHERE pupilsightPersonID=:pupilsightPersonID';
            $result = $connection2->prepare($sql);
            $result->execute($data);
        } catch (PDOException $e) {
            $fail = true;
        }
        if ($result->rowCount() != 1) {
            $fail = true;
        } else {
            $row = $result->fetch();
            $_SESSION[$guid]['organisationHRName'] = Format::name('', $row['preferredName'], $row['surname'], 'Staff', false, true);
            $_SESSION[$guid]['organisationHREmail'] = $row['email'];
        }

        try {
            $data = array('organisationAdmissions' => $organisationAdmissions);
            $sql = "UPDATE pupilsightSetting SET value=:organisationAdmissions WHERE scope='System' AND name='organisationAdmissions'";
            $result = $connection2->prepare($sql);
            $result->execute($data);
        } catch (PDOException $e) {
            $fail = true;
        }
        //Update session variables
        try {
            $data = array('pupilsightPersonID' => $organisationAdmissions);
            $sql = 'SELECT surname, preferredName, email FROM pupilsightPerson WHERE pupilsightPersonID=:pupilsightPersonID';
            $result = $connection2->prepare($sql);
            $result->execute($data);
        } catch (PDOException $e) {
            $fail = true;
        }
        if ($result->rowCount() != 1) {
            $fail = true;
        } else {
            $row = $result->fetch();
            $_SESSION[$guid]['organisationAdmissionsName'] = Format::name('', $row['preferredName'], $row['surname'], 'Staff', false, true);
            $_SESSION[$guid]['organisationAdmissionsEmail'] = $row['email'];
        }

        try {
            $data = array('pagination' => $pagination);
            $sql = "UPDATE pupilsightSetting SET value=:pagination WHERE scope='System' AND name='pagination'";
            $result = $connection2->prepare($sql);
            $result->execute($data);
        } catch (PDOException $e) {
            $fail = true;
        }

        try {
            $data = array('country' => $country);
            $sql = "UPDATE pupilsightSetting SET value=:country WHERE scope='System' AND name='country'";
            $result = $connection2->prepare($sql);
            $result->execute($data);
        } catch (PDOException $e) {
            $fail = true;
        }

        try {
            $data = array('firstDayOfTheWeek' => $firstDayOfTheWeek);
            $sql = "UPDATE pupilsightSetting SET value=:firstDayOfTheWeek WHERE scope='System' AND name='firstDayOfTheWeek'";
            $result = $connection2->prepare($sql);
            $result->execute($data);
        } catch (PDOException $e) {
            $fail = true;
        }

        if (setFirstDayOfTheWeek($connection2, $firstDayOfTheWeek, $databaseName) != true) {
            $fail = true;
        }

        try {
            $data = array('currency' => $currency);
            $sql = "UPDATE pupilsightSetting SET value=:currency WHERE scope='System' AND name='currency'";
            $result = $connection2->prepare($sql);
            $result->execute($data);
        } catch (PDOException $e) {
            $fail = true;
        }

        try {
            $data = array('pupilsighteduComOrganisationName' => $pupilsighteduComOrganisationName);
            $sql = "UPDATE pupilsightSetting SET value=:pupilsighteduComOrganisationName WHERE scope='System' AND name='pupilsighteduComOrganisationName'";
            $result = $connection2->prepare($sql);
            $result->execute($data);
        } catch (PDOException $e) {
            $fail = true;
        }

        try {
            $data = array('pupilsighteduComOrganisationKey' => $pupilsighteduComOrganisationKey);
            $sql = "UPDATE pupilsightSetting SET value=:pupilsighteduComOrganisationKey WHERE scope='System' AND name='pupilsighteduComOrganisationKey'";
            $result = $connection2->prepare($sql);
            $result->execute($data);
        } catch (PDOException $e) {
            $fail = true;
        }

        // Validate before changing
        $validTimezone = true;
        try {
            new DateTimeZone($timezone);
        } catch(Exception $e) {
            $validTimezone = false;
            $partialFail = true;
        }

        if ($validTimezone) {
            try {
                $data = array('timezone' => $timezone);
                $sql = "UPDATE pupilsightSetting SET value=:timezone WHERE scope='System' AND name='timezone'";
                $result = $connection2->prepare($sql);
                $result->execute($data);
            } catch (PDOException $e) {
                $fail = true;
            }
        }

        try {
            $data = array('analytics' => $analytics);
            $sql = "UPDATE pupilsightSetting SET value=:analytics WHERE scope='System' AND name='analytics'";
            $result = $connection2->prepare($sql);
            $result->execute($data);
        } catch (PDOException $e) {
            $fail = true;
        }

        try {
            $data = array('emailLink' => $emailLink);
            $sql = "UPDATE pupilsightSetting SET value=:emailLink WHERE scope='System' AND name='emailLink'";
            $result = $connection2->prepare($sql);
            $result->execute($data);
        } catch (PDOException $e) {
            $fail = true;
        }

        try {
            $data = array('webLink' => $webLink);
            $sql = "UPDATE pupilsightSetting SET value=:webLink WHERE scope='System' AND name='webLink'";
            $result = $connection2->prepare($sql);
            $result->execute($data);
        } catch (PDOException $e) {
            $fail = true;
        }

        try {
            $data = array('defaultAssessmentScale' => $defaultAssessmentScale);
            $sql = "UPDATE pupilsightSetting SET value=:defaultAssessmentScale WHERE scope='System' AND name='defaultAssessmentScale'";
            $result = $connection2->prepare($sql);
            $result->execute($data);
        } catch (PDOException $e) {
            $fail = true;
        }

        try {
            $data = array('installType' => $installType);
            $sql = "UPDATE pupilsightSetting SET value=:installType WHERE scope='System' AND name='installType'";
            $result = $connection2->prepare($sql);
            $result->execute($data);
        } catch (PDOException $e) {
            $fail = true;
        }

        try {
            $data = array('statsCollection' => $statsCollection);
            $sql = "UPDATE pupilsightSetting SET value=:statsCollection WHERE scope='System' AND name='statsCollection'";
            $result = $connection2->prepare($sql);
            $result->execute($data);
        } catch (PDOException $e) {
            $fail = true;
        }

        try {
            $data = array('passwordPolicyMinLength' => $passwordPolicyMinLength);
            $sql = "UPDATE pupilsightSetting SET value=:passwordPolicyMinLength WHERE scope='System' AND name='passwordPolicyMinLength'";
            $result = $connection2->prepare($sql);
            $result->execute($data);
        } catch (PDOException $e) {
            $fail = true;
        }
        try {
            $data = array('passwordPolicyAlpha' => $passwordPolicyAlpha);
            $sql = "UPDATE pupilsightSetting SET value=:passwordPolicyAlpha WHERE scope='System' AND name='passwordPolicyAlpha'";
            $result = $connection2->prepare($sql);
            $result->execute($data);
        } catch (PDOException $e) {
            $fail = true;
        }
        try {
            $data = array('passwordPolicyNumeric' => $passwordPolicyNumeric);
            $sql = "UPDATE pupilsightSetting SET value=:passwordPolicyNumeric WHERE scope='System' AND name='passwordPolicyNumeric'";
            $result = $connection2->prepare($sql);
            $result->execute($data);
        } catch (PDOException $e) {
            $fail = true;
        }
        try {
            $data = array('passwordPolicyNonAlphaNumeric' => $passwordPolicyNonAlphaNumeric);
            $sql = "UPDATE pupilsightSetting SET value=:passwordPolicyNonAlphaNumeric WHERE scope='System' AND name='passwordPolicyNonAlphaNumeric'";
            $result = $connection2->prepare($sql);
            $result->execute($data);
        } catch (PDOException $e) {
            $fail = true;
        }
        try {
            $data = array('sessionDuration' => $sessionDuration);
            $sql = "UPDATE pupilsightSetting SET value=:sessionDuration WHERE scope='System' AND name='sessionDuration'";
            $result = $connection2->prepare($sql);
            $result->execute($data);
        } catch (PDOException $e) {
            $fail = true;
        }

        if ($fail == true) {
            $URL .= '&return=error2';
            header("Location: {$URL}");
            exit;
        } elseif ($partialFail == true) {
            $URL .= '&return=warning1';
            header("Location: {$URL}");
            exit;
        } else {
            getSystemSettings($guid, $connection2);
            $_SESSION[$guid]['pageLoads'] = null;
            $URL .= '&return=success0';
            header("Location: {$URL}");
            exit;
        }
    }
}
