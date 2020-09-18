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

$URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_POST['address']).'/behaviour_manage_add.php&pupilsightPersonID='.$_GET['pupilsightPersonID'].'&pupilsightRollGroupID='.$_GET['pupilsightRollGroupID'].'&pupilsightYearGroupID='.$_GET['pupilsightYearGroupID'].'&type='.$_GET['type'];

if (isActionAccessible($guid, $connection2, '/modules/Behaviour/behaviour_manage_add.php') == false) {
    $URL .= '&return=error0&step=1';
    header("Location: {$URL}");
} else {
    $highestAction = getHighestGroupedAction($guid, $_POST['address'], $connection2);
    if ($highestAction == false) {
        $URL .= '&return=error0&step=1';
        header("Location: {$URL}");
    } else {
        $step = null;
        if (isset($_GET['step'])) {
            $step = $_GET['step'];
        }
        if ($step != 1 and $step != 2) {
            $step = 1;
        }
        $pupilsightBehaviourID = null;
        if (isset($_POST['pupilsightBehaviourID'])) {
            $pupilsightBehaviourID = $_POST['pupilsightBehaviourID'];
        }

        //Step 1
        if ($step == 1 or $pupilsightBehaviourID == null) {
            //Proceed!
            $pupilsightPersonID = $_POST['pupilsightPersonID'];
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

            if ($pupilsightPersonID == '' or $date == '' or $type == '' or ($descriptor == '' and $enableDescriptors == 'Y')) {
                $URL .= '&return=error1&step=1';
                header("Location: {$URL}");
            } else {
                //Write to database
                try {
                    $data = array('pupilsightPersonID' => $pupilsightPersonID, 'date' => dateConvert($guid, $date), 'type' => $type, 'descriptor' => $descriptor, 'level' => $level, 'comment' => $comment, 'followup' => $followup, 'pupilsightPersonIDCreator' => $_SESSION[$guid]['pupilsightPersonID'], 'pupilsightSchoolYearID' => $_SESSION[$guid]['pupilsightSchoolYearID']);
                    $sql = 'INSERT INTO pupilsightBehaviour SET pupilsightPersonID=:pupilsightPersonID, date=:date, type=:type, descriptor=:descriptor, level=:level, comment=:comment, followup=:followup, pupilsightPersonIDCreator=:pupilsightPersonIDCreator, pupilsightSchoolYearID=:pupilsightSchoolYearID';
                    $result = $connection2->prepare($sql);
                    $result->execute($data);
                } catch (PDOException $e) {
                    $URL .= '&return=erorr2&step=1';
                    header("Location: {$URL}");
                    exit();
                }

                //Last insert ID
                $AI = str_pad($connection2->lastInsertID(), 12, '0', STR_PAD_LEFT);

                $pupilsightBehaviourID = $connection2->lastInsertID();

                //Attempt to notify tutor(s) on negative behaviour
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

                        // Initialize the notification sender & gateway objects
                        $notificationGateway = new NotificationGateway($pdo);
                        $notificationSender = new NotificationSender($notificationGateway, $pupilsight->session);

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

                        // Send all notifications
                        $notificationSender->sendNotifications();
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

                    if (!$inserted) {
                        $URL .= "&return=warning1&step=2&pupilsightBehaviourID=$pupilsightBehaviourID&editID=$AI";
                        header("Location: {$URL}");
                        exit;
                    }
                }

                $URL .= "&return=success1&step=2&pupilsightBehaviourID=$pupilsightBehaviourID&editID=$AI";
                header("Location: {$URL}");
            }
        } elseif ($step == 2 and $pupilsightBehaviourID != null) {
            //Proceed!
            $pupilsightPersonID = $_POST['pupilsightPersonID'];
            if ($_POST['pupilsightPlannerEntryID'] == '') {
                $pupilsightPlannerEntryID = null;
            } else {
                $pupilsightPlannerEntryID = $_POST['pupilsightPlannerEntryID'];
            }
            $AI = '';
            if (isset($_GET['editID'])) {
                $AI = $_GET['editID'];
            }

            if ($pupilsightPersonID == '') {
                $URL .= '&return=error1';
                header("Location: {$URL}");
            } else {
                try {
                    $data = array('pupilsightSchoolYearID' => $_SESSION[$guid]['pupilsightSchoolYearID'], 'pupilsightBehaviourID' => $pupilsightBehaviourID, 'pupilsightPersonID' => $pupilsightPersonID);
                    $sql = "SELECT * FROM pupilsightBehaviour JOIN pupilsightPerson ON (pupilsightBehaviour.pupilsightPersonID=pupilsightPerson.pupilsightPersonID) JOIN pupilsightStudentEnrolment ON (pupilsightPerson.pupilsightPersonID=pupilsightStudentEnrolment.pupilsightPersonID) JOIN pupilsightRollGroup ON (pupilsightStudentEnrolment.pupilsightRollGroupID=pupilsightRollGroup.pupilsightRollGroupID) WHERE pupilsightRollGroup.pupilsightSchoolYearID=:pupilsightSchoolYearID AND status='Full' AND (dateStart IS NULL OR dateStart<='".date('Y-m-d')."') AND (dateEnd IS NULL  OR dateEnd>='".date('Y-m-d')."') AND pupilsightBehaviourID=:pupilsightBehaviourID AND pupilsightBehaviour.pupilsightPersonID=:pupilsightPersonID";
                    $result = $connection2->prepare($sql);
                    $result->execute($data);
                } catch (PDOException $e) {
                    $URL .= '&return=warning0&step=2';
                    header("Location: {$URL}");
                    exit();
                }
                if ($result->rowCount() != 1) {
                    $URL .= '&return=error2&step=2';
                    header("Location: {$URL}");
                    exit();
                } else {
                    //Write to database
                    try {
                        $data = array('pupilsightPlannerEntryID' => $pupilsightPlannerEntryID, 'pupilsightBehaviourID' => $pupilsightBehaviourID);
                        $sql = 'UPDATE pupilsightBehaviour SET pupilsightPlannerEntryID=:pupilsightPlannerEntryID WHERE pupilsightBehaviourID=:pupilsightBehaviourID';
                        $result = $connection2->prepare($sql);
                        $result->execute($data);
                    } catch (PDOException $e) {
                        $URL .= '&return=warning0&step=2';
                        header("Location: {$URL}");
                        exit();
                    }

                    $URL .= "&return=success0&editID=$pupilsightBehaviourID";
                    header("Location: {$URL}");
                }
            }
        }
    }
}
