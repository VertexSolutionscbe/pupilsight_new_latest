<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Forms\Prefab\DeleteForm;
use Pupilsight\Domain\Staff\StaffAbsenceTypeGateway;

if (isActionAccessible($guid, $connection2, '/modules/User Admin/staffSettings_manage_delete.php') == false) {
    // Access denied
    $page->addError(__('You do not have access to this action.'));
} else {
    // Proceed!
    $pupilsightStaffAbsenceTypeID = $_GET['pupilsightStaffAbsenceTypeID'] ?? '';
    $staffAbsenceTypeGateway = $container->get(StaffAbsenceTypeGateway::class);

    if (!$staffAbsenceTypeGateway->exists($pupilsightStaffAbsenceTypeID)) {
        $page->addError(__('The specified record cannot be found.'));
        return;
    }

    $form = DeleteForm::createForm($_SESSION[$guid]['absoluteURL'].'/modules/User Admin/staffSettings_manage_deleteProcess.php?pupilsightStaffAbsenceTypeID='.$pupilsightStaffAbsenceTypeID);
    echo $form->getOutput();
}
