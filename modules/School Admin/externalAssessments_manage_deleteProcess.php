<?php
/*
Pupilsight, Flexible & Open School System
*/

include '../../pupilsight.php';

$pupilsightExternalAssessmentID = $_GET['pupilsightExternalAssessmentID'];
$URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_POST['address']).'/externalAssessments_manage_delete.php&pupilsightExternalAssessmentID='.$pupilsightExternalAssessmentID;
$URLDelete = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_POST['address']).'/externalAssessments_manage.php';

if (isActionAccessible($guid, $connection2, '/modules/School Admin/externalAssessments_manage_delete.php') == false) {
    $URL .= '&return=error0';
    header("Location: {$URL}");
} else {
    //Proceed!
    if ($pupilsightExternalAssessmentID == '') {
        $URL .= '&return=error1';
        header("Location: {$URL}");
    } else {
        try {
            $data = array('pupilsightExternalAssessmentID' => $pupilsightExternalAssessmentID);
            $sql = 'SELECT * FROM pupilsightExternalAssessment WHERE pupilsightExternalAssessmentID=:pupilsightExternalAssessmentID';
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
            //Try to delete fields
            try {
                $data = array('pupilsightExternalAssessmentID' => $pupilsightExternalAssessmentID);
                $sql = 'DELETE FROM pupilsightExternalAssessmentField WHERE pupilsightExternalAssessmentID=:pupilsightExternalAssessmentID';
                $result = $connection2->prepare($sql);
                $result->execute($data);
            } catch (PDOException $e) {
            }

            //Delete assessment
            try {
                $data = array('pupilsightExternalAssessmentID' => $pupilsightExternalAssessmentID);
                $sql = 'DELETE FROM pupilsightExternalAssessment WHERE pupilsightExternalAssessmentID=:pupilsightExternalAssessmentID';
                $result = $connection2->prepare($sql);
                $result->execute($data);
            } catch (PDOException $e) {
                $URL .= '&return=error2';
                header("Location: {$URL}");
            }

            $URLDelete = $URLDelete.'&return=success0';
            header("Location: {$URLDelete}");
        }
    }
}
