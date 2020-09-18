<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Forms\Form;

//Module includes
require_once __DIR__ . '/moduleFunctions.php';

$style = '';

$highestAction = getHighestGroupedAction($guid, $_GET['q'], $connection2);
if (isActionAccessible($guid, $connection2, '/modules/Planner/planner_deadlines.php') == false) {
    //Acess denied
    echo "<div class='alert alert-danger'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    //Set variables
    $today = date('Y-m-d');

    //Proceed!
    //Get viewBy, date and class variables
    $params = [];
    $viewBy = null;
    if (isset($_GET['viewBy'])) {
        $viewBy = $_GET['viewBy'];
    }
    $subView = null;
    if (isset($_GET['subView'])) {
        $subView = $_GET['subView'];
    }
    if ($viewBy != 'date' and $viewBy != 'class') {
        $viewBy = 'date';
    }
    $pupilsightCourseClassID = null;
    $date = null;
    $dateStamp = null;
    if ($viewBy == 'date') {
        if (isset($_GET['date'])) {
            $date = $_GET['date'];
        }
        if (isset($_GET['dateHuman'])) {
            $date = dateConvert($guid, $_GET['dateHuman']);
        }
        if ($date == '') {
            $date = date('Y-m-d');
        }
        list($dateYear, $dateMonth, $dateDay) = explode('-', $date);
        $dateStamp = mktime(0, 0, 0, $dateMonth, $dateDay, $dateYear);
        $params += [
            'viewBy' => 'date',
            'date' => $date,
        ];
    } elseif ($viewBy == 'class') {
        $class = null;
        if (isset($_GET['class'])) {
            $class = $_GET['class'];
        }
        $pupilsightCourseClassID = $_GET['pupilsightCourseClassID'];
        $params += [
            'viewBy' => 'class',
            'date' => $class,
            'pupilsightCourseClassID' => $pupilsightCourseClassID,
        ];
    }
    list($todayYear, $todayMonth, $todayDay) = explode('-', $today);
    $todayStamp = mktime(0, 0, 0, $todayMonth, $todayDay, $todayYear);
    $show = null;
    if (isset($_GET['show'])) {
        $show = $_GET['show'];
    }
    $pupilsightCourseClassIDFilter = null;
    if (isset($_GET['pupilsightCourseClassIDFilter'])) {
        $pupilsightCourseClassIDFilter = $_GET['pupilsightCourseClassIDFilter'];
    }
    $pupilsightPersonID = null;
    if (isset($_GET['search'])) {
        $pupilsightPersonID = $_GET['search'];
    }

    //My children's classes
    if ($highestAction == 'Lesson Planner_viewMyChildrensClasses') {

        $page->breadcrumbs
            ->add(__('My Children\'s Classes'), 'planner.php')
            ->add(__('Homework + Deadlines'));

        //Test data access field for permission
        try {
            $data = array('pupilsightPersonID' => $_SESSION[$guid]['pupilsightPersonID']);
            $sql = "SELECT * FROM pupilsightFamilyAdult WHERE pupilsightPersonID=:pupilsightPersonID AND childDataAccess='Y'";
            $result = $connection2->prepare($sql);
            $result->execute($data);
        } catch (PDOException $e) {
            echo "<div class='alert alert-danger'>".$e->getMessage().'</div>';
        }
        if ($result->rowCount() < 1) {
            echo "<div class='alert alert-danger'>";
            echo __('Access denied.');
            echo '</div>';
        } else {
            //Get child list
            $count = 0;
            $options = array();
            while ($row = $result->fetch()) {
                try {
                    $dataChild = array('pupilsightFamilyID' => $row['pupilsightFamilyID'], 'pupilsightSchoolYearID' => $_SESSION[$guid]['pupilsightSchoolYearID']);
                    $sqlChild = "SELECT * FROM pupilsightFamilyChild JOIN pupilsightPerson ON (pupilsightFamilyChild.pupilsightPersonID=pupilsightPerson.pupilsightPersonID) JOIN pupilsightStudentEnrolment ON (pupilsightPerson.pupilsightPersonID=pupilsightStudentEnrolment.pupilsightPersonID) JOIN pupilsightRollGroup ON (pupilsightStudentEnrolment.pupilsightRollGroupID=pupilsightRollGroup.pupilsightRollGroupID) WHERE pupilsightFamilyID=:pupilsightFamilyID AND pupilsightPerson.status='Full' AND (dateStart IS NULL OR dateStart<='".date('Y-m-d')."') AND (dateEnd IS NULL  OR dateEnd>='".date('Y-m-d')."') AND pupilsightStudentEnrolment.pupilsightSchoolYearID=:pupilsightSchoolYearID ORDER BY surname, preferredName ";
                    $resultChild = $connection2->prepare($sqlChild);
                    $resultChild->execute($dataChild);
                } catch (PDOException $e) {
                    echo "<div class='alert alert-danger'>".$e->getMessage().'</div>';
                }

                while ($rowChild = $resultChild->fetch()) {
                    $options[$rowChild['pupilsightPersonID']] = formatName('', $rowChild['preferredName'], $rowChild['surname'], 'Student');
                    $pupilsightPersonIDArray[$count] = $rowChild['pupilsightPersonID'];
                    ++$count;
                }
            }

            if ($count == 0) {
                echo "<div class='alert alert-danger'>";
                echo __('Access denied.');
                echo '</div>';
            } elseif ($count == 1) {
                $pupilsightPersonID = $pupilsightPersonIDArray[0];
            } else {
                echo '<h3>';
                echo __('Choose');
                echo '</h3>';

                $form = Form::create('action', $_SESSION[$guid]['absoluteURL'].'/index.php', 'get');

                $form->setClass('noIntBorder fullWidth');

                $form->addHiddenValue('address', $_SESSION[$guid]['address']);
                $form->addHiddenValue('q', '/modules/'.$_SESSION[$guid]['module'].'/planner_deadlines.php');
                if (isset($pupilsightCourseClassID) && $pupilsightCourseClassID != '') {
                    $form->addHiddenValue('pupilsightCourseClassID', $pupilsightCourseClassID);
                    $form->addHiddenValue('viewBy', 'class');
                }
                else {
                    $form->addHiddenValue('viewBy', 'date');
                }

                $row = $form->addRow();
                $row->addLabel('search', __('Student'));
                $row->addSelect('search')->fromArray($options)->selected($pupilsightPersonID)->placeholder();

                $row = $form->addRow();
                    $row->addFooter();
                    $row->addSearchSubmit($pupilsight->session);

                echo $form->getOutput();
            }

            if ($pupilsightPersonID != '' and $count > 0) {
                //Confirm access to this student
                try {
                    $dataChild = array('pupilsightPersonID' => $pupilsightPersonID, 'pupilsightPersonID2' => $_SESSION[$guid]['pupilsightPersonID']);
                    $sqlChild = "SELECT * FROM pupilsightFamilyChild JOIN pupilsightFamily ON (pupilsightFamilyChild.pupilsightFamilyID=pupilsightFamily.pupilsightFamilyID) JOIN pupilsightFamilyAdult ON (pupilsightFamilyAdult.pupilsightFamilyID=pupilsightFamily.pupilsightFamilyID) JOIN pupilsightPerson ON (pupilsightFamilyChild.pupilsightPersonID=pupilsightPerson.pupilsightPersonID) WHERE pupilsightPerson.status='Full' AND (dateStart IS NULL OR dateStart<='".date('Y-m-d')."') AND (dateEnd IS NULL  OR dateEnd>='".date('Y-m-d')."') AND pupilsightFamilyChild.pupilsightPersonID=:pupilsightPersonID AND pupilsightFamilyAdult.pupilsightPersonID=:pupilsightPersonID2 AND childDataAccess='Y'";
                    $resultChild = $connection2->prepare($sqlChild);
                    $resultChild->execute($dataChild);
                } catch (PDOException $e) {
                    echo "<div class='alert alert-danger'>".$e->getMessage().'</div>';
                }
                if ($resultChild->rowCount() < 1) {
                    echo "<div class='alert alert-danger'>";
                    echo __('The selected record does not exist, or you do not have access to it.');
                    echo '</div>';
                } else {
                    $rowChild = $resultChild->fetch();

                    echo '<h3>';
                    echo __('Upcoming Deadlines');
                    echo '</h3>';

                    $proceed = true;
                    if ($viewBy == 'class') {
                        if ($pupilsightCourseClassID == '') {
                            $proceed = false;
                        } else {
                            try {
                                $data = array('pupilsightSchoolYearID' => $_SESSION[$guid]['pupilsightSchoolYearID'], 'pupilsightPersonID' => $pupilsightPersonID, 'pupilsightCourseClassID' => $pupilsightCourseClassID);
                                $sql = "SELECT pupilsightCourse.pupilsightCourseID, pupilsightCourseClass.pupilsightCourseClassID, pupilsightCourse.nameShort AS course, pupilsightCourseClass.nameShort AS class FROM pupilsightCourseClassPerson JOIN pupilsightCourseClass ON (pupilsightCourseClassPerson.pupilsightCourseClassID=pupilsightCourseClass.pupilsightCourseClassID) JOIN pupilsightCourse ON (pupilsightCourseClass.pupilsightCourseID=pupilsightCourse.pupilsightCourseID) WHERE pupilsightCourse.pupilsightSchoolYearID=:pupilsightSchoolYearID AND pupilsightPersonID=:pupilsightPersonID AND pupilsightCourseClass.pupilsightCourseClassID=:pupilsightCourseClassID AND role='Teacher' ORDER BY course, class";
                                $result = $connection2->prepare($sql);
                                $result->execute($data);
                            } catch (PDOException $e) {
                                echo "<div class='alert alert-danger'>".$e->getMessage().'</div>';
                            }
                            if ($result->rowCount() != 1) {
                                $proceed = false;
                            }
                        }
                    }

                    if ($proceed == false) {
                        echo "<div class='alert alert-danger'>";
                        echo __('Your request failed because you do not have access to this action.');
                        echo '</div>';
                    } else {
                        try {
                            $data = array('pupilsightPersonID' => $pupilsightPersonID, 'pupilsightSchoolYearID' => $_SESSION[$guid]['pupilsightSchoolYearID']);
                            $sql = "
							(SELECT 'teacherRecorded' AS type, pupilsightPlannerEntryID, pupilsightUnitID, pupilsightCourse.nameShort AS course, pupilsightCourseClass.nameShort AS class, pupilsightPlannerEntry.name, date, timeStart, timeEnd, viewableStudents, viewableParents, homework, homeworkDueDateTime, role FROM pupilsightPlannerEntry JOIN pupilsightCourseClass ON (pupilsightPlannerEntry.pupilsightCourseClassID=pupilsightCourseClass.pupilsightCourseClassID) JOIN pupilsightCourseClassPerson ON (pupilsightCourseClass.pupilsightCourseClassID=pupilsightCourseClassPerson.pupilsightCourseClassID) JOIN pupilsightCourse ON (pupilsightCourse.pupilsightCourseID=pupilsightCourseClass.pupilsightCourseID) WHERE pupilsightSchoolYearID=:pupilsightSchoolYearID AND pupilsightCourseClassPerson.pupilsightPersonID=:pupilsightPersonID AND NOT role='Student - Left' AND NOT role='Teacher - Left' AND homework='Y' AND (role='Teacher' OR (role='Student' AND viewableStudents='Y')) AND homeworkDueDateTime>'".date('Y-m-d H:i:s')."' AND ((date<'".date('Y-m-d')."') OR (date='".date('Y-m-d')."' AND timeEnd<='".date('H:i:s')."')))
							UNION
							(SELECT 'studentRecorded' AS type, pupilsightPlannerEntry.pupilsightPlannerEntryID, pupilsightUnitID, pupilsightCourse.nameShort AS course, pupilsightCourseClass.nameShort AS class, pupilsightPlannerEntry.name, date, timeStart, timeEnd, 'Y' AS viewableStudents, 'Y' AS viewableParents, 'Y' AS homework, pupilsightPlannerEntryStudentHomework.homeworkDueDateTime, role FROM pupilsightPlannerEntry JOIN pupilsightCourseClass ON (pupilsightPlannerEntry.pupilsightCourseClassID=pupilsightCourseClass.pupilsightCourseClassID) JOIN pupilsightCourseClassPerson ON (pupilsightCourseClass.pupilsightCourseClassID=pupilsightCourseClassPerson.pupilsightCourseClassID) JOIN pupilsightCourse ON (pupilsightCourse.pupilsightCourseID=pupilsightCourseClass.pupilsightCourseID) JOIN pupilsightPlannerEntryStudentHomework ON (pupilsightPlannerEntryStudentHomework.pupilsightPlannerEntryID=pupilsightPlannerEntry.pupilsightPlannerEntryID AND pupilsightPlannerEntryStudentHomework.pupilsightPersonID=pupilsightCourseClassPerson.pupilsightPersonID) WHERE pupilsightSchoolYearID=:pupilsightSchoolYearID AND pupilsightCourseClassPerson.pupilsightPersonID=:pupilsightPersonID AND NOT role='Student - Left' AND NOT role='Teacher - Left' AND (role='Teacher' OR (role='Student' AND viewableStudents='Y')) AND pupilsightPlannerEntryStudentHomework.homeworkDueDateTime>'".date('Y-m-d H:i:s')."' AND ((date<'".date('Y-m-d')."') OR (date='".date('Y-m-d')."' AND timeEnd<='".date('H:i:s')."')))
							ORDER BY homeworkDueDateTime, type";
                            $result = $connection2->prepare($sql);
                            $result->execute($data);
                        } catch (PDOException $e) {
                            echo "<div class='alert alert-danger'>".$e->getMessage().'</div>';
                        }

                        if ($result->rowCount() < 1) {
                            echo "<div class='alert alert-sucess'>";
                            echo __('No upcoming deadlines!');
                            echo '</div>';
                        } else {
                            echo '<ol>';
                            while ($row = $result->fetch()) {
                                $diff = (strtotime(substr($row['homeworkDueDateTime'], 0, 10)) - strtotime(date('Y-m-d'))) / 86400;
                                $style = "style='padding-right: 3px;'";
                                if ($diff < 2) {
                                    $style = "style='padding-right: 3px; border-right: 10px solid #cc0000'";
                                } elseif ($diff < 4) {
                                    $style = "style='padding-right: 3px; border-right: 10px solid #D87718'";
                                }
                                echo "<li $style>";
                                if ($viewBy == 'class') {
                                    echo "<b><a href='".$_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.$_SESSION[$guid]['module'].'/planner_view_full.php&pupilsightPlannerEntryID='.$row['pupilsightPlannerEntryID']."&viewBy=class&pupilsightCourseClassID=$pupilsightCourseClassID&search=".$pupilsightPersonID."'>".$row['course'].'.'.$row['class'].'</a> - '.$row['name'].'</b><br/>';
                                } else {
                                    echo "<b><a href='".$_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.$_SESSION[$guid]['module'].'/planner_view_full.php&pupilsightPlannerEntryID='.$row['pupilsightPlannerEntryID']."&viewBy=date&date=$date&search=".$pupilsightPersonID."'>".$row['course'].'.'.$row['class'].'</a> - '.$row['name'].'</b><br/>';
                                }
                                echo "<span style='margin-left: 15px; font-style: italic'>Due at ".substr($row['homeworkDueDateTime'], 11, 5).' on '.dateConvertBack($guid, substr($row['homeworkDueDateTime'], 0, 10));
                                echo '</li>';
                            }
                            echo '</ol>';
                        }
                    }

                    $style = '';

                    echo '<h3>';
                    echo __('All Homework');
                    echo '</h3>';

                    $filter = null;
                    $filter2 = null;
                    $data = array();
                    if ($pupilsightCourseClassIDFilter != '') {
                        $data['pupilsightCourseClassIDFilter'] = $pupilsightCourseClassIDFilter;
                        $data['pupilsightCourseClassIDFilter2'] = $pupilsightCourseClassIDFilter;
                        $filter = ' AND pupilsightPlannerEntry.pupilsightCourseClassID=:pupilsightCourseClassIDFilter';
                        $filte2 = ' AND pupilsightPlannerEntry.pupilsightCourseClassID=:pupilsightCourseClassIDFilte2';
                    }

                    try {
                        $data['pupilsightPersonID'] = $pupilsightPersonID;
                        $data['pupilsightSchoolYearID'] = $_SESSION[$guid]['pupilsightSchoolYearID'];
                        $sql = "
						(SELECT 'teacherRecorded' AS type, pupilsightPlannerEntryID, pupilsightUnitID, pupilsightPlannerEntry.pupilsightCourseClassID, pupilsightCourse.nameShort AS course, pupilsightCourseClass.nameShort AS class, pupilsightPlannerEntry.name, date, timeStart, timeEnd, viewableStudents, viewableParents, homework, role, homeworkDueDateTime, homeworkDetails, homeworkSubmission, homeworkSubmissionRequired FROM pupilsightPlannerEntry JOIN pupilsightCourseClass ON (pupilsightPlannerEntry.pupilsightCourseClassID=pupilsightCourseClass.pupilsightCourseClassID) JOIN pupilsightCourseClassPerson ON (pupilsightCourseClass.pupilsightCourseClassID=pupilsightCourseClassPerson.pupilsightCourseClassID) JOIN pupilsightCourse ON (pupilsightCourse.pupilsightCourseID=pupilsightCourseClass.pupilsightCourseID) WHERE pupilsightCourseClassPerson.pupilsightPersonID=:pupilsightPersonID AND NOT role='Student - Left' AND NOT role='Teacher - Left' AND homework='Y' AND pupilsightSchoolYearID=:pupilsightSchoolYearID AND (date<'".date('Y-m-d')."' OR (date='".date('Y-m-d')."' AND timeEnd<='".date('H:i:s')."')) $filter)
						UNION
						(SELECT 'studentRecorded' AS type, pupilsightPlannerEntry.pupilsightPlannerEntryID, pupilsightUnitID, pupilsightPlannerEntry.pupilsightCourseClassID, pupilsightCourse.nameShort AS course, pupilsightCourseClass.nameShort AS class, pupilsightPlannerEntry.name, date, timeStart, timeEnd, 'Y' AS viewableStudents, 'Y' AS viewableParents, 'Y' AS homework, role, pupilsightPlannerEntryStudentHomework.homeworkDueDateTime AS homeworkDueDateTime, pupilsightPlannerEntryStudentHomework.homeworkDetails AS homeworkDetails, 'N' AS homeworkSubmission, '' AS homeworkSubmissionRequired FROM pupilsightPlannerEntry JOIN pupilsightCourseClass ON (pupilsightPlannerEntry.pupilsightCourseClassID=pupilsightCourseClass.pupilsightCourseClassID) JOIN pupilsightCourseClassPerson ON (pupilsightCourseClass.pupilsightCourseClassID=pupilsightCourseClassPerson.pupilsightCourseClassID) JOIN pupilsightCourse ON (pupilsightCourse.pupilsightCourseID=pupilsightCourseClass.pupilsightCourseID) JOIN pupilsightPlannerEntryStudentHomework ON (pupilsightPlannerEntryStudentHomework.pupilsightPlannerEntryID=pupilsightPlannerEntry.pupilsightPlannerEntryID AND pupilsightPlannerEntryStudentHomework.pupilsightPersonID=pupilsightCourseClassPerson.pupilsightPersonID) WHERE pupilsightCourseClassPerson.pupilsightPersonID=:pupilsightPersonID AND NOT role='Student - Left' AND NOT role='Teacher - Left' AND pupilsightSchoolYearID=:pupilsightSchoolYearID AND (date<'".date('Y-m-d')."' OR (date='".date('Y-m-d')."' AND timeEnd<='".date('H:i:s')."')) $filter)
						ORDER BY date DESC, timeStart DESC";
                        $result = $connection2->prepare($sql);
                        $result->execute($data);
                    } catch (PDOException $e) {
                        echo "<div class='alert alert-danger'>".$e->getMessage().'</div>';
                    }

                    //Only show add if user has edit rights
                    if ($result->rowCount() < 1) {
                        echo "<div class='alert alert-danger'>";
                        echo __('There are no records to display.');
                        echo '</div>';
                    } else {
                        echo "<div class='linkTop'>";
                        echo "<form method='get' action='".$_SESSION[$guid]['absoluteURL']."/index.php'>";
                        echo "<table class='blank' cellspacing='0' style='float: right; width: 250px'>";
                        echo '<tr>';
                        echo "<td style='width: 190px'>";
                        echo "<select name='pupilsightCourseClassIDFilter' id='pupilsightCourseClassIDFilter' style='width:190px'>";
                        echo "<option value=''></option>";
                        try {
                            $dataSelect = array('pupilsightPersonID' => $pupilsightPersonID, 'pupilsightSchoolYearID' => $_SESSION[$guid]['pupilsightSchoolYearID'], 'date' => date('Y-m-d'));
                            $sqlSelect = "SELECT DISTINCT pupilsightCourse.nameShort AS course, pupilsightCourseClass.nameShort AS class, pupilsightPlannerEntry.pupilsightCourseClassID FROM pupilsightPlannerEntry JOIN pupilsightCourseClass ON (pupilsightPlannerEntry.pupilsightCourseClassID=pupilsightCourseClass.pupilsightCourseClassID) JOIN pupilsightCourseClassPerson ON (pupilsightCourseClass.pupilsightCourseClassID=pupilsightCourseClassPerson.pupilsightCourseClassID) JOIN pupilsightCourse ON (pupilsightCourse.pupilsightCourseID=pupilsightCourseClass.pupilsightCourseID) WHERE pupilsightCourseClassPerson.pupilsightPersonID=:pupilsightPersonID AND NOT role='Student - Left' AND NOT role='Teacher - Left' AND homework='Y' AND pupilsightSchoolYearID=:pupilsightSchoolYearID AND date<=:date ORDER BY course, class";
                            $resultSelect = $connection2->prepare($sqlSelect);
                            $resultSelect->execute($dataSelect);
                        } catch (PDOException $e) {
                        }
                        while ($rowSelect = $resultSelect->fetch()) {
                            $selected = '';
                            if ($rowSelect['pupilsightCourseClassID'] == $pupilsightCourseClassIDFilter) {
                                $selected = 'selected';
                            }
                            echo "<option $selected value='".$rowSelect['pupilsightCourseClassID']."'>".htmlPrep($rowSelect['course']).'.'.htmlPrep($rowSelect['class']).'</option>';
                        }
                        echo '</select>';
                        echo '</td>';
                        echo "<td class='right'>";
                        echo "<input type='submit' value='".__('Go')."' style='margin-right: 0px'>";
                        echo "<input type='hidden' name='q' value='/modules/Planner/planner_deadlines.php'>";
                        echo "<input type='hidden' name='search' value='$pupilsightPersonID'>";
                        echo '</td>';
                        echo '</tr>';
                        echo '</table>';
                        echo '</form>';
                        echo '</div>';
                        echo "<table cellspacing='0' style='width: 100%'>";
                        echo "<tr class='head'>";
                        echo '<th>';
                        echo __('Class').'</br>';
                        echo "<span style='font-size: 85%; font-style: italic'>".__('Date').'</span>';
                        echo '</th>';
                        echo '<th>';
                        echo __('Lesson').'</br>';
                        echo "<span style='font-size: 85%; font-style: italic'>".__('Unit').'</span>';
                        echo '</th>';
                        echo "<th style='min-width: 25%'>";
                        echo __('Type').'<br/>';
                        echo "<span style='font-size: 85%; font-style: italic'>".__('Details').'</span>';
                        echo '</th>';
                        echo '<th>';
                        echo __('Deadline');
                        echo '</th>';
                        echo '<th>';
                        echo sprintf(__('Online%1$sSubmission'), '<br/>');
                        echo '</th>';
                        echo '<th>';
                        echo __('Actions');
                        echo '</th>';
                        echo '</tr>';

                        $count = 0;
                        $rowNum = 'odd';
                        while ($row = $result->fetch()) {
                            if (!($row['role'] == 'Student' and $row['viewableParents'] == 'N')) {
                                if ($count % 2 == 0) {
                                    $rowNum = 'even';
                                } else {
                                    $rowNum = 'odd';
                                }
                                ++$count;

                                    //Highlight class in progress
                                    if ((date('Y-m-d') == $row['date']) and (date('H:i:s') > $row['timeStart']) and (date('H:i:s') < $row['timeEnd'])) {
                                        $rowNum = 'current';
                                    }

                                    //COLOR ROW BY STATUS!
                                    echo "<tr class=$rowNum>";
                                echo '<td>';
                                echo '<b>'.$row['course'].'.'.$row['class'].'</b></br>';
                                echo "<span style='font-size: 85%; font-style: italic'>".dateConvertBack($guid, $row['date']).'</span>';
                                echo '</td>';
                                echo '<td>';
                                echo '<b>'.$row['name'].'</b><br/>';
                                echo "<span style='font-size: 85%; font-style: italic'>";
                                $unit = getUnit($connection2, $row['pupilsightUnitID'], $row['pupilsightCourseClassID']);
                                if (isset($unit[0])) {
                                    echo $unit[0];
                                    if ($unit[1] != '') {
                                        echo '<br/><i>'.$unit[1].' Unit</i>';
                                    }
                                }
                                echo '</span>';
                                echo '</td>';
                                echo '<td>';
                                if ($row['type'] == 'teacherRecorded') {
                                    echo 'Teacher Recorded';
                                } else {
                                    echo 'Student Recorded';
                                }
                                echo  '<br/>';
                                echo "<span style='font-size: 85%; font-style: italic'>";
                                if ($row['homeworkDetails'] != '') {
                                    if (strlen(strip_tags($row['homeworkDetails'])) < 21) {
                                        echo strip_tags($row['homeworkDetails']);
                                    } else {
                                        echo "<span $style title='".htmlPrep(strip_tags($row['homeworkDetails']))."'>".substr(strip_tags($row['homeworkDetails']), 0, 20).'...</span>';
                                    }
                                }
                                echo '</span>';
                                echo '</td>';
                                echo '<td>';
                                echo dateConvertBack($guid, substr($row['homeworkDueDateTime'], 0, 10));
                                echo '</td>';
                                echo '<td>';
                                if ($row['homeworkSubmission'] == 'Y') {
                                    echo '<b>'.$row['homeworkSubmissionRequired'].'<br/></b>';
                                    if ($row['role'] == 'Student') {
                                        try {
                                            $dataVersion = array('pupilsightPlannerEntryID' => $row['pupilsightPlannerEntryID'], 'pupilsightPersonID' => $pupilsightPersonID);
                                            $sqlVersion = 'SELECT * FROM pupilsightPlannerEntryHomework WHERE pupilsightPlannerEntryID=:pupilsightPlannerEntryID AND pupilsightPersonID=:pupilsightPersonID ORDER BY count DESC';
                                            $resultVersion = $connection2->prepare($sqlVersion);
                                            $resultVersion->execute($dataVersion);
                                        } catch (PDOException $e) {
                                            echo "<div class='alert alert-danger'>".$e->getMessage().'</div>';
                                        }

                                        if ($resultVersion->rowCount() < 1) {
                                            //Before deadline
                                                        if (date('Y-m-d H:i:s') < $row['homeworkDueDateTime']) {
                                                            echo __('Pending');
                                                        }
                                                        //After
                                                        else {
                                                            if ($row['homeworkSubmissionRequired'] == 'Compulsory') {
                                                                echo "<div style='color: #ff0000; font-weight: bold; border: 2px solid #ff0000; padding: 2px 4px; margin: 2px 0px'>".__('Incomplete').'</div>';
                                                            } else {
                                                                echo  __('Not submitted online');
                                                            }
                                                        }
                                        } else {
                                            $rowVersion = $resultVersion->fetch();
                                            if ($rowVersion['status'] == 'On Time' or $rowVersion['status'] == 'Exemption') {
                                                echo $rowVersion['status'];
                                            } else {
                                                if ($row['homeworkSubmissionRequired'] == 'Compulsory') {
                                                    echo "<div style='color: #ff0000; font-weight: bold; border: 2px solid #ff0000; padding: 2px 4px; margin: 2px 0px'>".$rowVersion['status'].'</div>';
                                                } else {
                                                    echo $rowVersion['status'];
                                                }
                                            }
                                        }
                                    }
                                }
                                echo '</td>';
                                echo '<td>';
                                echo "<a href='".$_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.$_SESSION[$guid]['module']."/planner_view_full.php&search=$pupilsightPersonID&pupilsightPlannerEntryID=".$row['pupilsightPlannerEntryID'].'&viewBy=class&pupilsightCourseClassID='.$row['pupilsightCourseClassID']."'><img title='".__('View')."' src='./themes/".$_SESSION[$guid]['pupilsightThemeName']."/img/plus.png'/></a> ";
                                echo '</td>';
                                echo '</tr>';
                            }
                        }
                        echo '</table>';
                    }
                }
            }
        }
    } elseif ($highestAction == 'Lesson Planner_viewMyClasses' or $highestAction == 'Lesson Planner_viewAllEditMyClasses' or $highestAction == 'Lesson Planner_viewEditAllClasses') {
        //Get current role category
        $category = getRoleCategory($_SESSION[$guid]['pupilsightRoleIDCurrent'], $connection2);

        $page->breadcrumbs
            ->add(__('Planner'), 'planner.php', $params)
            ->add(__('Homework + Deadlines'));

        //Get Smart Workflow help message
        $category = getRoleCategory($_SESSION[$guid]['pupilsightRoleIDCurrent'], $connection2);
        if ($category == 'Staff') {
            $smartWorkflowHelp = getSmartWorkflowHelp($connection2, $guid, 4);
            if ($smartWorkflowHelp != false) {
                echo $smartWorkflowHelp;
            }
        }

        //Proceed!
        if (isset($_GET['return'])) {
            returnProcess($guid, $_GET['return'], null, null);
        }

        echo '<h3>';
        echo __('Upcoming Deadlines');
        echo '</h3>';

        $proceed = true;
        if ($viewBy == 'class') {
            if ($pupilsightCourseClassID == '') {
                $proceed = false;
            } else {
                try {
                    if ($highestAction == 'Lesson Planner_viewEditAllClasses') {
                        $data = array('pupilsightSchoolYearID' => $_SESSION[$guid]['pupilsightSchoolYearID'], 'pupilsightCourseClassID' => $pupilsightCourseClassID);
                        $sql = 'SELECT pupilsightCourse.pupilsightCourseID, pupilsightCourseClass.pupilsightCourseClassID, pupilsightCourse.nameShort AS course, pupilsightCourseClass.nameShort AS class FROM pupilsightCourseClass JOIN pupilsightCourse ON (pupilsightCourseClass.pupilsightCourseID=pupilsightCourse.pupilsightCourseID) WHERE pupilsightCourse.pupilsightSchoolYearID=:pupilsightSchoolYearID AND pupilsightCourseClass.pupilsightCourseClassID=:pupilsightCourseClassID ORDER BY course, class';
                    } else {
                        $data = array('pupilsightSchoolYearID' => $_SESSION[$guid]['pupilsightSchoolYearID'], 'pupilsightCourseClassID' => $pupilsightCourseClassID, 'pupilsightPersonID' => $_SESSION[$guid]['pupilsightPersonID']);
                        $sql = "SELECT pupilsightCourse.pupilsightCourseID, pupilsightCourseClass.pupilsightCourseClassID, pupilsightCourse.nameShort AS course, pupilsightCourseClass.nameShort AS class FROM pupilsightCourseClassPerson JOIN pupilsightCourseClass ON (pupilsightCourseClassPerson.pupilsightCourseClassID=pupilsightCourseClass.pupilsightCourseClassID) JOIN pupilsightCourse ON (pupilsightCourseClass.pupilsightCourseID=pupilsightCourse.pupilsightCourseID) WHERE pupilsightCourse.pupilsightSchoolYearID=:pupilsightSchoolYearID AND pupilsightPersonID=:pupilsightPersonID AND pupilsightCourseClass.pupilsightCourseClassID=:pupilsightCourseClassID AND role='Teacher' ORDER BY course, class";
                    }
                    $result = $connection2->prepare($sql);
                    $result->execute($data);
                } catch (PDOException $e) {
                    echo "<div class='alert alert-danger'>".$e->getMessage().'</div>';
                }
                if ($result->rowCount() != 1) {
                    $proceed = false;
                }
            }
        }

        if ($proceed == false) {
            echo "<div class='alert alert-danger'>";
            echo __('Your request failed because you do not have access to this action.');
            echo '</div>';
        } else {
            try {
                if ($highestAction == 'Lesson Planner_viewEditAllClasses' and $show == 'all') {
                    $data = array('homeworkDueDateTime' => date('Y-m-d H:i:s'), 'date1' => date('Y-m-d'), 'date2' => date('Y-m-d'), 'timeEnd' => date('H:i:s'));
                    $sql = "SELECT pupilsightPlannerEntryID, pupilsightUnitID, pupilsightCourse.nameShort AS course, pupilsightCourseClass.nameShort AS class, pupilsightPlannerEntry.name, date, timeStart, timeEnd, viewableStudents, viewableParents, homework, homeworkDueDateTime FROM pupilsightPlannerEntry JOIN pupilsightCourseClass ON (pupilsightPlannerEntry.pupilsightCourseClassID=pupilsightCourseClass.pupilsightCourseClassID) JOIN pupilsightCourse ON (pupilsightCourse.pupilsightCourseID=pupilsightCourseClass.pupilsightCourseID) WHERE homework='Y' AND homeworkDueDateTime>:homeworkDueDateTime AND ((date<:date1) OR (date=:date2 AND timeEnd<=:timeEnd)) ORDER BY homeworkDueDateTime";
                } else {
                    $data = array('pupilsightPersonID' => $_SESSION[$guid]['pupilsightPersonID'], 'pupilsightSchoolYearID' => $_SESSION[$guid]['pupilsightSchoolYearID']);
                    $sql = "
					(SELECT 'teacherRecorded' AS type, pupilsightPlannerEntryID, pupilsightUnitID, pupilsightCourse.nameShort AS course, pupilsightCourseClass.nameShort AS class, pupilsightPlannerEntry.name, date, timeStart, timeEnd, viewableStudents, viewableParents, homework, homeworkDueDateTime, role FROM pupilsightPlannerEntry JOIN pupilsightCourseClass ON (pupilsightPlannerEntry.pupilsightCourseClassID=pupilsightCourseClass.pupilsightCourseClassID) JOIN pupilsightCourseClassPerson ON (pupilsightCourseClass.pupilsightCourseClassID=pupilsightCourseClassPerson.pupilsightCourseClassID) JOIN pupilsightCourse ON (pupilsightCourse.pupilsightCourseID=pupilsightCourseClass.pupilsightCourseID) WHERE pupilsightSchoolYearID=:pupilsightSchoolYearID AND pupilsightCourseClassPerson.pupilsightPersonID=:pupilsightPersonID AND NOT role='Student - Left' AND NOT role='Teacher - Left' AND homework='Y' AND (role='Teacher' OR (role='Student' AND viewableStudents='Y')) AND homeworkDueDateTime>'".date('Y-m-d H:i:s')."' AND ((date<'".date('Y-m-d')."') OR (date='".date('Y-m-d')."' AND timeEnd<='".date('H:i:s')."')))
					UNION
					(SELECT 'studentRecorded' AS type, pupilsightPlannerEntry.pupilsightPlannerEntryID, pupilsightUnitID, pupilsightCourse.nameShort AS course, pupilsightCourseClass.nameShort AS class, pupilsightPlannerEntry.name, date, timeStart, timeEnd, 'Y' AS viewableStudents, 'Y' AS viewableParents, 'Y' AS homework, pupilsightPlannerEntryStudentHomework.homeworkDueDateTime, role FROM pupilsightPlannerEntry JOIN pupilsightCourseClass ON (pupilsightPlannerEntry.pupilsightCourseClassID=pupilsightCourseClass.pupilsightCourseClassID) JOIN pupilsightCourseClassPerson ON (pupilsightCourseClass.pupilsightCourseClassID=pupilsightCourseClassPerson.pupilsightCourseClassID) JOIN pupilsightCourse ON (pupilsightCourse.pupilsightCourseID=pupilsightCourseClass.pupilsightCourseID) JOIN pupilsightPlannerEntryStudentHomework ON (pupilsightPlannerEntryStudentHomework.pupilsightPlannerEntryID=pupilsightPlannerEntry.pupilsightPlannerEntryID AND pupilsightPlannerEntryStudentHomework.pupilsightPersonID=pupilsightCourseClassPerson.pupilsightPersonID) WHERE pupilsightSchoolYearID=:pupilsightSchoolYearID AND pupilsightCourseClassPerson.pupilsightPersonID=:pupilsightPersonID AND NOT role='Student - Left' AND NOT role='Teacher - Left' AND (role='Teacher' OR (role='Student' AND viewableStudents='Y')) AND pupilsightPlannerEntryStudentHomework.homeworkDueDateTime>'".date('Y-m-d H:i:s')."' AND ((date<'".date('Y-m-d')."') OR (date='".date('Y-m-d')."' AND timeEnd<='".date('H:i:s')."')))
					 ORDER BY homeworkDueDateTime, type";
                }
                $result = $connection2->prepare($sql);
                $result->execute($data);
            } catch (PDOException $e) {
                echo "<div class='alert alert-danger'>".$e->getMessage().'</div>';
            }

            if ($result->rowCount() < 1) {
                echo "<div class='alert alert-sucess'>";
                echo __('No upcoming deadlines!');
                echo '</div>';
            } else {
                echo '<ol>';
                while ($row = $result->fetch()) {
                    $diff = (strtotime(substr($row['homeworkDueDateTime'], 0, 10)) - strtotime(date('Y-m-d'))) / 86400;
                    $style = 'padding-right: 3px;';
                    if ($category == 'Student') {
                        if ($row['type'] == 'teacherRecorded') {
                            //Calculate style for student-specified completion
                            try {
                                $dataCompletion = array('pupilsightPlannerEntryID' => $row['pupilsightPlannerEntryID'], 'pupilsightPersonID' => $_SESSION[$guid]['pupilsightPersonID']);
                                $sqlCompletion = "SELECT pupilsightPlannerEntryID FROM pupilsightPlannerEntryStudentTracker WHERE pupilsightPlannerEntryID=:pupilsightPlannerEntryID AND pupilsightPersonID=:pupilsightPersonID AND homeworkComplete='Y'";
                                $resultCompletion = $connection2->prepare($sqlCompletion);
                                $resultCompletion->execute($dataCompletion);
                            } catch (PDOException $e) {
                                echo "<div class='alert alert-danger'>".$e->getMessage().'</div>';
                            }
                            if ($resultCompletion->rowCount() == 1) {
                                $style .= '; background-color: #B3EFC2';
                            }
                            //Calculate style for online submission completion
                            try {
                                $dataCompletion = array('pupilsightPlannerEntryID' => $row['pupilsightPlannerEntryID'], 'pupilsightPersonID' => $_SESSION[$guid]['pupilsightPersonID']);
                                $sqlCompletion = "SELECT pupilsightPlannerEntryID FROM pupilsightPlannerEntryHomework WHERE pupilsightPlannerEntryID=:pupilsightPlannerEntryID AND pupilsightPersonID=:pupilsightPersonID AND version='Final'";
                                $resultCompletion = $connection2->prepare($sqlCompletion);
                                $resultCompletion->execute($dataCompletion);
                            } catch (PDOException $e) {
                                echo "<div class='alert alert-danger'>".$e->getMessage().'</div>';
                            }
                            if ($resultCompletion->rowCount() == 1) {
                                $style .= '; background-color: #B3EFC2';
                            }
                        } else {
                            //Calculate style for student-recorded homework
                            try {
                                $dataCompletion = array('pupilsightPlannerEntryID' => $row['pupilsightPlannerEntryID'], 'pupilsightPersonID' => $_SESSION[$guid]['pupilsightPersonID']);
                                $sqlCompletion = "SELECT pupilsightPlannerEntryID FROM pupilsightPlannerEntryStudentHomework WHERE pupilsightPlannerEntryID=:pupilsightPlannerEntryID AND pupilsightPersonID=:pupilsightPersonID AND homeworkComplete='Y'";
                                $resultCompletion = $connection2->prepare($sqlCompletion);
                                $resultCompletion->execute($dataCompletion);
                            } catch (PDOException $e) {
                                echo "<div class='alert alert-danger'>".$e->getMessage().'</div>';
                            }
                            if ($resultCompletion->rowCount() == 1) {
                                $style .= '; background-color: #B3EFC2';
                            }
                        }
                    }

                    //Calculate style for deadline
                    if ($diff < 2) {
                        $style .= '; border-right: 10px solid #cc0000';
                    } elseif ($diff < 4) {
                        $style .= '; border-right: 10px solid #D87718';
                    }

                    echo "<li style='$style'>";
                    if ($viewBy == 'class') {
                        echo "<b><a href='".$_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.$_SESSION[$guid]['module'].'/planner_view_full.php&pupilsightPlannerEntryID='.$row['pupilsightPlannerEntryID']."&viewBy=class&pupilsightCourseClassID=$pupilsightCourseClassID'>".$row['course'].'.'.$row['class'].'</a> - '.$row['name'].'</b><br/>';
                    } else {
                        echo "<b><a href='".$_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.$_SESSION[$guid]['module'].'/planner_view_full.php&pupilsightPlannerEntryID='.$row['pupilsightPlannerEntryID']."&viewBy=date&date=$date'>".$row['course'].'.'.$row['class'].'</a> - '.$row['name'].'</b><br/>';
                    }
                    echo "<span style='margin-left: 15px; font-style: italic'>Due at ".substr($row['homeworkDueDateTime'], 11, 5).' on '.dateConvertBack($guid, substr($row['homeworkDueDateTime'], 0, 10));
                    echo '</li>';
                }
                echo '</ol>';
            }
        }

        echo '<h3>';
        echo __('All Homework');
        echo '</h3>';

        $completionArray = array();
        if ($category == 'Student') {
            try {
                $dataCompletion = array('pupilsightSchoolYearID' => $_SESSION[$guid]['pupilsightSchoolYearID'], 'pupilsightPersonID' => $_SESSION[$guid]['pupilsightPersonID'], 'pupilsightSchoolYearID2' => $_SESSION[$guid]['pupilsightSchoolYearID'], 'pupilsightPersonID2' => $_SESSION[$guid]['pupilsightPersonID']);
                $sqlCompletion = "
				(SELECT 'teacherRecorded' AS type, pupilsightPlannerEntryStudentTracker.pupilsightPlannerEntryID FROM pupilsightPlannerEntryStudentTracker JOIN pupilsightPlannerEntry ON (pupilsightPlannerEntryStudentTracker.pupilsightPlannerEntryID=pupilsightPlannerEntry.pupilsightPlannerEntryID) JOIN pupilsightCourseClass ON (pupilsightPlannerEntry.pupilsightCourseClassID=pupilsightCourseClass.pupilsightCourseClassID) JOIN pupilsightCourse ON (pupilsightCourseClass.pupilsightCourseID=pupilsightCourse.pupilsightCourseID) WHERE pupilsightSchoolYearID=:pupilsightSchoolYearID AND pupilsightPersonID=:pupilsightPersonID AND homeworkComplete='Y')
				UNION
				(SELECT 'studentRecorded' AS type, pupilsightPlannerEntry.pupilsightPlannerEntryID FROM pupilsightPlannerEntryStudentHomework JOIN pupilsightPlannerEntry ON (pupilsightPlannerEntryStudentHomework.pupilsightPlannerEntryID=pupilsightPlannerEntry.pupilsightPlannerEntryID) JOIN pupilsightCourseClass ON (pupilsightPlannerEntry.pupilsightCourseClassID=pupilsightCourseClass.pupilsightCourseClassID) JOIN pupilsightCourse ON (pupilsightCourseClass.pupilsightCourseID=pupilsightCourse.pupilsightCourseID) WHERE pupilsightSchoolYearID=:pupilsightSchoolYearID2 AND pupilsightPersonID=:pupilsightPersonID2 AND homeworkComplete='Y')
				ORDER BY pupilsightPlannerEntryID, type
				";
                $resultCompletion = $connection2->prepare($sqlCompletion);
                $resultCompletion->execute($dataCompletion);
            } catch (PDOException $e) {
                echo "<div class='alert alert-danger'>".$e->getMessage().'</div>';
            }
            while ($rowCompletion = $resultCompletion->fetch()) {
                $completionArray[$rowCompletion['pupilsightPlannerEntryID']][0] = 'checked';
                $completionArray[$rowCompletion['pupilsightPlannerEntryID']][1] = $rowCompletion['type'];
            }
        }

        $filter = null;
        $filter2 = null;
        $data = array();
        if ($pupilsightCourseClassIDFilter != '') {
            $data['pupilsightCourseClassIDFilter'] = $pupilsightCourseClassIDFilter;
            $data['pupilsightCourseClassIDFilter2'] = $pupilsightCourseClassIDFilter;
            $filter = ' AND pupilsightPlannerEntry.pupilsightCourseClassID=:pupilsightCourseClassIDFilter';
            $filte2 = ' AND pupilsightPlannerEntry.pupilsightCourseClassID=:pupilsightCourseClassIDFilte2';
        }

        try {
            if ($highestAction == 'Lesson Planner_viewEditAllClasses' and $show == 'all') {
                $data = array('pupilsightSchoolYearID' => $_SESSION[$guid]['pupilsightSchoolYearID'], 'date1' => date('Y-m-d'), 'date2' => date('Y-m-d'), 'timeEnd' => date('H:i:s'));
                $sql = "SELECT pupilsightPlannerEntryID, pupilsightUnitID, pupilsightPlannerEntry.pupilsightCourseClassID, pupilsightCourse.nameShort AS course, pupilsightCourseClass.nameShort AS class, pupilsightPlannerEntry.pupilsightCourseClassID, pupilsightPlannerEntry.name, date, timeStart, timeEnd, 'Y' AS viewableStudents, 'Y' AS viewableParents, homework, homeworkDueDateTime, homeworkDetails, homeworkSubmission, homeworkSubmissionRequired, homeworkCrowdAssess FROM pupilsightPlannerEntry JOIN pupilsightCourseClass ON (pupilsightPlannerEntry.pupilsightCourseClassID=pupilsightCourseClass.pupilsightCourseClassID) JOIN pupilsightCourse ON (pupilsightCourse.pupilsightCourseID=pupilsightCourseClass.pupilsightCourseID) WHERE homework='Y' AND pupilsightSchoolYearID=:pupilsightSchoolYearID AND (date<:date1 OR (date=:date2 AND timeEnd<=:timeEnd)) $filter ORDER BY date DESC, timeStart DESC";
            } else {
                $data['pupilsightPersonID'] = $_SESSION[$guid]['pupilsightPersonID'];
                $data['pupilsightSchoolYearID'] = $_SESSION[$guid]['pupilsightSchoolYearID'];
                $data['pupilsightPersonID'] = $_SESSION[$guid]['pupilsightPersonID'];
                $sql = "
				(SELECT 'teacherRecorded' AS type, pupilsightPlannerEntryID, pupilsightUnitID, pupilsightPlannerEntry.pupilsightCourseClassID, pupilsightCourse.nameShort AS course, pupilsightCourseClass.nameShort AS class, pupilsightPlannerEntry.name, date, timeStart, timeEnd, viewableStudents, viewableParents, homework, role, homeworkDueDateTime, homeworkDetails, homeworkSubmission, homeworkSubmissionRequired FROM pupilsightPlannerEntry JOIN pupilsightCourseClass ON (pupilsightPlannerEntry.pupilsightCourseClassID=pupilsightCourseClass.pupilsightCourseClassID) JOIN pupilsightCourseClassPerson ON (pupilsightCourseClass.pupilsightCourseClassID=pupilsightCourseClassPerson.pupilsightCourseClassID) JOIN pupilsightCourse ON (pupilsightCourse.pupilsightCourseID=pupilsightCourseClass.pupilsightCourseID) WHERE pupilsightCourseClassPerson.pupilsightPersonID=:pupilsightPersonID AND NOT role='Student - Left' AND NOT role='Teacher - Left' AND homework='Y' AND pupilsightSchoolYearID=:pupilsightSchoolYearID AND ((date<'".date('Y-m-d')."') OR (date='".date('Y-m-d')."' AND timeEnd<='".date('H:i:s')."')) $filter)
				UNION
				(SELECT 'studentRecorded' AS type, pupilsightPlannerEntry.pupilsightPlannerEntryID, pupilsightUnitID, pupilsightPlannerEntry.pupilsightCourseClassID, pupilsightCourse.nameShort AS course, pupilsightCourseClass.nameShort AS class, pupilsightPlannerEntry.name, date, timeStart, timeEnd, 'Y' AS viewableStudents, 'Y' AS viewableParents, 'Y' AS homework, role, pupilsightPlannerEntryStudentHomework.homeworkDueDateTime AS homeworkDueDateTime, pupilsightPlannerEntryStudentHomework.homeworkDetails AS homeworkDetails, 'N' AS homeworkSubmission, '' AS homeworkSubmissionRequired FROM pupilsightPlannerEntry JOIN pupilsightCourseClass ON (pupilsightPlannerEntry.pupilsightCourseClassID=pupilsightCourseClass.pupilsightCourseClassID) JOIN pupilsightCourseClassPerson ON (pupilsightCourseClass.pupilsightCourseClassID=pupilsightCourseClassPerson.pupilsightCourseClassID) JOIN pupilsightCourse ON (pupilsightCourse.pupilsightCourseID=pupilsightCourseClass.pupilsightCourseID) JOIN pupilsightPlannerEntryStudentHomework ON (pupilsightPlannerEntryStudentHomework.pupilsightPlannerEntryID=pupilsightPlannerEntry.pupilsightPlannerEntryID AND pupilsightPlannerEntryStudentHomework.pupilsightPersonID=pupilsightCourseClassPerson.pupilsightPersonID) WHERE pupilsightCourseClassPerson.pupilsightPersonID=:pupilsightPersonID AND NOT role='Student - Left' AND NOT role='Teacher - Left' AND pupilsightSchoolYearID=:pupilsightSchoolYearID AND ((date<'".date('Y-m-d')."') OR (date='".date('Y-m-d')."' AND timeEnd<='".date('H:i:s')."')) $filter)
				ORDER BY date DESC, timeStart DESC";
            }
            $result = $connection2->prepare($sql);
            $result->execute($data);
        } catch (PDOException $e) {
            echo "<div class='alert alert-danger'>".$e->getMessage().'</div>';
        }

        //Only show add if user has edit rights
        if ($result->rowCount() < 1) {
            echo "<div class='alert alert-danger'>";
            echo __('There are no records to display.');
            echo '</div>';
        } else {
            echo "<div class='linkTop'>";
            echo "<form method='get' action='".$_SESSION[$guid]['absoluteURL']."/index.php'>";
            echo "<table class='blank' cellspacing='0' style='float: right; width: 250px'>";
            echo '<tr>';
            echo "<td style='width: 190px'>";
            echo "<select name='pupilsightCourseClassIDFilter' id='pupilsightCourseClassIDFilter' style='width:190px'>";
            echo "<option value=''></option>";
            try {
                if ($highestAction == 'Lesson Planner_viewEditAllClasses' and $show == 'all') {
                    $dataSelect = array('pupilsightSchoolYearID' => $_SESSION[$guid]['pupilsightSchoolYearID'], 'date' => date('Y-m-d'));
                    $sqlSelect = "SELECT DISTINCT pupilsightCourse.nameShort AS course, pupilsightCourseClass.nameShort AS class, pupilsightPlannerEntry.pupilsightCourseClassID FROM pupilsightPlannerEntry JOIN pupilsightCourseClass ON (pupilsightPlannerEntry.pupilsightCourseClassID=pupilsightCourseClass.pupilsightCourseClassID) JOIN pupilsightCourse ON (pupilsightCourse.pupilsightCourseID=pupilsightCourseClass.pupilsightCourseID) WHERE homework='Y' AND pupilsightSchoolYearID=:pupilsightSchoolYearID AND date<=:date ORDER BY course, class";
                } else {
                    $dataSelect = array('pupilsightSchoolYearID' => $_SESSION[$guid]['pupilsightSchoolYearID'], 'date' => date('Y-m-d'), 'pupilsightPersonID' => $_SESSION[$guid]['pupilsightPersonID']);
                    $sqlSelect = "SELECT DISTINCT pupilsightCourse.nameShort AS course, pupilsightCourseClass.nameShort AS class, pupilsightPlannerEntry.pupilsightCourseClassID FROM pupilsightPlannerEntry JOIN pupilsightCourseClass ON (pupilsightPlannerEntry.pupilsightCourseClassID=pupilsightCourseClass.pupilsightCourseClassID) JOIN pupilsightCourseClassPerson ON (pupilsightCourseClass.pupilsightCourseClassID=pupilsightCourseClassPerson.pupilsightCourseClassID) JOIN pupilsightCourse ON (pupilsightCourse.pupilsightCourseID=pupilsightCourseClass.pupilsightCourseID) WHERE pupilsightCourseClassPerson.pupilsightPersonID=:pupilsightPersonID AND NOT role='Student - Left' AND NOT role='Teacher - Left' AND homework='Y' AND pupilsightSchoolYearID=:pupilsightSchoolYearID AND date<=:date ORDER BY course, class";
                }
                $resultSelect = $connection2->prepare($sqlSelect);
                $resultSelect->execute($dataSelect);
            } catch (PDOException $e) {
            }
            while ($rowSelect = $resultSelect->fetch()) {
                $selected = '';
                if ($rowSelect['pupilsightCourseClassID'] == $pupilsightCourseClassIDFilter) {
                    $selected = 'selected';
                }
                echo "<option $selected value='".$rowSelect['pupilsightCourseClassID']."'>".htmlPrep($rowSelect['course']).'.'.htmlPrep($rowSelect['class']).'</option>';
            }
            echo '</select>';
            echo '</td>';
            echo "<td class='right'>";
            echo "<input type='submit' value='".__('Go')."' style='margin-right: 0px'>";
            echo "<input type='hidden' name='q' value='/modules/Planner/planner_deadlines.php'>";
            echo '</td>';
            echo '</tr>';
            echo '</table>';
            echo '</form>';
            echo '</div>';
            echo "<form method='post' action='".$_SESSION[$guid]['absoluteURL'].'/modules/'.$_SESSION[$guid]['module']."/planner_deadlinesProcess.php?viewBy=$viewBy&subView=$subView&address=".$_SESSION[$guid]['address']."&pupilsightCourseClassIDFilter=$pupilsightCourseClassIDFilter'>";
            echo "<table cellspacing='0' style='width: 100%; margin-top: 60px'>";

            if ($category == 'Student') {
                ?>
				<tr>
					<td class="right" colspan=7>
						<input type="submit" value="<?php echo __('Submit'); ?>">
					</td>
				</tr>
				<?php

            }
            echo "<tr class='head'>";
            echo '<th>';
            echo __('Class').'</br>';
            echo "<span style='font-size: 85%; font-style: italic'>".__('Date').'</span>';
            echo '</th>';
            echo '<th>';
            echo __('Lesson').'</br>';
            echo "<span style='font-size: 85%; font-style: italic'>".__('Unit').'</span>';
            echo '</th>';
            echo "<th style='min-width: 25%'>";
            echo __('Type').'<br/>';
            echo "<span style='font-size: 85%; font-style: italic'>".__('Details').'</span>';
            echo '</th>';
            echo '<th>';
            echo __('Deadline');
            echo '</th>';

            if ($category == 'Student') {
                echo '<th colspan=2>';
                echo __('Complete?');
                echo '</th>';
            } else {
                echo '<th>';
                echo sprintf(__('Online%1$sSubmission'), '<br/>');
                echo '</th>';
            }
            echo '<th>';
            echo __('Actions');
            echo '</th>';
            echo '</tr>';

            $count = 0;
            $rowNum = 'odd';
            while ($row = $result->fetch()) {
                if (!($row['role'] == 'Student' and $row['viewableStudents'] == 'N')) {
                    if ($count % 2 == 0) {
                        $rowNum = 'even';
                    } else {
                        $rowNum = 'odd';
                    }
                    ++$count;

					//Deal with homework completion
					if ($category == 'Student') {
						$now = date('Y-m-d H:i:s');
						if (isset($completionArray[$row['pupilsightPlannerEntryID']][0]) and isset($completionArray[$row['pupilsightPlannerEntryID']][1])) {
							if ($completionArray[$row['pupilsightPlannerEntryID']][1] == $row['type']) {
								$rowNum = 'current';
							}
						} else {
							if ($row['homeworkDueDateTime'] < $now) {
								$rowNum = 'error';
							}
						}
						$status = '';
						$completion = '';
						if ($row['homeworkSubmission'] == 'Y') {
							$status = '<b>OS: '.$row['homeworkSubmissionRequired'].'</b><br/>';
							try {
								$dataVersion = array('pupilsightPlannerEntryID' => $row['pupilsightPlannerEntryID'], 'pupilsightPersonID' => $_SESSION[$guid]['pupilsightPersonID']);
								$sqlVersion = 'SELECT * FROM pupilsightPlannerEntryHomework WHERE pupilsightPlannerEntryID=:pupilsightPlannerEntryID AND pupilsightPersonID=:pupilsightPersonID ORDER BY count DESC';
								$resultVersion = $connection2->prepare($sqlVersion);
								$resultVersion->execute($dataVersion);
							} catch (PDOException $e) {
								echo "<div class='alert alert-danger'>".$e->getMessage().'</div>';
							}
							if ($resultVersion->rowCount() < 1) {
								//Before deadline
								if (date('Y-m-d H:i:s') < $row['homeworkDueDateTime']) {
									if ($row['homeworkSubmissionRequired'] == 'Compulsory') {
										$status .= 'Pending';
										$completion = "<input disabled type='checkbox'>";
									} else {
										$status .= __('Pending');
                                        $submissionCompletion = (isset($completionArray[$row['pupilsightPlannerEntryID']]))? $completionArray[$row['pupilsightPlannerEntryID']] : '';
										$completion = "<input $submissionCompletion name='complete-$count' type='checkbox'>";
									}
								}
								//After
								else {
									if ($row['homeworkSubmissionRequired'] == 'Compulsory') {
										$status .= "<div style='color: #ff0000; font-weight: bold; border: 2px solid #ff0000; padding: 2px 4px; margin: 2px 0px'>".__('Incomplete').'</div>';
										$completion = "<input disabled type='checkbox'>";
									} else {
										$status .= __('Not submitted online');
                                        $submissionCompletion = (isset($completionArray[$row['pupilsightPlannerEntryID']]))? $completionArray[$row['pupilsightPlannerEntryID']] : '';
										$completion = "<input $submissionCompletion name='complete-$count' type='checkbox'>";
									}
								}
							} else {
								$rowVersion = $resultVersion->fetch();
								if ($rowVersion['status'] == 'On Time' or $rowVersion['status'] == 'Exemption') {
									$status .= $rowVersion['status'];
									$completion = "<input disabled checked type='checkbox'>";
									$rowNum = 'current';
								} else {
									if ($row['homeworkSubmissionRequired'] == 'Compulsory') {
										$status .= "<div style='color: #ff0000; font-weight: bold; border: 2px solid #ff0000; padding: 2px 4px; margin: 2px 0px'>".$rowVersion['status'].'</div>';
										$completion = "<input disabled checked type='checkbox'>";
									} else {
										$status .= $rowVersion['status'];
										$completion = "<input disabled checked type='checkbox'>";
									}
								}
							}
						} else {
							$completion = '<input ';
							if (isset($completionArray[$row['pupilsightPlannerEntryID']][0]) and isset($completionArray[$row['pupilsightPlannerEntryID']][1])) {
								if ($completionArray[$row['pupilsightPlannerEntryID']][1] == $row['type']) {
									$completion .= $completionArray[$row['pupilsightPlannerEntryID']][0];
								}
							}
							$completion .= " name='complete-$count' type='checkbox'>";
							$completion .= "<input type='hidden' name='completeType-$count' value='".$row['type']."'/>";
						}
					}

					//COLOR ROW BY STATUS!
					echo "<tr class=$rowNum>";
                    echo '<td>';
                    echo '<b>'.$row['course'].'.'.$row['class'].'</b></br>';
                    echo "<span style='font-size: 85%; font-style: italic'>".dateConvertBack($guid, $row['date']).'</span>';
                    echo '</td>';
                    echo '<td>';
                    echo '<b>'.$row['name'].'</b><br/>';
                    echo "<span style='font-size: 85%; font-style: italic'>";
                    $unit = getUnit($connection2, $row['pupilsightUnitID'], $row['pupilsightCourseClassID']);
                    if (isset($unit[0])) {
                        echo $unit[0];
                        if ($unit[1] != '') {
                            echo '<br/><i>'.$unit[1].' Unit</i>';
                        }
                    }
                    echo '</span>';
                    echo '</td>';
                    echo '<td>';
                    if ($row['type'] == 'teacherRecorded') {
                        echo 'Teacher Recorded';
                    } else {
                        echo 'Student Recorded';
                    }
                    echo  '<br/>';
                    echo "<span style='font-size: 85%; font-style: italic'>";
                    if ($row['homeworkDetails'] != '') {
                        if (strlen(strip_tags($row['homeworkDetails'])) < 21) {
                            echo strip_tags($row['homeworkDetails']);
                        } else {
                            echo "<span $style title='".htmlPrep(strip_tags($row['homeworkDetails']))."'>".substr(strip_tags($row['homeworkDetails']), 0, 20).'...</span>';
                        }
                    }
                    echo '</span>';
                    echo '</td>';
                    echo '<td>';
                    echo dateConvertBack($guid, substr($row['homeworkDueDateTime'], 0, 10));
                    echo '</td>';
                    if ($category == 'Student') {
                        echo '<td>';
                        echo $status;
                        echo '</td>';
                        echo '<td>';
                        echo $completion;
                        echo "<input type='hidden' name='count[]' value='$count'>";
                        echo "<input type='hidden' name='pupilsightPlannerEntryID-$count' value='".$row['pupilsightPlannerEntryID']."'>";
                        echo '</td>';
                    } else {
                        echo '<td>';
                        if ($row['homeworkSubmission'] == 'Y') {
                            echo '<b>'.$row['homeworkSubmissionRequired'].'</b><br/>';
                            if ($row['role'] == 'Teacher') {
                                try {
                                    $dataVersion = array('pupilsightPlannerEntryID' => $row['pupilsightPlannerEntryID']);
                                    $sqlVersion = "SELECT DISTINCT pupilsightPlannerEntryHomework.pupilsightPersonID FROM pupilsightPlannerEntryHomework JOIN pupilsightPerson ON (pupilsightPlannerEntryHomework.pupilsightPersonID=pupilsightPerson.pupilsightPersonID) WHERE pupilsightPlannerEntryID=:pupilsightPlannerEntryID AND version='Final' AND pupilsightPlannerEntryHomework.status='On Time'";
                                    $resultVersion = $connection2->prepare($sqlVersion);
                                    $resultVersion->execute($dataVersion);
                                } catch (PDOException $e) {
                                    echo "<div class='alert alert-danger'>".$e->getMessage().'</div>';
                                }
                                $onTime = $resultVersion->rowCount();
                                echo "<span style='font-size: 85%; font-style: italic'>On Time: $onTime</span><br/>";

                                try {
                                    $dataVersion = array('pupilsightPlannerEntryID' => $row['pupilsightPlannerEntryID']);
                                    $sqlVersion = "SELECT DISTINCT pupilsightPlannerEntryHomework.pupilsightPersonID FROM pupilsightPlannerEntryHomework JOIN pupilsightPerson ON (pupilsightPlannerEntryHomework.pupilsightPersonID=pupilsightPerson.pupilsightPersonID) WHERE pupilsightPlannerEntryID=:pupilsightPlannerEntryID AND version='Final' AND pupilsightPlannerEntryHomework.status='Late'";
                                    $resultVersion = $connection2->prepare($sqlVersion);
                                    $resultVersion->execute($dataVersion);
                                } catch (PDOException $e) {
                                    echo "<div class='alert alert-danger'>".$e->getMessage().'</div>';
                                }
                                $late = $resultVersion->rowCount();
                                echo "<span style='font-size: 85%; font-style: italic'>Late: $late</span><br/>";

                                try {
                                    $dataVersion = array('pupilsightCourseClassID' => $row['pupilsightCourseClassID']);
                                    $sqlVersion = "SELECT pupilsightCourseClassPerson.pupilsightPersonID FROM pupilsightCourseClassPerson JOIN pupilsightPerson ON (pupilsightCourseClassPerson.pupilsightPersonID=pupilsightPerson.pupilsightPersonID) WHERE pupilsightCourseClassID=:pupilsightCourseClassID AND role='Student' AND status='Full'";
                                    $resultVersion = $connection2->prepare($sqlVersion);
                                    $resultVersion->execute($dataVersion);
                                } catch (PDOException $e) {
                                    echo "<div class='alert alert-danger'>".$e->getMessage().'</div>';
                                }
                                $class = $resultVersion->rowCount();
                                if (date('Y-m-d H:i:s') < $row['homeworkDueDateTime']) {
                                    echo "<span style='font-size: 85%; font-style: italic'>".__('Pending').': '.($class - $late - $onTime).'</span><br/>';
                                } else {
                                    if ($row['homeworkSubmissionRequired'] == 'Compulsory') {
                                        echo "<span style='font-size: 85%; font-style: italic'>".__('Incomplete').': '.($class - $late - $onTime).'</span><br/>';
                                    } else {
                                        echo "<span style='font-size: 85%; font-style: italic'>".__('Not Submitted Online').': '.($class - $late - $onTime).'</span><br/>';
                                    }
                                }
                            }
                        }
                        echo '</td>';
                    }
                    echo '<td>';
                    echo "<a href='".$_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.$_SESSION[$guid]['module']."/planner_view_full.php&search=$pupilsightPersonID&pupilsightPlannerEntryID=".$row['pupilsightPlannerEntryID'].'&viewBy=class&pupilsightCourseClassID='.$row['pupilsightCourseClassID']."'><img title='".__('View')."' src='./themes/".$_SESSION[$guid]['pupilsightThemeName']."/img/plus.png'/></a> ";
                    echo '</td>';
                    echo '</tr>';
                }
            }
            if ($category == 'Student') {
                ?>
				<tr>
					<td class="right" colspan=7>
						<input type="submit" value="<?php echo __('Submit'); ?>">
					</td>
				</tr>
				<?php

            }
            echo '</table>';
            echo '</form>';
        }
    }

    //Print sidebar
    $_SESSION[$guid]['sidebarExtra'] = sidebarExtra($guid, $connection2, $todayStamp, $_SESSION[$guid]['pupilsightPersonID'], $dateStamp, $pupilsightCourseClassID);
}
