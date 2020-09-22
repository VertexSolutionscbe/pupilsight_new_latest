<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Services\Format;

require_once '../../pupilsight.php';

if (isActionAccessible($guid, $connection2, '/modules/Staff/absences_add.php') == false) {
    die();
} else {
    // Proceed!
    $dateStart = $_POST['dateStart'] ?? '';
    $dateEnd = $_POST['dateEnd'] ?? '';
    
    $start = new DateTime(Format::dateConvert($dateStart).' 00:00:00');
    $end = new DateTime(Format::dateConvert($dateEnd).' 23:00:00');

    $dateRange = new DatePeriod($start, new DateInterval('P1D'), $end);
    $validDates = 0;

    // Count the valid school days in the selected range
    foreach ($dateRange as $date) {
        if (isSchoolOpen($guid, $date->format('Y-m-d'), $connection2)) {
            $validDates++;
        }
    }

    echo $validDates;
}
