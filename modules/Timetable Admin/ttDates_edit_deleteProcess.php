<?php
/*
Pupilsight, Flexible & Open School System
*/

include '../../pupilsight.php';

$pupilsightSchoolYearID = $_GET['pupilsightSchoolYearID'];
$dateStamp = $_GET['dateStamp'];
$pupilsightTTDayID = $_GET['pupilsightTTDayID'];

if ($pupilsightSchoolYearID == '' or $dateStamp == '') { echo 'Fatal error loading this page!';
} else {
    $URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_POST['address'])."/ttDates_edit.php&pupilsightSchoolYearID=$pupilsightSchoolYearID&dateStamp=$dateStamp";

    if (isActionAccessible($guid, $connection2, '/modules/Timetable Admin/ttDates_edit_delete.php') == false) {
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
                $data = array('date' => date('Y-m-d', $dateStamp), 'pupilsightTTDayID' => $pupilsightTTDayID);
                $sql = 'SELECT * FROM pupilsightTTDayDate WHERE pupilsightTTDayID=:pupilsightTTDayID AND date=:date';
                $result = $connection2->prepare($sql);
                $result->execute($data);
            } catch (PDOException $e) {
                $URL .= '&return=error2';
                header("Location: {$URL}");
                exit();
            }

            if ($result->rowCount() < 1) {
                $URL .= '&return=error2';
                header("Location: {$URL}");
            } else {
                //Write to database
                try {
                    $data = array('date' => date('Y-m-d', $dateStamp), 'pupilsightTTDayID' => $pupilsightTTDayID);
                    $sql = 'DELETE FROM pupilsightTTDayDate WHERE pupilsightTTDayID=:pupilsightTTDayID AND date=:date';
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
