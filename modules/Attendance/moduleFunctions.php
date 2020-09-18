<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Module\Attendance\AttendanceView;


//Get's a count of absent days for specified student between specified dates (YYYY-MM-DD, inclusive). Return of FALSE means there was an error, or no data
function getAbsenceCount($guid, $pupilsightPersonID, $connection2, $dateStart, $dateEnd, $pupilsightCourseClassID = 0)
{
    $queryFail = false;

    global $pupilsight, $session, $pdo;
    require_once __DIR__ . '/src/AttendanceView.php';
    $attendance = new AttendanceView($pupilsight, $pdo);

    //Get all records for the student, in the date range specified, ordered by date and timestamp taken.
    try {
        if (!empty($pupilsightCourseClassID)) {
            $data = array('pupilsightPersonID' => $pupilsightPersonID, 'dateStart' => $dateStart, 'dateEnd' => $dateEnd, 'pupilsightCourseClassID' => $pupilsightCourseClassID);
            $sql = "SELECT pupilsightAttendanceLogPerson.*, pupilsightSchoolYearSpecialDay.type AS specialDay FROM pupilsightAttendanceLogPerson
                    LEFT JOIN pupilsightSchoolYearSpecialDay ON (pupilsightSchoolYearSpecialDay.date=pupilsightAttendanceLogPerson.date AND pupilsightSchoolYearSpecialDay.type='School Closure')
                WHERE pupilsightPersonID=:pupilsightPersonID AND context='Class' AND pupilsightCourseClassID=:pupilsightCourseClassID AND (pupilsightAttendanceLogPerson.date BETWEEN :dateStart AND :dateEnd) ORDER BY pupilsightAttendanceLogPerson.date, timestampTaken";
        } else {
            $countClassAsSchool = getSettingByScope($connection2, 'Attendance', 'countClassAsSchool');
            $data = array('pupilsightPersonID' => $pupilsightPersonID, 'dateStart' => $dateStart, 'dateEnd' => $dateEnd);
            $sql = "SELECT pupilsightAttendanceLogPerson.*, pupilsightSchoolYearSpecialDay.type AS specialDay
                    FROM pupilsightAttendanceLogPerson
                    LEFT JOIN pupilsightSchoolYearSpecialDay ON (pupilsightSchoolYearSpecialDay.date=pupilsightAttendanceLogPerson.date AND pupilsightSchoolYearSpecialDay.type='School Closure')
                    WHERE pupilsightPersonID=:pupilsightPersonID
                    AND (pupilsightAttendanceLogPerson.date BETWEEN :dateStart AND :dateEnd)";
                    if ($countClassAsSchool == "N") {
                        $sql .= ' AND NOT context=\'Class\'';
                    }
                    $sql .= " ORDER BY pupilsightAttendanceLogPerson.date, timestampTaken";
        }
        $result = $connection2->prepare($sql);
        $result->execute($data);
    } catch (PDOException $e) {
        $queryFail = true;
    }

    if ($queryFail) {
        return false;
    } else {
        $absentCount = 0;
        if ($result->rowCount() >= 0) {
            $endOfDays = array();
            $dateCurrent = '';
            $dateLast = '';
            $count = -1;

            //Scan through all records, saving the last record for each day
            while ($row = $result->fetch()) {
                if ($row['specialDay'] != 'School Closure') {
                    $dateCurrent = $row['date'];
                    if ($dateCurrent != $dateLast) {
                        ++$count;
                    }
                    $endOfDays[$count] = $row['type'];
                    $dateLast = $dateCurrent;
                }
            }

            //Scan though all of the end of days records, counting up days ending in absent
            if (count($endOfDays) >= 0) {
                foreach ($endOfDays as $endOfDay) {
                    if ( $attendance->isTypeAbsent($endOfDay) ) {
                        ++$absentCount;
                    }
                }
            }
        }

        return $absentCount;
    }
}

//Get last N school days from currentDate within the last 100
function getLastNSchoolDays( $guid, $connection2, $date, $n = 5, $inclusive = false ) {


    $timestamp = dateConvertToTimestamp($date);
    if ($inclusive == true)  $timestamp += 86400;

    $count = 0;
    $spin = 1;
    $max = max($n, 100);
    $lastNSchoolDays = array();
    while ($count < $n and $spin <= $max) {
        $date = date('Y-m-d', ($timestamp - ($spin * 86400)));
        if (isSchoolOpen($guid, $date, $connection2 )) {
            $lastNSchoolDays[$count] = $date;
            ++$count;
        }
        ++$spin;
    }

    return $lastNSchoolDays;
}

//Get's a count of late days for specified student between specified dates (YYYY-MM-DD, inclusive). Return of FALSE means there was an error.
function getLatenessCount($guid, $pupilsightPersonID, $connection2, $dateStart, $dateEnd)
{
    $queryFail = false;

    //Get all records for the student, in the date range specified, ordered by date and timestamp taken.
    try {
        $countClassAsSchool = getSettingByScope($connection2, 'Attendance', 'countClassAsSchool');
        $data = array('pupilsightPersonID' => $pupilsightPersonID, 'dateStart' => $dateStart, 'dateEnd' => $dateEnd);
        $sql = "SELECT count(*) AS count
                FROM pupilsightAttendanceLogPerson p, pupilsightAttendanceCode c
                WHERE c.scope='Onsite - Late'
                AND p.pupilsightPersonID=:pupilsightPersonID
                AND p.date>=:dateStart
                AND p.date<=:dateEnd
                AND p.type=c.name";
                if ($countClassAsSchool == "N") {
                    $sql .= ' AND NOT context=\'Class\'';
                }
        $result = $connection2->prepare($sql);
        $result->execute($data);
    } catch (PDOException $e) {
        $queryFail = true;
    }

    if ($queryFail) {
        return false;
    } else {
        $row = $result->fetch();
        return $row['count'];
    }
}

function num2alpha($n)
{
    for ($r = ''; $n >= 0; $n = intval($n / 26) - 1) {
        $r = chr($n % 26 + 0x41).$r;
    }

    return $r;
}

function getColourArray()
{
    $return = array();

    $return[] = '153, 102, 255';
    $return[] = '255, 99, 132';
    $return[] = '54, 162, 235';
    $return[] = '255, 206, 86';
    $return[] = '75, 192, 192';
    $return[] = '255, 159, 64';
    $return[] = '152, 221, 95';

    return $return;
}
