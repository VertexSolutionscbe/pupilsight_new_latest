<?php
/*
Pupilsight, Flexible & Open School System
*/

include '../../pupilsight.php';

use Pupilsight\Comms\NotificationSender;
use Pupilsight\Domain\System\NotificationGateway;


$pupilsightPlannerEntryID = $_GET['pupilsightPlannerEntryID'];
$viewBy = $_GET['viewBy'];
$subView = $_GET['subView'];
if ($viewBy != 'date' and $viewBy != 'class') {
    $viewBy = 'date';
}
$pupilsightCourseClassID = $_POST['pupilsightCourseClassID'];
$date = dateConvert($guid, $_POST['date']);
$URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_GET['address'])."/planner_edit.php&pupilsightPlannerEntryID=$pupilsightPlannerEntryID";

//Params to pass back (viewBy + date or classID)
if ($viewBy == 'date') {
    $params = "&viewBy=$viewBy&date=$date";
} else {
    $params = "&viewBy=$viewBy&pupilsightCourseClassID=$pupilsightCourseClassID&subView=$subView";
}

if (isActionAccessible($guid, $connection2, '/modules/Planner/planner_edit.php') == false) {
    $URL .= "&return=error0$params";
    header("Location: {$URL}");
} else {
    $highestAction = getHighestGroupedAction($guid, $_GET['address'], $connection2);
    if ($highestAction == false) {
        $URL .= "&return=error0$params";
        header("Location: {$URL}");
    } else {
        if (empty($_POST)) {
            $URL .= '&return=error6';
            header("Location: {$URL}");
        } else {
            //Proceed!
            //Check if school year specified
            if ($pupilsightPlannerEntryID == '' or ($viewBy == 'class' and $pupilsightCourseClassID == '')) {
                $URL .= "&return=error1$params";
                header("Location: {$URL}");
            } else {
                try {
                    if ($highestAction == 'Lesson Planner_viewEditAllClasses') {
                        $data = array('pupilsightPlannerEntryID' => $pupilsightPlannerEntryID);
                        $sql = 'SELECT pupilsightPlannerEntryID, pupilsightUnitID, pupilsightCourse.nameShort AS course, pupilsightCourseClass.nameShort AS class, pupilsightPlannerEntry.name, summary FROM pupilsightPlannerEntry JOIN pupilsightCourseClass ON (pupilsightPlannerEntry.pupilsightCourseClassID=pupilsightCourseClass.pupilsightCourseClassID) JOIN pupilsightCourse ON (pupilsightCourse.pupilsightCourseID=pupilsightCourseClass.pupilsightCourseID) WHERE pupilsightPlannerEntryID=:pupilsightPlannerEntryID';
                    } else {
                        $data = array('pupilsightPlannerEntryID' => $pupilsightPlannerEntryID, 'pupilsightPersonID' => $_SESSION[$guid]['pupilsightPersonID']);
                        $sql = "SELECT pupilsightPlannerEntryID, pupilsightUnitID, pupilsightCourse.nameShort AS course, pupilsightCourseClass.nameShort AS class, pupilsightPlannerEntry.name, summary, role FROM pupilsightPlannerEntry JOIN pupilsightCourseClass ON (pupilsightPlannerEntry.pupilsightCourseClassID=pupilsightCourseClass.pupilsightCourseClassID) JOIN pupilsightCourseClassPerson ON (pupilsightCourseClass.pupilsightCourseClassID=pupilsightCourseClassPerson.pupilsightCourseClassID) JOIN pupilsightCourse ON (pupilsightCourse.pupilsightCourseID=pupilsightCourseClass.pupilsightCourseID) WHERE pupilsightCourseClassPerson.pupilsightPersonID=:pupilsightPersonID AND role='Teacher' AND pupilsightPlannerEntryID=:pupilsightPlannerEntryID";
                    }
                    $result = $connection2->prepare($sql);
                    $result->execute($data);
                } catch (PDOException $e) {
                    $URL .= "&return=error2$params";
                    header("Location: {$URL}");
                    exit();
                }

                if ($result->rowCount() != 1) {
                    $URL .= "&return=error2$params";
                    header("Location: {$URL}");
                } else {
                    $row = $result->fetch();

                    //Validate Inputs
                    $timeStart = $_POST['timeStart'];
                    $timeEnd = $_POST['timeEnd'];
                    $pupilsightUnitID = !empty($_POST['pupilsightUnitID']) ? $_POST['pupilsightUnitID'] : null;
                    $name = $_POST['name'];
                    $summary = $_POST['summary'];
                    if ($summary == '') {
                        $summary = trim(strip_tags($_POST['description'])) ;
                        if (strlen($summary) > 252) {
                            $summary = substr($summary, 0, 252).'...' ;
                        }
                    }
                    $summaryBlocks = '';
                    $description = $_POST['description'];
                    $teachersNotes = $_POST['teachersNotes'];
                    $homeworkSubmissionDateOpen = null;
                    $homeworkSubmissionDrafts = null;
                    $homeworkSubmissionType = null;
                    $homeworkSubmissionRequired = null;
                    $homeworkCrowdAssess = null;
                    $homeworkCrowdAssessOtherTeachersRead = null;
                    $homeworkCrowdAssessClassmatesRead = null;
                    $homeworkCrowdAssessOtherStudentsRead = null;
                    $homeworkCrowdAssessSubmitterParentsRead = null;
                    $homeworkCrowdAssessClassmatesParentsRead = null;
                    $homeworkCrowdAssessOtherParentsRead = null;
                    $homework = $_POST['homework'];
                    if ($_POST['homework'] == 'Y') {
                        $homework = 'Y';
                        $homeworkDetails = $_POST['homeworkDetails'];
                        if ($_POST['homeworkDueDateTime'] != '') {
                            $homeworkDueDateTime = $_POST['homeworkDueDateTime'].':59';
                        } else {
                            $homeworkDueDateTime = '21:00:00';
                        }
                        if ($_POST['homeworkDueDate'] != '') {
                            $homeworkDueDate = dateConvert($guid, $_POST['homeworkDueDate']).' '.$homeworkDueDateTime;
                        }

                        if ($_POST['homeworkSubmission'] == 'Y') {
                            $homeworkSubmission = 'Y';
                            if ($_POST['homeworkSubmissionDateOpen'] != '') {
                                $homeworkSubmissionDateOpen = dateConvert($guid, $_POST['homeworkSubmissionDateOpen']);
                            } else {
                                $homeworkSubmissionDateOpen = date('Y-m-d');
                            }
                            if (isset($_POST['homeworkSubmissionDrafts'])) {
                                $homeworkSubmissionDrafts = $_POST['homeworkSubmissionDrafts'];
                            }
                            $homeworkSubmissionType = $_POST['homeworkSubmissionType'];
                            $homeworkSubmissionRequired = $_POST['homeworkSubmissionRequired'];
                            if (!empty($_POST['homeworkCrowdAssess']) && $_POST['homeworkCrowdAssess'] == 'Y') {
                                $homeworkCrowdAssess = 'Y';
                                if (isset($_POST['homeworkCrowdAssessOtherTeachersRead'])) {
                                    $homeworkCrowdAssessOtherTeachersRead = 'Y';
                                } else {
                                    $homeworkCrowdAssessOtherTeachersRead = 'N';
                                }
                                if (isset($_POST['homeworkCrowdAssessClassmatesRead'])) {
                                    $homeworkCrowdAssessClassmatesRead = 'Y';
                                } else {
                                    $homeworkCrowdAssessClassmatesRead = 'N';
                                }
                                if (isset($_POST['homeworkCrowdAssessOtherStudentsRead'])) {
                                    $homeworkCrowdAssessOtherStudentsRead = 'Y';
                                } else {
                                    $homeworkCrowdAssessOtherStudentsRead = 'N';
                                }
                                if (isset($_POST['homeworkCrowdAssessSubmitterParentsRead'])) {
                                    $homeworkCrowdAssessSubmitterParentsRead = 'Y';
                                } else {
                                    $homeworkCrowdAssessSubmitterParentsRead = 'N';
                                }
                                if (isset($_POST['homeworkCrowdAssessClassmatesParentsRead'])) {
                                    $homeworkCrowdAssessClassmatesParentsRead = 'Y';
                                } else {
                                    $homeworkCrowdAssessClassmatesParentsRead = 'N';
                                }
                                if (isset($_POST['homeworkCrowdAssessOtherParentsRead'])) {
                                    $homeworkCrowdAssessOtherParentsRead = 'Y';
                                } else {
                                    $homeworkCrowdAssessOtherParentsRead = 'N';
                                }
                            }
                            else {
                                $homeworkCrowdAssess = 'N';
                            }
                        } else {
                            $homeworkSubmission = 'N';
                            $homeworkCrowdAssess = 'N';
                        }
                    } else {
                        $homework = 'N';
                        $homeworkDueDate = null;
                        $homeworkDetails = '';
                        $homeworkSubmission = 'N';
                        $homeworkCrowdAssess = 'N';
                    }

                    $viewableParents = $_POST['viewableParents'];
                    $viewableStudents = $_POST['viewableStudents'];
                    $pupilsightPersonIDCreator = $_SESSION[$guid]['pupilsightPersonID'];
                    $pupilsightPersonIDLastEdit = $_SESSION[$guid]['pupilsightPersonID'];

                    if ($viewBy == '' or $pupilsightCourseClassID == '' or $date == '' or $timeStart == '' or $timeEnd == '' or $name == '' or $homework == '' or $viewableParents == '' or $viewableStudents == '' or ($homework == 'Y' and ($homeworkDetails == '' or $homeworkDueDate == ''))) {
                        $URL .= "&return=error3$params";
                        header("Location: {$URL}");
                    } else {
                        //Scan through guests
                        $guests = null;
                        if (isset($_POST['guests'])) {
                            $guests = $_POST['guests'];
                        }
                        $role = $_POST['role'];
                        if ($role == '') {
                            $role = 'Student';
                        }
                        if (count($guests) > 0) {
                            foreach ($guests as $t) {
                                //Check to see if person is already registered in this class
                                try {
                                    $dataGuest = array('pupilsightPersonID' => $t, 'pupilsightCourseClassID' => $pupilsightCourseClassID);
                                    $sqlGuest = 'SELECT * FROM pupilsightCourseClassPerson WHERE pupilsightPersonID=:pupilsightPersonID AND pupilsightCourseClassID=:pupilsightCourseClassID';
                                    $resultGuest = $connection2->prepare($sqlGuest);
                                    $resultGuest->execute($dataGuest);
                                } catch (PDOException $e) {
                                    $partialFail = true;
                                }
                                if ($resultGuest->rowCount() == 0) {
                                    //Check to see if person is already a guest in this class
                                    try {
                                        $dataGuest2 = array('pupilsightPersonID' => $t, 'pupilsightPlannerEntryID' => $pupilsightPlannerEntryID);
                                        $sqlGuest2 = 'SELECT * FROM pupilsightPlannerEntryGuest WHERE pupilsightPersonID=:pupilsightPersonID AND pupilsightPlannerEntryID=:pupilsightPlannerEntryID';
                                        $resultGuest2 = $connection2->prepare($sqlGuest2);
                                        $resultGuest2->execute($dataGuest2);
                                    } catch (PDOException $e) {
                                        $partialFail = true;
                                    }
                                    if ($resultGuest2->rowCount() == 0) {
                                        try {
                                            $data = array('pupilsightPersonID' => $t, 'pupilsightPlannerEntryID' => $pupilsightPlannerEntryID, 'role' => $role);
                                            $sql = 'INSERT INTO pupilsightPlannerEntryGuest SET pupilsightPersonID=:pupilsightPersonID, pupilsightPlannerEntryID=:pupilsightPlannerEntryID, role=:role';
                                            $result = $connection2->prepare($sql);
                                            $result->execute($data);
                                        } catch (PDOException $e) {
                                            $partialFail = true;
                                        }
                                    }
                                }
                            }
                        }

                        //Deal with smart unit
                        $partialFail = false;
                        $order = null;
                        if (isset($_POST['order'])) {
                            $order = $_POST['order'];
                        }
                        $seq = null;
                        if (isset($_POST['minSeq'])) {
                            $seq = $_POST['minSeq'];
                        }

                        if (is_array($order)) {
                            foreach ($order as $i) {
                                $id = $_POST["pupilsightUnitClassBlockID$i"];
                                $title = $_POST["title$i"];
                                $summaryBlocks .= $title.', ';
                                $type = $_POST["type$i"];
                                $length = $_POST["length$i"];
                                $contents = $_POST["contents$i"];
                                $teachersNotesBlock = $_POST["teachersNotes$i"];
                                $complete = 'N';
                                if (isset($_POST["complete$i"])) {
                                    if ($_POST["complete$i"] == 'on') {
                                        $complete = 'Y';
                                    }
                                }

                                //Write to database
                                try {
                                    $data = array('title' => $title, 'type' => $type, 'length' => $length, 'contents' => $contents, 'teachersNotes' => $teachersNotesBlock, 'complete' => $complete, 'sequenceNumber' => $seq, 'pupilsightUnitClassBlockID' => $id);
                                    $sql = 'UPDATE pupilsightUnitClassBlock SET title=:title, type=:type, length=:length, contents=:contents, teachersNotes=:teachersNotes, complete=:complete, sequenceNumber=:sequenceNumber WHERE pupilsightUnitClassBlockID=:pupilsightUnitClassBlockID';
                                    $result = $connection2->prepare($sql);
                                    $result->execute($data);
                                } catch (PDOException $e) {
                                    $partialFail = true;
                                }

                                ++$seq;
                            }
                        }

                        //Delete all outcomes
                        try {
                            $dataDelete = array('pupilsightPlannerEntryID' => $pupilsightPlannerEntryID);
                            $sqlDelete = 'DELETE FROM pupilsightPlannerEntryOutcome WHERE pupilsightPlannerEntryID=:pupilsightPlannerEntryID';
                            $resultDelete = $connection2->prepare($sqlDelete);
                            $resultDelete->execute($dataDelete);
                        } catch (PDOException $e) {
                            $URL .= '&return=error2';
                            header("Location: {$URL}");
                            exit();
                        }
                        //Insert outcomes
                        $count = 0;
                        if (isset($_POST['outcomeorder'])) {
                            if (count($_POST['outcomeorder']) > 0) {
                                foreach ($_POST['outcomeorder'] as $outcome) {
                                    if ($_POST["outcomepupilsightOutcomeID$outcome"] != '') {
                                        try {
                                            $dataInsert = array('pupilsightPlannerEntryID' => $pupilsightPlannerEntryID, 'pupilsightOutcomeID' => $_POST["outcomepupilsightOutcomeID$outcome"], 'content' => $_POST["outcomecontents$outcome"], 'count' => $count);
                                            $sqlInsert = 'INSERT INTO pupilsightPlannerEntryOutcome SET pupilsightPlannerEntryID=:pupilsightPlannerEntryID, pupilsightOutcomeID=:pupilsightOutcomeID, content=:content, sequenceNumber=:count';
                                            $resultInsert = $connection2->prepare($sqlInsert);
                                            $resultInsert->execute($dataInsert);
                                        } catch (PDOException $e) {
                                            $partialFail = true;
                                        }
                                    }
                                    ++$count;
                                }
                            }
                        }

                        $summaryBlocks = substr($summaryBlocks, 0, -2);
                        if (strlen($summaryBlocks) > 75) {
                            $summaryBlocks = substr($summaryBlocks, 0, 72).'...';
                        }
                        if ($summaryBlocks) {
                            $summary = $summaryBlocks;
                        }

                        //Write to database
                        try {
                            $data = array('pupilsightCourseClassID' => $pupilsightCourseClassID, 'date' => $date, 'timeStart' => $timeStart, 'timeEnd' => $timeEnd, 'pupilsightUnitID' => $pupilsightUnitID, 'name' => $name, 'summary' => $summary, 'description' => $description, 'teachersNotes' => $teachersNotes, 'homework' => $homework, 'homeworkDueDate' => $homeworkDueDate, 'homeworkDetails' => $homeworkDetails, 'homeworkSubmission' => $homeworkSubmission, 'homeworkSubmissionDateOpen' => $homeworkSubmissionDateOpen, 'homeworkSubmissionDrafts' => $homeworkSubmissionDrafts, 'homeworkSubmissionType' => $homeworkSubmissionType, 'homeworkSubmissionRequired' => $homeworkSubmissionRequired, 'homeworkCrowdAssess' => $homeworkCrowdAssess, 'homeworkCrowdAssessOtherTeachersRead' => $homeworkCrowdAssessOtherTeachersRead, 'homeworkCrowdAssessClassmatesRead' => $homeworkCrowdAssessClassmatesRead, 'homeworkCrowdAssessOtherStudentsRead' => $homeworkCrowdAssessOtherStudentsRead, 'homeworkCrowdAssessSubmitterParentsRead' => $homeworkCrowdAssessSubmitterParentsRead, 'homeworkCrowdAssessClassmatesParentsRead' => $homeworkCrowdAssessClassmatesParentsRead, 'homeworkCrowdAssessOtherParentsRead' => $homeworkCrowdAssessOtherParentsRead, 'viewableParents' => $viewableParents, 'viewableStudents' => $viewableStudents, 'pupilsightPersonIDLastEdit' => $pupilsightPersonIDLastEdit, 'pupilsightPlannerEntryID' => $pupilsightPlannerEntryID);
                            $sql = 'UPDATE pupilsightPlannerEntry SET pupilsightCourseClassID=:pupilsightCourseClassID, date=:date, timeStart=:timeStart, timeEnd=:timeEnd, pupilsightUnitID=:pupilsightUnitID, name=:name, summary=:summary, description=:description, teachersNotes=:teachersNotes, homework=:homework, homeworkDueDateTime=:homeworkDueDate, homeworkDetails=:homeworkDetails, homeworkSubmission=:homeworkSubmission, homeworkSubmissionDateOpen=:homeworkSubmissionDateOpen, homeworkSubmissionDrafts=:homeworkSubmissionDrafts, homeworkSubmissionType=:homeworkSubmissionType, homeworkSubmissionRequired=:homeworkSubmissionRequired, homeworkCrowdAssess=:homeworkCrowdAssess, homeworkCrowdAssessOtherTeachersRead=:homeworkCrowdAssessOtherTeachersRead, homeworkCrowdAssessClassmatesRead=:homeworkCrowdAssessClassmatesRead, homeworkCrowdAssessOtherStudentsRead=:homeworkCrowdAssessOtherStudentsRead, homeworkCrowdAssessSubmitterParentsRead=:homeworkCrowdAssessSubmitterParentsRead, homeworkCrowdAssessClassmatesParentsRead=:homeworkCrowdAssessClassmatesParentsRead, homeworkCrowdAssessOtherParentsRead=:homeworkCrowdAssessOtherParentsRead, viewableParents=:viewableParents, viewableStudents=:viewableStudents, pupilsightPersonIDLastEdit=:pupilsightPersonIDLastEdit WHERE pupilsightPlannerEntryID=:pupilsightPlannerEntryID';
                            $result = $connection2->prepare($sql);
                            $result->execute($data);
                        } catch (PDOException $e) {
                            $URL .= "&return=error2$params";
                            header("Location: {$URL}");
                            exit();
                        }

                        if ($partialFail == true) {
                            $URL .= "&return=warning1$params";
                            header("Location: {$URL}");
                        } else {
                            $URL .= "&return=success0$params";
                            header("Location: {$URL}");
                        }
                        //Notify participants
                        if (isset($_POST['notify'])) {
                            //Create notification for all people in class except me
                            $notificationGateway = new NotificationGateway($pdo);
                            $notificationSender = new NotificationSender($notificationGateway, $pupilsight->session);

                            try {
                                $dataClassGroup = array('pupilsightCourseClassID' => $pupilsightCourseClassID);
                                $sqlClassGroup = "SELECT * FROM pupilsightCourseClassPerson INNER JOIN pupilsightPerson ON pupilsightCourseClassPerson.pupilsightPersonID=pupilsightPerson.pupilsightPersonID WHERE pupilsightCourseClassID=:pupilsightCourseClassID AND status='Full' AND (dateStart IS NULL OR dateStart<='".date('Y-m-d')."') AND (dateEnd IS NULL  OR dateEnd>='".date('Y-m-d')."') AND (NOT role='Student - Left') AND (NOT role='Teacher - Left') ORDER BY role DESC, surname, preferredName";
                                $resultClassGroup = $connection2->prepare($sqlClassGroup);
                                $resultClassGroup->execute($dataClassGroup);
                            } catch (PDOException $e) {
                                $URL .= "&return=warning1$params";
                                header("Location: {$URL}");
                                exit();
                            }

                            while ($rowClassGroup = $resultClassGroup->fetch()) {
                                if ($rowClassGroup['pupilsightPersonID'] != $_SESSION[$guid]['pupilsightPersonID']) {
                                    $notificationSender->addNotification($rowClassGroup['pupilsightPersonID'], sprintf(__('Lesson “%1$s” has been updated.'), $name), "Planner", "/index.php?q=/modules/Planner/planner_view_full.php&pupilsightPlannerEntryID=$pupilsightPlannerEntryID&viewBy=class&pupilsightCourseClassID=$pupilsightCourseClassID");
                                }
                            }
                            $notificationSender->sendNotifications();
                        }
                    }
                }
            }
        }
    }
}
