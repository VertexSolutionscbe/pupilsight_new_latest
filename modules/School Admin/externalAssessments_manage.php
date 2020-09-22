<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Tables\DataTable;
use Pupilsight\Services\Format;
use Pupilsight\Domain\School\ExternalAssessmentGateway;

if (isActionAccessible($guid, $connection2, '/modules/School Admin/externalAssessments_manage.php') == false) {
    //Acess denied
    echo "<div class='alert alert-danger'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    //Proceed!
    $page->breadcrumbs->add(__('Manage External Assessments'));

    if (isset($_GET['return'])) {
        returnProcess($guid, $_GET['return'], null, null);
    }

    $externalAssessmentGateway = $container->get(ExternalAssessmentGateway::class);

    // QUERY
    $criteria = $externalAssessmentGateway->newQueryCriteria()
        ->sortBy('name')
        ->fromPOST();

    $externalAssessments = $externalAssessmentGateway->queryExternalAssessments($criteria);

    // DATA TABLE
    $table = DataTable::createPaginated('externalAssessmentManage', $criteria);

    $table->addHeaderAction('add', __('Add'))
        ->setURL('/modules/School Admin/externalAssessments_manage_add.php')
        ->displayLabel();

    $table->modifyRows(function($externalAssessment, $row) {
        if ($externalAssessment['active'] != 'Y') $row->addClass('error');
        return $row;
    });

    $table->addColumn('name', __('Name'))->format(function ($externalAssessment) {
        return '<strong>' . __($externalAssessment['name']) . '</strong>';
      });
    $table->addColumn('description', __('description'))->translatable();
    $table->addColumn('active', __('Active'))->format(Format::using('yesNo', ['active']));
    $table->addColumn('allowFileUpload', __('File Upload'))->format(Format::using('yesNo', ['allowFileUpload']));
        
    // ACTIONS
    $table->addActionColumn()
        ->addParam('pupilsightExternalAssessmentID')
        ->format(function ($externalAssessment, $actions) {
            $actions->addAction('edit', __('Edit'))
                    ->setURL('/modules/School Admin/externalAssessments_manage_edit.php');

            $actions->addAction('delete', __('Delete'))
                    ->setURL('/modules/School Admin/externalAssessments_manage_delete.php');
        });

    echo $table->render($externalAssessments);
}
