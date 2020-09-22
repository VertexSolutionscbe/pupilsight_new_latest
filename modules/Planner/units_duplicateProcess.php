<?php
/*
Pupilsight, Flexible & Open School System
*/

include '../../pupilsight.php';

//Module includes
include './moduleFunctions.php';

$pupilsightSchoolYearID = $_GET['pupilsightSchoolYearID'];
$pupilsightCourseID = $_GET['pupilsightCourseID'];
$pupilsightUnitID = $_GET['pupilsightUnitID'];
$URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_GET['address'])."/units_duplicate.php&pupilsightUnitID=$pupilsightUnitID&pupilsightCourseID=$pupilsightCourseID&pupilsightSchoolYearID=$pupilsightSchoolYearID";

if (isActionAccessible($guid, $connection2, '/modules/Planner/units_duplicate.php') == false) {
    $URL .= '&return=error0';
    header("Location: {$URL}");
} else {
    $highestAction = getHighestGroupedAction($guid, $_GET['address'], $connection2);
    if ($highestAction == false) {
        $URL .= "&return=error0$params";
        header("Location: {$URL}");
    } else {
        //Proceed!
        //Validate Inputs
        $pupilsightCourseIDTarget = $_POST['pupilsightCourseIDTarget'];
        $copyLessons = $_POST['copyLessons'];

        if ($pupilsightSchoolYearID == '' or $pupilsightCourseID == '' or $pupilsightUnitID == '' or $pupilsightCourseIDTarget == '') {
            $URL .= '&return=error3';
            header("Location: {$URL}");
        } else {
            //Lock table
            try {
                $sql = 'LOCK TABLE pupilsightUnit WRITE';
                $result = $connection2->query($sql);
            } catch (PDOException $e) {
                $URL .= '&return=error2';
                header("Location: {$URL}");
                exit();
            }

            //Get next autoincrement for unit
            try {
                $sqlAI = "SHOW TABLE STATUS LIKE 'pupilsightUnit'";
                $resultAI = $connection2->query($sqlAI);
            } catch (PDOException $e) {
                $URL .= '&return=error2';
                header("Location: {$URL}");
                exit();
            }

            $rowAI = $resultAI->fetch();
            $AI = str_pad($rowAI['Auto_increment'], 8, '0', STR_PAD_LEFT);
            $partialFail = false;

            //Unlock locked database tables
            try {
                $sql = 'UNLOCK TABLES';
                $result = $connection2->query($sql);
            } catch (PDOException $e) {
            }

            if ($AI == '') {
                $URL .= '&return=error2';
                header("Location: {$URL}");
            } else {
                //Write to database
                try {
                    $data = array('pupilsightUnitID' => $pupilsightUnitID);
                    $sql = 'SELECT * FROM pupilsightUnit WHERE pupilsightUnitID=:pupilsightUnitID';
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
                    $name = $row['name'];
                    if ($pupilsightCourseIDTarget == $pupilsightCourseID) {
                        $name .= ' (Copy)';
                    }
                    try {
                        $data = array('pupilsightCourseID' => $pupilsightCourseIDTarget, 'name' => $name, 'description' => $row['description'], 'map' => $row['map'], 'tags' => $row['tags'], 'ordering' => $row['ordering'], 'attachment' => $row['attachment'], 'details' => $row['details'], 'pupilsightPersonIDCreator' => $_SESSION[$guid]['pupilsightPersonID'], 'pupilsightPersonIDLastEdit' => $_SESSION[$guid]['pupilsightPersonID']);
                        $sql = 'INSERT INTO pupilsightUnit SET pupilsightCourseID=:pupilsightCourseID, name=:name, description=:description, map=:map, tags=:tags, ordering=:ordering, attachment=:attachment, details=:details ,pupilsightPersonIDCreator=:pupilsightPersonIDCreator, pupilsightPersonIDLastEdit=:pupilsightPersonIDLastEdit';
                        $result = $connection2->prepare($sql);
                        $result->execute($data);
                    } catch (PDOException $e) {
                        $URL .= '&return=error2';
                        header("Location: {$URL}");
                        exit();
                    }

                    //Copy Outcomes
                    try {
                        $dataOutcomes = array('pupilsightUnitID' => $pupilsightUnitID);
                        $sqlOutcomes = 'SELECT * FROM pupilsightUnitOutcome WHERE pupilsightUnitID=:pupilsightUnitID';
                        $resultOutcomes = $connection2->prepare($sqlOutcomes);
                        $resultOutcomes->execute($dataOutcomes);
                    } catch (PDOException $e) {
                        $partialFail = true;
                    }

                    if ($resultOutcomes->rowCount() > 0) {
                        while ($rowOutcomes = $resultOutcomes->fetch()) {
                            //Write to database
                            try {
                                $dataCopy = array('pupilsightUnitID' => $AI, 'pupilsightOutcomeID' => $rowOutcomes['pupilsightOutcomeID'], 'sequenceNumber' => $rowOutcomes['sequenceNumber'], 'content' => $rowOutcomes['content']);
                                $sqlCopy = 'INSERT INTO pupilsightUnitOutcome SET pupilsightUnitID=:pupilsightUnitID, pupilsightOutcomeID=:pupilsightOutcomeID, sequenceNumber=:sequenceNumber, content=:content';
                                $resultCopy = $connection2->prepare($sqlCopy);
                                $resultCopy->execute($dataCopy);
                            } catch (PDOException $e) {
                                $partialFail = true;
                            }
                        }
                    }

                    //Copy Lessons & resources
                    if ($copyLessons == 'Y') {
                        $pupilsightCourseClassIDSource = $_POST['pupilsightCourseClassIDSource'];
                        $pupilsightCourseClassIDTarget = null;
                        if (isset($_POST['pupilsightCourseClassIDTarget'])) {
                            $pupilsightCourseClassIDTarget = $_POST['pupilsightCourseClassIDTarget'];
                        }

                        if ($pupilsightCourseClassIDSource == '' or count($pupilsightCourseClassIDTarget) < 1 or $AI == '') {
                            $URL .= '&return=error1';
                            header("Location: {$URL}");
                        } else {
                            foreach ($pupilsightCourseClassIDTarget as $t) {
                                //Turn class on
                                try {
                                    $dataOn = array('pupilsightUnitID' => $AI, 'pupilsightCourseClassID' => $t);
                                    $sqlOn = "INSERT INTO pupilsightUnitClass SET pupilsightUnitID=:pupilsightUnitID, pupilsightCourseClassID=:pupilsightCourseClassID, running='Y'";
                                    $resultOn = $connection2->prepare($sqlOn);
                                    $resultOn->execute($dataOn);
                                } catch (PDOException $e) {
                                    $partialFail = true;
                                }

                                $pupilsightUnitClassIDNew = $connection2->lastInsertID();

                                //Get lessons
                                try {
                                    $dataLessons = array('pupilsightCourseClassID' => $pupilsightCourseClassIDSource, 'pupilsightUnitID' => $pupilsightUnitID);
                                    $sqlLessons = 'SELECT * FROM pupilsightPlannerEntry WHERE pupilsightCourseClassID=:pupilsightCourseClassID AND pupilsightUnitID=:pupilsightUnitID';
                                    $resultLessons = $connection2->prepare($sqlLessons);
                                    $resultLessons->execute($dataLessons);
                                } catch (PDOException $e) {
                                    $partialFail = true;
                                }

                                if ($resultLessons->rowCount() > 0) {
                                    //Copy Lessons
                                    while ($rowLesson = $resultLessons->fetch()) {
                                        $copyOK = true;
                                        //Write to database
                                        try {
                                            $dataCopy = array('pupilsightCourseClassID' => $t, 'pupilsightUnitID' => $AI, 'name' => $rowLesson['name'], 'summary' => $rowLesson['summary'], 'description' => $rowLesson['description'], 'teachersNotes' => $rowLesson['teachersNotes'], 'homework' => $rowLesson['homework'], 'homeworkDetails' => $rowLesson['homeworkDetails'], 'homeworkSubmission' => $rowLesson['homeworkSubmission'], 'homeworkSubmissionDrafts' => $rowLesson['homeworkSubmissionDrafts'], 'homeworkSubmissionType' => $rowLesson['homeworkSubmissionType'], 'viewableStudents' => $rowLesson['viewableStudents'], 'viewableParents' => $rowLesson['viewableParents'], 'pupilsightPersonIDCreator' => $_SESSION[$guid]['pupilsightPersonID'], 'pupilsightPersonIDLastEdit' => $_SESSION[$guid]['pupilsightPersonID']);
                                            $sqlCopy = "INSERT INTO pupilsightPlannerEntry SET pupilsightCourseClassID=:pupilsightCourseClassID, pupilsightUnitID=:pupilsightUnitID, date=NULL, timeStart=NULL, timeEnd=NULL, name=:name, summary=:summary, description=:description, teachersNotes=:teachersNotes, homework=:homework, homeworkDueDateTime=NULL, homeworkDetails=:homeworkDetails, homeworkSubmission=:homeworkSubmission, homeworkSubmissionDateOpen=NULL, homeworkSubmissionDrafts=:homeworkSubmissionDrafts, homeworkSubmissionType=:homeworkSubmissionType, homeworkCrowdAssess='N', homeworkCrowdAssessOtherTeachersRead='N', homeworkCrowdAssessOtherParentsRead='N', homeworkCrowdAssessClassmatesParentsRead='N', homeworkCrowdAssessSubmitterParentsRead='N', homeworkCrowdAssessOtherStudentsRead='N', homeworkCrowdAssessClassmatesRead='N', viewableStudents=:viewableStudents, viewableParents=:viewableParents, pupilsightPersonIDCreator=:pupilsightPersonIDCreator, pupilsightPersonIDLastEdit=:pupilsightPersonIDLastEdit";
                                            $resultCopy = $connection2->prepare($sqlCopy);
                                            $resultCopy->execute($dataCopy);
                                        } catch (PDOException $e) {
                                            $partialFail = true;
                                            $copyOK = false;
                                        }
                                        if ($copyOK == true) {
                                            //Copy blocks for this lesson
                                            $pupilsightPlannerEntryNew = $connection2->lastInsertID();

                                            try {
                                                $dataBlocks = array('pupilsightPlannerEntryID' => $rowLesson['pupilsightPlannerEntryID']);
                                                $sqlBlocks = 'SELECT * FROM pupilsightUnitClassBlock WHERE pupilsightPlannerEntryID=:pupilsightPlannerEntryID ORDER BY sequenceNumber';
                                                $resultBlocks = $connection2->prepare($sqlBlocks);
                                                $resultBlocks->execute($dataBlocks);
                                            } catch (PDOException $e) {
                                                $partialFail = true;
                                            }
                                            while ($rowBlocks = $resultBlocks->fetch()) {
                                                try {
                                                    $dataBlock = array('pupilsightPlannerEntryID' => $pupilsightPlannerEntryNew, 'pupilsightUnitClassID' => $pupilsightUnitClassIDNew, 'pupilsightUnitBlockID' => $rowBlocks['pupilsightUnitBlockID'], 'title' => $rowBlocks['title'], 'type' => $rowBlocks['type'], 'length' => $rowBlocks['length'], 'contents' => $rowBlocks['contents'], 'teachersNotes' => $rowBlocks['teachersNotes'], 'sequenceNumber' => $rowBlocks['sequenceNumber']);
                                                    $sqlBlock = 'INSERT INTO pupilsightUnitClassBlock SET pupilsightPlannerEntryID=:pupilsightPlannerEntryID, pupilsightUnitClassID=:pupilsightUnitClassID, pupilsightUnitBlockID=:pupilsightUnitBlockID, title=:title, type=:type, length=:length, contents=:contents, teachersNotes=:teachersNotes, sequenceNumber=:sequenceNumber';
                                                    $resultBlock = $connection2->prepare($sqlBlock);
                                                    $resultBlock->execute($dataBlock);
                                                } catch (PDOException $e) {
                                                    $partialFail = true;
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }

                    try {
                        $dataBlocks = array('pupilsightUnitID' => $pupilsightUnitID);
                        $sqlBlocks = 'SELECT * FROM pupilsightUnitBlock WHERE pupilsightUnitID=:pupilsightUnitID ORDER BY sequenceNumber';
                        $resultBlocks = $connection2->prepare($sqlBlocks);
                        $resultBlocks->execute($dataBlocks);
                    } catch (PDOException $e) {
                        $partialFail = true;
                    }
                    while ($rowBlocks = $resultBlocks->fetch()) {
                        try {
                            $dataBlock = array('pupilsightUnitID' => $AI, 'title' => $rowBlocks['title'], 'type' => $rowBlocks['type'], 'length' => $rowBlocks['length'], 'contents' => $rowBlocks['contents'], 'teachersNotes' => $rowBlocks['teachersNotes'], 'sequenceNumber' => $rowBlocks['sequenceNumber']);
                            $sqlBlock = 'INSERT INTO pupilsightUnitBlock SET pupilsightUnitID=:pupilsightUnitID, title=:title, type=:type, length=:length, contents=:contents, teachersNotes=:teachersNotes, sequenceNumber=:sequenceNumber';
                            $resultBlock = $connection2->prepare($sqlBlock);
                            $resultBlock->execute($dataBlock);
                        } catch (PDOException $e) {
                            $partialFail = true;
                        }
                    }

                    if ($partialFail == true) {
                        $URL .= '&return=error6';
                        header("Location: {$URL}");
                    } else {
                        $URL .= '&return=success0';
                        header("Location: {$URL}");
                    }
                }
            }
        }
    }
}
