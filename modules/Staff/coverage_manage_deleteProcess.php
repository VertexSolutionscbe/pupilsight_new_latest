<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Domain\Staff\StaffCoverageGateway;
use Pupilsight\Domain\Staff\StaffCoverageDateGateway;

require_once '../../pupilsight.php';

$pupilsightStaffCoverageID = $_GET['pupilsightStaffCoverageID'] ?? '';
$search = $_POST['search'] ?? '';

$URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/Staff/coverage_manage.php&search='.$search;

if (isActionAccessible($guid, $connection2, '/modules/Staff/coverage_manage_delete.php') == false) {
    $URL .= '&return=error0';
    header("Location: {$URL}");
} elseif (empty($pupilsightStaffCoverageID)) {
    $URL .= '&return=error1';
    header("Location: {$URL}");
    exit;
} else {
    // Proceed!
    $staffCoverageGateway = $container->get(StaffCoverageGateway::class);
    $staffCoverageDateGateway = $container->get(StaffCoverageDateGateway::class);
    $values = $staffCoverageGateway->getByID($pupilsightStaffCoverageID);

    if (empty($values)) {
        $URL .= '&return=error2';
        header("Location: {$URL}");
        exit;
    }

    $coverageDates = $staffCoverageDateGateway->selectDatesByCoverage($pupilsightStaffCoverageID)->fetchAll();
    $partialFail = false;

    // Delete each date first
    foreach ($coverageDates as $date) {
        $partialFail &= !$staffCoverageDateGateway->delete($date['pupilsightStaffCoverageDateID']);
    }

    // Then delete the coverage itself
    $partialFail &= $staffCoverageGateway->delete($pupilsightStaffCoverageID);

    $URL .= $partialFail
        ? '&return=warning1'
        : '&return=success0';

    header("Location: {$URL}");
}
