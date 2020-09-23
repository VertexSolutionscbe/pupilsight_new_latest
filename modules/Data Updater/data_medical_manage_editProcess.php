<?php
/*
Pupilsight, Flexible & Open School System
*/

include '../../pupilsight.php';

$pupilsightPersonMedicalUpdateID = $_GET['pupilsightPersonMedicalUpdateID'];
$pupilsightPersonID = $_POST['pupilsightPersonID'];
$URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_POST['address'])."/data_medical_manage_edit.php&pupilsightPersonMedicalUpdateID=$pupilsightPersonMedicalUpdateID";

if (isActionAccessible($guid, $connection2, '/modules/Data Updater/data_medical_manage_edit.php') == false) {
    $URL .= '&return=error0';
    header("Location: {$URL}");
} else {
    //Proceed!
    //Check if school year specified
    if ($pupilsightPersonMedicalUpdateID == '' or $pupilsightPersonID == '') {
        $URL .= '&return=error1';
        header("Location: {$URL}");
    } else {
        try {
            $data = array('pupilsightPersonMedicalUpdateID' => $pupilsightPersonMedicalUpdateID);
            $sql = 'SELECT * FROM pupilsightPersonMedicalUpdate WHERE pupilsightPersonMedicalUpdateID=:pupilsightPersonMedicalUpdateID';
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
            $row = $result->fetch();
            $pupilsightPersonMedicalID = $row['pupilsightPersonMedicalID'];

            //Lock table
            try {
                $sql = 'LOCK TABLES pupilsightPersonMedical WRITE, pupilsightPersonMedicalCondition WRITE, pupilsightPersonMedicalConditionUpdate WRITE';
                $result = $connection2->query($sql);
            } catch (PDOException $e) {
                $URL .= '&return=error2';
                header("Location: {$URL}");
                exit();
            }

            //Get next autoincrement
            try {
                $sqlAI = "SHOW TABLE STATUS LIKE 'pupilsightPersonMedical'";
                $resultAI = $connection2->query($sqlAI);
            } catch (PDOException $e) {
                $URL .= '&return=error2';
                header("Location: {$URL}");
                exit();
            }

            $rowAI = $resultAI->fetch();
            $AI = str_pad($rowAI['Auto_increment'], 10, '0', STR_PAD_LEFT);
            if ($pupilsightPersonMedicalID == '') {
                $pupilsightPersonMedicalID = $AI;
            }

            //Set values
            $data = array();
            $sqlSet = '';
            if (isset($_POST['bloodTypeOn'])) {
                if ($_POST['bloodTypeOn'] == 'on') {
                    $data['bloodType'] = $_POST['bloodType'];
                    $sqlSet .= 'bloodType=:bloodType, ';
                }
            }
            if (isset($_POST['longTermMedicationOn'])) {
                if ($_POST['longTermMedicationOn'] == 'on') {
                    $data['longTermMedication'] = $_POST['longTermMedication'];
                    $sqlSet .= 'longTermMedication=:longTermMedication, ';
                }
            }
            if (isset($_POST['longTermMedicationDetailsOn'])) {
                if ($_POST['longTermMedicationDetailsOn'] == 'on') {
                    $data['longTermMedicationDetails'] = $_POST['longTermMedicationDetails'];
                    $sqlSet .= 'longTermMedicationDetails=:longTermMedicationDetails, ';
                }
            }
            if (isset($_POST['tetanusWithin10YearsOn'])) {
                if ($_POST['tetanusWithin10YearsOn'] == 'on') {
                    $data['tetanusWithin10Years'] = $_POST['tetanusWithin10Years'];
                    $sqlSet .= 'tetanusWithin10Years=:tetanusWithin10Years, ';
                }
            }
            if (isset($_POST['commentOn'])) {
                if ($_POST['commentOn'] == 'on') {
                    $data['comment'] = $_POST['comment'];
                    $sqlSet .= 'comment=:comment, ';
                }
            }

            $partialFail = false;

            //Write to database
            //If form already exisits
            $count = 0;
            $count2 = 0;
            if ($_POST['formExists'] == true) {
                //Scan through existing conditions
                if (isset($_POST['count'])) {
                    $count = $_POST['count'];
                }
                for ($i = 0; $i < $count; ++$i) {
                    $dataCond = array();
                    $sqlSetCond = '';
                    if (isset($_POST["nameOn$i"])) {
                        if ($_POST["nameOn$i"] == 'on') {
                            $dataCond['name'] = $_POST["name$i"];
                            $sqlSetCond .= 'name=:name, ';
                        }
                    }
                    if (isset($_POST["pupilsightAlertLevelIDOn$i"])) {
                        if ($_POST["pupilsightAlertLevelIDOn$i"] == 'on') {
                            if ($_POST["pupilsightAlertLevelID$i"] != '') {
                                $dataCond['pupilsightAlertLevelID'] = $_POST["pupilsightAlertLevelID$i"];
                                $sqlSetCond .= 'pupilsightAlertLevelID=:pupilsightAlertLevelID, ';
                            }
                        }
                    }
                    if (isset($_POST["triggersOn$i"])) {
                        if ($_POST["triggersOn$i"] == 'on') {
                            $dataCond['triggers'] = $_POST["triggers$i"];
                            $sqlSetCond .= 'triggers=:triggers, ';
                        }
                    }
                    if (isset($_POST["reactionOn$i"])) {
                        if ($_POST["reactionOn$i"] == 'on') {
                            $dataCond['reaction'] = $_POST["reaction$i"];
                            $sqlSetCond .= 'reaction=:reaction, ';
                        }
                    }
                    if (isset($_POST["responseOn$i"])) {
                        if ($_POST["responseOn$i"] == 'on') {
                            $dataCond['response'] = $_POST["response$i"];
                            $sqlSetCond .= 'response=:response, ';
                        }
                    }
                    if (isset($_POST["medicationOn$i"])) {
                        if ($_POST["medicationOn$i"] == 'on') {
                            $dataCond['medication'] = $_POST["medication$i"];
                            $sqlSetCond .= 'medication=:medication, ';
                        }
                    }
                    if (isset($_POST["lastEpisodeOn$i"])) {
                        if ($_POST["lastEpisodeOn$i"] == 'on') {
                            if ($_POST["lastEpisode$i"] != '') {
                                $dataCond['lastEpisode'] = $_POST["lastEpisode$i"];
                                $sqlSetCond .= 'lastEpisode=:lastEpisode, ';
                            } else {
                                $sqlSetCond .= 'lastEpisode=NULL, ';
                            }
                        }
                    }
                    if (isset($_POST["lastEpisodeTreatmentOn$i"])) {
                        if ($_POST["lastEpisodeTreatmentOn$i"] == 'on') {
                            $dataCond['lastEpisodeTreatment'] = $_POST["lastEpisodeTreatment$i"];
                            $sqlSetCond .= 'lastEpisodeTreatment=:lastEpisodeTreatment, ';
                        }
                    }
                    if (isset($_POST["commentOn$i"])) {
                        if ($_POST["commentOn$i"] == 'on') {
                            $dataCond['comment'] = $_POST["comment$i"];
                            $sqlSetCond .= 'comment=:comment, ';
                        }
                    }

                    try {
                        $dataCond['pupilsightPersonMedicalID'] = $pupilsightPersonMedicalID;
                        $dataCond['pupilsightPersonMedicalConditionID'] = $_POST["pupilsightPersonMedicalConditionID$i"];
                        $sqlCond = "UPDATE pupilsightPersonMedicalCondition SET $sqlSetCond pupilsightPersonMedicalID=:pupilsightPersonMedicalID WHERE pupilsightPersonMedicalConditionID=:pupilsightPersonMedicalConditionID";
                        $resultCond = $connection2->prepare($sqlCond);
                        $resultCond->execute($dataCond);
                    } catch (PDOException $e) {
                        $partialFail = true;
                    }
                }

                //Scan through new conditions
                if (isset($_POST['count2'])) {
                    $count2 = $_POST['count2'];
                }
                for ($i = ($count + 1); $i <= ($count + $count2); ++$i) {
                    if (isset($_POST["nameOn$i"]) && $_POST["nameOn$i"] == 'on' && $_POST["pupilsightPersonMedicalConditionUpdateID$i"] != '') {
                        $dataCond = array();
                        $sqlSetCond = '';
                        if (isset($_POST["nameOn$i"])) {
                            if ($_POST["nameOn$i"] == 'on') {
                                $dataCond['name'] = $_POST["name$i"];
                                $sqlSetCond .= 'name=:name, ';
                            }
                        }
                        if (isset($_POST["pupilsightAlertLevelIDOn$i"])) {
                            if ($_POST["pupilsightAlertLevelIDOn$i"] == 'on') {
                                if ($_POST["pupilsightAlertLevelID$i"] != '') {
                                    $dataCond['pupilsightAlertLevelID'] = $_POST["pupilsightAlertLevelID$i"];
                                    $sqlSetCond .= 'pupilsightAlertLevelID=:pupilsightAlertLevelID, ';
                                }
                            }
                        }
                        if (isset($_POST["triggersOn$i"])) {
                            if ($_POST["triggersOn$i"] == 'on') {
                                $dataCond['triggers'] = $_POST["triggers$i"];
                                $sqlSetCond .= 'triggers=:triggers, ';
                            }
                        }
                        if (isset($_POST["reactionOn$i"])) {
                            if ($_POST["reactionOn$i"] == 'on') {
                                $dataCond['reaction'] = $_POST["reaction$i"];
                                $sqlSetCond .= 'reaction=:reaction, ';
                            }
                        }
                        if (isset($_POST["responseOn$i"])) {
                            if ($_POST["responseOn$i"] == 'on') {
                                $dataCond['response'] = $_POST["response$i"];
                                $sqlSetCond .= 'response=:response, ';
                            }
                        }
                        if (isset($_POST["medicationOn$i"])) {
                            if ($_POST["medicationOn$i"] == 'on') {
                                $dataCond['medication'] = $_POST["medication$i"];
                                $sqlSetCond .= 'medication=:medication, ';
                            }
                        }
                        if (isset($_POST["lastEpisodeOn$i"])) {
                            if ($_POST["lastEpisodeOn$i"] == 'on') {
                                if ($_POST["lastEpisode$i"] != '') {
                                    $dataCond['lastEpisode'] = $_POST["lastEpisode$i"];
                                    $sqlSetCond .= 'lastEpisode=:lastEpisode, ';
                                } else {
                                    $sqlSetCond .= 'lastEpisode=NULL, ';
                                }
                            }
                        }
                        if (isset($_POST["lastEpisodeTreatmentOn$i"])) {
                            if ($_POST["lastEpisodeTreatmentOn$i"] == 'on') {
                                $dataCond['lastEpisodeTreatment'] = $_POST["lastEpisodeTreatment$i"];
                                $sqlSetCond .= 'lastEpisodeTreatment=:lastEpisodeTreatment, ';
                            }
                        }
                        if (isset($_POST["commentOn$i"])) {
                            if ($_POST["commentOn$i"] == 'on') {
                                $dataCond['comment'] = $_POST["comment$i"];
                                $sqlSetCond .= 'comment=:comment, ';
                            }
                        }

                        try {
                            $dataCond['pupilsightPersonMedicalID'] = $pupilsightPersonMedicalID;
                            $sqlCond = "INSERT INTO pupilsightPersonMedicalCondition SET $sqlSetCond pupilsightPersonMedicalID=:pupilsightPersonMedicalID";
                            $resultCond = $connection2->prepare($sqlCond);
                            $resultCond->execute($dataCond);
                        } catch (PDOException $e) {
                            $partialFail = true;
                        }

                        try {
                            $dataCond = array('pupilsightPersonMedicalConditionID' => $connection2->lastInsertID(), 'pupilsightPersonMedicalConditionUpdateID' => $_POST["pupilsightPersonMedicalConditionUpdateID$i"]);
                            $sqlCond = 'UPDATE pupilsightPersonMedicalConditionUpdate SET pupilsightPersonMedicalConditionID=:pupilsightPersonMedicalConditionID WHERE pupilsightPersonMedicalConditionUpdateID=:pupilsightPersonMedicalConditionUpdateID';
                            $resultCond = $connection2->prepare($sqlCond);
                            $resultCond->execute($dataCond);
                        } catch (PDOException $e) {
                            $partialFail = true;
                        }
                    }
                }

                try {
                    $data['pupilsightPersonMedicalID'] = $pupilsightPersonMedicalID;
                    $data['pupilsightPersonID'] = $pupilsightPersonID;
                    $sql = "UPDATE pupilsightPersonMedical SET $sqlSet pupilsightPersonMedicalID=:pupilsightPersonMedicalID WHERE pupilsightPersonID=:pupilsightPersonID";
                    $result = $connection2->prepare($sql);
                    $result->execute($data);
                } catch (PDOException $e) {
                    echo "<div class='alert alert-danger'>".$e->getMessage().'</div>';
                    $URL .= '&return=error2';
                    header("Location: {$URL}");
                    exit();
                }

                //Unlock module table
                try {
                    $sql = 'UNLOCK TABLES';
                    $result = $connection2->query($sql);
                } catch (PDOException $e) {
                    $URL .= '&return=error2';
                    header("Location: {$URL}");
                    exit();
                }

                if ($partialFail == true) {
                    $URL .= '&return=warning1';
                    header("Location: {$URL}");
                } else {
                    //Write to database
                    try {
                        $data = array('pupilsightPersonMedicalUpdateID' => $pupilsightPersonMedicalUpdateID);
                        $sql = "UPDATE pupilsightPersonMedicalUpdate SET status='Complete' WHERE pupilsightPersonMedicalUpdateID=:pupilsightPersonMedicalUpdateID";
                        $result = $connection2->prepare($sql);
                        $result->execute($data);
                    } catch (PDOException $e) {
                        $URL .= '&return=warning1';
                        header("Location: {$URL}");
                        exit();
                    }

                    $URL .= '&return=success0';
                    header("Location: {$URL}");
                }
            }

            //If form does not already exist
            else {
                //Scan through new conditions
                if (isset($_POST['count2'])) {
                    $count2 = $_POST['count2'];
                }
                for ($i = ($count + 1); $i <= ($count + $count2); ++$i) {
                    if ($_POST["nameOn$i"] == 'on' and $_POST["pupilsightAlertLevelIDOn$i"] == 'on') {
                        //Scan through existing conditions
                        $dataCond = array();
                        $sqlSetCond = '';
                        if (isset($_POST["nameOn$i"])) {
                            if ($_POST["nameOn$i"] == 'on') {
                                $dataCond['name'] = $_POST["name$i"];
                                $sqlSetCond .= 'name=:name, ';
                            }
                        }
                        if (isset($_POST["pupilsightAlertLevelIDOn$i"])) {
                            if ($_POST["pupilsightAlertLevelIDOn$i"] == 'on') {
                                if ($_POST["pupilsightAlertLevelID$i"] != '') {
                                    $dataCond['pupilsightAlertLevelID'] = $_POST["pupilsightAlertLevelID$i"];
                                    $sqlSetCond .= 'pupilsightAlertLevelID=:pupilsightAlertLevelID, ';
                                }
                            }
                        }
                        if (isset($_POST["triggersOn$i"])) {
                            if ($_POST["triggersOn$i"] == 'on') {
                                $dataCond['triggers'] = $_POST["triggers$i"];
                                $sqlSetCond .= 'triggers=:triggers, ';
                            }
                        }
                        if (isset($_POST["reactionOn$i"])) {
                            if ($_POST["reactionOn$i"] == 'on') {
                                $dataCond['reaction'] = $_POST["reaction$i"];
                                $sqlSetCond .= 'reaction=:reaction, ';
                            }
                        }
                        if (isset($_POST["responseOn$i"])) {
                            if ($_POST["responseOn$i"] == 'on') {
                                $dataCond['response'] = $_POST["response$i"];
                                $sqlSetCond .= 'response=:response, ';
                            }
                        }
                        if (isset($_POST["medicationOn$i"])) {
                            if ($_POST["medicationOn$i"] == 'on') {
                                $dataCond['medication'] = $_POST["medication$i"];
                                $sqlSetCond .= 'medication=:medication, ';
                            }
                        }
                        if (isset($_POST["lastEpisodeOn$i"])) {
                            if ($_POST["lastEpisodeOn$i"] == 'on') {
                                if ($_POST["lastEpisode$i"] != '') {
                                    $dataCond['lastEpisode'] = $_POST["lastEpisode$i"];
                                    $sqlSetCond .= 'lastEpisode=:lastEpisode, ';
                                } else {
                                    $sqlSetCond .= 'lastEpisode=NULL, ';
                                }
                            }
                        }
                        if (isset($_POST["lastEpisodeTreatmentOn$i"])) {
                            if ($_POST["lastEpisodeTreatmentOn$i"] == 'on') {
                                $dataCond['lastEpisodeTreatment'] = $_POST["lastEpisodeTreatment$i"];
                                $sqlSetCond .= 'lastEpisodeTreatment=:lastEpisodeTreatment, ';
                            }
                        }
                        if (isset($_POST["commentOn$i"])) {
                            if ($_POST["commentOn$i"] == 'on') {
                                $dataCond['comment'] = $_POST["comment$i"];
                                $sqlSetCond .= 'comment=:comment, ';
                            }
                        }

                        try {
                            $dataCond['pupilsightPersonMedicalID'] = $pupilsightPersonMedicalID;
                            $sqlCond = "INSERT INTO pupilsightPersonMedicalCondition SET $sqlSetCond pupilsightPersonMedicalID=:pupilsightPersonMedicalID";
                            $resultCond = $connection2->prepare($sqlCond);
                            $resultCond->execute($dataCond);
                        } catch (PDOException $e) {
                            $partialFail = true;
                        }

                        try {
                            $dataCond = array('pupilsightPersonMedicalConditionID' => $connection2->lastInsertID(), 'pupilsightPersonMedicalConditionUpdateID' => $_POST["pupilsightPersonMedicalConditionUpdateID$i"]);
                            $sqlCond = 'UPDATE pupilsightPersonMedicalConditionUpdate SET pupilsightPersonMedicalConditionID=:pupilsightPersonMedicalConditionID WHERE pupilsightPersonMedicalConditionUpdateID=:pupilsightPersonMedicalConditionUpdateID';
                            $resultCond = $connection2->prepare($sqlCond);
                            $resultCond->execute($dataCond);
                        } catch (PDOException $e) {
                            $partialFail = true;
                        }
                    }
                }

                try {
                    if ($sqlSet != '') {
                        $data['pupilsightPersonID'] = $pupilsightPersonID;
                        $sql = 'INSERT INTO pupilsightPersonMedical SET pupilsightPersonID=:pupilsightPersonID, '.substr($sqlSet, 0, (strlen($sqlSet) - 2));
                    } else {
                        $data['pupilsightPersonID'] = $pupilsightPersonID;
                        $sql = 'INSERT INTO pupilsightPersonMedical SET pupilsightPersonID=:pupilsightPersonID';
                    }
                    $result = $connection2->prepare($sql);
                    $result->execute($data);
                } catch (PDOException $e) {
                    $URL .= '&return=error2';
                    header("Location: {$URL}");
                    exit();
                }

                //Unlock module table
                try {
                    $sql = 'UNLOCK TABLES';
                    $result = $connection2->query($sql);
                } catch (PDOException $e) {
                    $URL .= '&return=error2';
                    header("Location: {$URL}");
                    exit();
                }

                if ($partialFail == true) {
                    $URL .= '&return=warning1';
                    header("Location: {$URL}");
                } else {
                    //Write to database
                    try {
                        $data = array('pupilsightPersonMedicalUpdateID' => $pupilsightPersonMedicalUpdateID);
                        $sql = "UPDATE pupilsightPersonMedicalUpdate SET status='Complete' WHERE pupilsightPersonMedicalUpdateID=:pupilsightPersonMedicalUpdateID";
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
