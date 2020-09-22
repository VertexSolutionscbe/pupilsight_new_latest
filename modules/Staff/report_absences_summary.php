<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Forms\Form;
use Pupilsight\Domain\DataSet;
use Pupilsight\Tables\DataTable;
use Pupilsight\Tables\Prefab\ReportTable;
use Pupilsight\Domain\School\SchoolYearGateway;
use Pupilsight\Domain\Staff\StaffAbsenceGateway;
use Pupilsight\Domain\Staff\StaffAbsenceTypeGateway;
use Pupilsight\Domain\Staff\StaffGateway;
use Pupilsight\Services\Format;

if (isActionAccessible($guid, $connection2, '/modules/Staff/report_absences_summary.php') == false) {
    // Access denied
    $page->addError(__('You do not have access to this action.'));
} else {
    $viewMode = $_REQUEST['format'] ?? '';
    $dateFormat = $_SESSION[$guid]['i18n']['dateFormatPHP'];
    $pupilsightSchoolYearID = $_SESSION[$guid]['pupilsightSchoolYearID'];
    $pupilsightStaffAbsenceTypeID = $_GET['pupilsightStaffAbsenceTypeID'] ?? '';
    $month = $_GET['month'] ?? '';

    $schoolYearGateway = $container->get(SchoolYearGateway::class);
    $staffAbsenceGateway = $container->get(StaffAbsenceGateway::class);
    $staffAbsenceTypeGateway = $container->get(StaffAbsenceTypeGateway::class);

    // ABSENCE DATA
    $criteria = $staffAbsenceGateway->newQueryCriteria()
        ->filterBy('type', $pupilsightStaffAbsenceTypeID)
        ->pageSize(0)
        ->fromPOST();

    $schoolYear = $schoolYearGateway->getSchoolYearByID($pupilsightSchoolYearID);


    // Setup the date range for this school year
    $dateStart = new DateTime(substr($schoolYear['firstDay'], 0, 7).'-01');
    $dateEnd = new DateTime($schoolYear['lastDay']);

    $months = [];
    $dateRange = new DatePeriod($dateStart, new DateInterval('P1M'), $dateEnd);

    // Translated array of months in the current school year
    foreach ($dateRange as $monthDate) {
        $months[$monthDate->format('Y-m-d')] = Format::dateReadable($monthDate->format('Y-m-d'), '%B %Y');
    }

    // Setup the date range used for this report
    if (!empty($month)) {
        $monthDate = new DateTimeImmutable($month);
        $dateStart = $monthDate->modify('first day of this month');
        $dateEnd = $monthDate->modify('last day of this month');

        $dateRange = new DatePeriod($dateStart, new DateInterval('P1M'), $dateEnd);
    } else {
        $dateStart = new DateTime($schoolYear['firstDay']);
    }

    $absences = $staffAbsenceGateway->queryApprovedAbsencesByDateRange($criteria, $dateStart->format('Y-m-d'), $dateEnd->format('Y-m-d'), false);


    if (empty($viewMode)) {
        $page->breadcrumbs->add(__('Staff Absence Summary'));

        $form = Form::create('filter', $_SESSION[$guid]['absoluteURL'].'/index.php', 'get');
        $form->setTitle(__('Filter'));
        $form->setClass('noIntBorder fullWidth');

        $form->addHiddenValue('address', $_SESSION[$guid]['address']);
        $form->addHiddenValue('q', '/modules/Staff/report_absences_summary.php');

        $types = $staffAbsenceTypeGateway->selectAllTypes()->fetchAll();
        $types = array_combine(array_column($types, 'pupilsightStaffAbsenceTypeID'), array_column($types, 'name'));
        
        $row = $form->addRow();
        $row->addLabel('pupilsightStaffAbsenceTypeID', __('Type'));
        $row->addSelect('pupilsightStaffAbsenceTypeID')
                ->fromArray(['' => __('All')])
                ->fromArray($types)
                ->selected($pupilsightStaffAbsenceTypeID);

        $row = $form->addRow();
            $row->addLabel('month', __('Month'));
            $row->addSelect('month')->fromArray(['' => __('All')])->fromArray($months)->selected($month);

        $row = $form->addRow();
        $row->addFooter();
        $row->addSearchSubmit($pupilsight->session);

        echo $form->getOutput();

    
        // CALENDAR DATA
        $absencesByDate = array_reduce($absences->toArray(), function ($group, $item) {
            $group[$item['date']][] = $item;
            return $group;
        }, []);

        $calendar = [];
        $totalAbsence = 0;
        $maxAbsence = 0;

        foreach ($dateRange as $monthDate) {
            $days = [];
            for ($dayCount = 1; $dayCount <= $monthDate->format('t'); $dayCount++) {
                $date = new DateTime($monthDate->format('Y-m').'-'.$dayCount);
                $absenceCount = count($absencesByDate[$date->format('Y-m-d')] ?? []);

                $days[$dayCount] = [
                    'date'    => $date,
                    'number'  => $dayCount,
                    'count'   => $absenceCount,
                    'weekend' => $date->format('N') >= 6,
                ];
                $totalAbsence += $absenceCount;
                $maxAbsence = max($absenceCount, $maxAbsence);
            }

            $calendar[] = [
                'name'  => Format::dateReadable($monthDate->format('Y-m-d'), '%b'),
                'days'  => $days,
            ];
        }
        
        // CALENDAR TABLE
        $table = DataTable::createPaginated('staffAbsenceCalendar', $criteria);
        $table->setTitle(__('Staff Absence Summary'));
        $table->setDescription(__n('{count} Absence', '{count} Absences', $totalAbsence));
        $table->getRenderer()->addData('class', 'calendarTable border-collapse bg-transparent border-r-0');
        $table->addMetaData('hidePagination', true);
        $table->modifyRows(function ($values, $row) {
            return $row->setClass('bg-transparent');
        });

        $table->addColumn('name', '')->notSortable();

        $baseURL = isActionAccessible($guid, $connection2, '/modules/Staff/absences_manage.php')
            ? $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/Staff/absences_manage.php'
            : $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/Staff/report_absences.php';

        for ($dayCount = 1; $dayCount <= 31; $dayCount++) {
            $table->addColumn($dayCount, '')
                ->context('primary')
                ->notSortable()
                ->format(function ($month) use ($baseURL, $dayCount, $pupilsightStaffAbsenceTypeID, $dateFormat) {
                    $day = $month['days'][$dayCount] ?? null;
                    if (empty($day)) return '';
                    $dateText = $day['date']->format($dateFormat);
                    $url = $baseURL.'&dateStart='.$dateText.'&dateEnd='.$dateText.'&pupilsightStaffAbsenceTypeID='.$pupilsightStaffAbsenceTypeID;
                    $title = $day['date']->format('l');
                    $title .= '<br/>'.$day['date']->format('M j, Y');
                    if ($day['count'] > 0) {
                        $title .= '<br/>'.__n('{count} Absence', '{count} Absences', $day['count']);
                    }

                    return Format::link($url, $day['number'], $title);
                })
                ->modifyCells(function ($month, $cell) use ($dayCount, $maxAbsence) {
                    $day = $month['days'][$dayCount] ?? null;
                    if (empty($day)) return '';

                    $count = $day['count'] ?? 0;

                    $cell->addClass($day['date']->format('Y-m-d') == date('Y-m-d') ? 'border-2 border-gray' : 'border');
                    
                    if ($count > ceil($maxAbsence * 0.8)) $cell->addClass('bg-purple-800');
                    elseif ($count > ceil($maxAbsence * 0.5)) $cell->addClass('bg-purple-600');
                    elseif ($count > ceil($maxAbsence * 0.2)) $cell->addClass('bg-purple-400');
                    elseif ($count > 0) $cell->addClass('bg-purple-200');
                    elseif ($day['weekend']) $cell->addClass('bg-gray');
                    else $cell->addClass('bg-white');

                    $cell->addClass('h-3 sm:h-6');

                    return $cell;
                });
        }

        echo $table->render(new DataSet($calendar));
    }

    // DATA TABLE
    $staffGateway = $container->get(StaffGateway::class);
    $criteria = $staffGateway->newQueryCriteria()
        ->sortBy(['surname', 'preferredName'])
        ->pageSize(0)
        ->fromPOST();

    $absenceTypes = $staffAbsenceTypeGateway->selectAllTypes()->fetchAll();
    $types = array_fill_keys(array_column($absenceTypes, 'name'), null);
    
    $absencesByPerson = [];

    foreach ($absences as $absence) {
        $id = $absence['pupilsightPersonID'];
        if (empty($absencesByPerson[$id])) $absencesByPerson[$id] = $types;
        
        $absencesByPerson[$id][$absence['type']] += $absence['value'];
    }

    $allStaff = $staffGateway->queryAllStaff($criteria);

    $allStaff->transform(function (&$person) use ($absencesByPerson) {
        $id = $person['pupilsightPersonID'];
        if (isset($absencesByPerson[$id])) {
            $person = array_merge($person, $absencesByPerson[$id]);
            $person['total'] = array_sum($absencesByPerson[$id]);
        } else {
            $person['total'] = 0;
        }
    });

    
    
    // DATA TABLE
    $table = ReportTable::createPaginated('staffAbsences', $criteria)->setViewMode($viewMode, $pupilsight->session);
    $table->setTitle(__('Report'));
    $table->setDescription(Format::dateRangeReadable($dateStart->format('Y-m-d'), $dateEnd->format('Y-m-d')));

    if (isActionAccessible($guid, $connection2, '/modules/Staff/staff_view.php', 'View Staff Profile_full')) {
        $table->addMetaData('filterOptions', [
            'all:on'        => __('All Staff'),
            'type:teaching' => __('Staff Type').': '.__('Teaching'),
            'type:support'  => __('Staff Type').': '.__('Support'),
            'type:other'    => __('Staff Type').': '.__('Other'),
        ]);
    }

    // COLUMNS
    $table->addColumn('fullName', __('Name'))
        ->sortable(['surname', 'preferredName'])
        ->format(function ($person) use ($guid) {

            $text = Format::name($person['title'], $person['preferredName'], $person['surname'], 'Staff', true, true);
            $url = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/Staff/absences_view_byPerson.php&pupilsightPersonID='.$person['pupilsightPersonID'];
            $output = Format::link($url, $text);
            $output .= '<br/>'.Format::small($person['jobTitle']);
            return $output;
        });

    foreach ($absenceTypes as $type) {
        $table->addColumn($type['name'], $type['nameShort'])
            ->setTitle($type['name'])
            ->notSortable()
            ->width('10%');
    }

    $table->addColumn('total', __('Total'))->notSortable();
    


    echo $table->render($allStaff);
}
