<?php
/*
Pupilsight, Flexible & Open School System
*/

include '../../pupilsight.php';

$pupilsightPlannerEntryID = $_GET['pupilsightPlannerEntryID'];
$viewBy = $_POST['viewBy'];
$subView = $_POST['subView'];
if ($viewBy != 'date' and $viewBy != 'class') {
    $viewBy = 'date';
}
$pupilsightCourseClassID = $_POST['pupilsightCourseClassID'];
$pupilsightSchoolYearID = $_POST['pupilsightSchoolYearID'];
$pupilsightPlannerEntryID_org = $_POST['pupilsightPlannerEntryID_org'];
$date = dateConvert($guid, $_POST['date']);
$duplicateReturnYear = 'current';
$URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_POST['address'])."/planner_duplicate.php&pupilsightPlannerEntryID=$pupilsightPlannerEntryID_org";

//Params to pass back (viewBy + date or classID)
if ($viewBy == 'date') {
    $params = "&viewBy=$viewBy&date=$date";
} else {
    $params = "&viewBy=$viewBy&pupilsightCourseClassID=$pupilsightCourseClassID&subView=$subView";
}

if (isActionAccessible($guid, $connection2, '/modules/Planner/planner_duplicate.php') == false) {
    $URL .= "&return=error0$params";
    header("Location: {$URL}");
} else {
    $highestAction = getHighestGroupedAction($guid, $_POST['address'], $connection2);
    if ($highestAction == false) {
        $URL .= "&return=error0$params";
        header("Location: {$URL}");
    } else {
        //Proceed!
        //Check if legitimate year/class selected
        if ($pupilsightPlannerEntryID == '' or $pupilsightSchoolYearID == '' or $pupilsightCourseClassID == '' or ($viewBy == 'class' and $pupilsightCourseClassID == 'Y')) {
            $URL .= "&return=error1$params";
            header("Location: {$URL}");
        } else {
            try {
                $data = array('pupilsightSchoolYearID' => $_SESSION[$guid]['pupilsightSchoolYearID'], 'pupilsightPlannerEntryID' => $pupilsightPlannerEntryID_org);
                $sql = 'SELECT *, pupilsightPlannerEntry.description AS description FROM pupilsightPlannerEntry JOIN pupilsightCourseClass ON (pupilsightPlannerEntry.pupilsightCourseClassID=pupilsightCourseClass.pupilsightCourseClassID) JOIN pupilsightCourse ON (pupilsightCourse.pupilsightCourseID=pupilsightCourseClass.pupilsightCourseID) WHERE pupilsightPlannerEntryID=:pupilsightPlannerEntryID AND pupilsightCourse.pupilsightSchoolYearID=:pupilsightSchoolYearID';
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
                $name = $_POST['name'];
                $timeStart = $_POST['timeStart'];
                $timeEnd = $_POST['timeEnd'];
                $summary = $row['summary'];
                $description = $row['description'];
                //Add to smart blocks to description if copying to another year
                if ($pupilsightSchoolYearID != $_SESSION[$guid]['pupilsightSchoolYearID'] or @$_POST['keepUnit'] != 'Y') {
                    try {
                        $dataBlocks = array('pupilsightPlannerEntryID' => $pupilsightPlannerEntryID);
                        $sqlBlocks = 'SELECT * FROM pupilsightUnitClassBlock WHERE pupilsightPlannerEntryID=:pupilsightPlannerEntryID AND pupilsightPlannerEntryID IS NOT NULL';
                        $resultBlocks = $connection2->prepare($sqlBlocks);
                        $resultBlocks->execute($dataBlocks);
                    } catch (PDOException $e) {
                        $partialFail = true;
                    }
                    while ($rowBlocks = $resultBlocks->fetch()) {
                        $description .= '<h2>'.$rowBlocks['title'].'</h2>';
                        $description .= $rowBlocks['contents'];
                    }

                    try {
                        $dataPlannerUpdate = array('pupilsightPlannerEntryID' => $pupilsightPlannerEntryID, 'description' => $description);
                        $sqlPlannerUpdate = 'UPDATE pupilsightPlannerEntry SET description=:description WHERE pupilsightPlannerEntryID=:pupilsightPlannerEntryID';
                        $resultPlannerUpdate = $connection2->prepare($sqlPlannerUpdate);
                        $resultPlannerUpdate->execute($dataPlannerUpdate);
                    } catch (PDOException $e) {
                    }
                }

                $keepUnit = null;
                $pupilsightUnitClassID = null;
                if (isset($_POST['keepUnit'])) {
                    $keepUnit = $_POST['keepUnit'];
                }
                if ($keepUnit == 'Y') {
                    $pupilsightUnitClassID = $_POST['pupilsightUnitClassID'];
                    $pupilsightUnitID = $row['pupilsightUnitID'];
                } else {
                    $pupilsightUnitID = null;
                }
                $teachersNotes = $row['teachersNotes'];
                $homework = $row['homework'];
                $homework = $row['homework'];
                $homeworkDetails = $row['homeworkDetails'];
                if ($row['homeworkDueDateTime'] == '') {
                    $homeworkDueDate = null;
                } else {
                    $homeworkDueDate = $row['homeworkDueDateTime'];
                }
                $homeworkSubmission = $row['homeworkSubmission'];
                if ($row['homeworkSubmissionDateOpen'] == '') {
                    $homeworkSubmissionDateOpen = null;
                } else {
                    $homeworkSubmissionDateOpen = $row['homeworkSubmissionDateOpen'];
                }
                $homeworkSubmissionDrafts = $row['homeworkSubmissionDrafts'];
                $homeworkSubmissionType = $row['homeworkSubmissionType'];
                $homeworkSubmissionRequired = $row['homeworkSubmissionRequired'];
                $homeworkCrowdAssess = $row['homeworkCrowdAssess'];
                $homeworkCrowdAssessOtherTeachersRead = $row['homeworkCrowdAssessOtherTeachersRead'];
                $homeworkCrowdAssessClassmatesRead = $row['homeworkCrowdAssessClassmatesRead'];
                $homeworkCrowdAssessOtherStudentsRead = $row['homeworkCrowdAssessOtherStudentsRead'];
                $homeworkCrowdAssessSubmitterParentsRead = $row['homeworkCrowdAssessSubmitterParentsRead'];
                $homeworkCrowdAssessClassmatesParentsRead = $row['homeworkCrowdAssessClassmatesParentsRead'];
                $homeworkCrowdAssessOtherParentsRead = $row['homeworkCrowdAssessOtherParentsRead'];
                $viewableParents = $row['viewableParents'];
                $viewableStudents = $row['viewableStudents'];
                $pupilsightPersonIDCreator = $_SESSION[$guid]['pupilsightPersonID'];
                $pupilsightPersonIDLastEdit = $_SESSION[$guid]['pupilsightPersonID'];

                if ($viewBy == '' or $pupilsightCourseClassID == '' or $date == '' or $timeStart == '' or $timeEnd == '' or $name == '' or $homework == '' or $viewableParents == '' or $viewableStudents == '' or ($homework == 'Y' and ($homeworkDetails == '' or $homeworkDueDate == ''))) {
                    $URL .= "&return=error3$params";
                    header("Location: {$URL}");
                } else {
                    //Lock markbook column table
                    try {
                        $sql = 'LOCK TABLES pupilsightPlannerEntry WRITE, pupilsightPlannerEntryGuest WRITE, pupilsightCourseClassPerson WRITE';
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

                    //Write to database
                    try {
                        $data = array('pupilsightCourseClassID' => $pupilsightCourseClassID, 'date' => $date, 'timeStart' => $timeStart, 'timeEnd' => $timeEnd, 'pupilsightUnitID' => $pupilsightUnitID, 'name' => $name, 'summary' => $summary, 'description' => $description, 'teachersNotes' => $teachersNotes, 'homework' => $homework, 'homeworkDueDate' => $homeworkDueDate, 'homeworkDetails' => $homeworkDetails, 'homeworkSubmission' => $homeworkSubmission, 'homeworkSubmissionDateOpen' => $homeworkSubmissionDateOpen, 'homeworkSubmissionDrafts' => $homeworkSubmissionDrafts, 'homeworkSubmissionType' => $homeworkSubmissionType, 'homeworkSubmissionRequired' => $homeworkSubmissionRequired, 'homeworkCrowdAssess' => $homeworkCrowdAssess, 'homeworkCrowdAssessOtherTeachersRead' => $homeworkCrowdAssessOtherTeachersRead, 'homeworkCrowdAssessClassmatesRead' => $homeworkCrowdAssessClassmatesRead, 'homeworkCrowdAssessOtherStudentsRead' => $homeworkCrowdAssessOtherStudentsRead, 'homeworkCrowdAssessSubmitterParentsRead' => $homeworkCrowdAssessSubmitterParentsRead, 'homeworkCrowdAssessClassmatesParentsRead' => $homeworkCrowdAssessClassmatesParentsRead, 'homeworkCrowdAssessOtherParentsRead' => $homeworkCrowdAssessOtherParentsRead, 'viewableParents' => $viewableParents, 'viewableStudents' => $viewableStudents, 'pupilsightPersonIDCreator' => $pupilsightPersonIDCreator, 'pupilsightPersonIDLastEdit' => $pupilsightPersonIDLastEdit);
                        $sql = 'INSERT INTO pupilsightPlannerEntry SET pupilsightCourseClassID=:pupilsightCourseClassID, date=:date, timeStart=:timeStart, timeEnd=:timeEnd, pupilsightUnitID=:pupilsightUnitID, name=:name, summary=:summary, description=:description, teachersNotes=:teachersNotes, homework=:homework, homeworkDueDateTime=:homeworkDueDate, homeworkDetails=:homeworkDetails, homeworkSubmission=:homeworkSubmission, homeworkSubmissionDateOpen=:homeworkSubmissionDateOpen, homeworkSubmissionDrafts=:homeworkSubmissionDrafts, homeworkSubmissionType=:homeworkSubmissionType, homeworkSubmissionRequired=:homeworkSubmissionRequired, homeworkCrowdAssess=:homeworkCrowdAssess, homeworkCrowdAssessOtherTeachersRead=:homeworkCrowdAssessOtherTeachersRead, homeworkCrowdAssessClassmatesRead=:homeworkCrowdAssessClassmatesRead, homeworkCrowdAssessOtherStudentsRead=:homeworkCrowdAssessOtherStudentsRead, homeworkCrowdAssessSubmitterParentsRead=:homeworkCrowdAssessSubmitterParentsRead, homeworkCrowdAssessClassmatesParentsRead=:homeworkCrowdAssessClassmatesParentsRead, homeworkCrowdAssessOtherParentsRead=:homeworkCrowdAssessOtherParentsRead, viewableParents=:viewableParents, viewableStudents=:viewableStudents, pupilsightPersonIDCreator=:pupilsightPersonIDCreator, pupilsightPersonIDLastEdit=:pupilsightPersonIDLastEdit';
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
                        $URL .= "&return=error2$params";
                        header("Location: {$URL}");
                        exit();
                    }

                    $partialFail = false;

                    //Try to duplicate MB columns
                    $duplicate = $_POST['duplicate'];
                    if ($duplicate == 'Y') {
                        try {
                            $dataMarkbook = array('pupilsightPlannerEntryID' => $pupilsightPlannerEntryID);
                            $sqlMarkbook = 'SELECT * FROM pupilsightMarkbookColumn WHERE pupilsightPlannerEntryID=:pupilsightPlannerEntryID';
                            $resultMarkbook = $connection2->prepare($sqlMarkbook);
                            $resultMarkbook->execute($dataMarkbook);
                        } catch (PDOException $e) {
                            $partialFail = true;
                        }
                        while ($rowMarkbook = $resultMarkbook->fetch()) {
                            try {
                                $dataMarkbookInsert = array('pupilsightUnitID' => $pupilsightUnitID, 'pupilsightPlannerEntryID' => $AI, 'pupilsightCourseClassID' => $pupilsightCourseClassID, 'name' => $rowMarkbook['name'], 'description' => $rowMarkbook['description'], 'type' => $rowMarkbook['type'], 'attainment' => $rowMarkbook['attainment'], 'pupilsightScaleIDAttainment' => $rowMarkbook['pupilsightScaleIDAttainment'], 'effort' => $rowMarkbook['effort'], 'pupilsightScaleIDEffort' => $rowMarkbook['pupilsightScaleIDEffort'], 'comment' => $rowMarkbook['comment'], 'viewableStudents' => $rowMarkbook['viewableStudents'], 'viewableParents' => $rowMarkbook['viewableParents'], 'attachment' => $rowMarkbook['attachment'], 'pupilsightPersonID1' => $_SESSION[$guid]['pupilsightPersonID'], 'pupilsightPersonID2' => $_SESSION[$guid]['pupilsightPersonID']);
                                $sqlMarkbookInsert = "INSERT INTO pupilsightMarkbookColumn SET pupilsightUnitID=:pupilsightUnitID, pupilsightPlannerEntryID=:pupilsightPlannerEntryID, pupilsightCourseClassID=:pupilsightCourseClassID, name=:name, description=:description, type=:type, attainment=:attainment, pupilsightScaleIDAttainment=:pupilsightScaleIDAttainment, effort=:effort, pupilsightScaleIDEffort=:pupilsightScaleIDEffort, comment=:comment, completeDate=NULL, complete='N' ,viewableStudents=:viewableStudents, viewableParents=:viewableParents ,attachment=:attachment, pupilsightPersonIDCreator=:pupilsightPersonID1, pupilsightPersonIDLastEdit=:pupilsightPersonID2";
                                $resultMarkbookInsert = $connection2->prepare($sqlMarkbookInsert);
                                $resultMarkbookInsert->execute($dataMarkbookInsert);
                            } catch (PDOException $e) {
                                $partialFail = true;
                            }
                        }
                    }

                    //DUPLICATE SMART BLOCKS
                    if ($pupilsightUnitClassID != null) {
                        try {
                            $dataBlocks = array('pupilsightPlannerEntryID' => $pupilsightPlannerEntryID);
                            $sqlBlocks = 'SELECT * FROM pupilsightUnitClassBlock WHERE pupilsightPlannerEntryID=:pupilsightPlannerEntryID';
                            $resultBlocks = $connection2->prepare($sqlBlocks);
                            $resultBlocks->execute($dataBlocks);
                        } catch (PDOException $e) {
                            $partialFail = true;
                        }
                        while ($rowBlocks = $resultBlocks->fetch()) {
                            try {
                                $dataBlocksInsert = array('pupilsightUnitClassID' => $pupilsightUnitClassID, 'pupilsightPlannerEntryID' => $AI, 'pupilsightUnitBlockID' => $rowBlocks['pupilsightUnitBlockID'], 'title' => $rowBlocks['title'], 'type' => $rowBlocks['type'], 'length' => $rowBlocks['length'], 'contents' => $rowBlocks['contents'], 'teachersNotes' => $rowBlocks['teachersNotes'], 'sequenceNumber' => $rowBlocks['sequenceNumber']);
                                $sqlBlocksInsert = "INSERT INTO pupilsightUnitClassBlock SET pupilsightUnitClassID=:pupilsightUnitClassID, pupilsightPlannerEntryID=:pupilsightPlannerEntryID, pupilsightUnitBlockID=:pupilsightUnitBlockID, title=:title, type=:type, length=:length, contents=:contents, teachersNotes=:teachersNotes, sequenceNumber=:sequenceNumber, complete='N'";
                                $resultBlocksInsert = $connection2->prepare($sqlBlocksInsert);
                                $resultBlocksInsert->execute($dataBlocksInsert);
                            } catch (PDOException $e) {
                                $partialFail = true;
                            }
                        }
                    }

                    //DUPLICATE OUTCOMES
                    try {
                        $dataBlocks = array('pupilsightPlannerEntryID' => $pupilsightPlannerEntryID);
                        $sqlBlocks = 'SELECT * FROM pupilsightPlannerEntryOutcome WHERE pupilsightPlannerEntryID=:pupilsightPlannerEntryID';
                        $resultBlocks = $connection2->prepare($sqlBlocks);
                        $resultBlocks->execute($dataBlocks);
                    } catch (PDOException $e) {
                        $partialFail = true;
                    }
                    while ($rowBlocks = $resultBlocks->fetch()) {
                        try {
                            $dataBlocksInsert = array('pupilsightPlannerEntryID' => $AI, 'pupilsightOutcomeID' => $rowBlocks['pupilsightOutcomeID'], 'sequenceNumber' => $rowBlocks['sequenceNumber'], 'content' => $rowBlocks['content']);
                            $sqlBlocksInsert = 'INSERT INTO pupilsightPlannerEntryOutcome SET pupilsightPlannerEntryID=:pupilsightPlannerEntryID, pupilsightOutcomeID=:pupilsightOutcomeID, sequenceNumber=:sequenceNumber, content=:content';
                            $resultBlocksInsert = $connection2->prepare($sqlBlocksInsert);
                            $resultBlocksInsert->execute($dataBlocksInsert);
                        } catch (PDOException $e) {
                            $partialFail = true;
                        }
                    }

                    if ($partialFail == true) {
                        $URL .= "&return=warning1$params";
                        header("Location: {$URL}");
                    } else {
                        if ($pupilsightSchoolYearID == $_SESSION[$guid]['pupilsightSchoolYearID']) {
                            $URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_POST['address'])."/planner_edit.php&pupilsightPlannerEntryID=$AI";
                            $URL .= "&return=success1$params";
                        } else {
                            $URL .= "&return=success0$params";
                        }
                        header("Location: {$URL}");
                    }
                }
            }
        }
    }
}
