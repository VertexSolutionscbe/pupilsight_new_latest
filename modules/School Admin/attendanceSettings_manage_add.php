<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Forms\Form;
use Pupilsight\Forms\DatabaseFormFactory;

//Module includes
require_once __DIR__ . '/moduleFunctions.php';

if (isActionAccessible($guid, $connection2, '/modules/School Admin/attendanceSettings_manage_add.php') == false) {
    //Acess denied
    echo "<div class='alert alert-danger'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    //Proceed!
    $page->breadcrumbs
        ->add(__('Manage Attendance Master'), 'attendanceSettings.php')
        ->add(__('Add Attendance Master'));

    $editLink = '';
    if (isset($_GET['editID'])) {
        $editLink = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/School Admin/attendanceSettings_manage_add.php&pupilsightDepartmentID='.$_GET['editID'];
    }
    if (isset($_GET['return'])) {
        returnProcess($guid, $_GET['return'], $editLink, null);
    }

    $form = Form::create('attendanceCode', $_SESSION[$guid]['absoluteURL'].'/modules/'.$_SESSION[$guid]['module'].'/attendanceSettings_manage_addProcess.php');
    $form->setFactory(DatabaseFormFactory::create($pdo));

    $form->addHiddenValue('address', $_SESSION[$guid]['address']);

    $row = $form->addRow();
        $row->addLabel('name', __('Name'))->description(__('Must be unique.'));
        $row->addTextField('name')->required()->maxLength(30);
    
    $row = $form->addRow();
        $row->addLabel('nameShort', __('Short Name'))->description(__('Must be unique.'));
        $row->addTextField('nameShort')->required()->maxLength(4);

    $directions = array(
        'In'     => __('In Class'),
        'Out' => __('Out of Class'),
    );
    $row = $form->addRow();
        $row->addLabel('direction', __('Direction'));
        $row->addSelect('direction')->required()->fromArray($directions);

    $scopes = array(
        'Onsite'         => __('Onsite'),
        'Onsite - Late'  => __('Onsite - Late'),
        'Offsite'        => __('Offsite'),
        'Offsite - Left' => __('Offsite - Left'),
    );
    $row = $form->addRow();
        $row->addLabel('scope', __('Scope'));
        $row->addSelect('scope')->required()->fromArray($scopes);

    $row = $form->addRow();
        $row->addLabel('sequenceNumber', __('Sequence Number'));
        $row->addSequenceNumber('sequenceNumber', 'pupilsightAttendanceCode')->required()->maxLength(3);

    $row = $form->addRow();
        $row->addLabel('active', __('Active'));
        $row->addYesNo('active')->required();

    $row = $form->addRow();
        $row->addLabel('reportable', __('Reportable'));
        $row->addYesNo('reportable')->required();

    $row = $form->addRow();
        $row->addLabel('future', __('Allow Future Use'))->description(__('Can this code be used in Set Future Absence?'));
        $row->addYesNo('future')->required();

    $row = $form->addRow();
        $row->addLabel('pupilsightRoleIDAll', __('Available to Roles'))->description(__('Controls who can use this code.'));
        $row->addSelectRole('pupilsightRoleIDAll')->selectMultiple();

    $row = $form->addRow();
        $row->addFooter();
        $row->addSubmit();

    echo $form->getOutput();
}
