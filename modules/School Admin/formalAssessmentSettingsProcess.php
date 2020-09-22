<?php
/*
Pupilsight, Flexible & Open School System
*/

include '../../pupilsight.php';

$URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_POST['address']).'/formalAssessmentSettings.php';

if (isActionAccessible($guid, $connection2, '/modules/School Admin/formalAssessmentSettings.php') == false) {
    $URL .= '&return=error0';
    header("Location: {$URL}");
} else {
    $internalAssessmentTypes = '';
    foreach (explode(',', $_POST['internalAssessmentTypes']) as $type) {
        $internalAssessmentTypes .= trim($type).',';
    }
    $internalAssessmentTypes = substr($internalAssessmentTypes, 0, -1);

    $pupilsightExternalAssessmentID = (isset($_POST['pupilsightExternalAssessmentID']))? $_POST['pupilsightExternalAssessmentID'] : array();
    $primaryExternalAssessmentByYearGroup = (isset($_POST['category']))? $_POST['category'] : array();

    foreach ($pupilsightExternalAssessmentID as $year => $assessmentID) {
        if (!isset($primaryExternalAssessmentByYearGroup[$year])) {
            $primaryExternalAssessmentByYearGroup[$year] = null;
        }
    }

    //Validate Inputs
    if ($internalAssessmentTypes == '') {
        $URL .= '&return=error3';
        header("Location: {$URL}");
    } else {
        //Write to database
        $fail = false;

        //Update internal assessment fields
        try {
            $data = array('value' => $internalAssessmentTypes);
            $sql = "UPDATE pupilsightSetting SET value=:value WHERE scope='Formal Assessment' AND name='internalAssessmentTypes'";
            $result = $connection2->prepare($sql);
            $result->execute($data);
        } catch (PDOException $e) {
            $fail = true;
        }

        //Update external assessment fields
        try {
            $data = array('value' => serialize($primaryExternalAssessmentByYearGroup));
            $sql = "UPDATE pupilsightSetting SET value=:value WHERE scope='School Admin' AND name='primaryExternalAssessmentByYearGroup'";
            $result = $connection2->prepare($sql);
            $result->execute($data);
        } catch (PDOException $e) {
            $fail = true;
        }

        if ($fail == true) {
            $URL .= '&return=error2';
            header("Location: {$URL}");
        } else {
            getSystemSettings($guid, $connection2);
            $URL .= '&return=success0';
            header("Location: {$URL}");
        }
    }
}
