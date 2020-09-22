<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Comms\NotificationEvent;

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

        try {
            $data = array('date' => $currentDate, 'pupilsightSchoolYearID' => $_SESSION[$guid]['pupilsightSchoolYearID']);

            $sql = "SELECT pupilsightRollGroup.nameShort AS rollGroup, pupilsightStudentEnrolment.pupilsightYearGroupID, pupilsightBehaviour.pupilsightBehaviourID, pupilsightPerson.pupilsightPersonID, pupilsightPerson.surname, pupilsightPerson.preferredName, staff.surname as staffSurname, staff.preferredName as staffPreferredName, pupilsightBehaviour.descriptor, pupilsightBehaviour.level, pupilsightBehaviour.comment, pupilsightBehaviour.followup, pupilsightBehaviour.timestamp
                    FROM pupilsightBehaviour
                    JOIN pupilsightPerson ON (pupilsightBehaviour.pupilsightPersonID=pupilsightPerson.pupilsightPersonID)
                    JOIN pupilsightStudentEnrolment ON (pupilsightPerson.pupilsightPersonID=pupilsightStudentEnrolment.pupilsightPersonID)
                    JOIN pupilsightRollGroup ON (pupilsightStudentEnrolment.pupilsightRollGroupID=pupilsightRollGroup.pupilsightRollGroupID)
                    JOIN pupilsightPerson as staff ON (pupilsightBehaviour.pupilsightPersonIDCreator=staff.pupilsightPersonID)
                    WHERE pupilsightBehaviour.pupilsightSchoolYearID=:pupilsightSchoolYearID
                    AND pupilsightBehaviour.type='Negative'
                    AND pupilsightBehaviour.date=:date
                    AND pupilsightPerson.status='Full'
                    AND pupilsightRollGroup.pupilsightSchoolYearID=pupilsightBehaviour.pupilsightSchoolYearID
                    GROUP BY pupilsightBehaviour.pupilsightBehaviourID
                    ORDER BY pupilsightBehaviour.timestamp";

            $result = $connection2->prepare($sql);
            $result->execute($data);
        } catch (PDOException $e) {
            $partialFail = true;
        }

        if ($result && $result->rowCount() > 0) {
            $report .= __('Daily Behaviour Summary').': '.$result->rowCount().' '.__('Records').'<br/><br/>';
            while ($row = $result->fetch()) {

                $studentName = formatName('', $row['preferredName'], $row['surname'], 'Student', false);
                $staffName = formatName('', $row['staffPreferredName'], $row['staffSurname'], 'Staff', false, true);

                $report .= date('g:i a', strtotime($row['timestamp'])).' - '.__('Negative').' '.__('Behaviour').' - '.$row['level'];
                $report .= '<br/>';

                $report .= sprintf(__('%1$s (%2$s) received a report for %3$s from %4$s'), '<b>'.$studentName.'</b>', $row['rollGroup'], $row['descriptor'], $staffName);
                $report .= ' &raquo; <a href="'.$_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/Behaviour/behaviour_manage_edit.php&pupilsightBehaviourID='.$row['pupilsightBehaviourID'].'&pupilsightPersonID='.$row['pupilsightPersonID'].'&pupilsightRollGroupID=&pupilsightYearGroupID=&type=">'.__('View').'</a>';

                $report .= '<br/><br/>';
            }

            // Raise a new notification event
            $event = new NotificationEvent('Behaviour', 'Daily Behaviour Summary');

            $event->setNotificationText(__('A Behaviour CLI script has run.').'<br/><br/>'.$report);
            $event->setActionLink('/index.php?q=/modules/Behaviour/behaviour_pattern.php&minimumCount=1&fromDate='.dateConvertBack($guid, $currentDate));

            // Send all notifications
            $sendReport = $event->sendNotifications($pdo, $pupilsight->session);

            // Output the result to terminal
            echo sprintf('Sent %1$s notifications: %2$s inserts, %3$s updates, %4$s emails sent, %5$s emails failed.', $sendReport['count'], $sendReport['inserts'], $sendReport['updates'], $sendReport['emailSent'], $sendReport['emailFailed'])."\n";

        } else {
            // Output the result to terminal
            echo __('There are no records to display.')."\n";
        }
    }
}
