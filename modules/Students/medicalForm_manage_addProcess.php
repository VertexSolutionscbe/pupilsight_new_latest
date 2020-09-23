<?php
/*
Pupilsight, Flexible & Open School System
*/

include '../../pupilsight.php';

$search = $_GET['search'];
$URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_POST['address'])."/medicalForm_manage_add.php&search=$search";

if (isActionAccessible($guid, $connection2, '/modules/Students/medicalForm_manage_add.php') == false) {
    $URL .= '&return=error0';
    header("Location: {$URL}");
} else {
    //Proceed!
    $pupilsightPersonID = $_POST['pupilsightPersonID'];
    $bloodType = $_POST['bloodType'];
    $longTermMedication = $_POST['longTermMedication'];
    $longTermMedicationDetails = (isset($_POST['longTermMedicationDetails']) ? $_POST['longTermMedicationDetails'] : '');
    $tetanusWithin10Years = $_POST['tetanusWithin10Years'];
    $comment = $_POST['comment'];

    //Validate Inputs
    if ($pupilsightPersonID == '') {
        $URL .= '&return=error1';
        header("Location: {$URL}");
    } else {
        //Check unique inputs for uniquness
        try {
            $data = array('pupilsightPersonID' => $pupilsightPersonID);
            $sql = 'SELECT * FROM pupilsightPersonMedical WHERE pupilsightPersonID=:pupilsightPersonID';
            $result = $connection2->prepare($sql);
            $result->execute($data);
        } catch (PDOException $e) {
            $URL .= '&return=error2';
            header("Location: {$URL}");
            exit();
        }

        if ($result->rowCount() > 0) {
            $URL .= '&return=error3';
            header("Location: {$URL}");
        } else {
            //Write to database
            try {
                $data = array('pupilsightPersonID' => $pupilsightPersonID, 'bloodType' => $bloodType, 'longTermMedication' => $longTermMedication, 'longTermMedicationDetails' => $longTermMedicationDetails, 'tetanusWithin10Years' => $tetanusWithin10Years, 'comment' => $comment);
                $sql = 'INSERT INTO pupilsightPersonMedical SET pupilsightPersonID=:pupilsightPersonID, bloodType=:bloodType, longTermMedication=:longTermMedication, longTermMedicationDetails=:longTermMedicationDetails, tetanusWithin10Years=:tetanusWithin10Years, comment=:comment';
                $result = $connection2->prepare($sql);
                $result->execute($data);
            } catch (PDOException $e) {
                $URL .= '&return=error2';
                header("Location: {$URL}");
                exit();
            }

            //Last insert ID
            $AI = str_pad($connection2->lastInsertID(), 10, '0', STR_PAD_LEFT);

            $URL .= "&return=success0&editID=$AI";
            header("Location: {$URL}");
        }
    }
}
