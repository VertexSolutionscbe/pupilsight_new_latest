<?php
/*
Pupilsight, Flexible & Open School System
*/

include '../../pupilsight.php';

//Module includes
include './moduleFunctions.php';

$URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_POST['address']).'/spaceBooking_manage_add.php';

if (isActionAccessible($guid, $connection2, '/modules/Timetable/spaceBooking_manage_add.php') == false) {
    $URL .= '&return=error0';
    header("Location: {$URL}");
} else {
    //Get action with highest precendence
    $highestAction = getHighestGroupedAction($guid, $_POST['address'], $connection2);
    if ($highestAction == false) {
        $URL .= "&return=error0$params";
        header("Location: {$URL}");
    } else {
        //Proceed!
        $foreignKey = $_POST['foreignKey'];
        $foreignKeyID = $_POST['foreignKeyID'];
        $dates = $_POST['dates'];
        $timeStart = $_POST['timeStart'];
        $timeEnd = $_POST['timeEnd'];
        $repeat = $_POST['repeat'];
        $repeatDaily = null;
        $repeatWeekly = null;
        if ($repeat == 'Daily') {
            $repeatDaily = $_POST['repeatDaily'];
        } elseif ($repeat == 'Weekly') {
            $repeatWeekly = $_POST['repeatWeekly'];
        }

        //Validate Inputs
        if ($foreignKey == '' or $foreignKeyID == '' or $timeStart == '' or $timeEnd == '' or $repeat == '' or count($dates) < 1) {
            $URL .= '&return=error1';
            header("Location: {$URL}");
        } else {
            //Lock tables
            try {
                $sql = 'LOCK TABLE pupilsightDaysOfWeek WRITE, pupilsightSchoolYear WRITE, pupilsightSchoolYearSpecialDay WRITE, pupilsightSchoolYearTerm WRITE, pupilsightTTColumnRow WRITE, pupilsightTTDay WRITE, pupilsightTTDayDate WRITE, pupilsightTTDayRowClass WRITE, pupilsightTTSpaceBooking WRITE, pupilsightTTSpaceChange WRITE';
                $result = $connection2->query($sql);
            } catch (PDOException $e) {
                $URL .= '&return=error2';
                header("Location: {$URL}");
                exit();
            }

            $failCount = 0;
            $available = '';
            //Scroll through all dates
            foreach ($dates as $date) {
                $available = isSpaceFree($guid, $connection2, $foreignKey, $foreignKeyID, $date, $timeStart, $timeEnd);
                if ($available == false) {
                    ++$failCount;
                } else {
                    //Write to database
                    try {
                        $data = array('foreignKey' => $foreignKey, 'foreignKeyID' => $foreignKeyID, 'date' => $date, 'timeStart' => $timeStart, 'timeEnd' => $timeEnd, 'pupilsightPersonID' => $_SESSION[$guid]['pupilsightPersonID']);
                        $sql = 'INSERT INTO pupilsightTTSpaceBooking SET foreignKey=:foreignKey, foreignKeyID=:foreignKeyID, date=:date, timeStart=:timeStart, timeEnd=:timeEnd, pupilsightPersonID=:pupilsightPersonID';
                        $result = $connection2->prepare($sql);
                        $result->execute($data);
                    } catch (PDOException $e) {
                        ++$failCount;
                    }
                }
            }

            $successCount = count($dates) - $failCount;

            //Unlock locked database tables
            try {
                $sql = 'UNLOCK TABLES';
                $result = $connection2->query($sql);
            } catch (PDOException $e) {
            }

            if ($successCount == 0) {
                $URL .= '&return=error3';
                header("Location: {$URL}");
            } elseif ($successCount < count($dates)) {
                $URL .= '&return=warning1';
                header("Location: {$URL}");
            } else {
                // Redirect back to View Timetable by Facility if we started there
                if (isset($_POST['source']) && $_POST['source'] == 'tt') {
                    $ttDate = dateConvertBack($guid, $dates[0]);
                    $URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/Timetable/tt_space_view.php&pupilsightSpaceID='.$foreignKeyID.'&ttDate='.$ttDate;
                }

                $URL .= '&return=success0';
                header("Location: {$URL}");
            }
        }
    }
}
