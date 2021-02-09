<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\UI\Chart\Chart;
use Pupilsight\Services\Format;
use Pupilsight\Domain\Staff\StaffAbsenceGateway;
use Pupilsight\Domain\Staff\StaffAbsenceDateGateway;
use Pupilsight\Tables\DataTable;
use Pupilsight\Forms\Form;
use Pupilsight\Domain\Staff\StaffAbsenceTypeGateway;
use Pupilsight\Domain\DataSet;
use Pupilsight\Module\Staff\Tables\AbsenceFormats;

if (isActionAccessible($guid, $connection2, '/modules/Staff/report_absences_weekly.php') == false) {
    // Access denied
    $page->addError(__('You do not have access to this action.'));
} else {
    $page->breadcrumbs->add(__('Weekly Absences'));

    $page->scripts->add('chart');

    $dateFormat = $_SESSION[$guid]['i18n']['dateFormatPHP'];
    $date = isset($_REQUEST['dateStart']) ? DateTimeImmutable::createFromFormat($dateFormat, $_REQUEST['dateStart']) : new DateTimeImmutable();

    $staffAbsenceGateway = $container->get(StaffAbsenceGateway::class);
    $staffAbsenceDateGateway = $container->get(StaffAbsenceDateGateway::class);
    $staffAbsenceTypeGateway = $container->get(StaffAbsenceTypeGateway::class);

    // DATE SELECTOR
    $form = Form::create('action', $_SESSION[$guid]['absoluteURL'] . '/index.php?q=/modules/Staff/report_absences_weekly.php');
    $form->setClass('blank fullWidth');
    $form->addHiddenValue('address', $_SESSION[$guid]['address']);

    $row = $form->addRow()->addClass('flex flex-wrap');

    $link = $_SESSION[$guid]['absoluteURL'] . '/index.php?q=/modules/Staff/report_absences_weekly.php';
    $lastWeek = $date->modify('-1 week')->format($dateFormat);
    $thisWeek = (new DateTime('Today'))->format($dateFormat);
    $nextWeek = $date->modify('+1 week')->format($dateFormat);

    $col = $row->addColumn()->addClass('flex items-center ');
    $col->addButton(__('Last Week'))->addClass('')->onClick("window.location.href='{$link}&dateStart={$lastWeek}'");
    $col->addButton(__('This Week'))->addClass('ml-px')->onClick("window.location.href='{$link}&dateStart={$thisWeek}'");
    $col->addButton(__('Next Week'))->addClass('ml-px')->onClick("window.location.href='{$link}&dateStart={$nextWeek}'");

    $col = $row->addColumn()->addClass('flex items-center justify-end');
    $col->addDate('dateStart')->setValue($date->format($dateFormat))->setClass('shortWidth');
    $col->addSubmit(__('Go'));

    echo $form->getOutput();

    // SETUP DAYS OF WEEK
    $sql = "SELECT name, nameShort FROM pupilsightDaysOfWeek WHERE schoolDay='Y' ORDER BY sequenceNumber";
    $result = $pdo->select($sql)->fetchAll();

    $weekdays = array_map(function ($weekday) use ($date) {
        $weekday['date'] = $date->modify($weekday['name'] . ' this week');
        return $weekday;
    }, $result);

    $weekdayNames = array_map('__', array_column($weekdays, 'nameShort'));

    $dateStart =  $weekdays[0]['date'];
    $dateEnd = $weekdays[count($weekdays) - 1]['date'];

    // BAR GRAPH
    $chartConfig = [
        'height' => '70',
        'tooltips' => [
            'mode' => 'x-axis',
        ],
        'scales' => [
            'yAxes' => [[
                'stacked' => true,
                'display' => false,
                'ticks'     => ['stepSize' => 1, 'suggestedMax' => 5],
            ]],
            'xAxes' => [[
                'display'   => true,
                'stacked'   => true,
                'gridLines' => ['display' => false],
            ]],
        ],
    ];

    // QUERY
    $criteria = $staffAbsenceGateway->newQueryCriteria()
        ->sortBy('date')
        ->sortBy('sequenceNumber')
        ->pageSize(0)
        ->fromPOST();

    $absencesThisWeek = $staffAbsenceGateway->queryApprovedAbsencesByDateRange($criteria, $dateStart->format('Y-m-d'), $dateEnd->format('Y-m-d'), false)->toArray();
    $absenceTypes = $staffAbsenceTypeGateway->selectAllTypes()->fetchAll();

    $listData = [];
    $chartData = array_fill_keys($weekdayNames, 0);

    foreach ($absencesThisWeek as $absence) {
        $weekday = __(DateTime::createFromFormat('Y-m-d', $absence['date'])->format('D'));
        $chartData[$weekday] += 1;
        $listData[$absence['date']][] = $absence;
    }

    $barGraph = Chart::create('staffAbsences', 'bar')
        ->setTitle(Format::dateRangeReadable($dateStart->format('Y-m-d'), $dateEnd->format('Y-m-d')))
        ->setOptions($chartConfig)
        ->setLabels($weekdayNames)
        ->setLegend(false)
        ->setColors(['hsl(260, 90%, 70%)']);

    $barGraph->addDataset('absent', __('Absent'))->setData($chartData);

    echo '<div style="overflow: visible;">' . $barGraph->render() . '</div>';

    if (empty($listData)) {
        echo Format::alert(__('There are no absences for this time period.'), 'message');
        return;
    }

    foreach ($weekdays as $weekday) {
        $date = $weekday['date'];

        $absencesThisDay = $listData[$date->format('Y-m-d')] ?? [];

        if (empty($absencesThisDay)) {
            continue;
        }

        if (!isSchoolOpen($guid, $date->format('Y-m-d'), $connection2)) {
            echo '<h2>' . __(Format::dateReadable($date->format('Y-m-d'), '%A')) . '</h2>';
            echo Format::alert(__('School is closed on the specified day.'));
            continue;
        }

        $table = DataTable::create('staffAbsences' . $date->format('D'));
        $table->setTitle(__(Format::dateReadable($date->format('Y-m-d'), '%A')));
        $table->setDescription(Format::dateReadable($date->format('Y-m-d')));

        $canView = isActionAccessible($guid, $connection2, '/modules/Staff/absences_view_byPerson.php', 'View Absences_any');

        // COLUMNS
        $table->addColumn('fullName', __('Name'))
            ->width('30%')
            ->sortable(['surname', 'preferredName'])
            ->format(function ($absence) use ($guid, $canView) {
                $output = Format::name($absence['title'], $absence['preferredName'], $absence['surname'], 'Staff', false, true);

                if ($canView) {
                    $output = Format::link($_SESSION[$guid]['absoluteURL'] . '/index.php?q=/modules/Staff/absences_view_byPerson.php&pupilsightPersonID=' . $absence['pupilsightPersonID'], $output);
                }

                if ($absence['allDay'] != 'Y') {
                    $output .= '<br/>' . Format::small(Format::timeRange($absence['timeStart'], $absence['timeEnd']));
                }
                return $output;
            });

        if (isActionAccessible($guid, $connection2, '/modules/Staff/report_subs_availability.php')) {
            $table->addColumn('coverage', __('Coverage'))
                ->width('30%')
                ->format([AbsenceFormats::class, 'coverage']);
        }

        echo $table->render(new DataSet($absencesThisDay));
    }
}
