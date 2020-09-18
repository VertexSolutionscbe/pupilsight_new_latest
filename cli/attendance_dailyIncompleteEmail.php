<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Comms\NotificationEvent;
use Pupilsight\Comms\NotificationSender;
use Pupilsight\Domain\System\NotificationGateway;

require getcwd().'/../pupilsight.php';

getSystemSettings($guid, $connection2);

setCurrentSchoolYear($guid, $connection2);

//Set up for i18n via gettext
if (isset($_SESSION[$guid]['i18n']['code'])) {
    if ($_SESSION[$guid]['i18n']['code'] != null) {
        putenv('LC_ALL='.$_SESSION[$guid]['i18n']['code']);
        setlocale(LC_ALL, $_SESSION[$guid]['i18n']['code']);
        bindtextdomain('pupilsight', getcwd().'/../i18n');
        textdomain('pupilsight');
    }
}

//Check for CLI, so this cannot be run through browser
if (!isCommandLineInterface()) { echo __('This script cannot be run from a browser, only via CLI.');
} else {
    $currentDate = date('Y-m-d');

    if (isSchoolOpen($guid, $currentDate, $connection2, true)) {
        $report = '';
        $reportInner = '';

        $partialFail = false;

        $userReport = array();
        $adminReport = array( 'rollGroup' => array(), 'classes' => array() );

        $enabledByRollGroup = getSettingByScope($connection2, 'Attendance', 'attendanceCLINotifyByRollGroup');
        $enabledByClass = getSettingByScope($connection2, 'Attendance', 'attendanceCLINotifyByClass');
        $additionalUsersList = getSettingByScope($connection2, 'Attendance', 'attendanceCLIAdditionalUsers');

        if ($enabledByRollGroup == 'N' && $enabledByClass == 'N') {
            die('Attendance CLI cancelled: Notifications not enabled in Attendance Settings.');
        }

        //Produce array of attendance data ------------------------------------------------------------------------------------------------------

        if ($enabledByRollGroup == 'Y') {
            try {
                $data = array('pupilsightSchoolYearID' => $_SESSION[$guid]['pupilsightSchoolYearID']);

                // Looks for roll groups with attendance='Y', also grabs primary tutor name
                $sql = "SELECT pupilsightRollGroupID, pupilsightRollGroup.name, pupilsightPersonIDTutor, pupilsightPersonIDTutor2, pupilsightPersonIDTutor3, pupilsightPerson.preferredName, pupilsightPerson.surname, (SELECT count(*) FROM pupilsightStudentEnrolment WHERE pupilsightStudentEnrolment.pupilsightRollGroupID=pupilsightRollGroup.pupilsightRollGroupID) AS studentCount
                FROM pupilsightRollGroup
                JOIN pupilsightPerson ON (pupilsightRollGroup.pupilsightPersonIDTutor=pupilsightPerson.pupilsightPersonID)
                WHERE pupilsightSchoolYearID=:pupilsightSchoolYearID
                AND attendance = 'Y'
                AND pupilsightPerson.status='Full'
                ORDER BY LENGTH(pupilsightRollGroup.name), pupilsightRollGroup.name";

                $result = $connection2->prepare($sql);
                $result->execute($data);
            } catch (PDOException $e) {
                $partialFail = true;
            }

            // Proceed if we have attendance-able Roll Groups
            if ($result->rowCount() > 0) {

                try {
                    $data = array('date' => $currentDate);
                    $sql = 'SELECT pupilsightRollGroupID FROM pupilsightAttendanceLogRollGroup WHERE date=:date';
                    $resultLog = $connection2->prepare($sql);
                    $resultLog->execute($data);
                } catch (PDOException $e) {
                    $partialFail = true;
                }

                // Gather the current Roll Group logs for the day
                $log = array();
                while ($row = $resultLog->fetch()) {
                    $log[$row['pupilsightRollGroupID']] = true;
                }

                while ($row = $result->fetch()) {
                    // Skip roll groups with no students
                    if ($row['studentCount'] <= 0) continue;

                    // Check for a current log
                    if (isset($log[$row['pupilsightRollGroupID']]) == false) {

                        $rollGroupInfo = array( 'pupilsightRollGroupID' => $row['pupilsightRollGroupID'], 'name' => $row['name'] );

                        // Compile info for Admin report
                        $adminReport['rollGroup'][] = '<b>'.$row['name'] .'</b> - '. $row['preferredName'].' '.$row['surname'];

                        // Compile info for User reports
                        if ($row['pupilsightPersonIDTutor'] != '') {
                            $userReport[ $row['pupilsightPersonIDTutor'] ]['rollGroup'][] = $rollGroupInfo;
                        }
                        if ($row['pupilsightPersonIDTutor2'] != '') {
                            $userReport[ $row['pupilsightPersonIDTutor2'] ]['rollGroup'][] = $rollGroupInfo;
                        }
                        if ($row['pupilsightPersonIDTutor3'] != '') {
                            $userReport[ $row['pupilsightPersonIDTutor3'] ]['rollGroup'][] = $rollGroupInfo;
                        }
                    }
                }

                // Use the roll group counts to generate a report
                if ( isset($adminReport['rollGroup']) && count($adminReport['rollGroup']) > 0) {
                    $reportInner = implode('<br>', $adminReport['rollGroup']);
                    $report .= '<br/><br/>';
                    $report .= sprintf(__('%1$s form groups have not been registered today  (%2$s).'), count($adminReport['rollGroup']), dateConvertBack($guid, $currentDate) ).'<br/><br/>'.$reportInner;
                } else {
                    $report .= '<br/><br/>';
                    $report .= sprintf(__('All form groups have been registered today (%1$s).'), dateConvertBack($guid, $currentDate));
                }
            }
        }


        //Produce array of attendance data for Classes ------------------------------------------------------------------------------------------------------
        if ($enabledByClass == 'Y') {
            try {
                $data = array('pupilsightSchoolYearID' => $_SESSION[$guid]['pupilsightSchoolYearID'], 'date' => $currentDate, 'time' => date("H:i:s"));

                // Looks for only courses that are scheduled on the current day and have attendance='Y', also grabs tutor name
                $sql = "SELECT pupilsightCourseClass.pupilsightCourseClassID, pupilsightCourseClass.name as class, pupilsightCourse.name as course, pupilsightCourse.nameShort as courseShort,  pupilsightCourseClassPerson.pupilsightPersonID, pupilsightPerson.preferredName, pupilsightPerson.surname, (SELECT count(*) FROM pupilsightCourseClassPerson JOIN pupilsightPerson AS student ON (pupilsightCourseClassPerson.pupilsightPersonID=student.pupilsightPersonID) WHERE role='Student' AND pupilsightCourseClassID=pupilsightCourseClass.pupilsightCourseClassID AND student.status='Full' AND (student.dateStart IS NULL OR student.dateStart<=:date) AND (student.dateEnd IS NULL OR student.dateEnd>=:date)) AS studentCount
                FROM pupilsightCourseClass
                JOIN pupilsightCourseClassPerson ON (pupilsightCourseClassPerson.pupilsightCourseClassID=pupilsightCourseClass.pupilsightCourseClassID)
                JOIN pupilsightPerson ON (pupilsightCourseClassPerson.pupilsightPersonID=pupilsightPerson.pupilsightPersonID)
                JOIN pupilsightCourse ON (pupilsightCourseClass.pupilsightCourseID=pupilsightCourse.pupilsightCourseID)
                JOIN pupilsightTTDayRowClass ON (pupilsightTTDayRowClass.pupilsightCourseClassID=pupilsightCourseClass.pupilsightCourseClassID)
                JOIN pupilsightTTColumnRow ON (pupilsightTTDayRowClass.pupilsightTTColumnRowID=pupilsightTTColumnRow.pupilsightTTColumnRowID)
                JOIN pupilsightTTDayDate ON (pupilsightTTDayDate.pupilsightTTDayID=pupilsightTTDayRowClass.pupilsightTTDayID)
                LEFT JOIN pupilsightTTDayRowClassException ON (pupilsightTTDayRowClassException.pupilsightTTDayRowClassID=pupilsightTTDayRowClass.pupilsightTTDayRowClassID AND pupilsightTTDayRowClassException.pupilsightPersonID=pupilsightPerson.pupilsightPersonID)
                WHERE pupilsightTTDayDate.date=:date
                AND pupilsightTTColumnRow.timeStart<=:time
                AND pupilsightCourseClassPerson.role='Teacher'
                AND pupilsightCourse.pupilsightSchoolYearID=:pupilsightSchoolYearID
                AND pupilsightCourseClass.attendance='Y'
                AND pupilsightTTDayRowClassException.pupilsightTTDayRowClassExceptionID IS NULL
                AND pupilsightPerson.status='Full'
                ORDER BY pupilsightPerson.surname, pupilsightCourse.nameShort, pupilsightCourseClass.nameShort";

                $result = $connection2->prepare($sql);
                $result->execute($data);
            } catch (PDOException $e) {
                $partialFail = true;
            }

            // Proceed if we have attendance-able Classes
            if ($result->rowCount() > 0) {

                try {
                    $data = array('date' => $currentDate);
                    $sql = 'SELECT pupilsightCourseClassID FROM pupilsightAttendanceLogCourseClass WHERE date=:date';
                    $resultLog = $connection2->prepare($sql);
                    $resultLog->execute($data);
                } catch (PDOException $e) {
                    $partialFail = true;
                }

                // Gather the current Class logs for the day
                $log = array();
                while ($row = $resultLog->fetch()) {
                    $log[$row['pupilsightCourseClassID']] = true;
                }

                while ($row = $result->fetch()) {
                    // Skip classes with no students
                    if ($row['studentCount'] <= 0) continue;

                    // Check for a current log
                    if (isset($log[$row['pupilsightCourseClassID']]) == false) {

                        $className = $row['course'].' ('.$row['courseShort'].'.'.$row['class'].')';
                        $classInfo = array( 'pupilsightCourseClassID' => $row['pupilsightCourseClassID'], 'name' => $className );

                        // Compile info for Admin report
                        $adminReport['classes'][ $row['preferredName'].' '.$row['surname'] ][] = $className;

                        // Compile info for User reports
                        if ($row['pupilsightPersonID'] != '') {
                            $userReport[ $row['pupilsightPersonID'] ]['classes'][] = $classInfo;
                        }
                    }
                }

                // Use the class counts to generate reports
                if ( isset($adminReport['classes']) && count($adminReport['classes']) > 0) {
                    $reportInner = '';

                    // Output the reports grouped by teacher
                    foreach ($adminReport['classes'] as $teacherName => $classes) {
                        $reportInner .= '<b>' . $teacherName;
                        $reportInner .= (count($classes) > 1)? ' ('.count($classes).')</b><br/>' : '</b><br/>';
                        foreach ($classes as $className) {
                            $reportInner .= '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;' . $className .'<br/>';
                        }
                        $reportInner .= '<br>';
                    }

                    $report .= '<br/><br/>';
                    $report .= sprintf(__('%1$s classes have not been registered today (%2$s).'), count($adminReport['classes']), dateConvertBack($guid, $currentDate)).'<br/><br/>'.$reportInner;
                } else {
                    $report .= '<br/><br/>';
                    $report .= sprintf(__('All classes have been registered today (%1$s).'), dateConvertBack($guid, $currentDate));
                }
            }
        }

        // Initialize the notification sender & gateway objects
        $notificationGateway = new NotificationGateway($pdo);
        $notificationSender = new NotificationSender($notificationGateway, $pupilsight->session);

        // Raise a new notification event
        $event = new NotificationEvent('Attendance', 'Daily Attendance Summary');

        if ($event->getEventDetails($notificationGateway, 'active') == 'Y' && $partialFail == false) {
            //Notify non-completing tutors
            foreach ($userReport as $pupilsightPersonID => $items ) {

                $notificationText = __('You have not taken attendance yet today. Please do so as soon as possible.');

                if ($enabledByRollGroup == 'Y') {
                    // Output the roll groups the particular user is a part of
                    if ( isset($items['rollGroup']) && count($items['rollGroup']) > 0) {
                        $notificationText .= '<br/><br/>';
                        $notificationText .= '<b>'.__('Roll Group').':</b><br/>';
                        foreach ($items['rollGroup'] as $rollGroup) {
                            $notificationText .= '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;' . $rollGroup['name'] .'<br/>';
                        }

                    }
                }

                if ($enabledByClass == 'Y') {
                    // Output the classes the particular user is a part of
                    if ( isset($items['classes']) && count($items['classes']) > 0) {
                        $notificationText .= '<br/><br/>';
                        $notificationText .= '<b>'.__('Classes').':</b><br/>';
                        foreach ($items['classes'] as $class) {
                            $notificationText .= '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;' . $class['name'] .'<br/>';
                        }
                        $notificationText .= '<br/>';
                    }
                }

                $notificationSender->addNotification($pupilsightPersonID, $notificationText, 'Attendance', '/index.php?q=/modules/Attendance/attendance.php&currentDate='.dateConvertBack($guid, date('Y-m-d')));
            }

            // Notify Additional Users
            if (!empty($additionalUsersList)) {
                $additionalUsers = explode(',', $additionalUsersList);

                if (is_array($additionalUsers) && count($additionalUsers) > 0) {
                    foreach ($additionalUsers as $pupilsightPersonID) {
                        // Confirm that this user still has permission to access these reports
                        try {
                            $data = array( 'pupilsightPersonID' => $pupilsightPersonID, 'action1' => '%report_rollGroupsNotRegistered_byDate.php%', 'action2' => '%report_courseClassesNotRegistered_byDate.php%' );
                            $sql = "SELECT pupilsightAction.name FROM pupilsightAction, pupilsightPermission, pupilsightRole, pupilsightPerson WHERE (pupilsightAction.URLList LIKE :action1 OR pupilsightAction.URLList LIKE :action2) AND (pupilsightAction.pupilsightActionID=pupilsightPermission.pupilsightActionID) AND (pupilsightPermission.pupilsightRoleID=pupilsightRole.pupilsightRoleID) AND (pupilsightPermission.pupilsightRoleID=pupilsightPerson.pupilsightRoleIDPrimary) AND (pupilsightPerson.pupilsightPersonID=:pupilsightPersonID) AND (pupilsightAction.pupilsightModuleID=(SELECT pupilsightModuleID FROM pupilsightModule WHERE name='Attendance'))";
                            $result = $connection2->prepare($sql);
                            $result->execute($data);
                        }  catch (PDOException $e) {}

                        if ($result->rowCount() > 0) {
                            $event->addRecipient($pupilsightPersonID);
                        }
                    }
                }
            }

        } else if ($partialFail) {
            // Notify admin if there was an error in the report
            $report = __('Your request failed due to a database error.') . '<br/><br/>' . $report;
        }

        $event->setNotificationText(__('An Attendance CLI script has run.').' '.$report);
        if ($enabledByRollGroup == 'N' && $enabledByClass == 'Y') {
            $event->setActionLink('/index.php?q=/modules/Attendance/report_courseClassesNotRegistered_byDate.php');
        }
        else {
            $event->setActionLink('/index.php?q=/modules/Attendance/report_rollGroupsNotRegistered_byDate.php');
        }

        // Add admin, then push the event to the notification sender
        $event->addRecipient($_SESSION[$guid]['organisationAdministrator']);
        $event->pushNotifications($notificationGateway, $notificationSender);

        // Send all notifications
        $sendReport = $notificationSender->sendNotifications();

        // Output the result to terminal
        echo sprintf('Sent %1$s notifications: %2$s inserts, %3$s updates, %4$s emails sent, %5$s emails failed.', $sendReport['count'], $sendReport['inserts'], $sendReport['updates'], $sendReport['emailSent'], $sendReport['emailFailed'])."\n";
    }
}
