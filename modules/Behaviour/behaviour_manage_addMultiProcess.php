<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Comms\NotificationEvent;
use Pupilsight\Comms\NotificationSender;
use Pupilsight\Domain\System\NotificationGateway;
use Pupilsight\Domain\Students\StudentNoteGateway;

include '../../pupilsight.php';

$enableDescriptors = getSettingByScope($connection2, 'Behaviour', 'enableDescriptors');
$enableLevels = getSettingByScope($connection2, 'Behaviour', 'enableLevels');

$URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_POST['address']).'/behaviour_manage_addMulti.php&pupilsightPersonID='.$_GET['pupilsightPersonID'].'&pupilsightRollGroupID='.$_GET['pupilsightRollGroupID'].'&pupilsightYearGroupID='.$_GET['pupilsightYearGroupID'].'&type='.$_GET['type'];

if (isActionAccessible($guid, $connection2, '/modules/Behaviour/behaviour_manage_add.php') == false) {
    $URL .= '&return=error0';
    header("Location: {$URL}");
} else {
    //Proceed!
    if (isset($_POST['pupilsightPersonIDMulti'])) {
        $pupilsightPersonIDMulti = $_POST['pupilsightPersonIDMulti'];
    } else {
        $pupilsightPersonIDMulti = null;
    }
    $date = $_POST['date'];
    $type = $_POST['type'];
    $descriptor = null;
    if (isset($_POST['descriptor'])) {
        $descriptor = $_POST['descriptor'];
    }
    $level = null;
    if (isset($_POST['level'])) {
        $level = $_POST['level'];
    }
    $comment = $_POST['comment'];
    $followup = $_POST['followup'];
    $copyToNotes = $_POST['copyToNotes'] ?? null;

    if (is_null($pupilsightPersonIDMulti) == true or $date == '' or $type == '' or ($descriptor == '' and $enableDescriptors == 'Y')) {
        $URL .= '&return=error1';
        header("Location: {$URL}");
    } else {
        $partialFail = false;

        // Initialize the notification sender & gateway objects
        $notificationGateway = new NotificationGateway($pdo);
        $notificationSender = new NotificationSender($notificationGateway, $pupilsight->session);

        foreach ($pupilsightPersonIDMulti as $pupilsightPersonID) {
            //Write to database
            try {
                $data = array('pupilsightPersonID' => $pupilsightPersonID, 'date' => dateConvert($guid, $date), 'type' => $type, 'descriptor' => $descriptor, 'level' => $level, 'comment' => $comment, 'followup' => $followup, 'pupilsightPersonIDCreator' => $_SESSION[$guid]['pupilsightPersonID'], 'pupilsightSchoolYearID' => $_SESSION[$guid]['pupilsightSchoolYearID']);
                $sql = 'INSERT INTO pupilsightBehaviour SET pupilsightPersonID=:pupilsightPersonID, date=:date, type=:type, descriptor=:descriptor, level=:level, comment=:comment, followup=:followup, pupilsightPersonIDCreator=:pupilsightPersonIDCreator, pupilsightSchoolYearID=:pupilsightSchoolYearID';
                $result = $connection2->prepare($sql);
                $result->execute($data);
            } catch (PDOException $e) {
                $partialFail = true;
            }

            $pupilsightBehaviourID = $connection2->lastInsertID();

            if ($type == 'Negative') {
                try {
                    $dataDetail = array('pupilsightSchoolYearID' => $_SESSION[$guid]['pupilsightSchoolYearID'], 'pupilsightPersonID' => $pupilsightPersonID);
                    $sqlDetail = 'SELECT pupilsightPersonIDTutor, pupilsightPersonIDTutor2, pupilsightPersonIDTutor3, surname, preferredName, pupilsightStudentEnrolment.pupilsightYearGroupID FROM pupilsightRollGroup JOIN pupilsightStudentEnrolment ON (pupilsightStudentEnrolment.pupilsightRollGroupID=pupilsightRollGroup.pupilsightRollGroupID) JOIN pupilsightPerson ON (pupilsightStudentEnrolment.pupilsightPersonID=pupilsightPerson.pupilsightPersonID) WHERE pupilsightStudentEnrolment.pupilsightSchoolYearID=:pupilsightSchoolYearID AND pupilsightStudentEnrolment.pupilsightPersonID=:pupilsightPersonID';
                    $resultDetail = $connection2->prepare($sqlDetail);
                    $resultDetail->execute($dataDetail);
                } catch (PDOException $e) {
                    echo "<div class='alert alert-danger'>".$e->getMessage().'</div>';
                }
                if ($resultDetail->rowCount() == 1) {
                    $rowDetail = $resultDetail->fetch();

                    $studentName = formatName('', $rowDetail['preferredName'], $rowDetail['surname'], 'Student', false);
                    $actionLink = "/index.php?q=/modules/Behaviour/behaviour_view_details.php&pupilsightPersonID=$pupilsightPersonID&search=";

                    // Raise a new notification event
                    $event = new NotificationEvent('Behaviour', 'New Negative Record');

                    $event->setNotificationText(sprintf(__('Someone has created a negative behaviour record for %1$s.'), $studentName));
                    $event->setActionLink($actionLink);

                    $event->addScope('pupilsightPersonIDStudent', $pupilsightPersonID);
                    $event->addScope('pupilsightYearGroupID', $rowDetail['pupilsightYearGroupID']);

                    // Add event listeners to the notification sender
                    $event->pushNotifications($notificationGateway, $notificationSender);

                    // Add direct notifications to roll group tutors
                    if ($event->getEventDetails($notificationGateway, 'active') == 'Y') {
                        $notificationText = sprintf(__('Someone has created a negative behaviour record for your tutee, %1$s.'), $studentName);

                        if ($rowDetail['pupilsightPersonIDTutor'] != null and $rowDetail['pupilsightPersonIDTutor'] != $_SESSION[$guid]['pupilsightPersonID']) {
                            $notificationSender->addNotification($rowDetail['pupilsightPersonIDTutor'], $notificationText, 'Behaviour', $actionLink);
                        }
                        if ($rowDetail['pupilsightPersonIDTutor2'] != null and $rowDetail['pupilsightPersonIDTutor2'] != $_SESSION[$guid]['pupilsightPersonID']) {
                            $notificationSender->addNotification($rowDetail['pupilsightPersonIDTutor2'], $notificationText, 'Behaviour', $actionLink);
                        }
                        if ($rowDetail['pupilsightPersonIDTutor3'] != null and $rowDetail['pupilsightPersonIDTutor3'] != $_SESSION[$guid]['pupilsightPersonID']) {
                            $notificationSender->addNotification($rowDetail['pupilsightPersonIDTutor3'], $notificationText, 'Behaviour', $actionLink);
                        }
                    }
                }
            }

            if ($copyToNotes == 'on') {
                //Write to notes
                $noteGateway = $container->get(StudentNoteGateway::class);
                $note = [
                    'title'                       => __('Behaviour').': '.$descriptor,
                    'note'                        => empty($followup) ? $comment : $comment.' <br/><br/>'.$followup,
                    'pupilsightPersonID'              => $pupilsightPersonID,
                    'pupilsightPersonIDCreator'       => $_SESSION[$guid]['pupilsightPersonID'],
                    'pupilsightStudentNoteCategoryID' => $noteGateway->getNoteCategoryIDByName('Behaviour') ?? null,
                    'timestamp'                   => date('Y-m-d H:i:s', time()),
                ];
                
                $inserted = $noteGateway->insert($note);

                if (!$inserted) $partialFail = true;
            }
        }

        // Send all notifications
        $notificationSender->sendNotifications();

        if ($partialFail == true) {
            $URL .= '&return=warning1';
            header("Location: {$URL}");
        } else {
            $URL .= '&return=success0';
            header("Location: {$URL}");
        }
    }
}
