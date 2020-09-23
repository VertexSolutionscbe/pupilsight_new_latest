<?php
/*
Pupilsight, Flexible & Open School System
*/

include '../../pupilsight.php';

$name = $_POST['name'];
$nameShort = $_POST['nameShort'];
$pupilsightExternalAssessmentID = $_POST['pupilsightExternalAssessmentID'];
$description = $_POST['description'];
$active = $_POST['active'];
$allowFileUpload = $_POST['allowFileUpload'];

$URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_POST['address']).'/externalAssessments_manage_edit.php&pupilsightExternalAssessmentID='.$pupilsightExternalAssessmentID;

if (isActionAccessible($guid, $connection2, '/modules/School Admin/externalAssessments_manage_edit.php') == false) {
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
            //Validate Inputs
            if ($name == '' or $nameShort == '' or $description == '' or $active == '') {
                $URL .= '&return=error3';
                header("Location: {$URL}");
            } else {
                //Check unique inputs for uniquness
                try {
                    $data = array('name' => $name, 'nameShort' => $nameShort, 'pupilsightExternalAssessmentID' => $pupilsightExternalAssessmentID);
                    $sql = 'SELECT * FROM pupilsightExternalAssessment WHERE (name=:name OR nameShort=:nameShort) AND NOT pupilsightExternalAssessmentID=:pupilsightExternalAssessmentID';
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
                        $data = array('name' => $name, 'nameShort' => $nameShort, 'description' => $description, 'active' => $active, 'allowFileUpload' => $allowFileUpload, 'pupilsightExternalAssessmentID' => $pupilsightExternalAssessmentID);
                        $sql = 'UPDATE pupilsightExternalAssessment SET name=:name, nameShort=:nameShort, `description`=:description, active=:active, allowFileUpload=:allowFileUpload WHERE pupilsightExternalAssessmentID=:pupilsightExternalAssessmentID';
                        $result = $connection2->prepare($sql);
                        $result->execute($data);
                    } catch (PDOException $e) {
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
