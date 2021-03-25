<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Tables\DataTable;
use Pupilsight\Services\Format;
use Pupilsight\Domain\Finance\FeesGateway;

if (isActionAccessible($guid, $connection2, '/modules/Finance/deposit_account_manage.php') == false) {
    //Acess denied
    echo "<div class='error'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    //Proceed!
    $page->breadcrumbs->add(__('Manage Deposit Account'));

    if (isset($_GET['return'])) {
        returnProcess($guid, $_GET['return'], null, null);
    }

    $FeesGateway = $container->get(FeesGateway::class);

    // QUERY
    $criteria = $FeesGateway->newQueryCriteria()
        ->sortBy(['id'])
        ->fromPOST();

    $yearGroups = $FeesGateway->getDepositAccount($criteria);

    // DATA TABLE
    $table = DataTable::createPaginated('DepositAccountManage', $criteria);

    // $table->addHeaderAction('add', __('Add'))
    //     ->setURL('/modules/Finance/program_manage_add.php')
    //     ->displayLabel();
    
    echo "<div style='height:50px;'><div class='float-right mb-2'><a href='fullscreen.php?q=/modules/Finance/deposit_account_manage_add.php' class='thickbox btn btn-primary'>Add</a></div><div class='float-none'></div></div>";  

    
    $table->addColumn('serial_number', __('SI No'));
    $table->addColumn('fee_item', __('Fee Item'));
    $table->addColumn('ac_name', __('Account Name'));
    $table->addColumn('ac_code', __('Account Code'));
    $table->addColumn('overpayment_account', __('Account Type'))
         ->format(function ($dataSet) {
             if ($dataSet['overpayment_account'] == '1') {
                 return 'Over Payment Account';
             } else {
                 return ' ';
             }
             return $dataSet['overpayment_account'];
    });  
    // $table->addColumn('amount', __('Current Balance'))
    //     ->format(function ($dataSet) {
    //         return '<a href="fullScreen.php?q=/modules/Finance/deposit_account_details.php&id='.$dataSet['id'].'" class="thickbox">'.$dataSet['amount'].'</a>';
    //     });  
   
        
    // ACTIONS
    $table->addActionColumn()
        ->addParam('id')
        ->format(function ($yearGroups, $actions) use ($guid) {
            $actions->addAction('editnew', __('Edit'))
                    ->setURL('/modules/Finance/deposit_account_manage_edit.php');
            if($yearGroups['overpayment_account'] != '1'){
               $actions->addAction('delete', __('Delete'))
                    ->setURL('/modules/Finance/deposit_account_manage_delete.php');
            } else {
                $actions->addAction('deleteAlert', __('DeleteAlert'))
                ->setId('alertDataDeposit');
            }   
            
        });

    echo $table->render($yearGroups);

    //echo formatName('', $row['preferredName'], $row['surname'], 'Staff', false, true);
}
