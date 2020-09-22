<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Domain\Staff\StaffAbsenceGateway;
use Pupilsight\Domain\Staff\StaffCoverageDateGateway;
use Pupilsight\Domain\Staff\StaffCoverageGateway;
use Pupilsight\Domain\Staff\SubstituteGateway;
use Pupilsight\Module\Staff\CoverageNotificationProcess;

require_once '../../pupilsight.php';

$pupilsightStaffCoverageID = $_POST['pupilsightStaffCoverageID'] ?? '';

$URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/Staff/coverage_view_decline.php&pupilsightStaffCoverageID='.$pupilsightStaffCoverageID;
$URLSuccess = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/Staff/coverage_my.php';

if (isActionAccessible($guid, $connection2, '/modules/Staff/coverage_view_decline.php') == false) {
    $URL .= '&return=error0';
    header("Location: {$URL}");
    exit;
} else {
    // Proceed!
    $staffCoverageGateway = $container->get(StaffCoverageGateway::class);
    $staffCoverageDateGateway = $container->get(StaffCoverageDateGateway::class);
    $substituteGateway = $container->get(SubstituteGateway::class);

    $markAsUnavailable = $_POST['markAsUnavailable'] ?? false;

    $data = [
        'timestampCoverage'      => date('Y-m-d H:i:s'),
        'notesCoverage'          => $_POST['notesCoverage'],
        'status'                 => 'Declined',
    ];

    // Validate the required values are present
    if (empty($pupilsightStaffCoverageID)) {
        $URL .= '&return=error1';
        header("Location: {$URL}");
        exit;
    }

    // Validate the database relationships exist
    $coverage = $staffCoverageGateway->getByID($pupilsightStaffCoverageID);
    $substitute = $substituteGateway->getSubstituteByPerson($coverage['pupilsightPersonIDCoverage'] ?? '');

    if (empty($coverage) || empty($substitute)) {
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

    // Prevent two people declining at the same time (?)
    if ($coverage['status'] != 'Requested') {
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
        $dateData = [
            'pupilsightStaffAbsenceDateID' => '',
            'pupilsightPersonIDUnavailable' => $markAsUnavailable ? $coverage['pupilsightPersonIDCoverage'] : '',
        ];

        $partialFail &= !$staffCoverageDateGateway->update($coverageDate['pupilsightStaffCoverageDateID'], $dateData);
    }

    // Send messages (Mail, SMS) to relevant users
    $process = $container->get(CoverageNotificationProcess::class);
    $process->startCoverageDeclined($pupilsightStaffCoverageID);


    $URLSuccess .= $partialFail
        ? "&return=warning1"
        : "&return=success0";

    header("Location: {$URLSuccess}");
}
