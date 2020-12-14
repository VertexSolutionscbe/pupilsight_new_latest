<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Tables\DataTable;
use Pupilsight\Services\Format;
use Pupilsight\Domain\Finance\FeesGateway;

if (isActionAccessible($guid, $connection2, '/modules/Finance/fee_master_manage.php') == false) {
    //Acess denied
    echo "<div class='error'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    //Proceed!
    $page->breadcrumbs->add(__('Manage Bank And Payment Mode'));

    if (isset($_GET['return'])) {
        returnProcess($guid, $_GET['return'], null, null);
    }

    $FeesGateway = $container->get(FeesGateway::class);

    // QUERY
    $criteria = $FeesGateway->newQueryCriteria()
        ->pageSize(1000)
        ->sortBy(['id'])
        ->fromPOST();

    $yearGroups = $FeesGateway->getFeesMaster($criteria);

    // DATA TABLE
    $table = DataTable::createPaginated('FeeItemManage', $criteria);

    // $table->addHeaderAction('add', __('Add'))
    //     ->setURL('/modules/Finance/program_manage_add.php')
    //     ->displayLabel();
    
    echo "<div style='height:50px;'><div class='float-right mb-2'><a href='fullscreen.php?q=/modules/Finance/fee_master_manage_add.php' class='thickbox btn btn-primary'>Add</a>&nbsp;&nbsp;<a href='index.php?q=/modules/Finance/import_fee_master_manage.php' class='btn btn-primary'>Import</a></div><div class='float-none'></div></div>";  

    
    
    //$table->addColumn('sequenceNumber', __('sequenceNumber'));
    $type = array('type:payment_mode' => 'Payment Mode' , 'type:bank' => 'Bank Name');
    $table->addMetaData('filterOptions', $type);
    $table->addColumn('type', __('Type'))
         ->format(function ($dataSet) {
             if ($dataSet['type'] == 'bank') {
                 return 'Bank Name';
             } else {
                return 'Payment Mode';
             }
             return $dataSet['type'];
    }); 
    $table->addColumn('name', __('Name'));
    
        
    // ACTIONS
    
    $table->addActionColumn()
        ->addParam('id')
        ->format(function ($facilities, $actions) use ($guid) {
            $actions->addAction('editnew', __('Edit'))
                    ->setURL('/modules/Finance/fee_master_manage_edit.php');

            $actions->addAction('delete', __('Delete'))
                    ->setURL('/modules/Finance/fee_master_manage_delete.php');
        });

    echo $table->render($yearGroups);

    //echo formatName('', $row['preferredName'], $row['surname'], 'Staff', false, true);
}
