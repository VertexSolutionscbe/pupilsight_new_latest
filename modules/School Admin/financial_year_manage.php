<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Tables\DataTable;
use Pupilsight\Services\Format;
use Pupilsight\Domain\School\SchoolYearGateway;

if (isActionAccessible($guid, $connection2, '/modules/School Admin/financial_year_manage.php') == false) {
    //Acess denied
    echo "<div class='alert alert-danger'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    //Proceed!
    $page->breadcrumbs->add(__('Manage Financial Years'));

    if (isset($_GET['return'])) {
        returnProcess($guid, $_GET['return'], null, null);
    }

    $schoolYearGateway = $container->get(SchoolYearGateway::class);

    // QUERY
    $criteria = $schoolYearGateway->newQueryCriteria()
        ->sortBy(['sequenceNumber'])
        ->fromPOST();

    $schoolYears = $schoolYearGateway->queryFinancialYears($criteria);

    // DATA TABLE
    $table = DataTable::createPaginated('schoolYearManage', $criteria);

    echo '<h2>';
     echo __('Financial Year Master');
     echo '</h2>';

    echo "<div style='height:50px;'><div class='float-right mb-2'><a href='fullscreen.php?q=/modules/School Admin/financial_year_manage_add.php' class='thickbox btn btn-primary'>Add</a><div class='float-none'></div></div></div>";  
    
    // $table->addHeaderAction('add', __('Add'))
    //     ->setURL('/modules/School Admin/financial_year_manage_add.php')
    //     ->displayLabel();

    $table->modifyRows(function($schoolYear, $row) {
        if ($schoolYear['status'] == 'Current') $row->addClass('current');
        return $row;
    });

    $table->addColumn('sequenceNumber', __('Sequence'))->width('10%');
    $table->addColumn('name', __('Name'));
    $table->addColumn('dates', __('Dates'))
          ->format(Format::using('dateRange', ['firstDay', 'lastDay']))
          ->sortable(['firstDay', 'lastDay']);
    $table->addColumn('status', __('Status'))->translatable();
        
    // ACTIONS
    $table->addActionColumn()
        ->addParam('pupilsightSchoolFinanceYearID')
        ->format(function ($schoolYear, $actions) {
            $actions->addAction('edit', __('Edit'))
                    ->setURL('/modules/School Admin/financial_year_manage_edit.php');

            // if ($schoolYear['status'] != 'Current') {
            //     $actions->addAction('delete', __('Delete'))
            //             ->setURL('/modules/School Admin/financial_year_manage_delete.php');
            // }
        });

    echo $table->render($schoolYears);
}
