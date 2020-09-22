<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Forms\Form;
use Pupilsight\Forms\DatabaseFormFactory;

//Module includes
require_once __DIR__ . '/moduleFunctions.php';

$page->breadcrumbs
    ->add(__('Manage Outcomes'), 'outcomes.php')
    ->add(__('Edit Outcome'));

if (isActionAccessible($guid, $connection2, '/modules/Planner/outcomes_edit.php') == false) {
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
        if ($highestAction != 'Manage Outcomes_viewEditAll' and $highestAction != 'Manage Outcomes_viewAllEditLearningArea') {
            echo "<div class='alert alert-danger'>";
            echo __('You do not have access to this action.');
            echo '</div>';
        } else {
            //Proceed!
            if (isset($_GET['return'])) {
                returnProcess($guid, $_GET['return'], null, null);
            }

            $filter2 = '';
            if (isset($_GET['filter2'])) {
                $filter2 = $_GET['filter2'];
            }

            if ($filter2 != '') {
                echo "<div class='linkTop'>";
                echo "<a href='".$_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/Planner/outcomes.php&filter2='.$filter2."'>".__('Back to Search Results').'</a>';
                echo '</div>';
            }

            //Check if school year specified
            $pupilsightOutcomeID = $_GET['pupilsightOutcomeID'];
            if ($pupilsightOutcomeID == '') {
                echo "<div class='alert alert-danger'>";
                echo __('You have not specified one or more required parameters.');
                echo '</div>';
            } else {
                try {
                    if ($highestAction == 'Manage Outcomes_viewEditAll') {
                        $data = array('pupilsightOutcomeID' => $pupilsightOutcomeID);
                        $sql = 'SELECT * FROM pupilsightOutcome WHERE pupilsightOutcomeID=:pupilsightOutcomeID';
                    } elseif ($highestAction == 'Manage Outcomes_viewAllEditLearningArea') {
                        $data = array('pupilsightOutcomeID' => $pupilsightOutcomeID, 'pupilsightPersonID' => $_SESSION[$guid]['pupilsightPersonID']);
                        $sql = "SELECT pupilsightOutcome.* FROM pupilsightOutcome JOIN pupilsightDepartment ON (pupilsightOutcome.pupilsightDepartmentID=pupilsightDepartment.pupilsightDepartmentID) JOIN pupilsightDepartmentStaff ON (pupilsightDepartmentStaff.pupilsightDepartmentID=pupilsightDepartment.pupilsightDepartmentID) AND NOT pupilsightOutcome.pupilsightDepartmentID IS NULL WHERE pupilsightOutcomeID=:pupilsightOutcomeID AND (role='Coordinator' OR role='Teacher (Curriculum)') AND pupilsightPersonID=:pupilsightPersonID AND scope='Learning Area'";
                    }
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
					
					$form = Form::create('outcomes', $_SESSION[$guid]['absoluteURL'].'/modules/'.$_SESSION[$guid]['module'].'/outcomes_editProcess.php?pupilsightOutcomeID='.$pupilsightOutcomeID.'&filter2='.$filter2);
					$form->setFactory(DatabaseFormFactory::create($pdo));
					
					$form->addHiddenValue('address', $_SESSION[$guid]['address']);

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
						$row->addTextField('name')->required()->maxLength(100);

					$row = $form->addRow();
						$row->addLabel('nameShort', __('Short Name'));
						$row->addTextField('nameShort')->required()->maxLength(14);

					$row = $form->addRow();
						$row->addLabel('active', __('Active'));
						$row->addYesNo('active')->required();

					$sql = "SELECT DISTINCT category FROM pupilsightOutcome ORDER BY category";
					$result = $pdo->executeQuery(array(), $sql);
					$categories = ($result->rowCount() > 0)? $result->fetchAll(\PDO::FETCH_COLUMN, 0) : array();

					$row = $form->addRow();
						$row->addLabel('category', __('Category'));
						$row->addTextField('category')->maxLength(100)->autocomplete($categories);
						
					$row = $form->addRow();
						$row->addLabel('description', __('Description'));
						$row->addTextArea('description')->setRows(5);

					$row = $form->addRow();
						$row->addLabel('pupilsightYearGroupIDList', __('Year Groups'))->description(__('Relevant student year groups'));
						$row->addCheckboxYearGroup('pupilsightYearGroupIDList')->addCheckAllNone()->loadFromCSV($values);

					$row = $form->addRow();
						$row->addSubmit();

					$form->loadAllValuesFrom($values);

					echo $form->getOutput();

                }
            }
        }
    }
}
