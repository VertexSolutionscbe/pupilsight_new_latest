<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Forms\Form;
use Pupilsight\Forms\DatabaseFormFactory;

//Module includes
require_once __DIR__ . '/moduleFunctions.php';

if (isActionAccessible($guid, $connection2, '/modules/Academics/department_manage_edit.php') == false) {
    //Acess denied
    echo "<div class='error'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    //Proceed!
    $page->breadcrumbs
        ->add(__('Manage Subjects'), 'department_manage.php')
        ->add(__('Edit Subject'));

    if (isset($_GET['return'])) {
        returnProcess($guid, $_GET['return'], null, null);
    }

    //Check if school year specified
    $pupilsightDepartmentID = $_GET['pupilsightDepartmentID'];
    if ($pupilsightDepartmentID == 'Y') {
        echo "<div class='error'>";
        echo __('You have not specified one or more required parameters.');
        echo '</div>';
    } else {
        try {
            $data = array('pupilsightDepartmentID' => $pupilsightDepartmentID);
            $sql = 'SELECT * FROM pupilsightDepartment WHERE pupilsightDepartmentID=:pupilsightDepartmentID';
            $result = $connection2->prepare($sql);
            $result->execute($data);
        } catch (PDOException $e) {
            echo "<div class='error'>".$e->getMessage().'</div>';
        }

        if ($result->rowCount() != 1) {
            echo "<div class='error'>";
            echo __('The selected record does not exist, or you do not have access to it.');
            echo '</div>';
        } else {
            //Let's go!
            $values = $result->fetch();

            $form = Form::create('departmentManageRecord', $_SESSION[$guid]['absoluteURL'].'/modules/'.$_SESSION[$guid]['module']."/department_manage_editProcess.php?pupilsightDepartmentID=$pupilsightDepartmentID&address=".$_SESSION[$guid]['address']);

            $form->setFactory(DatabaseFormFactory::create($pdo));
            $form->addHiddenValue('address', $_SESSION[$guid]['address']);

            $sqlq = 'SELECT * FROM pupilsightDepartmentType ORDER BY id ASC';
            $resultval = $connection2->query($sqlq);
            $rowdata = $resultval->fetchAll();

            if(!empty($rowdata)){
                $types = array();
                $types2 = array();
                $types1 = array('' => 'Select Type');
                foreach($rowdata as $rd){
                    $types2[$rd['name']] = $rd['name'];
                }
                $types = $types1 + $types2;
            } else {
                $types = array(
                    'Scholastic' => __('Scholastic'),
                    'Co-Scholastic' => __('Co-Scholastic'),
                    'Part A' => __('Part A'),
                    'Part B' => __('Part B'),
                    'Learning Area' => __('Learning Area'),
                    'Administration' => __('Administration'),
                );
            }

            $typesLA = array(
                'Coordinator'           => __('Coordinator'),
                'Assistant Coordinator' => __('Assistant Coordinator'),
                'Teacher (Curriculum)'  => __('Teacher (Curriculum)'),
                'Teacher'               => __('Teacher'),
                'Other'                 => __('Other'),
            );

            $typesAdmin = array(
                'Director'      => __('Director'),
                'Manager'       => __('Manager'),
                'Administrator' => __('Administrator'),
                'Other'         => __('Other'),
            );

            $row = $form->addRow();
                $row->addLabel('type', 'Type');
                $row->addSelect('type')->fromArray($types)->required();

            $row = $form->addRow();
                $row->addLabel('name', 'Name');
                $row->addTextField('name')->maxLength(40)->required();

            $row = $form->addRow();
                $row->addLabel('nameShort', 'Subject Code');
                $row->addTextField('nameShort')->setId('subCode')->required();

            // $row = $form->addRow();
            //     $row->addLabel('subjectListing', 'Subject Listing');
            //     $row->addTextField('subjectListing')->maxLength(255);

            // $row = $form->addRow();
            //    $column = $row->addColumn()->setClass('');
            //    $column->addLabel('blurb', 'Blurb');
            //    $column->addEditor('blurb', $guid);

            // $row = $form->addRow();
            //     $row->addLabel('file', 'Logo')->description('125x125px jpg/png/gif');
            //     $row->addFileUpload('file')
            //         ->accepts('.jpg,.jpeg,.gif,.png')
            //         ->setAttachment('logo', $_SESSION[$guid]['absoluteURL'], $values['logo']);

            // $form->addRow()->addHeading(__('Current Staff'));

            // $data = array('pupilsightDepartmentID' => $pupilsightDepartmentID);
            // $sql = "SELECT preferredName, surname, pupilsightDepartmentStaff.* FROM pupilsightDepartmentStaff JOIN pupilsightPerson ON (pupilsightDepartmentStaff.pupilsightPersonID=pupilsightPerson.pupilsightPersonID) WHERE pupilsightDepartmentID=:pupilsightDepartmentID AND pupilsightPerson.status='Full' ORDER BY surname, preferredName";

            // $results = $pdo->executeQuery($data, $sql);

            // if ($results->rowCount() == 0) {
            //     $form->addRow()->addAlert(__('There are no records to display.'), 'error');
            // } else {
            //     $form->addRow()->addContent('<b>'.__('Warning').'</b>: '.__('If you delete a member of staff, any unsaved changes to this record will be lost!'))->wrap('<i>', '</i>');

            //     $table = $form->addRow()->addTable()->addClass('colorOddEven');

            //     $header = $table->addHeaderRow();
            //     $header->addContent(__('Name'));
            //     $header->addContent(__('Role'));
            //     $header->addContent(__('Action'));

            //     while ($staff = $results->fetch()) {
            //         $row = $table->addRow();
            //         $row->addContent(formatName('', $staff['preferredName'], $staff['surname'], 'Staff', true, true));
            //         $row->addContent($staff['role']);
            //         $row->addContent("<a onclick='return confirm(\"".__('Are you sure you wish to delete this record?')."\")' href='".$_SESSION[$guid]['absoluteURL'].'/modules/'.$_SESSION[$guid]['module'].'/department_manage_edit_staff_deleteProcess.php?address='.$_GET['q'].'&pupilsightDepartmentStaffID='.$staff['pupilsightDepartmentStaffID']."&pupilsightDepartmentID=$pupilsightDepartmentID'><i title='".__('Delete')."' class='fas fa-trash-alt px-2'></i></a>");
            //     }
            // }

            // $form->addRow()->addHeading(__('New Staff'));

            // $row = $form->addRow();
            //     $row->addLabel('staff', 'Staff');
            //     $row->addSelectStaff('staff')->selectMultiple();

            // if ($values['type'] == 'Learning Area') {
            //     $row = $form->addRow()->setClass('roleLARow');
            //         $row->addLabel('role', 'Role');
            //         $row->addSelect('role')->fromArray($typesLA);
            // } else {
            //     $row = $form->addRow()->setClass('roleAdmin');
            //         $row->addLabel('role', 'Role');
            //         $row->addSelect('role')->fromArray($typesAdmin);
            // }

            $row = $form->addRow();
                $row->addFooter();
                $row->addSubmit();

            $form->loadAllValuesFrom($values);

            echo $form->getOutput();
        }
    }
}

?>
<script>
    // $(document).on('keyup','#subCode', function () {
	// 	if (this.value.match(/[^a-zA-Z0-9 ]/g)) {
	// 		this.value = this.value.replace(/[^a-zA-Z0-9 ]/g, '');
	// 	}
	// });
</script>