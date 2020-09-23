<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Domain\Staff\StaffCoverageGateway;

require_once '../../pupilsight.php';

$pupilsightStaffCoverageID = $_POST['pupilsightStaffCoverageID'] ?? '';

$URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/Staff/coverage_manage_edit.php&pupilsightStaffCoverageID='.$pupilsightStaffCoverageID;

if (isActionAccessible($guid, $connection2, '/modules/Staff/coverage_manage_edit.php') == false) {
    $URL .= '&return=error0';
    header("Location: {$URL}");
} elseif (empty($pupilsightStaffCoverageID)) {
    $URL .= '&return=error1';
    header("Location: {$URL}");
    exit;
} else {
    // Proceed!
    $staffCoverageGateway = $container->get(StaffCoverageGateway::class);
    $coverage = $staffCoverageGateway->getByID($pupilsightStaffCoverageID);

    if (empty($coverage)) {
        $URL .= '&return=error2';
        header("Location: {$URL}");
        exit;
    }

    $data = [
        'notesStatus' => $_POST['notesStatus'],
    ];

    // Update the coverage
    $updated = $staffCoverageGateway->update($pupilsightStaffCoverageID, $data);

    $URL .= !$updated
        ? '&return=error2'
        : '&return=success0';

    header("Location: {$URL}");
}
