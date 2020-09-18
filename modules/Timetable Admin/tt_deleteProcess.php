<?php
/*
Pupilsight, Flexible & Open School System
*/

include '../../pupilsight.php';

$pupilsightTTID = $_GET['pupilsightTTID'];
$URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_POST['address']).'/tt_delete.php&pupilsightTTID='.$pupilsightTTID.'&pupilsightSchoolYearID='.$_GET['pupilsightSchoolYearID'];
$URLDelete = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_POST['address']).'/tt.php&pupilsightSchoolYearID='.$_GET['pupilsightSchoolYearID'];

if (isActionAccessible($guid, $connection2, '/modules/Timetable Admin/tt_delete.php') == false) {
    $URL .= '&return=error0';
    header("Location: {$URL}");
} else {
    //Proceed!
    //Check if school year specified
    if ($pupilsightTTID == '') {
        $URL .= '&return=error1';
        header("Location: {$URL}");
    } else {
        try {
            $data = array('pupilsightTTID' => $pupilsightTTID);
            $sql = 'SELECT * FROM pupilsightTT WHERE pupilsightTTID=:pupilsightTTID';
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
            //Delete Course
            try {
                $data = array('pupilsightTTID' => $pupilsightTTID);
                $sql = 'DELETE FROM pupilsightTT WHERE pupilsightTTID=:pupilsightTTID';
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
