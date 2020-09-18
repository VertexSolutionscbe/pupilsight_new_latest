<?php
/*
Pupilsight, Flexible & Open School System
*/

include '../../pupilsight.php';

$pupilsightScaleGradeID = $_GET['pupilsightScaleGradeID'];
$pupilsightScaleID = $_GET['pupilsightScaleID'];

if ($pupilsightScaleID == '') { echo 'Fatal error loading this page!';
} else {
    $URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_POST['address'])."/gradeScales_manage_edit_grade_edit.php&pupilsightScaleID=$pupilsightScaleID&pupilsightScaleGradeID=$pupilsightScaleGradeID";

    if (isActionAccessible($guid, $connection2, '/modules/School Admin/gradeScales_manage_edit_grade_edit.php') == false) {
        $URL .= '&return=error0';
        header("Location: {$URL}");
    } else {
        //Proceed!
        //Check if tt specified
        if ($pupilsightScaleGradeID == '') {
            $URL .= '&return=error1';
            header("Location: {$URL}");
        } else {
            try {
                $data = array('pupilsightScaleGradeID' => $pupilsightScaleGradeID);
                $sql = 'SELECT * FROM pupilsightScaleGrade WHERE pupilsightScaleGradeID=:pupilsightScaleGradeID';
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
                $value = $_POST['value'];
                $descriptor = $_POST['descriptor'];
                $sequenceNumber = $_POST['sequenceNumber'];
                $isDefault = $_POST['isDefault'];

                if ($value == '' or $descriptor == '' or $sequenceNumber == '' or $isDefault == '') {
                    $URL .= '&return=error3';
                    header("Location: {$URL}");
                } else {
                    //Check unique inputs for uniquness
                    try {
                        $data = array('value' => $value, 'sequenceNumber' => $sequenceNumber, 'pupilsightScaleID' => $pupilsightScaleID, 'pupilsightScaleGradeID' => $pupilsightScaleGradeID);
                        $sql = 'SELECT * FROM pupilsightScaleGrade WHERE (value=:value OR sequenceNumber=:sequenceNumber) AND pupilsightScaleID=:pupilsightScaleID AND NOT pupilsightScaleGradeID=:pupilsightScaleGradeID';
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
                                $data = array('pupilsightScaleID' => $pupilsightScaleID, 'pupilsightScaleGradeID' => $pupilsightScaleGradeID);
                                $sql = "UPDATE pupilsightScaleGrade SET isDefault='N' WHERE pupilsightScaleID=:pupilsightScaleID AND NOT pupilsightScaleGradeID=:pupilsightScaleGradeID";
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
                            $data = array('value' => $value, 'descriptor' => $descriptor, 'sequenceNumber' => $sequenceNumber, 'isDefault' => $isDefault, 'pupilsightScaleGradeID' => $pupilsightScaleGradeID);
                            $sql = 'UPDATE pupilsightScaleGrade SET value=:value, descriptor=:descriptor, sequenceNumber=:sequenceNumber, isDefault=:isDefault WHERE pupilsightScaleGradeID=:pupilsightScaleGradeID';
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
}
