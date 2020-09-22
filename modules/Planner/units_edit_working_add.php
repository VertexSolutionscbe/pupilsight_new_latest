<?php
/*
Pupilsight, Flexible & Open School System
*/

//Module includes
require_once __DIR__ . '/moduleFunctions.php';

// common variables
$pupilsightSchoolYearID = $_GET['pupilsightSchoolYearID'] ?? '';
$pupilsightCourseID = $_GET['pupilsightCourseID'] ?? '';
$pupilsightCourseClassID = $_GET['pupilsightCourseClassID'] ?? '';
$pupilsightUnitID = $_GET['pupilsightUnitID'] ?? '';
$pupilsightUnitClassID = $_GET['pupilsightUnitClassID'] ?? '';

$page->breadcrumbs
    ->add(__('Unit Planner'), 'units.php', [
        'pupilsightSchoolYearID' => $pupilsightSchoolYearID,
        'pupilsightCourseID' => $pupilsightCourseID,
    ])
    ->add(__('Edit Unit'), 'units_edit.php', [
        'pupilsightSchoolYearID' => $pupilsightSchoolYearID,
        'pupilsightCourseID' => $pupilsightCourseID,
        'pupilsightUnitID' => $pupilsightUnitID,
    ])
    ->add(__('Edit Working Copy'), 'units_edit_working.php', [
        'pupilsightSchoolYearID' => $pupilsightSchoolYearID,
        'pupilsightCourseID' => $pupilsightCourseID,
        'pupilsightUnitID' => $pupilsightUnitID,
        'pupilsightCourseClassID' => $pupilsightCourseClassID,
        'pupilsightUnitClassID' => $pupilsightUnitClassID,
    ])
    ->add(__('Add Lessons'));

if (isActionAccessible($guid, $connection2, '/modules/Planner/units_edit_working_add.php') == false) {
    //Acess denied
    echo "<div class='alert alert-danger'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    //Get action with highest precendence
    $highestAction = getHighestGroupedAction($guid, $_GET['q'], $connection2);
    if ($highestAction == false) {
        echo "<div class='alert alert-danger'>";
        echo __('The highest grouped action cannot be determined.');
        echo '</div>';
    } else {
        //Proceed!
        if (isset($_GET['return'])) {
            returnProcess($guid, $_GET['return'], null, null);
        }

        //Check if courseschool year specified
        if ($pupilsightCourseID == '' or $pupilsightSchoolYearID == '' or $pupilsightCourseClassID == '' or $pupilsightUnitClassID == '') {
            echo "<div class='alert alert-danger'>";
            echo __('You have not specified one or more required parameters.');
            echo '</div>';
        } else {
            try {
                if ($highestAction == 'Unit Planner_all') {
                    $data = array('pupilsightSchoolYearID' => $pupilsightSchoolYearID, 'pupilsightCourseID' => $pupilsightCourseID, 'pupilsightCourseClassID' => $pupilsightCourseClassID);
                    $sql = 'SELECT *, pupilsightSchoolYear.name AS year, pupilsightCourse.nameShort AS course, pupilsightCourseClass.nameShort AS class FROM pupilsightCourse JOIN pupilsightCourseClass ON (pupilsightCourse.pupilsightCourseID=pupilsightCourseClass.pupilsightCourseID) JOIN pupilsightSchoolYear ON (pupilsightCourse.pupilsightSchoolYearID=pupilsightSchoolYear.pupilsightSchoolYearID) WHERE pupilsightCourse.pupilsightSchoolYearID=:pupilsightSchoolYearID AND pupilsightCourse.pupilsightCourseID=:pupilsightCourseID AND pupilsightCourseClassID=:pupilsightCourseClassID';
                } elseif ($highestAction == 'Unit Planner_learningAreas') {
                    $data = array('pupilsightSchoolYearID' => $pupilsightSchoolYearID, 'pupilsightCourseID' => $pupilsightCourseID, 'pupilsightCourseClassID' => $pupilsightCourseClassID, 'pupilsightPersonID' => $_SESSION[$guid]['pupilsightPersonID']);
                    $sql = "SELECT pupilsightCourse.pupilsightCourseID, pupilsightCourse.name, pupilsightCourse.nameShort, pupilsightSchoolYear.name AS year, pupilsightCourse.nameShort AS course, pupilsightCourseClass.nameShort AS class FROM pupilsightCourse JOIN pupilsightCourseClass ON (pupilsightCourse.pupilsightCourseID=pupilsightCourseClass.pupilsightCourseID) JOIN pupilsightSchoolYear ON (pupilsightCourse.pupilsightSchoolYearID=pupilsightSchoolYear.pupilsightSchoolYearID) JOIN pupilsightDepartment ON (pupilsightCourse.pupilsightDepartmentID=pupilsightDepartment.pupilsightDepartmentID) JOIN pupilsightDepartmentStaff ON (pupilsightDepartmentStaff.pupilsightDepartmentID=pupilsightDepartment.pupilsightDepartmentID) WHERE pupilsightDepartmentStaff.pupilsightPersonID=:pupilsightPersonID AND (role='Coordinator' OR role='Assistant Coordinator' OR role='Teacher (Curriculum)') AND pupilsightCourse.pupilsightSchoolYearID=:pupilsightSchoolYearID AND pupilsightCourse.pupilsightCourseID=:pupilsightCourseID AND pupilsightCourseClassID=:pupilsightCourseClassID ORDER BY pupilsightCourse.nameShort";
                }
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
                $year = $row['year'];
                $course = $row['course'];
                $class = $row['class'];

                //Check if unit specified
                if ($pupilsightUnitID == '') {
                    echo "<div class='alert alert-danger'>";
                    echo __('You have not specified one or more required parameters.');
                    echo '</div>';
                } else {
                    try {
                        $data = array('pupilsightUnitID' => $pupilsightUnitID, 'pupilsightCourseID' => $pupilsightCourseID);
                        $sql = 'SELECT pupilsightCourse.nameShort AS courseName, pupilsightUnit.* FROM pupilsightUnit JOIN pupilsightCourse ON (pupilsightUnit.pupilsightCourseID=pupilsightCourse.pupilsightCourseID) WHERE pupilsightUnitID=:pupilsightUnitID AND pupilsightUnit.pupilsightCourseID=:pupilsightCourseID';
                        $result = $connection2->prepare($sql);
                        $result->execute($data);
                    } catch (PDOException $e) {
                        echo "<div class='alert alert-danger'>".$e->getMessage().'</div>';
                    }

                    if ($result->rowCount() != 1) {
                        echo "<div class='alert alert-danger'>";
                        echo __('The specified record cannot be found.');
                        echo '</div>';
                    } else {
                        //Let's go!
                        $row = $result->fetch();

                        echo "<table class='table'>";
                        echo '<tr>';
                        echo "<td style='width: 34%; vertical-align: top'>";
                        echo "<span class='form-label'>".__('School Year').'</span><br/>';
                        echo '<i>'.$year.'</i>';
                        echo '</td>';
                        echo "<td style='width: 33%; vertical-align: top'>";
                        echo "<span class='form-label'>".__('Class').'</span><br/>';
                        echo '<i>'.$course.'.'.$class.'</i>';
                        echo '</td>';
                        echo "<td style='width: 34%; vertical-align: top'>";
                        echo "<span class='form-label'>".__('Unit').'</span><br/>';
                        echo '<i>'.$row['name'].'</i>';
                        echo '</td>';
                        echo '</tr>';
                        echo '</table>';

                        echo '<h3>';
                        echo __('Choose Lessons');
                        echo '</h3>';
                        echo '<p>';
                        echo __('Use the table below to select the lessons you wish to deploy this unit to. Only lessons without existing plans can be included in the deployment.');
                        echo '</p>';

                        //Find all unplanned slots for this class.
                        try {
                            $dataNext = array('pupilsightCourseClassID' => $pupilsightCourseClassID);
                            $sqlNext = 'SELECT timeStart, timeEnd, date, pupilsightTTColumnRow.name AS period, pupilsightTTDayRowClassID, pupilsightTTDayDateID FROM pupilsightTTDayRowClass JOIN pupilsightTTColumnRow ON (pupilsightTTDayRowClass.pupilsightTTColumnRowID=pupilsightTTColumnRow.pupilsightTTColumnRowID) JOIN pupilsightTTColumn ON (pupilsightTTColumnRow.pupilsightTTColumnID=pupilsightTTColumn.pupilsightTTColumnID) JOIN pupilsightTTDay ON (pupilsightTTDayRowClass.pupilsightTTDayID=pupilsightTTDay.pupilsightTTDayID) JOIN pupilsightTTDayDate ON (pupilsightTTDayDate.pupilsightTTDayID=pupilsightTTDay.pupilsightTTDayID) WHERE pupilsightCourseClassID=:pupilsightCourseClassID ORDER BY date, timestart';
                            $resultNext = $connection2->prepare($sqlNext);
                            $resultNext->execute($dataNext);
                        } catch (PDOException $e) {
                            echo "<div class='alert alert-danger'>".$e->getMessage().'</div>';
                        }

                        $count = 0;
                        $lessons = array();
                        while ($rowNext = $resultNext->fetch()) {
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
                                $dataTerms = array('pupilsightSchoolYearID' => $pupilsightSchoolYearID);
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
                                $dataSpecial = array('pupilsightSchoolYearID' => $pupilsightSchoolYearID);
                                $sqlSpecial = "SELECT pupilsightSchoolYearSpecialDay.date, pupilsightSchoolYearSpecialDay.name FROM pupilsightSchoolYearSpecialDay JOIN pupilsightSchoolYearTerm ON (pupilsightSchoolYearSpecialDay.pupilsightSchoolYearTermID=pupilsightSchoolYearTerm.pupilsightSchoolYearTermID) WHERE pupilsightSchoolYearID=:pupilsightSchoolYearID AND type='School Closure' ORDER BY date";
                                $resultSpecial = $connection2->prepare($sqlSpecial);
                                $resultSpecial->execute($dataSpecial);
                            } catch (PDOException $e) {
                                echo "<div class='alert alert-danger'>".$e->getMessage().'</div>';
                            }
                            $lastName = '';
                            $currentName = '';
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

                            echo "<form method='post' action='".$_SESSION[$guid]['absoluteURL'].'/modules/'.$_SESSION[$guid]['module']."/units_edit_working_addProcess.php?pupilsightUnitID=$pupilsightUnitID&pupilsightSchoolYearID=$pupilsightSchoolYearID&pupilsightCourseID=$pupilsightCourseID&pupilsightCourseClassID=$pupilsightCourseClassID&pupilsightUnitClassID=$pupilsightUnitClassID&address=".$_GET['q']."'>";
                            echo "<table cellspacing='0' style='width: 100%'>";
                            echo "<tr class='head'>";
                            echo '<th>';
                            echo sprintf(__('Lesson%1$sNumber'), '<br/>');
                            echo '</th>';
                            echo '<th>';
                            echo __('Date');
                            echo '</th>';
                            echo '<th>';
                            echo __('Day');
                            echo '</th>';
                            echo '<th>';
                            echo __('Month');
                            echo '</th>';
                            echo '<th>';
                            echo sprintf(__('TT Period%1$sTime'), '<br/>');
                            echo '</th>';
                            echo '<th>';
                            echo sprintf(__('Planned%1$sLesson'), '<br/>');
                            echo '</th>';
                            echo '<th>';
                            echo __('Include?');
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
									echo dateConvertBack($guid, $lesson['1']).'<br/>';
									if ($lesson[8] == 'Timing Change') {
										echo '<u>'.$lesson[8].'</u><br/><i>('.substr($lesson[9], 0, 5).'-'.substr($lesson[10], 0, 5).')</i>';
									}
									echo '</td>';
									echo "<td $style>";
									echo date('D', dateConvertToTimestamp($lesson['1']));
									echo '</td>';
									echo "<td $style>";
									echo date('M', dateConvertToTimestamp($lesson['1']));
									echo '</td>';
									echo "<td $style>";
									echo $lesson['4'].'<br/>';
									echo substr($lesson['2'], 0, 5).' - '.substr($lesson['3'], 0, 5);
									echo '</td>';
									echo "<td $style>";
									if ($lesson['0'] == 'Planned') {
										echo $lesson['5'].'<br/>';
									}
									echo '</td>';
									echo "<td $style>";
									if ($lesson['0'] == 'Unplanned') {
										echo "<input name='deploy$count' type='checkbox'>";
										echo "<input name='date$count' type='hidden' value='".$lesson['1']."'>";
										echo "<input name='timeStart$count' type='hidden' value='".$lesson['2']."'>";
										echo "<input name='timeEnd$count' type='hidden' value='".$lesson['3']."'>";
										echo "<input name='period$count' type='hidden' value='".$lesson['4']."'>";
										echo "<input name='pupilsightTTDayRowClassID$count' type='hidden' value='".$lesson['6']."'>";
										echo "<input name='pupilsightTTDayDateID$count' type='hidden' value='".$lesson['7']."'>";
									}
									echo '</td>';
									echo '</tr>';
									++$classCount;
								}

								//Spit out row for end of term
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

                            echo '<tr>';
                            echo "<td class='right' colspan=7>";
                            echo "<input name='count' id='count' value='$count' type='hidden'>";
                            echo "<input id='submit' type='submit' value='Submit'>";
                            echo '</td>';
                            echo '</tr>';
                            echo '</table>';
                            echo '</form>';
                        }
                    }
                }
            }
        }
    }
    //Print sidebar
    $_SESSION[$guid]['sidebarExtra'] = sidebarExtraUnits($guid, $connection2, $pupilsightCourseID, $pupilsightSchoolYearID);
}
