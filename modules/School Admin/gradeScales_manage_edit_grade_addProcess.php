<?php
/*
Pupilsight, Flexible & Open School System
*/

include '../../pupilsight.php';

$value = $_POST['value'];
$descriptor = $_POST['descriptor'];
$sequenceNumber = $_POST['sequenceNumber'];
$isDefault = $_POST['isDefault'];

$pupilsightScaleID = $_POST['pupilsightScaleID'];

$URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_POST['address'])."/gradeScales_manage_edit_grade_add.php&pupilsightScaleID=$pupilsightScaleID";

if (isActionAccessible($guid, $connection2, '/modules/School Admin/gradeScales_manage_edit_grade_add.php') == false) {
    $URL .= '&return=error0';
    header("Location: {$URL}");
} else {
    //Proceed!
    //Validate Inputs
    if ($pupilsightScaleID == '' or $value == '' or $descriptor == '' or $sequenceNumber == '' or $isDefault == '') {
        $URL .= '&return=error1';
        header("Location: {$URL}");
    } else {
        //Check unique inputs for uniquness
        try {
            $data = array('value' => $value, 'sequenceNumber' => $sequenceNumber, 'pupilsightScaleID' => $pupilsightScaleID);
            $sql = 'SELECT * FROM pupilsightScaleGrade WHERE ((value=:value) OR (sequenceNumber=:sequenceNumber)) AND pupilsightScaleID=:pupilsightScaleID';
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
            //If isDefault is Y, then set all other grades in scale to N
            if ($isDefault == 'Y') {
                try {
                    $data = array('pupilsightScaleID' => $pupilsightScaleID);
                    $sql = "UPDATE pupilsightScaleGrade SET isDefault='N' WHERE pupilsightScaleID=:pupilsightScaleID";
                    $result = $connection2->prepare($sql);
                    $result->execute($data);
                } catch (PDOException $e) {
                    $URL .= '&return=error2';
                    header("Location: {$URL}");
                    exit();
                }
            }

            //Write to database
            try {
                $data = array('pupilsightScaleID' => $pupilsightScaleID, 'value' => $value, 'descriptor' => $descriptor, 'sequenceNumber' => $sequenceNumber, 'isDefault' => $isDefault);
                $sql = 'INSERT INTO pupilsightScaleGrade SET pupilsightScaleID=:pupilsightScaleID, value=:value, descriptor=:descriptor, sequenceNumber=:sequenceNumber, isDefault=:isDefault';
                $result = $connection2->prepare($sql);
                $result->execute($data);
            } catch (PDOException $e) {
                $URL .= '&return=error2';
                header("Location: {$URL}");
                exit();
            }

            //Last insert ID
            $AI = str_pad($connection2->lastInsertID(), 7, '0', STR_PAD_LEFT);

            $URL .= "&return=success0&editID=$AI";
            header("Location: {$URL}");
        }
    }
}
