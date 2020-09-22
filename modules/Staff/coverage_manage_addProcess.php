<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Domain\Staff\StaffCoverageGateway;
use Pupilsight\Domain\Staff\SubstituteGateway;
use Pupilsight\Domain\Staff\StaffCoverageDateGateway;
use Pupilsight\Module\Staff\CoverageNotificationProcess;

require_once '../../pupilsight.php';

$URL = $pupilsight->session->get('absoluteURL').'/index.php?q=/modules/Staff/coverage_manage_add.php';

if (isActionAccessible($guid, $connection2, '/modules/Staff/coverage_manage_add.php') == false) {
    $URL .= '&return=error0';
    header("Location: {$URL}");
    exit;
} else {
    // Proceed!
    $staffCoverageGateway = $container->get(StaffCoverageGateway::class);
    $staffCoverageDateGateway = $container->get(StaffCoverageDateGateway::class);
    $fullDayThreshold =  floatval(getSettingByScope($connection2, 'Staff', 'absenceFullDayThreshold'));
    $halfDayThreshold = floatval(getSettingByScope($connection2, 'Staff', 'absenceHalfDayThreshold'));
    
    $requestDates = $_POST['requestDates'] ?? [];

    $data = [
        'pupilsightSchoolYearID'     => $pupilsight->session->get('pupilsightSchoolYearID'),
        'pupilsightPersonIDStatus'   => $pupilsight->session->get('pupilsightPersonID'),
        'pupilsightPersonIDCoverage' => $_POST['pupilsightPersonIDCoverage'] ?? null,
        'pupilsightPersonID'         => $_POST['pupilsightPersonID'] ?? '',
        'notesStatus'            => $_POST['notesStatus'] ?? '',
        'status'                 => $_POST['status'] ?? '',
        'requestType'            => 'Individual',
        'notificationSent'       => 'N',
    ];

    // Validate the required values are present
    if (empty($data['pupilsightPersonIDCoverage']) || empty($requestDates)) {
        $URL .= '&return=error1';
        header("Location: {$URL}");
        exit;
    }

    // Validate the database relationships exist
    $substitute = $container->get(SubstituteGateway::class)->selectBy(['pupilsightPersonID'=> $data['pupilsightPersonIDCoverage']])->fetch();

    if (empty($substitute)) {
        $URL .= '&return=error2';
        header("Location: {$URL}");
        exit;
    }

    // Create the coverage request
    $pupilsightStaffCoverageID = $staffCoverageGateway->insert($data);

    if (!$pupilsightStaffCoverageID) {
        $URL .= '&return=error2';
        header("Location: {$URL}");
        exit;
    }

    $partialFail = false;
    $dateCount = 0;

    // Create separate dates within the coverage time span
    foreach ($requestDates as $date) {
        if (!isSchoolOpen($guid, $date, $connection2)) {
            continue;
        }

        $dateData = [
            'pupilsightStaffCoverageID' => $pupilsightStaffCoverageID,
            'date'                  => $date,
            'allDay'                => $_POST['allDay'] ?? 'N',
            'timeStart'             => $_POST['timeStart'] ?? null,
            'timeEnd'               => $_POST['timeEnd'] ?? null,
        ];

        if ($dateData['allDay'] == 'Y') {
            $dateData['value'] = 1.0;
        } else {
            $start = new DateTime($date.' '.$dateData['timeStart']);
            $end = new DateTime($date.' '.$dateData['timeEnd']);

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
            $dateCount++;
        } else {
            $partialFail = true;
        }
    }

    if ($dateCount == 0) {
        $URL .= '&return=error1';
        header("Location: {$URL}");
        exit;
    }

    // Send messages (Mail, SMS) to relevant users
    if ($data['status'] == 'Requested') {
        $process = $container->get(CoverageNotificationProcess::class);
        $process->startIndividualRequest($pupilsightStaffCoverageID);
    }
    
    $URL .= $partialFail
        ? "&return=warning1"
        : "&return=success0";

    header("Location: {$URL}");
}
