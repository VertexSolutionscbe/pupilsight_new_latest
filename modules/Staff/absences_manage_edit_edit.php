<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Forms\Form;
use Pupilsight\Forms\DatabaseFormFactory;
use Pupilsight\Services\Format;
use Pupilsight\Domain\Staff\StaffAbsenceDateGateway;

if (isActionAccessible($guid, $connection2, '/modules/Staff/absences_manage_edit.php') == false) {
    // Access denied
    $page->addError(__('You do not have access to this action.'));
} else {
    $pupilsightStaffAbsenceID = $_GET['pupilsightStaffAbsenceID'] ?? '';
    $pupilsightStaffAbsenceDateID = $_GET['pupilsightStaffAbsenceDateID'] ?? '';

    // Proceed!
    $page->breadcrumbs
        ->add(__('Manage Staff Absences'), 'absences_manage.php')
        ->add(__('Edit Absence'), 'absences_manage_edit.php', ['pupilsightStaffAbsenceID' => $pupilsightStaffAbsenceID])
        ->add(__('Edit'));

    if (isset($_GET['return'])) {
        returnProcess($guid, $_GET['return'], null, null);
    }

    if (empty($pupilsightStaffAbsenceID) || empty($pupilsightStaffAbsenceDateID)) {
        $page->addError(__('You have not specified one or more required parameters.'));
        return;
    }

    $staffAbsenceDateGateway = $container->get(StaffAbsenceDateGateway::class);
    $values = $staffAbsenceDateGateway->getByID($pupilsightStaffAbsenceDateID);

    if (empty($values)) {
        $page->addError(__('The specified record cannot be found.'));
        return;
    }

    $form = Form::create('staffAbsenceEdit', $_SESSION[$guid]['absoluteURL'].'/modules/Staff/absences_manage_edit_editProcess.php');

    $form->setFactory(DatabaseFormFactory::create($pdo));
    $form->addHiddenValue('address', $_SESSION[$guid]['address']);
    $form->addHiddenValue('pupilsightStaffAbsenceID', $pupilsightStaffAbsenceID);
    $form->addHiddenValue('pupilsightStaffAbsenceDateID', $pupilsightStaffAbsenceDateID);
   
    $row = $form->addRow();
        $row->addLabel('dateLabel', __('Date'));
        $row->addTextField('dateLabel')->readonly()->setValue(Format::date($values['date']));

    $row = $form->addRow();
        $row->addLabel('allDay', __('When'));
        $row->addCheckbox('allDay')
            ->description(__('All Day'))
            ->inline()
            ->setClass()
            ->setValue('Y')
            ->checked($values['allDay'])
            ->wrap('<div class="standardWidth floatRight">', '</div>');

    $form->toggleVisibilityByClass('timeOptions')->onCheckbox('allDay')->whenNot('Y');

    $row = $form->addRow()->addClass('timeOptions');
        $row->addLabel('time', __('Time'));
        $col = $row->addColumn('time');
        $col->addTime('timeStart')
            ->setClass('w-full mr-1')
            ->isRequired();
        $col->addTime('timeEnd')
            ->setClass('w-full')
            ->chainedTo('timeStart', false)
            ->isRequired();

    $row = $form->addRow();
        $row->addLabel('value', __('Value'));
        $row->addNumber('value')->decimalPlaces(1)->maxLength(3)->minimum(0)->maximum(1);

    $row = $form->addRow();
        $row->addFooter();
        $row->addSubmit();

    $form->loadAllValuesFrom($values);

    echo $form->getOutput();
}
