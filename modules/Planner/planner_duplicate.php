<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Forms\Form;

//Module includes
require_once __DIR__ . '/moduleFunctions.php';

if (isActionAccessible($guid, $connection2, '/modules/Planner/planner_duplicate.php') == false) {
    //Acess denied
    echo "<div class='alert alert-danger'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
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
            $date = $_GET['date'];
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
                'subView' => $subView,
            ];
		}

        list($todayYear, $todayMonth, $todayDay) = explode('-', $today);
        $todayStamp = mktime(0, 0, 0, $todayMonth, $todayDay, $todayYear);

        //Check if school year specified
        $pupilsightCourseClassID = $_GET['pupilsightCourseClassID'];
        $pupilsightPlannerEntryID = $_GET['pupilsightPlannerEntryID'];
        if ($pupilsightPlannerEntryID == '' or ($viewBy == 'class' and $pupilsightCourseClassID == 'Y')) {
            echo "<div class='alert alert-danger'>";
            echo __('You have not specified one or more required parameters.');
            echo '</div>';
        } else {
            try {
                if ($viewBy == 'date') {
                    $data = array('date' => $date, 'pupilsightPlannerEntryID' => $pupilsightPlannerEntryID);
                    $sql = 'SELECT pupilsightPlannerEntryID, pupilsightUnitID, pupilsightCourse.nameShort AS course, pupilsightCourseClass.nameShort AS class, pupilsightPlannerEntry.name FROM pupilsightPlannerEntry JOIN pupilsightCourseClass ON (pupilsightPlannerEntry.pupilsightCourseClassID=pupilsightCourseClass.pupilsightCourseClassID) JOIN pupilsightCourse ON (pupilsightCourse.pupilsightCourseID=pupilsightCourseClass.pupilsightCourseID) WHERE date=:date AND pupilsightPlannerEntryID=:pupilsightPlannerEntryID';
                } else {
                    $data = array('pupilsightCourseClassID' => $pupilsightCourseClassID, 'pupilsightPlannerEntryID' => $pupilsightPlannerEntryID);
                    $sql = 'SELECT pupilsightPlannerEntryID, pupilsightUnitID, pupilsightCourse.nameShort AS course, pupilsightCourseClass.nameShort AS class, pupilsightPlannerEntry.name FROM pupilsightPlannerEntry JOIN pupilsightCourseClass ON (pupilsightPlannerEntry.pupilsightCourseClassID=pupilsightCourseClass.pupilsightCourseClassID) JOIN pupilsightCourse ON (pupilsightCourse.pupilsightCourseID=pupilsightCourseClass.pupilsightCourseID) WHERE pupilsightPlannerEntry.pupilsightCourseClassID=:pupilsightCourseClassID AND pupilsightPlannerEntryID=:pupilsightPlannerEntryID';
                }
                $result = $connection2->prepare($sql);
                $result->execute($data);
            } catch (PDOException $e) {
                echo "<div class='alert alert-danger'>".$e->getMessage().'</div>';
            }

            if ($result->rowCount() != 1) {
                $otherYearDuplicateSuccess = false;
                //Deal with duplicate to other year
                $returns = array();
                $returns['success0'] = __('Your request was completed successfully, but the target class is in another year, so you cannot see the results here.');
                if (isset($_GET['return'])) {
                    returnProcess($guid, $_GET['return'], null, $returns);
                }
                if ($otherYearDuplicateSuccess != true) {
                    echo "<div class='alert alert-danger'>";
                    echo __('The selected record does not exist, or you do not have access to it.');
                    echo '</div>';
                }
            } else {
                //Let's go!
				$values = $result->fetch();

				// target of the planner
				$target = ($viewBy === 'class') ? $values['course'].'.'.$values['class'] : dateConvertBack($guid, $date);

				$page->breadcrumbs
					->add(__('Planner for {classDesc}', [
						'classDesc' => $target,
					]), 'planner.php', $params)
					->add(__('Duplicate Lesson Plan'));

                if (isset($_GET['return'])) {
                    returnProcess($guid, $_GET['return'], null, null);
                }

                $step = null;
                if (isset($_GET['step'])) {
                    $step = $_GET['step'];
                }
                if ($step != 1 and $step != 2) {
                    $step = 1;
                }

                if ($step == 1) {
                    echo "<p>".__('This process will duplicate all aspects of the selected lesson. If a lesson is copied into another course, Smart Block content will be added into the lesson body, so it does not get left out.')."</p>";

                    $form = Form::create('action', $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.$_SESSION[$guid]['module']."/planner_duplicate.php&pupilsightPlannerEntryID=$pupilsightPlannerEntryID&viewBy=$viewBy&pupilsightCourseClassID=$pupilsightCourseClassID&date=$date&step=2");

                    $form->addHiddenValue('viewBy', $viewBy);
                    $form->addHiddenValue('pupilsightPlannerEntryID_org',  $pupilsightPlannerEntryID);
                    $form->addHiddenValue('subView', $subView);
                    $form->addHiddenValue('address', $_SESSION[$guid]['address']);

                    $data = array('pupilsightSchoolYearID' => $_SESSION[$guid]['pupilsightSchoolYearID']);
                    $sql = 'SELECT pupilsightSchoolYearID AS value, name FROM pupilsightSchoolYear WHERE sequenceNumber>=(SELECT sequenceNumber FROM pupilsightSchoolYear WHERE pupilsightSchoolYearID=:pupilsightSchoolYearID) ORDER BY sequenceNumber';
                    $row = $form->addRow();
                        $row->addLabel('pupilsightSchoolYearID', __('Target Year'));
                        $row->addSelect('pupilsightSchoolYearID')->fromQuery($pdo, $sql, $data)->required()->placeholder()->selected($_SESSION[$guid]['pupilsightSchoolYearID']);


                    if ($highestAction == 'Lesson Planner_viewEditAllClasses') {
                        $data = array('pupilsightSchoolYearID' => $_SESSION[$guid]['pupilsightSchoolYearID']);
                        $sql = 'SELECT pupilsightSchoolYear.pupilsightSchoolYearID AS chainedTo, pupilsightCourseClass.pupilsightCourseClassID AS value, CONCAT(pupilsightCourse.nameShort,".",pupilsightCourseClass.nameShort) AS name FROM pupilsightCourseClass JOIN pupilsightCourse ON (pupilsightCourseClass.pupilsightCourseID=pupilsightCourse.pupilsightCourseID) JOIN pupilsightSchoolYear ON (pupilsightCourse.pupilsightSchoolYearID=pupilsightSchoolYear.pupilsightSchoolYearID) WHERE pupilsightSchoolYear.sequenceNumber>=(SELECT sequenceNumber FROM pupilsightSchoolYear WHERE pupilsightSchoolYearID=:pupilsightSchoolYearID) ORDER BY pupilsightSchoolYear.pupilsightSchoolYearID, name';
                    } else {
                        $data = array('pupilsightSchoolYearID' => $_SESSION[$guid]['pupilsightSchoolYearID'], 'pupilsightPersonID' => $_SESSION[$guid]['pupilsightPersonID']);
                        $sql = 'SELECT pupilsightSchoolYear.pupilsightSchoolYearID AS chainedTo, pupilsightCourseClass.pupilsightCourseClassID AS value, CONCAT(pupilsightCourse.nameShort,".",pupilsightCourseClass.nameShort) AS name FROM pupilsightCourseClassPerson JOIN pupilsightCourseClass ON (pupilsightCourseClassPerson.pupilsightCourseClassID=pupilsightCourseClass.pupilsightCourseClassID) JOIN pupilsightCourse ON (pupilsightCourseClass.pupilsightCourseID=pupilsightCourse.pupilsightCourseID) JOIN pupilsightSchoolYear ON (pupilsightCourse.pupilsightSchoolYearID=pupilsightSchoolYear.pupilsightSchoolYearID) WHERE pupilsightSchoolYear.sequenceNumber>=(SELECT sequenceNumber FROM pupilsightSchoolYear WHERE pupilsightSchoolYearID=:pupilsightSchoolYearID) AND pupilsightPersonID=:pupilsightPersonID ORDER BY name';
                    }
                    $row = $form->addRow();
                        $row->addLabel('pupilsightCourseClassID', __('Target Class'));
                        $row->addSelect('pupilsightCourseClassID')->fromQueryChained($pdo, $sql, $data, 'pupilsightSchoolYearID')->required()->placeholder();

                    //DUPLICATE MARKBOOK COLUMN?
                    try {
                        $dataMarkbook = array('pupilsightCourseClassID' => $pupilsightCourseClassID, 'pupilsightPlannerEntryID' => $pupilsightPlannerEntryID);
                        $sqlMarkbook = 'SELECT * FROM pupilsightMarkbookColumn WHERE pupilsightCourseClassID=:pupilsightCourseClassID AND pupilsightPlannerEntryID=:pupilsightPlannerEntryID';
                        $resultMarkbook = $connection2->prepare($sqlMarkbook);
                        $resultMarkbook->execute($dataMarkbook);
                    } catch (PDOException $e) {
                        echo "<div class='alert alert-danger'>".$e->getMessage().'</div>';
                    }
                    if ($resultMarkbook->rowCount() >= 1) {
                        $row = $form->addRow();
                            $row->addLabel('duplicate', __('Duplicate Markbook Columns?'))->description(__('Will duplicate any columns linked to this lesson.'));
                            $row->addYesNo('duplicate')->selected('N');
                    }

                    $row = $form->addRow();
                        $row->addFooter();
                        $row->addSubmit(__('Next'));

                    echo $form->getOutput();

                } elseif ($step == 2) {
                    $pupilsightPlannerEntryID_org = $_POST['pupilsightPlannerEntryID_org'];
                    $pupilsightCourseClassID = $_POST['pupilsightCourseClassID'];
                    $pupilsightSchoolYearID = $_POST['pupilsightSchoolYearID'];
                    $duplicate = null;
                    if (isset($_POST['duplicate'])) {
                        $duplicate = $_POST['duplicate'];
                    }
                    if ($pupilsightCourseClassID == '' or $pupilsightSchoolYearID == '') {
                        echo "<div class='alert alert-danger'>";
                        echo __('You have not specified one or more required parameters.');
                        echo '</div>';
                    } else {
                        $form = Form::create('action', $_SESSION[$guid]['absoluteURL'].'/modules/'.$_SESSION[$guid]['module']."/planner_duplicateProcess.php?pupilsightPlannerEntryID=$pupilsightPlannerEntryID");

                        $form->addHiddenValue('duplicate', $duplicate);
                        $form->addHiddenValue('pupilsightPlannerEntryID_org', $pupilsightPlannerEntryID_org);
                        $form->addHiddenValue('pupilsightCourseClassID', $pupilsightCourseClassID);
                        $form->addHiddenValue('pupilsightSchoolYearID', $pupilsightSchoolYearID);
                        $form->addHiddenValue('viewBy', $viewBy);
                        $form->addHiddenValue('subView', $subView);
                        $form->addHiddenValue('address', $_SESSION[$guid]['address']);

                        $class='';
                        try {
                            if ($highestAction == 'Lesson Planner_viewEditAllClasses') {
                                $dataSelect = array('pupilsightCourseClassID' => $pupilsightCourseClassID, 'pupilsightSchoolYearID' => $pupilsightSchoolYearID);
                                $sqlSelect = 'SELECT pupilsightCourseClass.pupilsightCourseClassID, pupilsightCourse.nameShort AS course, pupilsightCourseClass.nameShort AS class FROM pupilsightCourseClass JOIN pupilsightCourse ON (pupilsightCourseClass.pupilsightCourseID=pupilsightCourse.pupilsightCourseID) WHERE pupilsightCourse.pupilsightSchoolYearID=:pupilsightSchoolYearID AND pupilsightCourseClass.pupilsightCourseClassID=:pupilsightCourseClassID ORDER BY course, class';
                            } else {
                                $dataSelect = array('pupilsightCourseClassID' => $pupilsightCourseClassID, 'pupilsightSchoolYearID' => $pupilsightSchoolYearID, 'pupilsightPersonID' => $_SESSION[$guid]['pupilsightPersonID']);
                                $sqlSelect = 'SELECT pupilsightCourseClass.pupilsightCourseClassID, pupilsightCourse.nameShort AS course, pupilsightCourseClass.nameShort AS class FROM pupilsightCourseClassPerson JOIN pupilsightCourseClass ON (pupilsightCourseClassPerson.pupilsightCourseClassID=pupilsightCourseClass.pupilsightCourseClassID) JOIN pupilsightCourse ON (pupilsightCourseClass.pupilsightCourseID=pupilsightCourse.pupilsightCourseID) WHERE pupilsightCourse.pupilsightSchoolYearID=:pupilsightSchoolYearID AND pupilsightPersonID=:pupilsightPersonID AND pupilsightCourseClass.pupilsightCourseClassID=:pupilsightCourseClassID ORDER BY course, class';
                            }
                            $resultSelect = $connection2->prepare($sqlSelect);
                            $resultSelect->execute($dataSelect);
                        } catch (PDOException $e) {
                            echo $e->getMEssage();
                        }
                        if ($resultSelect->rowCount() == 1) {
                            $rowSelect = $resultSelect->fetch();
                            $class = htmlPrep($rowSelect['course']).'.'.htmlPrep($rowSelect['class']);
                        }
                        $row = $form->addRow();
                            $row->addLabel('class', __('Class'));
                            $row->addTextField('class')->setValue($class)->readonly()->required();

                        if ($values['pupilsightUnitID'] != '' && $pupilsightSchoolYearID == $_SESSION[$guid]['pupilsightSchoolYearID']) {
                            //KEEP IN UNIT
                            try {
                                $dataMarkbook = array('pupilsightCourseClassID' => $pupilsightCourseClassID, 'pupilsightUnitID' => $values['pupilsightUnitID']);
                                $sqlMarkbook = 'SELECT * FROM pupilsightUnitClass WHERE pupilsightCourseClassID=:pupilsightCourseClassID AND pupilsightUnitID=:pupilsightUnitID';
                                $resultMarkbook = $connection2->prepare($sqlMarkbook);
                                $resultMarkbook->execute($dataMarkbook);
                            } catch (PDOException $e) {
                                echo "<div class='alert alert-danger'>".$e->getMessage().'</div>';
                            }

                            if ($resultMarkbook->rowCount() == 1) {
                                $rowMarkbook = $resultMarkbook->fetch();
                                $form->addHiddenValue('pupilsightUnitClassID', $rowMarkbook['pupilsightUnitClassID']);

                                $row = $form->addRow();
                                    $row->addLabel('keepUnit', __('Keep lesson in original unit?'))->description(__('Only available if source and target classes are in the same course.'));
                                    $row->addYesNo('keepUnit')->selected('Y')->required();

                            }
                        }

                        $row = $form->addRow();
                            $row->addLabel('name', __('Name'));
                            $row->addTextField('name')->setValue($values['name'])->maxLength(50)->required();

                        //Try and find the next unplanned slot for this class.
                        try {
                            $dataNext = array('pupilsightCourseClassID' => $pupilsightCourseClassID, 'date' => date('Y-m-d'));
                            $sqlNext = 'SELECT timeStart, timeEnd, date FROM pupilsightTTDayRowClass JOIN pupilsightTTColumnRow ON (pupilsightTTDayRowClass.pupilsightTTColumnRowID=pupilsightTTColumnRow.pupilsightTTColumnRowID) JOIN pupilsightTTColumn ON (pupilsightTTColumnRow.pupilsightTTColumnID=pupilsightTTColumn.pupilsightTTColumnID) JOIN pupilsightTTDay ON (pupilsightTTDayRowClass.pupilsightTTDayID=pupilsightTTDay.pupilsightTTDayID) JOIN pupilsightTTDayDate ON (pupilsightTTDayDate.pupilsightTTDayID=pupilsightTTDay.pupilsightTTDayID) WHERE pupilsightCourseClassID=:pupilsightCourseClassID AND date>=:date ORDER BY date, timestart LIMIT 0, 10';
                            $resultNext = $connection2->prepare($sqlNext);
                            $resultNext->execute($dataNext);
                        } catch (PDOException $e) {
                        }
                        $nextDate = '';
                        $nextTimeStart = '';
                        $nextTimeEnd = '';
                        while ($rowNext = $resultNext->fetch()) {
                            try {
                                $dataPlanner = array('date' => $rowNext['date'], 'timeStart' => $rowNext['timeStart'], 'timeEnd' => $rowNext['timeEnd'], 'pupilsightCourseClassID' => $pupilsightCourseClassID);
                                $sqlPlanner = 'SELECT * FROM pupilsightPlannerEntry WHERE date=:date AND timeStart=:timeStart AND timeEnd=:timeEnd AND pupilsightCourseClassID=:pupilsightCourseClassID';
                                $resultPlanner = $connection2->prepare($sqlPlanner);
                                $resultPlanner->execute($dataPlanner);
                            } catch (PDOException $e) {}
                            if ($resultPlanner->rowCount() == 0) {
                                $nextDate = $rowNext['date'];
                                $nextTimeStart = $rowNext['timeStart'];
                                $nextTimeEnd = $rowNext['timeEnd'];
                                break;
                            }
                        }
                        $row = $form->addRow();
                            $row->addLabel('date', __('Date'));
                            $row->addDate('date')->setValue(dateConvertBack($guid, $nextDate))->required();

                        $row = $form->addRow();
                            $row->addLabel('timeStart', __('Start Time'))->description("Format: hh:mm (24hr)");
                            $row->addTime('timeStart')->setValue(substr($nextTimeStart, 0, 5))->required();

                        $row = $form->addRow();
                            $row->addLabel('timeEnd', __('End Time'))->description("Format: hh:mm (24hr)");
                            $row->addTime('timeEnd')->setValue(substr($nextTimeEnd, 0, 5))->required();

                        $row = $form->addRow();
                            $row->addFooter();
                            $row->addSubmit();

                        echo $form->getOutput();
                    }
                }
            }
        }
        //Print sidebar
        $_SESSION[$guid]['sidebarExtra'] = sidebarExtra($guid, $connection2, $todayStamp, $_SESSION[$guid]['pupilsightPersonID'], $dateStamp, $pupilsightCourseClassID);
    }
}
