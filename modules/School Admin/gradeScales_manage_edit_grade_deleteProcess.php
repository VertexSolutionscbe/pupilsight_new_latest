<?php
/*
Pupilsight, Flexible & Open School System
*/

include '../../pupilsight.php';

$pupilsightScaleGradeID = $_GET['pupilsightScaleGradeID'];
$pupilsightScaleID = $_GET['pupilsightScaleID'];

if ($pupilsightScaleID == '') { echo 'Fatal error loading this page!';
} else {
    $URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_POST['address'])."/gradeScales_manage_edit_grade_delete.php&pupilsightScaleID=$pupilsightScaleID&pupilsightScaleGradeID=$pupilsightScaleGradeID";
    $URLDelete = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_POST['address'])."/gradeScales_manage_edit.php&pupilsightScaleID=$pupilsightScaleID&pupilsightScaleGradeID=$pupilsightScaleGradeID";

    if (isActionAccessible($guid, $connection2, '/modules/School Admin/gradeScales_manage_edit_grade_delete.php') == false) {
        $URL .= '&return=error0';
        header("Location: {$URL}");
    } else {
        //Proceed!
        //Check if school year specified
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
                //Write to database
                try {
                    $data = array('pupilsightScaleGradeID' => $pupilsightScaleGradeID);
                    $sql = 'DELETE FROM pupilsightScaleGrade WHERE pupilsightScaleGradeID=:pupilsightScaleGradeID';
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
