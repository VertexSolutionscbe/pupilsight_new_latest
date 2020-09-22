<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Domain\Staff\StaffAbsenceTypeGateway;

require_once '../../pupilsight.php';

$pupilsightStaffAbsenceTypeID = $_POST['pupilsightStaffAbsenceTypeID'] ?? '';
$URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/User Admin/staffSettings_manage_edit.php&pupilsightStaffAbsenceTypeID='.$pupilsightStaffAbsenceTypeID;

if (isActionAccessible($guid, $connection2, '/modules/User Admin/staffSettings_manage_edit.php') == false) {
    $URL .= '&return=error0';
    header("Location: {$URL}");
} else {
    // Proceed!
    $staffAbsenceTypeGateway = $container->get(StaffAbsenceTypeGateway::class);

    $data = [
        'name'             => $_POST['name'] ?? '',
        'nameShort'        => $_POST['nameShort'] ?? '',
        'active'           => $_POST['active'] ?? 'Y',
        'reasons'          => $_POST['reasons'] ?? '',
        'sequenceNumber'   => $_POST['sequenceNumber'] ?? '',
        'requiresApproval' => $_POST['requiresApproval'] ?? 'N',
    ];

    if (empty($data['name']) || empty($data['nameShort'])) {
        $URL .= '&return=error1';
        header("Location: {$URL}");
        exit;
    }

    if (!$staffAbsenceTypeGateway->exists($pupilsightStaffAbsenceTypeID)) {
        $URL .= '&return=error2';
        header("Location: {$URL}");
        exit;
    }

    if (!$staffAbsenceTypeGateway->unique($data, ['name', 'nameShort'], $pupilsightStaffAbsenceTypeID)) {
        $URL .= '&return=error7';
        header("Location: {$URL}");
        exit;
    }

    $updated = $staffAbsenceTypeGateway->update($pupilsightStaffAbsenceTypeID, $data);

    $URL .= !$updated
        ? "&return=error1"
        : "&return=success0";
    header("Location: {$URL}");
}
