<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Comms\NotificationEvent;
use Pupilsight\Domain\RollGroups\RollGroupGateway;
use Pupilsight\Domain\Students\StudentGateway;

include '../../pupilsight.php';

$enableDescriptors = getSettingByScope($connection2, 'Behaviour', 'enableDescriptors');
$enableLevels = getSettingByScope($connection2, 'Behaviour', 'enableLevels');

$pupilsightBehaviourID = $_GET['pupilsightBehaviourID'];
$URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_POST['address'])."/behaviour_manage_edit.php&pupilsightBehaviourID=$pupilsightBehaviourID&pupilsightPersonID=".$_GET['pupilsightPersonID'].'&pupilsightRollGroupID='.$_GET['pupilsightRollGroupID'].'&pupilsightYearGroupID='.$_GET['pupilsightYearGroupID'].'&type='.$_GET['type'];

if (isActionAccessible($guid, $connection2, '/modules/Behaviour/behaviour_manage_edit.php') == false) {
    $URL .= '&return=error0';
    header("Location: {$URL}");
} else {
    $highestAction = getHighestGroupedAction($guid, $_POST['address'], $connection2);
    if ($highestAction == false) {
        $URL .= "&return=error0$params";
        header("Location: {$URL}");
    } else {
        //Proceed!
        //Check if school year specified
        if ($pupilsightBehaviourID == '') {
            $URL .= '&return=error1';
            header("Location: {$URL}");
        } else {
            try {
                if ($highestAction == 'Manage Behaviour Records_all') {
                    $data = array('pupilsightSchoolYearID' => $_SESSION[$guid]['pupilsightSchoolYearID'], 'pupilsightBehaviourID' => $pupilsightBehaviourID);
                    $sql = 'SELECT pupilsightBehaviour.*, student.surname AS surnameStudent, student.preferredName AS preferredNameStudent, creator.surname AS surnameCreator, creator.preferredName AS preferredNameCreator, creator.title FROM pupilsightBehaviour JOIN pupilsightPerson AS student ON (pupilsightBehaviour.pupilsightPersonID=student.pupilsightPersonID) JOIN pupilsightPerson AS creator ON (pupilsightBehaviour.pupilsightPersonIDCreator=creator.pupilsightPersonID) WHERE pupilsightSchoolYearID=:pupilsightSchoolYearID AND pupilsightBehaviourID=:pupilsightBehaviourID ORDER BY date DESC';
                } elseif ($highestAction == 'Manage Behaviour Records_my') {
                    $data = array('pupilsightSchoolYearID' => $_SESSION[$guid]['pupilsightSchoolYearID'], 'pupilsightBehaviourID' => $pupilsightBehaviourID, 'pupilsightPersonID' => $_SESSION[$guid]['pupilsightPersonID']);
                    $sql = 'SELECT pupilsightBehaviour.*, student.surname AS surnameStudent, student.preferredName AS preferredNameStudent, creator.surname AS surnameCreator, creator.preferredName AS preferredNameCreator, creator.title FROM pupilsightBehaviour JOIN pupilsightPerson AS student ON (pupilsightBehaviour.pupilsightPersonID=student.pupilsightPersonID) JOIN pupilsightPerson AS creator ON (pupilsightBehaviour.pupilsightPersonIDCreator=creator.pupilsightPersonID) WHERE pupilsightSchoolYearID=:pupilsightSchoolYearID AND pupilsightBehaviourID=:pupilsightBehaviourID AND pupilsightPersonIDCreator=:pupilsightPersonID ORDER BY date DESC';
                }
                $result = $connection2->prepare($sql);
                $result->execute($data);
            } catch (PDOException $e) {
                $URL .= '&return=error2';
                header("Location: {$URL}");
                exit();
            }

            if ($result->rowCount() != 1) {
                $URL .= '&return=error2';
                header("Location: {$URL}");
            } else {
                $behaviourRecord = $result->fetch();

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
                if ($_POST['pupilsightPlannerEntryID'] == '') {
                    $pupilsightPlannerEntryID = null;
                } else {
                    $pupilsightPlannerEntryID = $_POST['pupilsightPlannerEntryID'];
                }

                if ($pupilsightPersonID == '' or $date == '' or $type == '' or ($descriptor == '' and $enableDescriptors == 'Y')) {
                    $URL .= '&return=error1';
                    header("Location: {$URL}");
                } else {
                    try {
                        $data = array('pupilsightPersonID' => $pupilsightPersonID, 'date' => dateConvert($guid, $date), 'type' => $type, 'descriptor' => $descriptor, 'level' => $level, 'comment' => $comment, 'followup' => $followup, 'pupilsightPlannerEntryID' => $pupilsightPlannerEntryID, 'pupilsightSchoolYearID' => $_SESSION[$guid]['pupilsightSchoolYearID'], 'pupilsightBehaviourID' => $pupilsightBehaviourID);
                        $sql = 'UPDATE pupilsightBehaviour SET pupilsightPersonID=:pupilsightPersonID, date=:date, type=:type, descriptor=:descriptor, level=:level, comment=:comment, followup=:followup, pupilsightPlannerEntryID=:pupilsightPlannerEntryID, pupilsightSchoolYearID=:pupilsightSchoolYearID WHERE pupilsightBehaviourID=:pupilsightBehaviourID';
                        $result = $connection2->prepare($sql);
                        $result->execute($data);
                    } catch (PDOException $e) {
                        $URL .= '&return=error2';
                        header("Location: {$URL}");
                        exit();
                    }

                    // Send a notification to student's tutors and anyone subscribed to the notification event
                    $studentGateway = $container->get(StudentGateway::class);
                    $rollGroupGateway = $container->get(RollGroupGateway::class);

                    $student = $studentGateway->selectActiveStudentByPerson($_SESSION[$guid]['pupilsightSchoolYearID'], $pupilsightPersonID)->fetch();
                    if (!empty($student)) {
                        $studentName = formatName('', $student['preferredName'], $student['surname'], 'Student', false);
                        $editorName = formatName('', $_SESSION[$guid]['preferredName'], $_SESSION[$guid]['surname'], 'Staff', false);
                        $actionLink = "/index.php?q=/modules/Behaviour/behaviour_manage_edit.php&pupilsightPersonID=$pupilsightPersonID&pupilsightRollGroupID=&pupilsightYearGroupID=&type=$type&pupilsightBehaviourID=$pupilsightBehaviourID";

                        // Raise a new notification event
                        $event = new NotificationEvent('Behaviour', 'Updated Behaviour Record');

                        $event->setNotificationText(sprintf(__('A %1$s behaviour record for %2$s has been updated by %3$s.'), strtolower($type), $studentName, $editorName));
                        $event->setActionLink($actionLink);

                        $event->addScope('pupilsightPersonIDStudent', $pupilsightPersonID);
                        $event->addScope('pupilsightYearGroupID', $student['pupilsightYearGroupID']);

                        // Add the person who created the behaviour record, if edited by someone else
                        if ($behaviourRecord['pupilsightPersonIDCreator'] != $_SESSION[$guid]['pupilsightPersonID']) {
                            $event->addRecipient($behaviourRecord['pupilsightPersonIDCreator']);
                        }

                        // Add direct notifications to roll group tutors
                        $tutors = $rollGroupGateway->selectTutorsByRollGroup($student['pupilsightRollGroupID'])->fetchAll();
                        foreach ($tutors as $tutor) {
                            $event->addRecipient($tutor['pupilsightPersonID']);
                        }

                        $event->sendNotificationsAsBcc($pdo, $pupilsight->session);
                    }
                    
                    $URL .= '&return=success0';
                    header("Location: {$URL}");
                }
            }
        }
    }
}
