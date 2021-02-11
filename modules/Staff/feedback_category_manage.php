<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Tables\DataTable;
use Pupilsight\Services\Format;
use Pupilsight\Domain\Staff\StaffGateway;

if (isActionAccessible($guid, $connection2, '/modules/Staff/feedback_category_manage.php') == false) {
    //Acess denied
    echo "<div class='error'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    //Proceed!
    $page->breadcrumbs->add(__('Manage Feedback Category'));

    if (isset($_GET['return'])) {
        returnProcess($guid, $_GET['return'], null, null);
    }

    $StaffGateway = $container->get(StaffGateway::class);

    // QUERY
    $criteria = $StaffGateway->newQueryCriteria()
        ->sortBy(['id'])
        ->fromPOST();

    $skills = $StaffGateway->getFeedbackCategory($criteria);

    // DATA TABLE
    $table = DataTable::createPaginated('schoolYearManage', $criteria);

    echo "<div style='height:50px;'><div class='float-right mb-2'><a href='fullscreen.php?q=/modules/Staff/feedback_category_manage_add.php' class='thickbox btn btn-primary'>Add</a><div class='float-none'></div></div></div>";

    // $table->addHeaderAction('add', __('Add'))
    //     ->setURL('/modules/Staff/schoolYear_manage_add.php')
    //     ->displayLabel();
    $table->addColumn('serial_number', __('SI No'));
    $table->addColumn('name', __('Name'));


    // ACTIONS
    $table->addActionColumn()
        ->addParam('id')
        ->format(function ($skills, $actions) {
            $actions->addAction('editnew', __('Edit'))
                ->setURL('/modules/Staff/feedback_category_manage_edit.php');



            // if ($schoolYear['status'] != 'Current') {
            $actions->addAction('delete', __('Delete'))
                ->setURL('/modules/Staff/feedback_category_manage_delete.php');
            // }
        });

    echo $table->render($skills);
}
