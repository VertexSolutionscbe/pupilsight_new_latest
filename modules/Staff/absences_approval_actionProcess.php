<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Domain\Staff\StaffAbsenceGateway;
use Pupilsight\Module\Staff\AbsenceNotificationProcess;

require_once '../../pupilsight.php';

$pupilsightStaffAbsenceID = $_POST['pupilsightStaffAbsenceID'] ?? '';
$status = $_POST['status'] ?? '';

$URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/Staff/absences_approval_action.php&pupilsightStaffAbsenceID='.$pupilsightStaffAbsenceID;
$URLSuccess = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/Staff/absences_approval.php';

if (isActionAccessible($guid, $connection2, '/modules/Staff/absences_approval_action.php') == false) {
    $URL .= '&return=error0';
    header("Location: {$URL}");
} elseif (empty($pupilsightStaffAbsenceID) || empty($status)) {
    $URL .= '&return=error1';
    header("Location: {$URL}");
    exit;
} else {
    // Proceed!
    $staffAbsenceGateway = $container->get(StaffAbsenceGateway::class);
    $absence = $staffAbsenceGateway->getByID($pupilsightStaffAbsenceID);

    if (empty($absence)) {
        $URL .= '&return=error2';
        header("Location: {$URL}");
        exit;
    }

    if ($absence['status'] != 'Pending Approval') {
        $URL .= '&return=error1';
        header("Location: {$URL}");
        exit;
    }

    if ($absence['pupilsightPersonIDApproval'] != $_SESSION[$guid]['pupilsightPersonID']) {
        $URL .= '&return=error0';
        header("Location: {$URL}");
        exit;
    }

    $data = [
        'status'            => $status,
        'timestampApproval' => date('Y-m-d H:i:s'),
        'notesApproval'     => $_POST['notesApproval'],
    ];

    $updated = $staffAbsenceGateway->update($pupilsightStaffAbsenceID, $data);

    if ($updated == false) {
        $URL .= '&return=error2';
        header("Location: {$URL}");
        exit;
    }

    // Start a background process for notifications
    $process = $container->get(AbsenceNotificationProcess::class);
    $process->startAbsenceApproval($pupilsightStaffAbsenceID);

    if ($status == 'Approved') {
        $process->startNewAbsence($pupilsightStaffAbsenceID);
    }

    $URLSuccess .= '&return=success0';

    header("Location: {$URLSuccess}");
    exit;
}
