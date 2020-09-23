<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Forms\Form;

//Module includes
require_once __DIR__ . '/moduleFunctions.php';

//Search & Filters
$search = null;
if (isset($_GET['search'])) {
    $search = $_GET['search'];
}
$filter2 = null;
if (isset($_GET['filter2'])) {
    $filter2 = $_GET['filter2'];
}

if (isActionAccessible($guid, $connection2, '/modules/Rubrics/rubrics_edit_editRowsColumns.php') == false) {
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
        if ($highestAction != 'Manage Rubrics_viewEditAll' and $highestAction != 'Manage Rubrics_viewAllEditLearningArea') {
            echo "<div class='alert alert-danger'>";
            echo __('You do not have access to this action.');
            echo '</div>';
        } else {
            //Proceed!
            $page->breadcrumbs
                ->add(__('Manage Rubrics'), 'rubrics.php', ['search' => $search, 'filter2' => $filter2])
                ->add(__('Edit Rubric'), 'rubrics_edit.php', ['pupilsightRubricID' => $_GET['pupilsightRubricID'], 'search' => $search, 'filter2' => $filter2, 'sidebar' => 'false'])
                ->add(__('Edit Rubric Rows & Columns'));

            if ($search != '' or $filter2 != '') {
                echo "<div class='linkTop'>";
                echo "<a href='".$_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/Rubrics/rubrics_edit.php&pupilsightRubricID='.$_GET['pupilsightRubricID']."&search=$search&filter2=$filter2&sidebar=false'>".__('Back').'</a>';
                echo '</div>';
            }

            if (isset($_GET['return'])) {
                returnProcess($guid, $_GET['return'], null, null);
            }

            //Check if school year specified
            $pupilsightRubricID = $_GET['pupilsightRubricID'];
            if ($pupilsightRubricID == '') {
                echo "<div class='alert alert-danger'>";
                echo __('You have not specified one or more required parameters.');
                echo '</div>';
            } else {
                try {
                    $data = array('pupilsightRubricID' => $pupilsightRubricID);
                    $sql = 'SELECT * FROM pupilsightRubric WHERE pupilsightRubricID=:pupilsightRubricID';
                    $result = $connection2->prepare($sql);
                    $result->execute($data);
                } catch (PDOException $e) {
                    echo "<div class='alert alert-danger'>".$e->getMessage().'</div>';
                }

                if ($result->rowCount() != 1) {
                    echo "<div class='alert alert-danger'>";
                    echo __('The specified record does not exist.');
                    echo '</div>';
                } else {
                    //Let's go!
					$values = $result->fetch(); 
					
					$form = Form::create('addRubric', $_SESSION[$guid]['absoluteURL'].'/modules/'.$_SESSION[$guid]['module'].'/rubrics_edit_editRowsColumnsProcess.php?pupilsightRubricID='.$pupilsightRubricID.'&search='.$search.'&filter2='.$filter2);

                    $form->addHiddenValue('address', $_SESSION[$guid]['address']);
                    
                    $form->addRow()->addHeading(__('Rubric Basics'));

                    $row = $form->addRow();
                        $row->addLabel('scope', 'Scope');
                        $row->addTextField('scope')->required()->readOnly();

                    if ($values['scope'] == 'Learning Area') {
                        $sql = "SELECT name FROM pupilsightDepartment WHERE pupilsightDepartmentID=:pupilsightDepartmentID";
                        $result = $pdo->executeQuery(array('pupilsightDepartmentID' => $values['pupilsightDepartmentID']), $sql);
                        $learningArea = ($result->rowCount() > 0)? $result->fetchColumn(0) : $values['pupilsightDepartmentID'];

                        $form->addHiddenValue('pupilsightDepartmentID', $values['pupilsightDepartmentID']);
                        $row = $form->addRow();
                            $row->addLabel('departmentName', __('Learning Area'));
                            $row->addTextField('departmentName')->required()->readOnly()->setValue($learningArea);
					}

					$row = $form->addRow();
                        $row->addLabel('name', __('Name'));
						$row->addTextField('name')->maxLength(50)->required()->readOnly();
						
					$form->addRow()->addHeading(__('Rows'));

					// Get outcomes by year group
					$data = array('pupilsightYearGroupIDList' => $values['pupilsightYearGroupIDList']);
					$sql = "SELECT pupilsightOutcome.pupilsightOutcomeID, pupilsightOutcome.scope, pupilsightOutcome.category, pupilsightOutcome.name 
							FROM pupilsightOutcome 
							LEFT JOIN pupilsightYearGroup ON (FIND_IN_SET(pupilsightYearGroup.pupilsightYearGroupID, pupilsightOutcome.pupilsightYearGroupIDList))
							WHERE pupilsightOutcome.active='Y' 
							AND FIND_IN_SET(pupilsightYearGroup.pupilsightYearGroupID, :pupilsightYearGroupIDList)
							GROUP BY pupilsightOutcome.pupilsightOutcomeID
							ORDER BY pupilsightOutcome.category, pupilsightOutcome.name";
					$result = $pdo->executeQuery($data, $sql);
					
					// Build a set of outcomes grouped by scope
					$outcomes = ($result->rowCount() > 0)? $result->fetchAll() : array();
					$outcomes = array_reduce($outcomes, function($group, $item) {
						$name = !empty($item['category'])? $item['category'].' - '.$item['name'] : $item['name'];
 						$group[$item['scope'].' '.__('Outcomes')][$item['pupilsightOutcomeID']] = $name;
						return $group;
					}, array());

					$typeOptions = array('Standalone' => __('Standalone'), 'Outcome Based' => __('Outcome Based'));
					
					$data = array('pupilsightRubricID' => $pupilsightRubricID);
					$sql = "SELECT pupilsightRubricRowID, title, pupilsightOutcomeID FROM pupilsightRubricRow WHERE pupilsightRubricID=:pupilsightRubricID ORDER BY sequenceNumber";
                    $result = $pdo->executeQuery($data, $sql);
					
					if ($result->rowCount() <= 0) {
						$form->addRow()->addAlert(__('There are no records to display.'), 'error');
					} else {
						$count = 0;
						while ($rubricRow = $result->fetch()) {
							$type = ($rubricRow['pupilsightOutcomeID'] != '')? 'Outcome Based' : 'Standalone';

							$row = $form->addRow();
								$row->addLabel('rowName'.$count, sprintf(__('Row %1$s Title'), ($count + 1)) );
								$column = $row->addColumn()->addClass('right');
								$column->addRadio('type'.$count)->fromArray($typeOptions)->inline()->checked($type);
								$column->addTextField('rowTitle['.$count.']')
									->setID('rowTitle'.$count)
									->addClass('rowTitle'.$count)
									->maxLength(40)
									->required()
									->setValue($rubricRow['title']);
								$column->addSelect('pupilsightOutcomeID['.$count.']')
									->setID('pupilsightOutcomeID'.$count)
									->addClass('pupilsightOutcomeID'.$count)
									->fromArray($outcomes)
									->required()
									->placeholder()
									->selected($rubricRow['pupilsightOutcomeID']);

							$form->toggleVisibilityByClass('rowTitle'.$count)->onRadio('type'.$count)->when('Standalone');
							$form->toggleVisibilityByClass('pupilsightOutcomeID'.$count)->onRadio('type'.$count)->when('Outcome Based');
							$form->addHiddenValue('pupilsightRubricRowID['.$count.']', $rubricRow['pupilsightRubricRowID']);
								
							$count++;
						}
					}

                    $row = $form->addRow();
                        $row->addHeading(__('Columns'));
                        $row->addContent(__('Visualise?'))->setClass('textCenter')->wrap('<strong>', '</strong>');
                        $row->addContent();

					$data = array('pupilsightRubricID' => $pupilsightRubricID);
					$sql = "SELECT pupilsightRubricColumnID, title, pupilsightScaleGradeID, visualise FROM pupilsightRubricColumn WHERE pupilsightRubricID=:pupilsightRubricID ORDER BY sequenceNumber";
                    $result = $pdo->executeQuery($data, $sql);
					
					if ($result->rowCount() <= 0) {
						$form->addRow()->addAlert(__('There are no records to display.'), 'error');
					} else {
						$count = 0;
						while ($rubricColumn = $result->fetch()) {
							$row = $form->addRow();
                            $row->addLabel('columnName'.$count, sprintf(__('Column %1$s Title'), ($count + 1)));
                            
                            $row->addCheckbox('columnVisualise['.$count.']')
                                ->setValue('Y')
                                ->checked($rubricColumn['visualise'])
                                ->setClass('textCenter');

							// Handle non-grade scale columns as a text field, otherwise a dropdown
							if ($values['pupilsightScaleID'] == '') {
								$row->addTextField('columnTitle['.$count.']')
									->setID('columnTitle'.$count)
									->maxLength(20)
									->required()
									->setValue($rubricColumn['title']);
							} else {
								$data = array('pupilsightScaleID' => $values['pupilsightScaleID']);
								$sql = "SELECT pupilsightScaleGradeID as value, CONCAT(value, ' - ', descriptor) as name FROM pupilsightScaleGrade WHERE pupilsightScaleID=:pupilsightScaleID AND NOT value='Incomplete' ORDER BY sequenceNumber";
								$row->addSelect('pupilsightScaleGradeID['.$count.']')
									->setID('pupilsightScaleGradeID'.$count)
									->fromQuery($pdo, $sql, $data)
									->required()
									->selected($rubricColumn['pupilsightScaleGradeID']);
							}
							$form->addHiddenValue('pupilsightRubricColumnID['.$count.']', $rubricColumn['pupilsightRubricColumnID']);

							$count++;
						}
					}

					$row = $form->addRow();
                        $row->addFooter();
                        $row->addSubmit();

                    $form->loadAllValuesFrom($values);
                    
					echo $form->getOutput();
                }
            }
        }
    }
}
