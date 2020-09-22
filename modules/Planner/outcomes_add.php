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
    ->add(__('Add Outcome'));

if (isActionAccessible($guid, $connection2, '/modules/Planner/outcomes_add.php') == false) {
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
            $editLink = '';
            if (isset($_GET['editID'])) {
                $editLink = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/Planner/outcomes_edit.php&pupilsightOutcomeID='.$_GET['editID'].'&filter2='.$_GET['filter2'];
            }
            if (isset($_GET['return'])) {
                returnProcess($guid, $_GET['return'], $editLink, null);
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

			$scopes = array(
                'School' => __('School'),
                'Learning Area' => __('Learning Area'),
            );

			$form = Form::create('outcomes', $_SESSION[$guid]['absoluteURL'].'/modules/'.$_SESSION[$guid]['module'].'/outcomes_addProcess.php?filter2='.$filter2);
			$form->setFactory(DatabaseFormFactory::create($pdo));

			$form->addHiddenValue('address', $_SESSION[$guid]['address']);

			$row = $form->addRow();
                $row->addLabel('scope', 'Scope');
            if ($highestAction == 'Manage Outcomes_viewEditAll') {
                $row->addSelect('scope')->fromArray($scopes)->required()->placeholder();
            } elseif ($highestAction == 'Manage Outcomes_viewAllEditLearningArea') {
                $row->addTextField('scope')->readOnly()->setValue('Learning Area');
			}

			if ($highestAction == 'Manage Outcomes_viewEditAll') {
				$data = array();
				$sql = "SELECT pupilsightDepartmentID as value, name FROM pupilsightDepartment WHERE type='Learning Area' ORDER BY name";
			} elseif ($highestAction == 'Manage Outcomes_viewAllEditLearningArea') {
				$data = array('pupilsightPersonID' => $_SESSION[$guid]['pupilsightPersonID']);
				$sql = "SELECT pupilsightDepartment.pupilsightDepartmentID as value, pupilsightDepartment.name FROM pupilsightDepartment JOIN pupilsightDepartmentStaff ON (pupilsightDepartmentStaff.pupilsightDepartmentID=pupilsightDepartment.pupilsightDepartmentID) WHERE pupilsightPersonID=:pupilsightPersonID AND (role='Coordinator' OR role='Teacher (Curriculum)') AND type='Learning Area' ORDER BY name";
			}


            if ($highestAction == 'Manage Outcomes_viewEditAll') {
                $form->toggleVisibilityByClass('learningAreaRow')->onSelect('scope')->when('Learning Area');
            }
            $row = $form->addRow()->addClass('learningAreaRow');
                $row->addLabel('pupilsightDepartmentID', __('Learning Area'));
                $row->addSelect('pupilsightDepartmentID')->fromQuery($pdo, $sql, $data)->required()->placeholder();

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
				$row->addCheckboxYearGroup('pupilsightYearGroupIDList')->addCheckAllNone();

			$row = $form->addRow();
				$row->addSubmit();

			echo $form->getOutput();
        }
    }
}
