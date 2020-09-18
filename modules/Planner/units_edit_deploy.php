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
    ->add(__('Deploy Working Copy'));

if (isActionAccessible($guid, $connection2, '/modules/Planner/units_edit_deploy.php') == false) {
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
        if (isset($_GET['updateReturn'])) {
            $updateReturn = $_GET['updateReturn'];
        } else {
            $updateReturn = '';
        }
        $updateReturnMessage = '';
        $class = 'error';
        if (!($updateReturn == '')) {
            if ($updateReturn == 'fail0') {
                $updateReturnMessage = __('Your request failed because you do not have access to this action.');
            } elseif ($updateReturn == 'fail1') {
                $updateReturnMessage = __('Your request failed because your inputs were invalid.');
            } elseif ($updateReturn == 'fail2') {
                $updateReturnMessage = __('Your request failed due to a database error.');
            } elseif ($updateReturn == 'fail3') {
                $updateReturnMessage = __('Your request failed because your inputs were invalid.');
            } elseif ($updateReturn == 'fail4') {
                $updateReturnMessage = __('Your request failed because your inputs were invalid.');
            } elseif ($updateReturn == 'fail5') {
                $updateReturnMessage = __('Your request failed due to an attachment error.');
            } elseif ($updateReturn == 'success0') {
                $updateReturnMessage = __('Your request was completed successfully.');
                $class = 'success';
            }
            echo "<div class='$class'>";
            echo $updateReturnMessage;
            echo '</div>';
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

                        $step = null;
                        if (isset($_GET['step'])) {
                            $step = $_GET['step'];
                        }
                        if ($step != 1 and $step != 2 and $step != 3) {
                            $step = 1;
                        }

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

                        //Step 1
                        if ($step == 1) {
                            echo '<h3>';
                            echo __('Step 1 - Select Lessons');
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
                                    $terms[$termCount][1] = 'Start of '.$rowTerms['nameShort'];
                                    ++$termCount;
                                    $terms[$termCount][0] = $rowTerms['lastDay'];
                                    $terms[$termCount][1] = 'End of '.$rowTerms['nameShort'];
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

                                echo "<form method='post' action='".$_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.$_SESSION[$guid]['module']."/units_edit_deploy.php&step=2&pupilsightUnitID=$pupilsightUnitID&pupilsightSchoolYearID=$pupilsightSchoolYearID&pupilsightCourseID=$pupilsightCourseID&pupilsightCourseClassID=$pupilsightCourseClassID&pupilsightUnitClassID=$pupilsightUnitClassID'>";
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
                                echo __('TT Period').'/<br/>'.__('Time');
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
									while ($lesson['1'] >= @$terms[$termCount][0] and $termCount < (count($terms) - 1)) {
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
                                    echo '<b>'.$terms[$termCount][1].'</b>';
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
                        //Step 2
                        if ($step == 2) {
                            echo '<h3>';
                            echo __('Step 2 - Distribute Blocks');
                            echo '</h3>';
                            echo '<p>';
                            echo __('You can now add your unit blocks using the dropdown menu in each lesson. Blocks can be dragged from one lesson to another.');
                            echo '</p>';

                            //Store UNIT BLOCKS in array
                            $blocks = array();
                            try {
                                $dataBlocks = array('pupilsightUnitID' => $pupilsightUnitID);
                                $sqlBlocks = 'SELECT * FROM pupilsightUnitBlock WHERE pupilsightUnitID=:pupilsightUnitID ORDER BY sequenceNumber';
                                $resultBlocks = $connection2->prepare($sqlBlocks);
                                $resultBlocks->execute($dataBlocks);
                                $resultLessonBlocks = $connection2->prepare($sqlBlocks);
                                $resultLessonBlocks->execute($dataBlocks);
                            } catch (PDOException $e) {
                                echo "<div class='alert alert-danger'>".$e->getMessage().'</div>';
                            }
                            $blockCount = 0;
                            while ($rowBlocks = $resultBlocks->fetch()) {
                                $blocks[$blockCount][0] = $rowBlocks['pupilsightUnitBlockID'];
                                $blocks[$blockCount][1] = $rowBlocks['title'];
                                $blocks[$blockCount][2] = $rowBlocks['type'];
                                $blocks[$blockCount][3] = $rowBlocks['length'];
                                $blocks[$blockCount][4] = $rowBlocks['contents'];
                                $blocks[$blockCount][5] = $rowBlocks['teachersNotes'];
                                ++$blockCount;
                            }

                            //Create drag and drop environment for blocks
                            echo "<form method='post' action='".$_SESSION[$guid]['absoluteURL'].'/modules/'.$_SESSION[$guid]['module']."/units_edit_deployProcess.php?pupilsightUnitID=$pupilsightUnitID&pupilsightSchoolYearID=$pupilsightSchoolYearID&pupilsightCourseID=$pupilsightCourseID&pupilsightCourseClassID=$pupilsightCourseClassID&address=".$_GET['q']."&pupilsightUnitClassID=$pupilsightUnitClassID'>";
                            //LESSONS (SORTABLES)
                            echo "<div style='width: 100%; height: auto'>";
                            echo '<b>Lessons</b><br/>';
                            $lessonCount = $_POST['count'];
                            if ($lessonCount < 1) {
                                echo "<div class='alert alert-danger'>";
                                echo __('There are no records to display.');
                                echo '</div>';
                            } else {
                                $lessons = array();
                                $count = 0;
                                for ($i = 1; $i <= $lessonCount; ++$i) {
                                    if (isset($_POST["deploy$i"])) {
                                        if ($_POST["deploy$i"] == 'on') {
                                            $lessons[$count][0] = $_POST["date$i"];
                                            $lessons[$count][1] = $_POST["timeStart$i"];
                                            $lessons[$count][2] = $_POST["timeEnd$i"];
                                            $lessons[$count][3] = $_POST["period$i"];
                                            ++$count;
                                        }
                                    }
                                }

                                $cells = count($lessons);
                                if ($cells < 1) {
                                    echo "<div class='alert alert-danger'>";
                                    echo __('There are no records to display.');
                                    echo '</div>';
                                } else {
                                    $deployCount = 0;
                                    $blockCount2 = $blockCount;
                                    for ($i = 0; $i < $cells; ++$i) {
                                        echo "<div id='lessonInner$i' style='min-height: 60px; border: 1px solid #333; width: 100%; margin-bottom: 45px; float: left; padding: 2px; background-color: #F7F0E3'>";
                                        $length = ((strtotime($lessons[$i][0].' '.$lessons[$i][2]) - strtotime($lessons[$i][0].' '.$lessons[$i][1])) / 60);
                                        echo "<div id='sortable$i' style='min-height: 60px; font-size: 120%; font-style: italic'>";
                                        echo "<div id='head$i' class='head' style='height: 54px; font-size: 85%; padding: 3px'>";
                                        echo '<b>'.($i + 1).'. '.date('D jS M, Y', dateConvertToTimestamp($lessons[$i][0])).'</b><br/>';
                                        echo "<span style='font-size: 80%'><i>".$lessons[$i][3].' ('.substr($lessons[$i][1], 0, 5).' - '.substr($lessons[$i][2], 0, 5).')</span>';
                                        echo "<input type='hidden' name='order[]' value='lessonHeader-$i' >";
                                        echo "<input type='hidden' name='date$i' value='".$lessons[$i][0]."' >";
                                        echo "<input type='hidden' name='timeStart$i' value='".$lessons[$i][1]."' >";
                                        echo "<input type='hidden' name='timeEnd$i' value='".$lessons[$i][2]."' >";
                                        echo "<div style='text-align: right; float: right; margin-top: -17px; margin-right: 3px'>";
                                        echo "<span style='font-size: 80%'><i>".__('Add Block:').'</span><br/>';
                                        echo "<script type='text/javascript'>";
                                        echo '$(document).ready(function(){';
                                        echo "$(\"#blockAdd$i\").change(function(){";
                                        echo "if ($(\"#blockAdd$i\").val()!='') {";
                                        echo "$(\"#sortable$i\").append('<div id=\'blockOuter' + count + '\' class=\'blockOuter\'><div class=\'odd\' style=\'text-align: center; font-size: 75%; height: 60px; border: 1px solid #d8dcdf; margin: 0 0 5px\' id=\'block$i\' style=\'padding: 0px\'><img style=\'margin: 10px 0 5px 0\' src=\'".$_SESSION[$guid]['absoluteURL'].'/themes/'.$_SESSION[$guid]['pupilsightThemeName']."/img/loading.gif\' alt=\'Loading\' onclick=\'return false;\' /><br/>Loading</div></div>');";
                                        echo '$("#blockOuter" + count).load("'.$_SESSION[$guid]['absoluteURL']."/modules/Planner/units_add_blockAjax.php?mode=workingDeploy&pupilsightUnitID=$pupilsightUnitID&pupilsightUnitBlockID=\" + $(\"#blockAdd$i\").val(),\"id=\" + count) ;";
                                        echo 'count++ ;';
                                        echo '}';
                                        echo '}) ;';
                                        echo '}) ;';
                                        echo '</script>';
                                        echo "<select name='blockAdd$i' id='blockAdd$i' style='width: 150px'>";
                                        echo "<option value=''></option>";
                                        $blockSelectCount = 0;
                                        foreach ($blocks as $block) {
                                            echo "<option value='".$block[0]."'>".($blockSelectCount + 1).') '.htmlPrep($block[1]).'</option>';
                                            ++$blockSelectCount;
                                        }
                                        echo '</select>';
                                        echo '</div>';
                                        echo '</div>';

										//Prep outcomes
										try {
											$dataOutcomes = array('pupilsightUnitID' => $pupilsightUnitID);
											$sqlOutcomes = "SELECT pupilsightOutcome.pupilsightOutcomeID, pupilsightOutcome.name, pupilsightOutcome.category, scope, pupilsightDepartment.name AS department FROM pupilsightUnitOutcome JOIN pupilsightOutcome ON (pupilsightUnitOutcome.pupilsightOutcomeID=pupilsightOutcome.pupilsightOutcomeID) LEFT JOIN pupilsightDepartment ON (pupilsightOutcome.pupilsightDepartmentID=pupilsightDepartment.pupilsightDepartmentID) WHERE pupilsightUnitID=:pupilsightUnitID AND active='Y' ORDER BY sequenceNumber";
											$resultOutcomes = $connection2->prepare($sqlOutcomes);
											$resultOutcomes->execute($dataOutcomes);
										} catch (PDOException $e) {
											echo "<div class='alert alert-danger'>".$e->getMessage().'</div>';
										}
                                        $unitOutcomes = $resultOutcomes->fetchall();

										//Attempt auto deploy
										$spinCount = 0;
                                        while ($spinCount < $blockCount and $length > 0) {
                                            if (isset($blocks[$deployCount])) {
                                                if ($blocks[$deployCount][3] < 1 or $blocks[$deployCount][3] == '') {
                                                    ++$deployCount;
                                                } else {
                                                    if (($length - $blocks[$deployCount][3]) >= 0) {
                                                        makeBlock($guid,  $connection2, $blockCount2, $mode = 'workingDeploy', $blocks[$deployCount][1], $blocks[$deployCount][2], $blocks[$deployCount][3], $blocks[$deployCount][4], 'N', $blocks[$deployCount][0], '', $blocks[$deployCount][5], true);
                                                        $length = $length - $blocks[$deployCount][3];
                                                        ++$deployCount;
                                                    }
                                                }
                                            }

                                            ++$spinCount;
                                            ++$blockCount2;
                                        }
                                        echo '</div>';
                                        echo '</div>';
                                        echo "<script type='text/javascript'>";
                                        echo "var count=$blockCount2 ;";
                                        echo '</script>';
                                    }
                                }
                            }

                            ?>
							<b><?php echo __('Access') ?></b><br/>
							<table class='smallIntBorder' cellspacing='0' style="width: 100%">
								<tr id="accessRowStudents">
									<td>
										<b><?php echo __('Viewable to Students') ?> *</b><br/>
										<span class="emphasis small"></span>
									</td>
									<td class="right">
										<?php
										$sharingDefaultStudents = getSettingByScope($connection2, 'Planner', 'sharingDefaultStudents');
                            			?>
										<select name="viewableStudents" id="viewableStudents" class="standardWidth">
											<option <?php if ($sharingDefaultStudents == 'Y') { echo 'selected'; } ?> value="Y"><?php echo __('Yes') ?></option>
											<option <?php if ($sharingDefaultStudents == 'N') { echo 'selected'; } ?> value="N"><?php echo __('No') ?></option>
										</select>
									</td>
								</tr>
								<tr id="accessRowParents">
									<td>
										<b><?php echo __('Viewable to Parents') ?> *</b><br/>
										<span class="emphasis small"></span>
									</td>
									<td class="right">
										<?php
										$sharingDefaultParents = getSettingByScope($connection2, 'Planner', 'sharingDefaultParents');
                            			?>
										<select name="viewableParents" id="viewableParents" class="standardWidth">
											<option <?php if ($sharingDefaultParents == 'Y') { echo 'selected'; } ?> value="Y"><?php echo __('Yes') ?></option>
											<option <?php if ($sharingDefaultParents == 'N') { echo 'selected'; } ?> value="N"><?php echo __('No') ?></option>
										</select>
									</td>
								</tr>
							</table>

							<table class='blank' style='width: 100%' cellspacing=0>
								<tr>
									<td>
										<?php
										echo "<div style='width: 100%; margin-bottom: 20px; text-align: right'>";
										echo "<input type='submit' value='Submit'>";
										echo '</div>';
										?>
									</td>
								</tr>
							</table>
						<?php
						echo '</div>';
					echo '</form>';

					//Add drag/drop controls
					$sortableList = '';
					?>
					<style>
						.default { border: none; background-color: #ffffff }
						.drop { border: none; background-color: #eeeeee }
						.hover { border: none; background-color: #D4F6DC }
					</style>

					<script type="text/javascript">
						$(function() {
							var receiveCount=0 ;

							//Create list of lesson sortables
							<?php for ($i = 0; $i < $cells; ++$i) { ?>
								<?php $sortableList .= "#sortable$i, " ?>
							<?php } ?>
								//Create lesson sortables
								<?php for ($i = 0; $i < $cells; ++$i) { ?>
									$( "#sortable<?php echo $i ?>" ).sortable({
										revert: false,
										tolerance: 15,
										connectWith: "<?php echo substr($sortableList, 0, -2) ?>",
										items: "div.blockOuter",
										receive: function(event,ui) {
											var sortid=$(newItem).attr("id", 'receive'+receiveCount) ;
											var receiveid='receive'+receiveCount ;
											$('#' + receiveid + ' .delete').show() ;
											$('#' + receiveid + ' .delete').click(function() {
												$('#' + receiveid).fadeOut(600, function(){
													$('#' + receiveid).remove();
												});
											});
											receiveCount++ ;
										},
										beforeStop: function (event, ui) {
										 newItem=ui.item;
										}
									});
								<?php } ?>

								//Draggables
								<?php for ($i = 0; $i < $blockCount; ++$i) { ?>
									$( "#draggable<?php echo $i ?>" ).draggable({
										connectToSortable: "<?php echo substr($sortableList, 0, -2) ?>",
										helper: "clone"
									});
								<?php } ?>

							});
						</script>
						<?php
                        }
                    }
                }
            }
        }
    }
    //Print sidebar
    $_SESSION[$guid]['sidebarExtra'] = sidebarExtraUnits($guid, $connection2, $pupilsightCourseID, $pupilsightSchoolYearID);
}
