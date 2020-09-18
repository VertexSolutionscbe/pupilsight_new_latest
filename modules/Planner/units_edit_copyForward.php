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
    ->add(__('Copy Unit Forward'));

if (isActionAccessible($guid, $connection2, '/modules/Planner/units_edit_copyForward.php') == false) {
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
        if ($pupilsightCourseID == '' or $pupilsightSchoolYearID == '' or $pupilsightCourseClassID == '') {
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

                        echo '<p>';
                        echo sprintf(__('This function allows you to take the selected working unit (%1$s in %2$s) and use its blocks, and the master unit details, to create a new unit. In this way you can use your refined and improved unit as a new master unit whilst leaving your existing master unit untouched.'), $row['name'], "$course.$class");
                        echo '</p>';

                        ?>
						<form method="post" action="<?php echo $_SESSION[$guid]['absoluteURL'].'/modules/'.$_SESSION[$guid]['module']."/units_edit_copyForwardProcess.php?pupilsightUnitID=$pupilsightUnitID&pupilsightCourseID=$pupilsightCourseID&pupilsightCourseClassID=$pupilsightCourseClassID&pupilsightSchoolYearID=$pupilsightSchoolYearID" ?>">
							<table class='smallIntBorder fullWidth' cellspacing='0'>	
								<tr class='break'>
									<td colspan=2> 
										<h3><?php echo __('Source') ?></h3>
									</td>
								</tr>
								<tr>
									<td style='width: 275px'> 
										<b><?php echo __('School Year') ?> *</b><br/>
										<span class="emphasis small"><?php echo __('This value cannot be changed.') ?></span>
									</td>
									<td class="right">
										<?php
                                        echo "<input readonly value='".$year."' type='text' style='width: 300px'>";
                        				?>
									</td>
								</tr>
								<tr>
									<td> 
										<b><?php echo __('Class') ?> *</b><br/>
										<span class="emphasis small"><?php echo __('This value cannot be changed.') ?></span>
									</td>
									<td class="right">
										<?php echo "<input readonly value='".$course.'.'.$class."' type='text' style='width: 300px'>";
                       				 	?>
									</td>
								</tr>
								<tr>
									<td> 
										<b><?php echo __('Unit') ?> *</b><br/>
										<span class="emphasis small"><?php echo __('This value cannot be changed.') ?></span>
									</td>
									<td class="right">
										<?php echo "<input readonly value='".$row['name']."' type='text' style='width: 300px'>";
                        				?>
									</td>
								</tr>
								
								<tr class='break'>
									<td colspan=2> 
										<h3><?php echo __('Target') ?></h3>
									</td>
								</tr>
										
								<tr>
									<td> 
										<b><?php echo __('Year') ?> *</b><br/>
									</td>
									<td class="right">
										<select name="pupilsightSchoolYearIDCopyTo" id="pupilsightSchoolYearIDCopyTo" class="standardWidth">
											<?php
                                            echo "<option value='Please select...'>".__('Please select...').'</option>';
											try {
												$dataSelect = array('pupilsightSchoolYearID' => $_SESSION[$guid]['pupilsightSchoolYearID']);
												$sqlSelect = 'SELECT * FROM pupilsightSchoolYear WHERE pupilsightSchoolYearID=:pupilsightSchoolYearID';
												$resultSelect = $connection2->prepare($sqlSelect);
												$resultSelect->execute($dataSelect);
											} catch (PDOException $e) {
											}
											if ($resultSelect->rowCount() == 1) {
												$rowSelect = $resultSelect->fetch();
												try {
													$dataSelect2 = array('sequenceNumber' => $rowSelect['sequenceNumber']);
													$sqlSelect2 = 'SELECT * FROM pupilsightSchoolYear WHERE sequenceNumber>=:sequenceNumber ORDER BY sequenceNumber ASC';
													$resultSelect2 = $connection2->prepare($sqlSelect2);
													$resultSelect2->execute($dataSelect2);
												} catch (PDOException $e) {
												}
												while ($rowSelect2 = $resultSelect2->fetch()) {
													echo "<option value='".$rowSelect2['pupilsightSchoolYearID']."'>".htmlPrep($rowSelect2['name']).'</option>';
												}
											}
											?>				
										</select>
										<script type="text/javascript">
											var pupilsightSchoolYearIDCopyTo=new LiveValidation('pupilsightSchoolYearIDCopyTo');
											pupilsightSchoolYearIDCopyTo.add(Validate.Exclusion, { within: ['Please select...'], failureMessage: "<?php echo __('Select something!') ?>"});
										</script>
									</td>
								</tr>
								<tr>
									<td> 
										<b><?php echo __('Course') ?> *</b><br/>
									</td>
									<td class="right">
										<select name="pupilsightCourseIDTarget" id="pupilsightCourseIDTarget" class="standardWidth">
											<?php
                                            try {
                                                if ($highestAction == 'Unit Planner_all') {
                                                    $dataSelect = array();
                                                    $sqlSelect = 'SELECT pupilsightCourse.nameShort AS course, pupilsightSchoolYear.name AS year, pupilsightCourseID, pupilsightSchoolYear.pupilsightSchoolYearID FROM pupilsightCourse JOIN pupilsightSchoolYear ON (pupilsightCourse.pupilsightSchoolYearID=pupilsightSchoolYear.pupilsightSchoolYearID) ORDER BY nameShort';
                                                } elseif ($highestAction == 'Unit Planner_learningAreas') {
                                                    $dataSelect = array('pupilsightPersonID' => $_SESSION[$guid]['pupilsightPersonID']);
                                                    $sqlSelect = "SELECT pupilsightCourse.nameShort AS course, pupilsightSchoolYear.name AS year, pupilsightCourseID, pupilsightSchoolYear.pupilsightSchoolYearID FROM pupilsightCourse JOIN pupilsightSchoolYear ON (pupilsightCourse.pupilsightSchoolYearID=pupilsightSchoolYear.pupilsightSchoolYearID) JOIN pupilsightDepartment ON (pupilsightCourse.pupilsightDepartmentID=pupilsightDepartment.pupilsightDepartmentID) JOIN pupilsightDepartmentStaff ON (pupilsightDepartmentStaff.pupilsightDepartmentID=pupilsightDepartment.pupilsightDepartmentID) WHERE pupilsightDepartmentStaff.pupilsightPersonID=:pupilsightPersonID AND (role='Coordinator' OR role='Assistant Coordinator' OR role='Teacher (Curriculum)') ORDER BY pupilsightCourse.nameShort";
                                                }
                                                $resultSelect = $connection2->prepare($sqlSelect);
                                                $resultSelect->execute($dataSelect);
                                            } catch (PDOException $e) {
                                            }
											while ($rowSelect = $resultSelect->fetch()) {
												echo "<option class='".$rowSelect['pupilsightSchoolYearID']."' value='".$rowSelect['pupilsightCourseID']."'>".htmlPrep($rowSelect['course']).'</option>';
											}
											?>				
										</select>
										<script type="text/javascript">
											$("#pupilsightCourseIDTarget").chainedTo("#pupilsightSchoolYearIDCopyTo");
										</script>
									</td>
								</tr>
								<tr>
									<td> 
										<b><?php echo __('New Unit Name') ?> *</b><br/>
										<span class="emphasis small"></span>
									</td>
									<td class="right">
										<?php echo "<input name='nameTarget' id='nameTarget' value='".$row['name']."' type='text' style='width: 300px'>"; ?>
										<script type="text/javascript">
											var nameTarget=new LiveValidation('nameTarget');
											nameTarget.add(Validate.Presence);
										</script>
									</td>
								</tr>
								
								<tr>
									<td>
										<span class="emphasis small">* <?php echo __('denotes a required field'); ?></span>
									</td>
									<td class="right">
										<input name="pupilsightCourseClassID" id="pupilsightCourseClassID" value="<?php echo $pupilsightCourseClassID ?>" type="hidden">
										<input name="pupilsightCourseID" id="pupilsightCourseID" value="<?php echo $pupilsightCourseID ?>" type="hidden">
										<input name="pupilsightUnitID" id="pupilsightUnitID" value="<?php echo $pupilsightUnitID ?>" type="hidden">
										<input name="pupilsightSchoolYearID" id="pupilsightSchoolYearID" value="<?php echo $pupilsightSchoolYearID ?>" type="hidden">
										<input type="hidden" name="address" value="<?php echo $_SESSION[$guid]['address'] ?>">
										<input type="submit" value="<?php echo __('Submit'); ?>">
									</td>
								</tr>
							</table>
						</form>
						<?php

                    }
                }
            }
        }
    }
    //Print sidebar
    $_SESSION[$guid]['sidebarExtra'] = sidebarExtraUnits($guid, $connection2, $pupilsightCourseID, $pupilsightSchoolYearID);
}
