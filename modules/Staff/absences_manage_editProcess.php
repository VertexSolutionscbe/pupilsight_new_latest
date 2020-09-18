<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Domain\Staff\StaffAbsenceGateway;

require_once '../../pupilsight.php';

$pupilsightStaffAbsenceID = $_POST['pupilsightStaffAbsenceID'] ?? '';

$URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/Staff/absences_manage_edit.php&pupilsightStaffAbsenceID='.$pupilsightStaffAbsenceID;

if (isActionAccessible($guid, $connection2, '/modules/Staff/absences_manage_edit.php') == false) {
    $URL .= '&return=error0';
    header("Location: {$URL}");
} elseif (empty($pupilsightStaffAbsenceID)) {
    $URL .= '&return=error1';
    header("Location: {$URL}");
    exit;
} else {
    // Proceed!
    $staffAbsenceGateway = $container->get(StaffAbsenceGateway::class);
    $values = $staffAbsenceGateway->getByID($pupilsightStaffAbsenceID);

    if (empty($values)) {
        $URL .= '&return=error2';
        header("Location: {$URL}");
        exit;
    }

    $data = [
        'pupilsightStaffAbsenceTypeID' => $_POST['pupilsightStaffAbsenceTypeID'] ?? '',
        'reason'                   => $_POST['reason'] ?? '',
        'comment'                  => $_POST['comment'] ?? '',
    ];

    $updated = $staffAbsenceGateway->update($pupilsightStaffAbsenceID, $data);

    $URL .= !$updated
        ? '&return=error2'
        : '&return=success0';

    header("Location: {$URL}");
}
