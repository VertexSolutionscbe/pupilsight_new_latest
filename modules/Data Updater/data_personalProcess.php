<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Comms\NotificationEvent;
use Pupilsight\Services\Format;

include '../../pupilsight.php';

//Module includes for User Admin (for custom fields)
include '../User Admin/moduleFunctions.php';

$pupilsightPersonID = $_GET['pupilsightPersonID'];
$URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_POST['address'])."/data_personal.php&pupilsightPersonID=$pupilsightPersonID";

if (isActionAccessible($guid, $connection2, '/modules/Data Updater/data_personal.php') == false) {
    $URL .= '&return=error0';
    header("Location: {$URL}");
} else {
    //Proceed!
    //Check if school year specified
    if ($pupilsightPersonID == '') {
        $URL .= '&return=error1';
        header("Location: {$URL}");
    } else {
        //Get action with highest precendence
        $highestAction = getHighestGroupedAction($guid, $_POST['address'], $connection2);
        if ($highestAction == false) {
            $URL .= "&return=error0$params";
            header("Location: {$URL}");
        } else {
            //Check access to person
            $checkCount = 0;
            $self = false;
            if ($highestAction == 'Update Personal Data_any') {
                $URLSuccess = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/Data Updater/data_personal.php&pupilsightPersonID='.$pupilsightPersonID;
                
                try {
                    $dataSelect = array('pupilsightPersonID' => $pupilsightPersonID);
                    $sqlSelect = "SELECT surname, preferredName, pupilsightPerson.pupilsightPersonID, pupilsightRoleIDAll FROM pupilsightPerson WHERE status='Full' AND pupilsightPersonID=:pupilsightPersonID ORDER BY surname, preferredName";
                    $resultSelect = $connection2->prepare($sqlSelect);
                    $resultSelect->execute($dataSelect);
                } catch (PDOException $e) {
                }
                $checkCount = $resultSelect->rowCount();
                $self = true;
            } else {
                $URLSuccess = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/Data Updater/data_updates.php&pupilsightPersonID='.$pupilsightPersonID;
                
                try {
                    $dataCheck = array('pupilsightPersonID' => $_SESSION[$guid]['pupilsightPersonID']);
                    $sqlCheck = "SELECT pupilsightFamilyAdult.pupilsightFamilyID, name FROM pupilsightFamilyAdult JOIN pupilsightFamily ON (pupilsightFamilyAdult.pupilsightFamilyID=pupilsightFamily.pupilsightFamilyID) WHERE pupilsightPersonID=:pupilsightPersonID AND childDataAccess='Y' ORDER BY name";
                    $resultCheck = $connection2->prepare($sqlCheck);
                    $resultCheck->execute($dataCheck);
                } catch (PDOException $e) {
                    echo $e->getMessage();
                }
                while ($rowCheck = $resultCheck->fetch()) {
                    try {
                        $dataCheck2 = array('pupilsightFamilyID1' => $rowCheck['pupilsightFamilyID'], 'pupilsightFamilyID2' => $rowCheck['pupilsightFamilyID']);
                        $sqlCheck2 = "(SELECT surname, preferredName, pupilsightPerson.pupilsightPersonID, pupilsightFamilyID, pupilsightRoleIDAll FROM pupilsightFamilyChild JOIN pupilsightPerson ON (pupilsightFamilyChild.pupilsightPersonID=pupilsightPerson.pupilsightPersonID) WHERE pupilsightPerson.status='Full' AND pupilsightFamilyID=:pupilsightFamilyID1) UNION (SELECT surname, preferredName, pupilsightPerson.pupilsightPersonID, pupilsightFamilyID, pupilsightRoleIDAll FROM pupilsightFamilyAdult JOIN pupilsightPerson ON (pupilsightFamilyAdult.pupilsightPersonID=pupilsightPerson.pupilsightPersonID) WHERE pupilsightPerson.status='Full' AND pupilsightFamilyID=:pupilsightFamilyID2)";
                        $resultCheck2 = $connection2->prepare($sqlCheck2);
                        $resultCheck2->execute($dataCheck2);
                    } catch (PDOException $e) {
                    }
                    while ($rowCheck2 = $resultCheck2->fetch()) {
                        if ($pupilsightPersonID == $rowCheck2['pupilsightPersonID']) {
                            ++$checkCount;
                        }
                        //Check for self
                        if ($rowCheck2['pupilsightPersonID'] == $_SESSION[$guid]['pupilsightPersonID']) {
                            $self = true;
                        }
                    }
                }
            }

            if ($self == false and $pupilsightPersonID == $_SESSION[$guid]['pupilsightPersonID']) {
                ++$checkCount;
            }

            if ($checkCount < 1) {
                $URL .= '&return=error2';
                header("Location: {$URL}");
            } else {
                //Get user data
                try {
                    $data = array('pupilsightPersonID' => $pupilsightPersonID);
                    $sql = "SELECT * FROM pupilsightPerson WHERE status='Full' AND pupilsightPersonID=:pupilsightPersonID ORDER BY surname, preferredName";
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
                    exit();
                } else {
                    $row = $result->fetch();

                    //Get categories
                    $staff = $student = $parent = $other = false;
                    $roles = explode(',', $row['pupilsightRoleIDAll']);
                    foreach ($roles as $role) {
                        $roleCategory = getRoleCategory($role, $connection2);
                        $staff = $staff || ($roleCategory == 'Staff');
                        $student = $student || ($roleCategory == 'Student');
                        $parent = $parent || ($roleCategory == 'Parent');
                        $other = $other || ($roleCategory == 'Other');
                    }

                    //Proceed!
                    $data = [
                        'pupilsightSchoolYearID'     => $_SESSION[$guid]['pupilsightSchoolYearID'],
                        'pupilsightPersonID'         => $_POST['pupilsightPersonID'] ?? $row['pupilsightPersonID'],
                        'title'                  => $_POST['title'] ?? $row['title'],
                        'surname'                => $_POST['surname'] ?? $row['surname'],
                        'firstName'              => $_POST['firstName'] ?? $row['firstName'],
                        'preferredName'          => $_POST['preferredName'] ?? $row['preferredName'],
                        'officialName'           => $_POST['officialName'] ?? $row['officialName'],
                        'nameInCharacters'       => $_POST['nameInCharacters'] ?? $row['nameInCharacters'],
                        'dob'                    => isset($_POST['dob']) ? Format::dateConvert($_POST['dob']) : $row['dob'],
                        'email'                  => $_POST['email'] ?? $row['email'],
                        'emailAlternate'         => $_POST['emailAlternate'] ?? $row['emailAlternate'],
                        'address1'               => $_POST['address1'] ?? $row['address1'],
                        'address1District'       => $_POST['address1District'] ?? $row['address1District'],
                        'address1Country'        => $_POST['address1Country'] ?? $row['address1Country'],
                        'address2'               => $_POST['address2'] ?? $row['address2'],
                        'address2District'       => $_POST['address2District'] ?? $row['address2District'],
                        'address2Country'        => $_POST['address2Country'] ?? $row['address2Country'],
                        'phone1Type'             => $_POST['phone1Type'] ?? $row['phone1Type'],
                        'phone1CountryCode'      => $_POST['phone1CountryCode'] ?? $row['phone1CountryCode'],
                        'phone1'                 => $_POST['phone1'] ?? $row['phone1'],
                        'phone2Type'             => $_POST['phone2Type'] ?? $row['phone2Type'],
                        'phone2CountryCode'      => $_POST['phone2CountryCode'] ?? $row['phone2CountryCode'],
                        'phone2'                 => $_POST['phone2'] ?? $row['phone2'],
                        'phone3Type'             => $_POST['phone3Type'] ?? $row['phone3Type'],
                        'phone3CountryCode'      => $_POST['phone3CountryCode'] ?? $row['phone3CountryCode'],
                        'phone3'                 => $_POST['phone3'] ?? $row['phone3'],
                        'phone4Type'             => $_POST['phone4Type'] ?? $row['phone4Type'],
                        'phone4CountryCode'      => $_POST['phone4CountryCode'] ?? $row['phone4CountryCode'],
                        'phone4'                 => $_POST['phone4'] ?? $row['phone4'],
                        'languageFirst'          => $_POST['languageFirst'] ?? $row['languageFirst'],
                        'languageSecond'         => $_POST['languageSecond'] ?? $row['languageSecond'],
                        'languageThird'          => $_POST['languageThird'] ?? $row['languageThird'],
                        'countryOfBirth'         => $_POST['countryOfBirth'] ?? $row['countryOfBirth'],
                        'ethnicity'              => $_POST['ethnicity'] ?? $row['ethnicity'],
                        'citizenship1'           => $_POST['citizenship1'] ?? $row['citizenship1'],
                        'citizenship1Passport'   => $_POST['citizenship1Passport'] ?? $row['citizenship1Passport'],
                        'citizenship2'           => $_POST['citizenship2'] ?? $row['citizenship2'],
                        'citizenship2Passport'   => $_POST['citizenship2Passport'] ?? $row['citizenship2Passport'],
                        'religion'               => $_POST['religion'] ?? $row['religion'],
                        'nationalIDCardNumber'   => $_POST['nationalIDCardNumber'] ?? $row['nationalIDCardNumber'],
                        'residencyStatus'        => $_POST['residencyStatus'] ?? $row['residencyStatus'],
                        'visaExpiryDate'         => isset($_POST['visaExpiryDate']) ? Format::dateConvert($_POST['visaExpiryDate']) : $row['visaExpiryDate'],
                        'emergency1Name'         => $_POST['emergency1Name'] ?? $row['emergency1Name'],
                        'emergency1Number1'      => $_POST['emergency1Number1'] ?? $row['emergency1Number1'],
                        'emergency1Number2'      => $_POST['emergency1Number2'] ?? $row['emergency1Number2'],
                        'emergency1Relationship' => $_POST['emergency1Relationship'] ?? $row['emergency1Relationship'],
                        'emergency2Name'         => $_POST['emergency2Name'] ?? $row['emergency2Name'],
                        'emergency2Number1'      => $_POST['emergency2Number1'] ?? $row['emergency2Number1'],
                        'emergency2Number2'      => $_POST['emergency2Number2'] ?? $row['emergency2Number2'],
                        'emergency2Relationship' => $_POST['emergency2Relationship'] ?? $row['emergency2Relationship'],
                        'profession'             => $_POST['profession'] ?? $row['profession'],
                        'employer'               => $_POST['employer'] ?? $row['employer'],
                        'jobTitle'               => $_POST['jobTitle'] ?? $row['jobTitle'],
                        'vehicleRegistration'    => $_POST['vehicleRegistration'] ?? $row['vehicleRegistration'],
                    ];
 
                    $data = array_map('trim', $data);

                    // Date handling - ensure NULL value
                    if (empty($data['dob'])) $data['dob'] = null;
                    if (empty($data['visaExpiryDate'])) $data['visaExpiryDate'] = null;

                    // Phone number filtering
                    for ($i = 1; $i <= 4; $i++) {
                        $data["phone{$i}"] = preg_replace('/[^0-9+]/', '', $data["phone{$i}"]);
                        if (!empty($data["phone{$i}"]) && empty($data["phone{$i}Type"])) {
                            $data["phone{$i}Type"] = 'Other';
                        }
                    }
                    
                    // Student privacy settings
                    $data['privacy'] = isset($_POST['privacyOptions']) && is_array($_POST['privacyOptions'])
                        ? implode(', ', $_POST['privacyOptions'])
                        : null;
                        

                    //DEAL WITH CUSTOM FIELDS
                    //Prepare field values
                    $customRequireFail = false;
                    $resultFields = getCustomFields($connection2, $guid, $student, $staff, $parent, $other, null, true);
                    $fields = [];
                    if ($resultFields->rowCount() > 0) {
                        while ($rowFields = $resultFields->fetch()) {
                            $fieldID = $rowFields['pupilsightPersonFieldID'];
                            $fieldValue = $_POST['custom'.$fieldID] ?? null;

                            if (!is_null($fieldValue)) {
                                $fields[$fieldID] = ($rowFields['type'] == 'date')
                                    ? Format::dateConvert($fieldValue)
                                    : $fieldValue;
                            }
                            if ($highestAction != 'Update Personal Data_any') {
                                if ($rowFields['required'] == 'Y' && empty($fieldValue)) {
                                    $customRequireFail = true;
                                }
                            }
                        }
                    }
                    if ($customRequireFail) {
                        $URL .= '&return=error1';
                        header("Location: {$URL}");
                    } else {
                        $data['fields'] = serialize($fields);

                        //Write to database
                        $existing = $_POST['existing'] ?? 'N';

                        try {
                            $data['pupilsightPersonIDUpdater'] = $_SESSION[$guid]['pupilsightPersonID'];
                            if ($existing != 'N') {
                                $data['pupilsightPersonUpdateID'] = $existing;
                                $sql = 'UPDATE pupilsightPersonUpdate SET pupilsightSchoolYearID=:pupilsightSchoolYearID, pupilsightPersonID=:pupilsightPersonID, title=:title, surname=:surname, firstName=:firstName, preferredName=:preferredName, officialName=:officialName, nameInCharacters=:nameInCharacters, dob=:dob, email=:email, emailAlternate=:emailAlternate, address1=:address1, address1District=:address1District, address1Country=:address1Country, address2=:address2, address2District=:address2District, address2Country=:address2Country, phone1Type=:phone1Type, phone1CountryCode=:phone1CountryCode, phone1=:phone1, phone2Type=:phone2Type, phone2CountryCode=:phone2CountryCode, phone2=:phone2, phone3Type=:phone3Type, phone3CountryCode=:phone3CountryCode, phone3=:phone3, phone4Type=:phone4Type, phone4CountryCode=:phone4CountryCode, phone4=:phone4, languageFirst=:languageFirst, languageSecond=:languageSecond, languageThird=:languageThird, countryOfBirth=:countryOfBirth, ethnicity=:ethnicity, citizenship1=:citizenship1, citizenship1Passport=:citizenship1Passport, citizenship2=:citizenship2, citizenship2Passport=:citizenship2Passport, religion=:religion, nationalIDCardNumber=:nationalIDCardNumber, residencyStatus=:residencyStatus, visaExpiryDate=:visaExpiryDate, emergency1Name=:emergency1Name, emergency1Number1=:emergency1Number1, emergency1Number2=:emergency1Number2, emergency1Relationship=:emergency1Relationship, emergency2Name=:emergency2Name, emergency2Number1=:emergency2Number1, emergency2Number2=:emergency2Number2, emergency2Relationship=:emergency2Relationship, profession=:profession, employer=:employer, jobTitle=:jobTitle, vehicleRegistration=:vehicleRegistration, pupilsightPersonIDUpdater=:pupilsightPersonIDUpdater, privacy=:privacy, fields=:fields, timestamp=NOW() WHERE pupilsightPersonUpdateID=:pupilsightPersonUpdateID';
                            } else {
                                $sql = 'INSERT INTO pupilsightPersonUpdate SET pupilsightSchoolYearID=:pupilsightSchoolYearID, pupilsightPersonID=:pupilsightPersonID, title=:title, surname=:surname, firstName=:firstName, preferredName=:preferredName, officialName=:officialName, nameInCharacters=:nameInCharacters, dob=:dob, email=:email, emailAlternate=:emailAlternate, address1=:address1, address1District=:address1District, address1Country=:address1Country, address2=:address2, address2District=:address2District, address2Country=:address2Country, phone1Type=:phone1Type, phone1CountryCode=:phone1CountryCode, phone1=:phone1, phone2Type=:phone2Type, phone2CountryCode=:phone2CountryCode, phone2=:phone2, phone3Type=:phone3Type, phone3CountryCode=:phone3CountryCode, phone3=:phone3, phone4Type=:phone4Type, phone4CountryCode=:phone4CountryCode, phone4=:phone4, languageFirst=:languageFirst, languageSecond=:languageSecond, languageThird=:languageThird, countryOfBirth=:countryOfBirth, ethnicity=:ethnicity, citizenship1=:citizenship1, citizenship1Passport=:citizenship1Passport, citizenship2=:citizenship2, citizenship2Passport=:citizenship2Passport, religion=:religion, nationalIDCardNumber=:nationalIDCardNumber, residencyStatus=:residencyStatus, visaExpiryDate=:visaExpiryDate, emergency1Name=:emergency1Name, emergency1Number1=:emergency1Number1, emergency1Number2=:emergency1Number2, emergency1Relationship=:emergency1Relationship, emergency2Name=:emergency2Name, emergency2Number1=:emergency2Number1, emergency2Number2=:emergency2Number2, emergency2Relationship=:emergency2Relationship, profession=:profession, employer=:employer, jobTitle=:jobTitle, vehicleRegistration=:vehicleRegistration, pupilsightPersonIDUpdater=:pupilsightPersonIDUpdater, privacy=:privacy, fields=:fields';
                            }
                            $result = $connection2->prepare($sql);
                            $result->execute($data);
                        } catch (PDOException $e) {
                            $URL .= '&return=error2';
                            header("Location: {$URL}");
                            exit();
                        }

                        //Update matching addresses
                        $partialFail = false;
                        $matchAddressCount = 0;
                        if (isset($_POST['matchAddressCount'])) {
                            $matchAddressCount = $_POST['matchAddressCount'];
                        }
                        if ($matchAddressCount > 0) {
                            for ($i = 0; $i < $matchAddressCount; ++$i) {
                                if (!empty($_POST[$i.'-matchAddress'])) {
                                    $sqlAddress = '';
                                    try {
                                        $dataCheck = array('pupilsightPersonID' => $_POST[$i.'-matchAddress'], 'pupilsightPersonIDUpdater' => $_SESSION[$guid]['pupilsightPersonID']);
                                        $sqlCheck = "SELECT * FROM pupilsightPersonUpdate WHERE pupilsightPersonID=:pupilsightPersonID AND pupilsightPersonIDUpdater=:pupilsightPersonIDUpdater AND status='Pending'";
                                        $resultCheck = $connection2->prepare($sqlCheck);
                                        $resultCheck->execute($dataCheck);
                                    } catch (PDOException $e) {
                                        $partialFail = true;
                                    }

                                    if ($resultCheck->rowCount() > 1) {
                                        $partialFail = true;
                                    } elseif ($resultCheck->rowCount() == 1) {
                                        $rowCheck = $resultCheck->fetch();
                                        $dataAddress = array('pupilsightPersonID' => $_POST[$i.'-matchAddress'], 'address1' => $address1, 'address1District' => $address1District, 'address1Country' => $address1Country, 'pupilsightPersonIDUpdater' => $_SESSION[$guid]['pupilsightPersonID'], 'pupilsightPersonUpdateID' => $rowCheck['pupilsightPersonUpdateID']);
                                        $sqlAddress = 'UPDATE pupilsightPersonUpdate SET pupilsightPersonID=:pupilsightPersonID, address1=:address1, address1District=:address1District, address1Country=:address1Country, pupilsightPersonIDUpdater=:pupilsightPersonIDUpdater WHERE pupilsightPersonUpdateID=:pupilsightPersonUpdateID';
                                    } else {
                                        $dataAddress = array('pupilsightPersonID' => $_POST[$i.'-matchAddress'], 'address1' => $address1, 'address1District' => $address1District, 'address1Country' => $address1Country, 'pupilsightPersonIDUpdater' => $_SESSION[$guid]['pupilsightPersonID']);
                                        $sqlAddress = 'INSERT INTO pupilsightPersonUpdate SET pupilsightPersonID=:pupilsightPersonID, address1=:address1, address1District=:address1District, address1Country=:address1Country, pupilsightPersonIDUpdater=:pupilsightPersonIDUpdater';
                                    }
                                    if ($sqlAddress != '') {
                                        try {
                                            $resultAddress = $connection2->prepare($sqlAddress);
                                            $resultAddress->execute($dataAddress);
                                        } catch (PDOException $e) {
                                            $partialFail = true;
                                        }
                                    }
                                }
                            }
                        }

                        // Raise a new notification event
                        $event = new NotificationEvent('Data Updater', 'Personal Data Updates');

                        $event->addRecipient($_SESSION[$guid]['organisationDBA']);
                        $event->setNotificationText(__('A personal data update request has been submitted.'));
                        $event->setActionLink('/index.php?q=/modules/Data Updater/data_personal_manage.php');

                        $event->sendNotifications($pdo, $pupilsight->session);


                        if ($partialFail == true) {
                            $URL .= '&return=warning1';
                            header("Location: {$URL}");
                        } else {
                            $URLSuccess .= '&return=success0';
                            header("Location: {$URLSuccess}");
                        }
                    }
                }
            }
        }
    }
}
