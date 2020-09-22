<?php
/*
Pupilsight, Flexible & Open School System
*/

include '../../pupilsight.php';

$pupilsightExternalAssessmentID = $_POST['pupilsightExternalAssessmentID'];
$URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_POST['address'])."/externalAssessments_manage_edit_field_add.php&pupilsightExternalAssessmentID=$pupilsightExternalAssessmentID";

if (isActionAccessible($guid, $connection2, '/modules/School Admin/externalAssessments_manage_edit_field_add.php') == false) {
    $URL .= '&return=error0';
    header("Location: {$URL}");
} else {
    //Proceed!
    //Validate Inputs
    $name = $_POST['name'];
    $category = $_POST['category'];
    $order = $_POST['order'];
    $pupilsightScaleID = $_POST['pupilsightScaleID'];
    $pupilsightYearGroupIDList = '';
    if (!empty($_POST['pupilsightYearGroupIDList']) && is_array($_POST['pupilsightYearGroupIDList'])) {
        $pupilsightYearGroupIDList = implode(',', $_POST['pupilsightYearGroupIDList']);
    }

    if ($pupilsightExternalAssessmentID == '' or $name == '' or $category == '' or $order == '' or $pupilsightScaleID == '') {
        $URL .= '&return=error1';
        header("Location: {$URL}");
    } else {
        //Write to database
        try {
            $data = array('pupilsightExternalAssessmentID' => $pupilsightExternalAssessmentID, 'name' => $name, 'category' => $category, 'order' => $order, 'pupilsightScaleID' => $pupilsightScaleID, 'pupilsightYearGroupIDList' => $pupilsightYearGroupIDList);
            $sql = 'INSERT INTO pupilsightExternalAssessmentField SET pupilsightExternalAssessmentID=:pupilsightExternalAssessmentID, name=:name, category=:category, `order`=:order, pupilsightScaleID=:pupilsightScaleID, pupilsightYearGroupIDList=:pupilsightYearGroupIDList';
            $result = $connection2->prepare($sql);
            $result->execute($data);
        } catch (PDOException $e) {
            $URL .= '&return=error2';
            header("Location: {$URL}");
            exit();
        }

        //Last insert ID
        $AI = str_pad($connection2->lastInsertID(), 6, '0', STR_PAD_LEFT);

        //Success 0
        $URL .= "&return=success0&editID=$AI";
        header("Location: {$URL}");
    }
}
