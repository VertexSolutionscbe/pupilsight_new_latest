<?php
/*
Pupilsight, Flexible & Open School System
*/
include '../../pupilsight.php';

$pupilsightSchoolYearID = $_GET['pupilsightSchoolYearID'];
$dates = 0;
if (isset($_POST['dates'])) {
    $dates = $_POST['dates'];
}
$pupilsightTTDayID_date = $_POST['pupilsightTTDayID'];
$URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_POST['q'])."/ttDates.php&pupilsightSchoolYearID=$pupilsightSchoolYearID";

if (isActionAccessible($guid, $connection2, '/modules/Timetable Admin/ttDates_edit_add.php') == false) {
    $URL .= '&return=error0';
    header("Location: {$URL}");
} else {
    //Proceed!
    //Validate Inputs
    if ($pupilsightSchoolYearID == '' or $dates == '' or count($dates) < 1 or count($pupilsightTTDayID_date) < 1 ) {
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
                $partialFail = false;
                foreach ($pupilsightTTDayID_date as $pupilsightTTDayID) {

                foreach ($dates as $date) {
                    if (isSchoolOpen($guid, date('Y-m-d', $date), $connection2, true) == false) {
                    $partialFail = true;
                    } else {
                    //Check if a day from the TT is already set. Not enough time to add this now, but should do this one day

                    //Write to database
                    try {
                    $data = array('pupilsightTTDayID' => $pupilsightTTDayID, 'date' => date('Y-m-d', $date));
                    $sql = 'INSERT INTO pupilsightTTDayDate SET pupilsightTTDayID=:pupilsightTTDayID, date=:date';
                    $result = $connection2->prepare($sql);
                    $result->execute($data);
                    } catch (PDOException $e) {
                    $partialFail = true;
                    }
                    }
                    }//data ends 
                }//days ends 

                //Report result
                if ($partialFail == true) {
                $URL .= '&return=warning1';
                header("Location: {$URL}");
                } else {
                $URL .= '&return=success0';
                header("Location: {$URL}");
                }
        } //ends here final
    }
}
