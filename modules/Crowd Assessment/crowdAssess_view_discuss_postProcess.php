<?php
/*
Pupilsight, Flexible & Open School System
*/

include '../../pupilsight.php';

//Module includes
require_once __DIR__ . '/moduleFunctions.php';

$pupilsightPlannerEntryID = $_GET['pupilsightPlannerEntryID'];
$pupilsightPlannerEntryHomeworkID = $_GET['pupilsightPlannerEntryHomeworkID'];
$pupilsightPersonID = $_GET['pupilsightPersonID'];

$URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_GET['address'])."/crowdAssess_view_discuss.php&pupilsightPlannerEntryID=$pupilsightPlannerEntryID&pupilsightPlannerEntryHomeworkID=$pupilsightPlannerEntryHomeworkID&pupilsightPersonID=$pupilsightPersonID";

if (isActionAccessible($guid, $connection2, '/modules/Crowd Assessment/crowdAssess_view_discuss_post.php') == false) {
    $URL .= '&return=error0';
    header("Location: {$URL}");
} else {
    //Proceed!
    //Check if school year specified
    if ($pupilsightPlannerEntryID == '' or $pupilsightPlannerEntryHomeworkID == '' or $pupilsightPersonID == '') {
        $URL .= '&return=error1';
        header("Location: {$URL}");
    } else {
        $and = " AND pupilsightPlannerEntryID=$pupilsightPlannerEntryID";
        $sql = getLessons($guid, $connection2, $and);
        try {
            $result = $connection2->prepare($sql[1]);
            $result->execute($sql[0]);
        } catch (PDOException $e) {
            $URL .= '&return=error2';
            header("Location: {$URL}");
            exit();
        }

        if ($result->rowCount() != 1) {
            $URL .= '&return=error1';
            header("Location: {$URL}");
        } else {
            $row = $result->fetch();

            $role = getCARole($guid, $connection2, $row['pupilsightCourseClassID']);

            if ($role == '') {
                $URL .= '&return=error2';
                header("Location: {$URL}");
            } else {
                $sqlList = getStudents($guid, $connection2, $role, $row['pupilsightCourseClassID'], $row['homeworkCrowdAssessOtherTeachersRead'], $row['homeworkCrowdAssessOtherParentsRead'], $row['homeworkCrowdAssessSubmitterParentsRead'], $row['homeworkCrowdAssessClassmatesParentsRead'], $row['homeworkCrowdAssessOtherStudentsRead'], $row['homeworkCrowdAssessClassmatesRead'], " AND pupilsightPerson.pupilsightPersonID=$pupilsightPersonID");

                if ($sqlList[1] != '') {
                    try {
                        $resultList = $connection2->prepare($sqlList[1]);
                        $resultList->execute($sqlList[0]);
                    } catch (PDOException $e) {
                        $URL .= '&return=erorr2';
                        header("Location: {$URL}");
                        exit();
                    }

                    if ($resultList->rowCount() != 1) {
                        $URL .= '&return=error2';
                        header("Location: {$URL}");
                    } else {
                        //INSERT
                        $replyTo = null;
                        if ($_GET['replyTo'] != '') {
                            $replyTo = $_GET['replyTo'];
                        }

                        //Attempt to prevent XSS attack
                        $comment = $_POST['comment'];
                        $comment = tinymceStyleStripTags($comment, $connection2);

                        try {
                            $data = array('pupilsightPlannerEntryHomeworkID' => $pupilsightPlannerEntryHomeworkID, 'pupilsightPersonID' => $_SESSION[$guid]['pupilsightPersonID'], 'comment' => $comment, 'replyTo' => $replyTo);
                            $sql = 'INSERT INTO pupilsightCrowdAssessDiscuss SET pupilsightPlannerEntryHomeworkID=:pupilsightPlannerEntryHomeworkID, pupilsightPersonID=:pupilsightPersonID, comment=:comment, pupilsightCrowdAssessDiscussIDReplyTo=:replyTo';
                            $result = $connection2->prepare($sql);
                            $result->execute($data);
                        } catch (PDOException $e) {
                            $URL .= '&return=erorr2';
                            header("Location: {$URL}");
                            exit();
                        }
                        $hash = '';
                        if ($_GET['replyTo'] != '') {
                            $hash = '#'.$_GET['replyTo'];
                        }

                        //Work out who we are replying too
                        $replyToID = null;
                        $dataClassGroup = array('pupilsightCrowdAssessDiscussID' => $replyTo);
                        $sqlClassGroup = 'SELECT * FROM pupilsightCrowdAssessDiscuss WHERE pupilsightCrowdAssessDiscussID=:pupilsightCrowdAssessDiscussID';
                        $resultClassGroup = $connection2->prepare($sqlClassGroup);
                        $resultClassGroup->execute($dataClassGroup);
                        if ($resultClassGroup->rowCount() == 1) {
                            $rowClassGroup = $resultClassGroup->fetch();
                            $replyToID = $rowClassGroup['pupilsightPersonID'];
                        }

                        //Get lesson plan name
                        $dataLesson = array('pupilsightPlannerEntryID' => $pupilsightPlannerEntryID);
                        $sqlLesson = 'SELECT * FROM pupilsightPlannerEntry WHERE pupilsightPlannerEntryID=:pupilsightPlannerEntryID';
                        $resultLesson = $connection2->prepare($sqlLesson);
                        $resultLesson->execute($dataLesson);
                        if ($resultLesson->rowCount() == 1) {
                            $rowLesson = $resultLesson->fetch();
                            $name = $rowLesson['name'];
                        }

                        //Create notification for homework owner, as long as it is not me.
                        if ($pupilsightPersonID != $_SESSION[$guid]['pupilsightPersonID'] and $pupilsightPersonID != $replyToID) {
                            $notificationText = sprintf(__('Someone has commented on your homework for lesson plan "%1$s".'), $name);
                            setNotification($connection2, $guid, $pupilsightPersonID, $notificationText, 'Crowd Assessment', "/index.php?q=/modules/Crowd Assessment/crowdAssess_view_discuss.php&pupilsightPlannerEntryID=$pupilsightPlannerEntryID&pupilsightPlannerEntryHomeworkID=$pupilsightPlannerEntryHomeworkID&pupilsightPersonID=$pupilsightPersonID");
                        }

                        //Create notification to person I am replying to
                        if (is_null($replyToID) == false) {
                            $notificationText = sprintf(__('Someone has replied to a comment on homework for lesson plan "%1$s".'), $name);
                            setNotification($connection2, $guid, $replyToID, $notificationText, 'Crowd Assessment', "/index.php?q=/modules/Crowd Assessment/crowdAssess_view_discuss.php&pupilsightPlannerEntryID=$pupilsightPlannerEntryID&pupilsightPlannerEntryHomeworkID=$pupilsightPlannerEntryHomeworkID&pupilsightPersonID=$pupilsightPersonID");
                        }

                        $URL .= "&return=success0$hash";
                        header("Location: {$URL}");
                    }
                }
            }
        }
    }
}
