<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Domain\Staff\StaffAbsenceTypeGateway;

require_once '../../pupilsight.php';

$URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/User Admin/staffSettings_manage_add.php';

if (isActionAccessible($guid, $connection2, '/modules/User Admin/staffSettings_manage_add.php') == false) {
    $URL .= '&return=error0';
    header("Location: {$URL}");
} else {
    // Proceed!
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

    $staffAbsenceTypeGateway = $container->get(StaffAbsenceTypeGateway::class);

    if (!$staffAbsenceTypeGateway->unique($data, ['name', 'nameShort'])) {
        $URL .= '&return=error7';
        header("Location: {$URL}");
        exit;
    }

    $inserted = $staffAbsenceTypeGateway->insert($data);

    $URL .= !$inserted
        ? "&return=error1"
        : "&return=success0&editID=$inserted";
    header("Location: {$URL}");
}
