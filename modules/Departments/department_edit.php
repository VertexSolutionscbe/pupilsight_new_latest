<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Forms\Form;

//Module includes
require_once __DIR__ . '/moduleFunctions.php';

if (isActionAccessible($guid, $connection2, '/modules/Departments/department_edit.php') == false) {
    //Acess denied
    echo "<div class='alert alert-danger'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    //Check if courseschool year specified
    $pupilsightDepartmentID = $_GET['pupilsightDepartmentID'];
    if ($pupilsightDepartmentID == '') {
        echo "<div class='alert alert-danger'>";
        echo __('You have not specified one or more required parameters.');
        echo '</div>';
    } else {
        try {
            $data = array('pupilsightDepartmentID' => $pupilsightDepartmentID);
            $sql = 'SELECT * FROM pupilsightDepartment WHERE pupilsightDepartmentID=:pupilsightDepartmentID';
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
            $values = $result->fetch();

            $urlParams = ['pupilsightDepartmentID' => $pupilsightDepartmentID];
            
            $page->breadcrumbs
                ->add(__('View All'), 'departments.php')
                ->add($values['name'], 'department.php', $urlParams)
                ->add(__('Edit Department'));            

            if (isset($_GET['return'])) {
                returnProcess($guid, $_GET['return'], null, array('error3' => 'Your request failed due to an attachment error.'));
            }

            //Get role within learning area
            $role = getRole($_SESSION[$guid]['pupilsightPersonID'], $pupilsightDepartmentID, $connection2);

            if ($role != 'Coordinator' and $role != 'Assistant Coordinator' and $role != 'Teacher (Curriculum)' and $role != 'Director' and $role != 'Manager') {
                echo "<div class='alert alert-danger'>";
                echo __('The selected record does not exist, or you do not have access to it.');
                echo '</div>';
            } else {

				$form = Form::create('action', $_SESSION[$guid]['absoluteURL'].'/modules/'.$_SESSION[$guid]['module'].'/department_editProcess.php?pupilsightDepartmentID='.$pupilsightDepartmentID);
				
				$form->addHiddenValue('address', $_SESSION[$guid]['address']);
				
				$form->addRow()->addHeading(__('Overview'));
				$form->addRow()->addEditor('blurb', $guid)->setRows(20)->setValue($values['blurb']);
				
				$form->addRow()->addHeading(__('Current Resources'));

				$data = array('pupilsightDepartmentID' => $pupilsightDepartmentID);
				$sql = 'SELECT * FROM pupilsightDepartmentResource WHERE pupilsightDepartmentID=:pupilsightDepartmentID ORDER BY name';
				$result = $pdo->executeQuery($data, $sql);

				if ($result->rowCount() == 0) {
					$form->addRow()->addAlert(__('There are no records to display.'), 'error');
				} else {
					$table = $form->addRow()->addTable()->addClass('fullWidth colorOddEven');

					$row = $table->addHeaderRow();
						$row->addContent(__('Name'));
						$row->addContent(__('Type'));
						$row->addContent(__('Actions'))->setClass('shortWidth');

					while ($resource = $result->fetch()) {
						$href = ($resource['type'] == 'Link')? $resource['url'] : $_SESSION[$guid]['absoluteURL'].'/'.$resource['url'];

						$row = $table->addRow();
							$row->addContent($resource['name'])->wrap('<a href="'.$href.'" target="blank">', '</a>');
							$row->addContent($resource['type']);
							$row->addContent("<img title='".__('Delete')."' src='./themes/".$_SESSION[$guid]['pupilsightThemeName']."/img/garbage.png'/>")->wrap("<a onclick='return confirm(\"Are you sure you want to delete this record? Unsaved changes will be lost.\")' href='".$_SESSION[$guid]['absoluteURL'].'/modules/'.$_SESSION[$guid]['module'].'/department_edit_resource_deleteProcess.php?pupilsightDepartmentResourceID='.$resource['pupilsightDepartmentResourceID'].'&pupilsightDepartmentID='.$resource['pupilsightDepartmentID'].'&address='.$_GET['q']."'>", '</a>');
					}
				}

				for ($i = 1; $i <= 3; $i++) {
					$row = $form->addRow();
						$row->addHeading(sprintf(__('New Resource %1$s'), $i));
						$row->addClass("resource{$i}Row resource{$i}RowTop");

					$row = $form->addRow()->addClass("resource{$i}Row resource{$i}RowTop");
						$row->addLabel("name{$i}", sprintf(__('Resource %1$s Name'), $i));
						$row->addTextField("name{$i}");

					$row = $form->addRow()->addClass("resource{$i}Row resource{$i}RowTop");
                        $row->addLabel("type{$i}", sprintf(__('Resource %1$s Type'), $i));
						$row->addRadio("type{$i}")->fromArray(array('Link' => __('Link'), 'File' => __('File')))->inline();

					$form->toggleVisibilityByClass("resource{$i}TypeLink")->onRadio("type{$i}")->when('Link');	
					$row = $form->addRow()->addClass("resource{$i}Row resource{$i}TypeLink");
                        $row->addLabel("url{$i}", sprintf(__('Resource %1$s URL'), $i));
						$row->addURL("url{$i}");
						
					$form->toggleVisibilityByClass("resource{$i}TypeFile")->onRadio("type{$i}")->when('File');
					$row = $form->addRow()->addClass("resource{$i}Row resource{$i}TypeFile");
                        $row->addLabel("file{$i}", sprintf(__('Resource %1$s File'), $i));
						$row->addFileUpload("file{$i}");
						
					if ($i < 3) {
						$form->toggleVisibilityByClass("resource{$i}Button")->onRadio("type{$i}")->when(array('Link', 'File'));
						$row = $form->addRow()->addClass("resource{$i}Row resource{$i}Button");
						$row->addButton(__('Add Another Resource'))
							->onClick("$('.resource".($i+1)."RowTop').show();$('.resource".$i."Button').hide();")
							->addClass('right');
					}
				}
				
				$row = $form->addRow();
					$row->addSubmit();
				
				echo $form->getOutput();
				?>

				<script type="text/javascript">
				$(document).ready(function(){
					$('.resource2Row').hide();
					$('.resource3Row').hide();
				});
				</script>
				<?php
            }
        }
    }
}
