<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Comms\NotificationEvent;

include '../../pupilsight.php';

$pupilsightPersonID = $_GET['pupilsightPersonID'];
$URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_POST['address'])."/data_medical.php&pupilsightPersonID=$pupilsightPersonID";

if (isActionAccessible($guid, $connection2, '/modules/Data Updater/data_medical.php') == false) {
    $URL .= '&return=error0';
    header("Location: {$URL}");
} else {
    $highestAction = getHighestGroupedAction($guid, $_POST['address'], $connection2);
    if ($highestAction == false) {
        $URL .= "&return=error0$params";
        header("Location: {$URL}");
    } else {
        //Proceed!
        //Check if school year specified
        if ($pupilsightPersonID == '') {
            $URL .= '&return=error1';
            header("Location: {$URL}");
        } else {
            //Check access to person
            $checkCount = 0;
            if ($highestAction == 'Update Medical Data_any') {
                $URLSuccess = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/Data Updater/data_medical.php&pupilsightPersonID='.$pupilsightPersonID;

                try {
                    $dataSelect = array();
                    $sqlSelect = "SELECT surname, preferredName, pupilsightPerson.pupilsightPersonID FROM pupilsightPerson WHERE status='Full' ORDER BY surname, preferredName";
                    $resultSelect = $connection2->prepare($sqlSelect);
                    $resultSelect->execute($dataSelect);
                } catch (PDOException $e) {
                    $URL .= "&return=error2$params";
                    header("Location: {$URL}");
                    exit();
                }
                $checkCount = $resultSelect->rowCount();
            } else {
                $URLSuccess = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/Data Updater/data_updates.php&pupilsightPersonID='.$pupilsightPersonID;

                try {
                    $dataCheck = array('pupilsightPersonID' => $_SESSION[$guid]['pupilsightPersonID']);
                    $sqlCheck = "SELECT pupilsightFamilyAdult.pupilsightFamilyID, name FROM pupilsightFamilyAdult JOIN pupilsightFamily ON (pupilsightFamilyAdult.pupilsightFamilyID=pupilsightFamily.pupilsightFamilyID) WHERE pupilsightPersonID=:pupilsightPersonID AND childDataAccess='Y' ORDER BY name";
                    $resultCheck = $connection2->prepare($sqlCheck);
                    $resultCheck->execute($dataCheck);
                } catch (PDOException $e) {
                    $URL .= "&return=error2$params";
                    header("Location: {$URL}");
                    exit();
                }
                while ($rowCheck = $resultCheck->fetch()) {
                    try {
                        $dataCheck2 = array('pupilsightFamilyID' => $rowCheck['pupilsightFamilyID'], 'pupilsightFamilyID2' => $rowCheck['pupilsightFamilyID']);
                        $sqlCheck2 = '(SELECT surname, preferredName, pupilsightPerson.pupilsightPersonID, pupilsightFamilyID FROM pupilsightFamilyChild JOIN pupilsightPerson ON (pupilsightFamilyChild.pupilsightPersonID=pupilsightPerson.pupilsightPersonID) WHERE pupilsightFamilyID=:pupilsightFamilyID) UNION (SELECT surname, preferredName, pupilsightPerson.pupilsightPersonID, pupilsightFamilyID FROM pupilsightFamilyAdult JOIN pupilsightPerson ON (pupilsightFamilyAdult.pupilsightPersonID=pupilsightPerson.pupilsightPersonID) WHERE pupilsightFamilyID=:pupilsightFamilyID2)';
                        $resultCheck2 = $connection2->prepare($sqlCheck2);
                        $resultCheck2->execute($dataCheck2);
                    } catch (PDOException $e) {
                        $URL .= "&return=error2$params";
                        header("Location: {$URL}");
                        exit();
                    }
                    while ($rowCheck2 = $resultCheck2->fetch()) {
                        if ($pupilsightPersonID == $rowCheck2['pupilsightPersonID']) {
                            ++$checkCount;
                        }
                    }
                }
            }
            if ($checkCount < 1) {
                $URL .= '&return=error2';
                header("Location: {$URL}");
            } else {
                $existing = $_POST['existing'];
                if ($existing != 'N') {
                    $AI = $existing;
                } else {
                    //Lock table
                    try {
                        $sqlLock = 'LOCK TABLES pupilsightPersonMedicalUpdate WRITE, pupilsightPersonMedicalConditionUpdate WRITE, pupilsightNotification WRITE, pupilsightModule WRITE, pupilsightPerson WRITE';
                        $resultLock = $connection2->query($sqlLock);
                    } catch (PDOException $e) {
                        $URL .= '&return=error2';
                        header("Location: {$URL}");
                        exit();
                    }

                    //Get next autoincrement
                    try {
                        $sqlAI = "SHOW TABLE STATUS LIKE 'pupilsightPersonMedicalUpdate'";
                        $resultAI = $connection2->query($sqlAI);
                    } catch (PDOException $e) {
                        $URL .= '&return=error2';
                        header("Location: {$URL}");
                        exit();
                    }

                    $rowAI = $resultAI->fetch();
                    $AI = str_pad($rowAI['Auto_increment'], 12, '0', STR_PAD_LEFT);
                }

                //Get medical form fields
                //Proceed!
                if ($_POST['pupilsightPersonMedicalID'] != '') {
                    $pupilsightPersonMedicalID = $_POST['pupilsightPersonMedicalID'];
                } else {
                    $pupilsightPersonMedicalID = null;
                }

                $bloodType = $_POST['bloodType'];
                $longTermMedication = $_POST['longTermMedication'];
                $longTermMedicationDetails = isset($_POST['longTermMedicationDetails'])? $_POST['longTermMedicationDetails'] : '';
                $tetanusWithin10Years = $_POST['tetanusWithin10Years'];
                $comment = $_POST['comment'];

                //Update existing medical conditions
                $partialFail = false;
                $count = 0;
                if (isset($_POST['count'])) {
                    $count = $_POST['count'];
                }

                if ($existing != 'N') {
                    for ($i = 0; $i < $count; ++$i) {
                        if ($AI != '') {
                            $pupilsightPersonMedicalUpdateID = $AI;
                        } else {
                            $pupilsightPersonMedicalUpdateID = null;
                        }
                        $pupilsightPersonMedicalConditionID = $_POST["pupilsightPersonMedicalConditionID$i"];
                        $pupilsightPersonMedicalConditionUpdateID = $_POST["pupilsightPersonMedicalConditionUpdateID$i"];
                        $name = $_POST["name$i"];
                        $pupilsightAlertLevelID = $_POST["pupilsightAlertLevelID$i"];
                        $triggers = $_POST["triggers$i"];
                        $reaction = $_POST["reaction$i"];
                        $response = $_POST["response$i"];
                        $medication = $_POST["medication$i"];
                        if ($_POST["lastEpisode$i"] != '') {
                            $lastEpisode = dateConvert($guid, $_POST["lastEpisode$i"]);
                        } else {
                            $lastEpisode = null;
                        }
                        $lastEpisodeTreatment = $_POST["lastEpisodeTreatment$i"];
                        $commentCond = $_POST["commentCond$i"];

                        try {
                            $data = array('pupilsightPersonMedicalUpdateID' => $pupilsightPersonMedicalUpdateID, 'pupilsightPersonMedicalID' => $pupilsightPersonMedicalID, 'name' => $name, 'pupilsightAlertLevelID' => $pupilsightAlertLevelID, 'triggers' => $triggers, 'reaction' => $reaction, 'response' => $response, 'medication' => $medication, 'lastEpisode' => $lastEpisode, 'lastEpisodeTreatment' => $lastEpisodeTreatment, 'comment' => $commentCond, 'pupilsightPersonIDUpdater' => $_SESSION[$guid]['pupilsightPersonID'], 'pupilsightPersonMedicalConditionUpdateID' => $pupilsightPersonMedicalConditionUpdateID);
                            $sql = 'UPDATE pupilsightPersonMedicalConditionUpdate SET pupilsightPersonMedicalUpdateID=:pupilsightPersonMedicalUpdateID, pupilsightPersonMedicalID=:pupilsightPersonMedicalID, name=:name, pupilsightAlertLevelID=:pupilsightAlertLevelID, triggers=:triggers, reaction=:reaction, response=:response, medication=:medication, lastEpisode=:lastEpisode, lastEpisodeTreatment=:lastEpisodeTreatment, comment=:comment, pupilsightPersonIDUpdater=:pupilsightPersonIDUpdater WHERE pupilsightPersonMedicalConditionUpdateID=:pupilsightPersonMedicalConditionUpdateID';
                            $result = $connection2->prepare($sql);
                            $result->execute($data);
                        } catch (PDOException $e) {
                            $partialFail = true;
                        }
                    }
                } else {
                    for ($i = 0; $i < $count; ++$i) {
                        if ($AI != '') {
                            $pupilsightPersonMedicalUpdateID = $AI;
                        } else {
                            $pupilsightPersonMedicalUpdateID = null;
                        }
                        $pupilsightPersonMedicalConditionID = $_POST["pupilsightPersonMedicalConditionID$i"];
                        $name = $_POST["name$i"];
                        $pupilsightAlertLevelID = $_POST["pupilsightAlertLevelID$i"];
                        $triggers = $_POST["triggers$i"];
                        $reaction = $_POST["reaction$i"];
                        $response = $_POST["response$i"];
                        $medication = $_POST["medication$i"];
                        if ($_POST["lastEpisode$i"] != '') {
                            $lastEpisode = dateConvert($guid, $_POST["lastEpisode$i"]);
                        } else {
                            $lastEpisode = null;
                        }
                        $lastEpisodeTreatment = $_POST["lastEpisodeTreatment$i"];
                        $commentCond = $_POST["commentCond$i"];

                        try {
                            $data = array('pupilsightPersonMedicalUpdateID' => $pupilsightPersonMedicalUpdateID, 'pupilsightPersonMedicalConditionID' => $pupilsightPersonMedicalConditionID, 'pupilsightPersonMedicalID' => $pupilsightPersonMedicalID, 'name' => $name, 'pupilsightAlertLevelID' => $pupilsightAlertLevelID, 'triggers' => $triggers, 'reaction' => $reaction, 'response' => $response, 'medication' => $medication, 'lastEpisode' => $lastEpisode, 'lastEpisodeTreatment' => $lastEpisodeTreatment, 'comment' => $commentCond, 'pupilsightPersonIDUpdater' => $_SESSION[$guid]['pupilsightPersonID']);
                            $sql = 'INSERT INTO pupilsightPersonMedicalConditionUpdate SET pupilsightPersonMedicalUpdateID=:pupilsightPersonMedicalUpdateID, pupilsightPersonMedicalConditionID=:pupilsightPersonMedicalConditionID, pupilsightPersonMedicalID=:pupilsightPersonMedicalID, name=:name, pupilsightAlertLevelID=:pupilsightAlertLevelID, triggers=:triggers, reaction=:reaction, response=:response, medication=:medication, lastEpisode=:lastEpisode, lastEpisodeTreatment=:lastEpisodeTreatment, comment=:comment, pupilsightPersonIDUpdater=:pupilsightPersonIDUpdater';
                            $result = $connection2->prepare($sql);
                            $result->execute($data);
                        } catch (PDOException $e) {
                            $partialFail = true;
                        }
                    }
                }

                //Add new medical condition
                if (isset($_POST['addCondition'])) {
                    if ($_POST['addCondition'] == 'Yes') {
                        if ($_POST['name'] != '' and $_POST['pupilsightAlertLevelID'] != '') {
                            if ($AI != '') {
                                $pupilsightPersonMedicalUpdateID = $AI;
                            } else {
                                $pupilsightPersonMedicalUpdateID = null;
                            }
                            $name = $_POST['name'];
                            $pupilsightAlertLevelID = null;
                            if ($_POST['pupilsightAlertLevelID'] != 'Please select...') {
                                $pupilsightAlertLevelID = $_POST['pupilsightAlertLevelID'];
                            }
                            $triggers = $_POST['triggers'];
                            $reaction = $_POST['reaction'];
                            $response = $_POST['response'];
                            $medication = $_POST['medication'];
                            if ($_POST['lastEpisode'] != '') {
                                $lastEpisode = dateConvert($guid, $_POST['lastEpisode']);
                            } else {
                                $lastEpisode = null;
                            }
                            $lastEpisodeTreatment = $_POST['lastEpisodeTreatment'];
                            $commentCond = $_POST['commentCond'];

                            try {
                                $data = array('pupilsightPersonMedicalUpdateID' => $pupilsightPersonMedicalUpdateID, 'pupilsightPersonMedicalID' => $pupilsightPersonMedicalID, 'name' => $name, 'pupilsightAlertLevelID' => $pupilsightAlertLevelID, 'triggers' => $triggers, 'reaction' => $reaction, 'response' => $response, 'medication' => $medication, 'lastEpisode' => $lastEpisode, 'lastEpisodeTreatment' => $lastEpisodeTreatment, 'comment' => $commentCond, 'pupilsightPersonIDUpdater' => $_SESSION[$guid]['pupilsightPersonID']);
                                $sql = 'INSERT INTO pupilsightPersonMedicalConditionUpdate SET pupilsightPersonMedicalUpdateID=:pupilsightPersonMedicalUpdateID, pupilsightPersonMedicalID=:pupilsightPersonMedicalID, name=:name, pupilsightAlertLevelID=:pupilsightAlertLevelID, triggers=:triggers, reaction=:reaction, response=:response, medication=:medication, lastEpisode=:lastEpisode, lastEpisodeTreatment=:lastEpisodeTreatment, comment=:comment, pupilsightPersonIDUpdater=:pupilsightPersonIDUpdater';
                                $result = $connection2->prepare($sql);
                                $result->execute($data);
                            } catch (PDOException $e) {
                                $partialFail = true;
                            }
                        }
                    }
                }

                //Write to database
                try {
                    if ($existing != 'N') {
                        $data = array('pupilsightSchoolYearID' => $_SESSION[$guid]['pupilsightSchoolYearID'], 'pupilsightPersonMedicalID' => $pupilsightPersonMedicalID, 'pupilsightPersonID' => $pupilsightPersonID, 'bloodType' => $bloodType, 'longTermMedication' => $longTermMedication, 'longTermMedicationDetails' => $longTermMedicationDetails, 'tetanusWithin10Years' => $tetanusWithin10Years, 'comment' => $comment, 'pupilsightPersonIDUpdater' => $_SESSION[$guid]['pupilsightPersonID'], 'pupilsightPersonMedicalUpdateID' => $existing);
                        $sql = 'UPDATE pupilsightPersonMedicalUpdate SET pupilsightSchoolYearID=:pupilsightSchoolYearID, pupilsightPersonMedicalID=:pupilsightPersonMedicalID, pupilsightPersonID=:pupilsightPersonID, bloodType=:bloodType, longTermMedication=:longTermMedication, longTermMedicationDetails=:longTermMedicationDetails, tetanusWithin10Years=:tetanusWithin10Years, comment=:comment, pupilsightPersonIDUpdater=:pupilsightPersonIDUpdater, timestamp=NOW() WHERE pupilsightPersonMedicalUpdateID=:pupilsightPersonMedicalUpdateID';
                    } else {
                        $data = array('pupilsightSchoolYearID' => $_SESSION[$guid]['pupilsightSchoolYearID'], 'pupilsightPersonMedicalID' => $pupilsightPersonMedicalID, 'pupilsightPersonID' => $pupilsightPersonID, 'bloodType' => $bloodType, 'longTermMedication' => $longTermMedication, 'longTermMedicationDetails' => $longTermMedicationDetails, 'tetanusWithin10Years' => $tetanusWithin10Years, 'comment' => $comment, 'pupilsightPersonIDUpdater' => $_SESSION[$guid]['pupilsightPersonID']);
                        $sql = 'INSERT INTO pupilsightPersonMedicalUpdate SET pupilsightSchoolYearID=:pupilsightSchoolYearID, pupilsightPersonMedicalID=:pupilsightPersonMedicalID, pupilsightPersonID=:pupilsightPersonID, bloodType=:bloodType, longTermMedication=:longTermMedication, longTermMedicationDetails=:longTermMedicationDetails, tetanusWithin10Years=:tetanusWithin10Years, comment=:comment, pupilsightPersonIDUpdater=:pupilsightPersonIDUpdater';
                    }
                    $result = $connection2->prepare($sql);
                    $result->execute($data);
                } catch (PDOException $e) {
                    $URL .= '&return=error2';
                    header("Location: {$URL}");
                    exit();
                }

                if ($existing == 'N') {
                    try {
                        $sqlLock = 'UNLOCK TABLES';
                        $result = $connection2->query($sqlLock);
                    } catch (PDOException $e) {
                    }
                }

                // Raise a new notification event
                $event = new NotificationEvent('Data Updater', 'Medical Form Updates');

                $event->addRecipient($_SESSION[$guid]['organisationDBA']);
                $event->setNotificationText(__('A medical data update request has been submitted.'));
                $event->setActionLink('/index.php?q=/modules/Data Updater/data_medical_manage.php');

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
