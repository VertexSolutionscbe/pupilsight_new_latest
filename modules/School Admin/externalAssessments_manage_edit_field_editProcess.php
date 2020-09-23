<?php
/*
Pupilsight, Flexible & Open School System
*/

include '../../pupilsight.php';

$pupilsightExternalAssessmentFieldID = $_GET['pupilsightExternalAssessmentFieldID'];
$pupilsightExternalAssessmentID = $_GET['pupilsightExternalAssessmentID'];
if ($pupilsightExternalAssessmentID == '') { echo 'Fatal error loading this page!';
} else {
    $URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_POST['address'])."/externalAssessments_manage_edit_field_edit.php&pupilsightExternalAssessmentID=$pupilsightExternalAssessmentID&pupilsightExternalAssessmentFieldID=$pupilsightExternalAssessmentFieldID";

    if (isActionAccessible($guid, $connection2, '/modules/School Admin/externalAssessments_manage_edit_field_edit.php') == false) {
        $URL .= '&return=error0';
        header("Location: {$URL}");
    } else {
        //Proceed!
        //Check if tt specified
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
                    $URL .= '&return=error3';
                    header("Location: {$URL}");
                } else {
                    //Write to database
                    try {
                        $data = array('name' => $name, 'category' => $category, 'order' => $order, 'pupilsightScaleID' => $pupilsightScaleID, 'pupilsightYearGroupIDList' => $pupilsightYearGroupIDList, 'pupilsightExternalAssessmentFieldID' => $pupilsightExternalAssessmentFieldID);
                        $sql = 'UPDATE pupilsightExternalAssessmentField SET name=:name, category=:category, `order`=:order, pupilsightScaleID=:pupilsightScaleID, pupilsightYearGroupIDList=:pupilsightYearGroupIDList WHERE pupilsightExternalAssessmentFieldID=:pupilsightExternalAssessmentFieldID';
                        $result = $connection2->prepare($sql);
                        $result->execute($data);
                    } catch (PDOException $e) {
                        echo 'Here';
                        $URL .= '&return=error2';
                        header("Location: {$URL}");
                        exit();
                    }

                    $URL .= '&return=success0';
                    header("Location: {$URL}");
                }
            }
        }
    }
}
