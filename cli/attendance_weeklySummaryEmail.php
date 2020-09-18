<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Comms\NotificationEvent;
use Pupilsight\Comms\NotificationSender;
use Pupilsight\Domain\System\NotificationGateway;
use Pupilsight\Module\Attendance\AttendanceView;

require getcwd().'/../pupilsight.php';

setCurrentSchoolYear($guid, $connection2);

//Check for CLI, so this cannot be run through browser
if (!isCommandLineInterface()) { 
    echo __('This script cannot be run from a browser, only via CLI.');
} else {
    setCurrentSchoolYear($guid, $connection2);

    require_once __DIR__ . '/../modules/Attendance/moduleFunctions.php';
    require_once __DIR__ . '/../modules/Attendance/src/AttendanceView.php';
    $attendance = new AttendanceView($pupilsight, $pdo);
    
    $countClassAsSchool = getSettingByScope($connection2, 'Attendance', 'countClassAsSchool');
    $firstDayOfTheWeek = $pupilsight->session->get('firstDayOfTheWeek');
    $dateFormat = $_SESSION[$guid]['i18n']['dateFormat'];
    
    $dateEnd = new DateTime();
    $dateStart = new DateTime();
    $dateStart->modify("$firstDayOfTheWeek this week");

    $data = array(
        'dateStart' => $dateStart->format('Y-m-d'), 
        'dateEnd' => $dateEnd->format('Y-m-d'), 
        'pupilsightSchoolYearID' => $pupilsight->session->get('pupilsightSchoolYearID')
    );
    $sql = "SELECT pupilsightRollGroup.nameShort as rollGroupName, pupilsightYearGroup.pupilsightYearGroupID, pupilsightAttendanceLogPerson.*, pupilsightPerson.surname, pupilsightPerson.preferredName, pupilsightCourse.nameShort as courseName, pupilsightCourseClass.nameShort as className, pupilsightCourseClass.pupilsightCourseClassID
            FROM pupilsightAttendanceLogPerson
            JOIN pupilsightPerson ON (pupilsightPerson.pupilsightPersonID=pupilsightAttendanceLogPerson.pupilsightPersonID)
            JOIN pupilsightStudentEnrolment ON (pupilsightStudentEnrolment.pupilsightPersonID=pupilsightPerson.pupilsightPersonID)
            JOIN pupilsightRollGroup ON (pupilsightRollGroup.pupilsightRollGroupID=pupilsightStudentEnrolment.pupilsightRollGroupID)
            JOIN pupilsightYearGroup ON (pupilsightYearGroup.pupilsightYearGroupID=pupilsightStudentEnrolment.pupilsightYearGroupID)
            JOIN pupilsightAttendanceCode ON (pupilsightAttendanceCode.pupilsightAttendanceCodeID=pupilsightAttendanceLogPerson.pupilsightAttendanceCodeID)
            LEFT JOIN pupilsightCourseClass ON (pupilsightAttendanceLogPerson.pupilsightCourseClassID=pupilsightCourseClass.pupilsightCourseClassID)
            LEFT JOIN pupilsightCourse ON (pupilsightCourse.pupilsightCourseID=pupilsightCourseClass.pupilsightCourseID)
            WHERE pupilsightAttendanceLogPerson.date BETWEEN :dateStart AND :dateEnd
            AND pupilsightPerson.status='Full' ";

    if ($countClassAsSchool == 'N') {
        $sql .= "AND NOT pupilsightAttendanceLogPerson.context='Class' ";
    }

    $sql .= "AND pupilsightStudentEnrolment.pupilsightSchoolYearID=:pupilsightSchoolYearID
            ORDER BY pupilsightYearGroup.sequenceNumber, pupilsightRollGroup.nameShort, pupilsightPerson.surname, pupilsightPerson.preferredName, pupilsightAttendanceLogPerson.date, pupilsightAttendanceLogPerson.timestampTaken
    ";

    $reportByYearGroup = array();
    $results = $pdo->executeQuery($data, $sql);

    if ($results && $results->rowCount() > 0) {
        $attendanceLogs = $results->fetchAll(\PDO::FETCH_GROUP);
        foreach ($attendanceLogs as $rollGroupName => $rollGroupLogs) {
            $pupilsightYearGroupID = current($rollGroupLogs)['pupilsightYearGroupID'];

            // Fields to group per-day for attendance logs
            $fields = array('context', 'date', 'type', 'reason', 'comment', 'timestampTaken', 'courseName', 'className', 'pupilsightCourseClassID');
            $fields = array_flip($fields);

            // Build an attendance log set of days for each student
            $logsByStudent = array_reduce($rollGroupLogs, function ($carry, &$item) use (&$fields) {
                $id = $item['pupilsightPersonID'];
                $carry[$id]['preferredName'] = $item['preferredName'];
                $carry[$id]['surname'] = $item['surname'];
                $carry[$id]['days'][$item['date']][] = array_intersect_key($item, $fields);
                
                return $carry;
            }, array());

            // Filter down to just the relevant logs
            foreach ($logsByStudent as $key => &$item) {
                $item['days'] = array_map(function($logs) use (&$attendance) {
                    $endOfDay = end($logs);

                    // Grab the end-of-class and end-of-day statuses for each set of logs
                    $filtered = array_reduce($logs, function($carry, $log) use ($endOfDay) {
                        if ($log['context'] == 'Class' || $log === $endOfDay) {
                            $carry[$log['pupilsightCourseClassID']] = $log;
                        }
                        return $carry;
                    }, array());

                    // Remove all logs that aren't absent or late
                    $filtered = array_filter($filtered, function($log) use (&$attendance)  {
                        return $attendance->isTypeAbsent($log['type']) || $attendance->isTypeLate($log['type']);
                    });
                    
                    return $filtered;
                }, $item['days']);

                // Keep only days with logs left
                $item['days'] = array_filter($item['days'], function($logs) {
                    return !empty($logs);
                });
            }

            // Keep only students who have absent days
            $logsByStudent = array_filter($logsByStudent, function ($item) {
                return !empty($item['days']);
            });

            // Skip reports for empty data sets
            if (count($logsByStudent) == 0) continue;

            $report = '<h4>'.$rollGroupName.'  &nbsp;<small>(Total '.count($logsByStudent).')</small></h4>';
            $report .= '<ul>';

            foreach ($logsByStudent as $pupilsightPersonID => $student) {
                $report .= '<li>';
                $report .= '<a href="'.$_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/Attendance/report_studentHistory.php&pupilsightPersonID='.$pupilsightPersonID.'" target="_blank">';
                $report .= formatName('', $student['preferredName'], $student['surname'], 'Student', true, true);
                $report .= '</a>';

                foreach ($student['days'] as $date => $logs) {
                    $report .= '<br/><span style="display:inline-block; width:45px;margin-left:30px;">'.date('D', strtotime($date)) .'</span>';
                    $report .= '<span style="display:inline-block; width:65px;">'.date('M j', strtotime($date)) .'</span>';

                    // Display frequencies of each absence type
                    $types = array_count_values(array_column($logs, 'type'));
                    $types = array_map(function($type, $count) {
                        return ($count > 1)? $type.' ('.$count.')' : $type;
                    }, array_keys($types), $types);

                    $report .= implode(', ', $types);
                }
                $report .= '<br/><br/></li>';
            }
            $report .= '</ul>';

            $reportByYearGroup[$pupilsightYearGroupID][$rollGroupName] = $report;
        }
    }
    
    if (!empty($reportByYearGroup)) {
        // Initialize the notification sender & gateway objects
        $notificationGateway = new NotificationGateway($pdo);
        $notificationSender = new NotificationSender($notificationGateway, $pupilsight->session);

        $reportHeading = '<h3>'.__('Weekly Attendance Summary').': '.$dateStart->format('M j').' - '.$dateEnd->format('M j').'</h3>';

        foreach ($reportByYearGroup as $pupilsightYearGroupID => $reportByRollGroup) {
            // Raise a new notification event
            $event = new NotificationEvent('Attendance', 'Weekly Attendance Summary');

            $event->addScope('pupilsightYearGroupID', $pupilsightYearGroupID);
            $event->setNotificationText(__('An Attendance CLI script has run.').'<br/><br/>'.$reportHeading . implode(' ', $reportByRollGroup));
            $event->setActionLink('/index.php?q=/modules/Attendance/report_summary_byDate.php&dateStart='.$dateStart->format($dateFormat).'dateEnd='.$dateEnd->format($dateFormat).'&group=all&sort=rollGroup');

            // Push the event to the notification sender
            $event->pushNotifications($notificationGateway, $notificationSender);
        }

        // Send all notifications
        $sendReport = $notificationSender->sendNotifications();

        // Output the result to terminal
        echo sprintf('Sent %1$s notifications: %2$s inserts, %3$s updates, %4$s emails sent, %5$s emails failed.', $sendReport['count'], $sendReport['inserts'], $sendReport['updates'], $sendReport['emailSent'], $sendReport['emailFailed'])."\n";
    }
}
