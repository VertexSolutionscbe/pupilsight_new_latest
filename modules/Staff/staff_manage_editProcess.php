<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Services\Format;
use Pupilsight\Comms\NotificationEvent;
use Pupilsight\Comms\NotificationSender;
use Pupilsight\Domain\System\NotificationGateway;
use Pupilsight\Domain\System\CustomField;

include '../../pupilsight.php';

//Module includes
include './moduleFunctions.php';

$pupilsightPersonID = $_GET['pupilsightPersonID'];
$URL = $_SESSION[$guid]['absoluteURL'] . '/index.php?q=/modules/' . getModuleName($_POST['address']) . "/staff_manage_edit.php&pupilsightPersonID=$pupilsightPersonID&search=" . $_GET['search'];
$isNotEditAccess = false;
//if (isActionAccessible($guid, $connection2, '/modules/User Admin/user_manage_edit.php') == false) {
if ($isNotEditAccess) {
    $URL .= '&return=error0';
    header("Location: {$URL}");
} else {
    //Proceed!
    //Check if school year specified
    if ($pupilsightPersonID == '') {
        $URL .= '&return=error1';
        header("Location: {$URL}");
    } else {
        try {
            $data = array('pupilsightPersonID' => $pupilsightPersonID);
            $sql = 'SELECT * FROM pupilsightPerson WHERE pupilsightPersonID=:pupilsightPersonID';
            $result = $connection2->prepare($sql);
            $result->execute($data);
        } catch (PDOException $e) {
            $URL .= '&return=error2';
            header("Location: {$URL}");
            exit();
        }

        if ($result->rowCount() != 1) {
            $URL .= '&return=error2';
            header("Location: {$URL}");
        } else {

            //custom Field Added
            $customField  = $container->get(CustomField::class);
            $customField->postCustomField($_POST["custom"], 'pupilsightPersonID', $pupilsightPersonID);

            $row = $result->fetch();

            //Get categories
            $staff = false;
            $student = false;
            $parent = false;
            $other = false;
            $roles = explode(',', $row['pupilsightRoleIDAll']);
            foreach ($roles as $role) {
                $roleCategory = getRoleCategory($role, $connection2);
                if ($roleCategory == 'Staff') {
                    $staff = true;
                }
                if ($roleCategory == 'Student') {
                    $student = true;
                }
                if ($roleCategory == 'Parent') {
                    $parent = true;
                }
                if ($roleCategory == 'Other') {
                    $other = true;
                }
            }

            $attachment1 = $_POST['attachment1'];
            $birthCertificateScan = $_POST['birthCertificateScanCurrent'];
            $nationalIDCardScan = $_POST['nationalIDCardScanCurrent'];
            $citizenship1PassportScan = $_POST['citizenship1PassportScanCurrent'];

            //Proceed!
            $title = $_POST['title'];
            $surname = trim($_POST['surname']);
            $firstName = trim($_POST['firstName']);
            $preferredName = trim($_POST['preferredName']);
            $officialName = trim($_POST['officialName']);
            $nameInCharacters = $_POST['nameInCharacters'];
            $gender = $_POST['gender'];
            $username = isset($_POST['username']) ? $_POST['username'] : $values['username'];
            $status = $_POST['status'];
            $canLogin = $_POST['canLogin'];
            $passwordForceReset = $_POST['passwordForceReset'];

            // Put together an array of this user's current roles
            $currentUserRoles = (is_array($_SESSION[$guid]['pupilsightRoleIDAll'])) ? array_column($_SESSION[$guid]['pupilsightRoleIDAll'], 0) : array();
            $currentUserRoles[] = $_SESSION[$guid]['pupilsightRoleIDPrimary'];

            try {
                $dataRoles = array('pupilsightRoleIDAll' => $row['pupilsightRoleIDAll']);
                $sqlRoles = 'SELECT pupilsightRoleID, restriction, name FROM pupilsightRole';
                $resultRoles = $connection2->prepare($sqlRoles);
                $resultRoles->execute($dataRoles);
            } catch (PDOException $e) {
                echo "<div class='alert alert-danger'>" . $e->getMessage() . '</div>';
            }

            $pupilsightRoleIDAll = array();
            $pupilsightRoleIDPrimary = $row['pupilsightRoleIDPrimary'];

            $selectedRoleIDPrimary = (isset($_POST['pupilsightRoleIDPrimary'])) ? $_POST['pupilsightRoleIDPrimary'] : null;
            $selectedRoleIDAll = (isset($_POST['pupilsightRoleIDAll'])) ? $_POST['pupilsightRoleIDAll'] : array();

            if ($resultRoles && $resultRoles->rowCount() > 0) {
                while ($rowRole = $resultRoles->fetch()) {

                    if ($rowRole['restriction'] == 'Admin Only') {
                        if (in_array('001', $currentUserRoles)) {
                            // Add selected roles only if they meet the restriction
                            if (in_array($rowRole['pupilsightRoleID'], $selectedRoleIDAll)) {
                                $pupilsightRoleIDAll[] = $rowRole['pupilsightRoleID'];
                            }

                            if ($rowRole['pupilsightRoleID'] == $selectedRoleIDPrimary) {
                                // Prevent primary role being changed to a restricted role (via modified POST)
                                $pupilsightRoleIDPrimary = $selectedRoleIDPrimary;
                            }
                        } else if (in_array($rowRole['pupilsightRoleID'], $roles)) {
                            // Add existing restricted roles because they cannot be removed by this user
                            $pupilsightRoleIDAll[] = $rowRole['pupilsightRoleID'];
                        }
                    } else if ($rowRole['restriction'] == 'Same Role') {
                        if (in_array($rowRole['pupilsightRoleID'], $currentUserRoles) || in_array('001', $currentUserRoles)) {
                            if (in_array($rowRole['pupilsightRoleID'], $selectedRoleIDAll)) {
                                $pupilsightRoleIDAll[] = $rowRole['pupilsightRoleID'];
                            }

                            if ($rowRole['pupilsightRoleID'] == $selectedRoleIDPrimary) {
                                $pupilsightRoleIDPrimary = $selectedRoleIDPrimary;
                            }
                        } else if (in_array($rowRole['pupilsightRoleID'], $roles)) {
                            $pupilsightRoleIDAll[] = $rowRole['pupilsightRoleID'];
                        }
                    } else {
                        if (in_array($rowRole['pupilsightRoleID'], $selectedRoleIDAll)) {
                            $pupilsightRoleIDAll[] = $rowRole['pupilsightRoleID'];
                        }

                        if ($rowRole['pupilsightRoleID'] == $selectedRoleIDPrimary) {
                            $pupilsightRoleIDPrimary = $selectedRoleIDPrimary;
                        }
                    }
                }
            }

            // Ensure the primary role is always in the all roles list
            if (!in_array($pupilsightRoleIDPrimary, $pupilsightRoleIDAll)) {
                $pupilsightRoleIDAll[] = $pupilsightRoleIDPrimary;
            }

            $pupilsightRoleIDAll = (is_array($pupilsightRoleIDAll)) ? implode(',', array_unique($pupilsightRoleIDAll)) : $row['pupilsightRoleIDAll'];

            $dob = $_POST['dob'];
            if ($dob == '') {
                $dob = null;
            } else {
                $dob = dateConvert($guid, $dob);
            }
            $email = trim($_POST['email']);
            $emailAlternate = trim($_POST['emailAlternate']);
            $address1 = isset($_POST['address1']) ? $_POST['address1'] : '';
            $address1District = isset($_POST['address1District']) ? $_POST['address1District'] : '';
            $address1Country = isset($_POST['address1Country']) ? $_POST['address1Country'] : '';
            $address2 = isset($_POST['address2']) ? $_POST['address2'] : '';
            $address2District = isset($_POST['address2District']) ? $_POST['address2District'] : '';
            $address2Country = isset($_POST['address2Country']) ? $_POST['address2Country'] : '';
            $phone1Type = $_POST['phone1Type'];
            if ($_POST['phone1'] != '' and $phone1Type == '') {
                $phone1Type = 'Other';
            }
            $phone1CountryCode = $_POST['phone1CountryCode'];
            $phone1 = preg_replace('/[^0-9+]/', '', $_POST['phone1']);
            $phone2Type = $_POST['phone2Type'];
            if ($_POST['phone2'] != '' and $phone2Type == '') {
                $phone2Type = 'Other';
            }
            $phone2CountryCode = $_POST['phone2CountryCode'];
            $phone2 = preg_replace('/[^0-9+]/', '', $_POST['phone2']);
            $phone3Type = $_POST['phone3Type'];
            if ($_POST['phone3'] != '' and $phone3Type == '') {
                $phone3Type = 'Other';
            }
            $phone3CountryCode = $_POST['phone3CountryCode'];
            $phone3 = preg_replace('/[^0-9+]/', '', $_POST['phone3']);
            $phone4Type = $_POST['phone4Type'];
            if ($_POST['phone4'] != '' and $phone4Type == '') {
                $phone4Type = 'Other';
            }
            $phone4CountryCode = $_POST['phone4CountryCode'];
            $phone4 = preg_replace('/[^0-9+]/', '', $_POST['phone4']);
            $website = $_POST['website'];
            $languageFirst = $_POST['languageFirst'];
            $languageSecond = $_POST['languageSecond'];
            $languageThird = $_POST['languageThird'];
            $countryOfBirth = $_POST['countryOfBirth'];
            $ethnicity = $_POST['ethnicity'];
            $citizenship1 = $_POST['citizenship1'];
            $citizenship1Passport = $_POST['citizenship1Passport'];
            $citizenship2 = $_POST['citizenship2'];
            $citizenship2Passport = $_POST['citizenship2Passport'];
            $religion = $_POST['religion'];
            $nationalIDCardNumber = $_POST['nationalIDCardNumber'];
            $residencyStatus = $_POST['residencyStatus'];
            $visaExpiryDate = $_POST['visaExpiryDate'];
            if ($visaExpiryDate == '') {
                $visaExpiryDate = null;
            } else {
                $visaExpiryDate = dateConvert($guid, $visaExpiryDate);
            }

            $profession = null;
            if (isset($_POST['profession'])) {
                $profession = $_POST['profession'];
            }

            $employer = null;
            if (isset($_POST['employer'])) {
                $employer = $_POST['employer'];
            }

            $jobTitle = null;
            if (isset($_POST['jobTitle'])) {
                $jobTitle = $_POST['jobTitle'];
            }

            $emergency1Name = null;
            if (isset($_POST['emergency1Name'])) {
                $emergency1Name = $_POST['emergency1Name'];
            }
            $emergency1Number1 = null;
            if (isset($_POST['emergency1Number1'])) {
                $emergency1Number1 = $_POST['emergency1Number1'];
            }
            $emergency1Number2 = null;
            if (isset($_POST['emergency1Number2'])) {
                $emergency1Number2 = $_POST['emergency1Number2'];
            }
            $emergency1Relationship = null;
            if (isset($_POST['emergency1Relationship'])) {
                $emergency1Relationship = $_POST['emergency1Relationship'];
            }
            $emergency2Name = null;
            if (isset($_POST['emergency2Name'])) {
                $emergency2Name = $_POST['emergency2Name'];
            }
            $emergency2Number1 = null;
            if (isset($_POST['emergency2Number1'])) {
                $emergency2Number1 = $_POST['emergency2Number1'];
            }
            $emergency2Number2 = null;
            if (isset($_POST['emergency2Number2'])) {
                $emergency2Number2 = $_POST['emergency2Number2'];
            }
            $emergency2Relationship = null;
            if (isset($_POST['emergency2Relationship'])) {
                $emergency2Relationship = $_POST['emergency2Relationship'];
            }


            $studentID = null;
            if (isset($_POST['studentID'])) {
                $studentID = $_POST['studentID'];
            }
            $dateStart = $_POST['dateStart'];
            if ($dateStart == '') {
                $dateStart = null;
            } else {
                $dateStart = dateConvert($guid, $dateStart);
            }
            $dateEnd = $_POST['dateEnd'];
            if ($dateEnd == '') {
                $dateEnd = null;
            } else {
                $dateEnd = dateConvert($guid, $dateEnd);
            }
            $pupilsightSchoolYearIDClassOf = null;
            if (isset($_POST['pupilsightSchoolYearIDClassOf'])) {
                $pupilsightSchoolYearIDClassOf = $_POST['pupilsightSchoolYearIDClassOf'];
            }
            $lastSchool = null;
            if (isset($_POST['lastSchool'])) {
                $lastSchool = $_POST['lastSchool'];
            }
            $nextSchool = null;
            if (isset($_POST['nextSchool'])) {
                $nextSchool = $_POST['nextSchool'];
            }
            $departureReason = null;
            if (isset($_POST['departureReason'])) {
                $departureReason = $_POST['departureReason'];
            }
            $transport = null;
            if (isset($_POST['transport'])) {
                $transport = $_POST['transport'];
            }
            $transportNotes = null;
            if (isset($_POST['transportNotes'])) {
                $transportNotes = $_POST['transportNotes'];
            }
            $lockerNumber = null;
            if (isset($_POST['lockerNumber'])) {
                $lockerNumber = $_POST['lockerNumber'];
            }


            $privacyOptions = null;
            $privacy = '';
            if (isset($_POST['privacyOptions'])) {
                $privacyOptions = $_POST['privacyOptions'];
                foreach ($privacyOptions as $privacyOption) {
                    if ($privacyOption != '') {
                        $privacy .= $privacyOption . ',';
                    }
                }
            }
            if ($privacy != '') {
                $privacy = substr($privacy, 0, -1);
            } else {
                $privacy = null;
            }
            $privacy_old = $row['privacy'];

            $studentAgreements = null;
            $agreements = '';
            if (isset($_POST['studentAgreements'])) {
                $studentAgreements = $_POST['studentAgreements'];
                foreach ($studentAgreements as $studentAgreement) {
                    if ($studentAgreement != '') {
                        $agreements .= $studentAgreement . ',';
                    }
                }
            }
            if ($agreements != '') {
                $agreements = substr($agreements, 0, -1);
            } else {
                $agreements = null;
            }

            $dayType = null;
            if (isset($_POST['dayType'])) {
                $dayType = $_POST['dayType'];
            }


            $type = $_POST['type'];
            $jobTitle = $_POST['jobTitle_staff'];
            $dateStart = $_POST['dateStart'];
            if ($dateStart == '') {
                $dateStart = null;
            } else {
                $dateStart = dateConvert($guid, $dateStart);
            }
            $dateEnd = $_POST['dateEnd'];
            if ($dateEnd == '') {
                $dateEnd = null;
            } else {
                $dateEnd = dateConvert($guid, $dateEnd);
            }
            $firstAidQualified = $_POST['firstAidQualified'];
            $firstAidExpiry = null;
            if ($firstAidQualified == 'Y' and $_POST['firstAidExpiry'] != '') {
                $firstAidExpiry = dateConvert($guid, $_POST['firstAidExpiry']);
            }
            $countryOfOrigin = $_POST['countryOfOrigin'];
            $qualifications = $_POST['qualifications'];
            $biographicalGrouping = $_POST['biographicalGrouping'];
            $biographicalGroupingPriority = $_POST['biographicalGroupingPriority'];
            $biography = $_POST['biography'];
            $signature_path = $_POST['signature_path'];

            if (!empty($_POST['is_principle'])) {
                $is_principle = $_POST['is_principle'];

                $allotherprinciple = '0';
                $datau = array('is_principle' => $allotherprinciple);
                $sqlu = 'UPDATE pupilsightStaff SET is_principle=:is_principle';
                $resultu = $connection2->prepare($sqlu);
                $resultu->execute($datau);
            } else {
                $is_principle = '';
            }


            //Validate Inputs
            if ($surname == '' or $firstName == '' or $preferredName == '' or $officialName == '' or $gender == '' or $username == '' or $status == '' or $pupilsightRoleIDPrimary == '') {
                $URL .= '&return=error3';
                header("Location: {$URL}");
            } else {

                try {
                    //Check unique inputs for uniquness
                    try {
                        $data = array('username' => $username, 'pupilsightPersonID' => $pupilsightPersonID);
                        $sql = 'SELECT * FROM pupilsightPerson WHERE username=:username AND NOT pupilsightPersonID=:pupilsightPersonID';
                        if ($studentID != '') {
                            $data = array('username' => $username, 'pupilsightPersonID' => $pupilsightPersonID, 'studentID' => $studentID);
                            $sql = 'SELECT * FROM pupilsightPerson WHERE (username=:username OR studentID=:studentID) AND NOT pupilsightPersonID=:pupilsightPersonID ';
                        }
                        $result = $connection2->prepare($sql);
                        $result->execute($data);
                    } catch (PDOException $e) {
                        $URL .= '&return=error2';
                        header("Location: {$URL}");
                        exit();
                    }

                    if ($result->rowCount() > 0) {
                        $URL .= '&return=error3';
                        header("Location: {$URL}");
                    } else {
                        $imageFail = false;
                        if (!empty($_FILES['file1']['tmp_name']) or !empty($_FILES['birthCertificateScan']['tmp_name']) or !empty($_FILES['nationalIDCardScan']['tmp_name']) or !empty($_FILES['citizenship1PassportScan']['tmp_name'])) {
                            $path = $_SESSION[$guid]['absolutePath'];
                            $fileUploader = new Pupilsight\FileUploader($pdo, $pupilsight->session);

                            //Move 240 attached file, if there is one
                            if (!empty($_FILES['file1']['tmp_name'])) {
                                $file = (isset($_FILES['file1'])) ? $_FILES['file1'] : null;

                                // Upload the file, return the /uploads relative path
                                $fileUploader->setFileSuffixType(Pupilsight\FileUploader::FILE_SUFFIX_INCREMENTAL);
                                $attachment1 = $fileUploader->uploadFromPost($file, $username . '_240');

                                if (empty($attachment1)) {
                                    $imageFail = true;
                                } else {
                                    //Check image sizes
                                    $size1 = getimagesize($path . '/' . $attachment1);
                                    $width1 = $size1[0];
                                    $height1 = $size1[1];
                                    $aspect1 = $height1 / $width1;
                                    if ($width1 > 360 or $height1 > 480 or $aspect1 < 1.2 or $aspect1 > 1.4) {
                                        $attachment1 = '';
                                        $imageFail = true;
                                    }
                                }
                            }

                            //Move birth certificate scan if there is one
                            if (!empty($_FILES['birthCertificateScan']['tmp_name'])) {
                                $file = (isset($_FILES['birthCertificateScan'])) ? $_FILES['birthCertificateScan'] : null;

                                // Upload the file, return the /uploads relative path
                                $fileUploader->setFileSuffixType(Pupilsight\FileUploader::FILE_SUFFIX_ALPHANUMERIC);
                                $birthCertificateScan = $fileUploader->uploadFromPost($file, $username . '_birthCertificate');

                                if (empty($birthCertificateScan)) {
                                    $imageFail = true;
                                } else {
                                    if (stripos($file['tmp_name'], 'pdf') === false) {
                                        //Check image sizes
                                        $size2 = getimagesize($path . '/' . $birthCertificateScan);
                                        $width2 = $size2[0];
                                        $height2 = $size2[1];
                                        if ($width2 > 1440 or $height2 > 900) {
                                            $birthCertificateScan = '';
                                            $imageFail = true;
                                        }
                                    }
                                }
                            }

                            //Move ID Card scan file, if there is one
                            if (!empty($_FILES['nationalIDCardScan']['tmp_name'])) {
                                $file = (isset($_FILES['nationalIDCardScan'])) ? $_FILES['nationalIDCardScan'] : null;

                                // Upload the file, return the /uploads relative path
                                $fileUploader->setFileSuffixType(Pupilsight\FileUploader::FILE_SUFFIX_ALPHANUMERIC);
                                $nationalIDCardScan = $fileUploader->uploadFromPost($file, $username . '_idscan');

                                if (empty($nationalIDCardScan)) {
                                    $imageFail = true;
                                } else {
                                    if (stripos($file['tmp_name'], 'pdf') === false) {
                                        //Check image sizes
                                        $size3 = getimagesize($path . '/' . $nationalIDCardScan);
                                        $width3 = $size3[0];
                                        $height3 = $size3[1];
                                        if ($width3 > 1440 or $height3 > 900) {
                                            $nationalIDCardScan = '';
                                            $imageFail = true;
                                        }
                                    }
                                }
                            }

                            //Move passport scan file, if there is one
                            if (!empty($_FILES['citizenship1PassportScan']['tmp_name'])) {
                                $file = (isset($_FILES['citizenship1PassportScan'])) ? $_FILES['citizenship1PassportScan'] : null;

                                // Upload the file, return the /uploads relative path
                                $fileUploader->setFileSuffixType(Pupilsight\FileUploader::FILE_SUFFIX_ALPHANUMERIC);
                                $citizenship1PassportScan = $fileUploader->uploadFromPost($file, $username . '_passportscan');

                                if (empty($citizenship1PassportScan)) {
                                    $imageFail = true;
                                } else {
                                    if (stripos($file['tmp_name'], 'pdf') === false) {
                                        //Check image sizes
                                        $size4 = getimagesize($path . '/' . $citizenship1PassportScan);
                                        $width4 = $size4[0];
                                        $height4 = $size4[1];
                                        if ($width4 > 1440 or $height4 > 900) {
                                            $citizenship1PassportScan = '';
                                            $imageFail = true;
                                        }
                                    }
                                }
                            }
                        }

                        //DEAL WITH CUSTOM FIELDS
                        //Prepare field values
                        $customRequireFail = false;
                        $resultFields = getCustomFields($connection2, $guid, $student, $staff, $parent, $other);
                        $fields = array();
                        if ($resultFields->rowCount() > 0) {
                            while ($rowFields = $resultFields->fetch()) {
                                if (isset($_POST['custom' . $rowFields['pupilsightPersonFieldID']])) {
                                    if ($rowFields['type'] == 'date') {
                                        $fields[$rowFields['pupilsightPersonFieldID']] = dateConvert($guid, $_POST['custom' . $rowFields['pupilsightPersonFieldID']]);
                                    } else {
                                        $fields[$rowFields['pupilsightPersonFieldID']] = $_POST['custom' . $rowFields['pupilsightPersonFieldID']];
                                    }
                                }
                                if ($rowFields['required'] == 'Y') {
                                    if (isset($_POST['custom' . $rowFields['pupilsightPersonFieldID']]) == false) {
                                        $customRequireFail = true;
                                    } elseif ($_POST['custom' . $rowFields['pupilsightPersonFieldID']] == '') {
                                        $customRequireFail = true;
                                    }
                                }
                            }
                        }

                        if ($customRequireFail) {
                            $URL .= '&return=error3';
                            header("Location: {$URL}");
                        } else {

                            $fields = serialize($fields);
                            //Write to database
                            try {
                                $data = array('title' => $title, 'surname' => $surname, 'firstName' => $firstName, 'preferredName' => $preferredName, 'officialName' => $officialName, 'nameInCharacters' => $nameInCharacters, 'gender' => $gender, 'username' => $username, 'status' => $status, 'canLogin' => $canLogin, 'passwordForceReset' => $passwordForceReset, 'pupilsightRoleIDPrimary' => $pupilsightRoleIDPrimary, 'pupilsightRoleIDAll' => $pupilsightRoleIDAll, 'dob' => $dob, 'email' => $email, 'emailAlternate' => $emailAlternate, 'address1' => $address1, 'address1District' => $address1District, 'address1Country' => $address1Country, 'address2' => $address2, 'address2District' => $address2District, 'address2Country' => $address2Country, 'phone1Type' => $phone1Type, 'phone1CountryCode' => $phone1CountryCode, 'phone1' => $phone1, 'phone2Type' => $phone2Type, 'phone2CountryCode' => $phone2CountryCode, 'phone2' => $phone2, 'phone3Type' => $phone3Type, 'phone3CountryCode' => $phone3CountryCode, 'phone3' => $phone3, 'phone4Type' => $phone4Type, 'phone4CountryCode' => $phone4CountryCode, 'phone4' => $phone4, 'website' => $website, 'languageFirst' => $languageFirst, 'languageSecond' => $languageSecond, 'languageThird' => $languageThird, 'countryOfBirth' => $countryOfBirth, 'birthCertificateScan' => $birthCertificateScan, 'ethnicity' => $ethnicity, 'citizenship1' => $citizenship1, 'citizenship1Passport' => $citizenship1Passport, 'citizenship1PassportScan' => $citizenship1PassportScan, 'citizenship2' => $citizenship2, 'citizenship2Passport' => $citizenship2Passport, 'religion' => $religion, 'nationalIDCardNumber' => $nationalIDCardNumber, 'nationalIDCardScan' => $nationalIDCardScan, 'residencyStatus' => $residencyStatus, 'visaExpiryDate' => $visaExpiryDate, 'emergency1Name' => $emergency1Name, 'emergency1Number1' => $emergency1Number1, 'emergency1Number2' => $emergency1Number2, 'emergency1Relationship' => $emergency1Relationship, 'emergency2Name' => $emergency2Name, 'emergency2Number1' => $emergency2Number1, 'emergency2Number2' => $emergency2Number2, 'emergency2Relationship' => $emergency2Relationship, 'profession' => $profession, 'employer' => $employer, 'jobTitle' => $jobTitle, 'attachment1' => $attachment1, 'studentID' => $studentID, 'dateStart' => $dateStart, 'dateEnd' => $dateEnd, 'pupilsightSchoolYearIDClassOf' => $pupilsightSchoolYearIDClassOf, 'lastSchool' => $lastSchool, 'nextSchool' => $nextSchool, 'departureReason' => $departureReason, 'transport' => $transport, 'transportNotes' => $transportNotes, 'lockerNumber' => $lockerNumber,  'privacy' => $privacy, 'agreements' => $agreements, 'dayType' => $dayType, 'fields' => $fields, 'pupilsightPersonID' => $pupilsightPersonID);
                                $sql = 'UPDATE pupilsightPerson SET title=:title, surname=:surname, firstName=:firstName, preferredName=:preferredName, officialName=:officialName, nameInCharacters=:nameInCharacters, gender=:gender, username=:username, status=:status, canLogin=:canLogin, passwordForceReset=:passwordForceReset, pupilsightRoleIDPrimary=:pupilsightRoleIDPrimary, pupilsightRoleIDAll=:pupilsightRoleIDAll, dob=:dob, email=:email, emailAlternate=:emailAlternate, address1=:address1, address1District=:address1District, address1Country=:address1Country, address2=:address2, address2District=:address2District, address2Country=:address2Country, phone1Type=:phone1Type, phone1CountryCode=:phone1CountryCode, phone1=:phone1, phone2Type=:phone2Type, phone2CountryCode=:phone2CountryCode, phone2=:phone2, phone3Type=:phone3Type, phone3CountryCode=:phone3CountryCode, phone3=:phone3, phone4Type=:phone4Type, phone4CountryCode=:phone4CountryCode, phone4=:phone4, website=:website, languageFirst=:languageFirst, languageSecond=:languageSecond, languageThird=:languageThird, countryOfBirth=:countryOfBirth, birthCertificateScan=:birthCertificateScan, ethnicity=:ethnicity,  citizenship1=:citizenship1, citizenship1Passport=:citizenship1Passport, citizenship1PassportScan=:citizenship1PassportScan, citizenship2=:citizenship2,  citizenship2Passport=:citizenship2Passport, religion=:religion, nationalIDCardNumber=:nationalIDCardNumber, nationalIDCardScan=:nationalIDCardScan, residencyStatus=:residencyStatus, visaExpiryDate=:visaExpiryDate, emergency1Name=:emergency1Name, emergency1Number1=:emergency1Number1, emergency1Number2=:emergency1Number2, emergency1Relationship=:emergency1Relationship, emergency2Name=:emergency2Name, emergency2Number1=:emergency2Number1, emergency2Number2=:emergency2Number2, emergency2Relationship=:emergency2Relationship, profession=:profession, employer=:employer, jobTitle=:jobTitle, image_240=:attachment1,  studentID=:studentID, dateStart=:dateStart, dateEnd=:dateEnd, pupilsightSchoolYearIDClassOf=:pupilsightSchoolYearIDClassOf, lastSchool=:lastSchool, nextSchool=:nextSchool, departureReason=:departureReason, transport=:transport, transportNotes=:transportNotes, lockerNumber=:lockerNumber,  privacy=:privacy, studentAgreements=:agreements, dayType=:dayType, fields=:fields WHERE pupilsightPersonID=:pupilsightPersonID';
                                $result = $connection2->prepare($sql);
                                $result->execute($data);


                                if (isset($_FILES["file"]) && $_FILES["file"]["error"] == 0) {
                                    $filename = $_FILES["file"]["name"];
                                    $filetype = $_FILES["file"]["type"];
                                    $filesize = $_FILES["file"]["size"];

                                    // Verify file extension
                                    $ext = pathinfo($filename, PATHINFO_EXTENSION);


                                    $filename = time() . '_' . $_FILES["file"]["name"];
                                    $fileTarget = $_SERVER['DOCUMENT_ROOT'] . "/public/staff_signature/" . $filename;
                                    if (move_uploaded_file($_FILES["file"]["tmp_name"], $fileTarget)) {
                                        echo "Signature updated successfully";
                                    } else {
                                        echo "No";
                                    }
                                } else {
                                    // echo "Error: " . $_FILES["file"]["error"];
                                    $fileTarget = $signature_path;
                                }


                                $data = array('type' => $type, 'jobTitle' => $jobTitle, 'dateStart' => $dateStart, 'dateEnd' => $dateEnd, 'firstAidQualified' => $firstAidQualified, 'firstAidExpiry' => $firstAidExpiry, 'countryOfOrigin' => $countryOfOrigin, 'qualifications' => $qualifications, 'biographicalGrouping' => $biographicalGrouping, 'biographicalGroupingPriority' => $biographicalGroupingPriority, 'biography' => $biography, 'is_principle' => $is_principle, 'signature_path' => $fileTarget,  'pupilsightPersonID' => $pupilsightPersonID);
                                $sql = 'UPDATE pupilsightStaff JOIN pupilsightPerson ON (pupilsightStaff.pupilsightPersonID=pupilsightPerson.pupilsightPersonID) SET pupilsightStaff.type=:type, pupilsightStaff.jobTitle=:jobTitle, dateStart=:dateStart, dateEnd=:dateEnd, firstAidQualified=:firstAidQualified, firstAidExpiry=:firstAidExpiry, countryOfOrigin=:countryOfOrigin, qualifications=:qualifications, biographicalGrouping=:biographicalGrouping, biographicalGroupingPriority=:biographicalGroupingPriority, biography=:biography, is_principle=:is_principle, signature_path=:signature_path WHERE pupilsightStaff.pupilsightPersonID=:pupilsightPersonID';
                                $result = $connection2->prepare($sql);
                                $result->execute($data);
                            } catch (PDOException $e) {
                                $URL .= '&return=error2';
                                header("Location: {$URL}");
                                exit();
                            }


                            //Deal with change to privacy settings
                            if ($student and getSettingByScope($connection2, 'User Admin', 'privacy') == 'Y') {
                                if ($privacy_old != $privacy) {

                                    //Notify tutor
                                    try {
                                        $dataDetail = array('pupilsightSchoolYearID' => $_SESSION[$guid]['pupilsightSchoolYearID'], 'pupilsightPersonID' => $pupilsightPersonID);
                                        $sqlDetail = 'SELECT pupilsightPersonIDTutor, pupilsightPersonIDTutor2, pupilsightPersonIDTutor3, pupilsightYearGroupID FROM pupilsightRollGroup JOIN pupilsightStudentEnrolment ON (pupilsightStudentEnrolment.pupilsightRollGroupID=pupilsightRollGroup.pupilsightRollGroupID) JOIN pupilsightPerson ON (pupilsightStudentEnrolment.pupilsightPersonID=pupilsightPerson.pupilsightPersonID) WHERE pupilsightStudentEnrolment.pupilsightSchoolYearID=:pupilsightSchoolYearID AND pupilsightStudentEnrolment.pupilsightPersonID=:pupilsightPersonID';
                                        $resultDetail = $connection2->prepare($sqlDetail);
                                        $resultDetail->execute($dataDetail);
                                    } catch (PDOException $e) {
                                    }
                                    if ($resultDetail->rowCount() == 1) {

                                        $rowDetail = $resultDetail->fetch();

                                        // Initialize the notification sender & gateway objects
                                        $notificationGateway = new NotificationGateway($pdo);
                                        $notificationSender = new NotificationSender($notificationGateway, $pupilsight->session);

                                        // Raise a new notification event
                                        $event = new NotificationEvent('Students', 'Updated Privacy Settings');

                                        $staffName = Format::name('', $_SESSION[$guid]['preferredName'], $_SESSION[$guid]['surname'], 'Staff', false, true);
                                        $studentName = Format::name('', $preferredName, $surname, 'Student', false);
                                        $actionLink = "/index.php?q=/modules/Students/student_view_details.php&pupilsightPersonID=$pupilsightPersonID&search=";

                                        $privacyText = __('Privacy') . ' (<i>' . __('New Value') . '</i>): ';
                                        $privacyText .= !empty($privacy) ? $privacy : __('None');

                                        $notificationText = sprintf(__('%1$s has altered the privacy settings for %2$s.'), $staffName, $studentName) . '<br/><br/>';
                                        $notificationText .= $privacyText;

                                        $event->setNotificationText($notificationText);
                                        $event->setActionLink($actionLink);

                                        $event->addScope('pupilsightPersonIDStudent', $pupilsightPersonID);
                                        $event->addScope('pupilsightYearGroupID', $rowDetail['pupilsightYearGroupID']);

                                        // Add event listeners to the notification sender
                                        $event->pushNotifications($notificationGateway, $notificationSender);

                                        // Add direct notifications to roll group tutors
                                        if ($event->getEventDetails($notificationGateway, 'active') == 'Y') {
                                            $notificationText = sprintf(__('Your tutee, %1$s, has had their privacy settings altered.'), $studentName) . '<br/><br/>';
                                            $notificationText .= $privacyText;

                                            if ($rowDetail['pupilsightPersonIDTutor'] != null and $rowDetail['pupilsightPersonIDTutor'] != $_SESSION[$guid]['pupilsightPersonID']) {
                                                $notificationSender->addNotification($rowDetail['pupilsightPersonIDTutor'], $notificationText, 'Students', $actionLink);
                                            }
                                            if ($rowDetail['pupilsightPersonIDTutor2'] != null and $rowDetail['pupilsightPersonIDTutor2'] != $_SESSION[$guid]['pupilsightPersonID']) {
                                                $notificationSender->addNotification($rowDetail['pupilsightPersonIDTutor2'], $notificationText, 'Students', $actionLink);
                                            }
                                            if ($rowDetail['pupilsightPersonIDTutor3'] != null and $rowDetail['pupilsightPersonIDTutor3'] != $_SESSION[$guid]['pupilsightPersonID']) {
                                                $notificationSender->addNotification($rowDetail['pupilsightPersonIDTutor3'], $notificationText, 'Students', $actionLink);
                                            }
                                        }

                                        // Send all notifications
                                        $notificationSender->sendNotifications();
                                    }

                                    //Set log
                                    $pupilsightModuleID = getModuleIDFromName($connection2, 'User Admin');
                                    $privacyValues = array();
                                    $privacyValues['oldValue'] = $privacy_old;
                                    $privacyValues['newValue'] = $privacy;
                                    setLog($connection2, $_SESSION[$guid]["pupilsightSchoolYearID"], $pupilsightModuleID, $_SESSION[$guid]["pupilsightPersonID"], 'Privacy - Value Changed', $privacyValues, $_SERVER['REMOTE_ADDR']);
                                }
                            }


                            //Update matching addresses
                            $partialFail = false;
                            $matchAddressCount = null;
                            if (isset($_POST['matchAddressCount'])) {
                                $matchAddressCount = $_POST['matchAddressCount'];
                            }
                            if ($matchAddressCount > 0) {
                                for ($i = 0; $i < $matchAddressCount; ++$i) {
                                    if (!empty($_POST[$i . '-matchAddress'])) {
                                        try {
                                            $dataAddress = array('address1' => $address1, 'address1District' => $address1District, 'address1Country' => $address1Country, 'pupilsightPersonID' => $_POST[$i . '-matchAddress']);
                                            $sqlAddress = 'UPDATE pupilsightPerson SET address1=:address1, address1District=:address1District, address1Country=:address1Country WHERE pupilsightPersonID=:pupilsightPersonID';
                                            $resultAddress = $connection2->prepare($sqlAddress);
                                            $resultAddress->execute($dataAddress);
                                        } catch (PDOException $e) {
                                            $partialFail = true;
                                        }
                                    }
                                }
                            }

                            if ($partialFail == true) {
                                $URL .= '&return=warning1';
                                header("Location: {$URL}");
                            } else {
                                if ($imageFail) {
                                    $URL .= '&return=warning1';
                                    header("Location: {$URL}");
                                } else {
                                    $URL .= '&return=success0';
                                    header("Location: {$URL}");
                                }
                            }
                        }
                    }
                } catch (Exception $ex) {
                    print_r($ex);
                }
            }
        }
    }
}
