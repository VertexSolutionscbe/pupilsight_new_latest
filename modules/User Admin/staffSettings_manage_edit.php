<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Forms\Form;
use Pupilsight\Domain\Staff\StaffAbsenceTypeGateway;
use Pupilsight\Forms\DatabaseFormFactory;

if (isActionAccessible($guid, $connection2, '/modules/User Admin/staffSettings_manage_edit.php') == false) {
    // Access denied
    $page->addError(__('You do not have access to this action.'));
} else {
    //Proceed!
    $page->breadcrumbs
        ->add(__('Manage Staff Settings'), 'staffSettings.php')
        ->add(__('Absence Type'));

    if (isset($_GET['return'])) {
        returnProcess($guid, $_GET['return'], null, null);
    }

    $pupilsightStaffAbsenceTypeID = $_GET['pupilsightStaffAbsenceTypeID'] ?? '';
    $staffAbsenceTypeGateway = $container->get(StaffAbsenceTypeGateway::class);

    $values = $staffAbsenceTypeGateway->getByID($pupilsightStaffAbsenceTypeID);

    if (empty($values)) {
        $page->addError(__('The specified record cannot be found.'));
        return;
    }

    $form = Form::create('staffAbsenceType', $_SESSION[$guid]['absoluteURL'].'/modules/User Admin/staffSettings_manage_editProcess.php');
    $form->setFactory(DatabaseFormFactory::create($pdo));

    $form->addHiddenValue('address', $_SESSION[$guid]['address']);
    $form->addHiddenValue('pupilsightStaffAbsenceTypeID', $pupilsightStaffAbsenceTypeID);

    $row = $form->addRow();
        $row->addLabel('name', __('Name'))->description(__('Must be unique.'));
        $row->addTextField('name')->required()->maxLength(60);
    
    $row = $form->addRow();
        $row->addLabel('nameShort', __('Short Name'))->description(__('Must be unique.'));
        $row->addTextField('nameShort')->required()->maxLength(10);

    $row = $form->addRow();
        $row->addLabel('active', __('Active'));
        $row->addYesNo('active')->required();

    $row = $form->addRow();
        $row->addLabel('requiresApproval', __('Requires Approval'))->description(__('If enabled, absences of this type must be submitted for approval before they are accepted.'));
        $row->addYesNo('requiresApproval')->required();

    $row = $form->addRow();
        $row->addLabel('reasons', __('Reasons'))->description(__('An optional, comma-separated list of reasons which are available when submitting this type of absence.'));
        $row->addTextArea('reasons')->setRows(4);

    $row = $form->addRow();
        $row->addLabel('sequenceNumber', __('Sequence Number'));
        $row->addSequenceNumber('sequenceNumber', 'pupilsightStaffAbsenceType', $values['sequenceNumber'])->maxLength(3);

    $row = $form->addRow();
        $row->addFooter();
        $row->addSubmit();

    $form->loadAllValuesFrom($values);

    echo $form->getOutput();
}
