<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Domain\Staff\StaffAbsenceDateGateway;

require_once '../../pupilsight.php';

$pupilsightStaffAbsenceID = $_POST['pupilsightStaffAbsenceID'] ?? '';
$pupilsightStaffAbsenceDateID = $_POST['pupilsightStaffAbsenceDateID'] ?? '';

$URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/Staff/absences_manage_edit_edit.php&pupilsightStaffAbsenceID='.$pupilsightStaffAbsenceID.'&pupilsightStaffAbsenceDateID='.$pupilsightStaffAbsenceDateID;

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

    $data = [
        'allDay'    => $_POST['allDay'] ?? 'N',
        'timeStart' => $_POST['timeStart'] ?? null,
        'timeEnd'   => $_POST['timeEnd'] ?? null,
        'value'     => $_POST['value'] ?? '',
    ];

    $updated = $staffAbsenceDateGateway->update($pupilsightStaffAbsenceDateID, $data);

    $URL .= !$updated
        ? '&return=error2'
        : '&return=success0';

    header("Location: {$URL}");
}
