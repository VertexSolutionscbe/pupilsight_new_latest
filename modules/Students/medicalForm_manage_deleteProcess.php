<?php
/*
Pupilsight, Flexible & Open School System
*/

include '../../pupilsight.php';

$pupilsightPersonMedicalID = $_GET['pupilsightPersonMedicalID'];
$search = $_GET['search'];
$URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_POST['address']).'/medicalForm_manage_delete.php&pupilsightPersonMedicalID='.$pupilsightPersonMedicalID."&search=$search";
$URLDelete = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_POST['address'])."/medicalForm_manage.php&search=$search";

if (isActionAccessible($guid, $connection2, '/modules/Students/medicalForm_manage_delete.php') == false) {
    $URL .= '&return=error0';
    header("Location: {$URL}");
} else {
    //Proceed!
    //Check if medical form specified
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
            //Write to database
            try {
                $data = array('pupilsightPersonMedicalID' => $pupilsightPersonMedicalID);
                $sql = 'DELETE FROM pupilsightPersonMedical WHERE pupilsightPersonMedicalID=:pupilsightPersonMedicalID';
                $result = $connection2->prepare($sql);
                $result->execute($data);
            } catch (PDOException $e) {
                $URL .= '&return=error2';
                header("Location: {$URL}");
                exit();
            }

            try {
                $data = array('pupilsightPersonMedicalID' => $pupilsightPersonMedicalID);
                $sql = 'DELETE FROM pupilsightPersonMedicalCondition WHERE pupilsightPersonMedicalID=:pupilsightPersonMedicalID';
                $result = $connection2->prepare($sql);
                $result->execute($data);
            } catch (PDOException $e) {
                $URL .= '&return=warning2';
                header("Location: {$URL}");
                exit();
            }

            $URLDelete = $URLDelete.'&return=success0';
            header("Location: {$URLDelete}");
        }
    }
}
