<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Services\Format;
use Pupilsight\Forms\Form;

//Module includes
require_once __DIR__ . '/moduleFunctions.php';

if (isActionAccessible($guid, $connection2, '/modules/Planner/planner.php') == false) {
    //Acess denied
    echo "<div class='alert alert-danger'>";
    echo __('Your request failed because you do not have access to this action.');
    echo '</div>';
} else {
    //Get action with highest precendence
    $highestAction = getHighestGroupedAction($guid, $_GET['q'], $connection2);
    if ($highestAction == false) {
        echo "<div class='alert alert-danger'>";
        echo __('The highest grouped action cannot be determined.');
        echo '</div>';
    } else {
        //Set variables
        $today = date('Y-m-d');

        //Proceed!
        //Get viewBy, date and class variables
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
        } elseif ($viewBy == 'class') {
            $class = null;
            if (isset($_GET['class'])) {
                $class = $_GET['class'];
            }
            $pupilsightCourseClassID = null;
            $pupilsightProgramID = null;
            if (isset($_GET['pupilsightCourseClassID'])) {
                $pupilsightCourseClassID = $_GET['pupilsightCourseClassID'];
            }

            if (isset($_GET['pupilsightProgramID'])) {
                $pupilsightProgramID = $_GET['pupilsightProgramID'];
            }
        }
        list($todayYear, $todayMonth, $todayDay) = explode('-', $today);
        $todayStamp = mktime(0, 0, 0, $todayMonth, $todayDay, $todayYear);
        $pupilsightPersonIDArray = [];

        //My children's classes
        if ($highestAction == 'Lesson Planner_viewMyChildrensClasses') {
            $search = null;
            if (isset($_GET['search'])) {
                $search = $_GET['search'];
            }
            $page->breadcrumbs->add(__('My Children\'s Classes'));

            if (isset($_GET['return'])) {
                returnProcess($guid, $_GET['return'], null, null);
            }

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
                    } catch (PDOException $e) {}
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
                    $search = $pupilsightPersonIDArray[0];
                } else {
                    echo '<h2>';
                    echo __('Choose');
                    echo '</h2>';

                    $form = Form::create('action', $_SESSION[$guid]['absoluteURL'].'/index.php', 'get');

                    $form->setClass('noIntBorder fullWidth');

                    $form->addHiddenValue('address', $_SESSION[$guid]['address']);
                    $form->addHiddenValue('q', '/modules/'.$_SESSION[$guid]['module'].'/planner.php');
                    if (isset($pupilsightCourseClassID) && $pupilsightCourseClassID != '') {
                        $form->addHiddenValue('pupilsightCourseClassID', $pupilsightCourseClassID);
                        $form->addHiddenValue('viewBy', 'class');
                    }
                    else {
                        $form->addHiddenValue('viewBy', 'date');
                    }

                    $row = $form->addRow();
                    $row->addLabel('search', __('Student'));
                    $row->addSelect('search')->fromArray($options)->selected($search)->placeholder();

                    $row = $form->addRow();
                        $row->addFooter();
                        $row->addSearchSubmit($pupilsight->session);

                    echo $form->getOutput();
                }

                $pupilsightPersonID = $search;

                if ($search != '' and $count > 0) {
                    //Confirm access to this student
                    try {
                        $dataChild = array('pupilsightPersonID' => $_SESSION[$guid]['pupilsightPersonID'], 'pupilsightPersonID2' => $pupilsightPersonID);
                        $sqlChild = "SELECT * FROM pupilsightFamilyChild JOIN pupilsightFamily ON (pupilsightFamilyChild.pupilsightFamilyID=pupilsightFamily.pupilsightFamilyID) JOIN pupilsightFamilyAdult ON (pupilsightFamilyAdult.pupilsightFamilyID=pupilsightFamily.pupilsightFamilyID) JOIN pupilsightPerson ON (pupilsightFamilyChild.pupilsightPersonID=pupilsightPerson.pupilsightPersonID) WHERE pupilsightPerson.status='Full' AND (dateStart IS NULL OR dateStart<='".date('Y-m-d')."') AND (dateEnd IS NULL  OR dateEnd>='".date('Y-m-d')."') AND pupilsightFamilyChild.pupilsightPersonID=:pupilsightPersonID2 AND pupilsightFamilyAdult.pupilsightPersonID=:pupilsightPersonID AND childDataAccess='Y'";
                        $resultChild = $connection2->prepare($sqlChild);
                        $resultChild->execute($dataChild);
                    } catch (PDOException $e) {
                        echo "<div class='alert alert-danger'>".$e->getMessage().'</div>';
                    }

                    if ($resultChild->rowCount() < 1) {
                        echo "<div class='alert alert-danger'>";
                        echo __('There are no records to display.');
                        echo '</div>';
                    } else {
                        $rowChild = $resultChild->fetch();

                        if ($count > 1) {
                            echo '<h2>';
                            echo __('Lessons');
                            echo '</h2>';
                        }

                        //Print planner
                        if ($viewBy == 'date') {
                            if (isSchoolOpen($guid, date('Y-m-d', $dateStamp), $connection2) == false) {
                                echo "<div class='alert alert-warning'>";
                                echo __('School is closed on the specified day.');
                                echo '</div>';
                            } else {
                                try {
                                    $data = array('date1' => $date, 'pupilsightPersonID1' => $pupilsightPersonID, 'date2' => $date, 'pupilsightPersonID2' => $pupilsightPersonID);
                                    $sql = "
                                    (SELECT
                                        pupilsightPlannerEntry.pupilsightPlannerEntryID, pupilsightUnitID, pupilsightPlannerEntry.pupilsightCourseClassID, pupilsightCourse.nameShort AS course, pupilsightCourseClass.nameShort AS class, pupilsightPlannerEntry.name, timeStart, timeEnd, viewableStudents, viewableParents, homework, role, homeworkSubmission, homeworkCrowdAssess, date, pupilsightPlannerEntryStudentHomework.homeworkDueDateTime AS myHomeworkDueDateTime
                                    FROM pupilsightPlannerEntry
                                        JOIN pupilsightCourseClass ON (pupilsightPlannerEntry.pupilsightCourseClassID=pupilsightCourseClass.pupilsightCourseClassID)
                                        JOIN pupilsightCourseClassPerson ON (pupilsightCourseClass.pupilsightCourseClassID=pupilsightCourseClassPerson.pupilsightCourseClassID)
                                        JOIN pupilsightCourse ON (pupilsightCourse.pupilsightCourseID=pupilsightCourseClass.pupilsightCourseID)
                                        LEFT JOIN pupilsightPlannerEntryStudentHomework ON (pupilsightPlannerEntryStudentHomework.pupilsightPlannerEntryID=pupilsightPlannerEntry.pupilsightPlannerEntryID AND pupilsightPlannerEntryStudentHomework.pupilsightPersonID=pupilsightCourseClassPerson.pupilsightPersonID)
                                    WHERE date=:date1
                                        AND pupilsightCourseClassPerson.pupilsightPersonID=:pupilsightPersonID1
                                        AND NOT role='Student - Left'
                                        AND NOT role='Teacher - Left')
                                    UNION
                                    (SELECT
                                        pupilsightPlannerEntry.pupilsightPlannerEntryID, pupilsightUnitID, pupilsightPlannerEntry.pupilsightCourseClassID, pupilsightCourse.nameShort AS course, pupilsightCourseClass.nameShort AS class, pupilsightPlannerEntry.name, timeStart, timeEnd, viewableStudents, viewableParents, homework, role, homeworkSubmission, homeworkCrowdAssess, date, NULL AS myHomeworkDueDateTime
                                    FROM pupilsightPlannerEntry
                                        JOIN pupilsightCourseClass ON (pupilsightPlannerEntry.pupilsightCourseClassID=pupilsightCourseClass.pupilsightCourseClassID)
                                        JOIN pupilsightPlannerEntryGuest ON (pupilsightPlannerEntryGuest.pupilsightPlannerEntryID=pupilsightPlannerEntry.pupilsightPlannerEntryID)
                                        JOIN pupilsightCourse ON (pupilsightCourse.pupilsightCourseID=pupilsightCourseClass.pupilsightCourseID)
                                    WHERE date=:date2
                                        AND pupilsightPlannerEntryGuest.pupilsightPersonID=:pupilsightPersonID2)
                                    ORDER BY date, timeStart
                                    ";
                                    $result = $connection2->prepare($sql);
                                    $result->execute($data);
                                } catch (PDOException $e) {
                                    echo "<div class='alert alert-danger'>".$e->getMessage().'</div>';
                                }

                                //Only show add if user has edit rights
                                if ($highestAction == 'Lesson Planner_viewAllEditMyClasses' or $highestAction == 'Lesson Planner_viewEditAllClasses') {
                                    echo "<div class='linkTop'>";
                                    echo "<a class='btn btn-primary'  href='".$_SESSION[$guid]['absoluteURL']."/index.php?q=/modules/Planner/planner_add.php&date=$date'>".__('Add')." </a>";
                                    echo '</div>';
                                }

                                if ($result->rowCount() < 1) {
                                    echo "<div class='alert alert-danger'>";
                                    echo __('There are no records to display.');
                                    echo '</div>';
                                } else {
                                    echo "<table cellspacing='0' style='width: 100%'>";
                                    echo "<tr class='head'>";
                                    echo '<th>';
                                    echo __('Class');
                                    echo '</th>';
                                    echo '<th>';
                                    echo __('Lesson').'</br>';
                                    echo "<span style='font-size: 85%; font-style: italic'>".__('Unit').'</span>';
                                    echo '</th>';
                                    echo '<th>';
                                    echo __('Time');
                                    echo '</th>';
                                    echo '<th>';
                                    echo __('Homework');
                                    echo '</th>';
                                    echo '<th>';
                                    echo __('Access');
                                    echo '</th>';
                                    echo "<th style='min-width: 140px'>";
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
											if ((date('H:i:s') > $row['timeStart']) and (date('H:i:s') < $row['timeEnd']) and ($date) == date('Y-m-d')) {
												$rowNum = 'current';
											}

											//COLOR ROW BY STATUS!
											echo "<tr class=$rowNum>";
                                            echo '<td>';
                                            echo $row['course'].'.'.$row['class'];
                                            echo '</td>';
                                            echo '<td>';
                                            echo '<b>'.$row['name'].'</b><br/>';
                                            echo "<span style='font-size: 85%; font-style: italic'>";
                                            $unit = getUnit($connection2, $row['pupilsightUnitID'], $row['pupilsightCourseClassID']);
                                            if (isset($unit[0])) {
                                                echo $unit[0];
                                                if ($unit[1] != '') {
                                                    echo '<br/><i>'.$unit[1].' '.__('Unit').'</i>';
                                                }
                                            }
                                            echo '</span>';
                                            echo '</td>';
                                            echo '<td>';
                                            echo substr($row['timeStart'], 0, 5).'-'.substr($row['timeEnd'], 0, 5);
                                            echo '</td>';
                                            echo '<td>';
                                            if ($row['homework'] == 'N' and $row['myHomeworkDueDateTime'] == '') {
                                                echo __('No');
                                            } else {
                                                if ($row['homework'] == 'Y') {
                                                    echo __('Yes').': '.__('Teacher Recorded').'<br/>';
                                                    if ($row['homeworkSubmission'] == 'Y') {
                                                        echo "<span style='font-size: 85%; font-style: italic'>+".__('Submission').'</span><br/>';
                                                        if ($row['homeworkCrowdAssess'] == 'Y') {
                                                            echo "<span style='font-size: 85%; font-style: italic'>+".__('Crowd Assessment').'</span><br/>';
                                                        }
                                                    }
                                                }
                                                if ($row['myHomeworkDueDateTime'] != '') {
                                                    echo __('Yes').': '.__('Student Recorded').'</br>';
                                                }
                                            }
                                            echo '</td>';
                                            echo '<td>';
                                            if ($row['viewableStudents'] == 'Y') {
                                                echo __('Students');
                                            }
                                            if ($row['viewableStudents'] == 'Y' and $row['viewableParents'] == 'Y') {
                                                echo ', ';
                                            }
                                            if ($row['viewableParents'] == 'Y') {
                                                echo __('Parents');
                                            }
                                            echo '</td>';
                                            echo '<td>';
                                            echo "<a href='".$_SESSION[$guid]['absoluteURL']."/index.php?q=/modules/Planner/planner_view_full.php&search=$pupilsightPersonID&pupilsightPlannerEntryID=".$row['pupilsightPlannerEntryID']."&viewBy=$viewBy&pupilsightCourseClassID=$pupilsightCourseClassID&date=$date&width=1000&height=550'><img title='".__('View')."' src='./themes/".$_SESSION[$guid]['pupilsightThemeName']."/img/plus.png'/></a> ";
                                            echo '</td>';
                                            echo '</tr>';
                                        }
                                    }
                                    echo '</table>';
                                }
                            }
                        } elseif ($viewBy == 'class') {
                            if ($pupilsightCourseClassID == '') {
                                echo "<div class='alert alert-danger'>";
                                echo __('You have not specified one or more required parameters.');
                                echo '</div>';
                            } else {
                                try {
                                    $data = array('pupilsightCourseClassID' => $pupilsightCourseClassID, 'pupilsightPersonID' => $pupilsightPersonID);
                                    $sql = 'SELECT pupilsightCourseClass.pupilsightCourseClassID, pupilsightCourse.nameShort AS course, pupilsightCourseClass.nameShort AS class FROM pupilsightCourseClassPerson JOIN pupilsightCourseClass ON (pupilsightCourseClassPerson.pupilsightCourseClassID=pupilsightCourseClass.pupilsightCourseClassID) JOIN pupilsightCourse ON (pupilsightCourseClass.pupilsightCourseID=pupilsightCourse.pupilsightCourseID) WHERE pupilsightCourse.pupilsightSchoolYearID='.$_SESSION[$guid]['pupilsightSchoolYearID'].' AND pupilsightCourseClass.pupilsightCourseClassID=:pupilsightCourseClassID AND pupilsightPersonID=:pupilsightPersonID';
                                    $result = $connection2->prepare($sql);
                                    $result->execute($data);
                                } catch (PDOException $e) {
                                    echo "<div class='alert alert-danger'>".$e->getMessage().'</div>';
                                }

                                if ($result->rowCount() != 1) {
                                    echo "<div class='alert alert-danger'>";
                                    echo __('The selected record does not exist, or you do not have access to it.');
                                    echo '</div>';
                                } else {
                                    $row = $result->fetch();

                                    try {
                                        $data = array('pupilsightCourseClassID1' => $pupilsightCourseClassID, 'pupilsightPersonID1' => $pupilsightPersonID, 'pupilsightCourseClassID2' => $pupilsightCourseClassID, 'pupilsightPersonID2' => $pupilsightPersonID);
                                        $sql = "(SELECT pupilsightPlannerEntry.pupilsightPlannerEntryID, pupilsightUnitID, pupilsightPlannerEntry.pupilsightCourseClassID, pupilsightCourse.nameShort AS course, pupilsightCourseClass.nameShort AS class, pupilsightPlannerEntry.name, timeStart, timeEnd, viewableStudents, viewableParents, homework, role, homeworkSubmission, homeworkCrowdAssess, date, pupilsightPlannerEntryStudentHomework.homeworkDueDateTime AS myHomeworkDueDateTime FROM pupilsightPlannerEntry JOIN pupilsightCourseClass ON (pupilsightPlannerEntry.pupilsightCourseClassID=pupilsightCourseClass.pupilsightCourseClassID) JOIN pupilsightCourseClassPerson ON (pupilsightCourseClass.pupilsightCourseClassID=pupilsightCourseClassPerson.pupilsightCourseClassID) JOIN pupilsightCourse ON (pupilsightCourse.pupilsightCourseID=pupilsightCourseClass.pupilsightCourseID) LEFT JOIN pupilsightPlannerEntryStudentHomework ON (pupilsightPlannerEntryStudentHomework.pupilsightPlannerEntryID=pupilsightPlannerEntry.pupilsightPlannerEntryID AND pupilsightPlannerEntryStudentHomework.pupilsightPersonID=pupilsightCourseClassPerson.pupilsightPersonID) WHERE pupilsightPlannerEntry.pupilsightCourseClassID=:pupilsightCourseClassID1 AND pupilsightCourseClassPerson.pupilsightPersonID=:pupilsightPersonID1 AND NOT role='Student - Left' AND NOT role='Teacher - Left') UNION (SELECT pupilsightPlannerEntry.pupilsightPlannerEntryID, pupilsightUnitID, pupilsightPlannerEntry.pupilsightCourseClassID, pupilsightCourse.nameShort AS course, pupilsightCourseClass.nameShort AS class, pupilsightPlannerEntry.name, timeStart, timeEnd, viewableStudents, viewableParents, homework, role, homeworkSubmission, homeworkCrowdAssess, date, NULL AS myHomeworkDueDateTime FROM pupilsightPlannerEntry JOIN pupilsightCourseClass ON (pupilsightPlannerEntry.pupilsightCourseClassID=pupilsightCourseClass.pupilsightCourseClassID) JOIN pupilsightPlannerEntryGuest ON (pupilsightPlannerEntryGuest.pupilsightPlannerEntryID=pupilsightPlannerEntry.pupilsightPlannerEntryID) JOIN pupilsightCourse ON (pupilsightCourse.pupilsightCourseID=pupilsightCourseClass.pupilsightCourseID) WHERE pupilsightPlannerEntry.pupilsightCourseClassID=:pupilsightCourseClassID2 AND pupilsightPlannerEntryGuest.pupilsightPersonID=:pupilsightPersonID2) ORDER BY date DESC, timeStart DESC";
                                        $result = $connection2->prepare($sql);
                                        $result->execute($data);
                                    } catch (PDOException $e) {
                                        echo "<div class='alert alert-danger'>".$e->getMessage().'</div>';
                                    }

                                    //Only show add if user has edit rights
                                    if ($highestAction == 'Lesson Planner_viewAllEditMyClasses' or $highestAction == 'Lesson Planner_viewEditAllClasses') {
                                        echo "<div class='linkTop'>";
                                        echo "<a class='btn btn-primary' href='".$_SESSION[$guid]['absoluteURL']."/index.php?q=/modules/Planner/planner_add.php&viewBy=$viewBy&pupilsightCourseClassID=$pupilsightCourseClassID'>".__('Add')."</a>";
                                        echo '</div>';
                                    }

                                    if ($result->rowCount() < 1) {
                                        echo "<div class='alert alert-danger'>";
                                        echo __('There are no records to display.');
                                        echo '</div>';
                                    } else {
                                        echo "<table cellspacing='0' style='width: 100%'>";
                                        echo "<tr class='head'>";
                                        echo '<th>';
                                        echo __('Date');
                                        echo '</th>';
                                        echo '<th>';
                                        echo __('Lesson').'</br>';
                                        echo "<span style='font-size: 85%; font-style: italic'>".__('Unit').'</span>';
                                        echo '</th>';
                                        echo '<th>';
                                        echo __('Time');
                                        echo '</th>';
                                        echo '<th>';
                                        echo __('Homework');
                                        echo '</th>';
                                        echo '<th>';
                                        echo __('Access');
                                        echo '</th>';
                                        echo "<th style='min-width: 140px'>";
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
                                                if (!(is_null($row['date']))) {
                                                    echo '<b>'.dateConvertBack($guid, $row['date']).'</b><br/>';
                                                    echo Format::dateReadable($row['date'], '%A');
                                                }
                                                echo '</td>';
                                                echo '<td>';
                                                echo '<b>'.$row['name'].'</b><br/>';
                                                if ($row['pupilsightUnitID'] != '') {
                                                    $unit = getUnit($connection2, $row['pupilsightUnitID'], $row['pupilsightCourseClassID']);
                                                    if (!empty($unit[0])) {
                                                        echo "<span style='font-size: 85%; font-style: italic'>";
                                                            echo $unit[0];
                                                            if ($unit[1] != '') {
                                                                echo '<br/><i>'.$unit[1].' '.__('Unit').'</i>';
                                                            }
                                                        echo '</span>';
                                                    }

                                                }
                                                echo '</td>';
                                                echo '<td>';
                                                if ($row['timeStart'] != '' and $row['timeEnd'] != '') {
                                                    echo substr($row['timeStart'], 0, 5).'-'.substr($row['timeEnd'], 0, 5);
                                                }
                                                echo '</td>';
                                                echo '<td>';
                                                if ($row['homework'] == 'N' and $row['myHomeworkDueDateTime'] == '') {
                                                    echo __('No');
                                                } else {
                                                    if ($row['homework'] == 'Y') {
                                                        echo __('Yes').': '.__('Teacher Recorded').'<br/>';
                                                        if ($row['homeworkSubmission'] == 'Y') {
                                                            echo "<span style='font-size: 85%; font-style: italic'>+".__('Submission').'</span><br/>';
                                                            if ($row['homeworkCrowdAssess'] == 'Y') {
                                                                echo "<span style='font-size: 85%; font-style: italic'>+".__('Crowd Assessment').'</span><br/>';
                                                            }
                                                        }
                                                    }
                                                    if ($row['myHomeworkDueDateTime'] != '') {
                                                        echo __('Yes').': '.__('Student Recorded').'</br>';
                                                    }
                                                }
                                                echo '</td>';
                                                echo '<td>';
                                                if ($row['viewableStudents'] == 'Y') {
                                                    echo __('Students');
                                                }
                                                if ($row['viewableStudents'] == 'Y' and $row['viewableParents'] == 'Y') {
                                                    echo ', ';
                                                }
                                                if ($row['viewableParents'] == 'Y') {
                                                    echo __('Parents');
                                                }
                                                echo '</td>';
                                                echo '<td>';
                                                echo "<a href='".$_SESSION[$guid]['absoluteURL']."/index.php?q=/modules/Planner/planner_view_full.php&search=$pupilsightPersonID&pupilsightPlannerEntryID=".$row['pupilsightPlannerEntryID']."&viewBy=class&pupilsightCourseClassID=$pupilsightCourseClassID&width=1000&height=550'><img title='".__('View')."' src='./themes/".$_SESSION[$guid]['pupilsightThemeName']."/img/plus.png'/></a> ";
                                                echo '</td>';
                                                echo '</tr>';
                                            }
                                        }
                                        echo '</table>';
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
        //My Classes
        elseif ($highestAction == 'Lesson Planner_viewMyClasses' or $highestAction == 'Lesson Planner_viewAllEditMyClasses' or $highestAction == 'Lesson Planner_viewEditAllClasses') {
            $pupilsightPersonID = $_SESSION[$guid]['pupilsightPersonID'];
            if ($viewBy == 'date') {
                $page->breadcrumbs->add(__('Planner for {classDesc}', [
                    'classDesc' => dateConvertBack($guid, $date),
                ]));

                //Get Smart Workflow help message
                $category = getRoleCategory($_SESSION[$guid]['pupilsightRoleIDCurrent'], $connection2);
                if ($category == 'Staff') {
                    $smartWorkflowHelp = getSmartWorkflowHelp($connection2, $guid, 3);
                    if ($smartWorkflowHelp != false) {
                        echo $smartWorkflowHelp;
                    }
                }

                if (isset($_GET['return'])) {
                    returnProcess($guid, $_GET['return'], null, null);
                }

                if (isSchoolOpen($guid, date('Y-m-d', $dateStamp), $connection2) == false) {
                    echo "<div class='alert alert-warning'>";
                    echo __('School is closed on the specified day.');
                    echo '</div>';
                } else {
                    //Set pagination variable
                    $page = 1;
                    if (isset($_GET['page'])) {
                        $page = $_GET['page'];
                    }
                    if ((!is_numeric($page)) or $page < 1) {
                        $page = 1;
                    }

                    try {
                        if ($highestAction == 'Lesson Planner_viewEditAllClasses') {
                            $data = array('date' => $date);
                            // $sql = "SELECT pupilsightPlannerEntry.pupilsightPlannerEntryID, pupilsightUnitID, pupilsightCourse.nameShort AS course, pupilsightCourseClass.nameShort AS class, pupilsightPlannerEntry.name, timeStart, timeEnd, viewableStudents, viewableParents, homework, 'Teacher' AS role, homeworkSubmission, homeworkCrowdAssess, date, pupilsightPlannerEntry.pupilsightCourseClassID, NULL AS myHomeworkDueDateTime FROM pupilsightPlannerEntry JOIN pupilsightCourseClass ON (pupilsightPlannerEntry.pupilsightCourseClassID=pupilsightCourseClass.pupilsightCourseClassID) JOIN pupilsightCourse ON (pupilsightCourse.pupilsightCourseID=pupilsightCourseClass.pupilsightCourseID) WHERE date=:date ORDER BY date, timeStart";
                            $sql = "SELECT pupilsightPlannerEntry.pupilsightPlannerEntryID, pupilsightUnitID, pupilsightProgram.name AS progName, pupilsightYearGroup.name AS className , pupilsightRollGroup.name AS sectionName, pupilsightDepartment.name AS subjectName, pupilsightPlannerEntry.name, timeStart, timeEnd, viewableStudents, viewableParents, homework, 'Teacher' AS role, homeworkSubmission, homeworkCrowdAssess, date, pupilsightPlannerEntry.pupilsightCourseClassID, NULL AS myHomeworkDueDateTime FROM pupilsightPlannerEntry JOIN pupilsightProgram ON (pupilsightPlannerEntry.pupilsightProgramID=pupilsightProgram.pupilsightProgramID) JOIN pupilsightYearGroup ON (pupilsightPlannerEntry.pupilsightYearGroupID=pupilsightYearGroup.pupilsightYearGroupID) JOIN pupilsightRollGroup ON (pupilsightPlannerEntry.pupilsightRollGroupID=pupilsightRollGroup.pupilsightRollGroupID) JOIN pupilsightDepartment ON (pupilsightPlannerEntry.pupilsightDepartmentID=pupilsightDepartment.pupilsightDepartmentID) WHERE date=:date ORDER BY date, timeStart";
                        } elseif ($highestAction == 'Lesson Planner_viewMyClasses' or $highestAction == 'Lesson Planner_viewAllEditMyClasses') {
                            $data = array('date1' => $date, 'pupilsightPersonID1' => $pupilsightPersonID, 'date2' => $date, 'pupilsightPersonID2' => $pupilsightPersonID);
                            $sql = "(SELECT pupilsightPlannerEntry.pupilsightPlannerEntryID, pupilsightUnitID, pupilsightCourse.nameShort AS course, pupilsightCourseClass.nameShort AS class, pupilsightPlannerEntry.name, timeStart, timeEnd, viewableStudents, viewableParents, homework, role, homeworkSubmission, homeworkCrowdAssess, date, pupilsightPlannerEntry.pupilsightCourseClassID, pupilsightPlannerEntryStudentHomework.homeworkDueDateTime AS myHomeworkDueDateTime FROM pupilsightPlannerEntry JOIN pupilsightCourseClass ON (pupilsightPlannerEntry.pupilsightCourseClassID=pupilsightCourseClass.pupilsightCourseClassID) JOIN pupilsightCourseClassPerson ON (pupilsightCourseClass.pupilsightCourseClassID=pupilsightCourseClassPerson.pupilsightCourseClassID) JOIN pupilsightCourse ON (pupilsightCourse.pupilsightCourseID=pupilsightCourseClass.pupilsightCourseID) LEFT JOIN pupilsightPlannerEntryStudentHomework ON (pupilsightPlannerEntryStudentHomework.pupilsightPlannerEntryID=pupilsightPlannerEntry.pupilsightPlannerEntryID AND pupilsightPlannerEntryStudentHomework.pupilsightPersonID=pupilsightCourseClassPerson.pupilsightPersonID) WHERE date=:date1 AND pupilsightCourseClassPerson.pupilsightPersonID=:pupilsightPersonID1 AND NOT role='Student - Left' AND NOT role='Teacher - Left') UNION (SELECT pupilsightPlannerEntry.pupilsightPlannerEntryID, pupilsightUnitID, pupilsightCourse.nameShort AS course, pupilsightCourseClass.nameShort AS class, pupilsightPlannerEntry.name, timeStart, timeEnd, viewableStudents, viewableParents, homework, role, homeworkSubmission, homeworkCrowdAssess, date, pupilsightPlannerEntry.pupilsightCourseClassID, NULL AS myHomeworkDueDateTime FROM pupilsightPlannerEntry JOIN pupilsightCourseClass ON (pupilsightPlannerEntry.pupilsightCourseClassID=pupilsightCourseClass.pupilsightCourseClassID) JOIN pupilsightPlannerEntryGuest ON (pupilsightPlannerEntryGuest.pupilsightPlannerEntryID=pupilsightPlannerEntry.pupilsightPlannerEntryID) JOIN pupilsightCourse ON (pupilsightCourse.pupilsightCourseID=pupilsightCourseClass.pupilsightCourseID) WHERE date=:date2 AND pupilsightPlannerEntryGuest.pupilsightPersonID=:pupilsightPersonID2) ORDER BY date, timeStart";
                        }
                        $result = $connection2->prepare($sql);
                        $result->execute($data);
                    } catch (PDOException $e) {
                        echo "<div class='alert alert-danger'>".$e->getMessage().'</div>';
                    }

                    //Only show add if user has edit rights
                    if ($highestAction == 'Lesson Planner_viewEditMyClasses' or $highestAction == 'Lesson Planner_viewEditAllClasses' or $highestAction == 'Lesson Planner_viewAllEditMyClasses') {
                        echo "<div class='linkTop'>";
                        echo "<a class='btn btn-primary' href='".$_SESSION[$guid]['absoluteURL']."/index.php?q=/modules/Planner/planner_add.php&date=$date'>".__('Add')."</a>";
                        echo '</div>';
                    }

                    if ($result->rowCount() < 1) {
                        echo "<div class='alert alert-danger'>";
                        echo __('There are no records to display.');
                        echo '</div>';
                    } else {
                        echo "<table cellspacing='0' style='width: 100%'>";
                        echo "<tr class='head'>";
                        echo '<th>';
                        echo __('Program');
                        echo '</th>';
                        echo '<th>';
                        echo __('Class');
                        echo '</th>';
                        echo '<th>';
                        echo __('Section');
                        echo '</th>';
                        echo '<th>';
                        echo __('Subject');
                        echo '</th>';
                        echo '<th>';
                        echo __('Lesson').'</br>';
                        echo "<span style='font-size: 85%; font-style: italic'>".__('Unit').'</span>';
                        echo '</th>';
                        echo '<th>';
                        echo __('Time');
                        echo '</th>';
                        echo '<th>';
                        echo __('Homework');
                        echo '</th>';
                        echo '<th>';
                        echo __('Access');
                        echo '</th>';
                        echo "<th style='min-width: 140px'>";
                        echo __('Actions');
                        echo '</th>';
                        echo '</tr>';

                        $count = 0;
                        $rowNum = 'odd';
                        while ($row = $result->fetch()) {
                            if ((!($row['role'] == 'Student' and $row['viewableStudents'] == 'N')) and (!($row['role'] == 'Guest Student' and $row['viewableStudents'] == 'N'))) {
                                if ($count % 2 == 0) {
                                    $rowNum = 'even';
                                } else {
                                    $rowNum = 'odd';
                                }
                                ++$count;

                                    //Highlight class in progress
                                    if ((date('H:i:s') > $row['timeStart']) and (date('H:i:s') < $row['timeEnd']) and ($date) == date('Y-m-d')) {
                                        $rowNum = 'current';
                                    }
                                    //Dull out past classes
                                    if ((($row['date']) == date('Y-m-d') and (date('H:i:s') > $row['timeEnd'])) or ($row['date']) < date('Y-m-d')) {
                                        $rowNum = 'past';
                                    }

                                    //COLOR ROW BY STATUS!
                                    echo "<tr class=$rowNum>";
                                echo '<td>';
                                echo $row['progName'];
                                echo '</td>';
                                echo '<td>';
                                echo $row['className'];
                                echo '</td>';
                                echo '<td>';
                                echo $row['sectionName'];
                                echo '</td>';
                                echo '<td>';
                                echo $row['subjectName'];
                                echo '</td>';
                                echo '<td>';
                                echo '<b>'.$row['name'].'</b><br/>';
                                echo "<span style='font-size: 85%; font-style: italic'>";
                                $unit = getUnit($connection2, $row['pupilsightUnitID'], $row['pupilsightCourseClassID']);
                                if (isset($unit[0])) {
                                    echo $unit[0];
                                    if ($unit[1] != '') {
                                        echo '<br/><i>'.$unit[1].' '.__('Unit').'</i>';
                                    }
                                }
                                echo '</span>';
                                echo '</td>';
                                echo '<td>';
                                echo substr($row['timeStart'], 0, 5).'-'.substr($row['timeEnd'], 0, 5);
                                echo '</td>';
                                echo '<td>';
                                if ($row['homework'] == 'N' and $row['myHomeworkDueDateTime'] == '') {
                                    echo __('No');
                                } else {
                                    if ($row['homework'] == 'Y') {
                                        echo __('Yes').': '.__('Teacher Recorded').'<br/>';
                                        if ($row['homeworkSubmission'] == 'Y') {
                                            echo "<span style='font-size: 85%; font-style: italic'>+".__('Submission').'</span><br/>';
                                            if ($row['homeworkCrowdAssess'] == 'Y') {
                                                echo "<span style='font-size: 85%; font-style: italic'>+".__('Crowd Assessment').'</span><br/>';
                                            }
                                        }
                                    }
                                    if ($row['myHomeworkDueDateTime'] != '') {
                                        echo __('Yes').': '.__('Student Recorded').'</br>';
                                    }
                                }
                                echo '<td>';
                                if ($row['viewableStudents'] == 'Y') {
                                    echo __('Students');
                                }
                                if ($row['viewableStudents'] == 'Y' and $row['viewableParents'] == 'Y') {
                                    echo ', ';
                                }
                                if ($row['viewableParents'] == 'Y') {
                                    echo __('Parents');
                                }
                                echo '</td>';
                                echo '<td>';
                                echo "<a href='".$_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/Planner/planner_view_full.php&pupilsightPlannerEntryID='.$row['pupilsightPlannerEntryID']."&viewBy=$viewBy&pupilsightCourseClassID=$pupilsightCourseClassID&date=$date&width=1000&height=550'><i title='".__('View Details')."' class='mdi mdi-eye-outline mdi-24px  px-1'></i></a> ";
                                if ($highestAction == 'Lesson Planner_viewAllEditMyClasses' or $highestAction == 'Lesson Planner_viewEditAllClasses') {
                                    echo "<a href='".$_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/Planner/planner_edit.php&pupilsightPlannerEntryID='.$row['pupilsightPlannerEntryID']."&viewBy=$viewBy&pupilsightCourseClassID=$pupilsightCourseClassID&date=$date'><i title='".__('Edit')."' class='mdi mdi-pencil-box-outline mdi-24px'></i></a> ";
                                    echo "<a class='thickbox' href='".$_SESSION[$guid]['absoluteURL'].'/fullscreen.php?q=/modules/Planner/planner_delete.php&pupilsightPlannerEntryID='.$row['pupilsightPlannerEntryID']."&viewBy=$viewBy&pupilsightCourseClassID=$pupilsightCourseClassID&date=$date&subView=$subView&width=650&height=135'><i title='".__('Delete')."' class='mdi mdi-trash-can-outline mdi-24px'></i></a>";
                                    echo "<a href='".$_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/Planner/planner_duplicate.php&pupilsightPlannerEntryID='.$row['pupilsightPlannerEntryID']."&viewBy=$viewBy&pupilsightCourseClassID=$pupilsightCourseClassID&date=$date'><i title='".__('Duplicate')."' class='mdi mdi-content-copy mdi-24px'></i></a>";
                                }
                                echo '</td>';
                                echo '</tr>';
                            }
                        }
                        echo '</table>';
                    }
                }
            } elseif ($viewBy == 'class') {
                if ($pupilsightCourseClassID == '') {
                    echo "<div class='alert alert-danger'>";
                    echo __('You have not specified one or more required parameters.');
                    echo '</div>';
                } else {
                    if ($highestAction == 'Lesson Planner_viewEditAllClasses' or $highestAction == 'Lesson Planner_viewAllEditMyClasses') {
                        try {
                            /* closed By Bikash */
                            // $data = array('pupilsightSchoolYearID' => $_SESSION[$guid]['pupilsightSchoolYearID'], 'pupilsightCourseClassID' => $pupilsightCourseClassID);
                            // $sql = 'SELECT pupilsightCourseClass.pupilsightCourseClassID, pupilsightCourse.nameShort AS course, pupilsightCourseClass.nameShort AS class FROM pupilsightCourseClass JOIN pupilsightCourse ON (pupilsightCourseClass.pupilsightCourseID=pupilsightCourse.pupilsightCourseID) WHERE pupilsightCourse.pupilsightSchoolYearID=:pupilsightSchoolYearID AND pupilsightCourseClass.pupilsightCourseClassID=:pupilsightCourseClassID';

                            $data = array('pupilsightProgramID' => $pupilsightProgramID, 'pupilsightCourseClassID' => $pupilsightCourseClassID);
                            //print_r($data);
                            $sql = "SELECT pupilsightPlannerEntry.pupilsightPlannerEntryID, pupilsightUnitID, pupilsightProgram.name AS progName, pupilsightYearGroup.name AS className , pupilsightRollGroup.name AS sectionName, pupilsightDepartment.name AS subjectName, pupilsightPlannerEntry.name, timeStart, timeEnd, viewableStudents, viewableParents, homework, 'Teacher' AS role, homeworkSubmission, homeworkCrowdAssess, date, pupilsightPlannerEntry.pupilsightCourseClassID, NULL AS myHomeworkDueDateTime FROM pupilsightPlannerEntry JOIN pupilsightProgram ON (pupilsightPlannerEntry.pupilsightProgramID=pupilsightProgram.pupilsightProgramID) JOIN pupilsightYearGroup ON (pupilsightPlannerEntry.pupilsightYearGroupID=pupilsightYearGroup.pupilsightYearGroupID) JOIN pupilsightRollGroup ON (pupilsightPlannerEntry.pupilsightRollGroupID=pupilsightRollGroup.pupilsightRollGroupID) JOIN pupilsightDepartment ON (pupilsightPlannerEntry.pupilsightDepartmentID=pupilsightDepartment.pupilsightDepartmentID) WHERE pupilsightPlannerEntry.pupilsightProgramID=:pupilsightProgramID AND pupilsightPlannerEntry.pupilsightCourseClassID=:pupilsightCourseClassID ORDER BY date, timeStart";

                            $result = $connection2->prepare($sql);
                            $result->execute($data);
                            
                        } catch (PDOException $e) {
                            echo "<div class='alert alert-danger'>".$e->getMessage().'</div>';
                        }
                        $teacher = false;

                        try {
                            $dataTeacher = array('pupilsightSchoolYearID' => $_SESSION[$guid]['pupilsightSchoolYearID'], 'pupilsightCourseClassID' => $pupilsightCourseClassID, 'pupilsightPersonID' => $_SESSION[$guid]['pupilsightPersonID']);
                            $sqlTeacher = 'SELECT pupilsightCourseClass.pupilsightCourseClassID, pupilsightCourse.nameShort AS course, pupilsightCourseClass.nameShort AS class FROM pupilsightCourseClassPerson JOIN pupilsightCourseClass ON (pupilsightCourseClassPerson.pupilsightCourseClassID=pupilsightCourseClass.pupilsightCourseClassID) JOIN pupilsightCourse ON (pupilsightCourseClass.pupilsightCourseID=pupilsightCourse.pupilsightCourseID) WHERE pupilsightCourse.pupilsightSchoolYearID=:pupilsightSchoolYearID AND pupilsightCourseClass.pupilsightCourseClassID=:pupilsightCourseClassID AND pupilsightPersonID=:pupilsightPersonID';
                            $resultTeacher = $connection2->prepare($sqlTeacher);
                            $resultTeacher->execute($dataTeacher);
                        } catch (PDOException $e) {
                        }
                        if ($resultTeacher->rowCount() > 0) {
                            $teacher = true;
                        }
                    } elseif ($highestAction == 'Lesson Planner_viewMyClasses') {
                        try {
                            /* closed By Bikash */
                            // $data = array('pupilsightSchoolYearID' => $_SESSION[$guid]['pupilsightSchoolYearID'], 'pupilsightProgramID' => $pupilsightProgramID, 'pupilsightCourseClassID' => $pupilsightCourseClassID, 'pupilsightPersonID' => $_SESSION[$guid]['pupilsightPersonID']);
                            // $sql = 'SELECT pupilsightCourseClass.pupilsightCourseClassID, pupilsightCourse.nameShort AS course, pupilsightCourseClass.nameShort AS class FROM pupilsightCourseClassPerson JOIN pupilsightCourseClass ON (pupilsightCourseClassPerson.pupilsightCourseClassID=pupilsightCourseClass.pupilsightCourseClassID) JOIN pupilsightCourse ON (pupilsightCourseClass.pupilsightCourseID=pupilsightCourse.pupilsightCourseID) WHERE pupilsightCourse.pupilsightSchoolYearID=:pupilsightSchoolYearID AND pupilsightCourseClass.pupilsightCourseClassID=:pupilsightCourseClassID AND pupilsightPersonID=:pupilsightPersonID';

                            $data = array('pupilsightProgramID' => $pupilsightProgramID, 'pupilsightCourseClassID' => $pupilsightCourseClassID);

                            $sql = "SELECT pupilsightPlannerEntry.pupilsightPlannerEntryID, pupilsightUnitID, pupilsightProgram.name AS progName, pupilsightYearGroup.name AS className , pupilsightRollGroup.name AS sectionName, pupilsightDepartment.name AS subjectName,pupilsightPlannerEntry.name, timeStart, timeEnd, viewableStudents, viewableParents, homework, 'Teacher' AS role, homeworkSubmission, homeworkCrowdAssess, date, pupilsightPlannerEntry.pupilsightCourseClassID, NULL AS myHomeworkDueDateTime FROM pupilsightPlannerEntry JOIN pupilsightProgram ON (pupilsightPlannerEntry.pupilsightProgramID=pupilsightProgram.pupilsightProgramID) JOIN pupilsightYearGroup ON (pupilsightPlannerEntry.pupilsightYearGroupID=pupilsightYearGroup.pupilsightYearGroupID) JOIN pupilsightRollGroup ON (pupilsightPlannerEntry.pupilsightRollGroupID=pupilsightRollGroup.pupilsightRollGroupID) JOIN pupilsightDepartment ON (pupilsightPlannerEntry.pupilsightDepartmentID=pupilsightDepartment.pupilsightDepartmentID) WHERE pupilsightProgramID=:pupilsightProgramID AND pupilsightCourseClassID=:pupilsightCourseClassID ORDER BY date, timeStart";

                            $result = $connection2->prepare($sql);
                            $result->execute($data);
                        } catch (PDOException $e) {
                            echo "<div class='alert alert-danger'>".$e->getMessage().'</div>';
                        }
                    }
                    //print_r($result->rowCount());

                    // closed by bikash if ($result->rowCount() != 1) {
                    if ($result->rowCount() <= 1) {
                        echo "<div class='alert alert-danger'>";
                        echo __('The selected record does not exist, or you do not have access to it.');
                        echo '</div>';
                    } else {
                        $row = $result->fetch();

                        $page->breadcrumbs->add(__('Planner for {classDesc}', [
                            'classDesc' => $row['course'].'.'.$row['class'],
                        ]));

                        //Get Smart Workflow help message
                        $category = getRoleCategory($_SESSION[$guid]['pupilsightRoleIDCurrent'], $connection2);
                        if ($category == 'Staff') {
                            $smartWorkflowHelp = getSmartWorkflowHelp($connection2, $guid, 3);
                            if ($smartWorkflowHelp != false) {
                                echo $smartWorkflowHelp;
                            }
                        }

                        $returns = array();
                        $returns['success1'] = __('Bump was successful. It is possible that some lessons have not been moved (if there was no space for them), but a reasonable effort has been made.');
                        if (isset($_GET['return'])) {
                            returnProcess($guid, $_GET['return'], null, $returns);
                        }

                        try {
                            if ($highestAction == 'Lesson Planner_viewEditAllClasses' or $highestAction == 'Lesson Planner_viewAllEditMyClasses') {
                                if ($subView == 'lesson' or $subView == '') {
                                    /* closed By Bikash */
                                    // $data = array('pupilsightCourseClassID' => $pupilsightCourseClassID);
                                    // $sql = "SELECT pupilsightPlannerEntryID, pupilsightUnitID, pupilsightCourse.nameShort AS course, pupilsightCourseClass.nameShort AS class, pupilsightPlannerEntry.name, date, timeStart, timeEnd, viewableStudents, viewableParents, homework, 'Teacher' as role, homeworkSubmission, homeworkCrowdAssess, pupilsightPlannerEntry.pupilsightCourseClassID, NULL AS myHomeworkDueDateTime FROM pupilsightPlannerEntry JOIN pupilsightCourseClass ON (pupilsightPlannerEntry.pupilsightCourseClassID=pupilsightCourseClass.pupilsightCourseClassID) JOIN pupilsightCourse ON (pupilsightCourse.pupilsightCourseID=pupilsightCourseClass.pupilsightCourseID) WHERE pupilsightPlannerEntry.pupilsightCourseClassID=:pupilsightCourseClassID ORDER BY date DESC, timeStart DESC";

                                    $data = array('pupilsightProgramID' => $pupilsightProgramID, 'pupilsightCourseClassID' => $pupilsightCourseClassID);

                                    $sql = "SELECT pupilsightPlannerEntry.pupilsightPlannerEntryID, pupilsightUnitID, pupilsightProgram.name AS progName, pupilsightYearGroup.name AS className , pupilsightRollGroup.name AS sectionName, pupilsightDepartment.name AS subjectName, pupilsightPlannerEntry.name, timeStart, timeEnd, viewableStudents, viewableParents, homework, 'Teacher' AS role, homeworkSubmission, homeworkCrowdAssess, date, pupilsightPlannerEntry.pupilsightCourseClassID, NULL AS myHomeworkDueDateTime FROM pupilsightPlannerEntry JOIN pupilsightProgram ON (pupilsightPlannerEntry.pupilsightProgramID=pupilsightProgram.pupilsightProgramID) JOIN pupilsightYearGroup ON (pupilsightPlannerEntry.pupilsightYearGroupID=pupilsightYearGroup.pupilsightYearGroupID) JOIN pupilsightRollGroup ON (pupilsightPlannerEntry.pupilsightRollGroupID=pupilsightRollGroup.pupilsightRollGroupID) JOIN pupilsightDepartment ON (pupilsightPlannerEntry.pupilsightDepartmentID=pupilsightDepartment.pupilsightDepartmentID) WHERE pupilsightPlannerEntry.pupilsightProgramID=:pupilsightProgramID AND pupilsightPlannerEntry.pupilsightCourseClassID=:pupilsightCourseClassID ORDER BY date, timeStart";
                                } else {
                                    $data = array('pupilsightCourseClassID' => $pupilsightCourseClassID);
                                    $sql = 'SELECT timeStart, timeEnd, date, pupilsightTTColumnRow.name AS period, pupilsightTTDayRowClassID, pupilsightTTDayDateID, NULL AS myHomeworkDueDateTime FROM pupilsightTTDayRowClass JOIN pupilsightTTColumnRow ON (pupilsightTTDayRowClass.pupilsightTTColumnRowID=pupilsightTTColumnRow.pupilsightTTColumnRowID) JOIN pupilsightTTColumn ON (pupilsightTTColumnRow.pupilsightTTColumnID=pupilsightTTColumn.pupilsightTTColumnID) JOIN pupilsightTTDay ON (pupilsightTTDayRowClass.pupilsightTTDayID=pupilsightTTDay.pupilsightTTDayID) JOIN pupilsightTTDayDate ON (pupilsightTTDayDate.pupilsightTTDayID=pupilsightTTDay.pupilsightTTDayID) WHERE pupilsightCourseClassID=:pupilsightCourseClassID ORDER BY date, timestart';
                                }
                            } elseif ($highestAction == 'Lesson Planner_viewMyClasses') {
                                $data = array('pupilsightCourseClassID' => $pupilsightCourseClassID, 'pupilsightPersonID' => $_SESSION[$guid]['pupilsightPersonID']);
                                $sql = "SELECT pupilsightPlannerEntry.pupilsightPlannerEntryID, pupilsightUnitID, pupilsightCourse.nameShort AS course, pupilsightCourseClass.nameShort AS class, pupilsightPlannerEntry.name, date, timeStart, timeEnd, viewableStudents, viewableParents, homework, role, homeworkSubmission, homeworkCrowdAssess, pupilsightPlannerEntry.pupilsightCourseClassID, pupilsightPlannerEntryStudentHomework.homeworkDueDateTime AS myHomeworkDueDateTime FROM pupilsightPlannerEntry JOIN pupilsightCourseClass ON (pupilsightPlannerEntry.pupilsightCourseClassID=pupilsightCourseClass.pupilsightCourseClassID) JOIN pupilsightCourseClassPerson ON (pupilsightCourseClass.pupilsightCourseClassID=pupilsightCourseClassPerson.pupilsightCourseClassID) JOIN pupilsightCourse ON (pupilsightCourse.pupilsightCourseID=pupilsightCourseClass.pupilsightCourseID) LEFT JOIN pupilsightPlannerEntryStudentHomework ON (pupilsightPlannerEntryStudentHomework.pupilsightPlannerEntryID=pupilsightPlannerEntry.pupilsightPlannerEntryID AND pupilsightPlannerEntryStudentHomework.pupilsightPersonID=pupilsightCourseClassPerson.pupilsightPersonID) WHERE pupilsightPlannerEntry.pupilsightCourseClassID=:pupilsightCourseClassID AND pupilsightCourseClassPerson.pupilsightPersonID=:pupilsightPersonID AND NOT role='Student - Left' AND NOT role='Teacher - Left' ORDER BY date DESC, timeStart DESC";
                            }
                            $result = $connection2->prepare($sql);
                            $result->execute($data);
                        } catch (PDOException $e) {
                            echo "<div class='alert alert-danger'>".$e->getMessage().'</div>';
                        }

                        //Only show add if user has edit rights
                        if ($highestAction == 'Lesson Planner_viewAllEditMyClasses' or $highestAction == 'Lesson Planner_viewEditAllClasses') {
                            echo "<div class='linkTop'>";
                            $style = '';
                            if ($subView == 'lesson' or $subView == '') {
                                $style = "style='font-weight: bold'";
                            }
                            echo "<a $style href='".$_SESSION[$guid]['absoluteURL']."/index.php?q=/modules/Planner/planner.php&viewBy=$viewBy&pupilsightCourseClassID=$pupilsightCourseClassID&subView=lesson'>".__('Lesson View').'</a> | ';
                            $style = '';
                            if ($subView == 'year') {
                                $style = "style='font-weight: bold'";
                            }
                            echo "<a $style href='".$_SESSION[$guid]['absoluteURL']."/index.php?q=/modules/Planner/planner.php&viewBy=$viewBy&pupilsightCourseClassID=$pupilsightCourseClassID&subView=year'>".__('Year Overview').'</a> | ';
                            echo "<a class='btn btn-primary' href='".$_SESSION[$guid]['absoluteURL']."/index.php?q=/modules/Planner/planner_add.php&viewBy=$viewBy&pupilsightProgramID=$pupilsightProgramID&pupilsightCourseClassID=$pupilsightCourseClassID&subView=$subView'>".__('Add')."</a>";
                            echo '</div>';
                        }

                        if ($result->rowCount() < 1) {
                            echo "<div class='alert alert-danger'>";
                            echo __('There are no records to display.');
                            echo '</div>';
                        } else {
                            //PRINT LESSON VIEW
                            if ($subView == 'lesson' or $subView == '') {
                                echo "<table cellspacing='0' style='width: 100%'>";
                                echo "<tr class='head'>";
                                echo '<th>';
                                echo __('Date');
                                echo '</th>';
                                echo '<th>';
                                echo __('Lesson').'</br>';
                                echo "<span style='font-size: 85%; font-style: italic'>".__('Unit').'</span>';
                                echo '</th>';
                                echo '<th>';
                                echo __('Time');
                                echo '</th>';
                                echo '<th>';
                                echo __('Homework');
                                echo '</th>';
                                echo '<th>';
                                echo __('Access');
                                echo '</th>';
                                echo "<th style='min-width: 150px'>";
                                echo __('Actions');
                                echo '</th>';
                                echo '</tr>';

                                $count = 0;
                                $pastCount = 0;
                                $rowNum = 'odd';
                                while ($row = $result->fetch()) {
                                    if ((!($row['role'] == 'Student' and $row['viewableStudents'] == 'N')) and (!($row['role'] == 'Guest Student' and $row['viewableStudents'] == 'N'))) {
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

                                        //Dull out past classes
                                        if ((($row['date']) == date('Y-m-d') and (date('H:i:s') > $row['timeEnd'])) or ($row['date']) < date('Y-m-d')) {
                                            $rowNum = 'past';
                                            if ($pastCount == 0) {
                                                echo "<tr style='padding: 0px; height: 2px; background-color: #000'>";
                                                echo "<td style='padding: 0px' colspan=8>";
                                                echo '</tr>';
                                            }
                                            ++$pastCount;
                                        }

                                        //COLOR ROW BY STATUS!
                                        echo "<tr class=$rowNum>";
                                        echo '<td>';
                                        if (!(is_null($row['date']))) {
                                            echo '<b>'.dateConvertBack($guid, $row['date']).'</b><br/>';
                                            echo Format::dateReadable($row['date'], '%A');
                                        }
                                        echo '</td>';
                                        echo '<td>';
                                        echo '<b>'.$row['name'].'</b><br/>';
                                        echo "<span style='font-size: 85%; font-style: italic'>";
                                        $unit = getUnit($connection2, $row['pupilsightUnitID'], $row['pupilsightCourseClassID']);
                                        if (isset($unit[0])) {
                                            echo $unit[0];
                                            if (isset($unit[1])) {
                                                if ($unit[1] != '') {
                                                    echo '<br/><i>'.$unit[1].' '.__('Unit').'</i>';
                                                }
                                            }
                                        }
                                        echo '</span>';
                                        echo '</td>';
                                        echo '<td>';
                                        if ($row['timeStart'] != '' and $row['timeEnd'] != '') {
                                            echo substr($row['timeStart'], 0, 5).'-'.substr($row['timeEnd'], 0, 5);
                                        }
                                        echo '</td>';
                                        echo '<td>';
                                        if ($row['homework'] == 'N' and $row['myHomeworkDueDateTime'] == '') {
                                            echo __('No');
                                        } else {
                                            if ($row['homework'] == 'Y') {
                                                echo __('Yes').': '.__('Teacher Recorded').'<br/>';
                                                if ($row['homeworkSubmission'] == 'Y') {
                                                    echo "<span style='font-size: 85%; font-style: italic'>+".__('Submission').'</span><br/>';
                                                    if ($row['homeworkCrowdAssess'] == 'Y') {
                                                        echo "<span style='font-size: 85%; font-style: italic'>+".__('Crowd Assessment').'</span><br/>';
                                                    }
                                                }
                                            }
                                            if ($row['myHomeworkDueDateTime'] != '') {
                                                echo __('Yes').': '.__('Student Recorded').'</br>';
                                            }
                                        }
                                        echo '</td>';
                                        echo '<td>';
                                        if ($row['viewableStudents'] == 'Y') {
                                            echo __('Students');
                                        }
                                        if ($row['viewableStudents'] == 'Y' and $row['viewableParents'] == 'Y') {
                                            echo ', ';
                                        }
                                        if ($row['viewableParents'] == 'Y') {
                                            echo __('Parents');
                                        }
                                        echo '</td>';
                                        echo '<td>';
                                        echo "<a href='".$_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/Planner/planner_view_full.php&pupilsightPlannerEntryID='.$row['pupilsightPlannerEntryID']."&viewBy=class&pupilsightCourseClassID=$pupilsightCourseClassID&width=1000&height=550'><i title='".__('View')."' class='mdi mdi-eye-outline mdi-24px'></i></a> ";
                                        if ((($highestAction == 'Lesson Planner_viewAllEditMyClasses' and $teacher == true) or $highestAction == 'Lesson Planner_viewEditAllClasses')) {
                                            echo "<a href='".$_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/Planner/planner_edit.php&pupilsightPlannerEntryID='.$row['pupilsightPlannerEntryID']."&viewBy=$viewBy&pupilsightProgramID=$pupilsightProgramID&pupilsightCourseClassID=$pupilsightCourseClassID'><i title='".__('Edit')."' class='mdi mdi-pencil-box-outline mdi-24px'></i></a> ";
                                            echo "<a href='".$_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/Planner/planner_bump.php&pupilsightPlannerEntryID='.$row['pupilsightPlannerEntryID']."&viewBy=$viewBy&pupilsightCourseClassID=$pupilsightCourseClassID'><i title='".__('Bump')."'  class='mdi mdi-arrow-right-circle-outline mdi-24px'></i></a>";
                                            echo "<a class='thickbox' href='".$_SESSION[$guid]['absoluteURL'].'/fullscreen.php?q=/modules/Planner/planner_delete.php&pupilsightPlannerEntryID='.$row['pupilsightPlannerEntryID']."&viewBy=$viewBy&pupilsightCourseClassID=$pupilsightCourseClassID&date=$date&subView=$subView&width=650&height=135'><i title='".__('Delete')."' class='mdi mdi-trash-can-outline mdi-24px'></i></a>";
                                        }
                                        if ($highestAction == 'Lesson Planner_viewAllEditMyClasses' or $highestAction == 'Lesson Planner_viewEditAllClasses') {
                                            echo "<a href='".$_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/Planner/planner_duplicate.php&pupilsightPlannerEntryID='.$row['pupilsightPlannerEntryID']."&viewBy=$viewBy&pupilsightCourseClassID=$pupilsightCourseClassID&date=$date'><i title='".__('Duplicate')."' class='mdi mdi-content-copy mdi-24px'></i></a>";
                                        }
                                        echo '</td>';
                                        echo '</tr>';
                                    }
                                }
                                echo '</table>';
                            }
                            //PRINT YEAR OVERVIEW
                            else {
                                $count = 0;
                                $lessons = array();
                                while ($rowNext = $result->fetch()) {
                                    try {
                                        $dataPlanner = array('date' => $rowNext['date'], 'timeStart' => $rowNext['timeStart'], 'timeEnd' => $rowNext['timeEnd'], 'pupilsightCourseClassID' => $pupilsightCourseClassID);
                                        $sqlPlanner = 'SELECT * FROM pupilsightPlannerEntry WHERE date=:date AND timeStart=:timeStart AND timeEnd=:timeEnd AND pupilsightCourseClassID=:pupilsightCourseClassID';
                                        $resultPlanner = $connection2->prepare($sqlPlanner);
                                        $resultPlanner->execute($dataPlanner);
                                    } catch (PDOException $e) {
                                        echo "<div class='alert alert-danger'>".$e->getMessage().'</div>';
                                    }
                                    if ($resultPlanner->rowCount() == 0) {
                                        $lessons[$count][0] = 'Unplanned';
                                        $lessons[$count][1] = $rowNext['date'];
                                        $lessons[$count][2] = $rowNext['timeStart'];
                                        $lessons[$count][3] = $rowNext['timeEnd'];
                                        $lessons[$count][4] = $rowNext['period'];
                                        $lessons[$count][6] = $rowNext['pupilsightTTDayRowClassID'];
                                        $lessons[$count][7] = $rowNext['pupilsightTTDayDateID'];
                                        $lessons[$count][11] = null;
                                        $lessons[$count][12] = null;
                                        $lessons[$count][13] = null;
                                    } else {
                                        $rowPlanner = $resultPlanner->fetch();
                                        $lessons[$count][0] = 'Planned';
                                        $lessons[$count][1] = $rowNext['date'];
                                        $lessons[$count][2] = $rowNext['timeStart'];
                                        $lessons[$count][3] = $rowNext['timeEnd'];
                                        $lessons[$count][4] = $rowNext['period'];
                                        $lessons[$count][5] = $rowPlanner['name'];
                                        $lessons[$count][6] = false;
                                        $lessons[$count][7] = false;
                                        $lessons[$count][11] = $rowPlanner['pupilsightUnitID'];
                                        $lessons[$count][12] = $rowPlanner['pupilsightPlannerEntryID'];
                                        $lessons[$count][13] = $rowPlanner['pupilsightCourseClassID'];
                                    }

                                    //Check for special days
                                    try {
                                        $dataSpecial = array('date' => $rowNext['date']);
                                        $sqlSpecial = 'SELECT * FROM pupilsightSchoolYearSpecialDay WHERE date=:date';
                                        $resultSpecial = $connection2->prepare($sqlSpecial);
                                        $resultSpecial->execute($dataSpecial);
                                    } catch (PDOException $e) {
                                        echo "<div class='alert alert-danger'>".$e->getMessage().'</div>';
                                    }

                                    if ($resultSpecial->rowCount() == 1) {
                                        $rowSpecial = $resultSpecial->fetch();
                                        $lessons[$count][8] = $rowSpecial['type'];
                                        $lessons[$count][9] = $rowSpecial['schoolStart'];
                                        $lessons[$count][10] = $rowSpecial['schoolEnd'];
                                    } else {
                                        $lessons[$count][8] = false;
                                        $lessons[$count][9] = false;
                                        $lessons[$count][10] = false;
                                    }

                                    ++$count;
                                }

                                if (count($lessons) < 1) {
                                    echo "<div class='alert alert-danger'>";
                                    echo __('There are no records to display.');
                                    echo '</div>';
                                } else {
                                    //Get term dates
                                    $terms = array();
                                    $termCount = 0;
                                    try {
                                        $dataTerms = array('pupilsightSchoolYearID' => $_SESSION[$guid]['pupilsightSchoolYearID']);
                                        $sqlTerms = 'SELECT * FROM pupilsightSchoolYearTerm WHERE pupilsightSchoolYearID=:pupilsightSchoolYearID ORDER BY sequenceNumber';
                                        $resultTerms = $connection2->prepare($sqlTerms);
                                        $resultTerms->execute($dataTerms);
                                    } catch (PDOException $e) {
                                        echo "<div class='alert alert-danger'>".$e->getMessage().'</div>';
                                    }

                                    while ($rowTerms = $resultTerms->fetch()) {
                                        $terms[$termCount][0] = $rowTerms['firstDay'];
                                        $terms[$termCount][1] = __('Start of').' '.$rowTerms['nameShort'];
                                        ++$termCount;
                                        $terms[$termCount][0] = $rowTerms['lastDay'];
                                        $terms[$termCount][1] = __('End of').' '.$rowTerms['nameShort'];
                                        ++$termCount;
                                    }
                                    //Get school closure special days
                                    $specials = array();
                                    $specialCount = 0;
                                    try {
                                        $dataSpecial = array('pupilsightSchoolYearID' => $_SESSION[$guid]['pupilsightSchoolYearID']);
                                        $sqlSpecial = "SELECT pupilsightSchoolYearSpecialDay.date, pupilsightSchoolYearSpecialDay.name FROM pupilsightSchoolYearSpecialDay JOIN pupilsightSchoolYearTerm ON (pupilsightSchoolYearSpecialDay.pupilsightSchoolYearTermID=pupilsightSchoolYearTerm.pupilsightSchoolYearTermID) WHERE pupilsightSchoolYearID=:pupilsightSchoolYearID AND type='School Closure' ORDER BY date";
                                        $resultSpecial = $connection2->prepare($sqlSpecial);
                                        $resultSpecial->execute($dataSpecial);
                                    } catch (PDOException $e) {
                                        echo "<div class='alert alert-danger'>".$e->getMessage().'</div>';
                                    }

                                    $lastName = '';
                                    $currentName = '';
                                    $lastDate = '';
                                    $currentDate = '';
                                    $originalDate = '';
                                    while ($rowSpecial = $resultSpecial->fetch()) {
                                        $currentName = $rowSpecial['name'];
                                        $currentDate = $rowSpecial['date'];
                                        if ($currentName != $lastName) {
                                            $currentName = $rowSpecial['name'];
                                            $specials[$specialCount][0] = $rowSpecial['date'];
                                            $specials[$specialCount][1] = $rowSpecial['name'];
                                            $specials[$specialCount][2] = dateConvertBack($guid, $rowSpecial['date']);
                                            $originalDate = dateConvertBack($guid, $rowSpecial['date']);
                                            ++$specialCount;
                                        } else {
                                            if ((strtotime($currentDate) - strtotime($lastDate)) == 86400) {
                                                $specials[$specialCount - 1][2] = $originalDate.' - '.dateConvertBack($guid, $rowSpecial['date']);
                                            } else {
                                                $currentName = $rowSpecial['name'];
                                                $specials[$specialCount][0] = $rowSpecial['date'];
                                                $specials[$specialCount][1] = $rowSpecial['name'];
                                                $specials[$specialCount][2] = dateConvertBack($guid, $rowSpecial['date']);
                                                $originalDate = dateConvertBack($guid, $rowSpecial['date']);
                                                ++$specialCount;
                                            }
                                        }
                                        $lastName = $rowSpecial['name'];
                                        $lastDate = $rowSpecial['date'];
                                    }

                                    echo "<table cellspacing='0' style='width: 100%'>";
                                    echo "<tr class='head'>";
                                    echo '<th>';
                                    echo __('Lesson<br/>Number');
                                    echo '</th>';
                                    echo '<th>';
                                    echo __('Date');
                                    echo '</th>';
                                    echo '<th>';
                                    echo __('TT Period').'<br/>';
                                    echo "<span style='font-size: 85%; font-style: italic'>Time</span>";
                                    echo '</th>';
                                    echo '<th>';
                                    echo __('Planned Lesson').'<br/>';
                                    echo "<span style='font-size: 85%; font-style: italic'>Unit</span>";
                                    echo '</th>';
                                    echo '<th>';
                                    echo __('Actions');
                                    echo '</th>';
                                    echo '</tr>';

                                    $count = 0;
                                    $termCount = 0;
                                    $specialCount = 0;
                                    $classCount = 0;
                                    $rowNum = 'odd';
                                    $divide = false; //Have we passed gotten to today yet?

                                    foreach ($lessons as $lesson) {
                                        if ($count % 2 == 0) {
                                            $rowNum = 'even';
                                        } else {
                                            $rowNum = 'odd';
                                        }

                                        $style = '';
                                        if ($lesson[1] >= date('Y-m-d') and $divide == false) {
                                            $divide = true;
                                            $style = "style='border-top: 2px solid #333'";
                                        }

                                        if ($divide == false) {
                                            $rowNum = 'error';
                                        }
                                        ++$count;

                                        //Spit out row for start of term
                                        while ($lesson['1'] >= $terms[$termCount][0] and $termCount < (count($terms) - 1)) {
                                            if (substr($terms[$termCount][1], 0, 3) == 'End' and $lesson['1'] == $terms[$termCount][0]) {
                                                break;
                                            } else {
                                                echo "<tr class='dull'>";
                                                echo '<td>';
                                                echo '<b>'.$terms[$termCount][1].'</b>';
                                                echo '</td>';
                                                echo '<td colspan=6>';
                                                echo dateConvertBack($guid, $terms[$termCount][0]);
                                                echo '</td>';
                                                echo '</tr>';
                                                ++$termCount;
                                            }
                                        }

                                        //Spit out row for special day
                                        while ($lesson['1'] >= @$specials[$specialCount][0] and $specialCount < count($specials)) {
                                            echo "<tr class='dull'>";
                                            echo '<td>';
                                            echo '<b>'.$specials[$specialCount][1].'</b>';
                                            echo '</td>';
                                            echo '<td colspan=6>';
                                            echo $specials[$specialCount][2];
                                            echo '</td>';
                                            echo '</tr>';
                                            ++$specialCount;
                                        }

                                        //COLOR ROW BY STATUS!
                                        if ($lesson[8] != 'School Closure') {
                                            echo "<tr class=$rowNum>";
                                            echo "<td $style>";
                                            echo '<b>Lesson '.($classCount + 1).'</b>';
                                            echo '</td>';
                                            echo "<td $style>";
                                            echo '<b>'.dateConvertBack($guid, $lesson['1']).'</b><br/>';
                                            echo Format::dateReadable($lesson['1'], '%A').'<br/>';
                                            echo Format::dateReadable($lesson['1'], '%B').'<br/>';
                                            if ($lesson[8] == 'Timing Change') {
                                                echo '<u>'.$lesson[8].'</u><br/><i>('.substr($lesson[9], 0, 5).'-'.substr($lesson[10], 0, 5).')</i>';
                                            }
                                            echo '</td>';
                                            echo "<td $style>";
                                            echo $lesson['4'].'<br/>';
                                            echo "<span style='font-size: 85%; font-style: italic'>".substr($lesson['2'], 0, 5).' - '.substr($lesson['3'], 0, 5).'</span>';
                                            echo '</td>';
                                            echo "<td $style>";
                                            if ($lesson['0'] == 'Planned') {
                                                echo '<b>'.$lesson['5'].'</b><br/>';
                                                $unit = getUnit($connection2, $lesson[11], $lesson[13]);
                                                if (isset($unit[0])) {
                                                    echo "<span style='font-size: 85%; font-style: italic'>";
                                                    echo $unit[0];
                                                    if (isset($unit[1])) {
                                                        if ($unit[1] != '') {
                                                            echo '<br/><i>'.$unit[1].' Unit</i>';
                                                        }
                                                    }
                                                    echo '</span>';
                                                }
                                            }
                                            echo '</td>';
                                            echo "<td $style>";
                                            if ($lesson['0'] == 'Unplanned') {
                                                echo "<a class='btn btn-primary' href='".$_SESSION[$guid]['absoluteURL']."/index.php?q=/modules/Planner/planner_add.php&viewBy=$viewBy&pupilsightCourseClassID=$pupilsightCourseClassID&date=".$lesson[1].'&timeStart='.$lesson[2].'&timeEnd='.$lesson[3]."&subView=$subView'>".__('Add')."</a>";
                                            } else {
                                                echo "<a href='".$_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/Planner/planner_view_full.php&pupilsightPlannerEntryID='.$lesson[12]."&viewBy=class&pupilsightCourseClassID=$pupilsightCourseClassID&width=1000&height=550&subView=$subView'><i title='".__('View')."' class='mdi mdi-eye-outline mdi-24px'></i></a> ";
                                                if ((($highestAction == 'Lesson Planner_viewAllEditMyClasses' and $teacher == true) or $highestAction == 'Lesson Planner_viewEditAllClasses')) {
                                                    echo "<a href='".$_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/Planner/planner_edit.php&pupilsightPlannerEntryID='.$lesson[12]."&viewBy=$viewBy&pupilsightCourseClassID=$pupilsightCourseClassID&subView=$subView'><i title='".__('Edit')."' class='mdi mdi-pencil-box-outline mdi-24px'></i></a> ";
                                                    echo "<a href='".$_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/Planner/planner_bump.php&pupilsightPlannerEntryID='.$lesson[12]."&viewBy=$viewBy&pupilsightCourseClassID=$pupilsightCourseClassID&subView=$subView'><i title='".__('Bump')."'  class='mdi mdi-arrow-right-circle-outline mdi-24px'></i></a>";
                                                    echo "<a class='thickbox' href='".$_SESSION[$guid]['absoluteURL'].'/fullscreen.php?q=/modules/Planner/planner_delete.php&pupilsightPlannerEntryID='.$lesson[12]."&viewBy=$viewBy&pupilsightCourseClassID=$pupilsightCourseClassID&date=$date&subView=$subView&width=650&height=135'><i title='".__('Delete')."' class='mdi mdi-trash-can-outline mdi-24px'></i></a>";
                                                    echo "<a href='".$_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/Planner/planner_duplicate.php&pupilsightPlannerEntryID='.$lesson[12]."&viewBy=$viewBy&pupilsightCourseClassID=$pupilsightCourseClassID&date=$date&subView=$subView'><i title='".__('Duplicate')."' class='mdi mdi-content-copy mdi-24px'></i></a>";
                                                }
                                            }
                                            echo '</td>';
                                            echo '</tr>';
                                            ++$classCount;
                                        }

                                        //Spit out row for end of term/year
                                        while ($lesson['1'] >= @$terms[$termCount][0] and $termCount < count($terms) and substr($terms[$termCount][1], 0, 3) == 'End') {
                                            echo "<tr class='dull'>";
                                            echo '<td>';
                                            echo '<b>'.$terms[$termCount][1].'</b>';
                                            echo '</td>';
                                            echo '<td colspan=6>';
                                            echo dateConvertBack($guid, $terms[$termCount][0]);
                                            echo '</td>';
                                            echo '</tr>';
                                            ++$termCount;
                                        }
                                    }

                                    if (@$terms[$termCount][0] != '') {
                                        echo "<tr class='dull'>";
                                        echo '<td>';
                                        echo '<b><u>'.$terms[$termCount][1].'</u></b>';
                                        echo '</td>';
                                        echo '<td colspan=6>';
                                        echo dateConvertBack($guid, $terms[$termCount][0]);
                                        echo '</td>';
                                        echo '</tr>';
                                    }
                                    echo '</table>';
                                }
                            }
                        }
                    }
                }
            }
        }
    }
    if ($pupilsightPersonID != '') {
        //Print sidebar
        $_SESSION[$guid]['sidebarExtra'] = sidebarExtra($guid, $connection2, $todayStamp, $pupilsightPersonID, $dateStamp, $pupilsightCourseClassID);
    }
}
