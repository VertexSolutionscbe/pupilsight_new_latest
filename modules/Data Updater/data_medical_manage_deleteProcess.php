<?php
/*
Pupilsight, Flexible & Open School System
*/

include '../../pupilsight.php';

$pupilsightSchoolYearID = isset($_POST['pupilsightSchoolYearID'])? $_POST['pupilsightSchoolYearID'] : $_SESSION[$guid]['pupilsightSchoolYearID'];
$pupilsightPersonMedicalUpdateID = $_POST['pupilsightPersonMedicalUpdateID'];
$URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_POST['address'])."/data_medical_manage_delete.php&pupilsightPersonMedicalUpdateID=$pupilsightPersonMedicalUpdateID&pupilsightSchoolYearID=$pupilsightSchoolYearID";
$URLDelete = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_POST['address']).'/data_medical_manage.php&pupilsightSchoolYearID='.$pupilsightSchoolYearID;

if (isActionAccessible($guid, $connection2, '/modules/Data Updater/data_medical_manage_delete.php') == false) {
    $URL .= '&return=error0';
    header("Location: {$URL}");
} else {
    //Proceed!
    if ($pupilsightPersonMedicalUpdateID == '') {
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
            //Write to database
            try {
                $data = array('pupilsightPersonMedicalUpdateID' => $pupilsightPersonMedicalUpdateID);
                $sql = 'DELETE FROM pupilsightPersonMedicalUpdate WHERE pupilsightPersonMedicalUpdateID=:pupilsightPersonMedicalUpdateID';
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
