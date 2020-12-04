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
    $page->breadcrumbs->add(__('Manage Feedback'));

    if (isset($_GET['return'])) {
        returnProcess($guid, $_GET['return'], null, null);
    }

    $StaffGateway = $container->get(StaffGateway::class);

    $staff_id = $_GET['stid'];

    // QUERY
    $criteria = $StaffGateway->newQueryCriteria()
        ->sortBy(['id'])
        ->fromPOST();

    $feedbacks = $StaffGateway->getFeedback($criteria, $staff_id);

    // DATA TABLE
    $table = DataTable::createPaginated('schoolYearManage', $criteria);

    echo "<div style='height:50px;'><div class='float-right mb-2'><a href='index.php?q=/modules/Staff/feedback_manage_add.php&stid=".$staff_id."' class='btn btn-primary'>Add</a><div class='float-none'></div></div></div>";  
    
    // $table->addHeaderAction('add', __('Add'))
    //     ->setURL('/modules/Staff/schoolYear_manage_add.php')
    //     ->displayLabel();
    $table->addColumn('serial_number', __('SI No'));
    $table->addColumn('name', __('Name'));
    $table->addColumn('catName', __('Category'));
    $table->addColumn('program', __('Program'));
    $table->addColumn('class', __('Class'));
    $table->addColumn('section', __('Section'));
    $table->addColumn('subjectName', __('Subject'));
    $table->addColumn('feedback_date', __('Date'))
    ->format(function ($feedbacks) {
        if($feedbacks['feedback_date'] != '1970-01-01'){
            $fdate = date('d/m/Y', strtotime($feedbacks['feedback_date']));
        } else {
            $fdate = '';
        }
        return $fdate;
    });
    $table->addColumn('description', __('Description'));

    
        
    // ACTIONS
    $table->addActionColumn()
        ->addParam('id')
        ->format(function ($feedbacks, $actions) {
            // $actions->addAction('editnew', __('Edit'))
            //         ->setURL('/modules/Staff/feedback_category_manage_edit.php');
                    
                    

                $actions->addAction('delete', __('Delete'))
                       ->setURL('/modules/Staff/feedback_manage_delete.php');
        });

    echo $table->render($feedbacks);
}
