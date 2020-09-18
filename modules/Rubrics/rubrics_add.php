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

if (isActionAccessible($guid, $connection2, '/modules/Rubrics/rubrics_add.php') == false) {
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
                ->add(__('Add Rubric'));

            if ($search != '' or $filter2 != '') {
                echo "<div class='linkTop'>";
                echo "<a href='".$_SESSION[$guid]['absoluteURL']."/index.php?q=/modules/Rubrics/rubrics.php&search=$search&filter2=$filter2'>".__('Back to Search Results').'</a>';
                echo '</div>';
            }

            if (isset($_GET['return'])) {
                returnProcess($guid, $_GET['return'], null, null);
            }
            
            $scopes = array(
                'School' => __('School'),
                'Learning Area' => __('Learning Area'),
            );

            $form = Form::create('addRubric', $_SESSION[$guid]['absoluteURL'].'/modules/'.$_SESSION[$guid]['module'].'/rubrics_addProcess.php?search='.$search.'&filter2='.$filter2);
            $form->setFactory(DatabaseFormFactory::create($pdo));

            $form->addHiddenValue('address', $_SESSION[$guid]['address']);
            
            $form->addRow()->addHeading(__('Rubric Basics'));

            $row = $form->addRow();
                $row->addLabel('scope', 'Scope');
            if ($highestAction == 'Manage Rubrics_viewEditAll') {
                $row->addSelect('scope')->fromArray($scopes)->required()->placeholder();
                $form->toggleVisibilityByClass('learningAreaRow')->onSelect('scope')->when('Learning Area');
            } else if ($highestAction == 'Manage Rubrics_viewAllEditLearningArea') {
                $row->addTextField('scope')->readOnly()->setValue('Learning Area');
            }

            if ($highestAction == 'Manage Rubrics_viewEditAll') {
                $data = array();
                $sql = "SELECT pupilsightDepartmentID as value, name FROM pupilsightDepartment WHERE type='Learning Area' ORDER BY name";
            } else if ($highestAction == 'Manage Rubrics_viewAllEditLearningArea') {
                $data = array('pupilsightPersonID' => $_SESSION[$guid]['pupilsightPersonID']);
                $sql = "SELECT pupilsightDepartment.pupilsightDepartmentID as value, pupilsightDepartment.name FROM pupilsightDepartment JOIN pupilsightDepartmentStaff ON (pupilsightDepartmentStaff.pupilsightDepartmentID=pupilsightDepartment.pupilsightDepartmentID) WHERE pupilsightPersonID=:pupilsightPersonID AND (role='Coordinator' OR role='Teacher (Curriculum)') AND type='Learning Area' ORDER BY name";
            }

            $row = $form->addRow()->addClass('learningAreaRow');
                $row->addLabel('pupilsightDepartmentID', __('Learning Area'));
                $row->addSelect('pupilsightDepartmentID')->fromQuery($pdo, $sql, $data)->required()->placeholder();

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
                $row->addCheckboxYearGroup('pupilsightYearGroupIDList[]')->addCheckAllNone()->checkAll();

            $sql = "SELECT pupilsightScaleID as value, name FROM pupilsightScale WHERE (active='Y') ORDER BY name";
            $row = $form->addRow();
                $row->addLabel('pupilsightScaleID', __('Grade Scale'))->description(__('Link columns to grades on a scale?'));
                $row->addSelect('pupilsightScaleID')->fromQuery($pdo, $sql)->placeholder();

            $form->addRow()->addHeading(__('Rubric Design'));

            $row = $form->addRow();
                $row->addLabel('rows', __('Initial Rows'))->description(__('Rows store assessment strands.'));
                $row->addSelect('rows')->fromArray(range(1, 10))->required();

            $row = $form->addRow();
                $row->addLabel('columns', __('Initial Columns'))->description(__('Columns store assessment levels.'));
                $row->addSelect('columns')->fromArray(range(1, 10))->required();
            
            $row = $form->addRow();
                $row->addFooter();
                $row->addSubmit();
            
            echo $form->getOutput();
        }
    }
}
