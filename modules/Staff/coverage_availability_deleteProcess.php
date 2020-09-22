<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Domain\Staff\StaffCoverageDateGateway;

require_once '../../pupilsight.php';

$pupilsightPersonID = $_REQUEST['pupilsightPersonID'] ?? '';
$pupilsightStaffCoverageDateID = $_REQUEST['pupilsightStaffCoverageDateID'] ?? '';

$URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/Staff/coverage_availability.php&pupilsightPersonID='.$pupilsightPersonID;

if (isActionAccessible($guid, $connection2, '/modules/Staff/coverage_availability.php') == false) {
    $URL .= '&return=error0';
    header("Location: {$URL}");
} elseif (empty($pupilsightPersonID) || empty($pupilsightStaffCoverageDateID)) {
    $URL .= '&return=error1';
    header("Location: {$URL}");
    exit;
} else {
    // Proceed!
    $staffCoverageDateGateway = $container->get(StaffCoverageDateGateway::class);

    $exceptionList = is_array($pupilsightStaffCoverageDateID)? $pupilsightStaffCoverageDateID : [$pupilsightStaffCoverageDateID];
    $partialFail = false;

    foreach ($exceptionList as $exceptionID) {
        $deleted = $staffCoverageDateGateway->delete($exceptionID);
        $partialFail &= !$deleted;
    }

    $URL .= $partialFail
        ? '&return=warning1'
        : '&return=success0';

    header("Location: {$URL}");
}
