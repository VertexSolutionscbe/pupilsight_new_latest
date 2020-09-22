<?php
/*
Pupilsight, Flexible & Open School System
*/

include '../../pupilsight.php';

$pupilsightPersonMedicalID = $_GET['pupilsightPersonMedicalID'];
$search = $_GET['search'];
$URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_POST['address'])."/medicalForm_manage_edit.php&pupilsightPersonMedicalID=$pupilsightPersonMedicalID&search=$search";

if (isActionAccessible($guid, $connection2, '/modules/Students/medicalForm_manage_edit.php') == false) {
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
            $bloodType = $_POST['bloodType'];
            $longTermMedication = $_POST['longTermMedication'];
            $longTermMedicationDetails = (isset($_POST['longTermMedicationDetails']) ? $_POST['longTermMedicationDetails'] : '');
            $tetanusWithin10Years = $_POST['tetanusWithin10Years'];
            $comment = $_POST['comment'];

            //Write to database
            try {
                $data = array('bloodType' => $bloodType, 'longTermMedication' => $longTermMedication, 'longTermMedicationDetails' => $longTermMedicationDetails, 'tetanusWithin10Years' => $tetanusWithin10Years, 'comment' => $comment, 'pupilsightPersonMedicalID' => $pupilsightPersonMedicalID);
                $sql = 'UPDATE pupilsightPersonMedical SET bloodType=:bloodType, longTermMedication=:longTermMedication, longTermMedicationDetails=:longTermMedicationDetails, tetanusWithin10Years=:tetanusWithin10Years, comment=:comment WHERE pupilsightPersonMedicalID=:pupilsightPersonMedicalID';
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
