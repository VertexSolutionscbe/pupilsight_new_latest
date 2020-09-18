<?php
/*
Pupilsight, Flexible & Open School System
*/

include '../../pupilsight.php';

$pupilsightPersonMedicalID = $_GET['pupilsightPersonMedicalID'];
$pupilsightPersonMedicalConditionID = $_GET['pupilsightPersonMedicalConditionID'];
$search = $_GET['search'];
if ($pupilsightPersonMedicalID == '' or $pupilsightPersonMedicalConditionID == '') { echo 'Fatal error loading this page!';
} else {
    $URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_POST['address'])."/medicalForm_manage_condition_edit.php&pupilsightPersonMedicalID=$pupilsightPersonMedicalID&pupilsightPersonMedicalConditionID=$pupilsightPersonMedicalConditionID&search=$search";

    if (isActionAccessible($guid, $connection2, '/modules/Students/medicalForm_manage_condition_edit.php') == false) {
        $URL .= '&return=error0';
        header("Location: {$URL}");
    } else {
        //Proceed!
        //Check if person specified
        if ($pupilsightPersonMedicalConditionID == '') {
            $URL .= '&return=error1';
            header("Location: {$URL}");
        } else {
            try {
                $data = array('pupilsightPersonMedicalConditionID' => $pupilsightPersonMedicalConditionID);
                $sql = 'SELECT * FROM pupilsightPersonMedicalCondition WHERE pupilsightPersonMedicalConditionID=:pupilsightPersonMedicalConditionID';
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
                    $URL .= '&return=error3';
                    header("Location: {$URL}");
                } else {
                    //Write to database
                    try {
                        $data = array('pupilsightPersonMedicalID' => $pupilsightPersonMedicalID, 'name' => $name, 'pupilsightAlertLevelID' => $pupilsightAlertLevelID, 'triggers' => $triggers, 'reaction' => $reaction, 'response' => $response, 'medication' => $medication, 'lastEpisode' => $lastEpisode, 'lastEpisodeTreatment' => $lastEpisodeTreatment, 'comment' => $comment, 'pupilsightPersonMedicalConditionID' => $pupilsightPersonMedicalConditionID);
                        $sql = 'UPDATE pupilsightPersonMedicalCondition SET pupilsightPersonMedicalID=:pupilsightPersonMedicalID, name=:name, pupilsightAlertLevelID=:pupilsightAlertLevelID, triggers=:triggers, reaction=:reaction, response=:response, medication=:medication, lastEpisode=:lastEpisode, lastEpisodeTreatment=:lastEpisodeTreatment, comment=:comment WHERE pupilsightPersonMedicalConditionID=:pupilsightPersonMedicalConditionID';
                        $result = $connection2->prepare($sql);
                        $result->execute($data);
                    } catch (PDOException $e) {
                        $URL .= '&return=error2';
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
