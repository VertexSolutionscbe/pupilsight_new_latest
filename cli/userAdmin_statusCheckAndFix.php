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
    $count = 0;

    //Scan through every user to correct own status
    try {
        $data = array('pupilsightSchoolYearID' => $_SESSION[$guid]['pupilsightSchoolYearID']);
        $sql = 'SELECT pupilsightPersonID, status, dateEnd, dateStart, pupilsightRoleIDAll FROM pupilsightPerson ORDER BY pupilsightPersonID';
        $result = $connection2->prepare($sql);
        $result->execute($data);
    } catch (PDOException $e) {
    }

    while ($row = $result->fetch()) {
        //Check for status=='Expected' when met or exceeded start date and set to 'Full'
        if ($row['dateStart'] != '' and date('Y-m-d') >= $row['dateStart'] and $row['status'] == 'Expected') {
            try {
                $dataUpdate = array('pupilsightPersonID' => $row['pupilsightPersonID']);
                $sqlUpdate = "UPDATE pupilsightPerson SET status='Full' WHERE pupilsightPersonID=:pupilsightPersonID";
                $resultUpdate = $connection2->prepare($sqlUpdate);
                $resultUpdate->execute($dataUpdate);
            } catch (PDOException $e) {
            }
            ++$count;
        }

        //Check for status=='Full' when end date exceeded, and set to 'Left'
        if ($row['dateEnd'] != '' and date('Y-m-d') > $row['dateEnd'] and $row['status'] == 'Full') {
            try {
                $dataUpdate = array('pupilsightPersonID' => $row['pupilsightPersonID']);
                $sqlUpdate = "UPDATE pupilsightPerson SET status='Left' WHERE pupilsightPersonID=:pupilsightPersonID";
                $resultUpdate = $connection2->prepare($sqlUpdate);
                $resultUpdate->execute($dataUpdate);
            } catch (PDOException $e) {
            }
            ++$count;
        }
    }

    // Look for parents who are set to Full and counts the active children (also catches parents with no children)
    try {
        $data = array();
        $sql = "SELECT adult.pupilsightPersonID,
                COUNT(DISTINCT CASE WHEN NOT child.status='Left' THEN child.pupilsightPersonID END) as activeChildren
                FROM pupilsightPerson as adult
                JOIN pupilsightFamilyAdult ON (adult.pupilsightPersonID=pupilsightFamilyAdult.pupilsightPersonID)
                LEFT JOIN pupilsightFamilyChild ON (pupilsightFamilyChild.pupilsightFamilyID=pupilsightFamilyAdult.pupilsightFamilyID)
                LEFT JOIN pupilsightPerson as child ON (child.pupilsightPersonID=pupilsightFamilyChild.pupilsightPersonID)
                WHERE adult.status='Full'
                GROUP BY adult.pupilsightPersonID";
        $result = $connection2->prepare($sql);
        $result->execute($data);
    } catch (PDOException $e) {
    }

    while ($row = $result->fetch()) {
        // Skip parents who have any active children
        if ($row['activeChildren'] > 0) continue;

        // Mark parents as Left only if they don't have other non-parent roles
        try {
            $data = array('pupilsightPersonID' => $row['pupilsightPersonID']);
            $sql = "UPDATE pupilsightPerson SET pupilsightPerson.status='Left' 
                    WHERE pupilsightPerson.pupilsightPersonID=:pupilsightPersonID 
                    AND (SELECT COUNT(*) FROM pupilsightRole WHERE FIND_IN_SET(pupilsightRole.pupilsightRoleID, pupilsightPerson.pupilsightRoleIDAll) AND category<>'Parent') = 0";
            $resultUpdate = $connection2->prepare($sql);
            $resultUpdate->execute($data);
        } catch (PDOException $e) {
        }

        // Add the number of updated rows to the count
        $count += $resultUpdate->rowCount();
    }

    // Raise a new notification event
    $event = new NotificationEvent('User Admin', 'User Status Check and Fix');

    $event->setNotificationText(sprintf(__('A User Admin CLI script has run, updating %1$s users.'), $count));
    $event->setActionLink('/index.php?q=/modules/User Admin/user_manage.php');

    //Notify admin
    $event->addRecipient($_SESSION[$guid]['organisationAdministrator']);

    // Send all notifications
    $sendReport = $event->sendNotifications($pdo, $pupilsight->session);

    // Output the result to terminal
    echo sprintf('Sent %1$s notifications: %2$s inserts, %3$s updates, %4$s emails sent, %5$s emails failed.', $sendReport['count'], $sendReport['inserts'], $sendReport['updates'], $sendReport['emailSent'], $sendReport['emailFailed'])."\n";
}
