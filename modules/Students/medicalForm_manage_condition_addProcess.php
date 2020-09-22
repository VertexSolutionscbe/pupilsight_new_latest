<?php
/*
Pupilsight, Flexible & Open School System
*/

include '../../pupilsight.php';

$pupilsightPersonMedicalID = $_POST['pupilsightPersonMedicalID'];
$search = $_GET['search'];

if ($pupilsightPersonMedicalID == '') { echo 'Fatal error loading this page!';
} else {
    $URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_POST['address'])."/medicalForm_manage_condition_add.php&pupilsightPersonMedicalID=$pupilsightPersonMedicalID&search=$search";

    if (isActionAccessible($guid, $connection2, '/modules/Students/medicalForm_manage_condition_add.php') == false) {
        $URL .= '&return=error0';
        header("Location: {$URL}");
    } else {
        //Proceed!
        //Check if person specified
        if ($pupilsightPersonMedicalID == '') {
            $URL .= '&return=error1';
            header("Location: {$URL}");
        } else {
            try {
                $data = array('pupilsightPersonMedicalID' => $pupilsightPersonMedicalID);
                $sql = 'SELECT * FROM pupilsightPersonMedical WHERE pupilsightPersonMedicalID=:pupilsightPersonMedicalID';
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
                //Validate Inputs
                $name = $_POST['name'];
                $pupilsightAlertLevelID = $_POST['pupilsightAlertLevelID'];
                $triggers = $_POST['triggers'];
                $reaction = $_POST['reaction'];
                $response = $_POST['response'];
                $medication = $_POST['medication'];
                if ($_POST['lastEpisode'] == '') {
                    $lastEpisode = null;
                } else {
                    $lastEpisode = dateConvert($guid, $_POST['lastEpisode']);
                }
                $lastEpisodeTreatment = $_POST['lastEpisodeTreatment'];
                $comment = $_POST['comment'];

                if ($name == '' or $pupilsightAlertLevelID == '') {
                    $URL .= '&return=error1';
                    header("Location: {$URL}");
                } else {
                    //Write to database
                    try {
                        $data = array('pupilsightPersonMedicalID' => $pupilsightPersonMedicalID, 'name' => $name, 'pupilsightAlertLevelID' => $pupilsightAlertLevelID, 'triggers' => $triggers, 'reaction' => $reaction, 'response' => $response, 'medication' => $medication, 'lastEpisode' => $lastEpisode, 'lastEpisodeTreatment' => $lastEpisodeTreatment, 'comment' => $comment);
                        $sql = 'INSERT INTO pupilsightPersonMedicalCondition SET pupilsightPersonMedicalID=:pupilsightPersonMedicalID, name=:name, pupilsightAlertLevelID=:pupilsightAlertLevelID, triggers=:triggers, reaction=:reaction, response=:response, medication=:medication, lastEpisode=:lastEpisode, lastEpisodeTreatment=:lastEpisodeTreatment, comment=:comment';
                        $result = $connection2->prepare($sql);
                        $result->execute($data);
                    } catch (PDOException $e) {
                        $URL .= '&return=error2';
                        header("Location: {$URL}");
                        exit();
                    }

                    //Last insert ID
                    $AI = str_pad($connection2->lastInsertID(), 12, '0', STR_PAD_LEFT);

                    $URL .= "&return=success0&editID=$AI";
                    header("Location: {$URL}");
                }
            }
        }
    }
}
