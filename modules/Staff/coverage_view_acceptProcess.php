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

$URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/Staff/coverage_view_accept.php&pupilsightStaffCoverageID='.$pupilsightStaffCoverageID;
$URLSuccess = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/Staff/coverage_my.php';

if (isActionAccessible($guid, $connection2, '/modules/Staff/coverage_view_accept.php') == false) {
    $URL .= '&return=error0';
    header("Location: {$URL}");
    exit;
} else {
    // Proceed!
    $staffCoverageGateway = $container->get(StaffCoverageGateway::class);
    $staffCoverageDateGateway = $container->get(StaffCoverageDateGateway::class);

    $requestDates = $_POST['coverageDates'] ?? [];

    $data = [
        'pupilsightPersonIDCoverage' => $_SESSION[$guid]['pupilsightPersonID'],
        'timestampCoverage'      => date('Y-m-d H:i:s'),
        'notesCoverage'          => $_POST['notesCoverage'],
        'status'                 => 'Accepted',
    ];

    // Validate the required values are present
    if (empty($pupilsightStaffCoverageID) || empty($data['pupilsightPersonIDCoverage']) || empty($requestDates)) {
        $URL .= '&return=error1';
        header("Location: {$URL}");
        exit;
    }

    // Validate the database relationships exist
    $coverage = $staffCoverageGateway->getByID($pupilsightStaffCoverageID);
    $substitute = $container->get(SubstituteGateway::class)->getSubstituteByPerson($data['pupilsightPersonIDCoverage']);

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

    // Prevent two people accepting at the same time
    if ($coverage['status'] != 'Requested') {
        $URL .= '&return=warning3';
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

    $coverageDates = $staffCoverageDateGateway->selectDatesByCoverage($pupilsightStaffCoverageID);
    $uncoveredDates = [];

    // Remove any coverage dates from the coverage request if they were not selected
    foreach ($coverageDates as $date) {
        if (!in_array($date['date'], $requestDates)) {
            $uncoveredDates[] = $date['date'];
            $partialFail &= !$staffCoverageDateGateway->delete($date['pupilsightStaffCoverageDateID']);
        }
    }

    // Send messages (Mail, SMS) to relevant users
    $process = $container->get(CoverageNotificationProcess::class);
    $process->startCoverageAccepted($pupilsightStaffCoverageID, $uncoveredDates);


    $URLSuccess .= $partialFail
        ? "&return=warning1"
        : "&return=success0";

    header("Location: {$URLSuccess}");
}
