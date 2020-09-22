<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Domain\Staff\StaffAbsenceTypeGateway;

require_once '../../pupilsight.php';

$pupilsightStaffAbsenceTypeID = $_GET['pupilsightStaffAbsenceTypeID'] ?? '';
$URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/User Admin/staffSettings.php';

if (isActionAccessible($guid, $connection2, '/modules/User Admin/staffSettings_manage_delete.php') == false) {
    $URL .= '&return=error0';
    header("Location: {$URL}");
} else {
    // Proceed!
    $staffAbsenceTypeGateway = $container->get(StaffAbsenceTypeGateway::class);

    if (empty($pupilsightStaffAbsenceTypeID)) {
        $URL .= '&return=error1';
        header("Location: {$URL}");
        exit;
    }

    if (!$staffAbsenceTypeGateway->exists($pupilsightStaffAbsenceTypeID)) {
        $URL .= '&return=error2';
        header("Location: {$URL}");
        exit;
    }

    $deleted = $staffAbsenceTypeGateway->delete($pupilsightStaffAbsenceTypeID);

    $URL .= !$deleted
        ? "&return=error1"
        : "&return=success0";
    header("Location: {$URL}");
}
