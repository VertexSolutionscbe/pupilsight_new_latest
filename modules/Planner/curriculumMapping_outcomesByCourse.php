<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Forms\Form;

//Module includes
require_once __DIR__ . '/moduleFunctions.php';

$page->breadcrumbs->add(__('Outcomes By Course'));

if (isActionAccessible($guid, $connection2, '/modules/Planner/curriculumMapping_outcomesByCourse.php') == false) {
    //Acess denied
    echo "<div class='alert alert-danger'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    //Proceed!
    echo '<p>';
    echo __('This view gives an overview of which whole school and learning area outcomes are covered by classes in a given course, allowing for curriculum mapping by outcome and course.');
    echo '</p>';

    echo '<h2>';
    echo __('Choose Course');
    echo '</h2>';

    $pupilsightCourseID = isset($_GET['pupilsightCourseID'])? $_GET['pupilsightCourseID'] : null;

	$form = Form::create('searchForm', $_SESSION[$guid]['absoluteURL'].'/index.php', 'get');

	$form->addHiddenValue('q', '/modules/'.$_SESSION[$guid]['module'].'/curriculumMapping_outcomesByCourse.php');


	$data = array('pupilsightSchoolYearID' => $_SESSION[$guid]['pupilsightSchoolYearID']);
	$sql = "SELECT pupilsightCourse.pupilsightCourseID, pupilsightCourse.name, pupilsightDepartment.name AS department FROM pupilsightCourse LEFT JOIN pupilsightDepartment ON (pupilsightCourse.pupilsightDepartmentID=pupilsightDepartment.pupilsightDepartmentID) WHERE pupilsightCourse.pupilsightSchoolYearID=:pupilsightSchoolYearID AND NOT pupilsightYearGroupIDList='' ORDER BY department, pupilsightCourse.nameShort";
	$result = $pdo->executeQuery($data, $sql);

	$courses = ($result->rowCount() > 0)? $result->fetchAll() : array();
	$courses = array_reduce($courses, function($group, $item) {
		$group['--'.$item['department'].'--'][$item['pupilsightCourseID']] = $item['name'];
		return $group;
	}, array());

	$row = $form->addRow();
		$row->addLabel('pupilsightCourseID', __('Course'));
		$row->addSelect('pupilsightCourseID')->fromArray($courses)->required()->selected($pupilsightCourseID)->placeholder();

	$row = $form->addRow();
		$row->addSearchSubmit($pupilsight->session, __('Clear Filters'));

	echo $form->getOutput();

    if ($pupilsightCourseID != '') {
        echo '<h2>';
        echo __('Outcomes');
        echo '</h2>';

        //Check course exists
        try {
            $data = array('pupilsightSchoolYearID' => $_SESSION[$guid]['pupilsightSchoolYearID'], 'pupilsightCourseID' => $pupilsightCourseID);
            $sql = "SELECT pupilsightCourse.*, pupilsightDepartment.name AS department FROM pupilsightCourse LEFT JOIN pupilsightDepartment ON (pupilsightCourse.pupilsightDepartmentID=pupilsightDepartment.pupilsightDepartmentID) WHERE pupilsightCourse.pupilsightSchoolYearID=:pupilsightSchoolYearID AND NOT pupilsightYearGroupIDList='' AND pupilsightCourseID=:pupilsightCourseID ORDER BY department, nameShort";
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
            //Get classes in this course
            try {
                $dataClasses = array('pupilsightCourseID' => $pupilsightCourseID);
                $sqlClasses = 'SELECT * FROM pupilsightCourseClass WHERE pupilsightCourseID=:pupilsightCourseID ORDER BY name';
                $resultClasses = $connection2->prepare($sqlClasses);
                $resultClasses->execute($dataClasses);
            } catch (PDOException $e) {
                echo "<div class='alert alert-danger'>".$e->getMessage().'</div>';
            }

            if ($resultClasses->rowCount() < 1) {
                echo "<div class='alert alert-danger'>";
                echo __('The selected record does not exist, or you do not have access to it.');
                echo '</div>';
            } else {
                $classCount = $resultClasses->rowCount();
                $classes = $resultClasses->fetchAll();

                //GET ALL OUTCOMES MET IN THIS COURSE, AND STORE IN AN ARRAY FOR DB-EFFICIENT USE IN TABLE
                try {
                    $dataOutcomes = array('pupilsightCourseID1' => $pupilsightCourseID, 'pupilsightCourseID2' => $pupilsightCourseID);
                    $sqlOutcomes = "(SELECT 'Unit' AS type, pupilsightCourseClass.pupilsightCourseClassID, pupilsightOutcome.* FROM pupilsightOutcome JOIN pupilsightUnitOutcome ON (pupilsightUnitOutcome.pupilsightOutcomeID=pupilsightOutcome.pupilsightOutcomeID) JOIN pupilsightUnit ON (pupilsightUnitOutcome.pupilsightUnitID=pupilsightUnit.pupilsightUnitID) JOIN pupilsightUnitClass ON (pupilsightUnitClass.pupilsightUnitID=pupilsightUnit.pupilsightUnitID) JOIN pupilsightCourseClass ON (pupilsightUnitClass.pupilsightCourseClassID=pupilsightCourseClass.pupilsightCourseClassID) WHERE pupilsightCourseClass.pupilsightCourseID=:pupilsightCourseID1 AND pupilsightOutcome.active='Y' AND running='Y')
					UNION ALL
					(SELECT 'Planner Entry' AS type, pupilsightCourseClass.pupilsightCourseClassID, pupilsightOutcome.* FROM pupilsightOutcome JOIN pupilsightPlannerEntryOutcome ON (pupilsightPlannerEntryOutcome.pupilsightOutcomeID=pupilsightOutcome.pupilsightOutcomeID) JOIN pupilsightPlannerEntry ON (pupilsightPlannerEntryOutcome.pupilsightPlannerEntryID=pupilsightPlannerEntry.pupilsightPlannerEntryID) JOIN pupilsightCourseClass ON (pupilsightPlannerEntry.pupilsightCourseClassID=pupilsightCourseClass.pupilsightCourseClassID) WHERE pupilsightCourseClass.pupilsightCourseID=:pupilsightCourseID2 AND pupilsightOutcome.active='Y')";
                    $resultOutcomes = $connection2->prepare($sqlOutcomes);
                    $resultOutcomes->execute($dataOutcomes);
                } catch (PDOException $e) {
                    echo "<div class='alert alert-danger'>".$e->getMessage().'</div>';
                }
                $allOutcomes = $resultOutcomes->fetchAll();

                echo "<table class='table'>";
                echo "<tr class='head'>";
                echo '<th>';
                echo __('Category');
                echo '</th>';
                echo '<th>';
                echo __('Outcome');
                echo '</th>';
                foreach ($classes as $class) {
                    echo '<th colspan=2>';
                    echo $row['nameShort'].'.'.__($class['nameShort']);
                    echo '</th>';
                }
                echo '</tr>';
                echo "<tr class='head'>";
                echo '<th>';

                echo '</th>';
                echo '<th>';

                echo '</th>';
                foreach ($classes as $class) {
                    echo '<th>';
                    echo "<span style='font-style: italic; font-size: 85%'>".__('Unit').'</span>';
                    echo '</th>';
                    echo '<th>';
                    echo "<span style='font-style: italic; font-size: 85%'>".__('Lesson').'</span>';
                    echo '</th>';
                }
                echo '</tr>';

                    //Prep where for year group matching of outcomes to course
					$where = '';
					$yearGroups = explode(',', $row['pupilsightYearGroupIDList']);
					foreach ($yearGroups as $yearGroup) {
						$where .= " AND pupilsightYearGroupIDList LIKE concat('%', $yearGroup, '%')";
					}

				//SCHOOL OUTCOMES
				echo "<tr class='break'>";
                echo '<td colspan='.(($classCount * 2) + 2).'>';
                echo '<h4>'.__('School Outcomes').'</h4>';
                echo '</td>';
                echo '</tr>';
                try {
                    $dataOutcomes = array();
                    $sqlOutcomes = "SELECT * FROM pupilsightOutcome WHERE scope='School' AND active='Y' $where ORDER BY category, name";
                    $resultOutcomes = $connection2->prepare($sqlOutcomes);
                    $resultOutcomes->execute($dataOutcomes);
                } catch (PDOException $e) {
                    echo '<tr>';
                    echo '<td colspan='.(($classCount * 2) + 2).'>';
                    echo "<div class='alert alert-danger'>".$e->getMessage().'</div>';
                    echo '</td>';
                    echo '</tr>';
                }

                if ($resultOutcomes->rowCount() < 1) {
                    echo '<tr>';
                    echo '<td colspan='.(($classCount * 2) + 2).'>';
                    echo "<div class='alert alert-danger'>".__('There are no records to display.').'</div>';
                    echo '</td>';
                    echo '</tr>';
                } else {
                    $count = 0;
                    $rowNum = 'odd';
                    while ($rowOutcomes = $resultOutcomes->fetch()) {
                        if ($count % 2 == 0) {
                            $rowNum = 'even';
                        } else {
                            $rowNum = 'odd';
                        }
                        ++$count;

                        //COLOR ROW BY STATUS!
                        echo "<tr class=$rowNum>";
                        echo '<td>';
                        echo $rowOutcomes['category'];
                        echo '</td>';
                        echo '<td>';
                        echo $rowOutcomes['name'];
                        echo '</td>';

						//Deal with outcomes
						foreach ($classes as $class) {
							echo '<td>';
							$outcomeCount = 0;
							foreach ($allOutcomes as $anOutcome) {
								if ($anOutcome['type'] == 'Unit' and $anOutcome['scope'] == 'School' and $anOutcome['pupilsightOutcomeID'] == $rowOutcomes['pupilsightOutcomeID'] and $class['pupilsightCourseClassID'] == $anOutcome['pupilsightCourseClassID']) {
									++$outcomeCount;
								}
							}
							if ($outcomeCount < 1) {
								echo "<img src='./themes/".$_SESSION[$guid]['pupilsightThemeName']."/img/iconCross.png'/> ";
							} else {
								echo "<img src='./themes/".$_SESSION[$guid]['pupilsightThemeName']."/img/iconTick.png'/>&nbsp;x&nbsp;".$outcomeCount;
							}
							echo '</td>';
							echo '<td>';
							$outcomeCount = 0;
							foreach ($allOutcomes as $anOutcome) {
								if ($anOutcome['type'] != 'Unit' and $anOutcome['scope'] == 'School' and $anOutcome['pupilsightOutcomeID'] == $rowOutcomes['pupilsightOutcomeID'] and $class['pupilsightCourseClassID'] == $anOutcome['pupilsightCourseClassID']) {
									++$outcomeCount;
								}
							}
							if ($outcomeCount < 1) {
								echo "<img src='./themes/".$_SESSION[$guid]['pupilsightThemeName']."/img/iconCross.png'/> ";
							} else {
								echo "<img src='./themes/".$_SESSION[$guid]['pupilsightThemeName']."/img/iconTick.png'/>&nbsp;x&nbsp;".$outcomeCount;
							}
							echo '</td>';
						}
                        echo '</tr>';
                    }
                }

                    //LEARNING AREA OUTCOMES
                    echo "<tr class='break'>";
					echo '<td colspan='.(($classCount * 2) + 2).'>';
					echo '<h4>'.sprintf(__('%1$s Outcomes'), $row['department']).'</h4>';
					echo '</td>';
					echo '</tr>';
					try {
						$dataOutcomes = array('pupilsightDepartmentID' => $row['pupilsightDepartmentID']);
						$sqlOutcomes = "SELECT * FROM pupilsightOutcome WHERE scope='Learning Area' AND pupilsightDepartmentID=:pupilsightDepartmentID AND active='Y' $where ORDER BY category, name";
						$resultOutcomes = $connection2->prepare($sqlOutcomes);
						$resultOutcomes->execute($dataOutcomes);
					} catch (PDOException $e) {
						echo '<tr>';
						echo '<td colspan='.(($classCount * 2) + 2).'>';
						echo "<div class='alert alert-danger'>".$e->getMessage().'</div>';
						echo '</td>';
						echo '</tr>';
					}

					if ($resultOutcomes->rowCount() < 1) {
						echo '<tr>';
						echo '<td colspan='.(($classCount * 2) + 2).'>';
						echo "<div class='alert alert-danger'>".__('There are no records to display.').'</div>';
						echo '</td>';
						echo '</tr>';
					} else {
						$count = 0;
						$rowNum = 'odd';
						while ($rowOutcomes = $resultOutcomes->fetch()) {
							if ($count % 2 == 0) {
								$rowNum = 'even';
							} else {
								$rowNum = 'odd';
							}
							++$count;

							//COLOR ROW BY STATUS!
							echo "<tr class=$rowNum>";
							echo '<td>';
							echo $rowOutcomes['category'];
							echo '</td>';
							echo '<td>';
							echo $rowOutcomes['name'];
							echo '</td>';

							//Deal with outcomes
							foreach ($classes as $class) {
								echo '<td>';
								$outcomeCount = 0;
								foreach ($allOutcomes as $anOutcome) {
									if ($anOutcome['type'] == 'Unit' and $anOutcome['scope'] == 'Learning Area' and $anOutcome['pupilsightOutcomeID'] == $rowOutcomes['pupilsightOutcomeID'] and $class['pupilsightCourseClassID'] == $anOutcome['pupilsightCourseClassID']) {
										++$outcomeCount;
									}
								}
								if ($outcomeCount < 1) {
									echo "<img src='./themes/".$_SESSION[$guid]['pupilsightThemeName']."/img/iconCross.png'/> ";
								} else {
									echo "<img src='./themes/".$_SESSION[$guid]['pupilsightThemeName']."/img/iconTick.png'/>&nbsp;x&nbsp;".$outcomeCount;
								}
								echo '</td>';
								echo '<td>';
								$outcomeCount = 0;
								foreach ($allOutcomes as $anOutcome) {
									if ($anOutcome['type'] != 'Unit' and $anOutcome['scope'] == 'Learning Area' and $anOutcome['pupilsightOutcomeID'] == $rowOutcomes['pupilsightOutcomeID'] and $class['pupilsightCourseClassID'] == $anOutcome['pupilsightCourseClassID']) {
										++$outcomeCount;
									}
								}
								if ($outcomeCount < 1) {
									echo "<img src='./themes/".$_SESSION[$guid]['pupilsightThemeName']."/img/iconCross.png'/> ";
								} else {
									echo "<img src='./themes/".$_SESSION[$guid]['pupilsightThemeName']."/img/iconTick.png'/>&nbsp;x&nbsp;".$outcomeCount;
								}
								echo '</td>';
							}

                        echo '</tr>';
                    }
                }
                echo '</table>';
            }
        }
    }
}
