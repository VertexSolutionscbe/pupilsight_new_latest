<?php
/*
Pupilsight, Flexible & Open School System
*/

include '../../pupilsight.php';

$pupilsightExternalAssessmentStudentID = $_GET['pupilsightExternalAssessmentStudentID'];
$pupilsightPersonID = $_GET['pupilsightPersonID'];
$search = $_GET['search'];
$allStudents = '';
if (isset($_GET['allStudents'])) {
    $allStudents = $_GET['allStudents'];
}

if ($pupilsightPersonID == '' or $pupilsightExternalAssessmentStudentID == '') { echo 'Fatal error loading this page!';
} else {
    $URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_POST['address'])."/externalAssessment_manage_details_delete.php&pupilsightPersonID=$pupilsightPersonID&pupilsightExternalAssessmentStudentID=$pupilsightExternalAssessmentStudentID&search=$search&allStudents=$allStudents";
    $URLDelete = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_POST['address'])."/externalAssessment_details.php&pupilsightPersonID=$pupilsightPersonID&search=$search&allStudents=$allStudents";

    if (isActionAccessible($guid, $connection2, '/modules/Formal Assessment/externalAssessment_manage_details_delete.php') == false) {
        $URL .= '&return=error0';
        header("Location: {$URL}");
    } else {
        //Proceed!
        //Check if school year specified
        if ($pupilsightExternalAssessmentStudentID == '') {
            $URL .= '&return=error1';
            header("Location: {$URL}");
        } else {
            try {
                $data = array('pupilsightExternalAssessmentStudentID' => $pupilsightExternalAssessmentStudentID);
                $sql = 'SELECT * FROM pupilsightExternalAssessmentStudent WHERE pupilsightExternalAssessmentStudentID=:pupilsightExternalAssessmentStudentID';
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
                //Delete fields
                try {
                    $data = array('pupilsightExternalAssessmentStudentID' => $pupilsightExternalAssessmentStudentID);
                    $sql = 'DELETE FROM pupilsightExternalAssessmentStudentEntry WHERE pupilsightExternalAssessmentStudentID=:pupilsightExternalAssessmentStudentID';
                    $result = $connection2->prepare($sql);
                    $result->execute($data);
                } catch (PDOException $e) {
                    $URL .= '&return=error2';
                    header("Location: {$URL}");
                    exit();
                }

                //Delete assessment
                try {
                    $data = array('pupilsightExternalAssessmentStudentID' => $pupilsightExternalAssessmentStudentID);
                    $sql = 'DELETE FROM pupilsightExternalAssessmentStudent WHERE pupilsightExternalAssessmentStudentID=:pupilsightExternalAssessmentStudentID';
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
