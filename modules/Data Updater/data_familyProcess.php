<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Comms\NotificationEvent;

include '../../pupilsight.php';

$pupilsightFamilyID = $_GET['pupilsightFamilyID'];
$URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_POST['address'])."/data_family.php&pupilsightFamilyID=$pupilsightFamilyID";

if (isActionAccessible($guid, $connection2, '/modules/Data Updater/data_family.php') == false) {
    $URL .= '&return=error0';
    header("Location: {$URL}");
} else {
    //Proceed!
    //Check if school year specified
    if ($pupilsightFamilyID == '') {
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
            if ($highestAction == 'Update Family Data_any') {
                $URLSuccess = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/Data Updater/data_family.php&pupilsightFamilyID='.$pupilsightFamilyID;

                try {
                    $dataCheck = array('pupilsightFamilyID' => $pupilsightFamilyID);
                    $sqlCheck = 'SELECT name, pupilsightFamily.pupilsightFamilyID FROM pupilsightFamily WHERE pupilsightFamilyID=:pupilsightFamilyID';
                    $resultCheck = $connection2->prepare($sqlCheck);
                    $resultCheck->execute($dataCheck);
                } catch (PDOException $e) {
                }
            } else {
                $URLSuccess = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/Data Updater/data_updates.php&pupilsightFamilyID='.$pupilsightFamilyID;

                try {
                    $dataCheck = array('pupilsightFamilyID' => $pupilsightFamilyID, 'pupilsightPersonID' => $_SESSION[$guid]['pupilsightPersonID']);
                    $sqlCheck = "SELECT name, pupilsightFamily.pupilsightFamilyID FROM pupilsightFamily JOIN pupilsightFamilyAdult ON (pupilsightFamilyAdult.pupilsightFamilyID=pupilsightFamily.pupilsightFamilyID) WHERE pupilsightPersonID=:pupilsightPersonID AND childDataAccess='Y' AND pupilsightFamily.pupilsightFamilyID=:pupilsightFamilyID";
                    $resultCheck = $connection2->prepare($sqlCheck);
                    $resultCheck->execute($dataCheck);
                } catch (PDOException $e) {
                }
            }

            if ($resultCheck->rowCount() != 1) {
                $URL .= '&return=warning';
                header("Location: {$URL}");
            } else {
                //Proceed!
                $nameAddress = $_POST['nameAddress'];
                $homeAddress = $_POST['homeAddress'];
                $homeAddressDistrict = $_POST['homeAddressDistrict'];
                $homeAddressCountry = $_POST['homeAddressCountry'];
                $languageHomePrimary = $_POST['languageHomePrimary'];
                $languageHomeSecondary = $_POST['languageHomeSecondary'];

                //Write to database
                $existing = $_POST['existing'];

                try {
                    if ($existing != 'N') {
                        $data = array('pupilsightSchoolYearID' => $_SESSION[$guid]['pupilsightSchoolYearID'], 'nameAddress' => $nameAddress, 'homeAddress' => $homeAddress, 'homeAddressDistrict' => $homeAddressDistrict, 'homeAddressCountry' => $homeAddressCountry, 'languageHomePrimary' => $languageHomePrimary, 'languageHomeSecondary' => $languageHomeSecondary, 'pupilsightPersonIDUpdater' => $_SESSION[$guid]['pupilsightPersonID'], 'pupilsightFamilyUpdateID' => $existing);
                        $sql = 'UPDATE pupilsightFamilyUpdate SET pupilsightSchoolYearID=:pupilsightSchoolYearID, nameAddress=:nameAddress, homeAddress=:homeAddress, homeAddressDistrict=:homeAddressDistrict, homeAddressCountry=:homeAddressCountry, languageHomePrimary=:languageHomePrimary, languageHomeSecondary=:languageHomeSecondary, pupilsightPersonIDUpdater=:pupilsightPersonIDUpdater, timestamp=NOW() WHERE pupilsightFamilyUpdateID=:pupilsightFamilyUpdateID';
                    } else {
                        $data = array('pupilsightSchoolYearID' => $_SESSION[$guid]['pupilsightSchoolYearID'], 'pupilsightFamilyID' => $pupilsightFamilyID, 'nameAddress' => $nameAddress, 'homeAddress' => $homeAddress, 'homeAddressDistrict' => $homeAddressDistrict, 'homeAddressCountry' => $homeAddressCountry, 'languageHomePrimary' => $languageHomePrimary, 'languageHomeSecondary' => $languageHomeSecondary, 'pupilsightPersonIDUpdater' => $_SESSION[$guid]['pupilsightPersonID']);
                        $sql = 'INSERT INTO pupilsightFamilyUpdate SET pupilsightSchoolYearID=:pupilsightSchoolYearID, pupilsightFamilyID=:pupilsightFamilyID, nameAddress=:nameAddress, homeAddress=:homeAddress, homeAddressDistrict=:homeAddressDistrict, homeAddressCountry=:homeAddressCountry, languageHomePrimary=:languageHomePrimary, languageHomeSecondary=:languageHomeSecondary, pupilsightPersonIDUpdater=:pupilsightPersonIDUpdater';
                    }
                    $result = $connection2->prepare($sql);
                    $result->execute($data);
                } catch (PDOException $e) {
                    $URL .= '&return=error2';
                    header("Location: {$URL}");
                    exit();
                }

                // Raise a new notification event
                $event = new NotificationEvent('Data Updater', 'Family Data Updates');

                $event->addRecipient($_SESSION[$guid]['organisationDBA']);
                $event->setNotificationText(__('A family data update request has been submitted.'));
                $event->setActionLink('/index.php?q=/modules/Data Updater/data_family_manage.php');

                $event->sendNotifications($pdo, $pupilsight->session);

                $URLSuccess .= '&return=success0';
                header("Location: {$URLSuccess}");
            }
        }
    }
}
