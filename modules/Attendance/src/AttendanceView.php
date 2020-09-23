<?php
/*
Pupilsight, Flexible & Open School System
 */

namespace Pupilsight\Module\Attendance;

use Pupilsight\Contracts\Database\Connection;
use Pupilsight\session;
use Pupilsight\Services\Format;

/**
 * Attendance display & edit class
 *
 * @version 12th Sept 2016
 * @since   12th Sept 2016
 */
class AttendanceView
{
    /**
     * Pupilsight\Contracts\Database\Connection
     */
    protected $pdo;

    /**
     * Pupilsight\session
     */
    protected $session;

    /**
     * Attendance Types
     * @var array
     */
    protected $attendanceTypes = array();

    /**
     * Attendance Reasons
     * @var array
     */
    protected $attendanceReasons = array();
    protected $genericReasons = array();
    protected $medicalReasons = array();

    protected $currentDate;
    protected $last5SchoolDays = array();

    protected $guid;

    /**
     * Constructor
     *
     * @version  3rd May 2016
     * @since    3rd May 2016
     * @param    Pupilsight\session
     * @param    Pupilsight\config
     * @param    Pupilsight\Contracts\Database\Connection
     * @return   void
     */
    public function __construct(\Pupilsight\Core $pupilsight, Connection $pdo)
    {
        $this->session = $pupilsight->session;
        $this->pdo = $pdo;

        $this->guid = $pupilsight->guid();

        // Get attendance codes
        try {
            $data = array();
            $sql = "SELECT * FROM pupilsightAttendanceCode WHERE active = 'Y' ORDER BY sequenceNumber ASC, name";
            $result = $this->pdo->executeQuery($data, $sql);
        } catch (PDOException $e) {
            echo "<div class='alert alert-danger'>" . $e->getMessage() . '</div>';
        }
        if ($result->rowCount() > 0) {
            while ($attendanceCode = $result->fetch()) {
                $this->attendanceTypes[$attendanceCode['name']] = $attendanceCode;
            }
        }

        // Get the current date
        $currentDate = (isset($_GET['currentDate'])) ? dateConvert($this->guid, $_GET['currentDate']) : date('Y-m-d');

        // Get attendance reasons
        $this->genericReasons = explode(',', getSettingByScope($this->pdo->getConnection(), 'Attendance', 'attendanceReasons'));
        $this->medicalReasons = explode(',', getSettingByScope($this->pdo->getConnection(), 'Attendance', 'attendanceMedicalReasons'));

        //$this->attendanceReasons = array_merge( array(''), $this->genericReasons, $this->medicalReasons );
        $this->attendanceReasons = array_merge(array(''), $this->genericReasons);

        //Get last 5 school days from currentDate within the last 100
        $this->last5SchoolDays = getLastNSchoolDays($this->guid, $this->pdo->getConnection(), $currentDate, 5);
    }

    public function getAttendanceTypes()
    {
        return $this->attendanceTypes;
    }

    public function getAttendanceReasons()
    {
        return $this->attendanceReasons;
    }

    public function getAttendanceCodeByType($type)
    {
        if (isset($this->attendanceTypes[$type]) == false) {
            return '';
        }

        return $this->attendanceTypes[$type];
    }

    public function isTypePresent($type)
    {
        if (isset($this->attendanceTypes[$type]) == false) {
            return false;
        }

        return ($this->attendanceTypes[$type]['direction'] == 'In');
    }

    public function isTypeLate($type)
    {
        if (isset($this->attendanceTypes[$type]) == false) {
            return false;
        }

        return ($this->attendanceTypes[$type]['scope'] == 'Onsite - Late');
    }

    public function isTypeLeft($type)
    {
        if (isset($this->attendanceTypes[$type]) == false) {
            return false;
        }

        return ($this->attendanceTypes[$type]['scope'] == 'Offsite - Left');
    }

    public function isTypeAbsent($type)
    {
        if (isset($this->attendanceTypes[$type]) == false) {
            return false;
        }

        return ($this->attendanceTypes[$type]['direction'] == 'Out' && $this->isTypeOffsite($type));
    }

    public function isTypeOnsite($type)
    {
        if (isset($this->attendanceTypes[$type]) == false) {
            return false;
        }

        return (stristr($this->attendanceTypes[$type]['scope'], 'Onsite') !== false);
    }

    public function isTypeOffsite($type)
    {
        if (isset($this->attendanceTypes[$type]) == false) {
            return false;
        }

        return (stristr($this->attendanceTypes[$type]['scope'], 'Offsite') !== false);
    }

    public function renderMiniHistory($pupilsightPersonID, $context, $pupilsightCourseClassID = null, $cssClass = '')
    {

        $countClassAsSchool = getSettingByScope($this->pdo->getConnection(), 'Attendance', 'countClassAsSchool');

        $schoolDays = (is_array($this->last5SchoolDays)) ? implode(',', $this->last5SchoolDays) : '';

        // Grab all 5 days on one query to improve page load performance
        if ($context == 'Class') {
            $data = array('pupilsightPersonID' => $pupilsightPersonID, 'schoolDays' => $schoolDays, 'pupilsightCourseClassID' => $pupilsightCourseClassID);
            $sql = "SELECT date, type, reason
                    FROM pupilsightAttendanceLogPerson
                    WHERE pupilsightPersonID=:pupilsightPersonID
                    AND pupilsightCourseClassID=:pupilsightCourseClassID
                    AND FIND_IN_SET(date, :schoolDays)
                    ORDER BY pupilsightAttendanceLogPerson.timestampTaken";
        }
        else {
            $data = array('pupilsightPersonID' => $pupilsightPersonID, 'schoolDays' => $schoolDays);
            $sql = "SELECT date, type, reason
                    FROM pupilsightAttendanceLogPerson
                    WHERE pupilsightPersonID=:pupilsightPersonID";
                    if ($countClassAsSchool == "N") {
                        $sql .= " AND NOT context='Class'";
                    }
                    $sql .= " AND FIND_IN_SET(date, :schoolDays)
                    ORDER BY pupilsightAttendanceLogPerson.timestampTaken";
        }

        $result = $this->pdo->executeQuery($data, $sql);

        $logs = ($result->rowCount() > 0) ? $result->fetchAll(\PDO::FETCH_GROUP) : array();
        $logs = array_reduce(array_keys($logs), function ($group, $date) use ($logs) {
            $group[$date] = end($logs[$date]);
            return $group;
        }, array());

        $dateFormat = $_SESSION[$this->guid]['i18n']['dateFormatPHP'];

        $output = '';
        $output .= '<table class="table historyCalendarMini ' . $cssClass . '">';
        $output .= '<tr>';
        for ($i = 4; $i >= 0; --$i) {
            if (!isset($this->last5SchoolDays[$i])) {
                $output .= '<td class="highlightNoData">';
                $output .= '<i>' . __('NA') . '</i>';
                $output .= '</td>';
            } else {
                $date = $this->last5SchoolDays[$i];
                $currentDay = new \DateTime($date);
                $link = './index.php?q=/modules/Attendance/attendance_take_byPerson.php&pupilsightPersonID=' . $pupilsightPersonID . '&currentDate=' . $currentDay->format($dateFormat);

                if (isset($logs[$date])) {
                    $log = $logs[$date];

                    $class = ($this->isTypeAbsent($log['type'])) ? 'highlightAbsent' : 'highlightPresent';
                    $linkTitle = (!empty($log['reason'])) ? $log['type'] . ': ' . $log['reason'] : $log['type'];
                } else {
                    $class = 'highlightNoData';
                    $linkTitle = '';
                }

                $output .= '<td class="' . $class . '">';
                $output .= '<a href="' . $link . '" title="' . $linkTitle . '">';
                $output .= Format::dateReadable($currentDay, '%d') . '<br/>';
                $output .= '<span>' . Format::dateReadable($currentDay, '%b') . '</span>';
                $output .= '</a>';
                $output .= '</td>';
            }
        }
        $output .= '</tr>';
        $output .= '</table>';

        return $output;
    }

    public function renderAttendanceTypeSelect($lastType = '', $name = 'type', $width = '302px', $future = false)
    {

        $output = '';

        // Collect the current IDs of the user
        $userRoleIDs = array();
        foreach ($_SESSION[$this->guid]['pupilsightRoleIDAll'] as $role) {
            if (isset($role[0])) {
                $userRoleIDs[] = $role[0];
            }
        }

        $output .= "<select style='float: none; width: $width; margin-bottom: 3px' name='$name' id='$name'>";
        if (!empty($this->attendanceTypes)) {
            foreach ($this->attendanceTypes as $name => $attendanceType) {
                // Skip non-future codes on Set Future Absence
                if ($future && $attendanceType['future'] == 'N') {
                    continue;
                }

                // Check if a role is restricted - blank for unrestricted use
                if (!empty($attendanceType['pupilsightRoleIDAll'])) {
                    $allowAttendanceType = false;
                    $rolesAllowed = explode(',', $attendanceType['pupilsightRoleIDAll']);

                    foreach ($rolesAllowed as $role) {
                        if (in_array($role, $userRoleIDs)) {
                            $allowAttendanceType = true;
                        }
                    }
                    if ($allowAttendanceType == false) {
                        continue;
                    }
                }

                $output .= sprintf('<option value="%1$s" %2$s/>%1$s</option>', $name, (($lastType == $name) ? 'selected' : ''));
            }
        }
        $output .= '</select>';

        return $output;
    }

    public function renderAttendanceReasonSelect($lastReason = '', $name = 'reason', $width = '302px')
    {

        $output = '';

        $output .= "<select style='float: none; width: $width; margin-bottom: 3px' name='$name' id='$name'>";

        if (!empty($this->attendanceReasons) && is_array($this->attendanceReasons)) {
            foreach ($this->attendanceReasons as $attendanceReason) {
                $output .= sprintf('<option value="%1$s" %2$s/>%1$s</option>', $attendanceReason, (($lastReason == $attendanceReason) ? 'selected' : ''));
            }
        }

        $output .= '</select>';

        return $output;
    }
}
