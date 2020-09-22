<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Domain\Staff\StaffAbsenceGateway;
use Pupilsight\Domain\Staff\StaffAbsenceDateGateway;
use Pupilsight\Services\Format;

require_once '../../pupilsight.php';

$pupilsightStaffAbsenceID = $_POST['pupilsightStaffAbsenceID'] ?? '';

$URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/Staff/absences_manage_edit.php&pupilsightStaffAbsenceID='.$pupilsightStaffAbsenceID;

if (isActionAccessible($guid, $connection2, '/modules/Staff/absences_manage_edit.php') == false) {
    $URL .= '&return=error0';
    header("Location: {$URL}");
} elseif (empty($pupilsightStaffAbsenceID) || empty($_POST['date'])) {
    $URL .= '&return=error1';
    header("Location: {$URL}");
    exit;
} else {
    // Proceed!
    $staffAbsenceGateway = $container->get(StaffAbsenceGateway::class);
    $staffAbsenceDateGateway = $container->get(StaffAbsenceDateGateway::class);

    $values = $staffAbsenceGateway->getByID($pupilsightStaffAbsenceID);

    if (empty($values)) {
        $URL .= '&return=error2';
        header("Location: {$URL}");
        exit;
    }

    $date = Format::dateConvert($_POST['date']);

    if (!isSchoolOpen($guid, $date, $connection2)) {
        $URL .= '&return=error3';
        header("Location: {$URL}");
        exit;
    }

    $data = [
        'pupilsightStaffAbsenceID' => $pupilsightStaffAbsenceID,
        'date'                 => $date,
        'allDay'               => $_POST['allDay'] ?? 'N',
        'timeStart'            => $_POST['timeStart'] ?? null,
        'timeEnd'              => $_POST['timeEnd'] ?? null,
    ];

    if ($staffAbsenceDateGateway->unique($data, ['pupilsightStaffAbsenceID', 'date'])) {
        $inserted = $staffAbsenceDateGateway->insert($data);
    } else {
        $URL .= '&return=error1';
        header("Location: {$URL}");
        exit;
    }

    $URL .= !$inserted
        ? '&return=error2'
        : '&return=success0';

    header("Location: {$URL}");
}
