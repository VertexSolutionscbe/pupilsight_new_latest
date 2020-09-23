<?php
/*
Pupilsight, Flexible & Open School System
*/

include '../../pupilsight.php';

//Check if school year specified
$pupilsightPersonMedicalID = $_GET['pupilsightPersonMedicalID'];
$pupilsightPersonMedicalConditionID = $_GET['pupilsightPersonMedicalConditionID'];
$search = $_GET['search'];
if ($pupilsightPersonMedicalID == '' or $pupilsightPersonMedicalConditionID == '') { echo 'Fatal error loading this page!';
} else {
    $URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_POST['address'])."/medicalForm_manage_condition_delete.php&pupilsightPersonMedicalID=$pupilsightPersonMedicalID&pupilsightPersonMedicalConditionID=$pupilsightPersonMedicalConditionID&search=$search";
    $URLDelete = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_POST['address'])."/medicalForm_manage_edit.php&pupilsightPersonMedicalID=$pupilsightPersonMedicalID&search=$search";

    if (isActionAccessible($guid, $connection2, '/modules/Students/medicalForm_manage_condition_delete.php') == false) {
        $URL .= '&return=error0';
        header("Location: {$URL}");
    } else {
        //Proceed!
        //Check if condition specified
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
                //Write to database
                try {
                    $data = array('pupilsightPersonMedicalConditionID' => $pupilsightPersonMedicalConditionID);
                    $sql = 'DELETE FROM pupilsightPersonMedicalCondition WHERE pupilsightPersonMedicalConditionID=:pupilsightPersonMedicalConditionID';
                    $result = $connection2->prepare($sql);
                    $result->execute($data);
                } catch (PDOException $e) {
                    $URL .= '&return=error2';
                    header("Location: {$URL}");
                    exit();
                }

                $URLDelete = $URLDelete.'&return=success0';
                header("Location: {$URLDelete}");
            }
        }
    }
}
