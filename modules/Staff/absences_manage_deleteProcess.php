<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Domain\Staff\StaffAbsenceGateway;
use Pupilsight\Domain\Staff\StaffAbsenceDateGateway;

$_POST['address'] = '/modules/Staff/absences_manage.php';
$pupilsightStaffAbsenceID = $_GET['pupilsightStaffAbsenceID'] ?? '';
$search = $_POST['search'] ?? '';

require_once '../../pupilsight.php';

$URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/Staff/absences_manage.php&search='.$search;

if (isActionAccessible($guid, $connection2, '/modules/Staff/absences_manage_delete.php') == false) {
    $URL .= '&return=error0';
    header("Location: {$URL}");
} elseif (empty($pupilsightStaffAbsenceID)) {
    $URL .= '&return=error1';
    header("Location: {$URL}");
    exit;
} else {
    // Proceed!
    $staffAbsenceGateway = $container->get(StaffAbsenceGateway::class);
    $staffAbsenceDateGateway = $container->get(StaffAbsenceDateGateway::class);
    $values = $staffAbsenceGateway->getByID($pupilsightStaffAbsenceID);

    if (empty($values)) {
        $URL .= '&return=error2';
        header("Location: {$URL}");
        exit;
    }

    $absenceDates = $staffAbsenceDateGateway->selectDatesByAbsence($pupilsightStaffAbsenceID)->fetchAll();
    $partialFail = false;

    // Delete each date first
    foreach ($absenceDates as $log) {
        $partialFail &= $staffAbsenceDateGateway->delete($log['pupilsightStaffAbsenceDateID']);
    }

    // Then delete the absence itself
    $partialFail &= $staffAbsenceGateway->delete($pupilsightStaffAbsenceID);

    $URL .= $partialFail
        ? '&return=warning1'
        : '&return=success0';

    header("Location: {$URL}");
}
