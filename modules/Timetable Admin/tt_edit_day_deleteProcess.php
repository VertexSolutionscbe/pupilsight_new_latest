<?php
/*
Pupilsight, Flexible & Open School System
*/

include '../../pupilsight.php';

$pupilsightTTDayID = $_GET['pupilsightTTDayID'];
$pupilsightTTID = $_GET['pupilsightTTID'];
$pupilsightSchoolYearID = $_GET['pupilsightSchoolYearID'];

if ($pupilsightTTID == '' or $pupilsightSchoolYearID == '') { echo 'Fatal error loading this page!';
} else {
    $URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_POST['address'])."/tt_edit_day_delete.php&pupilsightTTID=$pupilsightTTID&pupilsightTTDayID=$pupilsightTTDayID&pupilsightSchoolYearID=$pupilsightSchoolYearID";
    $URLDelete = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_POST['address'])."/tt_edit.php&pupilsightTTID=$pupilsightTTID&pupilsightTTDayID=$pupilsightTTDayID&pupilsightSchoolYearID=$pupilsightSchoolYearID";

    if (isActionAccessible($guid, $connection2, '/modules/Timetable Admin/tt_edit_day_delete.php') == false) {
        $URL .= '&return=error0';
        header("Location: {$URL}");
    } else {
        //Proceed!
        //Check if school year specified
        if ($pupilsightTTDayID == '') {
            $URL .= '&return=error1';
            header("Location: {$URL}");
        } else {
            try {
                $data = array('pupilsightTTDayID' => $pupilsightTTDayID);
                $sql = 'SELECT * FROM pupilsightTTDay WHERE pupilsightTTDayID=:pupilsightTTDayID';
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
                    $data = array('pupilsightTTDayID' => $pupilsightTTDayID);
                    $sql = 'DELETE FROM pupilsightTTDay WHERE pupilsightTTDayID=:pupilsightTTDayID';
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
