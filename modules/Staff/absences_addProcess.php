<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Services\Format;
use Pupilsight\Domain\User\UserGateway;
use Pupilsight\Domain\Staff\StaffAbsenceGateway;
use Pupilsight\Domain\Staff\StaffAbsenceDateGateway;
use Pupilsight\Domain\Staff\StaffAbsenceTypeGateway;
use Pupilsight\Module\Staff\AbsenceNotificationProcess;

require_once '../../pupilsight.php';

$URL = $pupilsight->session->get('absoluteURL').'/index.php?q=/modules/Staff/absences_add.php';
$URLSuccess = $pupilsight->session->get('absoluteURL').'/index.php?q=/modules/Staff/absences_view_details.php';

if (isActionAccessible($guid, $connection2, '/modules/Staff/absences_add.php') == false) {
    $URL .= '&return=error0';
    header("Location: {$URL}");
    exit;
} else {
    // Proceed!
    $staffAbsenceGateway = $container->get(StaffAbsenceGateway::class);
    $staffAbsenceDateGateway = $container->get(StaffAbsenceDateGateway::class);
    $fullDayThreshold =  floatval(getSettingByScope($connection2, 'Staff', 'absenceFullDayThreshold'));
    $halfDayThreshold = floatval(getSettingByScope($connection2, 'Staff', 'absenceHalfDayThreshold'));

    $dateStart = $_POST['dateStart'] ?? '';
    $dateEnd = $_POST['dateEnd'] ?? '';
    $notificationList = !empty($_POST['notificationList'])? explode(',', $_POST['notificationList']) : [];
    $schoolClosedOverride = $_POST['schoolClosedOverride'] ?? '';

    $data = [
        'pupilsightSchoolYearID'       => $pupilsight->session->get('pupilsightSchoolYearID'),
        'pupilsightPersonID'           => $_POST['pupilsightPersonID'] ?? '',
        'pupilsightStaffAbsenceTypeID' => $_POST['pupilsightStaffAbsenceTypeID'] ?? '',
        'reason'                   => $_POST['reason'] ?? '',
        'comment'                  => $_POST['comment'] ?? '',
        'commentConfidential'      => $_POST['commentConfidential'] ?? '',
        'status'                   => 'Approved',
        'coverageRequired'         => $_POST['coverageRequired'] ?? 'N',
        'pupilsightPersonIDCreator'    => $pupilsight->session->get('pupilsightPersonID'),
        'notificationSent'         => 'N',
        'notificationList'         => json_encode($notificationList),
        'pupilsightGroupID'            => $_POST['pupilsightGroupID'] ?? null,
    ];

    // Validate the required values are present
    if (empty($data['pupilsightStaffAbsenceTypeID']) || empty($data['pupilsightPersonID']) || empty($dateStart) || empty($dateEnd)) {
        $URL .= '&return=error1';
        header("Location: {$URL}");
        exit;
    }

    // Validate the database relationships exist
    $type = $container->get(StaffAbsenceTypeGateway::class)->getByID($data['pupilsightStaffAbsenceTypeID']);
    $person = $container->get(UserGateway::class)->getByID($data['pupilsightPersonID']);

    if (empty($type) || empty($person)) {
        $URL .= '&return=error2';
        header("Location: {$URL}");
        exit;
    }

    // Is approval required? Record the name of the approver & update the status.
    if ($type['requiresApproval'] == 'Y') {
        $data['pupilsightPersonIDApproval'] = $_POST['pupilsightPersonIDApproval'] ?? '';
        $data['status'] = 'Pending Approval';

        if (empty($data['pupilsightPersonIDApproval'])) {
            $URL .= '&return=error1';
            header("Location: {$URL}");
            exit;
        }
    }

    // Create the absence
    $pupilsightStaffAbsenceID = $staffAbsenceGateway->insert($data);
    $pupilsightStaffAbsenceID = str_pad($pupilsightStaffAbsenceID, 14, '0', STR_PAD_LEFT);

    if (!$pupilsightStaffAbsenceID) {
        $URL .= '&return=error2';
        header("Location: {$URL}");
        exit;
    }

    $start = new DateTime(Format::dateConvert($dateStart).' 00:00:00');
    $end = new DateTime(Format::dateConvert($dateEnd).' 23:00:00');

    $dateRange = new DatePeriod($start, new DateInterval('P1D'), $end);
    $partialFail = false;
    $absenceCount = 0;

    // Create separate dates within the absence time span
    foreach ($dateRange as $date) {
        $dateData = [
            'pupilsightStaffAbsenceID' => $pupilsightStaffAbsenceID,
            'date'                 => $date->format('Y-m-d'),
            'allDay'               => $_POST['allDay'] ?? 'N',
            'timeStart'            => $_POST['timeStart'] ?? null,
            'timeEnd'              => $_POST['timeEnd'] ?? null,
        ];

        if ($dateData['allDay'] == 'Y') {
            $dateData['value'] = 1.0;
        } else {
            $start = new DateTime($date->format('Y-m-d').' '.$dateData['timeStart']);
            $end = new DateTime($date->format('Y-m-d').' '.$dateData['timeEnd']);

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

        if (!isSchoolOpen($guid, $dateData['date'], $connection2) && $schoolClosedOverride != 'Y') {
            continue;
        }

        if ($staffAbsenceDateGateway->unique($dateData, ['pupilsightStaffAbsenceID', 'date'])) {
            $partialFail &= !$staffAbsenceDateGateway->insert($dateData);
            $absenceCount++;
        } else {
            $partialFail = true;
        }
    }

    if ($absenceCount == 0) {
        $URL .= '&return=error8';
        header("Location: {$URL}");
        exit;
    }

    // Start a background process for notifications
    $process = $container->get(AbsenceNotificationProcess::class);
    if ($type['requiresApproval'] == 'Y') {
        $process->startAbsencePendingApproval($pupilsightStaffAbsenceID);
    } else {
        $process->startNewAbsence($pupilsightStaffAbsenceID);
    }

    // Redirect to coverage request
    if ($data['coverageRequired'] == 'Y') {
        $URL = $_SESSION[$guid]['absoluteURL']."/index.php?q=/modules/Staff/coverage_request.php&coverage=Y&pupilsightStaffAbsenceID=$pupilsightStaffAbsenceID";
        $URL .= '&return=success1';
        header("Location: {$URL}");
        exit;
    }

    $URLSuccess .= "&pupilsightStaffAbsenceID=$pupilsightStaffAbsenceID";
    $URLSuccess .= $partialFail
        ? "&return=warning1"
        : "&return=success0";

    header("Location: {$URLSuccess}");
    exit;
}
