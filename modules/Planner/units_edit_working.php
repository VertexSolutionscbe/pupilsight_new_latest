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
    ->add(__('Edit Working Copy'));

if (isActionAccessible($guid, $connection2, '/modules/Planner/units_edit_working.php') == false) {
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
                        echo __('Lessons & Blocks');
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
                            $blocks[$blockCount][5] = $rowBlocks['teachersNotes'];
                            ++$blockCount;
                        }



                        echo "<form method='post' action='".$_SESSION[$guid]['absoluteURL'].'/modules/'.$_SESSION[$guid]['module']."/units_edit_workingProcess.php?pupilsightUnitID=$pupilsightUnitID&pupilsightSchoolYearID=$pupilsightSchoolYearID&pupilsightCourseID=$pupilsightCourseID&pupilsightCourseClassID=$pupilsightCourseClassID&address=".$_GET['q']."&pupilsightUnitClassID=$pupilsightUnitClassID'>";
                        //LESSONS (SORTABLES)
                        echo "<div class='linkTop'>";
                        echo "<a href='".$_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.$_SESSION[$guid]['module']."/units_edit_working_add.php&pupilsightUnitID=$pupilsightUnitID&pupilsightSchoolYearID=$pupilsightSchoolYearID&pupilsightCourseID=$pupilsightCourseID&pupilsightCourseClassID=$pupilsightCourseClassID&pupilsightUnitClassID=$pupilsightUnitClassID'>".__('Add')."<img style='margin-left: 5px' title='".__('Add')."' src='./themes/".$_SESSION[$guid]['pupilsightThemeName']."/img/page_new.png'/></a>";
                        echo '</div>';
                        echo "<div style='width: 100%; height: auto'>";
                        try {
                            $dataLessons = array('pupilsightCourseClassID' => $pupilsightCourseClassID, 'pupilsightUnitID' => $pupilsightUnitID);
                            $sqlLessons = 'SELECT * FROM pupilsightPlannerEntry WHERE pupilsightCourseClassID=:pupilsightCourseClassID AND pupilsightUnitID=:pupilsightUnitID ORDER BY date, timeStart';
                            $resultLessons = $connection2->prepare($sqlLessons);
                            $resultLessons->execute($dataLessons);
                        } catch (PDOException $e) {
                            echo "<div class='alert alert-danger'>".$e->getMessage().'</div>';
                        }

                        if ($resultLessons->rowCount() < 1) {
                            echo "<div class='alert alert-danger'>";
                            echo __('There are no records to display.');
                            echo '</div>';
                        } else {
                            $i = 0;
                            $blockCount2 = $blockCount;
                            while ($rowLessons = $resultLessons->fetch()) {
                                echo "<div class='lessonInner' id='lessonInner$i' style='min-height: 60px; border: 1px solid #333; width: 100%; margin-bottom: 65px; float: left; padding: 2px; background-color: #F7F0E3'>";
                                echo "<div class='sortable' id='sortable$i' style='height: auto!important; min-height: 60px; font-size: 120%; font-style: italic'>";
                                echo "<div id='head$i' class='head' style='height: 54px; font-size: 85%; padding: 3px'>";

                                echo "<a onclick='return confirm(\"Are you sure you want to jump to this lesson? Any unsaved changes will be lost.\")' style='font-weight: bold; font-style: normal; color: #333' href='".$_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/Planner/planner_view_full.php&viewBy=class&pupilsightCourseClassID='.$rowLessons['pupilsightCourseClassID'].'&pupilsightPlannerEntryID='.$rowLessons['pupilsightPlannerEntryID']."'>".($i + 1).'. '.$rowLessons['name']."</a> <a onclick='return confirm(\"".__('Are you sure you want to delete this record? Any unsaved changes will be lost.')."\")' href='".$_SESSION[$guid]['absoluteURL'].'/modules/'.$_SESSION[$guid]['module']."/units_edit_working_lessonDelete.php?pupilsightUnitID=$pupilsightUnitID&pupilsightSchoolYearID=$pupilsightSchoolYearID&pupilsightCourseID=$pupilsightCourseID&pupilsightCourseClassID=$pupilsightCourseClassID&pupilsightUnitClassID=$pupilsightUnitClassID&address=".$_GET['q'].'&pupilsightPlannerEntryID='.$rowLessons['pupilsightPlannerEntryID']."'><img title='".__('Delete')."' src='./themes/".$_SESSION[$guid]['pupilsightThemeName']."/img/garbage.png'/ style='position: absolute; margin: -1px 0px 2px 10px'></a><br/>";

                                try {
                                    $dataTT = array('date' => $rowLessons['date'], 'timeStart' => $rowLessons['timeStart'], 'timeEnd' => $rowLessons['timeEnd'], 'pupilsightCourseClassID' => $pupilsightCourseClassID);
                                    $sqlTT = 'SELECT timeStart, timeEnd, date, pupilsightTTColumnRow.name AS period, pupilsightTTDayRowClassID, pupilsightTTDayDateID FROM pupilsightTTDayRowClass JOIN pupilsightTTColumnRow ON (pupilsightTTDayRowClass.pupilsightTTColumnRowID=pupilsightTTColumnRow.pupilsightTTColumnRowID) JOIN pupilsightTTColumn ON (pupilsightTTColumnRow.pupilsightTTColumnID=pupilsightTTColumn.pupilsightTTColumnID) JOIN pupilsightTTDay ON (pupilsightTTDayRowClass.pupilsightTTDayID=pupilsightTTDay.pupilsightTTDayID) JOIN pupilsightTTDayDate ON (pupilsightTTDayDate.pupilsightTTDayID=pupilsightTTDay.pupilsightTTDayID) WHERE date=:date AND timeStart=:timeStart AND timeEnd=:timeEnd AND pupilsightCourseClassID=:pupilsightCourseClassID ORDER BY date, timestart';
                                    $resultTT = $connection2->prepare($sqlTT);
                                    $resultTT->execute($dataTT);
                                } catch (PDOException $e) {
                                    echo "<div class='alert alert-danger'>".$e->getMessage().'</div>';
                                }

                                if ($resultTT->rowCount() == 1) {
                                    $rowTT = $resultTT->fetch();
                                    echo "<span style='font-size: 80%'><i>".date('D jS M, Y', dateConvertToTimestamp($rowLessons['date'])).'<br/>'.$rowTT['period'].' ('.substr($rowLessons['timeStart'], 0, 5).' - '.substr($rowLessons['timeEnd'], 0, 5).')</i></span>';
                                } else {
                                    echo "<span style='font-size: 80%'><i>";
                                    if ($rowLessons['date'] != '') {
                                        echo date('D jS M, Y', dateConvertToTimestamp($rowLessons['date'])).'<br/>';
                                        echo substr($rowLessons['timeStart'], 0, 5).' - '.substr($rowLessons['timeEnd'], 0, 5).'</i>';
                                    } else {
                                        echo 'Date not set<br/>';
                                    }
                                    echo '</i></span>';
                                }

                                echo "<input type='hidden' name='order[]' value='lessonHeader-$i' >";
                                echo "<input type='hidden' name='date$i' value='".$rowLessons['date']."' >";
                                echo "<input type='hidden' name='timeStart$i' value='".$rowLessons['timeStart']."' >";
                                echo "<input type='hidden' name='timeEnd$i' value='".$rowLessons['timeEnd']."' >";
                                echo "<input type='hidden' name='pupilsightPlannerEntryID$i' value='".$rowLessons['pupilsightPlannerEntryID']."' >";
                                echo "<div style='text-align: right; float: right; margin-top: -33px; margin-right: 3px'>";
                                echo "<span style='font-size: 80%'><i>".__('Add Block:').'</i></span><br/>';
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

								//Get blocks
								try {
									$dataLessonBlocks = array('pupilsightPlannerEntryID' => $rowLessons['pupilsightPlannerEntryID'], 'pupilsightCourseClassID' => $pupilsightCourseClassID);
									$sqlLessonBlocks = 'SELECT * FROM pupilsightUnitClassBlock JOIN pupilsightUnitClass ON (pupilsightUnitClassBlock.pupilsightUnitClassID=pupilsightUnitClass.pupilsightUnitClassID) WHERE pupilsightPlannerEntryID=:pupilsightPlannerEntryID AND pupilsightCourseClassID=:pupilsightCourseClassID ORDER BY sequenceNumber';
									$resultLessonBlocks = $connection2->prepare($sqlLessonBlocks);
									$resultLessonBlocks->execute($dataLessonBlocks);
								} catch (PDOException $e) {
									echo "<div class='alert alert-danger'>".$e->getMessage().'</div>';
								}

								//Get outcomes
								try {
									$dataOutcomes = array('pupilsightUnitID' => $pupilsightUnitID);
									$sqlOutcomes = "SELECT pupilsightOutcome.pupilsightOutcomeID, pupilsightOutcome.name, pupilsightOutcome.category, scope, pupilsightDepartment.name AS department FROM pupilsightUnitOutcome JOIN pupilsightOutcome ON (pupilsightUnitOutcome.pupilsightOutcomeID=pupilsightOutcome.pupilsightOutcomeID) LEFT JOIN pupilsightDepartment ON (pupilsightOutcome.pupilsightDepartmentID=pupilsightDepartment.pupilsightDepartmentID) WHERE pupilsightUnitID=:pupilsightUnitID AND active='Y' ORDER BY sequenceNumber";
									$resultOutcomes = $connection2->prepare($sqlOutcomes);
									$resultOutcomes->execute($dataOutcomes);
								} catch (PDOException $e) {
									echo "<div class='alert alert-danger'>".$e->getMessage().'</div>';
								}
                                $unitOutcomes = $resultOutcomes->fetchall();

                                while ($rowLessonBlocks = $resultLessonBlocks->fetch()) {
                                    makeBlock($guid,  $connection2, $blockCount2, $mode = 'workingEdit', $rowLessonBlocks['title'], $rowLessonBlocks['type'], $rowLessonBlocks['length'], $rowLessonBlocks['contents'], $rowLessonBlocks['complete'], $rowLessonBlocks['pupilsightUnitBlockID'], $rowLessonBlocks['pupilsightUnitClassBlockID'], $rowLessonBlocks['teachersNotes'], true);
                                    ++$blockCount2;
                                }
                                echo '</div>';
                                echo '</div>';
                                ++$i;
                            }
                            $cells = $i;
                        }
                        ?>
						<div class='linkTop' style='margin-top: 0px!important'>
							<?php
							echo "<script type='text/javascript'>";
							echo "var count=$blockCount2 ;";
							echo '</script>';
							echo "<input type='submit' value='Submit'>";
							?>
						</div>
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
											$('#' + receiveid + ' .completeDiv').show() ;
											$('#' + receiveid + ' .complete').show() ;
											$('#' + receiveid + ' .complete').click(function() {
												if ($('#' + receiveid + ' .complete').is(':checked')==true) {
													$('#' + receiveid + ' .completeHide').val('on') ;
												} else {
													$('#' + receiveid + ' .completeHide').val('off') ;
												}
											});
											receiveCount++ ;
										},
										beforeStop: function (event, ui) {
										 newItem=ui.item;
										}
									});
									<?php for ($j = $blockCount; $j < $blockCount2; ++$j) { ?>
										$("#draggable<?php echo $j ?> .delete").show() ;
										$("#draggable<?php echo $j ?> .delete").click(function() {
											$("#draggable<?php echo $j ?>").fadeOut(600, function(){
												$("#draggable<?php echo $j ?>").remove();
											});
										});
										$("#draggable<?php echo $j ?> .completeDiv").show() ;
										$("#draggable<?php echo $j ?> .complete").show() ;
										$("#draggable<?php echo $j ?> .complete").click(function() {
												if ($("#draggable<?php echo $j ?> .complete").is(':checked')==true) {
													$("#draggable<?php echo $j ?> .completeHide").val('on') ;
												} else {
													$("#draggable<?php echo $j ?> .completeHide").val('off') ;
												}
											});
									<?php }
								} ?>

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
    //Print sidebar
    $_SESSION[$guid]['sidebarExtra'] = sidebarExtraUnits($guid, $connection2, $pupilsightCourseID, $pupilsightSchoolYearID);
}
