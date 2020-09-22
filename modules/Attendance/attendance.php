<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Domain\DataSet;
use Pupilsight\Forms\DatabaseFormFactory;
use Pupilsight\Forms\Form;
use Pupilsight\Services\Format;
use Pupilsight\Tables\DataTable;

//Module includes
require_once __DIR__ . '/moduleFunctions.php';

// get session object
$session = $container->get('session');
$page->breadcrumbs->add(__('View Daily Attendance'));

// show access denied message, if needed
if (!isActionAccessible($guid, $connection2, '/modules/Attendance/attendance.php')) {
    $page->addError(__("You do not have access to this action."));
    return;
}

// rendering parameters
$currentDate = isset($_GET['currentDate']) ? Format::dateConvert($_GET['currentDate']) : date('Y-m-d');
$today = date("Y-m-d");
$lastNSchoolDays = getLastNSchoolDays($guid, $connection2, $currentDate, 10, true);
$accessNotRegistered = isActionAccessible($guid, $connection2, "/modules/Attendance/report_rollGroupsNotRegistered_byDate.php")
    && isActionAccessible($guid, $connection2, "/modules/Attendance/report_courseClassesNotRegistered_byDate.php");
$pupilsightPersonID = ($accessNotRegistered && isset($_GET['pupilsightPersonID'])) ?
    $_GET['pupilsightPersonID'] : $session->get('pupilsightPersonID');

// define attendance filter form, if user is permit to view it
$form = Form::create('action', $session->get('absoluteURL') . '/index.php', 'get');

$form->setTitle(__('View Daily Attendance'));
$form->setFactory(DatabaseFormFactory::create($pdo));
$form->setClass('noIntBorder fullWidth');

$form->addHiddenValue('q', '/modules/' . $session->get('module') . '/attendance.php');

$row = $form->addRow();
$row->addLabel('currentDate', __('Date'))->description($_SESSION[$guid]['i18n']['dateFormat'])->prepend(__('Format:'));
$row->addDate('currentDate')->setValue(Format::date($currentDate))->required();

if (isActionAccessible($guid, $connection2, '/modules/Attendance/report_rollGroupsNotRegistered_byDate.php')) {
    $row = $form->addRow();
    $row->addLabel('pupilsightPersonID', __('Staff'));
    $row->addSelectStaff('pupilsightPersonID')->selected($pupilsightPersonID)->placeholder()->required();
} else {
    $form->addHiddenValue('pupilsightPersonID', $session->get('pupilsightPersonID'));
}

$row = $form->addRow();
$row->addFooter();
$row->addSearchSubmit($pupilsight->session)->addClass('submit_align submt');

$page->write($form->getOutput());


// define attendance tables, if user is permit to view them
if (isset($_SESSION[$guid]["username"])) {
    // generator of basic attendance table
    $getDailyAttendanceTable = function ($guid, $connection2, $currentDate, $rowID, $takeAttendanceURL) use ($session) {

        // proto attendance table with columns for both
        // roll group and course class
        $dailyAttendanceTable = DataTable::create('dailyAttendanceTable');

        // column definitions
        $dailyAttendanceTable->addColumn('group', __('Class'))
            ->context('primary')
            ->format(function ($row) use ($session, $rowID) {
                return Format::link(
                    $session->get('absoluteURL') . '/index.php?' .
                        http_build_query(['q' => $row['groupQuery'], $rowID => $row[$rowID]]),
                    $row['groupName']
                );
            });
        $dailyAttendanceTable->addColumn('recent-history', __('Recent History'))
            ->width('40%')
            ->format(function ($row) use ($takeAttendanceURL, $rowID, $session) {
                $dayTable = "<table class='historyCalendarMini rounded-sm overflow-hidden' cellspacing='0'>";

                $l = sizeof($row['recentHistory']);
                for ($i = 0; $i < $l; $i++) {
                    $dayTable .= '<tr>';
                    for ($j = 0; ($j < 10) && ($i + $j < $l); $j++) {
                        // grouping 10 days as a row
                        $day = $row['recentHistory'][$i + $j];
                        $link = '';
                        $content = '';

                        // default link and content
                        if (!empty($day['currentDate']) && !empty($day['currentDayTimestamp'])) {
                            // link and date content of a cell
                            $link = $session->get('absoluteURL') . '/index.php?' . http_build_query([
                                'q' => $takeAttendanceURL,
                                $rowID => $row[$rowID],
                                'currentDate' => $day['currentDate'],
                            ]);
                            $content =
                                '<div class="day text-xs">' . Format::dateReadable($day['currentDate'], '%d') . '</div>' .
                                '<div class="month text-xxs mt-px">' . Format::dateReadable($day['currentDate'], '%b') . '</div>';
                        }

                        // determine how to display link and content
                        // according to status
                        switch ($day['status']) {
                            case 'na':
                                $class = 'highlightNoData';
                                $content = __('NA');
                                break;
                            case 'present':
                                $class = 'highlightPresent';
                                $content = Format::link($link, $content);
                                break;
                            case 'absent':
                                $class = 'highlightAbsent';
                                $content = Format::link($link, $content);
                                break;
                            default:
                                $class = 'highlightNoData';
                                break;
                        }

                        $dayTable .= "<td class=\"{$class}\" style=\"padding: 12px !important;\">{$content}</td>";
                    }
                    $i += $j;
                    $dayTable .= '</tr>';
                }

                $dayTable .= '</table>';
                return $dayTable;
            });
        $dailyAttendanceTable->addColumn('today', __('Today'))
            ->context('primary')
            ->width('6%')
            ->format(function ($row) use ($session) {
                switch ($row['today']) {
                    case 'taken':
                        // attendance taken
                        return '<img src="./themes/' . $session->get('pupilsightThemeName') . '/img/iconTick.png"/>';
                    case 'not taken':
                        // attendance not taken
                        return '<img src="./themes/' . $session->get('pupilsightThemeName') . '/img/iconCross.png"/>';
                    case 'not timetabled':
                        // class not timetabled on the day
                        return '<span title="' . __('This class is not timetabled to run on the specified date. Attendance may still be taken for this group however it currently falls outside the regular schedule for this class.') . '">' .
                            __('N/A') . '</span>';
                }
            });
       /* $dailyAttendanceTable->addColumn('in', __('In'))
            ->context('primary')
            ->width('6%');*/

        /*$dailyAttendanceTable->addColumn('out', __('Out'))
            ->context('primary')
            ->width('6%');*/

        // action column, if user has the permission, and if this is a school day.
        if (isActionAccessible($guid, $connection2, $takeAttendanceURL) && isSchoolOpen($guid, $currentDate, $connection2)) {
            $dailyAttendanceTable->addActionColumn()
                ->addParam($rowID)
                ->addParam('currentDate')
                ->addAction('takeAttendance')
                ->setLabel(__('Take Attendance'))
                ->setIcon('attendance')
                ->setURL($takeAttendanceURL);
        }

        return $dailyAttendanceTable;
    };

    if ($currentDate > $today) {
        $page->write(Format::alert(__("The specified date is in the future: it must be today or earlier.")));
        return;
    } elseif (isSchoolOpen($guid, $currentDate, $connection2)==false) {
        $page->write(Format::alert(__("School is closed on the specified date, and so attendance information cannot be recorded.")));
        return;
    }

    if (isActionAccessible($guid, $connection2, "/modules/Attendance/attendance_take_byRollGroup.php")) {
        // Show My Form Groups
        try {
           
         
          
                $result = $connection2->prepare("SELECT pupilsightRollGroupID, pupilsightRollGroup.nameShort as name, firstDay, lastDay FROM pupilsightRollGroup JOIN pupilsightSchoolYear ON (pupilsightRollGroup.pupilsightSchoolYearID=pupilsightSchoolYear.pupilsightSchoolYearID) WHERE (pupilsightPersonIDTutor=:pupilsightPersonIDTutor1 OR pupilsightPersonIDTutor2=:pupilsightPersonIDTutor2 OR pupilsightPersonIDTutor3=:pupilsightPersonIDTutor3) AND pupilsightRollGroup.pupilsightSchoolYearID=:pupilsightSchoolYearID AND pupilsightRollGroup.attendance = 'Y'");
                $result->execute([
                    'pupilsightPersonIDTutor1' => $pupilsightPersonID,
                    'pupilsightPersonIDTutor2' => $pupilsightPersonID,
                    'pupilsightPersonIDTutor3' => $pupilsightPersonID,
                    'pupilsightSchoolYearID' => $session->get('pupilsightSchoolYearID'),
                ]);
            
           
        } catch (PDOException $e) {
            $page->addError($e->getMessage());
        }

        if ($result->rowCount() > 0) {
            $attendanceByRollGroup = [];
            while ($row = $result->fetch()) {
                //Produce array of attendance data
                try {
                    $resultAttendance = $connection2->prepare('SELECT date, pupilsightRollGroupID, UNIX_TIMESTAMP(timestampTaken) FROM pupilsightAttendanceLogRollGroup WHERE pupilsightRollGroupID=:pupilsightRollGroupID AND date>=:dateStart AND date<=:dateEnd ORDER BY date');
                    $resultAttendance->execute([
                        'pupilsightRollGroupID' => $row["pupilsightRollGroupID"],
                        'dateStart' => $lastNSchoolDays[count($lastNSchoolDays) - 1],
                        'dateEnd' => $lastNSchoolDays[0],
                    ]);
                } catch (PDOException $e) {
                    $page->addError($e->getMessage());
                }
                $logHistory = array();
                while ($rowAttendance = $resultAttendance->fetch()) {
                    $logHistory[$rowAttendance['date']] = true;
                }

                //Grab attendance log for the group & current day
                try {
                    $resultLog = $connection2->prepare("SELECT DISTINCT pupilsightAttendanceLogRollGroupID, pupilsightAttendanceLogRollGroup.timestampTaken as timestamp,
                        COUNT(DISTINCT pupilsightAttendanceLogPerson.pupilsightPersonID) AS total,
                        COUNT(DISTINCT CASE WHEN pupilsightAttendanceLogPerson.direction = 'Out' THEN pupilsightAttendanceLogPerson.pupilsightPersonID END) AS absent
                        FROM pupilsightAttendanceLogPerson
                        JOIN pupilsightAttendanceLogRollGroup ON (pupilsightAttendanceLogRollGroup.date = pupilsightAttendanceLogPerson.date)
                        JOIN pupilsightStudentEnrolment ON (pupilsightStudentEnrolment.pupilsightPersonID=pupilsightAttendanceLogPerson.pupilsightPersonID AND pupilsightStudentEnrolment.pupilsightRollGroupID=pupilsightAttendanceLogRollGroup.pupilsightRollGroupID)
                        WHERE pupilsightAttendanceLogRollGroup.pupilsightRollGroupID=:pupilsightRollGroupID
                        AND pupilsightAttendanceLogPerson.date LIKE :date
                        AND pupilsightAttendanceLogPerson.context = 'Roll Group'
                        GROUP BY pupilsightAttendanceLogRollGroup.pupilsightAttendanceLogRollGroupID
                        ORDER BY pupilsightAttendanceLogPerson.timestampTaken");
                    $resultLog->execute([
                        'pupilsightRollGroupID' => $row['pupilsightRollGroupID'],
                        'date' => $currentDate . '%'
                    ]);
                } catch (PDOException $e) {
                    $page->addError($e->getMessage());
                }

                $log = $resultLog->fetch();

                // general row variables
                $row['currentDate'] = Format::date($currentDate);

                // render group link variables
                $row['groupQuery'] = '/modules/Roll Groups/rollGroups_details.php';
                $row['groupName'] = $row['name'];

                // render recentHistory into the row
                for ($i = count($lastNSchoolDays) - 1; $i >= 0; --$i) {
                    if ($i > (count($lastNSchoolDays) - 1)) {
                        $dayData = [
                            'currentDate' => null,
                            'currentDayTimestamp' => null,
                            'status' => 'na',
                        ];
                    } else {
                        $dayData = [
                            'currentDate' => Format::dateConvert($lastNSchoolDays[$i]),
                            'currentDayTimestamp' => Format::timestamp($lastNSchoolDays[$i]),
                            'status' => isset($logHistory[$lastNSchoolDays[$i]]) ? 'present' : 'absent',
                        ];
                    }
                    $row['recentHistory'][] = $dayData;
                }

                // Attendance not taken
                $row['today'] = ($resultLog->rowCount() < 1) ? 'not taken' : 'taken';
                $row['in'] = ($resultLog->rowCount() < 1) ? "" : ($log["total"] - $log["absent"]);
                $row['out'] = $log["absent"];

                $attendanceByRollGroup[] = $row;
            }

            // define DataTable
            $takeAttendanceURL = '/modules/Attendance/attendance_take_byRollGroup.php';
            $attendanceByRollGroupTable = $getDailyAttendanceTable(
                $guid,
                $connection2,
                $currentDate,
                'pupilsightRollGroupID',
                $takeAttendanceURL
            );
            $attendanceByRollGroupTable->setTitle(__('My Roll Group'));
            $attendanceByRollGroupTable->withData(new DataSet($attendanceByRollGroup));
        }
    }

    if (isActionAccessible($guid, $connection2, "/modules/Attendance/attendance_take_byCourseClass.php")) {
        // Produce array of attendance data
        try {
            $result = $connection2->prepare("SELECT date, pupilsightCourseClassID FROM pupilsightAttendanceLogCourseClass WHERE date>=:dateStart AND date<=:dateEnd ORDER BY date");
            $result->execute([
                'dateStart' => $lastNSchoolDays[count($lastNSchoolDays) - 1],
                'dateEnd' => $lastNSchoolDays[0],
            ]);
        } catch (PDOException $e) {
            $page->addError($e->getMessage());
        }
        $logHistory = array();
        while ($row = $result->fetch()) {
            $logHistory[$row['pupilsightCourseClassID']][$row['date']] = true;
        }

        // Produce an array of scheduled classes
        try {
            $result = $connection2->prepare("SELECT pupilsightTTDayRowClass.pupilsightCourseClassID, pupilsightTTDayDate.date FROM pupilsightTTDayRowClass JOIN pupilsightTTDayDate ON (pupilsightTTDayDate.pupilsightTTDayID=pupilsightTTDayRowClass.pupilsightTTDayID) JOIN pupilsightCourseClass ON (pupilsightCourseClass.pupilsightCourseClassID=pupilsightTTDayRowClass.pupilsightCourseClassID) WHERE pupilsightCourseClass.attendance = 'Y' AND pupilsightTTDayDate.date>=:dateStart AND pupilsightTTDayDate.date<=:dateEnd ORDER BY pupilsightTTDayDate.date");
            $result->execute([
                'dateStart' => $lastNSchoolDays[count($lastNSchoolDays) - 1],
                'dateEnd' => $lastNSchoolDays[0],
            ]);
        } catch (PDOException $e) {
            $page->addError($e->getMessage());
        }
        $ttHistory = array();
        while ($row = $result->fetch()) {
            $ttHistory[$row['pupilsightCourseClassID']][$row['date']] = true;
        }

        //Show My Classes
        try {
            $result = $connection2->prepare("SELECT pupilsightCourse.nameShort AS course, pupilsightCourseClass.nameShort AS class, pupilsightCourseClass.pupilsightCourseClassID,
                (SELECT count(*) FROM pupilsightCourseClassPerson WHERE role='Student' AND pupilsightCourseClassID=pupilsightCourseClass.pupilsightCourseClassID) as studentCount
                FROM pupilsightCourse, pupilsightCourseClass, pupilsightCourseClassPerson
                WHERE pupilsightSchoolYearID=:pupilsightSchoolYearID AND pupilsightCourse.pupilsightCourseID=pupilsightCourseClass.pupilsightCourseID
                AND pupilsightCourseClass.pupilsightCourseClassID=pupilsightCourseClassPerson.pupilsightCourseClassID
                AND pupilsightCourseClassPerson.pupilsightPersonID=:pupilsightPersonID AND NOT role LIKE '% - Left%'
                AND pupilsightCourseClass.attendance = 'Y'
                ORDER BY course, class");
            $result->execute([
                'pupilsightSchoolYearID' => $session->get('pupilsightSchoolYearID'),
                'pupilsightPersonID' => $pupilsightPersonID,
            ]);
        } catch (PDOException $e) {
            //
        }

        if ($result->rowCount() > 0) {
            $count = 0;

            $attendanceByCourseClass = [];
            while ($row = $result->fetch()) {
                // Skip classes with no students
                if ($row['studentCount'] <= 0) {
                    continue;
                }

                $count++;

                //Grab attendance log for the class & current day
                try {
                    $resultLog = $connection2->prepare("SELECT pupilsightAttendanceLogCourseClass.timestampTaken as timestamp,
                        COUNT(pupilsightAttendanceLogPerson.pupilsightPersonID) AS total, SUM(pupilsightAttendanceLogPerson.direction = 'Out') AS absent
                        FROM pupilsightAttendanceLogCourseClass
                        JOIN pupilsightAttendanceLogPerson ON pupilsightAttendanceLogPerson.pupilsightCourseClassID = pupilsightAttendanceLogCourseClass.pupilsightCourseClassID
                        WHERE pupilsightAttendanceLogCourseClass.pupilsightCourseClassID=:pupilsightCourseClassID
                        AND pupilsightAttendanceLogPerson.context='Class'
                        AND pupilsightAttendanceLogCourseClass.date LIKE :date AND pupilsightAttendanceLogPerson.date LIKE :date
                        GROUP BY pupilsightAttendanceLogCourseClass.pupilsightAttendanceLogCourseClassID
                        ORDER BY pupilsightAttendanceLogCourseClass.timestampTaken");
                    $resultLog->execute([
                        'pupilsightCourseClassID' => $row['pupilsightCourseClassID'],
                        'date' => $currentDate . '%',
                    ]);
                } catch (PDOException $e) {
                    $page->addError($e->getMessage());
                }

                $log = $resultLog->fetch();

                // general row variables
                $row['currentDate'] = Format::date($currentDate);

                // render group link variables
                $row['groupQuery'] = '/modules/Departments/department_course_class.php';
                $row['groupName'] = $row["course"] . "." . $row["class"];

                // render recentHistory into the row
                for ($i = count($lastNSchoolDays) - 1; $i >= 0; --$i) {
                    if ($i > (count($lastNSchoolDays) - 1)) {
                        $dayData = [
                            'currentDate' => null,
                            'currentDayTimestamp' => null,
                            'status' => 'na',
                        ];
                    } else {
                        $dayData = [
                            'currentDate' => Format::dateConvert($lastNSchoolDays[$i]),
                            'currentDayTimestamp' => Format::timestamp($lastNSchoolDays[$i]),
                        ];
                        if (isset($logHistory[$row['pupilsightCourseClassID']][$lastNSchoolDays[$i]]) == true) {
                            $dayData['status'] = 'present';
                        } else {
                            $dayData['status'] =
                            isset($ttHistory[$row['pupilsightCourseClassID']][$lastNSchoolDays[$i]]) ?
                            $dayData['status'] = 'absent' :
                            $dayData['status'] = null;
                        }
                    }
                    $row['recentHistory'][] = $dayData;
                }

                // attendance today, if timetabled
                $row['today'] = null;
                if (isset($ttHistory[$row['pupilsightCourseClassID']][$currentDate])) {
                    $row['today'] = ($resultLog->rowCount() < 1) ? 'not taken' : 'taken';
                } elseif (isset($logHistory[$row['pupilsightCourseClassID']][$currentDate])) {
                    // class is not timetabled to run on the specified date
                    $row['today'] = 'not timetabled';
                }
                $row['in'] = ($resultLog->rowCount() < 1) ? "" : ($log["total"] - $log["absent"]);
                $row['out'] = $log["absent"];

                $attendanceByCourseClass[] = $row;
            }

            // define DataTable
            $takeAttendanceURL = '/modules/Attendance/attendance_take_byCourseClass.php';
            $attendanceByCourseClassTable = $getDailyAttendanceTable(
                $guid,
                $connection2,
                $currentDate,
                'pupilsightCourseClassID',
                $takeAttendanceURL
            );
            $attendanceByCourseClassTable->setTitle(__('My Classes'));
            $attendanceByCourseClassTable->withData(new DataSet($attendanceByCourseClass));
        }
    }
}

//
// write page outputs
//
if (isset($attendanceByRollGroupTable)) {
    $page->write($attendanceByRollGroupTable->getOutput());
}
if (isset($attendanceByCourseClassTable)) {
    $page->write($attendanceByCourseClassTable->getOutput());
}
