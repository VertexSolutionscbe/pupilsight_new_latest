<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Forms\Form;

if (isActionAccessible($guid, $connection2, '/modules/User Admin/role_manage_add.php') == false) {
    //Acess denied
    echo "<div class='alert alert-danger'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    //Proceed!
    $page->breadcrumbs
        ->add(__('Manage Roles'),'role_manage.php')
        ->add(__('Add Role'));

    $editLink = '';
    if (isset($_GET['editID'])) {
        $editLink = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/User Admin/role_manage_edit.php&pupilsightRoleID='.$_GET['editID'];
    }
    if (isset($_GET['return'])) {
        returnProcess($guid, $_GET['return'], $editLink, null);
    }

    $form = Form::create('addRole', $_SESSION[$guid]['absoluteURL'].'/modules/'.$_SESSION[$guid]['module'].'/role_manage_addProcess.php');

    $form->addHiddenValue('address', $_SESSION[$guid]['address']);

    $categories = array(
        'Staff'   => __('Staff'),
        'Student' => __('Student'),
        'Parent'  => __('Parent'),
        'Other'   => __('Other'),
    );

    $restrictions = array(
        'None'       => __('None'),
        'Same Role'  => __('Users with the same role'),
        'Admin Only' => __('Administrators only'),
    );

    $row = $form->addRow();
        $row->addLabel('category', __('Category'));
        $row->addSelect('category')->fromArray($categories)->required()->placeholder();

    $row = $form->addRow();
        $row->addLabel('name', __('Name'));
        $row->addTextField('name')->required()->maxLength(20);

    $row = $form->addRow();
        $row->addLabel('nameShort', __('Short Name'));
        $row->addTextField('nameShort')->required()->maxLength(4);

    $row = $form->addRow();
        $row->addLabel('description', __('Description'));
        $row->addTextField('description')->required()->maxLength(60);

    $row = $form->addRow();
        $row->addLabel('type', __('Type'));
        $row->addTextField('type')->required()->readonly()->setValue('Additional');

    $row = $form->addRow();
        $row->addLabel('canLoginRole', __('Can Login?'))->description(__('Are users with this primary role able to login?'));
        $row->addYesNo('canLoginRole')->required()->selected('Y');

    $form->toggleVisibilityByClass('loginOptions')->onSelect('canLoginRole')->when('Y');
    $row = $form->addRow()->addClass('loginOptions');
        $row->addLabel('pastYearsLogin', __('Login To Past Years'));
        $row->addYesNo('pastYearsLogin')->required();

    $row = $form->addRow()->addClass('loginOptions');
        $row->addLabel('futureYearsLogin', __('Login To Future Years'));
        $row->addYesNo('futureYearsLogin')->required();

    $row = $form->addRow();
        $row->addLabel('restriction', __('Restriction'))->description('Determines who can grant or remove this role in Manage Users.');
        $row->addSelect('restriction')->fromArray($restrictions)->required();

    $row = $form->addRow();
        $row->addFooter();
        $row->addSubmit();

    echo $form->getOutput();
}
