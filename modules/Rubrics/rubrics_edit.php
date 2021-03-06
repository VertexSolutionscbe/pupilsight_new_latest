<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Forms\Form;
use Pupilsight\Forms\DatabaseFormFactory;

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

if (isActionAccessible($guid, $connection2, '/modules/Rubrics/rubrics_edit.php') == false) {
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
                ->add(__('Edit Rubric'));

            if (isset($_GET['return'])) {
                returnProcess($guid, $_GET['return'], null, null);
            }

            if (isset($_GET['addReturn'])) {
                $addReturn = $_GET['addReturn'];
            } else {
                $addReturn = '';
            }
            $addReturnMessage = '';
            $class = 'error';
            if (!($addReturn == '')) {
                if ($addReturn == 'success0') {
                    $addReturnMessage = __('Your request was completed successfully.');
                    $class = 'success';
                }
                echo "<div class='$class'>";
                echo $addReturnMessage;
                echo '</div>';
            }

            if (isset($_GET['columnDeleteReturn'])) {
                $columnDeleteReturn = $_GET['columnDeleteReturn'];
            } else {
                $columnDeleteReturn = '';
            }
            $columnDeleteReturnMessage = '';
            $class = 'error';
            if (!($columnDeleteReturn == '')) {
                if ($columnDeleteReturn == 'fail0') {
                    $columnDeleteReturnMessage = __('Your request failed because you do not have access to this action.');
                } elseif ($columnDeleteReturn == 'fail1') {
                    $columnDeleteReturnMessage = __('Your request failed because your inputs were invalid.');
                } elseif ($columnDeleteReturn == 'fail2') {
                    $columnDeleteReturnMessage = __('Your request failed due to a database error.');
                } elseif ($columnDeleteReturn == 'fail3') {
                    $columnDeleteReturnMessage = __('Your request failed because your inputs were invalid.');
                } elseif ($columnDeleteReturn == 'success0') {
                    $columnDeleteReturnMessage = __('Your request was completed successfully.');
                    $class = 'success';
                }
                echo "<div class='$class'>";
                echo $columnDeleteReturnMessage;
                echo '</div>';
            }

            if (isset($_GET['rowDeleteReturn'])) {
                $rowDeleteReturn = $_GET['rowDeleteReturn'];
            } else {
                $rowDeleteReturn = '';
            }
            $rowDeleteReturnMessage = '';
            $class = 'error';
            if (!($rowDeleteReturn == '')) {
                if ($rowDeleteReturn == 'fail0') {
                    $rowDeleteReturnMessage = __('Your request failed because you do not have access to this action.');
                } elseif ($rowDeleteReturn == 'fail1') {
                    $rowDeleteReturnMessage = __('Your request failed because your inputs were invalid.');
                } elseif ($rowDeleteReturn == 'fail2') {
                    $rowDeleteReturnMessage = __('Your request failed due to a database error.');
                } elseif ($rowDeleteReturn == 'fail3') {
                    $rowDeleteReturnMessage = __('Your request failed because your inputs were invalid.');
                } elseif ($rowDeleteReturn == 'success0') {
                    $rowDeleteReturnMessage = __('Your request was completed successfully.');
                    $class = 'success';
                }
                echo "<div class='$class'>";
                echo $rowDeleteReturnMessage;
                echo '</div>';
            }

            if (isset($_GET['cellEditReturn'])) {
                $cellEditReturn = $_GET['cellEditReturn'];
            } else {
                $cellEditReturn = '';
            }
            $cellEditReturnMessage = '';
            $class = 'error';
            if (!($cellEditReturn == '')) {
                if ($cellEditReturn == 'fail0') {
                    $cellEditReturnMessage = __('Your request failed because you do not have access to this action.');
                } elseif ($cellEditReturn == 'fail1') {
                    $cellEditReturnMessage = __('Your request failed because your inputs were invalid.');
                } elseif ($cellEditReturn == 'fail2') {
                    $cellEditReturnMessage = __('Your request failed due to a database error.');
                } elseif ($cellEditReturn == 'fail3') {
                    $cellEditReturnMessage = __('Your request failed because your inputs were invalid.');
                } elseif ($cellEditReturn == 'fail5') {
                    $cellEditReturnMessage = __('Your request was successful, but some data was not properly saved.');
                } elseif ($cellEditReturn == 'success0') {
                    $cellEditReturnMessage = __('Your request was completed successfully.');
                    $class = 'success';
                }
                echo "<div class='$class'>";
                echo $cellEditReturnMessage;
                echo '</div>';
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

                    if ($search != '' or $filter2 != '') {
                        echo "<div class='linkTop'>";
                        echo "<a href='".$_SESSION[$guid]['absoluteURL']."/index.php?q=/modules/Rubrics/rubrics.php&search=$search&filter2=$filter2'>".__('Back to Search Results').'</a>';
                        echo '</div>';
                    }

                    $form = Form::create('addRubric', $_SESSION[$guid]['absoluteURL'].'/modules/'.$_SESSION[$guid]['module'].'/rubrics_editProcess.php?pupilsightRubricID='.$pupilsightRubricID.'&search='.$search.'&filter2='.$filter2);
                    $form->setFactory(DatabaseFormFactory::create($pdo));

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
                        $row->addTextField('name')->maxLength(50)->required();

                    $row = $form->addRow();
                        $row->addLabel('active', __('Active'));
                        $row->addYesNo('active')->required();

                    $sql = "SELECT DISTINCT category FROM pupilsightRubric ORDER BY category";
                    $result = $pdo->executeQuery(array(), $sql);
                    $categories = ($result->rowCount() > 0)? $result->fetchAll(\PDO::FETCH_COLUMN, 0) : array();

                    $row = $form->addRow();
                        $row->addLabel('category', __('Category'));
                        $row->addTextField('category')->maxLength(100)->autocomplete($categories);

                    $row = $form->addRow();
                        $row->addLabel('description', __('Description'));
                        $row->addTextArea('description')->setRows(5);

                    $row = $form->addRow();
                        $row->addLabel('pupilsightYearGroupIDList[]', __('Year Groups'));
                        $row->addCheckboxYearGroup('pupilsightYearGroupIDList[]')->addCheckAllNone()->loadFromCSV($values);

                    $sql = "SELECT name FROM pupilsightScale WHERE pupilsightScaleID=:pupilsightScaleID";
                    $result = $pdo->executeQuery(array('pupilsightScaleID' => $values['pupilsightScaleID']), $sql);
                    $gradeScaleName = ($result->rowCount() > 0)? $result->fetchColumn(0) : $values['pupilsightScaleID'];

                    $form->addHiddenValue('pupilsightScaleID', $values['pupilsightScaleID']);
                    $row = $form->addRow();
                        $row->addLabel('gradeScale', __('Grade Scale'))->description(__('Link columns to grades on a scale?'));
                        $row->addTextField('gradeScale')->readOnly()->setValue($gradeScaleName);

                    $row = $form->addRow();
                        $row->addFooter();
                        $row->addSubmit()->addClass('submit_align submt');

                    $form->loadAllValuesFrom($values);
                    
                    echo $form->getOutput();

					echo '<a name="rubricDesign"></a>';
					echo '<table class="smallIntBorder" cellspacing="0" style="width:100%">';
						echo '<tr class="break">';
							echo '<td colspan=2>';
								echo '<h3>'. __('Rubric Design') .'</h3>';
							echo '</td>';
						echo '</tr>';
					echo '</table>';

                    echo rubricEdit($guid, $connection2, $pupilsightRubricID, $gradeScaleName, $search, $filter2);
                }
            }
        }
    }
}
