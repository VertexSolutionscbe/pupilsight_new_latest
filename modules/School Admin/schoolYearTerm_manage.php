<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Tables\DataTable;
use Pupilsight\Services\Format;
use Pupilsight\Domain\School\SchoolYearTermGateway;

if (isActionAccessible($guid, $connection2, '/modules/School Admin/schoolYearTerm_manage.php') == false) {
    //Acess denied
    echo "<div class='alert alert-danger'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    //Proceed!
    $page->breadcrumbs->add(__('Manage Terms'));

    if (isset($_GET['return'])) {
        returnProcess($guid, $_GET['return'], null, null);
    }

    $termGateway = $container->get(SchoolYearTermGateway::class);

    // QUERY
    $criteria = $termGateway->newQueryCriteria()
        ->sortBy(['schoolYearSequence', 'sequenceNumber'])
        ->fromPOST();

    $terms = $termGateway->querySchoolYearTerms($criteria);

    // DATA TABLE
    $table = DataTable::createPaginated('schoolYearTermManage', $criteria);

    $table->addHeaderAction('add', __('Add'))
        ->setURL('/modules/School Admin/schoolYearTerm_manage_add.php')
        ->displayLabel();

    $table->modifyRows(function($schoolYear, $row) {
        if ($schoolYear['status'] == 'Current') $row->addClass('current');
        return $row;
    });

    $table->addColumn('schoolYearName', __('School Year'));
    $table->addColumn('sequenceNumber', __('Sequence'))->width('10%');
    $table->addColumn('name', __('Name'));
    $table->addColumn('nameShort', __('Short Name'));
    $table->addColumn('dates', __('Dates'))
          ->format(Format::using('dateRange', ['firstDay', 'lastDay']))
          ->sortable(['firstDay', 'lastDay']);
        
    // ACTIONS
    $table->addActionColumn()
        ->addParam('pupilsightSchoolYearTermID')
        ->format(function ($schoolYear, $actions) {
            $actions->addAction('edit', __('Edit'))
                    ->setURL('/modules/School Admin/schoolYearTerm_manage_edit.php');

            $actions->addAction('delete', __('Delete'))
                    ->setURL('/modules/School Admin/schoolYearTerm_manage_delete.php');

        });

    echo $table->render($terms);
}
