<?php
/*
Pupilsight, Flexible & Open School System
*/

include '../../pupilsight.php';

use Pupilsight\Comms\NotificationSender;
use Pupilsight\Domain\System\NotificationGateway;


$URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_GET['address']).'/planner_add.php';

if (isActionAccessible($guid, $connection2, '/modules/Planner/planner_add.php') == false) {
    $URL .= '&return=error0';
    header("Location: {$URL}");
} else {
    $highestAction = getHighestGroupedAction($guid, $_GET['address'], $connection2);
    if ($highestAction == false) {
        $URL .= "&return=error0$params";
        header("Location: {$URL}");
    } else {
        if (empty($_POST)) {
            $URL .= '&return=warning1';
            header("Location: {$URL}");
        } else {
            //Proceed!
            //Validate Inputs
            $viewBy = $_GET['viewBy'];
            $subView = $_GET['subView'];
            if ($viewBy != 'date' and $viewBy != 'class') {
                $viewBy = 'date';
            }
            $pupilsightProgramID = $_POST['pupilsightProgramID'];
            $pupilsightYearGroupID = $_POST['pupilsightYearGroupID'];
            $pupilsightRollGroupID = $_POST['pupilsightRollGroupID'];
            $pupilsightDepartmentID = $_POST['pupilsightDepartmentID'];
            $pupilsightCourseClassID = $_POST['pupilsightYearGroupID'];
            $date = dateConvert($guid, $_POST['date']);
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
            $description = $_POST['description'];
            $teachersNotes = $_POST['teachersNotes'];
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
                        $homeworkSubmissionDateOpen = dateConvert($guid, $_POST['date']);
                    }
                    $homeworkSubmissionDrafts = $_POST['homeworkSubmissionDrafts'];
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
                    } else {
                        $homeworkCrowdAssess = 'N';
                        $homeworkCrowdAssessOtherTeachersRead = 'N';
                        $homeworkCrowdAssessClassmatesRead = 'N';
                        $homeworkCrowdAssessOtherStudentsRead = 'N';
                        $homeworkCrowdAssessSubmitterParentsRead = 'N';
                        $homeworkCrowdAssessClassmatesParentsRead = 'N';
                        $homeworkCrowdAssessOtherParentsRead = 'N';
                    }
                } else {
                    $homeworkSubmission = 'N';
                    $homeworkSubmissionDateOpen = null;
                    $homeworkSubmissionType = '';
                    $homeworkSubmissionDrafts = null;
                    $homeworkSubmissionRequired = null;
                    $homeworkCrowdAssess = 'N';
                    $homeworkCrowdAssessOtherTeachersRead = 'N';
                    $homeworkCrowdAssessClassmatesRead = 'N';
                    $homeworkCrowdAssessOtherStudentsRead = 'N';
                    $homeworkCrowdAssessSubmitterParentsRead = 'N';
                    $homeworkCrowdAssessClassmatesParentsRead = 'N';
                    $homeworkCrowdAssessOtherParentsRead = 'N';
                }
            } else {
                $homework = 'N';
                $homeworkDueDate = null;
                $homeworkDetails = '';
                $homeworkSubmission = 'N';
                $homeworkSubmissionDateOpen = null;
                $homeworkSubmissionType = '';
                $homeworkSubmissionDrafts = null;
                $homeworkSubmissionRequired = null;
                $homeworkCrowdAssess = 'N';
                $homeworkCrowdAssessOtherTeachersRead = 'N';
                $homeworkCrowdAssessClassmatesRead = 'N';
                $homeworkCrowdAssessOtherStudentsRead = 'N';
                $homeworkCrowdAssessSubmitterParentsRead = 'N';
                $homeworkCrowdAssessClassmatesParentsRead = 'N';
                $homeworkCrowdAssessOtherParentsRead = 'N';
            }

            $viewableParents = $_POST['viewableParents'] ?? 'Y';
            $viewableStudents = $_POST['viewableStudents'] ?? 'Y';
            $pupilsightPersonIDCreator = $_SESSION[$guid]['pupilsightPersonID'];
            $pupilsightPersonIDLastEdit = $_SESSION[$guid]['pupilsightPersonID'];

            //Params to pass back (viewBy + date or classID)
            if ($viewBy == 'date') {
                $params = "&viewBy=$viewBy&date=$date";
            } else {
                $params = "&viewBy=$viewBy&pupilsightProgramID=$pupilsightProgramID&pupilsightCourseClassID=$pupilsightCourseClassID&subView=$subView";
            }

            //Lock markbook column table
            try {
                $sql = 'LOCK TABLES pupilsightPlannerEntry WRITE, pupilsightPlannerEntryGuest WRITE, pupilsightCourseClassPerson WRITE, pupilsightPlannerEntryOutcome WRITE';
                $result = $connection2->query($sql);
            } catch (PDOException $e) {
                $URL .= "&return=error2$params";
                header("Location: {$URL}");
                exit();
            }

            //Get next autoincrement
            try {
                $sqlAI = "SHOW TABLE STATUS LIKE 'pupilsightPlannerEntry'";
                $resultAI = $connection2->query($sqlAI);
            } catch (PDOException $e) {
                $URL .= "&return=error2$params";
                header("Location: {$URL}");
                exit();
            }

            $rowAI = $resultAI->fetch();
            $AI = str_pad($rowAI['Auto_increment'], 14, '0', STR_PAD_LEFT);

            if ($viewBy == '' or $pupilsightCourseClassID == '' or $date == '' or $timeStart == '' or $timeEnd == '' or $name == '' or $homework == '' or $viewableParents == '' or $viewableStudents == '' or ($homework == 'Y' and ($homeworkDetails == '' or $homeworkDueDate == ''))) {
                $URL .= "&return=error1$params";
                header("Location: {$URL}");
            } else {
                $partialFail = false;

                //Scan through guests
                $guests = null;
                if (isset($_POST['guests'])) {
                    $guests = $_POST['guests'];
                }
                $role = $_POST['role'] ?? 'Student';

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
                            try {
                                $data = array('pupilsightPersonID' => $t, 'pupilsightPlannerEntryID' => $AI, 'role' => $role);
                                $sql = 'INSERT INTO pupilsightPlannerEntryGuest SET pupilsightPersonID=:pupilsightPersonID, pupilsightPlannerEntryID=:pupilsightPlannerEntryID, role=:role';
                                $result = $connection2->prepare($sql);
                                $result->execute($data);
                            } catch (PDOException $e) {
                                $partialFail = true;
                            }
                        }
                    }
                }

                //Insert outcomes
                $count = 0;
                if (isset($_POST['outcomeorder'])) {
                    if (count($_POST['outcomeorder']) > 0) {
                        foreach ($_POST['outcomeorder'] as $outcome) {
                            if ($_POST["outcomepupilsightOutcomeID$outcome"] != '') {
                                try {
                                    $dataInsert = array('AI' => $AI, 'pupilsightOutcomeID' => $_POST["outcomepupilsightOutcomeID$outcome"], 'content' => $_POST["outcomecontents$outcome"], 'count' => $count);
                                    $sqlInsert = 'INSERT INTO pupilsightPlannerEntryOutcome SET pupilsightPlannerEntryID=:AI, pupilsightOutcomeID=:pupilsightOutcomeID, content=:content, sequenceNumber=:count';
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

                //Write to database
                try {
                    $data = array('pupilsightProgramID' => $pupilsightProgramID,'pupilsightYearGroupID' => $pupilsightYearGroupID,'pupilsightRollGroupID' => $pupilsightRollGroupID, 'pupilsightDepartmentID' => $pupilsightDepartmentID, 'pupilsightCourseClassID' => $pupilsightCourseClassID, 'date' => $date, 'timeStart' => $timeStart, 'timeEnd' => $timeEnd, 'pupilsightUnitID' => $pupilsightUnitID, 'name' => $name, 'summary' => $summary, 'description' => $description, 'teachersNotes' => $teachersNotes, 'homework' => $homework, 'homeworkDueDate' => $homeworkDueDate, 'homeworkDetails' => $homeworkDetails, 'homeworkSubmission' => $homeworkSubmission, 'homeworkSubmissionDateOpen' => $homeworkSubmissionDateOpen, 'homeworkSubmissionDrafts' => $homeworkSubmissionDrafts, 'homeworkSubmissionType' => $homeworkSubmissionType, 'homeworkSubmissionRequired' => $homeworkSubmissionRequired, 'homeworkCrowdAssess' => $homeworkCrowdAssess, 'homeworkCrowdAssessOtherTeachersRead' => $homeworkCrowdAssessOtherTeachersRead, 'homeworkCrowdAssessClassmatesRead' => $homeworkCrowdAssessClassmatesRead, 'homeworkCrowdAssessOtherStudentsRead' => $homeworkCrowdAssessOtherStudentsRead, 'homeworkCrowdAssessSubmitterParentsRead' => $homeworkCrowdAssessSubmitterParentsRead, 'homeworkCrowdAssessClassmatesParentsRead' => $homeworkCrowdAssessClassmatesParentsRead, 'homeworkCrowdAssessOtherParentsRead' => $homeworkCrowdAssessOtherParentsRead, 'viewableParents' => $viewableParents, 'viewableStudents' => $viewableStudents, 'pupilsightPersonIDCreator' => $pupilsightPersonIDCreator, 'pupilsightPersonIDLastEdit' => $pupilsightPersonIDLastEdit);
                    $sql = 'INSERT INTO pupilsightPlannerEntry SET pupilsightProgramID=:pupilsightProgramID, pupilsightYearGroupID=:pupilsightYearGroupID, pupilsightRollGroupID=:pupilsightRollGroupID, pupilsightDepartmentID=:pupilsightDepartmentID, pupilsightCourseClassID=:pupilsightCourseClassID, date=:date, timeStart=:timeStart, timeEnd=:timeEnd, pupilsightUnitID=:pupilsightUnitID, name=:name, summary=:summary, description=:description, teachersNotes=:teachersNotes, homework=:homework, homeworkDueDateTime=:homeworkDueDate, homeworkDetails=:homeworkDetails, homeworkSubmission=:homeworkSubmission, homeworkSubmissionDateOpen=:homeworkSubmissionDateOpen, homeworkSubmissionDrafts=:homeworkSubmissionDrafts, homeworkSubmissionType=:homeworkSubmissionType, homeworkSubmissionRequired=:homeworkSubmissionRequired, homeworkCrowdAssess=:homeworkCrowdAssess, homeworkCrowdAssessOtherTeachersRead=:homeworkCrowdAssessOtherTeachersRead, homeworkCrowdAssessClassmatesRead=:homeworkCrowdAssessClassmatesRead, homeworkCrowdAssessOtherStudentsRead=:homeworkCrowdAssessOtherStudentsRead, homeworkCrowdAssessSubmitterParentsRead=:homeworkCrowdAssessSubmitterParentsRead, homeworkCrowdAssessClassmatesParentsRead=:homeworkCrowdAssessClassmatesParentsRead, homeworkCrowdAssessOtherParentsRead=:homeworkCrowdAssessOtherParentsRead, viewableParents=:viewableParents, viewableStudents=:viewableStudents, pupilsightPersonIDCreator=:pupilsightPersonIDCreator, pupilsightPersonIDLastEdit=:pupilsightPersonIDLastEdit';
                    $result = $connection2->prepare($sql);
                    $result->execute($data);
                } catch (PDOException $e) {
                    $URL .= "&return=error2$params";
                    header("Location: {$URL}");
                    exit();
                }

                //Unlock module table
                try {
                    $sql = 'UNLOCK TABLES';
                    $result = $connection2->query($sql);
                } catch (PDOException $e) {
                }

                if ($partialFail == true) {
                    $URL .= "&return=warning1$params";
                    header("Location: {$URL}");
                    exit();
                } else {
                    //Jump to Markbook?
                    $markbook = $_POST['markbook'];
                    if ($markbook == 'Y') {
                        $URL = $_SESSION[$guid]['absoluteURL']."/index.php?q=/modules/Markbook/markbook_edit_add.php&pupilsightPlannerEntryID=$AI&pupilsightCourseClassID=$pupilsightCourseClassID&pupilsightUnitID=".$_POST['pupilsightUnitID']."&date=$date&viewableParents=$viewableParents&viewableStudents=$viewableStudents&name=$name&summary=$summary&return=success1";
                        header("Location: {$URL}");
                        exit();
                    } else {
                        $URL .= "&return=success0&editID=".$AI.$params;
                        header("Location: {$URL}");
                        exit();
                    }
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
                            $notificationSender->addNotification($rowClassGroup['pupilsightPersonID'], sprintf(__('Lesson “%1$s” has been created.'), $name), "Planner", "/index.php?q=/modules/Planner/planner_view_full.php&pupilsightPlannerEntryID=$AI&viewBy=class&pupilsightCourseClassID=$pupilsightCourseClassID");
                        }
                    }
                    $notificationSender->sendNotifications();
                }
            }
        }
    }
}
