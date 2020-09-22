<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Forms\Prefab\DeleteForm;
use Pupilsight\Domain\Staff\StaffCoverageGateway;

if (isActionAccessible($guid, $connection2, '/modules/Staff/coverage_manage_delete.php') == false) {
    // Access denied
    $page->addError(__('You do not have access to this action.'));
} else {
    // Proceed!
    $pupilsightStaffCoverageID = $_GET['pupilsightStaffCoverageID'] ?? '';
    $staffCoverageGateway = $container->get(StaffCoverageGateway::class);
    
    if (empty($pupilsightStaffCoverageID)) {
        $page->addError(__('You have not specified one or more required parameters.'));
        return;
    }

    $values = $staffCoverageGateway->getByID($pupilsightStaffCoverageID);

    if (empty($pupilsightStaffCoverageID) || empty($values)) {
        $page->addError(__('The specified record cannot be found.'));
        return;
    }

    $form = DeleteForm::createForm($_SESSION[$guid]['absoluteURL'].'/modules/Staff/coverage_manage_deleteProcess.php?pupilsightStaffCoverageID='.$pupilsightStaffCoverageID, true);
    echo $form->getOutput();
}
