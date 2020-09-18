<?php
/*
Pupilsight, Flexible & Open School System
*/

function num2alpha($n)
{
    for ($r = ''; $n >= 0; $n = intval($n / 26) - 1) {
        $r = chr($n % 26 + 0x41).$r;
    }

    return $r;
}

function getActivityWeekDays($connection2, $pupilsightActivityID)
{

    // Get the time slots for this activity to determine weekdays
    try {
        $data = array('pupilsightActivityID' => $pupilsightActivityID);
        $sql = 'SELECT nameShort FROM pupilsightActivitySlot JOIN pupilsightDaysOfWeek ON (pupilsightActivitySlot.pupilsightDaysOfWeekID=pupilsightDaysOfWeek.pupilsightDaysOfWeekID) WHERE pupilsightActivityID=:pupilsightActivityID ORDER BY pupilsightDaysOfWeek.pupilsightDaysOfWeekID';
        $result = $connection2->prepare($sql);
        $result->execute($data);
    } catch (PDOException $e) {
        echo "<div class='alert alert-danger'>".$e->getMessage().'</div>';
    }

    return $result->fetchAll(PDO::FETCH_COLUMN);
}

function getActivitySessions($weekDays, $timespan, $sessionAttendanceData)
{
    $activitySlots = array();

    if (count($timespan) > 0) {
        // Iterate one day at a time from start to end, adding the weekdays that match a time slot
        for ($time = $timespan['start']; $time <= $timespan['end']; $time += 86400) {
            $day = date('Y-m-d', $time);
            if (isset($sessionAttendanceData[ $day ])) {
                $activitySlots[$day] = $time;
            } elseif (in_array(date('D', $time), $weekDays)) {
                $activitySlots[$day] = $time;
            }
        }

        foreach ($sessionAttendanceData as $sessionDate => $sessionData) {
            $activitySlots[$sessionDate] = strtotime($sessionDate);
        }

        ksort($activitySlots);
    }

    return $activitySlots;
}

function getActivityTimespan($connection2, $pupilsightActivityID, $pupilsightSchoolYearTermIDList)
{
    $timespan = array();

    // Figure out what kind of dateType we're using
    $dateType = getSettingByScope($connection2, 'Activities', 'dateType');
    if ($dateType != 'Date') {
        if (empty($pupilsightSchoolYearTermIDList)) {
            return array();
        }

        try {
            $data = array();
            $sql = 'SELECT MIN(UNIX_TIMESTAMP(firstDay)) as start, MAX(UNIX_TIMESTAMP(lastDay)) as end FROM pupilsightSchoolYearTerm WHERE pupilsightSchoolYearTermID IN ('.$pupilsightSchoolYearTermIDList.')';
            $result = $connection2->prepare($sql);
            $result->execute();
        } catch (PDOException $e) {
            echo "<div class='alert alert-danger'>".$e->getMessage().'</div>';
        }
        $timespan = $result->fetch();
    } else {
        try {
            $data = array('pupilsightActivityID' => $pupilsightActivityID);
            $sql = 'SELECT UNIX_TIMESTAMP(programStart) as start, UNIX_TIMESTAMP(programEnd) as end FROM pupilsightActivity WHERE pupilsightActivityID=:pupilsightActivityID';
            $result = $connection2->prepare($sql);
            $result->execute($data);
        } catch (PDOException $e) {
            echo "<div class='alert alert-danger'>".$e->getMessage().'</div>';
        }
        $timespan = $result->fetch();
    }

    return $timespan;
}

function formatDateRange($start, $end)
{
    $output = '';
    if (empty($start) || empty($end)) return $output;

    $startDate = ($start instanceof DateTime)? $start : new DateTime($start);
    $endDate = ($end instanceof DateTime)? $end : new DateTime($end);

    if ($startDate->format('Y-m') == $endDate->format('Y-m')) {
        $output = $startDate->format('M Y');
    } else if ($startDate->format('Y') == $endDate->format('Y')) {
        $output = $startDate->format('M').' - '.$endDate->format('M Y');
    } else {
        $output = $startDate->format('M Y').' - '.$endDate->format('M Y');
    }

    return $output;
}   
