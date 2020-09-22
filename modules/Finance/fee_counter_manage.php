<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Tables\DataTable;
use Pupilsight\Services\Format;
use Pupilsight\Domain\Finance\FeesGateway;

if (isActionAccessible($guid, $connection2, '/modules/Finance/fee_counter_manage.php') == false) {
    //Acess denied
    echo "<div class='error'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    //Proceed!
    $page->breadcrumbs->add(__('Manage Fee Counter'));

    if (isset($_GET['return'])) {
        returnProcess($guid, $_GET['return'], null, null);
    }

    $FeesGateway = $container->get(FeesGateway::class);

    // QUERY
    $criteria = $FeesGateway->newQueryCriteria()
        ->sortBy(['id'])
        ->fromPOST();

    $yearGroups = $FeesGateway->getFeesCounter($criteria);

    // DATA TABLE
    $table = DataTable::createPaginated('FeeCounterManage', $criteria);

    // $table->addHeaderAction('add', __('Add'))
    //     ->setURL('/modules/Finance/program_manage_add.php')
    //     ->displayLabel();
    
    echo "<div style='height:50px;'><div class='float-right mb-2'><a href='fullscreen.php?q=/modules/Finance/fee_counter_manage_add.php' class='thickbox btn btn-primary'>Add</a></div><div class='float-none'></div></div>";  

    
    
    //$table->addColumn('sequenceNumber', __('sequenceNumber'));
    $table->addColumn('name', __('Name'));
    $table->addColumn('code', __('Code'));
    $table->addColumn('collection', __('Total Collected for the day'));
    $table->addColumn('status', __('Status'))
         ->format(function ($yearGroups) {
             if ($yearGroups['status'] == '1') {
                 return '<a id="deactiveCounter" data-id="'.$yearGroups['id'].'" style="cursor:pointer; color:blue;text-decoration: underline;">Active</a>';
             } else {
                return 'Inactive';
             }
             return $yearGroups['status'];
    });  
   // $table->addColumn('status', __('Status'));
    
   
        
    // ACTIONS
    $table->addActionColumn()
        ->addParam('id')
        ->format(function ($facilities, $actions) use ($guid) {
            $actions->addAction('editnew', __('Edit'))
                    ->setURL('/modules/Finance/fee_counter_manage_edit.php');

            $actions->addAction('delete', __('Delete'))
                    ->setURL('/modules/Finance/fee_counter_manage_delete.php');

            $actions->addAction('list', __('Counter Used by'))
                    ->setURL('/modules/Finance/fee_counter_used_by.php');        
        });

    echo $table->render($yearGroups);

    //echo formatName('', $row['preferredName'], $row['surname'], 'Staff', false, true);
}
