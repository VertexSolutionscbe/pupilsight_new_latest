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

if (isActionAccessible($guid, $connection2, '/modules/Rubrics/rubrics_duplicate.php') == false) {
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
                ->add(__('Duplicate Rubric'));

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

                    if ($search != '' or $filter2 != '') {
                        echo "<div class='linkTop'>";
                        echo "<a href='".$_SESSION[$guid]['absoluteURL']."/index.php?q=/modules/Rubrics/rubrics.php&search=$search&filter2=$filter2'>".__('Back to Search Results').'</a>';
                        echo '</div>';
					}
					
					$scopes = array(
						'School' => __('School'),
						'Learning Area' => __('Learning Area'),
					);

					$form = Form::create('addRubric', $_SESSION[$guid]['absoluteURL'].'/modules/'.$_SESSION[$guid]['module'].'/rubrics_duplicateProcess.php?pupilsightRubricID='.$pupilsightRubricID.'&search='.$search.'&filter2='.$filter2);

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
						$row->addFooter();
						$row->addSubmit()->addClass('submit_align submt');

					$form->loadAllValuesFrom($values);
					
					echo $form->getOutput();
                }
            }
        }
    }
}

