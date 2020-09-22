<?php
/*
Pupilsight, Flexible & Open School System
*/

include '../../pupilsight.php';

$pupilsightExternalAssessmentFieldID = $_GET['pupilsightExternalAssessmentFieldID'];
$pupilsightExternalAssessmentID = $_GET['pupilsightExternalAssessmentID'];

if ($pupilsightExternalAssessmentID == '') { echo 'Fatal error loading this page!';
} else {
    $URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_POST['address'])."/externalAssessments_manage_edit_field_delete.php&pupilsightExternalAssessmentID=$pupilsightExternalAssessmentID&pupilsightExternalAssessmentFieldID=$pupilsightExternalAssessmentFieldID";
    $URLDelete = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_POST['address'])."/externalAssessments_manage_edit.php&pupilsightExternalAssessmentID=$pupilsightExternalAssessmentID&pupilsightExternalAssessmentFieldID=$pupilsightExternalAssessmentFieldID";

    if (isActionAccessible($guid, $connection2, '/modules/School Admin/externalAssessments_manage_edit_field_delete.php') == false) {
        $URL .= '&return=error0';
        header("Location: {$URL}");
    } else {
        //Proceed!
        //Check if school year specified
        if ($pupilsightExternalAssessmentFieldID == '') {
            $URL .= '&return=error1';
            header("Location: {$URL}");
        } else {
            try {
                $data = array('pupilsightExternalAssessmentFieldID' => $pupilsightExternalAssessmentFieldID);
                $sql = 'SELECT * FROM pupilsightExternalAssessmentField WHERE pupilsightExternalAssessmentFieldID=:pupilsightExternalAssessmentFieldID';
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
                    $data = array('pupilsightExternalAssessmentFieldID' => $pupilsightExternalAssessmentFieldID);
                    $sql = 'DELETE FROM pupilsightExternalAssessmentField WHERE pupilsightExternalAssessmentFieldID=:pupilsightExternalAssessmentFieldID';
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
