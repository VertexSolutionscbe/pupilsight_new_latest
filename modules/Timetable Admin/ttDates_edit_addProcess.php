<?php
/*
Pupilsight, Flexible & Open School System
*/

include '../../pupilsight.php';

$pupilsightSchoolYearID = $_POST['pupilsightSchoolYearID'];
$dateStamp = $_POST['dateStamp'];
$pupilsightTTDayID = $_POST['pupilsightTTDayID'];

$URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_POST['address'])."/ttDates_edit_add.php&pupilsightSchoolYearID=$pupilsightSchoolYearID&dateStamp=".$dateStamp;

if (isActionAccessible($guid, $connection2, '/modules/Timetable Admin/ttDates_edit_add.php') == false) {
    $URL .= '&return=error0';
    header("Location: {$URL}");
} else {
    //Proceed!
    //Validate Inputs
    if ($pupilsightSchoolYearID == '' or $dateStamp == '' or $pupilsightTTDayID == '') {
        $URL .= '&return=error1';
        header("Location: {$URL}");
    } else {
        if (isSchoolOpen($guid, date('Y-m-d', $dateStamp), $connection2, true) == false) {
            $URL .= '&return=error1';
            header("Location: {$URL}");
        } else {
            try {
                $data = array('pupilsightSchoolYearID' => $pupilsightSchoolYearID);
                $sql = 'SELECT * FROM pupilsightSchoolYear WHERE pupilsightSchoolYearID=:pupilsightSchoolYearID';
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
                    $data = array('pupilsightTTDayID' => $pupilsightTTDayID, 'date' => date('Y-m-d', $dateStamp));
                    $sql = 'INSERT INTO pupilsightTTDayDate SET pupilsightTTDayID=:pupilsightTTDayID, date=:date';
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
