<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Services\Format;
use Pupilsight\Contracts\Comms\Mailer;
use Pupilsight\Comms\NotificationEvent;

include '../../pupilsight.php';

//Check to see if system settings are set from databases
if (empty($_SESSION[$guid]['systemSettingsSet'])) {
    getSystemSettings($guid, $connection2);
}

//Module includes from User Admin (for custom fields)
include '../User Admin/moduleFunctions.php';

//Module includes from Finance (for setting payment log)
include '../Finance/moduleFunctions.php';

$URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/Students/applicationForm.php';

$proceed = false;
$public = false;

if (isset($_SESSION[$guid]['username']) == false) {
    $public = true;
    //Get public access
    $access = getSettingByScope($connection2, 'Application Form', 'publicApplications');
    if ($access == 'Y') {
        $proceed = true;
    }
} else {
    if (isActionAccessible($guid, $connection2, '/modules/Students/applicationForm.php') != false) {
        $proceed = true;
    }
}

if ($proceed == false) {
    $URL .= '&return=error0';
    header("Location: {$URL}");
} else {
    $id = null;
    if (isset($_GET['id'])) {
        $id = $_GET['id'];
    }
    //IF ID IS NOT SET IT IS A NEW APPLICATION, SO PROCESS AND SAVE.
    if (is_null($id)) {
        //Proceed!

        // Sanitize the whole $_POST array
        $validator = new \Pupilsight\Data\Validator();
        $_POST = $validator->sanitize($_POST);

        //GET STUDENT FIELDS
        $surname = $_POST['surname'];
        $firstName = trim($_POST['firstName']);
        $preferredName = trim($_POST['preferredName']);
        $officialName = trim($_POST['officialName']);
        $nameInCharacters = $_POST['nameInCharacters'];
        $gender = $_POST['gender'];
        $dob = $_POST['dob'];
        if ($dob == '') {
            $dob = null;
        } else {
            $dob = dateConvert($guid, $dob);
        }
        $languageHomePrimary = $_POST['languageHomePrimary'];
        $languageHomeSecondary = $_POST['languageHomeSecondary'];
        $languageFirst = $_POST['languageFirst'];
        $languageSecond = $_POST['languageSecond'];
        $languageThird = $_POST['languageThird'];
        $countryOfBirth = $_POST['countryOfBirth'];
        $citizenship1 = $_POST['citizenship1'];
        $citizenship1Passport = $_POST['citizenship1Passport'];
        $nationalIDCardNumber = $_POST['nationalIDCardNumber'];
        $residencyStatus = $_POST['residencyStatus'];
        $visaExpiryDate = $_POST['visaExpiryDate'];
        if ($visaExpiryDate == '') {
            $visaExpiryDate = null;
        } else {
            $visaExpiryDate = dateConvert($guid, $visaExpiryDate);
        }
        $email = (isset($_POST['email']))? trim($_POST['email']) : '';
        $phone1Type = (isset($_POST['phone1Type']))? $_POST['phone1Type'] : '';
        if (!empty($_POST['phone1']) and $phone1Type == '') {
            $phone1Type = 'Other';
        }
        $phone1CountryCode = (isset($_POST['phone1CountryCode']))? $_POST['phone1CountryCode'] : '';
        $phone1 = (isset($_POST['phone1']))? preg_replace('/[^0-9+]/', '', $_POST['phone1']) : '';
        $phone2Type = (isset($_POST['phone2Type']))? $_POST['phone2Type'] : '';
        if (!empty($_POST['phone2']) and $phone2Type == '') {
            $phone2Type = 'Other';
        }
        $phone2CountryCode = (isset($_POST['phone2CountryCode']))? $_POST['phone2CountryCode'] : '';
        $phone2 = (isset($_POST['phone2']))? preg_replace('/[^0-9+]/', '', $_POST['phone2']) : '';

        $medicalInformation = (isset($_POST['medicalInformation']))? $_POST['medicalInformation'] : '';
        $sen = (isset($_POST['sen']))? $_POST['sen'] : 'N';
        if ($sen == 'N') {
            $senDetails = '';
        } else {
            $senDetails = (isset($_POST['senDetails']))? $_POST['senDetails'] : '';
        }
        $pupilsightSchoolYearIDEntry = $_POST['pupilsightSchoolYearIDEntry'];
        $dayType = null;
        if (isset($_POST['dayType'])) {
            $dayType = $_POST['dayType'];
        }
        $dateStart = dateConvert($guid, $_POST['dateStart']);
        $pupilsightYearGroupIDEntry = $_POST['pupilsightYearGroupIDEntry'];
        $referenceEmail = null;
        if (isset($_POST['referenceEmail'])) {
            $referenceEmail = $_POST['referenceEmail'];
        }
        $schoolName1 = $_POST['schoolName1'];
        $schoolAddress1 = $_POST['schoolAddress1'];
        $schoolGrades1 = $_POST['schoolGrades1'];
        $schoolLanguage1 = $_POST['schoolLanguage1'];
        $schoolDate1 = $_POST['schoolDate1'];
        if ($schoolDate1 == '') {
            $schoolDate1 = null;
        } else {
            $schoolDate1 = dateConvert($guid, $schoolDate1);
        }
        $schoolName2 = $_POST['schoolName2'];
        $schoolAddress2 = $_POST['schoolAddress2'];
        $schoolGrades2 = $_POST['schoolGrades2'];
        $schoolLanguage2 = $_POST['schoolLanguage2'];
        $schoolDate2 = $_POST['schoolDate2'];
        if ($schoolDate2 == '') {
            $schoolDate2 = null;
        } else {
            $schoolDate2 = dateConvert($guid, $schoolDate2);
        }

        //GET FAMILY FEILDS
        $pupilsightFamily = $_POST['pupilsightFamily'];
        if ($pupilsightFamily == 'TRUE') {
            $pupilsightFamilyID = $_POST['pupilsightFamilyID'];
        } else {
            $pupilsightFamilyID = null;
        }
        $homeAddress = null;
        if (isset($_POST['homeAddress'])) {
            $homeAddress = $_POST['homeAddress'];
        }
        $homeAddressDistrict = null;
        if (isset($_POST['homeAddressDistrict'])) {
            $homeAddressDistrict = $_POST['homeAddressDistrict'];
        }
        $homeAddressCountry = null;
        if (isset($_POST['homeAddressCountry'])) {
            $homeAddressCountry = $_POST['homeAddressCountry'];
        }

        //GET PARENT1 FEILDS
        $parent1pupilsightPersonID = null;
        if (isset($_POST['parent1pupilsightPersonID'])) {
            $parent1pupilsightPersonID = $_POST['parent1pupilsightPersonID'];
        }
        $parent1title = null;
        if (isset($_POST['parent1title'])) {
            $parent1title = $_POST['parent1title'];
        }
        $parent1surname = null;
        if (isset($_POST['parent1surname'])) {
            $parent1surname = trim($_POST['parent1surname']);
        }
        $parent1firstName = null;
        if (isset($_POST['parent1firstName'])) {
            $parent1firstName = trim($_POST['parent1firstName']);
        }
        $parent1preferredName = null;
        if (isset($_POST['parent1preferredName'])) {
            $parent1preferredName = trim($_POST['parent1preferredName']);
        }
        $parent1officialName = null;
        if (isset($_POST['parent1officialName'])) {
            $parent1officialName = trim($_POST['parent1officialName']);
        }
        $parent1nameInCharacters = null;
        if (isset($_POST['parent1nameInCharacters'])) {
            $parent1nameInCharacters = $_POST['parent1nameInCharacters'];
        }
        $parent1gender = null;
        if (isset($_POST['parent1gender'])) {
            $parent1gender = $_POST['parent1gender'];
        }
        $parent1relationship = null;
        if (isset($_POST['parent1relationship'])) {
            $parent1relationship = $_POST['parent1relationship'];
        }
        $parent1languageFirst = null;
        if (isset($_POST['parent1languageFirst'])) {
            $parent1languageFirst = $_POST['parent1languageFirst'];
        }
        $parent1languageSecond = null;
        if (isset($_POST['parent1languageSecond'])) {
            $parent1languageSecond = $_POST['parent1languageSecond'];
        }
        $parent1citizenship1 = null;
        if (isset($_POST['parent1citizenship1'])) {
            $parent1citizenship1 = $_POST['parent1citizenship1'];
        }
        $parent1nationalIDCardNumber = null;
        if (isset($_POST['parent1nationalIDCardNumber'])) {
            $parent1nationalIDCardNumber = $_POST['parent1nationalIDCardNumber'];
        }
        $parent1residencyStatus = null;
        if (isset($_POST['parent1residencyStatus'])) {
            $parent1residencyStatus = $_POST['parent1residencyStatus'];
        }
        $parent1visaExpiryDate = null;
        if (isset($_POST['parent1visaExpiryDate'])) {
            if ($_POST['parent1visaExpiryDate'] != '') {
                $parent1visaExpiryDate = dateConvert($guid, $_POST['parent1visaExpiryDate']);
            }
        }
        $parent1email = null;
        if (isset($_POST['parent1email'])) {
            $parent1email = trim($_POST['parent1email']);
        }
        $parent1phone1Type = null;
        if (isset($_POST['parent1phone1Type'])) {
            $parent1phone1Type = $_POST['parent1phone1Type'];
        }
        if (isset($_POST['parent1phone1']) and $parent1phone1Type == '') {
            $parent1phone1Type = 'Other';
        }
        $parent1phone1CountryCode = null;
        if (isset($_POST['parent1phone1CountryCode'])) {
            $parent1phone1CountryCode = $_POST['parent1phone1CountryCode'];
        }
        $parent1phone1 = null;
        if (isset($_POST['parent1phone1'])) {
            $parent1phone1 = $_POST['parent1phone1'];
        }
        $parent1phone2Type = null;
        if (isset($_POST['parent1phone2Type'])) {
            $parent1phone2Type = $_POST['parent1phone2Type'];
        }
        if (isset($_POST['parent1phone2']) and $parent1phone2Type == '') {
            $parent1phone2Type = 'Other';
        }
        $parent1phone2CountryCode = null;
        if (isset($_POST['parent1phone2CountryCode'])) {
            $parent1phone2CountryCode = $_POST['parent1phone2CountryCode'];
        }
        $parent1phone2 = null;
        if (isset($_POST['parent1phone2'])) {
            $parent1phone2 = $_POST['parent1phone2'];
        }
        $parent1profession = null;
        if (isset($_POST['parent1profession'])) {
            $parent1profession = $_POST['parent1profession'];
        }
        $parent1employer = null;
        if (isset($_POST['parent1employer'])) {
            $parent1employer = $_POST['parent1employer'];
        }

        //GET PARENT2 FEILDS
        $parent2title = null;
        if (isset($_POST['parent2title'])) {
            $parent2title = $_POST['parent2title'];
        }
        $parent2surname = null;
        if (isset($_POST['parent2surname'])) {
            $parent2surname = trim($_POST['parent2surname']);
        }
        $parent2firstName = null;
        if (isset($_POST['parent2firstName'])) {
            $parent2firstName = trim($_POST['parent2firstName']);
        }
        $parent2preferredName = null;
        if (isset($_POST['parent2preferredName'])) {
            $parent2preferredName = trim($_POST['parent2preferredName']);
        }
        $parent2officialName = null;
        if (isset($_POST['parent2officialName'])) {
            $parent2officialName = trim($_POST['parent2officialName']);
        }
        $parent2nameInCharacters = null;
        if (isset($_POST['parent2nameInCharacters'])) {
            $parent2nameInCharacters = $_POST['parent2nameInCharacters'];
        }
        $parent2gender = null;
        if (isset($_POST['parent2gender'])) {
            $parent2gender = $_POST['parent2gender'];
        }
        $parent2relationship = null;
        if (isset($_POST['parent2relationship'])) {
            $parent2relationship = $_POST['parent2relationship'];
        }
        $parent2languageFirst = null;
        if (isset($_POST['parent2languageFirst'])) {
            $parent2languageFirst = $_POST['parent2languageFirst'];
        }
        $parent2languageSecond = null;
        if (isset($_POST['parent2languageSecond'])) {
            $parent2languageSecond = $_POST['parent2languageSecond'];
        }
        $parent2citizenship1 = null;
        if (isset($_POST['parent2citizenship1'])) {
            $parent2citizenship1 = $_POST['parent2citizenship1'];
        }
        $parent2nationalIDCardNumber = null;
        if (isset($_POST['parent2nationalIDCardNumber'])) {
            $parent2nationalIDCardNumber = $_POST['parent2nationalIDCardNumber'];
        }
        $parent2residencyStatus = null;
        if (isset($_POST['parent2residencyStatus'])) {
            $parent2residencyStatus = $_POST['parent2residencyStatus'];
        }
        $parent2visaExpiryDate = null;
        if (isset($_POST['parent2visaExpiryDate'])) {
            if ($_POST['parent2visaExpiryDate'] != '') {
                $parent2visaExpiryDate = dateConvert($guid, $_POST['parent2visaExpiryDate']);
            }
        }
        $parent2email = null;
        if (isset($_POST['parent2email'])) {
            $parent2email = trim($_POST['parent2email']);
        }
        $parent2phone1Type = null;
        if (isset($_POST['parent2phone1Type'])) {
            $parent2phone1Type = $_POST['parent2phone1Type'];
        }
        if (isset($_POST['parent2phone1']) and $parent2phone1Type == '') {
            $parent2phone1Type = 'Other';
        }
        $parent2phone1CountryCode = null;
        if (isset($_POST['parent2phone1CountryCode'])) {
            $parent2phone1CountryCode = $_POST['parent2phone1CountryCode'];
        }
        $parent2phone1 = null;
        if (isset($_POST['parent2phone1'])) {
            $parent2phone1 = $_POST['parent2phone1'];
        }
        $parent2phone2Type = null;
        if (isset($_POST['parent2phone2Type'])) {
            $parent2phone2Type = $_POST['parent2phone2Type'];
        }
        if (isset($_POST['parent2phone2']) and $parent2phone2Type == '') {
            $parent2phone2Type = 'Other';
        }
        $parent2phone2CountryCode = null;
        if (isset($_POST['parent2phone2CountryCode'])) {
            $parent2phone2CountryCode = $_POST['parent2phone2CountryCode'];
        }
        $parent2phone2 = null;
        if (isset($_POST['parent2phone2'])) {
            $parent2phone2 = $_POST['parent2phone2'];
        }
        $parent2profession = null;
        if (isset($_POST['parent2profession'])) {
            $parent2profession = $_POST['parent2profession'];
        }
        $parent2employer = null;
        if (isset($_POST['parent2employer'])) {
            $parent2employer = $_POST['parent2employer'];
        }

        //GET SIBLING FIELDS
        $siblingName1 = $_POST['siblingName1'];
        $siblingDOB1 = $_POST['siblingDOB1'];
        if ($siblingDOB1 == '') {
            $siblingDOB1 = null;
        } else {
            $siblingDOB1 = dateConvert($guid, $siblingDOB1);
        }
        $siblingSchool1 = $_POST['siblingSchool1'];
        $siblingSchoolJoiningDate1 = $_POST['siblingSchoolJoiningDate1'];
        if ($siblingSchoolJoiningDate1 == '') {
            $siblingSchoolJoiningDate1 = null;
        } else {
            $siblingSchoolJoiningDate1 = dateConvert($guid, $siblingSchoolJoiningDate1);
        }
        $siblingName2 = $_POST['siblingName2'];
        $siblingDOB2 = $_POST['siblingDOB2'];
        if ($siblingDOB2 == '') {
            $siblingDOB2 = null;
        } else {
            $siblingDOB2 = dateConvert($guid, $siblingDOB2);
        }
        $siblingSchool2 = $_POST['siblingSchool2'];
        $siblingSchoolJoiningDate2 = $_POST['siblingSchoolJoiningDate2'];
        if ($siblingSchoolJoiningDate2 == '') {
            $siblingSchoolJoiningDate2 = null;
        } else {
            $siblingSchoolJoiningDate2 = dateConvert($guid, $siblingSchoolJoiningDate2);
        }
        $siblingName3 = $_POST['siblingName3'];
        $siblingDOB3 = $_POST['siblingDOB3'];
        if ($siblingDOB3 == '') {
            $siblingDOB3 = null;
        } else {
            $siblingDOB3 = dateConvert($guid, $siblingDOB3);
        }
        $siblingSchool3 = $_POST['siblingSchool3'];
        $siblingSchoolJoiningDate3 = $_POST['siblingSchoolJoiningDate3'];
        if ($siblingSchoolJoiningDate3 == '') {
            $siblingSchoolJoiningDate3 = null;
        } else {
            $siblingSchoolJoiningDate3 = dateConvert($guid, $siblingSchoolJoiningDate3);
        }

        //GET PAYMENT FIELDS
        $payment = (isset($_POST['payment']))? $_POST['payment'] : '';
        $companyName = null;
        if (isset($_POST['companyName'])) {
            $companyName = $_POST['companyName'];
        }
        $companyContact = null;
        if (isset($_POST['companyContact'])) {
            $companyContact = $_POST['companyContact'];
        }
        $companyAddress = null;
        if (isset($_POST['companyAddress'])) {
            $companyAddress = $_POST['companyAddress'];
        }
        $companyEmail = null;
        if (isset($_POST['companyEmail'])) {
            $companyEmail = $_POST['companyEmail'];
        }
        $companyCCFamily = null;
        if (isset($_POST['companyCCFamily'])) {
            $companyCCFamily = $_POST['companyCCFamily'];
        }
        $companyPhone = null;
        if (isset($_POST['companyPhone'])) {
            $companyPhone = $_POST['companyPhone'];
        }
        $companyAll = null;
        if (isset($_POST['companyAll'])) {
            $companyAll = $_POST['companyAll'];
        }
        $pupilsightFinanceFeeCategoryIDList = null;
        if (isset($_POST['pupilsightFinanceFeeCategoryIDList'])) {
            $pupilsightFinanceFeeCategoryIDArray = $_POST['pupilsightFinanceFeeCategoryIDList'];
            if (count($pupilsightFinanceFeeCategoryIDArray) > 0) {
                foreach ($pupilsightFinanceFeeCategoryIDArray as $pupilsightFinanceFeeCategoryID) {
                    $pupilsightFinanceFeeCategoryIDList .= $pupilsightFinanceFeeCategoryID.',';
                }
                $pupilsightFinanceFeeCategoryIDList = substr($pupilsightFinanceFeeCategoryIDList, 0, -1);
            }
        }

        //GET OTHER FIELDS
        $languageChoice = null;
        if (isset($_POST['languageChoice'])) {
            $languageChoice = $_POST['languageChoice'];
        }
        $languageChoiceExperience = null;
        if (isset($_POST['languageChoiceExperience'])) {
            $languageChoiceExperience = $_POST['languageChoiceExperience'];
        }
        $scholarshipInterest = '';
        if (isset($_POST['scholarshipInterest'])) {
            $scholarshipInterest = $_POST['scholarshipInterest'];
        }
        $scholarshipRequired = '';
        if (isset($_POST['scholarshipRequired'])) {
            $scholarshipRequired = $_POST['scholarshipRequired'];
        }
        $howDidYouHear = null;
        if (isset($_POST['howDidYouHear'])) {
            $howDidYouHear = $_POST['howDidYouHear'];
        }
        $howDidYouHearMore = null;
        if (isset($_POST['howDidYouHearMore'])) {
            $howDidYouHearMore = $_POST['howDidYouHearMore'];
        }
        $agreement = null;
        if (isset($_POST['agreement'])) {
            if ($_POST['agreement'] == 'on') {
                $agreement = 'Y';
            } else {
                $agreement = 'N';
            }
        }
        $privacy = null;
        if (isset($_POST['privacyOptions'])) {
            $privacyOptions = $_POST['privacyOptions'];
            foreach ($privacyOptions as $privacyOption) {
                if ($privacyOption != '') {
                    $privacy .= $privacyOption.', ';
                }
            }
            if ($privacy != '') {
                $privacy = substr($privacy, 0, -2);
            } else {
                $privacy = null;
            }
        }

        //VALIDATE INPUTS
        $familyFail = false;
        if ($pupilsightFamily == 'TRUE') {
            if ($pupilsightFamilyID == '') {
                $familyFail = true;
            }
        } else {
            if ($homeAddress == '' or $homeAddressDistrict == '' or $homeAddressCountry == '') {
                $familyFail = true;
            }
            if ($parent1pupilsightPersonID == null) {
                if ($parent1title == '' or $parent1surname == '' or $parent1firstName == '' or $parent1preferredName == '' or $parent1officialName == '' or $parent1gender == '' or $parent1relationship == '' or $parent1phone1 == '' or $parent1profession == '') {
                    $familyFail = true;
                }
            }
            if (isset($_POST['secondParent'])) {
                if ($_POST['secondParent'] != 'No') {
                    if ($parent2title == '' or $parent2surname == '' or $parent2firstName == '' or $parent2preferredName == '' or $parent2officialName == '' or $parent2gender == '' or $parent2relationship == '' or $parent2phone1 == '' or $parent2profession == '') {
                        $familyFail = true;
                    }
                }
            }
        }
        if ($surname == '' or $firstName == '' or $preferredName == '' or $officialName == '' or $gender == '' or $dob == '' or $languageHomePrimary == '' or $languageFirst == '' or $countryOfBirth == '' or $citizenship1 == '' or $pupilsightSchoolYearIDEntry == '' or $dateStart == '' or $pupilsightYearGroupIDEntry == '' or $sen == '' or $howDidYouHear == '' or (isset($_POST['agreement']) and $agreement != 'Y') or $familyFail) {
            $URL .= '&return=error1';
            header("Location: {$URL}");
        } else {
            //DEAL WITH CUSTOM FIELDS
            $customRequireFail = false;
            //Prepare field values
            //CHILD
            $resultFields = getCustomFields($connection2, $guid, true, false, false, false, true, null);
            $fields = array();
            if ($resultFields->rowCount() > 0) {
                while ($rowFields = $resultFields->fetch()) {
                    if (isset($_POST['custom'.$rowFields['pupilsightPersonFieldID']])) {
                        if ($rowFields['type'] == 'date') {
                            $fields[$rowFields['pupilsightPersonFieldID']] = dateConvert($guid, $_POST['custom'.$rowFields['pupilsightPersonFieldID']]);
                        } else {
                            $fields[$rowFields['pupilsightPersonFieldID']] = $_POST['custom'.$rowFields['pupilsightPersonFieldID']];
                        }
                    }
                    if ($rowFields['required'] == 'Y') {
                        if (isset($_POST['custom'.$rowFields['pupilsightPersonFieldID']]) == false) {
                            $customRequireFail = true;
                        } elseif ($_POST['custom'.$rowFields['pupilsightPersonFieldID']] == '') {
                            $customRequireFail = true;
                        }
                    }
                }
            }
            if ($pupilsightFamily == 'FALSE') { //Only if there is no family
                //PARENT 1
                $resultFields = getCustomFields($connection2, $guid, false, false, true, false, true, null);
                $parent1fields = array();
                if ($resultFields->rowCount() > 0) {
                    while ($rowFields = $resultFields->fetch()) {
                        if (isset($_POST['parent1custom'.$rowFields['pupilsightPersonFieldID']])) {
                            if ($rowFields['type'] == 'date') {
                                $parent1fields[$rowFields['pupilsightPersonFieldID']] = dateConvert($guid, $_POST['parent1custom'.$rowFields['pupilsightPersonFieldID']]);
                            } else {
                                $parent1fields[$rowFields['pupilsightPersonFieldID']] = $_POST['parent1custom'.$rowFields['pupilsightPersonFieldID']];
                            }
                        }
                        if ($rowFields['required'] == 'Y') {
                            if (isset($_POST['parent1custom'.$rowFields['pupilsightPersonFieldID']]) == false) {
                                $customRequireFail = true;
                            } elseif ($_POST['parent1custom'.$rowFields['pupilsightPersonFieldID']] == '') {
                                $customRequireFail = true;
                            }
                        }
                    }
                }
                if (isset($_POST['secondParent']) == false) {
                    //PARENT 2
                    $resultFields = getCustomFields($connection2, $guid, false, false, true, false, true, null);
                    $parent2fields = array();
                    if ($resultFields->rowCount() > 0) {
                        while ($rowFields = $resultFields->fetch()) {
                            if (isset($_POST['parent2custom'.$rowFields['pupilsightPersonFieldID']])) {
                                if ($rowFields['type'] == 'date') {
                                    $parent2fields[$rowFields['pupilsightPersonFieldID']] = dateConvert($guid, $_POST['parent2custom'.$rowFields['pupilsightPersonFieldID']]);
                                } else {
                                    $parent2fields[$rowFields['pupilsightPersonFieldID']] = $_POST['parent2custom'.$rowFields['pupilsightPersonFieldID']];
                                }
                            }
                            if ($rowFields['required'] == 'Y') {
                                if (isset($_POST['parent2custom'.$rowFields['pupilsightPersonFieldID']]) == false) {
                                    $customRequireFail = true;
                                } elseif ($_POST['parent2custom'.$rowFields['pupilsightPersonFieldID']] == '') {
                                    $customRequireFail = true;
                                }
                            }
                        }
                    }
                }
            }

            if ($customRequireFail) {
                $URL .= '&return=error1';
                header("Location: {$URL}");
                exit();
            } else {
                $fields = serialize($fields);
                if (isset($parent1fields)) {
                    $parent1fields = serialize($parent1fields);
                } else {
                    $parent1fields = '';
                }
                if (isset($parent2fields)) {
                    $parent2fields = serialize($parent2fields);
                } else {
                    $parent2fields = '';
                }

                //Write to database
                try {
                    $data = array('surname' => $surname, 'firstName' => $firstName, 'preferredName' => $preferredName, 'officialName' => $officialName, 'nameInCharacters' => $nameInCharacters, 'gender' => $gender, 'dob' => $dob, 'languageHomePrimary' => $languageHomePrimary, 'languageHomeSecondary' => $languageHomeSecondary, 'languageFirst' => $languageFirst, 'languageSecond' => $languageSecond, 'languageThird' => $languageThird, 'countryOfBirth' => $countryOfBirth, 'citizenship1' => $citizenship1, 'citizenship1Passport' => $citizenship1Passport, 'nationalIDCardNumber' => $nationalIDCardNumber, 'residencyStatus' => $residencyStatus, 'visaExpiryDate' => $visaExpiryDate, 'email' => $email, 'homeAddress' => $homeAddress, 'homeAddressDistrict' => $homeAddressDistrict, 'homeAddressCountry' => $homeAddressCountry, 'phone1Type' => $phone1Type, 'phone1CountryCode' => $phone1CountryCode, 'phone1' => $phone1, 'phone2Type' => $phone2Type, 'phone2CountryCode' => $phone2CountryCode, 'phone2' => $phone2, 'medicalInformation' => $medicalInformation, 'sen' => $sen, 'senDetails' => $senDetails, 'pupilsightSchoolYearIDEntry' => $pupilsightSchoolYearIDEntry, 'dayType' => $dayType, 'dateStart' => $dateStart, 'pupilsightYearGroupIDEntry' => $pupilsightYearGroupIDEntry, 'referenceEmail' => $referenceEmail, 'schoolName1' => $schoolName1, 'schoolAddress1' => $schoolAddress1, 'schoolGrades1' => $schoolGrades1, 'schoolLanguage1' => $schoolLanguage1, 'schoolDate1' => $schoolDate1, 'schoolName2' => $schoolName2, 'schoolAddress2' => $schoolAddress2, 'schoolGrades2' => $schoolGrades2, 'schoolLanguage2' => $schoolLanguage2, 'schoolDate2' => $schoolDate2, 'pupilsightFamilyID' => $pupilsightFamilyID, 'parent1pupilsightPersonID' => $parent1pupilsightPersonID, 'parent1title' => $parent1title, 'parent1surname' => $parent1surname, 'parent1firstName' => $parent1firstName, 'parent1preferredName' => $parent1preferredName, 'parent1officialName' => $parent1officialName, 'parent1nameInCharacters' => $parent1nameInCharacters, 'parent1gender' => $parent1gender, 'parent1relationship' => $parent1relationship, 'parent1languageFirst' => $parent1languageFirst, 'parent1languageSecond' => $parent1languageSecond, 'parent1citizenship1' => $parent1citizenship1, 'parent1nationalIDCardNumber' => $parent1nationalIDCardNumber, 'parent1residencyStatus' => $parent1residencyStatus, 'parent1visaExpiryDate' => $parent1visaExpiryDate, 'parent1email' => $parent1email, 'parent1phone1Type' => $parent1phone1Type, 'parent1phone1CountryCode' => $parent1phone1CountryCode, 'parent1phone1' => $parent1phone1, 'parent1phone2Type' => $parent1phone2Type, 'parent1phone2CountryCode' => $parent1phone2CountryCode, 'parent1phone2' => $parent1phone2, 'parent1profession' => $parent1profession, 'parent1employer' => $parent1employer, 'parent2title' => $parent2title, 'parent2surname' => $parent2surname, 'parent2firstName' => $parent2firstName, 'parent2preferredName' => $parent2preferredName, 'parent2officialName' => $parent2officialName, 'parent2nameInCharacters' => $parent2nameInCharacters, 'parent2gender' => $parent2gender, 'parent2relationship' => $parent2relationship, 'parent2languageFirst' => $parent2languageFirst, 'parent2languageSecond' => $parent2languageSecond, 'parent2citizenship1' => $parent2citizenship1, 'parent2nationalIDCardNumber' => $parent2nationalIDCardNumber, 'parent2residencyStatus' => $parent2residencyStatus, 'parent2visaExpiryDate' => $parent2visaExpiryDate, 'parent2email' => $parent2email, 'parent2phone1Type' => $parent2phone1Type, 'parent2phone1CountryCode' => $parent2phone1CountryCode, 'parent2phone1' => $parent2phone1, 'parent2phone2Type' => $parent2phone2Type, 'parent2phone2CountryCode' => $parent2phone2CountryCode, 'parent2phone2' => $parent2phone2, 'parent2profession' => $parent2profession, 'parent2employer' => $parent2employer, 'siblingName1' => $siblingName1, 'siblingDOB1' => $siblingDOB1, 'siblingSchool1' => $siblingSchool1, 'siblingSchoolJoiningDate1' => $siblingSchoolJoiningDate1, 'siblingName2' => $siblingName2, 'siblingDOB2' => $siblingDOB2, 'siblingSchool2' => $siblingSchool2, 'siblingSchoolJoiningDate2' => $siblingSchoolJoiningDate2, 'siblingName3' => $siblingName3, 'siblingDOB3' => $siblingDOB3, 'siblingSchool3' => $siblingSchool3, 'siblingSchoolJoiningDate3' => $siblingSchoolJoiningDate3, 'languageChoice' => $languageChoice, 'languageChoiceExperience' => $languageChoiceExperience, 'scholarshipInterest' => $scholarshipInterest, 'scholarshipRequired' => $scholarshipRequired, 'payment' => $payment, 'companyName' => $companyName, 'companyContact' => $companyContact, 'companyAddress' => $companyAddress, 'companyEmail' => $companyEmail, 'companyCCFamily' => $companyCCFamily, 'companyPhone' => $companyPhone, 'companyAll' => $companyAll, 'pupilsightFinanceFeeCategoryIDList' => $pupilsightFinanceFeeCategoryIDList, 'howDidYouHear' => $howDidYouHear, 'howDidYouHearMore' => $howDidYouHearMore, 'agreement' => $agreement, 'privacy' => $privacy, 'fields' => $fields, 'parent1fields' => $parent1fields, 'parent2fields' => $parent2fields, 'timestamp' => date('Y-m-d H:i:s'));
                    $sql = 'INSERT INTO pupilsightApplicationForm SET surname=:surname, firstName=:firstName, preferredName=:preferredName, officialName=:officialName, nameInCharacters=:nameInCharacters, gender=:gender, dob=:dob, languageHomePrimary=:languageHomePrimary, languageHomeSecondary=:languageHomeSecondary, languageFirst=:languageFirst, languageSecond=:languageSecond, languageThird=:languageThird, countryOfBirth=:countryOfBirth, citizenship1=:citizenship1, citizenship1Passport=:citizenship1Passport, nationalIDCardNumber=:nationalIDCardNumber, residencyStatus=:residencyStatus, visaExpiryDate=:visaExpiryDate, email=:email, homeAddress=:homeAddress, homeAddressDistrict=:homeAddressDistrict, homeAddressCountry=:homeAddressCountry, phone1Type=:phone1Type, phone1CountryCode=:phone1CountryCode, phone1=:phone1, phone2Type=:phone2Type, phone2CountryCode=:phone2CountryCode, phone2=:phone2, medicalInformation=:medicalInformation, sen=:sen, senDetails=:senDetails, pupilsightSchoolYearIDEntry=:pupilsightSchoolYearIDEntry, dateStart=:dateStart, pupilsightYearGroupIDEntry=:pupilsightYearGroupIDEntry, dayType=:dayType, referenceEmail=:referenceEmail, schoolName1=:schoolName1, schoolAddress1=:schoolAddress1, schoolGrades1=:schoolGrades1, schoolLanguage1=:schoolLanguage1, schoolDate1=:schoolDate1, schoolName2=:schoolName2, schoolAddress2=:schoolAddress2, schoolGrades2=:schoolGrades2, schoolLanguage2=:schoolLanguage2, schoolDate2=:schoolDate2, pupilsightFamilyID=:pupilsightFamilyID, parent1pupilsightPersonID=:parent1pupilsightPersonID, parent1title=:parent1title, parent1surname=:parent1surname, parent1firstName=:parent1firstName, parent1preferredName=:parent1preferredName, parent1officialName=:parent1officialName, parent1nameInCharacters=:parent1nameInCharacters, parent1gender=:parent1gender, parent1relationship=:parent1relationship, parent1languageFirst=:parent1languageFirst, parent1languageSecond=:parent1languageSecond, parent1citizenship1=:parent1citizenship1, parent1nationalIDCardNumber=:parent1nationalIDCardNumber, parent1residencyStatus=:parent1residencyStatus, parent1visaExpiryDate=:parent1visaExpiryDate, parent1email=:parent1email, parent1phone1Type=:parent1phone1Type, parent1phone1CountryCode=:parent1phone1CountryCode, parent1phone1=:parent1phone1, parent1phone2Type=:parent1phone2Type, parent1phone2CountryCode=:parent1phone2CountryCode, parent1phone2=:parent1phone2, parent1profession=:parent1profession, parent1employer=:parent1employer, parent2title=:parent2title, parent2surname=:parent2surname, parent2firstName=:parent2firstName, parent2preferredName=:parent2preferredName, parent2officialName=:parent2officialName, parent2nameInCharacters=:parent2nameInCharacters, parent2gender=:parent2gender, parent2relationship=:parent2relationship, parent2languageFirst=:parent2languageFirst, parent2languageSecond=:parent2languageSecond, parent2citizenship1=:parent2citizenship1, parent2nationalIDCardNumber=:parent2nationalIDCardNumber, parent2residencyStatus=:parent2residencyStatus, parent2visaExpiryDate=:parent2visaExpiryDate, parent2email=:parent2email, parent2phone1Type=:parent2phone1Type, parent2phone1CountryCode=:parent2phone1CountryCode, parent2phone1=:parent2phone1, parent2phone2Type=:parent2phone2Type, parent2phone2CountryCode=:parent2phone2CountryCode, parent2phone2=:parent2phone2, parent2profession=:parent2profession, parent2employer=:parent2employer, siblingName1=:siblingName1, siblingDOB1=:siblingDOB1, siblingSchool1=:siblingSchool1, siblingSchoolJoiningDate1=:siblingSchoolJoiningDate1, siblingName2=:siblingName2, siblingDOB2=:siblingDOB2, siblingSchool2=:siblingSchool2, siblingSchoolJoiningDate2=:siblingSchoolJoiningDate2, siblingName3=:siblingName3, siblingDOB3=:siblingDOB3, siblingSchool3=:siblingSchool3, siblingSchoolJoiningDate3=:siblingSchoolJoiningDate3, languageChoice=:languageChoice, languageChoiceExperience=:languageChoiceExperience, scholarshipInterest=:scholarshipInterest, scholarshipRequired=:scholarshipRequired, payment=:payment, companyName=:companyName, companyContact=:companyContact, companyAddress=:companyAddress, companyEmail=:companyEmail, companyCCFamily=:companyCCFamily, companyPhone=:companyPhone, companyAll=:companyAll, pupilsightFinanceFeeCategoryIDList=:pupilsightFinanceFeeCategoryIDList, howDidYouHear=:howDidYouHear, howDidYouHearMore=:howDidYouHearMore, agreement=:agreement, privacy=:privacy, fields=:fields, parent1fields=:parent1fields, parent2fields=:parent2fields, timestamp=:timestamp';
                    $result = $connection2->prepare($sql);
                    $result->execute($data);
                } catch (PDOException $e) {
                    $URL .= '&return=error2';
                    header("Location: {$URL}");
                    exit();
                }

                //Last insert ID
                $AI = str_pad($connection2->lastInsertID(), 7, '0', STR_PAD_LEFT);
                $secureAI = sha1($AI.'X2J53ZGy'.$guid.$pupilsightSchoolYearIDEntry);

                // Update the Application Form with a hash for looking up this record in the future
                try {
                    $data = array('pupilsightApplicationFormID' => $AI, 'pupilsightApplicationFormHash' => $secureAI );
                    $sql = 'UPDATE pupilsightApplicationForm SET pupilsightApplicationFormHash=:pupilsightApplicationFormHash WHERE pupilsightApplicationFormID=:pupilsightApplicationFormID';
                    $result = $connection2->prepare($sql);
                    $result->execute($data);
                } catch (PDOException $e) {
                }

                //Deal with family relationships
                if ($pupilsightFamily == 'TRUE') {
                    $relationships = $_POST[$pupilsightFamilyID.'-relationships'];
                    $relationshipsPupilsightPersonIDs = $_POST[$pupilsightFamilyID.'-relationshipsPupilsightPersonID'];
                    $count = 0;
                    foreach ($relationships as $relationship) {
                        try {
                            $data = array('pupilsightApplicationFormID' => $AI, 'pupilsightPersonID' => $relationshipsPupilsightPersonIDs[$count], 'relationship' => $relationship);
                            $sql = 'INSERT INTO pupilsightApplicationFormRelationship SET pupilsightApplicationFormID=:pupilsightApplicationFormID, pupilsightPersonID=:pupilsightPersonID, relationship=:relationship';
                            $result = $connection2->prepare($sql);
                            $result->execute($data);
                        } catch (PDOException $e) {
                        }
                        ++$count;
                    }
                }

                //Deal with required documents
                $requiredDocuments = getSettingByScope($connection2, 'Application Form', 'requiredDocuments');
                if ($requiredDocuments != '' and $requiredDocuments != false) {
                    $fileCount = 0;
                    if (isset($_POST['fileCount'])) {
                        $fileCount = $_POST['fileCount'];
                    }

                    $fileUploader = new Pupilsight\FileUploader($pdo, $pupilsight->session);

                    for ($i = 0; $i < $fileCount; ++$i) {
                        if (empty($_FILES["file$i"]['tmp_name'])) continue;

                        $file = (isset($_FILES["file$i"]))? $_FILES["file$i"] : null;
                        $fileName = (isset($_POST["fileName$i"]))? $_POST["fileName$i"] : null;

                        // Upload the file, return the /uploads relative path
                        $attachment = $fileUploader->uploadFromPost($file, 'ApplicationDocument');

                        // Write files to database, if there is one
                        if (!empty($attachment)) {
                            try {
                                $dataFile = array('pupilsightApplicationFormID' => $AI, 'name' => $fileName, 'path' => $attachment);
                                $sqlFile = 'INSERT INTO pupilsightApplicationFormFile SET pupilsightApplicationFormID=:pupilsightApplicationFormID, name=:name, path=:path';
                                $resultFile = $connection2->prepare($sqlFile);
                                $resultFile->execute($dataFile);
                            } catch (PDOException $e) {
                            }
                        }
                    }
                }

                // Raise a new notification event
                $event = new NotificationEvent('Students', 'New Application Form');

                $event->addRecipient($_SESSION[$guid]['organisationAdmissions']);
                $event->setNotificationText(sprintf(__('An application form has been submitted for %1$s.'), Format::name('', $preferredName, $surname, 'Student')));
                $event->setActionLink("/index.php?q=/modules/Students/applicationForm_manage_edit.php&pupilsightApplicationFormID=$AI&pupilsightSchoolYearID=$pupilsightSchoolYearIDEntry&search=");

                $event->sendNotifications($pdo, $pupilsight->session);


                //Email reference form link to referee
                $applicationFormRefereeLink = getSettingByScope($connection2, 'Students', 'applicationFormRefereeLink');
                if ($applicationFormRefereeLink != '' and $referenceEmail != '' and $_SESSION[$guid]['organisationAdmissionsName'] != '' and $_SESSION[$guid]['organisationAdmissionsEmail'] != '') {
                    //Prep message
                    $subject = __('Request For Reference');
                    $body = sprintf(__('To whom it may concern,%4$sThis email is being sent in relation to the application of a current or former student of your school: %1$s.%4$sIn assessing their application for our school, we would like to enlist your help in completing the following reference form: %2$s.<br/><br/>Please feel free to contact me, should you have any questions in regard to this matter.%4$sRegards,%4$s%3$s'), $officialName, "<a href='$applicationFormRefereeLink' target='_blank'>$applicationFormRefereeLink</a>", $_SESSION[$guid]['organisationAdmissionsName'], '<br/><br/>');
                    $body .= "<p style='font-style: italic;'>".sprintf(__('Email sent via %1$s at %2$s.'), $_SESSION[$guid]['systemName'], $_SESSION[$guid]['organisationName']).'</p>';
                    $bodyPlain = emailBodyConvert($body);
                    $mail = $container->get(Mailer::class);
                    $mail->SetFrom($_SESSION[$guid]['organisationAdmissionsEmail'], $_SESSION[$guid]['organisationAdmissionsName']);
                    $mail->AddAddress($referenceEmail);
                    $mail->CharSet = 'UTF-8';
                    $mail->Encoding = 'base64';
                    $mail->IsHTML(true);
                    $mail->Subject = $subject;
                    $mail->Body = $body;
                    $mail->AltBody = $bodyPlain;
                    $mail->Send();

                    $data=array('email'=>$referenceEmail);
                    $sql="SELECT pupilsightPersonID FROM pupilsightPerson WHERE email=:email";
                    $result = $connection2->prepare($sql);
                    $result->execute($data);
                    if ($result->rowCount() > 0) {
                        while ($rowppid = $result->fetch()) {
                            $ppid = $rowppid['pupilsightPersonID'];


                            $msgby = $_SESSION[$guid]["pupilsightPersonID"];
                            $msgto = $ppid;

                            $sqlAI = "SHOW TABLE STATUS LIKE 'pupilsightMessenger'";
                            $resultAI = $connection2->query($sqlAI);
                            $rowAI = $resultAI->fetch();
                            $AI = str_pad($rowAI['Auto_increment'], 12, "0", STR_PAD_LEFT);

                            $email = "Y";
                            $messageWall = "N";
                            $sms = "N";
                            $date1 = date('Y-m-d');
                            $data = array("email" => $email, "messageWall" => $messageWall, "messageWall_date1" => $date1, "sms" => $sms, "subject" => $subject, "body" => $body, "pupilsightPersonID" => $_SESSION[$guid]["pupilsightPersonID"], "category" => 'Other', "timestamp" => date("Y-m-d H:i:s"));
                            $sql = "INSERT INTO pupilsightMessenger SET email=:email, messageWall=:messageWall, messageWall_date1=:messageWall_date1, sms=:sms, subject=:subject, body=:body, pupilsightPersonID=:pupilsightPersonID,messengercategory=:category, timestamp=:timestamp";
                            $result = $connection2->prepare($sql);
                            $result->execute($data);

                            $data = array("AI" => $AI, "t" => $msgto);
                            $sql = "INSERT INTO pupilsightMessengerTarget SET pupilsightMessengerID=:AI, type='Individuals', id=:t";
                            $result = $connection2->prepare($sql);
                            $result->execute($data);
                        }
                    }
                }

                $skipEmailNotification = (isset($_POST['skipEmailNotification']))? $_POST['skipEmailNotification'] : false;

                //Notify parent 1 of application status
                if (!empty($parent1email) && !$skipEmailNotification) {
                    $body = sprintf(__('Dear Parent%1$sThank you for applying for a student place at %2$s.'), '<br/><br/>', $_SESSION[$guid]['organisationName']).' ';
                    $body .= __('Your application was successfully submitted. Our admissions team will review your application and be in touch in due course.').'<br/><br/>';
                    $body .= __('You may continue submitting applications for siblings with the form below and they will be linked to your family data.').'<br/><br/>';
                    $body .= "<a href='{$URL}&id={$secureAI}'>{$URL}&id={$secureAI}</a><br/><br/>";
                    $body .= sprintf(__('In the meantime, should you have any questions please contact %1$s at %2$s.'), $_SESSION[$guid]['organisationAdmissionsName'], $_SESSION[$guid]['organisationAdmissionsEmail']).'<br/><br/>';
                    $body .= "<p style='font-style: italic;'>".sprintf(__('Email sent via %1$s at %2$s.'), $_SESSION[$guid]['systemName'], $_SESSION[$guid]['organisationName']).'</p>';
                    $bodyPlain = emailBodyConvert($body);
                    $mail = $container->get(Mailer::class);
                    $mail->SetFrom($_SESSION[$guid]['organisationAdmissionsEmail'], $_SESSION[$guid]['organisationAdmissionsName']);
                    $mail->AddAddress($parent1email);
                    $mail->CharSet = 'UTF-8';
                    $mail->Encoding = 'base64';
                    $mail->IsHTML(true);
                    $mail->Subject = sprintf(__('%1$s Application Form Confirmation'), $_SESSION[$guid]['organisationName']);
                    $mail->Body = $body;
                    $mail->AltBody = $bodyPlain;
                    $mail->Send();

                    $data=array('email'=>$parent1email);
                    $sql="SELECT pupilsightPersonID FROM pupilsightPerson WHERE email=:email";
                    $result = $connection2->prepare($sql);
                    $result->execute($data);
                    if ($result->rowCount() > 0) {
                        while ($rowppid = $result->fetch()) {
                            $ppid = $rowppid['pupilsightPersonID'];


                            $msgby = $_SESSION[$guid]["pupilsightPersonID"];
                            $msgto = $ppid;

                            $sqlAI = "SHOW TABLE STATUS LIKE 'pupilsightMessenger'";
                            $resultAI = $connection2->query($sqlAI);
                            $rowAI = $resultAI->fetch();
                            $AI = str_pad($rowAI['Auto_increment'], 12, "0", STR_PAD_LEFT);

                            $email = "Y";
                            $messageWall = "N";
                            $sms = "N";
                            $date1 = date('Y-m-d');
                            $data = array("email" => $email, "messageWall" => $messageWall, "messageWall_date1" => $date1, "sms" => $sms, "subject" => $subject, "body" => $body, "pupilsightPersonID" => $_SESSION[$guid]["pupilsightPersonID"], "category" => 'Other', "timestamp" => date("Y-m-d H:i:s"));
                            $sql = "INSERT INTO pupilsightMessenger SET email=:email, messageWall=:messageWall, messageWall_date1=:messageWall_date1, sms=:sms, subject=:subject, body=:body, pupilsightPersonID=:pupilsightPersonID,messengercategory=:category, timestamp=:timestamp";
                            $result = $connection2->prepare($sql);
                            $result->execute($data);

                            $data = array("AI" => $AI, "t" => $msgto);
                            $sql = "INSERT INTO pupilsightMessengerTarget SET pupilsightMessengerID=:AI, type='Individuals', id=:t";
                            $result = $connection2->prepare($sql);
                            $result->execute($data);
                        }
                    }
                }

                // Handle Sibling Applications
                if (!empty($_POST['linkedApplicationFormID'])) {
                    $data = array( 'pupilsightApplicationFormID' => $_POST['linkedApplicationFormID'] );
                    $sql = 'SELECT DISTINCT pupilsightApplicationFormID FROM pupilsightApplicationForm
                            LEFT JOIN pupilsightApplicationFormLink ON (pupilsightApplicationForm.pupilsightApplicationFormID=pupilsightApplicationFormLink.pupilsightApplicationFormID1 OR pupilsightApplicationForm.pupilsightApplicationFormID=pupilsightApplicationFormLink.pupilsightApplicationFormID2)
                            WHERE (pupilsightApplicationFormID=:pupilsightApplicationFormID AND pupilsightApplicationFormLinkID IS NULL)
                            OR pupilsightApplicationFormID1=:pupilsightApplicationFormID
                            OR pupilsightApplicationFormID2=:pupilsightApplicationFormID';
                    $resultLinked = $pdo->executeQuery($data, $sql);

                    if ($resultLinked && $resultLinked->rowCount() > 0) {
                        // Create a new link to each existing form
                        while ($linkedApplication = $resultLinked->fetch()) {
                            $data = array( 'pupilsightApplicationFormID1' => $AI, 'pupilsightApplicationFormID2' => $linkedApplication['pupilsightApplicationFormID'] );
                            $sql = "INSERT INTO pupilsightApplicationFormLink SET pupilsightApplicationFormID1=:pupilsightApplicationFormID1, pupilsightApplicationFormID2=:pupilsightApplicationFormID2 ON DUPLICATE KEY UPDATE timestamp=NOW()";
                            $resultNewLink = $pdo->executeQuery($data, $sql);
                        }
                    }
                }

                //Attempt payment if everything is set up for it
                $applicationFee = getSettingByScope($connection2, 'Application Form', 'applicationFee');
                $enablePayments = getSettingByScope($connection2, 'System', 'enablePayments');
                $paypalAPIUsername = getSettingByScope($connection2, 'System', 'paypalAPIUsername');
                $paypalAPIPassword = getSettingByScope($connection2, 'System', 'paypalAPIPassword');
                $paypalAPISignature = getSettingByScope($connection2, 'System', 'paypalAPISignature');

                if ($applicationFee > 0 and is_numeric($applicationFee) and $enablePayments == 'Y' and $paypalAPIUsername != '' and $paypalAPIPassword != '' and $paypalAPISignature != '') {
                    $_SESSION[$guid]['gatewayCurrencyNoSupportReturnURL'] = $_SESSION[$guid]['absoluteURL']."/index.php?q=/modules/Students/applicationForm.php&return=success4&id=$secureAI";
                    $URL = $_SESSION[$guid]['absoluteURL']."/lib/paypal/expresscheckout.php?Payment_Amount=$applicationFee&return=".urlencode("modules/Students/applicationFormProcess.php?return=success1&id=$secureAI&applicationFee=$applicationFee").'&fail='.urlencode("modules/Students/applicationFormProcess.php?return=success2&id=$secureAI&applicationFee=$applicationFee");
                    header("Location: {$URL}");
                } else {
                    $URL .= "&return=success0&id=$secureAI";
                    header("Location: {$URL}");
                }
            }
        }
    }
    //IF ID IS SET WE ARE JUST RETURNING TO FINALISE PAYMENT AND RECORD OF PAYMENT, SO LET'S DO IT.
    else {
        //Get returned paypal tokens, ids, etc
        $paymentMade = 'N';
        if ($_GET['return'] == 'success1') {
            $paymentMade = 'Y';
        }
        $paymentToken = null;
        if (isset($_GET['token'])) {
            $paymentToken = $_GET['token'];
        }
        $paymentPayerID = null;
        if (isset($_GET['PayerID'])) {
            $paymentPayerID = $_GET['PayerID'];
        }
        $pupilsightApplicationFormID = null;
        if (isset($_GET['id'])) {
            // Find the ID based on the hash provided for added security
            $data = array( 'pupilsightApplicationFormHash' => $_GET['id'] );
            $sql = "SELECT pupilsightApplicationFormID FROM pupilsightApplicationForm WHERE pupilsightApplicationFormHash=:pupilsightApplicationFormHash";
            $resultID = $pdo->executeQuery($data, $sql);

            if ($resultID && $resultID->rowCount() == 1) {
                $pupilsightApplicationFormID = $resultID->fetchColumn(0);
            }
        }
        $applicationFee = null;
        if (isset($_GET['applicationFee'])) {
            $applicationFee = $_GET['applicationFee'];
        }

        //Get email parameters ready to send messages for to admissions for payment problems
        $to = $_SESSION[$guid]['organisationAdmissionsEmail'];
        $subject = $_SESSION[$guid]['organisationNameShort'].' Pupilsight Application Form Payment Issue';

        //Check return values to see if we can proceed
        if ($paymentToken == '' or $pupilsightApplicationFormID == '' or $applicationFee == '') {
            $body = __('Payment via PayPal may or may not have been successful, but has not been recorded either way due to a system error. Please check your PayPal account for details. The following may be useful:')."<br/><br/>Payment Token: $paymentToken<br/><br/>Payer ID: $paymentPayerID<br/><br/>Application Form ID: $pupilsightApplicationFormID<br/><br/>Application Fee: $applicationFee<br/><br/>".$_SESSION[$guid]['systemName'].' '.__('Admissions Administrator');
            $body .= "<p style='font-style: italic;'>".sprintf(__('Email sent via %1$s at %2$s.'), $_SESSION[$guid]['systemName'], $_SESSION[$guid]['organisationName']).'</p>';
            $bodyPlain = emailBodyConvert($body);

            $mail = $container->get(Mailer::class);
            $mail->SetFrom($_SESSION[$guid]['organisationAdmissionsEmail'], $_SESSION[$guid]['organisationAdmissionsName']);
            $mail->AddAddress($to);
            $mail->CharSet = 'UTF-8';
            $mail->Encoding = 'base64';
            $mail->IsHTML(true);
            $mail->Subject = $subject;
            $mail->Body = $body;
            $mail->AltBody = $bodyPlain;
            $mail->Send();

            $data=array('email'=>$to);
            $sql="SELECT pupilsightPersonID FROM pupilsightPerson WHERE email=:email";
            $result = $connection2->prepare($sql);
            $result->execute($data);
            if ($result->rowCount() > 0) {
                while ($rowppid = $result->fetch()) {
                    $ppid = $rowppid['pupilsightPersonID'];


                    $msgby = $_SESSION[$guid]["pupilsightPersonID"];
                    $msgto = $ppid;

                    $sqlAI = "SHOW TABLE STATUS LIKE 'pupilsightMessenger'";
                    $resultAI = $connection2->query($sqlAI);
                    $rowAI = $resultAI->fetch();
                    $AI = str_pad($rowAI['Auto_increment'], 12, "0", STR_PAD_LEFT);

                    $email = "Y";
                    $messageWall = "N";
                    $sms = "N";
                    $date1 = date('Y-m-d');
                    $data = array("email" => $email, "messageWall" => $messageWall, "messageWall_date1" => $date1, "sms" => $sms, "subject" => $subject, "body" => $body, "pupilsightPersonID" => $_SESSION[$guid]["pupilsightPersonID"], "category" => 'Other', "timestamp" => date("Y-m-d H:i:s"));
                    $sql = "INSERT INTO pupilsightMessenger SET email=:email, messageWall=:messageWall, messageWall_date1=:messageWall_date1, sms=:sms, subject=:subject, body=:body, pupilsightPersonID=:pupilsightPersonID,messengercategory=:category, timestamp=:timestamp";
                    $result = $connection2->prepare($sql);
                    $result->execute($data);

                    $data = array("AI" => $AI, "t" => $msgto);
                    $sql = "INSERT INTO pupilsightMessengerTarget SET pupilsightMessengerID=:AI, type='Individuals', id=:t";
                    $result = $connection2->prepare($sql);
                    $result->execute($data);
                }
            }
            //Success 2
            $URL .= '&return=success2&id='.$_GET['id'];
            header("Location: {$URL}");
            exit();
        } else {
            //PROCEED AND FINALISE PAYMENT
            require '../../lib/paypal/paypalfunctions.php';

            //Ask paypal to finalise the payment
            $confirmPayment = confirmPayment($guid, $applicationFee, $paymentToken, $paymentPayerID);

            $ACK = $confirmPayment['ACK'];
            $paymentTransactionID = $confirmPayment['PAYMENTINFO_0_TRANSACTIONID'];
            $paymentReceiptID = $confirmPayment['PAYMENTINFO_0_RECEIPTID'];

            //Payment was successful. Yeah!
            if ($ACK == 'Success') {
                $updateFail = false;

                //Save payment details to pupilsightPayment
                $pupilsightPaymentID = setPaymentLog($connection2, $guid, 'pupilsightApplicationForm', $pupilsightApplicationFormID, 'Online', 'Complete', $applicationFee, 'Paypal', 'Success', $paymentToken, $paymentPayerID, $paymentTransactionID, $paymentReceiptID);

                //Link pupilsightPayment record to pupilsightApplicationForm, and make note that payment made
                if ($pupilsightPaymentID != '') {
                    try {
                        $data = array('paymentMade' => $paymentMade, 'pupilsightPaymentID' => $pupilsightPaymentID, 'pupilsightApplicationFormID' => $pupilsightApplicationFormID);
                        $sql = 'UPDATE pupilsightApplicationForm SET paymentMade=:paymentMade, pupilsightPaymentID=:pupilsightPaymentID WHERE pupilsightApplicationFormID=:pupilsightApplicationFormID';
                        $result = $connection2->prepare($sql);
                        $result->execute($data);
                    } catch (PDOException $e) {
                        $updateFail = true;
                    }
                } else {
                    $updateFail = true;
                }

                if ($updateFail == true) {
                    $body = __('Payment via PayPal was successful, but has not been recorded due to a system error. Please check your PayPal account for details. The following may be useful:')."<br/><br/>Payment Token: $paymentToken<br/><br/>Payer ID: $paymentPayerID<br/><br/>Application Form ID: $pupilsightApplicationFormID<br/><br/>Application Fee: $applicationFee<br/><br/>".$_SESSION[$guid]['systemName'].' '.__('Admissions Administrator');
                    $body .= "<p style='font-style: italic;'>".sprintf(__('Email sent via %1$s at %2$s.'), $_SESSION[$guid]['systemName'], $_SESSION[$guid]['organisationName']).'</p>';
                    $bodyPlain = emailBodyConvert($body);

                    $mail = $container->get(Mailer::class);
                    $mail->SetFrom($_SESSION[$guid]['organisationAdmissionsEmail'], $_SESSION[$guid]['organisationAdmissionsName']);
                    $mail->AddAddress($to);
                    $mail->CharSet = 'UTF-8';
                    $mail->Encoding = 'base64';
                    $mail->IsHTML(true);
                    $mail->Subject = $subject;
                    $mail->Body = $body;
                    $mail->AltBody = $bodyPlain;
                    $mail->Send();

                    $data=array('email'=>$to);
                    $sql="SELECT pupilsightPersonID FROM pupilsightPerson WHERE email=:email";
                    $result = $connection2->prepare($sql);
                    $result->execute($data);
                    if ($result->rowCount() > 0) {
                        while ($rowppid = $result->fetch()) {
                            $ppid = $rowppid['pupilsightPersonID'];


                            $msgby = $_SESSION[$guid]["pupilsightPersonID"];
                            $msgto = $ppid;

                            $sqlAI = "SHOW TABLE STATUS LIKE 'pupilsightMessenger'";
                            $resultAI = $connection2->query($sqlAI);
                            $rowAI = $resultAI->fetch();
                            $AI = str_pad($rowAI['Auto_increment'], 12, "0", STR_PAD_LEFT);

                            $email = "Y";
                            $messageWall = "N";
                            $sms = "N";
                            $date1 = date('Y-m-d');
                            $data = array("email" => $email, "messageWall" => $messageWall, "messageWall_date1" => $date1, "sms" => $sms, "subject" => $subject, "body" => $body, "pupilsightPersonID" => $_SESSION[$guid]["pupilsightPersonID"], "category" => 'Other', "timestamp" => date("Y-m-d H:i:s"));
                            $sql = "INSERT INTO pupilsightMessenger SET email=:email, messageWall=:messageWall, messageWall_date1=:messageWall_date1, sms=:sms, subject=:subject, body=:body, pupilsightPersonID=:pupilsightPersonID,messengercategory=:category, timestamp=:timestamp";
                            $result = $connection2->prepare($sql);
                            $result->execute($data);

                            $data = array("AI" => $AI, "t" => $msgto);
                            $sql = "INSERT INTO pupilsightMessengerTarget SET pupilsightMessengerID=:AI, type='Individuals', id=:t";
                            $result = $connection2->prepare($sql);
                            $result->execute($data);
                        }
                    }

                    $URL .= '&return=success3&id='.$_GET['id'];
                    header("Location: {$URL}");
                    exit;
                }

                $URL .= '&return=success1&id='.$_GET['id'];
                header("Location: {$URL}");
            } else {
                $updateFail = false;

                //Save payment details to pupilsightPayment
                $pupilsightPaymentID = setPaymentLog($connection2, $guid, 'pupilsightApplicationForm', $pupilsightApplicationFormID, 'Online', 'Failure', $applicationFee, 'Paypal', 'Failure', $paymentToken, $paymentPayerID, $paymentTransactionID, $paymentReceiptID);

                //Link pupilsightPayment record to pupilsightApplicationForm, and make note that payment made
                if ($pupilsightPaymentID != '') {
                    try {
                        $data = array('paymentMade' => $paymentMade, 'pupilsightPaymentID' => $pupilsightPaymentID, 'pupilsightApplicationFormID' => $pupilsightApplicationFormID);
                        $sql = 'UPDATE pupilsightApplicationForm SET paymentMade=:paymentMade, pupilsightPaymentID=:pupilsightPaymentID WHERE pupilsightApplicationFormID=:pupilsightApplicationFormID';
                        $result = $connection2->prepare($sql);
                        $result->execute($data);
                    } catch (PDOException $e) {
                        $updateFail = true;
                    }
                } else {
                    $updateFail = true;
                }

                if ($updateFail == true) {
                    $body = __('Payment via PayPal was unsuccessful, and has also not been recorded due to a system error. Please check your PayPal account for details. The following may be useful:')."<br/><br/>Payment Token: $paymentToken<br/><br/>Payer ID: $paymentPayerID<br/><br/>Application Form ID: $pupilsightApplicationFormID<br/><br/>Application Fee: $applicationFee<br/><br/>".$_SESSION[$guid]['systemName'].' '.__('Admissions Administrator');
                    $body .= "<p style='font-style: italic;'>".sprintf(__('Email sent via %1$s at %2$s.'), $_SESSION[$guid]['systemName'], $_SESSION[$guid]['organisationName']).'</p>';
                    $bodyPlain = emailBodyConvert($body);

                    $mail = $container->get(Mailer::class);
                    $mail->SetFrom($_SESSION[$guid]['organisationAdmissionsEmail'], $_SESSION[$guid]['organisationAdmissionsName']);
                    $mail->AddAddress($to);
                    $mail->CharSet = 'UTF-8';
                    $mail->Encoding = 'base64';
                    $mail->IsHTML(true);
                    $mail->Subject = $subject;
                    $mail->Body = $body;
                    $mail->AltBody = $bodyPlain;
                    $mail->Send();

                    $data=array('email'=>$to);
                    $sql="SELECT pupilsightPersonID FROM pupilsightPerson WHERE email=:email";
                    $result = $connection2->prepare($sql);
                    $result->execute($data);
                    if ($result->rowCount() > 0) {
                        while ($rowppid = $result->fetch()) {
                            $ppid = $rowppid['pupilsightPersonID'];


                            $msgby = $_SESSION[$guid]["pupilsightPersonID"];
                            $msgto = $ppid;

                            $sqlAI = "SHOW TABLE STATUS LIKE 'pupilsightMessenger'";
                            $resultAI = $connection2->query($sqlAI);
                            $rowAI = $resultAI->fetch();
                            $AI = str_pad($rowAI['Auto_increment'], 12, "0", STR_PAD_LEFT);

                            $email = "Y";
                            $messageWall = "N";
                            $sms = "N";
                            $date1 = date('Y-m-d');
                            $data = array("email" => $email, "messageWall" => $messageWall, "messageWall_date1" => $date1, "sms" => $sms, "subject" => $subject, "body" => $body, "pupilsightPersonID" => $_SESSION[$guid]["pupilsightPersonID"], "category" => 'Other', "timestamp" => date("Y-m-d H:i:s"));
                            $sql = "INSERT INTO pupilsightMessenger SET email=:email, messageWall=:messageWall, messageWall_date1=:messageWall_date1, sms=:sms, subject=:subject, body=:body, pupilsightPersonID=:pupilsightPersonID,messengercategory=:category, timestamp=:timestamp";
                            $result = $connection2->prepare($sql);
                            $result->execute($data);

                            $data = array("AI" => $AI, "t" => $msgto);
                            $sql = "INSERT INTO pupilsightMessengerTarget SET pupilsightMessengerID=:AI, type='Individuals', id=:t";
                            $result = $connection2->prepare($sql);
                            $result->execute($data);
                        }
                    }
                    //Success 2
                    $URL .= '&return=success2&id='.$_GET['id'];
                    header("Location: {$URL}");
                    exit;
                }

                //Success 2
                $URL .= '&return=success2&id='.$_GET['id'];
                header("Location: {$URL}");
            }
        }
    }
}
