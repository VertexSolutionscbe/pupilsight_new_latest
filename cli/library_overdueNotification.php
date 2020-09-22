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
if (!isCommandLineInterface()) {
	print __("This script cannot be run from a browser, only via CLI.") ;
}
else {
    //SCAN THROUGH ALL OVERDUE LOANS
    $today = date('Y-m-d');

    try {
        $data = array('today' => $today);
        $sql = "SELECT pupilsightLibraryItem.*, surname, preferredName, email FROM pupilsightLibraryItem JOIN pupilsightPerson ON (pupilsightLibraryItem.pupilsightPersonIDStatusResponsible=pupilsightPerson.pupilsightPersonID) WHERE pupilsightLibraryItem.status='On Loan' AND borrowable='Y' AND returnExpected<:today AND pupilsightPerson.status='Full' ORDER BY surname, preferredName";
        $result = $connection2->prepare($sql);
        $result->execute($data);
    } catch (PDOException $e) {
    }

    // Initialize the notification sender & gateway objects
    $notificationGateway = new NotificationGateway($pdo);
    $notificationSender = new NotificationSender($notificationGateway, $pupilsight->session);

    // Raise a new notification event
    $event = new NotificationEvent('Library', 'Overdue Loan Items');

    if ($event->getEventDetails($notificationGateway, 'active') == 'Y') {
        if ($result->rowCount() > 0) {
            while ($row = $result->fetch()) { //For every student
                $notificationText = sprintf(__('You have an overdue loan item that needs to be returned (%1$s).'), $row['name']);
                $notificationSender->addNotification($row['pupilsightPersonIDStatusResponsible'], $notificationText, 'Library', '/index.php?q=/modules/Library/library_browse.php&pupilsightLibraryItemID='.$row['pupilsightLibraryItemID']);
            }
        }
    }

    $event->setNotificationText(sprintf(__('A Library Overdue Items CLI script has run, notifying %1$s users.'), $notificationSender->getNotificationCount()));
    $event->setActionLink('/index.php?q=/modules/Attendance/report_rollGroupsNotRegistered_byDate.php');

    // Push the event to the notification sender
    $event->pushNotifications($notificationGateway, $notificationSender);

    // Send all notifications
    $sendReport = $notificationSender->sendNotifications();

    // Output the result to terminal
    echo sprintf('Sent %1$s notifications: %2$s inserts, %3$s updates, %4$s emails sent, %5$s emails failed.', $sendReport['count'], $sendReport['inserts'], $sendReport['updates'], $sendReport['emailSent'], $sendReport['emailFailed'])."\n";
}
?>
