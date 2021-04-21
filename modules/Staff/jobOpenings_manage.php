<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Tables\DataTable;
use Pupilsight\Services\Format;
use Pupilsight\Domain\Staff\StaffJobOpeningGateway;

if (isActionAccessible($guid, $connection2, '/modules/Staff/jobOpenings_manage.php') == false) {
    //Acess denied
    echo "<div class='alert alert-danger'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    //Proceed!
    $page->breadcrumbs->add(__('Job Openings'));

    if (isset($_GET['return'])) {
        returnProcess($guid, $_GET['return'], null, null);
    }

    $jobGateway = $container->get(StaffJobOpeningGateway::class);

    // QUERY
    $criteria = $jobGateway->newQueryCriteria()
        ->sortBy(['dateOpen', 'jobTitle'])
        ->fromPOST();

    $jobs = $jobGateway->queryJobOpenings($criteria);

    // DATA TABLE
    $table = DataTable::createPaginated('jobOpeningsManage', $criteria);

    $table->addHeaderAction('add', __('Add'))
        ->setURL('/modules/Staff/jobOpenings_manage_add.php')
        ->displayLabel();

    $table->modifyRows(function ($values, $row) {
        if ($values['active'] == 'N') $row->addClass('error');
        return $row;
    });

    $table->addMetaData('filterOptions', [
        'active:Y' => __('Active') . ': ' . __('Yes'),
        'active:N' => __('Active') . ': ' . __('No'),
    ]);

    // COLUMNS
    $table->addColumn('serial_number', __('Sl No'));
    $table->addColumn('typeName', __('Type'));
    $table->addColumn('jobTitle', __('Job Title'));
    $table->addColumn('dateOpen', __('Opening Date'))
        ->format(Format::using('date', 'dateOpen'));
    $table->addColumn('active', __('Active'))
        ->format(Format::using('yesNo', 'active'));

    // ACTIONS
    $table->addActionColumn()
        ->addParam('pupilsightStaffJobOpeningID')
        ->addParam('search', $criteria->getSearchText(true))
        ->format(function ($person, $actions) use ($guid) {
            $actions->addAction('edit', __('Edit'))
                ->setURL('/modules/Staff/jobOpenings_manage_edit.php');

            $actions->addAction('delete', __('Delete'))
                ->setURL('/modules/Staff/jobOpenings_manage_delete.php');
        });

    echo $table->render($jobs);
}
