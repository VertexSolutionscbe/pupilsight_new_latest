<?php
/*
Pupilsight, Flexible & Open School System
*/

include '../../pupilsight.php';

$pupilsightCourseClassID = $_POST['pupilsightCourseClassID'];
$pupilsightInternalAssessmentColumnID = $_GET['pupilsightInternalAssessmentColumnID'];
$URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_POST['address'])."/internalAssessment_manage_delete.php&pupilsightInternalAssessmentColumnID=$pupilsightInternalAssessmentColumnID&pupilsightCourseClassID=$pupilsightCourseClassID";
$URLDelete = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_POST['address'])."/internalAssessment_manage.php&pupilsightCourseClassID=$pupilsightCourseClassID";

if (isActionAccessible($guid, $connection2, '/modules/Formal Assessment/internalAssessment_manage_delete.php') == false) {
    $URL .= '&return=error0';
    header("Location: {$URL}");
} else {
    //Proceed!
    //Check if school year specified
    if ($pupilsightInternalAssessmentColumnID == '' or $pupilsightCourseClassID == '') {
        $URL .= '&return=error1';
        header("Location: {$URL}");
    } else {
        try {
            $data = array('pupilsightInternalAssessmentColumnID' => $pupilsightInternalAssessmentColumnID, 'pupilsightCourseClassID' => $pupilsightCourseClassID);
            $sql = 'SELECT * FROM pupilsightInternalAssessmentColumn WHERE pupilsightInternalAssessmentColumnID=:pupilsightInternalAssessmentColumnID AND pupilsightCourseClassID=:pupilsightCourseClassID';
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
                $data = array('pupilsightInternalAssessmentColumnID' => $pupilsightInternalAssessmentColumnID);
                $sql = 'DELETE FROM pupilsightInternalAssessmentColumn WHERE pupilsightInternalAssessmentColumnID=:pupilsightInternalAssessmentColumnID';
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
