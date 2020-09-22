<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Forms\Form;
use Pupilsight\Forms\DatabaseFormFactory;
use Pupilsight\Services\Format;
use Pupilsight\Domain\User\UserGateway;
use Pupilsight\Domain\Staff\StaffAbsenceTypeGateway;
use Pupilsight\Domain\Staff\StaffAbsenceGateway;
use Pupilsight\Domain\Staff\StaffAbsenceDateGateway;
use Pupilsight\Module\Staff\Tables\AbsenceDates;

if (isActionAccessible($guid, $connection2, '/modules/Staff/absences_manage_edit.php') == false) {
    // Access denied
    $page->addError(__('You do not have access to this action.'));
} else {
    // Proceed!
    $page->breadcrumbs
        ->add(__('Manage Staff Absences'), 'absences_manage.php')
        ->add(__('Edit Absence'));

    if (isset($_GET['return'])) {
        returnProcess($guid, $_GET['return'], null, array('error3' => __('School is closed on the specified day.')));
    }

    $pupilsightStaffAbsenceID = $_GET['pupilsightStaffAbsenceID'] ?? '';

    $staffAbsenceGateway = $container->get(StaffAbsenceGateway::class);
    $staffAbsenceDateGateway = $container->get(StaffAbsenceDateGateway::class);
    $staffAbsenceTypeGateway = $container->get(StaffAbsenceTypeGateway::class);

    if (empty($pupilsightStaffAbsenceID)) {
        $page->addError(__('You have not specified one or more required parameters.'));
        return;
    }

    $values = $staffAbsenceGateway->getByID($pupilsightStaffAbsenceID);

    if (empty($values)) {
        $page->addError(__('The specified record cannot be found.'));
        return;
    }

    // Get absence types & format them for the chained select lists
    $type = $staffAbsenceTypeGateway->getByID($values['pupilsightStaffAbsenceTypeID']);
    $types = $staffAbsenceTypeGateway->selectAllTypes()->fetchAll();

    $typesWithReasons = $reasonsOptions = $reasonsChained = [];

    $types = array_reduce($types, function ($group, $item) use (&$reasonsOptions, &$reasonsChained, &$typesWithReasons) {
        $id = $item['pupilsightStaffAbsenceTypeID'];
        $group[$id] = $item['name'];
        $reasons = array_filter(array_map('trim', explode(',', $item['reasons'])));
        if (!empty($reasons)) {
            $typesWithReasons[] = $id;
            foreach ($reasons as $reason) {
                $reasonsOptions[$reason] = $reason;
                $reasonsChained[$reason] = $id;
            }
        }
        return $group;
    }, []);

    // FORM
    $form = Form::create('staffAbsenceEdit', $_SESSION[$guid]['absoluteURL'].'/modules/Staff/absences_manage_editProcess.php');

    $form->setFactory(DatabaseFormFactory::create($pdo));
    $form->addHiddenValue('address', $_SESSION[$guid]['address']);
    $form->addHiddenValue('pupilsightStaffAbsenceID', $pupilsightStaffAbsenceID);

    $form->addRow()->addHeading(__('Basic Information'));

    $row = $form->addRow();
        $row->addLabel('pupilsightPersonID', __('Person'));
        $row->addSelectStaff('pupilsightPersonID')->placeholder()->isRequired()->readonly();

    if ($type['requiresApproval'] == 'Y') {
        $approver = '';
        if (!empty($values['pupilsightPersonIDApproval'])) {
            $approver = $container->get(UserGateway::class)->getByID($values['pupilsightPersonIDApproval']);
            $approver = Format::small(__('By').' '.Format::nameList([$approver], 'Staff'));
        }

        $row = $form->addRow();
            $row->addLabel('status', __('Status'));
            $row->addContent($values['status'].'<br/>'.$approver)->wrap('<div class="standardWidth floatRight">', '</div>');
    }

    $row = $form->addRow();
        $row->addLabel('pupilsightStaffAbsenceTypeID', __('Type'));
        $row->addSelect('pupilsightStaffAbsenceTypeID')
            ->fromArray($types)
            ->placeholder()
            ->isRequired();

    $form->toggleVisibilityByClass('reasonOptions')->onSelect('pupilsightStaffAbsenceTypeID')->when($typesWithReasons);

    $row = $form->addRow()->addClass('reasonOptions');
        $row->addLabel('reason', __('Reason'));
        $row->addSelect('reason')
            ->fromArray($reasonsOptions)
            ->chainedTo('pupilsightStaffAbsenceTypeID', $reasonsChained)
            ->placeholder()
            ->isRequired();

    $row = $form->addRow();
        $row->addLabel('comment', __('Comment'));
        $row->addTextArea('comment')->setRows(2);

    $notificationList = !empty($values['notificationList'])? json_decode($values['notificationList']) : [];
    $notified = $container->get(UserGateway::class)->selectNotificationDetailsByPerson($notificationList)->fetchGroupedUnique();

    $row = $form->addRow();
        $row->addLabel('sentToLabel', __('Notified'));
        $row->addTextArea('sentTo')->setRows(3)->readonly()->setValue(Format::nameList($notified, 'Staff', false, true, ', '));

    $row = $form->addRow();
        $row->addFooter();
        $row->addSubmit();

    $form->loadAllValuesFrom($values);

    echo $form->getOutput();

    // Absence Dates
    $table = $container->get(AbsenceDates::class)->create($pupilsightStaffAbsenceID, true);
    $table->setTitle(__('Dates'));
    echo $table->getOutput();

    $form = Form::create('staffAbsenceAdd', $_SESSION[$guid]['absoluteURL'].'/modules/Staff/absences_manage_edit_addProcess.php');

    $form->setFactory(DatabaseFormFactory::create($pdo));
    $form->addHiddenValue('address', $_SESSION[$guid]['address']);
    $form->addHiddenValue('pupilsightStaffAbsenceID', $pupilsightStaffAbsenceID);

    $form->addRow()->addHeading(__('Add'));

    $row = $form->addRow();
        $row->addLabel('allDay', __('All Day'));
        $row->addYesNoRadio('allDay')->checked('Y');

    $form->toggleVisibilityByClass('timeOptions')->onRadio('allDay')->when('N');

    $row = $form->addRow();
        $row->addLabel('date', __('Date'));
        $row->addDate('date')->isRequired();

    $row = $form->addRow()->addClass('timeOptions');
        $row->addLabel('time', __('Time'));
        $col = $row->addColumn('time');
        $col->addTime('timeStart')
            ->addClass('w-full mr-1')
            ->isRequired();
        $col->addTime('timeEnd')
            ->chainedTo('timeStart', false)
            ->addClass('w-full')
            ->isRequired();

    $row = $form->addRow();
        $row->addFooter();
        $row->addSubmit();

    echo $form->getOutput();
}
