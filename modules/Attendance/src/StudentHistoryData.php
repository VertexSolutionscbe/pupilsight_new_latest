<?php
/*
Pupilsight, Flexible & Open School System
*/

namespace Pupilsight\Module\Attendance;

use DatePeriod;
use DateInterval;
use DateTimeImmutable;
use Pupilsight\Domain\DataSet;
use Pupilsight\Services\Format;
use Pupilsight\Contracts\Database\Connection;
use Pupilsight\Domain\School\SchoolYearTermGateway;
use Pupilsight\Domain\Attendance\AttendanceLogPersonGateway;

/**
 * Student History Data
 *
 * @version v18
 * @since   v18
 */
class StudentHistoryData
{
    protected $pdo;
    protected $termGateway;
    protected $attendanceLogGateway;

    public function __construct(Connection $pdo, SchoolYearTermGateway $termGateway, AttendanceLogPersonGateway $attendanceLogGateway)
    {
        $this->pdo = $pdo;
        $this->termGateway = $termGateway;
        $this->attendanceLogGateway = $attendanceLogGateway;
    }

    /**
     * Build a data set of attendance logs grouped by term.
     *
     * @param string $pupilsightSchoolYearID
     * @param string $pupilsightPersonID
     * @param string $dateStart     Y-m-d
     * @param string $dateEnd       Y-m-d
     * @return DataSet
     */
    public function getAttendanceData($pupilsightSchoolYearID, $pupilsightPersonID, $dateStart, $dateEnd)
    {
        $connection2 = $this->pdo->getConnection();

        $countClassAsSchool = getSettingByScope($connection2, 'Attendance', 'countClassAsSchool');
        $firstDayOfTheWeek = getSettingByScope($connection2, 'System', 'firstDayOfTheWeek');

        // Get Logs
        $logs = $this->attendanceLogGateway
            ->selectAllAttendanceLogsByPerson($pupilsightSchoolYearID, $pupilsightPersonID, $countClassAsSchool)
            ->fetchGrouped();

        // Get Weekdays
        $sql = "SELECT nameShort, name FROM pupilsightDaysOfWeek where schoolDay='Y'";
        $daysOfWeek = $this->pdo->select($sql)->fetchKeyPair();
        if ($firstDayOfTheWeek == 'Sunday' && in_array('Sunday', $daysOfWeek)) {
            $daysOfWeek = array('Sun' => 'Sunday') + $daysOfWeek;
        }

        // Get Terms
        $criteria = $this->termGateway->newQueryCriteria()
            ->filterBy('schoolYear', $pupilsightSchoolYearID)
            ->filterBy('firstDay', date('Y-m-d'))
            ->sortBy('firstDay');

        $terms = $this->termGateway->querySchoolYearTerms($criteria)->toArray();
        $today = new DateTimeImmutable();

        foreach ($terms as $index => $term) {
            $specialDays = $this->termGateway->selectSchoolClosuresByTerm($term['pupilsightSchoolYearTermID'])->fetchKeyPair();

            $firstDay = new DateTimeImmutable($term['firstDay']);
            $lastDay = new DateTimeImmutable($term['lastDay']);

            $dateRange = new DatePeriod(
                $firstDay->modify($firstDayOfTheWeek == 'Monday' ? "Monday this week" : "Sunday last week"),
                new DateInterval('P1D'),
                $lastDay->modify('+1 day')
            );

            $dayCount = 0;
            foreach ($dateRange as $i => $date) {
                if ($date > $today) continue;
                if ($date > $lastDay) continue;

                $week = floor($dayCount / count($daysOfWeek));
                $weekday = $date->format('D');
                $dateYmd = $date->format('Y-m-d');

                if (!isset($daysOfWeek[$weekday])) continue;

                $absentCount = $presentCount = $partialCount = 0;

                $logs[$dateYmd] = array_map(function ($log) use (&$absentCount, &$presentCount, &$partialCount) {
                    if ($log['direction'] == 'Out' && $log['scope'] == 'Offsite') {
                        $log['status'] = 'absent';
                        $log['statusClass'] = 'error';
                        $absentCount++;
                    } elseif ($log['scope'] == 'Onsite - Late' || $log['scope'] == 'Offsite - Left') {
                        $log['status'] = 'partial';
                        $log['statusClass'] = 'warning';
                        $partialCount++;
                    } else {
                        $log['status'] = 'present';
                        $log['statusClass'] = $log['scope'] == 'Offsite' ? 'message' : 'success';
                        $presentCount++;
                    }

                    return $log;
                }, $logs[$dateYmd] ?? []);

                $endOfDay = isset($logs[$dateYmd]) ? end($logs[$dateYmd]) : [];

                $dayData = [
                    'date'            => $dateYmd,
                    'dateDisplay'     => Format::date($date),
                    'name'            => $daysOfWeek[$weekday],
                    'nameShort'       => $weekday,
                    'logs'            => $logs[$dateYmd] ?? [],
                    'endOfDay'        => $endOfDay,
                    'specialDay'      => $specialDays[$dateYmd] ?? '',
                    'outsideTerm'     => $date < $firstDay || $date > $lastDay,
                    'beforeStartDate' => !empty($dateStart) && $dateYmd < $dateStart,
                    'afterEndDate'    => !empty($dateEnd) && $dateYmd > $dateEnd,
                    'absentCount'     => $absentCount,
                    'presentCount'    => $presentCount,
                    'partialCount'    => $partialCount,
                    'pupilsightPersonID'  => $pupilsightPersonID,
                ];

                $terms[$index]['weeks'][$week][$weekday] = $dayData;
                $dayCount++;
            }
        }

        return new DataSet($terms);
    }
}
