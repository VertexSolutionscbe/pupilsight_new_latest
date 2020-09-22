<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Services\Format;
use Pupilsight\Comms\NotificationEvent;
use Pupilsight\Comms\NotificationSender;
use Pupilsight\Domain\System\NotificationGateway;

include '../../pupilsight.php';

//Module includes
include '../User Admin/moduleFunctions.php';

$pupilsightPersonUpdateID = $_GET['pupilsightPersonUpdateID'];
$pupilsightPersonID = $_POST['pupilsightPersonID'];
$URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_POST['address'])."/data_personal_manage_edit.php&pupilsightPersonUpdateID=$pupilsightPersonUpdateID";

if (isActionAccessible($guid, $connection2, '/modules/Data Updater/data_personal_manage_edit.php') == false) {
    $URL .= '&return=error0';
    header("Location: {$URL}");
} else {
    //Proceed!
    //Check if school year specified
    if ($pupilsightPersonUpdateID == '' or $pupilsightPersonID == '') {
        $URL .= '&return=error1';
        header("Location: {$URL}");
    } else {
        try {
            $data = array('pupilsightPersonUpdateID' => $pupilsightPersonUpdateID);
            $sql = 'SELECT * FROM pupilsightPersonUpdate WHERE pupilsightPersonUpdateID=:pupilsightPersonUpdateID';
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
            try {
                $data2 = array('pupilsightPersonID' => $pupilsightPersonID);
                $sql2 = 'SELECT * FROM pupilsightPerson WHERE pupilsightPersonID=:pupilsightPersonID';
                $result2 = $connection2->prepare($sql2);
                $result2->execute($data2);
            } catch (PDOException $e) {
                $URL .= '&return=error2';
                header("Location: {$URL}");
                exit();
            }

            if ($result2->rowCount() != 1) {
                $URL .= '&return=error2';
                header("Location: {$URL}");
            } else {
                $row = $result->fetch();
                $row2 = $result2->fetch();

                //Get categories
                $staff = false;
                $student = false;
                $parent = false;
                $other = false;
                $roles = explode(',', $row2['pupilsightRoleIDAll']);
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

                //Set values
                $data = array();
                $set = '';
                if (isset($_POST['newtitleOn'])) {
                    if ($_POST['newtitleOn'] == 'on') {
                        $data['title'] = $_POST['newtitle'];
                        $set .= 'pupilsightPerson.title=:title, ';
                    }
                }
                if (isset($_POST['newsurnameOn'])) {
                    if ($_POST['newsurnameOn'] == 'on') {
                        $data['surname'] = $_POST['newsurname'];
                        $set .= 'pupilsightPerson.surname=:surname, ';
                    }
                }
                if (isset($_POST['newfirstNameOn'])) {
                    if ($_POST['newfirstNameOn'] == 'on') {
                        $data['firstName'] = $_POST['newfirstName'];
                        $set .= 'pupilsightPerson.firstName=:firstName, ';
                    }
                }
                if (isset($_POST['newpreferredNameOn'])) {
                    if ($_POST['newpreferredNameOn'] == 'on') {
                        $data['preferredName'] = $_POST['newpreferredName'];
                        $set .= 'pupilsightPerson.preferredName=:preferredName, ';
                    }
                }
                if (isset($_POST['newofficialNameOn'])) {
                    if ($_POST['newofficialNameOn'] == 'on') {
                        $data['officialName'] = $_POST['newofficialName'];
                        $set .= 'pupilsightPerson.officialName=:officialName, ';
                    }
                }
                if (isset($_POST['newnameInCharactersOn'])) {
                    if ($_POST['newnameInCharactersOn'] == 'on') {
                        $data['nameInCharacters'] = $_POST['newnameInCharacters'];
                        $set .= 'pupilsightPerson.nameInCharacters=:nameInCharacters, ';
                    }
                }
                if (isset($_POST['newdobOn'])) {
                    if ($_POST['newdobOn'] == 'on') {
                        $data['dob'] = $_POST['newdob'];
                        $set .= 'pupilsightPerson.dob=:dob, ';
                    }
                }
                if (isset($_POST['newemailOn'])) {
                    if ($_POST['newemailOn'] == 'on') {
                        $data['email'] = $_POST['newemail'];
                        $set .= 'pupilsightPerson.email=:email, ';
                    }
                }
                if (isset($_POST['newemailAlternateOn'])) {
                    if ($_POST['newemailAlternateOn'] == 'on') {
                        $data['emailAlternate'] = $_POST['newemailAlternate'];
                        $set .= 'pupilsightPerson.emailAlternate=:emailAlternate, ';
                    }
                }
                if (isset($_POST['newaddress1On'])) {
                    if ($_POST['newaddress1On'] == 'on') {
                        $data['address1'] = $_POST['newaddress1'];
                        $set .= 'pupilsightPerson.address1=:address1, ';
                    }
                }
                if (isset($_POST['newaddress1DistrictOn'])) {
                    if ($_POST['newaddress1DistrictOn'] == 'on') {
                        $data['address1District'] = $_POST['newaddress1District'];
                        $set .= 'pupilsightPerson.address1District=:address1District, ';
                    }
                }
                if (isset($_POST['newaddress1CountryOn'])) {
                    if ($_POST['newaddress1CountryOn'] == 'on') {
                        $data['address1Country'] = $_POST['newaddress1Country'];
                        $set .= 'pupilsightPerson.address1Country=:address1Country, ';
                    }
                }
                if (isset($_POST['newaddress2On'])) {
                    if ($_POST['newaddress2On'] == 'on') {
                        $data['address2'] = $_POST['newaddress2'];
                        $set .= 'pupilsightPerson.address2=:address2, ';
                    }
                }
                if (isset($_POST['newaddress2DistrictOn'])) {
                    if ($_POST['newaddress2DistrictOn'] == 'on') {
                        $data['address2District'] = $_POST['newaddress2District'];
                        $set .= 'pupilsightPerson.address2District=:address2District, ';
                    }
                }
                if (isset($_POST['newaddress2CountryOn'])) {
                    if ($_POST['newaddress2CountryOn'] == 'on') {
                        $data['address2Country'] = $_POST['newaddress2Country'];
                        $set .= 'pupilsightPerson.address2Country=:address2Country, ';
                    }
                }
                if (isset($_POST['newphone1TypeOn'])) {
                    if ($_POST['newphone1TypeOn'] == 'on') {
                        $data['phone1Type'] = $_POST['newphone1Type'];
                        $set .= 'pupilsightPerson.phone1Type=:phone1Type, ';
                    }
                }
                if (isset($_POST['newphone1CountryCodeOn'])) {
                    if ($_POST['newphone1CountryCodeOn'] == 'on') {
                        $data['phone1CountryCode'] = $_POST['newphone1CountryCode'];
                        $set .= 'pupilsightPerson.phone1CountryCode=:phone1CountryCode, ';
                    }
                }
                if (isset($_POST['newphone1On'])) {
                    if ($_POST['newphone1On'] == 'on') {
                        $data['phone1'] = $_POST['newphone1'];
                        $set .= 'pupilsightPerson.phone1=:phone1, ';
                    }
                }
                if (isset($_POST['newphone2TypeOn'])) {
                    if ($_POST['newphone2TypeOn'] == 'on') {
                        $data['phone2Type'] = $_POST['newphone2Type'];
                        $set .= 'pupilsightPerson.phone2Type=:phone2Type, ';
                    }
                }
                if (isset($_POST['newphone2CountryCodeOn'])) {
                    if ($_POST['newphone2CountryCodeOn'] == 'on') {
                        $data['phone2CountryCode'] = $_POST['newphone2CountryCode'];
                        $set .= 'pupilsightPerson.phone2CountryCode=:phone2CountryCode, ';
                    }
                }
                if (isset($_POST['newphone2On'])) {
                    if ($_POST['newphone2On'] == 'on') {
                        $data['phone2'] = $_POST['newphone2'];
                        $set .= 'pupilsightPerson.phone2=:phone2, ';
                    }
                }
                if (isset($_POST['newphone3TypeOn'])) {
                    if ($_POST['newphone3TypeOn'] == 'on') {
                        $data['phone3Type'] = $_POST['newphone3Type'];
                        $set .= 'pupilsightPerson.phone3Type=:phone3Type, ';
                    }
                }
                if (isset($_POST['newphone3CountryCodeOn'])) {
                    if ($_POST['newphone3CountryCodeOn'] == 'on') {
                        $data['phone3CountryCode'] = $_POST['newphone3CountryCode'];
                        $set .= 'pupilsightPerson.phone3CountryCode=:phone3CountryCode, ';
                    }
                }
                if (isset($_POST['newphone3On'])) {
                    if ($_POST['newphone3On'] == 'on') {
                        $data['phone3'] = $_POST['newphone3'];
                        $set .= 'pupilsightPerson.phone3=:phone3, ';
                    }
                }
                if (isset($_POST['newphone4TypeOn'])) {
                    if ($_POST['newphone4TypeOn'] == 'on') {
                        $data['phone4Type'] = $_POST['newphone4Type'];
                        $set .= 'pupilsightPerson.phone4Type=:phone4Type, ';
                    }
                }
                if (isset($_POST['newphone4CountryCodeOn'])) {
                    if ($_POST['newphone4CountryCodeOn'] == 'on') {
                        $data['phone4CountryCode'] = $_POST['newphone4CountryCode'];
                        $set .= 'pupilsightPerson.phone4CountryCode=:phone4CountryCode, ';
                    }
                }
                if (isset($_POST['newphone4On'])) {
                    if ($_POST['newphone4On'] == 'on') {
                        $data['phone4'] = $_POST['newphone4'];
                        $set .= 'pupilsightPerson.phone4=:phone4, ';
                    }
                }
                if (isset($_POST['newlanguageFirstOn'])) {
                    if ($_POST['newlanguageFirstOn'] == 'on') {
                        $data['languageFirst'] = $_POST['newlanguageFirst'];
                        $set .= 'pupilsightPerson.languageFirst=:languageFirst, ';
                    }
                }
                if (isset($_POST['newlanguageSecondOn'])) {
                    if ($_POST['newlanguageSecondOn'] == 'on') {
                        $data['languageSecond'] = $_POST['newlanguageSecond'];
                        $set .= 'pupilsightPerson.languageSecond=:languageSecond, ';
                    }
                }
                if (isset($_POST['newlanguageThirdOn'])) {
                    if ($_POST['newlanguageThirdOn'] == 'on') {
                        $data['languageThird'] = $_POST['newlanguageThird'];
                        $set .= 'pupilsightPerson.languageThird=:languageThird, ';
                    }
                }
                if (isset($_POST['newcountryOfBirthOn'])) {
                    if ($_POST['newcountryOfBirthOn'] == 'on') {
                        $data['countryOfBirth'] = $_POST['newcountryOfBirth'];
                        $set .= 'pupilsightPerson.countryOfBirth=:countryOfBirth, ';
                    }
                }
                if (isset($_POST['newethnicityOn'])) {
                    if ($_POST['newethnicityOn'] == 'on') {
                        $data['ethnicity'] = $_POST['newethnicity'];
                        $set .= 'pupilsightPerson.ethnicity=:ethnicity, ';
                    }
                }
                if (isset($_POST['newcitizenship1On'])) {
                    if ($_POST['newcitizenship1On'] == 'on') {
                        $data['citizenship1'] = $_POST['newcitizenship1'];
                        $set .= 'pupilsightPerson.citizenship1=:citizenship1, ';
                    }
                }
                if (isset($_POST['newcitizenship1PassportOn'])) {
                    if ($_POST['newcitizenship1PassportOn'] == 'on') {
                        $data['citizenship1Passport'] = $_POST['newcitizenship1Passport'];
                        $set .= 'pupilsightPerson.citizenship1Passport=:citizenship1Passport, ';
                    }
                }
                if (isset($_POST['newcitizenship2On'])) {
                    if ($_POST['newcitizenship2On'] == 'on') {
                        $data['citizenship2'] = $_POST['newcitizenship2'];
                        $set .= 'pupilsightPerson.citizenship2=:citizenship2, ';
                    }
                }
                if (isset($_POST['newcitizenship2PassportOn'])) {
                    if ($_POST['newcitizenship2PassportOn'] == 'on') {
                        $data['citizenship2Passport'] = $_POST['newcitizenship2Passport'];
                        $set .= 'pupilsightPerson.citizenship2Passport=:citizenship2Passport, ';
                    }
                }
                if (isset($_POST['newreligionOn'])) {
                    if ($_POST['newreligionOn'] == 'on') {
                        $data['religion'] = $_POST['newreligion'];
                        $set .= 'pupilsightPerson.religion=:religion, ';
                    }
                }
                if (isset($_POST['newnationalIDCardNumberOn'])) {
                    if ($_POST['newnationalIDCardNumberOn'] == 'on') {
                        $data['nationalIDCardNumber'] = $_POST['newnationalIDCardNumber'];
                        $set .= 'pupilsightPerson.nationalIDCardNumber=:nationalIDCardNumber, ';
                    }
                }
                if (isset($_POST['newresidencyStatusOn'])) {
                    if ($_POST['newresidencyStatusOn'] == 'on') {
                        $data['residencyStatus'] = $_POST['newresidencyStatus'];
                        $set .= 'pupilsightPerson.residencyStatus=:residencyStatus, ';
                    }
                }
                if (isset($_POST['newvisaExpiryDateOn'])) {
                    if ($_POST['newvisaExpiryDateOn'] == 'on') {
                        $data['visaExpiryDate'] = !empty($_POST['newvisaExpiryDate'])? $_POST['newvisaExpiryDate'] : null;
                        $set .= 'pupilsightPerson.visaExpiryDate=:visaExpiryDate, ';
                    }
                }
                if (isset($_POST['newprofessionOn'])) {
                    if ($_POST['newprofessionOn'] == 'on') {
                        $data['profession'] = $_POST['newprofession'];
                        $set .= 'pupilsightPerson.profession=:profession, ';
                    }
                }
                if (isset($_POST['newemployerOn'])) {
                    if ($_POST['newemployerOn'] == 'on') {
                        $data['employer'] = $_POST['newemployer'];
                        $set .= 'pupilsightPerson.employer=:employer, ';
                    }
                }
                if (isset($_POST['newjobTitleOn'])) {
                    if ($_POST['newjobTitleOn'] == 'on') {
                        $data['jobTitle'] = $_POST['newjobTitle'];
                        $set .= 'pupilsightPerson.jobTitle=:jobTitle, ';
                    }
                }
                if (isset($_POST['newemergency1NameOn'])) {
                    if ($_POST['newemergency1NameOn'] == 'on') {
                        $data['emergency1Name'] = $_POST['newemergency1Name'];
                        $set .= 'pupilsightPerson.emergency1Name=:emergency1Name, ';
                    }
                }
                if (isset($_POST['newemergency1Number1On'])) {
                    if ($_POST['newemergency1Number1On'] == 'on') {
                        $data['emergency1Number1'] = $_POST['newemergency1Number1'];
                        $set .= 'pupilsightPerson.emergency1Number1=:emergency1Number1, ';
                    }
                }
                if (isset($_POST['newemergency1Number2On'])) {
                    if ($_POST['newemergency1Number2On'] == 'on') {
                        $data['emergency1Number2'] = $_POST['newemergency1Number2'];
                        $set .= 'pupilsightPerson.emergency1Number2=:emergency1Number2, ';
                    }
                }
                if (isset($_POST['newemergency1RelationshipOn'])) {
                    if ($_POST['newemergency1RelationshipOn'] == 'on') {
                        $data['emergency1Relationship'] = $_POST['newemergency1Relationship'];
                        $set .= 'pupilsightPerson.emergency1Relationship=:emergency1Relationship, ';
                    }
                }
                if (isset($_POST['newemergency2NameOn'])) {
                    if ($_POST['newemergency2NameOn'] == 'on') {
                        $data['emergency2Name'] = $_POST['newemergency2Name'];
                        $set .= 'pupilsightPerson.emergency2Name=:emergency2Name, ';
                    }
                }
                if (isset($_POST['newemergency2Number1On'])) {
                    if ($_POST['newemergency2Number1On'] == 'on') {
                        $data['emergency2Number1'] = $_POST['newemergency2Number1'];
                        $set .= 'pupilsightPerson.emergency2Number1=:emergency2Number1, ';
                    }
                }
                if (isset($_POST['newemergency2Number2On'])) {
                    if ($_POST['newemergency2Number2On'] == 'on') {
                        $data['emergency2Number2'] = $_POST['newemergency2Number2'];
                        $set .= 'pupilsightPerson.emergency2Number2=:emergency2Number2, ';
                    }
                }
                if (isset($_POST['newemergency2RelationshipOn'])) {
                    if ($_POST['newemergency2RelationshipOn'] == 'on') {
                        $data['emergency2Relationship'] = $_POST['newemergency2Relationship'];
                        $set .= 'pupilsightPerson.emergency2Relationship=:emergency2Relationship, ';
                    }
                }
                if (isset($_POST['newvehicleRegistrationOn'])) {
                    if ($_POST['newvehicleRegistrationOn'] == 'on') {
                        $data['vehicleRegistration'] = $_POST['newvehicleRegistration'];
                        $set .= 'pupilsightPerson.vehicleRegistration=:vehicleRegistration, ';
                    }
                }
                $privacy_old=$row2["privacy"] ;
                if (isset($_POST['newprivacyOn'])) {
                    if ($_POST['newprivacyOn'] == 'on') {
                        $data['privacy'] = $_POST['newprivacy'];
                        $set .= 'pupilsightPerson.privacy=:privacy, ';
                    }
                }

                //DEAL WITH CUSTOM FIELDS
                //Prepare field values
                $resultFields = getCustomFields($connection2, $guid, $student, $staff, $parent, $other, null, true);
                $fields = isset($row2['fields']) ? unserialize($row2['fields']) : [];
                if ($resultFields->rowCount() > 0) {
                    while ($rowFields = $resultFields->fetch()) {
                        if (isset($_POST['newcustom'.$rowFields['pupilsightPersonFieldID'].'On'])) {
                            if (isset($_POST['newcustom'.$rowFields['pupilsightPersonFieldID']])) {
                                if ($rowFields['type'] == 'date') {
                                    $fields[$rowFields['pupilsightPersonFieldID']] = dateConvert($guid, $_POST['newcustom'.$rowFields['pupilsightPersonFieldID']]);
                                } else {
                                    $fields[$rowFields['pupilsightPersonFieldID']] = $_POST['newcustom'.$rowFields['pupilsightPersonFieldID']];
                                }
                            }
                        }
                    }
                }

                $fields = serialize($fields);

                if (strlen($set) > 1) {
                    //Write to database
                    try {
                        $data['pupilsightPersonID'] = $pupilsightPersonID;
                        $data['fields'] = $fields;
                        $sql = 'UPDATE pupilsightPerson SET '.substr($set, 0, (strlen($set) - 2)).', fields=:fields WHERE pupilsightPersonID=:pupilsightPersonID';
                        $result = $connection2->prepare($sql);
                        $result->execute($data);
                    } catch (PDOException $e) {
                        $URL .= '&return=error2';
                        header("Location: {$URL}");
                        exit();
                    }

                    //Write to database
                    try {
                        $data = array('pupilsightPersonUpdateID' => $pupilsightPersonUpdateID);
                        $sql = "UPDATE pupilsightPersonUpdate SET status='Complete' WHERE pupilsightPersonUpdateID=:pupilsightPersonUpdateID";
                        $result = $connection2->prepare($sql);
                        $result->execute($data);
                    } catch (PDOException $e) {
                        $URL .= '&return=warning1';
                        header("Location: {$URL}");
                        exit();
                    }

                    //Notify tutors of change to privacy settings
                    if (isset($_POST['newprivacyOn'])) {
                        if ($_POST['newprivacyOn'] == 'on') {
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
                                $studentName = Format::name('', $row2['preferredName'], $row2['surname'], 'Student', false);
                                $actionLink = "/index.php?q=/modules/Students/student_view_details.php&pupilsightPersonID=$pupilsightPersonID&search=";

                                $privacyText = __('Privacy').' (<i>'.__('New Value').'</i>): ';
                                $privacyText .= !empty($_POST['newprivacy']) ? $_POST['newprivacy'] : __('None');

                                $notificationText = sprintf(__('%1$s has altered the privacy settings for %2$s.'), $staffName, $studentName).'<br/><br/>';
                                $notificationText .= $privacyText;

                                $event->setNotificationText($notificationText);
                                $event->setActionLink($actionLink);

                                $event->addScope('pupilsightPersonIDStudent', $pupilsightPersonID);
                                $event->addScope('pupilsightYearGroupID', $rowDetail['pupilsightYearGroupID']);

                                // Add event listeners to the notification sender
                                $event->pushNotifications($notificationGateway, $notificationSender);

                                // Add direct notifications to roll group tutors
                                if ($event->getEventDetails($notificationGateway, 'active') == 'Y') {
                                    $notificationText = sprintf(__('Your tutee, %1$s, has had their privacy settings altered.'), $studentName).'<br/><br/>';
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
                            $pupilsightModuleID=getModuleIDFromName($connection2, 'User Admin') ;
                            $privacyValues=array() ;
                            $privacyValues['oldValue'] = $privacy_old ;
                            $privacyValues['newValue'] = $_POST['newprivacy'] ;
                            $privacyValues['pupilsightPersonIDRequestor'] = $row['pupilsightPersonIDUpdater'] ;
                            $privacyValues['pupilsightPersonIDAcceptor'] = $_SESSION[$guid]["pupilsightPersonID"] ;

                            setLog($connection2, $_SESSION[$guid]["pupilsightSchoolYearID"], $pupilsightModuleID, $_SESSION[$guid]["pupilsightPersonID"], 'Privacy - Value Changed via Data Updater', $privacyValues, $_SERVER['REMOTE_ADDR']) ;

                        }
                    }

                    $URL .= '&return=success0';
                    header("Location: {$URL}");
                } else {
                    //Write to database
                    try {
                        $data['pupilsightPersonID'] = $pupilsightPersonID;
                        $data['fields'] = $fields;
                        $sql = 'UPDATE pupilsightPerson SET fields=:fields WHERE pupilsightPersonID=:pupilsightPersonID';
                        $result = $connection2->prepare($sql);
                        $result->execute($data);
                    } catch (PDOException $e) {
                        $URL .= '&return=error2';
                        header("Location: {$URL}");
                        exit();
                    }

                    //Write to database
                    try {
                        $data = array('pupilsightPersonUpdateID' => $pupilsightPersonUpdateID);
                        $sql = "UPDATE pupilsightPersonUpdate SET status='Complete' WHERE pupilsightPersonUpdateID=:pupilsightPersonUpdateID";
                        $result = $connection2->prepare($sql);
                        $result->execute($data);
                    } catch (PDOException $e) {
                        $URL .= '&updateReturn=success1';
                        header("Location: {$URL}");
                        exit();
                    }

                    $URL .= '&return=success0';
                    header("Location: {$URL}");
                }
            }
        }
    }
}
