<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Domain\Staff\StaffAbsenceGateway;
use Pupilsight\Domain\Staff\StaffCoverageDateGateway;
use Pupilsight\Domain\Staff\StaffCoverageGateway;
use Pupilsight\Module\Staff\CoverageNotificationProcess;

require_once '../../pupilsight.php';

$pupilsightStaffCoverageID = $_POST['pupilsightStaffCoverageID'] ?? '';

$URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/Staff/coverage_view_cancel.php&pupilsightStaffCoverageID='.$pupilsightStaffCoverageID;
$URLSuccess = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/Staff/coverage_my.php';

if (isActionAccessible($guid, $connection2, '/modules/Staff/coverage_view_cancel.php') == false) {
    $URL .= '&return=error0';
    header("Location: {$URL}");
    exit;
} else {
    // Proceed!
    $staffCoverageGateway = $container->get(StaffCoverageGateway::class);
    $staffCoverageDateGateway = $container->get(StaffCoverageDateGateway::class);

    $data = [
        'timestampStatus' => date('Y-m-d H:i:s'),
        'notesStatus'     => $_POST['notesStatus'] ?? '',
        'status'          => 'Cancelled',
    ];

    // Validate the required values are present
    if (empty($pupilsightStaffCoverageID)) {
        $URL .= '&return=error1';
        header("Location: {$URL}");
        exit;
    }

    // Validate the database relationships exist
    $coverage = $staffCoverageGateway->getByID($pupilsightStaffCoverageID);

    if (empty($coverage)) {
        $URL .= '&return=error2';
        header("Location: {$URL}");
        exit;
    }

    // If the coverage is for a particular absence, ensure this exists
    if (!empty($coverage['pupilsightStaffAbsenceID'])) {
        $absence = $container->get(StaffAbsenceGateway::class)->getByID($coverage['pupilsightStaffAbsenceID']);
        if (empty($absence)) {
            $URL .= '&return=error2';
            header("Location: {$URL}");
            exit;
        }
    }

    // Prevent two people cancelling at the same time (?)
    if ($coverage['status'] != 'Requested' && $coverage['status'] != 'Accepted') {
        $URL .= '&return=error1';
        header("Location: {$URL}");
        exit;
    }

    // Update the database
    $updated = $staffCoverageGateway->update($pupilsightStaffCoverageID, $data);

    if (!$updated) {
        $URL .= '&return=error2';
        header("Location: {$URL}");
        exit;
    }

    $partialFail = false;

    $coverage = $staffCoverageGateway->getCoverageDetailsByID($pupilsightStaffCoverageID);
    $coverageDates = $staffCoverageDateGateway->selectDatesByCoverage($pupilsightStaffCoverageID);

    // Unlink any absence dates from the coverage request so they can be re-requested
    foreach ($coverageDates as $coverageDate) {
        $dateData = ['pupilsightStaffAbsenceDateID' => null];
        $partialFail &= !$staffCoverageDateGateway->update($coverageDate['pupilsightStaffCoverageDateID'], $dateData);
    }

    // Send messages (Mail, SMS) to relevant users
    if ($coverage['requestType'] == 'Individual') {
        $process = $container->get(CoverageNotificationProcess::class);
        $process->startCoverageCancelled($pupilsightStaffCoverageID);
    }

    $URLSuccess .= $partialFail
        ? "&return=warning1"
        : "&return=success0";

    header("Location: {$URLSuccess}");
}
