<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Forms\Form;
use Pupilsight\Forms\DatabaseFormFactory;

//Module includes
require_once __DIR__ . '/moduleFunctions.php';

if (isActionAccessible($guid, $connection2, '/modules/Academics/department_manage_add.php') == false) {
    //Acess denied
    echo "<div class='error'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    $page->breadcrumbs
        ->add(__('Manage Subjects'), 'department_manage.php')
        ->add(__('Add Subject'));

    $editLink = '';
    if (isset($_GET['editID'])) {
        $editLink = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/Academics/department_manage_edit.php&pupilsightDepartmentID='.$_GET['editID'];
    }
    if (isset($_GET['return'])) {
        returnProcess($guid, $_GET['return'], $editLink, null);
    }

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
    

    $form = Form::create('departmentManageRecord', $_SESSION[$guid]['absoluteURL'].'/modules/'.$_SESSION[$guid]['module'].'/department_manage_addProcess.php?address='.$_SESSION[$guid]['address']);

    $form->setFactory(DatabaseFormFactory::create($pdo));
    $form->addHiddenValue('address', $_SESSION[$guid]['address']);

   

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
        $row->addLabel('name', __('Name'));
        $row->addTextField('name')->required();

    $row = $form->addRow();
        $row->addLabel('nameShort', __('Subject Code'));
        $row->addTextField('nameShort')->setId('subCode');

    // $row = $form->addRow();
    //     $row->addLabel('subjectListing', __('Subject Listing'));
    //     $row->addTextField('subjectListing')->maxLength(255);

    // $row = $form->addRow();
    //    $column = $row->addColumn()->setClass('');
    //    $column->addLabel('blurb', __('Blurb'));
    //    $column->addEditor('blurb', $guid);

    // $row = $form->addRow();
    //     $row->addLabel('file', __('Logo'))->description(__('125x125px jpg/png/gif'));
    //     $row->addFileUpload('file')
    //         ->accepts('.jpg,.jpeg,.gif,.png');

    // $row = $form->addRow();
    //     $row->addLabel('staff', __('Staff'));
    //     $row->addSelectStaff('staff')->selectMultiple();

    $form->toggleVisibilityByClass('roleLARow')->onSelect('type')->when('Learning Area');

    $row = $form->addRow()->setClass('roleLARow');
        $row->addLabel('roleLA', 'Role');
        $row->addSelect('roleLA')->fromArray($typesLA);

    $form->toggleVisibilityByClass('roleAdmin')->onSelect('type')->when('Administration');

    $row = $form->addRow()->setClass('roleAdmin');
        $row->addLabel('roleAdmin', 'Role');
        $row->addSelect('roleAdmin')->fromArray($typesAdmin);

    $row = $form->addRow();
        $row->addFooter();
        $row->addSubmit();

    echo $form->getOutput();
}

?>
<script>
    // $(document).on('keyup','#subCode', function () {
	// 	if (this.value.match(/[^a-zA-Z0-9 ]/g)) {
	// 		this.value = this.value.replace(/[^a-zA-Z0-9 ]/g, '');
	// 	}
	// });
</script>
