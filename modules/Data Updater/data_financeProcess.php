<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Comms\NotificationEvent;

include '../../pupilsight.php';

$pupilsightFinanceInvoiceeID = $_GET['pupilsightFinanceInvoiceeID'];
$URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_POST['address'])."/data_finance.php&pupilsightFinanceInvoiceeID=$pupilsightFinanceInvoiceeID";

if (isActionAccessible($guid, $connection2, '/modules/Data Updater/data_finance.php') == false) {
    $URL .= '&return=error0';
    header("Location: {$URL}");
} else {
    //Proceed!
    //Check if school year specified
    if ($pupilsightFinanceInvoiceeID == '') {
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
            if ($highestAction == 'Update Finance Data_any') {
                $URLSuccess = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/Data Updater/data_finance.php&pupilsightFinanceInvoiceeID='.$pupilsightFinanceInvoiceeID;
                
                try {
                    $dataSelect = array('pupilsightFinanceInvoiceeID' => $pupilsightFinanceInvoiceeID);
                    $sqlSelect = "SELECT surname, preferredName, pupilsightPerson.pupilsightPersonID, pupilsightFinanceInvoiceeID FROM pupilsightFinanceInvoicee JOIN pupilsightPerson ON (pupilsightFinanceInvoicee.pupilsightPersonID=pupilsightPerson.pupilsightPersonID) WHERE status='Full' AND pupilsightFinanceInvoiceeID=:pupilsightFinanceInvoiceeID ORDER BY surname, preferredName";
                    $resultSelect = $connection2->prepare($sqlSelect);
                    $resultSelect->execute($dataSelect);
                } catch (PDOException $e) {
                }
                $checkCount = $resultSelect->rowCount();
            } else {
                $URLSuccess = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/Data Updater/data_updates.php&pupilsightFinanceInvoiceeID='.$pupilsightFinanceInvoiceeID;
                
                try {
                    $dataCheck = array('pupilsightPersonID' => $_SESSION[$guid]['pupilsightPersonID']);
                    $sqlCheck = "SELECT pupilsightFamilyAdult.pupilsightFamilyID, name FROM pupilsightFamilyAdult JOIN pupilsightFamily ON (pupilsightFamilyAdult.pupilsightFamilyID=pupilsightFamily.pupilsightFamilyID) WHERE pupilsightPersonID=:pupilsightPersonID AND childDataAccess='Y' ORDER BY name";
                    $resultCheck = $connection2->prepare($sqlCheck);
                    $resultCheck->execute($dataCheck);
                } catch (PDOException $e) {
                }
                while ($rowCheck = $resultCheck->fetch()) {
                    try {
                        $dataCheck2 = array('pupilsightFamilyID' => $rowCheck['pupilsightFamilyID']);
                        $sqlCheck2 = "SELECT surname, preferredName, pupilsightPerson.pupilsightPersonID, pupilsightFamilyID, pupilsightFinanceInvoiceeID FROM pupilsightFamilyChild JOIN pupilsightPerson ON (pupilsightFamilyChild.pupilsightPersonID=pupilsightPerson.pupilsightPersonID) JOIN pupilsightFinanceInvoicee ON (pupilsightFinanceInvoicee.pupilsightPersonID=pupilsightPerson.pupilsightPersonID) WHERE pupilsightPerson.status='Full' AND pupilsightFamilyID=:pupilsightFamilyID";
                        $resultCheck2 = $connection2->prepare($sqlCheck2);
                        $resultCheck2->execute($dataCheck2);
                    } catch (PDOException $e) {
                    }
                    while ($rowCheck2 = $resultCheck2->fetch()) {
                        if ($pupilsightFinanceInvoiceeID == $rowCheck2['pupilsightFinanceInvoiceeID']) {
                            ++$checkCount;
                        }
                    }
                }
            }

            if ($checkCount < 1) {
                $URL .= '&return=error2';
                header("Location: {$URL}");
            } else {
                //Proceed!
                $invoiceTo = $_POST['invoiceTo'];
                if ($invoiceTo == 'Company') {
                    $companyName = $_POST['companyName'];
                    $companyContact = $_POST['companyContact'];
                    $companyAddress = $_POST['companyAddress'];
                    $companyEmail = $_POST['companyEmail'];
                    $companyCCFamily = $_POST['companyCCFamily'];
                    $companyPhone = $_POST['companyPhone'];
                    $companyAll = $_POST['companyAll'];
                    $pupilsightFinanceFeeCategoryIDList = null;
                    if ($companyAll == 'N') {
                        $pupilsightFinanceFeeCategoryIDList == '';
                        $pupilsightFinanceFeeCategoryIDArray = $_POST['pupilsightFinanceFeeCategoryIDList'];
                        if (count($pupilsightFinanceFeeCategoryIDArray) > 0) {
                            foreach ($pupilsightFinanceFeeCategoryIDArray as $pupilsightFinanceFeeCategoryID) {
                                $pupilsightFinanceFeeCategoryIDList .= $pupilsightFinanceFeeCategoryID.',';
                            }
                            $pupilsightFinanceFeeCategoryIDList = substr($pupilsightFinanceFeeCategoryIDList, 0, -1);
                        }
                    }
                } else {
                    $companyName = null;
                    $companyContact = null;
                    $companyAddress = null;
                    $companyEmail = null;
                    $companyCCFamily = null;
                    $companyPhone = null;
                    $companyAll = null;
                    $pupilsightFinanceFeeCategoryIDList = null;
                }

                //Write to database
                $existing = $_POST['existing'];

                try {
                    if ($existing != 'N') {
                        $data = array('pupilsightSchoolYearID' => $_SESSION[$guid]['pupilsightSchoolYearID'], 'invoiceTo' => $invoiceTo, 'companyName' => $companyName, 'companyContact' => $companyContact, 'companyAddress' => $companyAddress, 'companyEmail' => $companyEmail, 'companyCCFamily' => $companyCCFamily, 'companyPhone' => $companyPhone, 'companyAll' => $companyAll, 'pupilsightFinanceFeeCategoryIDList' => $pupilsightFinanceFeeCategoryIDList, 'pupilsightFinanceInvoiceeUpdateID' => $existing);
                        $sql = 'UPDATE pupilsightFinanceInvoiceeUpdate SET pupilsightSchoolYearID=:pupilsightSchoolYearID, invoiceTo=:invoiceTo, companyName=:companyName, companyContact=:companyContact, companyAddress=:companyAddress, companyEmail=:companyEmail, companyCCFamily=:companyCCFamily, companyPhone=:companyPhone, companyAll=:companyAll, pupilsightFinanceFeeCategoryIDList=:pupilsightFinanceFeeCategoryIDList, timestamp=NOW() WHERE pupilsightFinanceInvoiceeUpdateID=:pupilsightFinanceInvoiceeUpdateID';
                    } else {
                        $data = array('pupilsightSchoolYearID' => $_SESSION[$guid]['pupilsightSchoolYearID'], 'pupilsightFinanceInvoiceeID' => $pupilsightFinanceInvoiceeID, 'invoiceTo' => $invoiceTo, 'companyName' => $companyName, 'companyContact' => $companyContact, 'companyAddress' => $companyAddress, 'companyEmail' => $companyEmail, 'companyCCFamily' => $companyCCFamily, 'companyPhone' => $companyPhone, 'companyAll' => $companyAll, 'pupilsightFinanceFeeCategoryIDList' => $pupilsightFinanceFeeCategoryIDList, 'pupilsightPersonIDUpdater' => $_SESSION[$guid]['pupilsightPersonID']);
                        $sql = 'INSERT INTO pupilsightFinanceInvoiceeUpdate SET pupilsightSchoolYearID=:pupilsightSchoolYearID, pupilsightFinanceInvoiceeID=:pupilsightFinanceInvoiceeID, invoiceTo=:invoiceTo, companyName=:companyName, companyContact=:companyContact, companyAddress=:companyAddress, companyEmail=:companyEmail, companyCCFamily=:companyCCFamily, companyPhone=:companyPhone, companyAll=:companyAll, pupilsightFinanceFeeCategoryIDList=:pupilsightFinanceFeeCategoryIDList, pupilsightPersonIDUpdater=:pupilsightPersonIDUpdater';
                    }
                    $result = $connection2->prepare($sql);
                    $result->execute($data);
                } catch (PDOException $e) {
                    $URL .= '&return=error2';
                    header("Location: {$URL}");
                    exit();
                }

                // Raise a new notification event
                $event = new NotificationEvent('Data Updater', 'Finance Data Updates');

                $event->addRecipient($_SESSION[$guid]['organisationDBA']);
                $event->setNotificationText(__('A finance data update request has been submitted.'));
                $event->setActionLink('/index.php?q=/modules/Data Updater/data_finance_manage.php');

                $event->sendNotifications($pdo, $pupilsight->session);


                $URLSuccess .= '&return=success0';
                header("Location: {$URLSuccess}");
            }
        }
    }
}
