<?php
/*
Pupilsight, Flexible & Open School System
*/

include '../../pupilsight.php';

$URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_POST['address']).'/plannerSettings.php';

if (isActionAccessible($guid, $connection2, '/modules/School Admin/plannerSettings.php') == false) {
    $URL .= '&return=error0';
    header("Location: {$URL}");
} else {
    //Proceed!
    $lessonDetailsTemplate = $_POST['lessonDetailsTemplate'];
    $teachersNotesTemplate = $_POST['teachersNotesTemplate'];
    $unitOutlineTemplate = $_POST['unitOutlineTemplate'];
    $smartBlockTemplate = $_POST['smartBlockTemplate'];
    $makeUnitsPublic = $_POST['makeUnitsPublic'];
    $shareUnitOutline = $_POST['shareUnitOutline'];
    $allowOutcomeEditing = $_POST['allowOutcomeEditing'];
    $sharingDefaultParents = $_POST['sharingDefaultParents'];
    $sharingDefaultStudents = $_POST['sharingDefaultStudents'];
    $parentWeeklyEmailSummaryIncludeBehaviour = $_POST['parentWeeklyEmailSummaryIncludeBehaviour'];
    $parentWeeklyEmailSummaryIncludeMarkbook = $_POST['parentWeeklyEmailSummaryIncludeMarkbook'];

    //Write to database
    $fail = false;

    try {
        $data = array('value' => $lessonDetailsTemplate);
        $sql = "UPDATE pupilsightSetting SET value=:value WHERE scope='Planner' AND name='lessonDetailsTemplate'";
        $result = $connection2->prepare($sql);
        $result->execute($data);
    } catch (PDOException $e) {
        $fail = true;
    }

    try {
        $data = array('value' => $teachersNotesTemplate);
        $sql = "UPDATE pupilsightSetting SET value=:value WHERE scope='Planner' AND name='teachersNotesTemplate'";
        $result = $connection2->prepare($sql);
        $result->execute($data);
    } catch (PDOException $e) {
        $fail = true;
    }

    try {
        $data = array('value' => $unitOutlineTemplate);
        $sql = "UPDATE pupilsightSetting SET value=:value WHERE scope='Planner' AND name='unitOutlineTemplate'";
        $result = $connection2->prepare($sql);
        $result->execute($data);
    } catch (PDOException $e) {
        $fail = true;
    }

    try {
        $data = array('value' => $smartBlockTemplate);
        $sql = "UPDATE pupilsightSetting SET value=:value WHERE scope='Planner' AND name='smartBlockTemplate'";
        $result = $connection2->prepare($sql);
        $result->execute($data);
    } catch (PDOException $e) {
        $fail = true;
    }

    try {
        $data = array('value' => $makeUnitsPublic);
        $sql = "UPDATE pupilsightSetting SET value=:value WHERE scope='Planner' AND name='makeUnitsPublic'";
        $result = $connection2->prepare($sql);
        $result->execute($data);
    } catch (PDOException $e) {
        $fail = true;
    }

    try {
        $data = array('value' => $shareUnitOutline);
        $sql = "UPDATE pupilsightSetting SET value=:value WHERE scope='Planner' AND name='shareUnitOutline'";
        $result = $connection2->prepare($sql);
        $result->execute($data);
    } catch (PDOException $e) {
        $fail = true;
    }

    try {
        $data = array('value' => $allowOutcomeEditing);
        $sql = "UPDATE pupilsightSetting SET value=:value WHERE scope='Planner' AND name='allowOutcomeEditing'";
        $result = $connection2->prepare($sql);
        $result->execute($data);
    } catch (PDOException $e) {
        $fail = true;
    }

    try {
        $data = array('value' => $sharingDefaultParents);
        $sql = "UPDATE pupilsightSetting SET value=:value WHERE scope='Planner' AND name='sharingDefaultParents'";
        $result = $connection2->prepare($sql);
        $result->execute($data);
    } catch (PDOException $e) {
        $fail = true;
    }

    try {
        $data = array('value' => $sharingDefaultStudents);
        $sql = "UPDATE pupilsightSetting SET value=:value WHERE scope='Planner' AND name='sharingDefaultStudents'";
        $result = $connection2->prepare($sql);
        $result->execute($data);
    } catch (PDOException $e) {
        $fail = true;
    }

    try {
        $data = array('value' => $parentWeeklyEmailSummaryIncludeBehaviour);
        $sql = "UPDATE pupilsightSetting SET value=:value WHERE scope='Planner' AND name='parentWeeklyEmailSummaryIncludeBehaviour'";
        $result = $connection2->prepare($sql);
        $result->execute($data);
    } catch (PDOException $e) {
        $fail = true;
    }

    try {
        $data = array('value' => $parentWeeklyEmailSummaryIncludeMarkbook);
        $sql = "UPDATE pupilsightSetting SET value=:value WHERE scope='Planner' AND name='parentWeeklyEmailSummaryIncludeMarkbook'";
        $result = $connection2->prepare($sql);
        $result->execute($data);
    } catch (PDOException $e) {
        $fail = true;
    }

    if ($fail == true) {
        $URL .= '&return=error2';
        header("Location: {$URL}");
    } else {
        //Success 0
        getSystemSettings($guid, $connection2);
        $URL .= '&return=success0';
        header("Location: {$URL}");
    }
}
