<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Forms\Form;
use Pupilsight\Domain\Staff\StaffAbsenceGateway;
use Pupilsight\Module\Staff\View\StaffCard;
use Pupilsight\Module\Staff\View\AbsenceView;
use Pupilsight\Module\Staff\Tables\AbsenceDates;

if (isActionAccessible($guid, $connection2, '/modules/Staff/absences_approval_action.php') == false) {
    // Access denied
    $page->addError(__('You do not have access to this action.'));
} else {
    // Proceed!
    $pupilsightStaffAbsenceID = $_GET['pupilsightStaffAbsenceID'] ?? '';
    $status = $_GET['status'] ?? '';

    $page->breadcrumbs
        ->add(__('Approve Staff Absences'), 'absences_approval.php')
        ->add(__('Approval'));

    if (isset($_GET['return'])) {
        returnProcess($guid, $_GET['return'], $editLink, null);
    }

    $absence = $container->get(StaffAbsenceGateway::class)->getAbsenceDetailsByID($pupilsightStaffAbsenceID);

    if (empty($absence)) {
        $page->addError(__('The specified record cannot be found.'));
        return;
    }

    if ($absence['pupilsightPersonIDApproval'] != $_SESSION[$guid]['pupilsightPersonID']) {
        $page->addError(__('You do not have access to this action.'));
        return;
    }
    
    // Staff Card
    $staffCard = $container->get(StaffCard::class);
    $staffCard->setPerson($absence['pupilsightPersonID'])->compose($page);

    // Absence Dates
    $table = $container->get(AbsenceDates::class)->create($pupilsightStaffAbsenceID, true);
    $page->write($table->getOutput());

    // Absence View Composer
    $absenceView = $container->get(AbsenceView::class);
    $absenceView->setAbsence($pupilsightStaffAbsenceID, $_SESSION[$guid]['pupilsightPersonID'])->compose($page);
    
    // Approval Form
    $form = Form::create('staffAbsenceApproval', $_SESSION[$guid]['absoluteURL'].'/modules/Staff/absences_approval_actionProcess.php');

    $form->addHiddenValue('address', $_SESSION[$guid]['address']);
    $form->addHiddenValue('pupilsightStaffAbsenceID', $pupilsightStaffAbsenceID);

    $options = [
        'Approved' => __('Approved'),
        'Declined' => __('Declined'),
    ];
    $row = $form->addRow();
        $row->addLabel('status', __('Status'));
        $row->addSelect('status')->fromArray($options)->selected($status)->required();

    $row = $form->addRow();
        $row->addLabel('notesApproval', __('Reply'));
        $row->addTextArea('notesApproval')->setRows(3);

    $row = $form->addRow();
        $row->addFooter();
        $row->addSubmit();

    echo $form->getOutput();
}
