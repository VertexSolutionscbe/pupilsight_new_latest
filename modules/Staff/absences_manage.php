<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Forms\Form;
use Pupilsight\Tables\DataTable;
use Pupilsight\Services\Format;
use Pupilsight\Domain\Staff\StaffAbsenceGateway;
use Pupilsight\Domain\Staff\StaffAbsenceTypeGateway;
use Pupilsight\Domain\Staff\StaffAbsenceDateGateway;
use Pupilsight\Module\Staff\Tables\AbsenceFormats;

if (isActionAccessible($guid, $connection2, '/modules/Staff/absences_manage.php') == false) {
    // Access denied
    $page->addError(__('You do not have access to this action.'));
} else {
    // Proceed!
    $page->breadcrumbs->add(__('Manage Staff Absences'));

    if (isset($_GET['return'])) {
        returnProcess($guid, $_GET['return'], null, null);
    }

    $pupilsightSchoolYearID = $_SESSION[$guid]['pupilsightSchoolYearID'];
    $pupilsightStaffAbsenceTypeID = $_GET['pupilsightStaffAbsenceTypeID'] ?? '';
    $search = $_GET['search'] ?? '';
    $dateStart = $_GET['dateStart'] ?? '';
    $dateEnd = $_GET['dateEnd'] ?? '';

    $staffAbsenceGateway = $container->get(StaffAbsenceGateway::class);
    $staffAbsenceTypeGateway = $container->get(StaffAbsenceTypeGateway::class);
    $staffAbsenceDateGateway = $container->get(StaffAbsenceDateGateway::class);

    $form = Form::create('filter', $_SESSION[$guid]['absoluteURL'] . '/index.php', 'get');
    $form->setTitle(__('Filter'));
    $form->setClass('noIntBorder fullWidth');

    $form->addHiddenValue('q', '/modules/Staff/absences_manage.php');

    $row = $form->addRow();
    $row->addLabel('search', __('Search'));
    $row->addTextField('search')->setValue($search);

    $row = $form->addRow();
    $row->addLabel('dateStart', __('Start Date'));
    $row->addDate('dateStart')->setValue($dateStart);

    $row = $form->addRow();
    $row->addLabel('dateEnd', __('End Date'));
    $row->addDate('dateEnd')->setValue($dateEnd);

    $types = $staffAbsenceTypeGateway->selectAllTypes()->fetchAll();
    $types = array_combine(array_column($types, 'pupilsightStaffAbsenceTypeID'), array_column($types, 'name'));

    $row = $form->addRow();
    $row->addLabel('pupilsightStaffAbsenceTypeID', __('Type'));
    $row->addSelect('pupilsightStaffAbsenceTypeID')
        ->fromArray(['' => __('All')])
        ->fromArray($types)
        ->selected($pupilsightStaffAbsenceTypeID);

    $row = $form->addRow();
    $row->addFooter();
    $row->addSearchSubmit($pupilsight->session, __('Clear Filters'));

    echo $form->getOutput();


    // QUERY
    $criteria = $staffAbsenceGateway->newQueryCriteria()
        ->searchBy($staffAbsenceGateway->getSearchableColumns(), $search)
        ->sortBy('date', 'ASC')
        ->filterBy('dateStart', Format::dateConvert($dateStart))
        ->filterBy('dateEnd', Format::dateConvert($dateEnd))
        ->filterBy('type', $pupilsightStaffAbsenceTypeID);

    // $criteria->filterBy('date', !$criteria->hasFilter() && !$criteria->hasSearchText() ? 'upcoming' : '')
    //     ->fromPOST();

    $absences = $staffAbsenceGateway->queryAbsencesBySchoolYear($criteria, $pupilsightSchoolYearID, true);

    // Join a set of coverage data per absence
    $absenceIDs = $absences->getColumn('pupilsightStaffAbsenceID');
    $coverageData = $staffAbsenceDateGateway->selectDatesByAbsence($absenceIDs)->fetchGrouped();
    $absences->joinColumn('pupilsightStaffAbsenceID', 'coverageList', $coverageData);

    // DATA TABLE
    $table = DataTable::createPaginated('staffAbsences', $criteria);
    $table->setTitle(__('View'));

    $table->modifyRows(function ($absence, $row) {
        if ($absence['status'] == 'Pending Approval') $row->addClass('warning');
        if ($absence['status'] == 'Declined') $row->addClass('error');
        return $row;
    });

    if (isActionAccessible($guid, $connection2, '/modules/Staff/report_absences_summary.php')) {
        $table->addHeaderAction('view', __('View'))
            ->setIcon('planner')
            ->setURL('/modules/Staff/report_absences_summary.php')
            ->displayLabel()
            ->append('&nbsp;|&nbsp;');
    }

    $table->addHeaderAction('add', __('New Absence'))
        ->setURL('/modules/Staff/absences_add.php')
        ->addParam('pupilsightPersonID', '')
        ->addParam('date', $dateStart)
        ->displayLabel();

    $table->addMetaData('filterOptions', [
        'date:upcoming'    => __('Upcoming'),
        'date:today'       => __('Today'),
        'date:past'        => __('Past'),

        'status:pending approval' => __('Status') . ': ' . __('Pending Approval'),
        'status:approved'         => __('Status') . ': ' . __('Approved'),
        'status:declined'         => __('Status') . ': ' . __('Declined'),
        'coverage:requested'      => __('Coverage') . ': ' . __('Requested'),
        'coverage:accepted'       => __('Coverage') . ': ' . __('Accepted'),
    ]);

    // COLUMNS
    $table->addColumn('fullName', __('Name'))
        ->sortable(['surname', 'preferredName'])
        ->format(function ($absence) {
            $text = Format::name($absence['title'], $absence['preferredName'], $absence['surname'], 'Staff', false, true);
            $url = './index.php?q=/modules/Staff/absences_view_byPerson.php&pupilsightPersonID=' . $absence['pupilsightPersonID'];

            return Format::link($url, $text);
        });

    $table->addColumn('date', __('Date'))
        ->width('18%')
        ->format([AbsenceFormats::class, 'dateDetails']);

    $table->addColumn('type', __('Type'))
        ->description(__('Reason'))
        ->format([AbsenceFormats::class, 'typeAndReason']);

    $table->addColumn('coverage', __('Coverage'))
        ->format([AbsenceFormats::class, 'coverageList']);

    $table->addColumn('timestampCreator', __('Created'))
        ->format([AbsenceFormats::class, 'createdOn']);

    // ACTIONS
    $table->addActionColumn()
        ->addParam('search', $criteria->getSearchText(true))
        ->addParam('pupilsightStaffAbsenceID')
        ->format(function ($absence, $actions) {
            $actions->addAction('view', __('View Details'))
                ->isModal(800, 550)
                ->setURL('/modules/Staff/absences_view_details.php');

            $actions->addAction('edit', __('Edit'))
                ->setURL('/modules/Staff/absences_manage_edit.php');

            $actions->addAction('delete', __('Delete'))
                ->setURL('/modules/Staff/absences_manage_delete.php');
        });

    echo $table->render($absences);
}
