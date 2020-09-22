<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Tables\DataTable;
use Pupilsight\Services\Format;
use Pupilsight\Domain\Staff\StaffAbsenceDateGateway;
use Pupilsight\Domain\Staff\SubstituteGateway;
use Pupilsight\Domain\User\UserGateway;
use Pupilsight\Forms\FormFactoryInterface;

require_once '../../pupilsight.php';

$pupilsightStaffAbsenceID = $_POST['pupilsightStaffAbsenceID'] ?? '';
$pupilsightPersonIDCoverage = $_POST['pupilsightPersonIDCoverage'] ?? '';

if (isActionAccessible($guid, $connection2, '/modules/Staff/coverage_request.php') == false) {
    die(Format::alert(__('You do not have access to this action.')));
} elseif (empty($pupilsightStaffAbsenceID) || empty($pupilsightPersonIDCoverage)|| $pupilsightPersonIDCoverage == 'Please select...') {
    die();
} else {
    // Proceed!
    $substituteGateway = $container->get(SubstituteGateway::class);
    $staffAbsenceDateGateway = $container->get(StaffAbsenceDateGateway::class);

    // DATA TABLE
    $substitute = $substituteGateway->selectBy(['pupilsightPersonID' => $pupilsightPersonIDCoverage])->fetch();
    $person = $container->get(UserGateway::class)->getByID($pupilsightPersonIDCoverage);
    $absenceDates = $staffAbsenceDateGateway->selectDatesByAbsence($pupilsightStaffAbsenceID)->toDataSet();
    $unavailable = $substituteGateway->selectUnavailableDatesBySub($pupilsightPersonIDCoverage)->fetchGrouped();

    if (empty($absenceDates) || empty($substitute) || empty($person)) {
        die();
    }

    if (empty($_POST['allDay']) && (empty($_POST['timeStart']) || empty($_POST['timeEnd']))) {
        die();
    }

    $absenceDates->transform(function (&$absence) use (&$unavailable, $pupilsightPersonIDCoverage) {
        // Has this date already been requested?
        if (!empty($absence['pupilsightStaffCoverageID'])) {
            $absence['unavailable'] = !empty($absence['preferredNameCoverage'])
                ? Format::name($absence['titleCoverage'], $absence['preferredNameCoverage'], $absence['surnameCoverage'], 'Staff', false, true)
                : __('Requested');
            return;
        }

        // Allow coverage request form to override absence times
        $absence['allDay'] = $_POST['allDay'] ?? 'N';
        $absence['timeStart'] = isset($_POST['timeStart']) ? $_POST['timeStart'].':00' : $absence['timeStart'];
        $absence['timeEnd'] = isset($_POST['timeEnd']) ? $_POST['timeEnd'].':00' : $absence['timeEnd'];

        // Is this date unavailable: absent, already booked, or has an availability exception
        if (isset($unavailable[$absence['date']])) {
            $times = $unavailable[$absence['date']];

            foreach ($times as $time) {
                // Handle full day and partial day unavailability
                if ($time['allDay'] == 'Y' 
                || ($time['allDay'] == 'N' && $absence['allDay'] == 'Y')
                || ($time['allDay'] == 'N' && $absence['allDay'] == 'N'
                    && $time['timeStart'] < $absence['timeEnd']
                    && $time['timeEnd'] > $absence['timeStart'])) {
                    $absence['unavailable'] = Format::small(__($time['status'] ?? 'Not Available'));
                }
            }
        }
    });

    $fullName = Format::name('', $person['preferredName'], $person['surname'], 'Staff', false, true);

    $table = DataTable::create('staffAbsenceDates');
    $table->setTitle(__('Availability'));
    $table->setDescription('<strong>'.$fullName.'</strong><br/><br/>'.$substitute['details']);
    $table->getRenderer()->addData('class', 'bulkActionForm');

    $table->modifyRows(function ($absence, $row) {
        if (!empty($absence['pupilsightStaffCoverageID'])) return; // Hide requested dates?
        return $row->addClass('h-10');
    });

    $table->addColumn('dateLabel', __('Date'))
        ->format(Format::using('dateReadable', 'date'));

    $table->addColumn('timeStart', __('Time'))
        ->width('50%')
        ->format(function ($absence) {
            return $absence['allDay'] == 'N'
                ? Format::small(Format::timeRange($absence['timeStart'], $absence['timeEnd']))
                : Format::small(__('All Day'));
        });

    $table->addCheckboxColumn('requestDates', 'date')
        ->width('15%')
        ->checked(true)
        ->format(function ($absence) {
            if (!empty($absence['unavailable'])) {
                return $absence['unavailable'];
            }
        });

    echo $table->render($absenceDates);
}
