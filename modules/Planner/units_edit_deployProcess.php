<?php
/*
Pupilsight, Flexible & Open School System
*/

include '../../pupilsight.php';

$pupilsightSchoolYearID = $_GET['pupilsightSchoolYearID'];
$pupilsightCourseID = $_GET['pupilsightCourseID'];
$pupilsightCourseClassID = $_GET['pupilsightCourseClassID'];
$pupilsightUnitID = $_GET['pupilsightUnitID'];
$pupilsightUnitClassID = $_GET['pupilsightUnitClassID'];
$orders = $_POST['order'];

$URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_GET['address'])."/units_edit.php&pupilsightSchoolYearID=$pupilsightSchoolYearID&pupilsightCourseID=$pupilsightCourseID&pupilsightUnitID=$pupilsightUnitID";

if (isActionAccessible($guid, $connection2, '/modules/Planner/units_edit_deploy.php') == false) {
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
        if ($pupilsightSchoolYearID == '' or $pupilsightCourseID == '' or $pupilsightUnitID == '' or $pupilsightUnitClassID == '' or $orders == '') {
            $URL .= '&return=error3';
            header("Location: {$URL}");
        } else {
            //Check access to specified course
            try {
                if ($highestAction == 'Unit Planner_all') {
                    $data = array('pupilsightSchoolYearID' => $pupilsightSchoolYearID, 'pupilsightCourseID' => $pupilsightCourseID);
                    $sql = 'SELECT * FROM pupilsightCourse WHERE pupilsightSchoolYearID=:pupilsightSchoolYearID AND pupilsightCourseID=:pupilsightCourseID';
                } elseif ($highestAction == 'Unit Planner_learningAreas') {
                    $data = array('pupilsightSchoolYearID' => $pupilsightSchoolYearID, 'pupilsightCourseID' => $pupilsightCourseID, 'pupilsightPersonID' => $_SESSION[$guid]['pupilsightPersonID']);
                    $sql = "SELECT pupilsightCourseID, pupilsightCourse.name, pupilsightCourse.nameShort FROM pupilsightCourse JOIN pupilsightDepartment ON (pupilsightCourse.pupilsightDepartmentID=pupilsightDepartment.pupilsightDepartmentID) JOIN pupilsightDepartmentStaff ON (pupilsightDepartmentStaff.pupilsightDepartmentID=pupilsightDepartment.pupilsightDepartmentID) WHERE pupilsightDepartmentStaff.pupilsightPersonID=:pupilsightPersonID AND (role='Coordinator' OR role='Assistant Coordinator' OR role='Teacher (Curriculum)') AND pupilsightSchoolYearID=:pupilsightSchoolYearID AND pupilsightCourseID=:pupilsightCourseID ORDER BY pupilsightCourse.nameShort";
                }
                $result = $connection2->prepare($sql);
                $result->execute($data);
            } catch (PDOException $e) {
                $URL .= '&return=error2a';
                header("Location: {$URL}");
                exit();
            }

            if ($result->rowCount() != 1) {
                $URL .= '&return=error4';
                header("Location: {$URL}");
            } else {
                //Check existence of specified unit
                try {
                    $data = array('pupilsightUnitID' => $pupilsightUnitID, 'pupilsightCourseID' => $pupilsightCourseID);
                    $sql = 'SELECT pupilsightCourse.nameShort AS courseName, pupilsightUnit.* FROM pupilsightUnit JOIN pupilsightCourse ON (pupilsightUnit.pupilsightCourseID=pupilsightCourse.pupilsightCourseID) WHERE pupilsightUnitID=:pupilsightUnitID AND pupilsightUnit.pupilsightCourseID=:pupilsightCourseID';
                    $result = $connection2->prepare($sql);
                    $result->execute($data);
                } catch (PDOException $e) {
                    $URL .= '&return=error2b';
                    header("Location: {$URL}");
                    exit();
                }

                if ($result->rowCount() != 1) {
                    $URL .= '&return=error4';
                    header("Location: {$URL}");
                } else {
                    $row = $result->fetch();

                    $partialFail = false;

                    //CREATE LESSON PLANS
                    try {
                        $sql = 'LOCK TABLES pupilsightPlannerEntry WRITE, pupilsightUnitClassBlock WRITE';
                        $result = $connection2->query($sql);
                    } catch (PDOException $e) {
                        $URL .= '&return=error2e';
                        header("Location: {$URL}");
                        exit();
                    }

                    //Get next autoincrement
                    try {
                        $sqlAI = "SHOW TABLE STATUS LIKE 'pupilsightPlannerEntry'";
                        $resultAI = $connection2->query($sqlAI);
                    } catch (PDOException $e) {
                        $URL .= '&return=error2f';
                        header("Location: {$URL}");
                        exit();
                    }

                    $rowAI = $resultAI->fetch();
                    $AI = str_pad($rowAI['Auto_increment'], 14, '0', STR_PAD_LEFT);

                    $lessonCount = 0;
                    $sequenceNumber = 0;
                    $lessDescriptions = array();
                    foreach ($orders as $order) {
                        //It is a lesson, so add it
                        if (strpos($order, 'lessonHeader-') !== false) {
                            if ($lessonCount != 0) {
                                ++$AI;
                                $AI = str_pad($AI, 14, '0', STR_PAD_LEFT);
                            }
                            $summary = 'Part of the '.$row['name'].' unit.';
                            $lessonDescriptions[$AI][0] = $AI;
                            $lessonDescriptions[$AI][1] = '';
                            $teachersNotes = getSettingByScope($connection2, 'Planner', 'teachersNotesTemplate');
                            $viewableStudents = $_POST['viewableStudents'];
                            $viewableParents = $_POST['viewableParents'];

                            try {
                                $data = array('pupilsightPlannerEntryID' => $AI, 'pupilsightCourseClassID' => $pupilsightCourseClassID, 'date' => $_POST["date$lessonCount"], 'timeStart' => $_POST["timeStart$lessonCount"], 'timeEnd' => $_POST["timeEnd$lessonCount"], 'pupilsightUnitID' => $pupilsightUnitID, 'name' => $row['name'].' '.($lessonCount + 1), 'summary' => $summary, 'teachersNotes' => $teachersNotes, 'viewableParents' => $viewableParents, 'viewableStudents' => $viewableStudents, 'pupilsightPersonIDCreator' => $_SESSION[$guid]['pupilsightPersonID'], 'pupilsightPersonIDLastEdit' => $_SESSION[$guid]['pupilsightPersonID']);
                                $sql = "INSERT INTO pupilsightPlannerEntry SET pupilsightPlannerEntryID=:pupilsightPlannerEntryID, pupilsightCourseClassID=:pupilsightCourseClassID, date=:date, timeStart=:timeStart, timeEnd=:timeEnd, pupilsightUnitID=:pupilsightUnitID, name=:name, summary=:summary, description='', teachersNotes=:teachersNotes, homework='N', viewableParents=:viewableParents, viewableStudents=:viewableStudents, pupilsightPersonIDCreator=:pupilsightPersonIDCreator, pupilsightPersonIDLastEdit=:pupilsightPersonIDLastEdit";
                                $result = $connection2->prepare($sql);
                                $result->execute($data);
                            } catch (PDOException $e) {
                                $partialFail = true;
                            }
                            ++$lessonCount;
                        }
                        //It is a block, so add it to the last added lesson
                        else {
                            $titles = $_POST['title'.$order];
                            $lessonDescriptions[$AI][1] .= $titles.', ';
                            $types = $_POST['type'.$order];
                            $lengths = $_POST['length'.$order];
                            $contents = $_POST['contents'.$order];
                            $teachersNotes = $_POST['teachersNotes'.$order];
                            $pupilsightUnitBlockID = $_POST['pupilsightUnitBlockID'.$order];

                            try {
                                $data = array('pupilsightUnitClassID' => $pupilsightUnitClassID, 'pupilsightPlannerEntryID' => $AI, 'pupilsightUnitBlockID' => $pupilsightUnitBlockID, 'title' => $titles, 'type' => $types, 'length' => $lengths, 'contents' => $contents, 'teachersNotes' => $teachersNotes, 'sequenceNumber' => $sequenceNumber);
                                $sql = "INSERT INTO pupilsightUnitClassBlock SET pupilsightUnitClassID=:pupilsightUnitClassID, pupilsightPlannerEntryID=:pupilsightPlannerEntryID, pupilsightUnitBlockID=:pupilsightUnitBlockID, title=:title, type=:type, length=:length, contents=:contents, teachersNotes=:teachersNotes, sequenceNumber=:sequenceNumber, complete='N'";
                                $result = $connection2->prepare($sql);
                                $result->execute($data);
                            } catch (PDOException $e) {
                                $partialFail = true;
                            }
                            ++$sequenceNumber;
                        }
                    }

                    //Update lesson description
                    foreach ($lessonDescriptions as $lessonDescription) {
                        $lessonDescription[1] = substr($lessonDescription[1], 0, -2);
                        if (strlen($lessonDescription[1]) > 75) {
                            $lessonDescription[1] = substr($lessonDescription[1], 0, 72).'...';
                        }
                        try {
                            $data = array('summary' => $lessonDescription[1], 'pupilsightPlannerEntryID' => $lessonDescription[0]);
                            $sql = 'UPDATE pupilsightPlannerEntry SET summary=:summary WHERE pupilsightPlannerEntryID=:pupilsightPlannerEntryID';
                            $result = $connection2->prepare($sql);
                            $result->execute($data);
                        } catch (PDOException $e) {
                            $partialFail = true;
                        }
                    }

                    //RETURN
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
