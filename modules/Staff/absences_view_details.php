<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Domain\Staff\StaffAbsenceGateway;
use Pupilsight\Module\Staff\View\StaffCard;
use Pupilsight\Module\Staff\View\AbsenceView;
use Pupilsight\Module\Staff\Tables\AbsenceDates;

if (isActionAccessible($guid, $connection2, '/modules/Staff/absences_view_details.php') == false) {
    // Access denied
    $page->addError(__('You do not have access to this action.'));
} else {
    $page->breadcrumbs
        ->add(__('View Absences'), 'absences_view_byPerson.php')
        ->add(__('View Details'));

    $highestAction = getHighestGroupedAction($guid, $_GET['q'], $connection2);
    if (empty($highestAction)) {
        $page->addError(__('You do not have access to this action.'));
        return;
    }

    if (isset($_GET['return'])) {
        ob_start();
        returnProcess($guid, $_GET['return'], null, null);
        $page->write(ob_get_clean());
    }

    $pupilsightStaffAbsenceID = $_GET['pupilsightStaffAbsenceID'] ?? '';

    $staffAbsenceGateway = $container->get(StaffAbsenceGateway::class);

    if (empty($pupilsightStaffAbsenceID)) {
        $page->addError(__('You have not specified one or more required parameters.'));
        return;
    }

    $absence = $staffAbsenceGateway->getByID($pupilsightStaffAbsenceID);

    if (empty($absence)) {
        $page->addError(__('The specified record cannot be found.'));
        return;
    }

    if ($highestAction == 'View Absences_mine' && $absence['pupilsightPersonID'] != $_SESSION[$guid]['pupilsightPersonID']) {
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
}
