<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Tables\DataTable;
use Pupilsight\Services\Format;
use Pupilsight\Domain\School\GradeScaleGateway;

if (isActionAccessible($guid, $connection2, '/modules/School Admin/gradeScales_manage.php') == false) {
    //Acess denied
    echo "<div class='alert alert-danger'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    //Proceed!
    $page->breadcrumbs->add(__('Manage Grade Scales'));
    echo '<p>';
    echo __('Grade scales are used through the Assess modules to control what grades can be entered into the system. Editing some of the inbuilt scales can impact other areas of the system: it is advised to take a backup of the entire system before doing this.');
    echo '</p>';

    if (isset($_GET['return'])) {
        returnProcess($guid, $_GET['return'], null, null);
    }

    $gradeScaleGateway = $container->get(GradeScaleGateway::class);

    // QUERY
    $criteria = $gradeScaleGateway->newQueryCriteria()
        ->sortBy('name')
        ->fromPOST();

    $gradeScales = $gradeScaleGateway->queryGradeScales($criteria);

    // DATA TABLE
    $table = DataTable::createPaginated('gradeScaleManage', $criteria);

    $table->addHeaderAction('add', __('Add'))
        ->setURL('/modules/School Admin/gradeScales_manage_add.php')
        ->displayLabel();

    $table->modifyRows(function($gradeScale, $row) {
        if ($gradeScale['active'] != 'Y') $row->addClass('error');
        return $row;
    });

    $table->addColumn('name', __('Name'))
          ->description(__('Short Name'))
          ->format(function ($gradeScale) {
            return '<strong>' . __($gradeScale['name']) . '</strong><br/><small><i>' . __($gradeScale['nameShort']) . '</i></small>';
          });
    $table->addColumn('usage', __('Usage'))->translatable();
    $table->addColumn('active', __('Active'))->format(Format::using('yesNo', ['active']));
    $table->addColumn('numeric', __('Numeric'))->format(Format::using('yesNo', ['numeric']));
        
    // ACTIONS
    $table->addActionColumn()
        ->addParam('pupilsightScaleID')
        ->format(function ($gradeScale, $actions) {
            $actions->addAction('edit', __('Edit'))
                    ->setURL('/modules/School Admin/gradeScales_manage_edit.php');

            $actions->addAction('delete', __('Delete'))
                    ->setURL('/modules/School Admin/gradeScales_manage_delete.php');
        });

    echo $table->render($gradeScales);
}
