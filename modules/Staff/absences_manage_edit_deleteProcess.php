<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Domain\Staff\StaffAbsenceDateGateway;

$_POST['address'] = '/modules/Staff/absences_manage_edit.php';
$pupilsightStaffAbsenceID = $_GET['pupilsightStaffAbsenceID'] ?? '';
$pupilsightStaffAbsenceDateID = $_GET['pupilsightStaffAbsenceDateID'] ?? '';

require_once '../../pupilsight.php';

$URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/Staff/absences_manage_edit.php&pupilsightStaffAbsenceID='.$pupilsightStaffAbsenceID;

if (isActionAccessible($guid, $connection2, '/modules/Staff/absences_manage_edit.php') == false) {
    $URL .= '&return=error0';
    header("Location: {$URL}");
} elseif (empty($pupilsightStaffAbsenceID) || empty($pupilsightStaffAbsenceDateID)) {
    $URL .= '&return=error1';
    header("Location: {$URL}");
    exit;
} else {
    // Proceed!
    $staffAbsenceDateGateway = $container->get(StaffAbsenceDateGateway::class);
    $values = $staffAbsenceDateGateway->getByID($pupilsightStaffAbsenceDateID);

    if (empty($values)) {
        $URL .= '&return=error2';
        header("Location: {$URL}");
        exit;
    }

    $deleted = $staffAbsenceDateGateway->delete($pupilsightStaffAbsenceDateID);
    
    $URL .= !$deleted
        ? '&return=error2'
        : '&return=success0';

    header("Location: {$URL}");
}
