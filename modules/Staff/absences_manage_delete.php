<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Forms\Prefab\DeleteForm;
use Pupilsight\Domain\Staff\StaffAbsenceGateway;

if (isActionAccessible($guid, $connection2, '/modules/Staff/absences_manage_delete.php') == false) {
    // Access denied
    $page->addError(__('You do not have access to this action.'));
} else {
    // Proceed!
    $pupilsightStaffAbsenceID = $_GET['pupilsightStaffAbsenceID'] ?? '';
    $staffAbsenceGateway = $container->get(StaffAbsenceGateway::class);
    
    if (empty($pupilsightStaffAbsenceID)) {
        $page->addError(__('You have not specified one or more required parameters.'));
        return;
    }

    $values = $staffAbsenceGateway->getByID($pupilsightStaffAbsenceID);

    if (empty($pupilsightStaffAbsenceID) || empty($values)) {
        $page->addError(__('The specified record cannot be found.'));
        return;
    }

    $form = DeleteForm::createForm($_SESSION[$guid]['absoluteURL'].'/modules/Staff/absences_manage_deleteProcess.php?pupilsightStaffAbsenceID='.$pupilsightStaffAbsenceID, true);
    echo $form->getOutput();
}
