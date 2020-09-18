<?php
/*
Pupilsight, Flexible & Open School System
*/

//Pupilsight system-wide includes
include '../../pupilsight.php';

//Module includes
include './moduleFunctions.php';

$pupilsightCourseClassID = $_GET['pupilsightCourseClassID'];
$pupilsightInternalAssessmentColumnID = $_GET['pupilsightInternalAssessmentColumnID'];
$pupilsightPersonID = $_GET['pupilsightPersonID'];
$URL = $_SESSION[$guid]['absoluteURL']."/index.php?q=/modules/Formal Assessment/internalAssessment_write_data.php&pupilsightCourseClassID=$pupilsightCourseClassID&pupilsightInternalAssessmentColumnID=$pupilsightInternalAssessmentColumnID";

if (isActionAccessible($guid, $connection2, '/modules/Formal Assessment/internalAssessment_write_data.php') == false) {
    $URL .= '&return=error0';
    header("Location: {$URL}");
} else {
    //Proceed!
    //Check if planner specified
    if ($pupilsightPersonID == '' or $pupilsightCourseClassID == '' or $pupilsightInternalAssessmentColumnID == '') {
        $URL .= '&return=error1';
        header("Location: {$URL}");
    } else {
        try {
            $data = array('pupilsightPersonID' => $pupilsightPersonID, 'pupilsightInternalAssessmentColumnID' => $pupilsightInternalAssessmentColumnID);
            $sql = "UPDATE pupilsightInternalAssessmentEntry SET response='' WHERE pupilsightPersonIDStudent=:pupilsightPersonID AND pupilsightInternalAssessmentColumnID=:pupilsightInternalAssessmentColumnID";
            $result = $connection2->prepare($sql);
            $result->execute($data);
        } catch (PDOException $e) {
            $URL .= '&return=error2';
            header("Location: {$URL}");
            exit();
        }

        $URL .= '&return=success0';
        //Success 0
        header("Location: {$URL}");
    }
}
