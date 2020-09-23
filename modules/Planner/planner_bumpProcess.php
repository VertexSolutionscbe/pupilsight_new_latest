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
$date = $_POST['date'];
$direction = $_POST['direction'];
$URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_POST['address'])."/planner_bump.php&pupilsightPlannerEntryID=$pupilsightPlannerEntryID";
$URLBump = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_POST['address']).'/planner.php';

//Params to pass back (viewBy + date or classID)
$params = "&viewBy=$viewBy&pupilsightCourseClassID=$pupilsightCourseClassID&subView=$subView";

if (isActionAccessible($guid, $connection2, '/modules/Planner/planner_bump.php') == false) {
    $URL .= "&return=error0$params";
    header("Location: {$URL}");
} else {
    $highestAction = getHighestGroupedAction($guid, $_POST['address'], $connection2);
    if ($highestAction == false) {
        $URL .= "&return=error0$params";
        header("Location: {$URL}");
    } else {
        //Proceed!
        if (($direction != 'forward' and $direction != 'backward') or $pupilsightPlannerEntryID == '' or $viewBy == 'date' or ($viewBy == 'class' and $pupilsightCourseClassID == 'Y')) {
            $URL .= "&return=error1$params";
            header("Location: {$URL}");
        } else {
            try {
                if ($highestAction == 'Lesson Planner_viewEditAllClasses') {
                    $data = array('pupilsightCourseClassID' => $pupilsightCourseClassID, 'pupilsightPlannerEntryID' => $pupilsightPlannerEntryID);
                    $sql = 'SELECT pupilsightPlannerEntryID, pupilsightUnitID, pupilsightCourse.nameShort AS course, pupilsightCourseClass.nameShort AS class, pupilsightPlannerEntry.name, date, timeStart, timeEnd FROM pupilsightPlannerEntry JOIN pupilsightCourseClass ON (pupilsightPlannerEntry.pupilsightCourseClassID=pupilsightCourseClass.pupilsightCourseClassID) JOIN pupilsightCourse ON (pupilsightCourse.pupilsightCourseID=pupilsightCourseClass.pupilsightCourseID) WHERE pupilsightPlannerEntry.pupilsightCourseClassID=:pupilsightCourseClassID AND pupilsightPlannerEntryID=:pupilsightPlannerEntryID';
                } else {
                    $data = array('pupilsightCourseClassID' => $pupilsightCourseClassID, 'pupilsightPlannerEntryID' => $pupilsightPlannerEntryID, 'pupilsightPersonID' => $_SESSION[$guid]['pupilsightPersonID']);
                    $sql = "SELECT pupilsightPlannerEntryID, pupilsightUnitID, pupilsightCourse.nameShort AS course, pupilsightCourseClass.nameShort AS class, pupilsightPlannerEntry.name, role, date, timeStart, timeEnd FROM pupilsightPlannerEntry JOIN pupilsightCourseClass ON (pupilsightPlannerEntry.pupilsightCourseClassID=pupilsightCourseClass.pupilsightCourseClassID) JOIN pupilsightCourseClassPerson ON (pupilsightCourseClass.pupilsightCourseClassID=pupilsightCourseClassPerson.pupilsightCourseClassID) JOIN pupilsightCourse ON (pupilsightCourse.pupilsightCourseID=pupilsightCourseClass.pupilsightCourseID) WHERE pupilsightCourseClassPerson.pupilsightPersonID=:pupilsightPersonID AND role='Teacher' AND pupilsightPlannerEntry.pupilsightCourseClassID=:pupilsightCourseClassID AND pupilsightPlannerEntryID=:pupilsightPlannerEntryID";
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
                $partialFail = false;

                if ($direction == 'forward') { //BUMP FORWARD
                    try {
                        $dataList = array('pupilsightCourseClassID' => $pupilsightCourseClassID, 'date' => $row['date'], 'timeStart' => $row['timeStart'], 'timeEnd' => $row['timeEnd']);
                        $sqlList = 'SELECT * FROM pupilsightPlannerEntry WHERE pupilsightPlannerEntry.pupilsightCourseClassID=:pupilsightCourseClassID AND (date>=:date OR (date=:date AND timeStart>=:timeStart)) ORDER BY date DESC, timeStart DESC';
                        $resultList = $connection2->prepare($sqlList);
                        $resultList->execute($dataList);
                    } catch (PDOException $e) {
                        $URL .= "&return=error2$params";
                        header("Location: {$URL}");
                        exit();
                    }
                    while ($rowList = $resultList->fetch()) {
                        //Look for next available slot
                        try {
                            $dataNext = array('pupilsightCourseClassID' => $pupilsightCourseClassID, 'date' => $rowList['date']);
                            $sqlNext = 'SELECT timeStart, timeEnd, date FROM pupilsightTTDayRowClass JOIN pupilsightTTColumnRow ON (pupilsightTTDayRowClass.pupilsightTTColumnRowID=pupilsightTTColumnRow.pupilsightTTColumnRowID) JOIN pupilsightTTColumn ON (pupilsightTTColumnRow.pupilsightTTColumnID=pupilsightTTColumn.pupilsightTTColumnID) JOIN pupilsightTTDay ON (pupilsightTTDayRowClass.pupilsightTTDayID=pupilsightTTDay.pupilsightTTDayID) JOIN pupilsightTTDayDate ON (pupilsightTTDayDate.pupilsightTTDayID=pupilsightTTDay.pupilsightTTDayID) WHERE pupilsightCourseClassID=:pupilsightCourseClassID AND date>=:date ORDER BY date, timestart LIMIT 0, 10';
                            $resultNext = $connection2->prepare($sqlNext);
                            $resultNext->execute($dataNext);
                        } catch (PDOException $e) {
                            $partialFail = true;
                        }
                        while ($rowNext = $resultNext->fetch()) {
                            if (isSchoolOpen($guid, $row['date'], $connection2)) {
                                try {
                                    $dataPlanner = array('date' => $rowNext['date'], 'timeStart' => $rowNext['timeStart'], 'timeEnd' => $rowNext['timeEnd'], 'pupilsightCourseClassID' => $pupilsightCourseClassID);
                                    $sqlPlanner = 'SELECT * FROM pupilsightPlannerEntry WHERE date=:date AND timeStart=:timeStart AND timeEnd=:timeEnd AND pupilsightCourseClassID=:pupilsightCourseClassID';
                                    $resultPlanner = $connection2->prepare($sqlPlanner);
                                    $resultPlanner->execute($dataPlanner);
                                } catch (PDOException $e) {
                                    $partialFail = true;
                                }
                                if ($resultPlanner->rowCount() == 0) {
                                    try {
                                        $dataNext = array('pupilsightPlannerEntryID' => $rowList['pupilsightPlannerEntryID'], 'date' => $rowNext['date'], 'timeStart' => $rowNext['timeStart'], 'timeEnd' => $rowNext['timeEnd']);
                                        $sqlNext = 'UPDATE pupilsightPlannerEntry  set date=:date, timeStart=:timeStart, timeEnd=:timeEnd WHERE pupilsightPlannerEntryID=:pupilsightPlannerEntryID';
                                        $resultNext = $connection2->prepare($sqlNext);
                                        $resultNext->execute($dataNext);
                                    } catch (PDOException $e) {
                                        $partialFail = true;
                                    }
                                    break;
                                }
                            }
                        }
                    }
                } else { //BUMP BACKWARD
                    try {
                        $dataList = array('pupilsightCourseClassID' => $pupilsightCourseClassID, 'date' => $row['date'], 'timeStart' => $row['timeStart'], 'timeEnd' => $row['timeEnd']);
                        $sqlList = 'SELECT * FROM pupilsightPlannerEntry WHERE pupilsightPlannerEntry.pupilsightCourseClassID=:pupilsightCourseClassID AND (date<=:date OR (date=:date AND timeStart<=:timeStart)) ORDER BY date, timeStart';
                        $resultList = $connection2->prepare($sqlList);
                        $resultList->execute($dataList);
                    } catch (PDOException $e) {
                        $URL .= "&return=error2$params";
                        header("Location: {$URL}");
                        exit();
                    }
                    while ($rowList = $resultList->fetch()) {
                        //Look for last available slot
                        try {
                            $dataNext = array('pupilsightCourseClassID' => $pupilsightCourseClassID, 'date' => $rowList['date']);
                            $sqlNext = 'SELECT timeStart, timeEnd, date FROM pupilsightTTDayRowClass JOIN pupilsightTTColumnRow ON (pupilsightTTDayRowClass.pupilsightTTColumnRowID=pupilsightTTColumnRow.pupilsightTTColumnRowID) JOIN pupilsightTTColumn ON (pupilsightTTColumnRow.pupilsightTTColumnID=pupilsightTTColumn.pupilsightTTColumnID) JOIN pupilsightTTDay ON (pupilsightTTDayRowClass.pupilsightTTDayID=pupilsightTTDay.pupilsightTTDayID) JOIN pupilsightTTDayDate ON (pupilsightTTDayDate.pupilsightTTDayID=pupilsightTTDay.pupilsightTTDayID) WHERE pupilsightCourseClassID=:pupilsightCourseClassID AND date<=:date ORDER BY date DESC, timestart DESC LIMIT 0, 10';
                            $resultNext = $connection2->prepare($sqlNext);
                            $resultNext->execute($dataNext);
                        } catch (PDOException $e) {
                            $partialFail = true;
                        }
                        while ($rowNext = $resultNext->fetch()) {
                            if (isSchoolOpen($guid, $row['date'], $connection2)) {
                                try {
                                    $dataPlanner = array('date' => $rowNext['date'], 'timeStart' => $rowNext['timeStart'], 'timeEnd' => $rowNext['timeEnd'], 'pupilsightCourseClassID' => $pupilsightCourseClassID);
                                    $sqlPlanner = 'SELECT * FROM pupilsightPlannerEntry WHERE date=:date AND timeStart=:timeStart AND timeEnd=:timeEnd AND pupilsightCourseClassID=:pupilsightCourseClassID';
                                    $resultPlanner = $connection2->prepare($sqlPlanner);
                                    $resultPlanner->execute($dataPlanner);
                                } catch (PDOException $e) {
                                    $partialFail = true;
                                }
                                if ($resultPlanner->rowCount() == 0) {
                                    try {
                                        $dataNext = array('pupilsightPlannerEntryID' => $rowList['pupilsightPlannerEntryID'], 'date' => $rowNext['date'], 'timeStart' => $rowNext['timeStart'], 'timeEnd' => $rowNext['timeEnd']);
                                        $sqlNext = 'UPDATE pupilsightPlannerEntry  set date=:date, timeStart=:timeStart, timeEnd=:timeEnd WHERE pupilsightPlannerEntryID=:pupilsightPlannerEntryID';
                                        $resultNext = $connection2->prepare($sqlNext);
                                        $resultNext->execute($dataNext);
                                    } catch (PDOException $e) {
                                        $partialFail = true;
                                    }
                                    break;
                                }
                            }
                        }
                    }
                }

                //Write to database
                if ($partialFail == true) {
                    $URL .= "&return=error5$params";
                    header("Location: {$URL}");
                } else {
                    $URL = $URLBump."&return=success1$params";
                    header("Location: {$URL}");
                }
            }
        }
    }
}
