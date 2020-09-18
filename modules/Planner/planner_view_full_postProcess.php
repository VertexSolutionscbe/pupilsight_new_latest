<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Comms\NotificationSender;
use Pupilsight\Domain\System\NotificationGateway;

//Pupilsight system-wide includes
include '../../pupilsight.php';

//Module includes
include './moduleFunctions.php';

$pupilsightPlannerEntryID = $_POST['pupilsightPlannerEntryID'];
$URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_POST['address'])."/planner_view_full.php&pupilsightPlannerEntryID=$pupilsightPlannerEntryID&search=".$_POST['search'].$_POST['params'];

if (isActionAccessible($guid, $connection2, '/modules/Planner/planner_view_full.php') == false) {
    $URL .= '&return=error0';
    header("Location: {$URL}");
} else {
    $highestAction = getHighestGroupedAction($guid, $_POST['address'], $connection2);
    if ($highestAction == false) {
        $URL .= "&return=error0$params";
        header("Location: {$URL}");
    } else {
        //Proceed!
        //Check if planner specified
        if ($pupilsightPlannerEntryID == '') {
            $URL .= '&return=error1';
            header("Location: {$URL}");
        } else {
            try {
                $data = array('pupilsightPlannerEntryID' => $pupilsightPlannerEntryID);
                $sql = 'SELECT * FROM pupilsightPlannerEntry WHERE pupilsightPlannerEntryID=:pupilsightPlannerEntryID';
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
                $row = $result->fetch();

                //INSERT
                $replyTo = $_POST['replyTo'];
                if ($_POST['replyTo'] == '') {
                    $replyTo = null;
                }
                //Attempt to prevent XSS attack
                $comment = $_POST['comment'];
                $comment = tinymceStyleStripTags($comment, $connection2);

                try {
                    $dataInsert = array('pupilsightPlannerEntryID' => $pupilsightPlannerEntryID, 'pupilsightPersonID' => $_SESSION[$guid]['pupilsightPersonID'], 'comment' => $comment, 'replyTo' => $replyTo);
                    $sqlInsert = 'INSERT INTO pupilsightPlannerEntryDiscuss SET pupilsightPlannerEntryID=:pupilsightPlannerEntryID, pupilsightPersonID=:pupilsightPersonID, comment=:comment, pupilsightPlannerEntryDiscussIDReplyTo=:replyTo';
                    $resultInsert = $connection2->prepare($sqlInsert);
                    $resultInsert->execute($dataInsert);
                } catch (PDOException $e) {
                    $URL .= '&return=error2';
                    header("Location: {$URL}");
                    exit();
                }

                //Work out who we are replying too
                $replyToID = null;
                $dataClassGroup = array('pupilsightPlannerEntryDiscussID' => $replyTo);
                $sqlClassGroup = 'SELECT * FROM pupilsightPlannerEntryDiscuss WHERE pupilsightPlannerEntryDiscussID=:pupilsightPlannerEntryDiscussID';
                $resultClassGroup = $connection2->prepare($sqlClassGroup);
                $resultClassGroup->execute($dataClassGroup);
                if ($resultClassGroup->rowCount() == 1) {
                    $rowClassGroup = $resultClassGroup->fetch();
                    $replyToID = $rowClassGroup['pupilsightPersonID'];
                }

                // Initialize the notification sender & gateway objects
                $notificationGateway = new NotificationGateway($pdo);
                $notificationSender = new NotificationSender($notificationGateway, $pupilsight->session);

                //Create notification for all people in class except me
                $dataClassGroup = array('pupilsightCourseClassID' => $row['pupilsightCourseClassID']);
                $sqlClassGroup = "SELECT * FROM pupilsightCourseClassPerson INNER JOIN pupilsightPerson ON pupilsightCourseClassPerson.pupilsightPersonID=pupilsightPerson.pupilsightPersonID WHERE pupilsightCourseClassID=:pupilsightCourseClassID AND status='Full' AND (dateStart IS NULL OR dateStart<='".date('Y-m-d')."') AND (dateEnd IS NULL  OR dateEnd>='".date('Y-m-d')."') AND (NOT role='Student - Left') AND (NOT role='Teacher - Left') ORDER BY role DESC, surname, preferredName";
                $resultClassGroup = $connection2->prepare($sqlClassGroup);
                $resultClassGroup->execute($dataClassGroup);
                while ($rowClassGroup = $resultClassGroup->fetch()) {
                    if ($rowClassGroup['pupilsightPersonID'] != $_SESSION[$guid]['pupilsightPersonID'] and $rowClassGroup['pupilsightPersonID'] != $replyToID) {
                        $notificationText = sprintf(__('Someone has commented on your lesson plan "%1$s".'), $row['name']);

                        $notificationSender->addNotification($rowClassGroup['pupilsightPersonID'], $notificationText, 'Planner', "/index.php?q=/modules/Planner/planner_view_full.php&pupilsightPlannerEntryID=$pupilsightPlannerEntryID&viewBy=date&date=".$row['date'].'&pupilsightCourseClassID=&search=#chat');
                    }
                }

                $notificationSender->sendNotificationsAsBcc();

                //Create notification to person I am replying to
                if (is_null($replyToID) == false) {
                    $notificationText = sprintf(__('Someone has replied to a comment you made on lesson plan "%1$s".'), $row['name']);
                    $notificationSender->addNotification($replyToID, $notificationText, 'Planner', "/index.php?q=/modules/Planner/planner_view_full.php&pupilsightPlannerEntryID=$pupilsightPlannerEntryID&viewBy=date&date=".$row['date'].'&pupilsightCourseClassID=&search=#chat');

                    $notificationSender->sendNotificationsAsBcc();
                }

                $URL .= '&return=success0';
                header("Location: {$URL}");
            }
        }
    }
}
