<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Forms\Form;
use Pupilsight\Tables\DataTable;
use Pupilsight\Services\Format;
use Pupilsight\Domain\Staff\StaffCoverageGateway;
use Pupilsight\Module\Staff\Tables\AbsenceFormats;

if (isActionAccessible($guid, $connection2, '/modules/Staff/coverage_manage.php') == false) {
    // Access denied
    $page->addError(__('You do not have access to this action.'));
} else {
    // Proceed!
    $page->breadcrumbs->add(__('Manage Staff Coverage'));

    if (isset($_GET['return'])) {
        returnProcess($guid, $_GET['return'], null, null);
    }

    $pupilsightSchoolYearID = $_SESSION[$guid]['pupilsightSchoolYearID'];
    $search = $_GET['search'] ?? '';

    $urgencyThreshold = getSettingByScope($connection2, 'Staff', 'urgencyThreshold');
    $StaffCoverageGateway = $container->get(StaffCoverageGateway::class);
    
    // SEARCH FORM
    $form = Form::create('filter', $_SESSION[$guid]['absoluteURL'].'/index.php', 'get');
    $form->setTitle(__('Filter'));
    $form->setClass('noIntBorder fullWidth');

    $form->addHiddenValue('q', '/modules/Staff/coverage_manage.php');

    $row = $form->addRow();
        $row->addLabel('search', __('Search'));
        $row->addTextField('search')->setValue($search);

    $row = $form->addRow();
        $row->addFooter();
        $row->addSearchSubmit($pupilsight->session);

    echo $form->getOutput();


    // QUERY
    $criteria = $StaffCoverageGateway
        ->newQueryCriteria()
        ->searchBy($StaffCoverageGateway->getSearchableColumns(), $search);

    if (!$criteria->hasFilter() && !$criteria->hasSearchText()) {
        $criteria->filterBy('date', 'upcoming')
                 ->sortBy('status', 'ASC');
    }
    
    $criteria->sortBy('date', 'ASC')
             ->fromPOST();

    $coverage = $StaffCoverageGateway->queryCoverageBySchoolYear($criteria, $pupilsightSchoolYearID, true);

    // DATA TABLE
    $table = DataTable::createPaginated('staffCoverage', $criteria);
    $table->setTitle(__('View'));

    $table->addHeaderAction('add', __('Add'))
        ->setURL('/modules/Staff/coverage_manage_add.php')
        ->displayLabel();

    $table->modifyRows(function ($coverage, $row) {
        if ($coverage['status'] == 'Accepted') $row->addClass('current');
        if ($coverage['status'] == 'Declined') $row->addClass('error');
        if ($coverage['status'] == 'Cancelled') $row->addClass('dull');
        return $row;
    });

    $table->addMetaData('filterOptions', [
        'date:upcoming'    => __('Upcoming'),
        'date:today'       => __('Today'),
        'date:past'        => __('Past'),
        'status:requested' => __('Coverage').': '.__('Requested'),
        'status:accepted'  => __('Coverage').': '.__('Accepted'),
        'status:declined'  => __('Coverage').': '.__('Declined'),
        'status:cancelled' => __('Coverage').': '.__('Cancelled'),
    ]);

    // COLUMNS
    $table->addColumn('requested', __('Name'))
        ->sortable(['surnameAbsence', 'preferredNameAbsence'])
        ->format([AbsenceFormats::class, 'personAndTypeDetails']);

    $table->addColumn('date', __('Date'))
        ->width('18%')
        ->format([AbsenceFormats::class, 'dateDetails']);

    $table->addColumn('coverage', __('Substitute'))
        ->sortable(['surnameCoverage', 'preferredNameCoverage'])
        ->format([AbsenceFormats::class, 'substituteDetails']);

    $table->addColumn('status', __('Status'))
        ->width('15%')
        ->format(function ($coverage) use ($urgencyThreshold) {
            return AbsenceFormats::coverageStatus($coverage, $urgencyThreshold);
        });

    $table->addColumn('timestampStatus', __('Requested'))
        ->format(function ($coverage) {
            if (empty($coverage['timestampStatus'])) return;
            return Format::relativeTime($coverage['timestampStatus'], 'M j, Y H:i');
        });

    // ACTIONS
    $table->addActionColumn()
        ->addParam('search', $criteria->getSearchText(true))
        ->addParam('pupilsightStaffCoverageID')
        ->format(function ($coverage, $actions) {
            $actions->addAction('view', __('View Details'))
                ->addParam('pupilsightStaffAbsenceID', $coverage['pupilsightStaffAbsenceID'] ?? '')
                ->isModal(800, 550)
                ->setURL('/modules/Staff/coverage_view_details.php');
                
            $actions->addAction('edit', __('Edit'))
                ->setURL('/modules/Staff/coverage_manage_edit.php');

            $actions->addAction('delete', __('Delete'))
                ->setURL('/modules/Staff/coverage_manage_delete.php');
        });

    echo $table->render($coverage);
}
