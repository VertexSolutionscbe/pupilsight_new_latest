<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Domain\User\UserGateway;
use Pupilsight\Domain\Staff\StaffAbsenceGateway;
use Pupilsight\Domain\Staff\StaffAbsenceDateGateway;
use Pupilsight\Domain\Staff\StaffCoverageGateway;
use Pupilsight\Domain\Staff\StaffCoverageDateGateway;
use Pupilsight\Module\Staff\CoverageNotificationProcess;

require_once '../../pupilsight.php';

$pupilsightStaffAbsenceID = $_POST['pupilsightStaffAbsenceID'] ?? '';

$URL = $pupilsight->session->get('absoluteURL').'/index.php?q=/modules/Staff/coverage_request.php&pupilsightStaffAbsenceID='.$pupilsightStaffAbsenceID;
$URLSuccess = isActionAccessible($guid, $connection2, '/modules/Staff/coverage_view_edit.php')
    ? $pupilsight->session->get('absoluteURL').'/index.php?q=/modules/Staff/coverage_view_edit.php&pupilsightStaffAbsenceID='.$pupilsightStaffAbsenceID
    : $pupilsight->session->get('absoluteURL').'/index.php?q=/modules/Staff/coverage_view_details.php&pupilsightStaffAbsenceID='.$pupilsightStaffAbsenceID;

if (isActionAccessible($guid, $connection2, '/modules/Staff/coverage_request.php') == false) {
    $URL .= '&return=error0';
    header("Location: {$URL}");
    exit;
} else {
    // Proceed!
    $staffCoverageGateway = $container->get(StaffCoverageGateway::class);
    $staffCoverageDateGateway = $container->get(StaffCoverageDateGateway::class);
    $staffAbsenceDateGateway = $container->get(StaffAbsenceDateGateway::class);
    $fullDayThreshold =  floatval(getSettingByScope($connection2, 'Staff', 'absenceFullDayThreshold'));
    $halfDayThreshold = floatval(getSettingByScope($connection2, 'Staff', 'absenceHalfDayThreshold'));

    $requestDates = $_POST['requestDates'] ?? [];
    $substituteTypes = $_POST['substituteTypes'] ?? [];

    // Validate the database relationships exist
    $absence = $container->get(StaffAbsenceGateway::class)->getByID($pupilsightStaffAbsenceID);

    if (empty($absence)) {
        $URL .= '&return=error2';
        header("Location: {$URL}");
        exit;
    }

    $data = [
        'pupilsightStaffAbsenceID'   => $pupilsightStaffAbsenceID,
        'pupilsightSchoolYearID'     => $pupilsight->session->get('pupilsightSchoolYearID'),
        'pupilsightPersonIDStatus'   => $pupilsight->session->get('pupilsightPersonID'),
        'pupilsightPersonID'         => $absence['pupilsightPersonID'],
        'pupilsightPersonIDCoverage' => $_POST['pupilsightPersonIDCoverage'] ?? null,
        'notesStatus'            => $_POST['notesStatus'] ?? '',
        'requestType'            => $_POST['requestType'] ?? '',
        'substituteTypes'        => implode(',', $substituteTypes),
        'status'                 => 'Requested',
        'notificationSent'       => 'N',
    ];

    // Validate the required values are present
    if (empty($data['pupilsightStaffAbsenceID']) || !($data['requestType'] == 'Individual' || $data['requestType'] == 'Broadcast')) {
        $URL .= '&return=error1';
        header("Location: {$URL}");
        exit;
    }
    
    if ($data['requestType'] == 'Individual') {
        // Return a custom error message if no dates have been selected
        if (empty($requestDates)) {
            $URL .= '&return=error8';
            header("Location: {$URL}");
            exit;
        }

        // Ensure the person is selected & exists for Individual coverage requests
        $personCoverage = $container->get(UserGateway::class)->getByID($data['pupilsightPersonIDCoverage']);
        if (empty($personCoverage)) {
            $URL .= '&return=error2';
            header("Location: {$URL}");
            exit;
        }
    }

    // Create the coverage request
    $pupilsightStaffCoverageID = $staffCoverageGateway->insert($data);
    $pupilsightStaffCoverageID = str_pad($pupilsightStaffCoverageID, 14, '0', STR_PAD_LEFT);

    if (!$pupilsightStaffCoverageID) {
        $URL .= '&return=error2';
        header("Location: {$URL}");
        exit;
    }

    $partialFail = false;

    $absenceDates = $staffAbsenceDateGateway->selectDatesByAbsence($data['pupilsightStaffAbsenceID']);

    // Create a coverage date for each absence date, allow coverage request form to override absence times
    foreach ($absenceDates as $absenceDate) {
        // Skip any absence dates that have already been covered
        if (!empty($absenceDate['pupilsightStaffCoverageID'])) {
            continue;
        }

        // Skip dates that were not selected for Individual requests
        if ($data['requestType'] == 'Individual' && !in_array($absenceDate['date'], $requestDates)) {
            continue;
        }

        $dateData = [
            'pupilsightStaffCoverageID'    => $pupilsightStaffCoverageID,
            'pupilsightStaffAbsenceDateID' => $absenceDate['pupilsightStaffAbsenceDateID'],
            'date'      => $absenceDate['date'],
            'allDay'    => $_POST['allDay'] ?? 'N',
            'timeStart' => $_POST['timeStart'] ?? $absenceDate['timeStart'],
            'timeEnd'   => $_POST['timeEnd'] ?? $absenceDate['timeEnd'],
        ];

        // Calculate the day 'value' of each date, based on thresholds from Staff Settings.
        if ($dateData['allDay'] == 'Y') {
            $dateData['value'] = 1.0;
        } else {
            $start = new DateTime($absenceDate['date'].' '.$dateData['timeStart']);
            $end = new DateTime($absenceDate['date'].' '.$dateData['timeEnd']);

            $timeDiff = $end->getTimestamp() - $start->getTimestamp();
            $hoursAbsent = abs($timeDiff / 3600);
            
            if ($hoursAbsent < $halfDayThreshold) {
                $dateData['value'] = 0.0;
            } elseif ($hoursAbsent < $fullDayThreshold) {
                $dateData['value'] = 0.5;
            } else {
                $dateData['value'] = 1.0;
            }
        }

        if ($staffCoverageDateGateway->unique($dateData, ['pupilsightStaffCoverageID', 'date'])) {
            $partialFail &= !$staffCoverageDateGateway->insert($dateData);
        } else {
            $partialFail = true;
        }
    }

    // Send messages (Mail, SMS) to relevant users
    $process = $container->get(CoverageNotificationProcess::class);
    if ($data['requestType'] == 'Broadcast') {
        $process->startBroadcastRequest($pupilsightStaffCoverageID);
    } else {
        $process->startIndividualRequest($pupilsightStaffCoverageID);
    }
    
    $URLSuccess .= $partialFail
        ? "&return=warning1"
        : "&return=success0";

    header("Location: {$URLSuccess}&pupilsightStaffCoverageID={$pupilsightStaffCoverageID}");
}
