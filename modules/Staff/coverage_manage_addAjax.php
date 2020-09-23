<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Tables\DataTable;
use Pupilsight\Services\Format;
use Pupilsight\Domain\DataSet;
use Pupilsight\Domain\User\UserGateway;
use Pupilsight\Domain\Staff\SubstituteGateway;

require_once '../../pupilsight.php';

$request = [
    'dateStart' => $_POST['dateStart'] ?? '',
    'dateEnd'   => $_POST['dateEnd'] ?? '',
    'allDay'    => $_POST['allDay'] ?? 'N',
    'timeStart' => isset($_POST['timeStart']) ? $_POST['timeStart'].':00' : '',
    'timeEnd'   => isset($_POST['timeEnd']) ? $_POST['timeEnd'].':00' : '',
];

$pupilsightPersonIDCoverage = $_POST['pupilsightPersonIDCoverage'] ?? '';

if (isActionAccessible($guid, $connection2, '/modules/Staff/coverage_manage_add.php') == false) {
    die(Format::alert(__('Your request failed because you do not have access to this action.')));
} elseif (empty($request['dateStart']) || empty($request['dateEnd'])|| $pupilsightPersonIDCoverage == 'Please select...') {
    die();
} else {
    // Proceed!
    $substituteGateway = $container->get(SubstituteGateway::class);

    // DATA TABLE
    $substitute = $substituteGateway->selectBy(['pupilsightPersonID'=> $pupilsightPersonIDCoverage])->fetch();
    $person = $container->get(UserGateway::class)->getByID($pupilsightPersonIDCoverage);
    $unavailable = $substituteGateway->selectUnavailableDatesBySub($pupilsightPersonIDCoverage)->fetchGrouped();

    $start = new DateTime(Format::dateConvert($request['dateStart']).' 00:00:00');
    $end = new DateTime(Format::dateConvert($request['dateEnd']).' 23:00:00');

    $dates = [];
    $dateRange = new DatePeriod($start, new DateInterval('P1D'), $end);
    foreach ($dateRange as $date) {
        if (!isSchoolOpen($guid, $date->format('Y-m-d'), $connection2)) continue;
        
        $dates[] = ['date' => $date->format('Y-m-d')];
    }

    if (empty($dates) || empty($substitute)) {
        die();
    }

    $fullName = Format::name('', $person['preferredName'], $person['surname'], 'Staff', false, true);

    $table = DataTable::create('staffAbsenceDates');
    $table->setTitle(__('Availability'));
    $table->setDescription('<strong>'.$fullName.'</strong><br/><br/>'.$substitute['details']);
    $table->getRenderer()->addData('class', 'bulkActionForm');

    $table->modifyRows(function ($values, $row) {
        return $row->addClass('h-10');
    });

    $table->addColumn('dateLabel', __('Date'))
        ->format(Format::using('dateReadable', 'date'));

    $table->addCheckboxColumn('requestDates', 'date')
        ->width('15%')
        ->checked(true)
        ->format(function ($date) use (&$unavailable, &$request) {
            // Is this date unavailable: absent, already booked, or has an availability exception
            if (isset($unavailable[$date['date']])) {
                $times = $unavailable[$date['date']];

                foreach ($times as $time) {
                
                    // Handle full day and partial day unavailability
                    if ($time['allDay'] == 'Y' 
                    || ($time['allDay'] == 'N' && $request['allDay'] == 'Y')
                    || ($time['allDay'] == 'N' && $request['allDay'] == 'N'
                        && $time['timeStart'] < $request['timeEnd']
                        && $time['timeEnd'] > $request['timeStart'])) {
                        return Format::small(__($time['status'] ?? 'Not Available'));
                    }
                }
            }
        });

    echo $table->render(new DataSet($dates));
}
