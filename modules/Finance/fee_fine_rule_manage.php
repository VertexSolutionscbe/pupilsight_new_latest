<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Tables\DataTable;
use Pupilsight\Services\Format;
use Pupilsight\Domain\Finance\FeesGateway;

if (isActionAccessible($guid, $connection2, '/modules/Finance/fee_fine_rule_manage.php') == false) {
    //Acess denied
    echo "<div class='error'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    //Proceed!
    $page->breadcrumbs->add(__('Manage Fee Fine Rule'));

    if (isset($_GET['return'])) {
        returnProcess($guid, $_GET['return'], null, null);
    }

    $FeesGateway = $container->get(FeesGateway::class);

    // QUERY
    $criteria = $FeesGateway->newQueryCriteria()
        ->sortBy(['id'])
        ->fromPOST();

    $yearGroups = $FeesGateway->getFeeFineRule($criteria);

    // DATA TABLE
    $table = DataTable::createPaginated('FeeFineRuleManage', $criteria);

    // $table->addHeaderAction('add', __('Add'))
    //     ->setURL('/modules/Finance/program_manage_add.php')
    //     ->displayLabel();
    
    echo "<div style='height:50px;'><div class='float-right mb-2'><a href='index.php?q=/modules/Finance/fee_fine_rule_manage_add.php' class='btn btn-primary'>Add</a></div><div class='float-none'></div></div>";  

    
    
    $table->addColumn('serial_number', __('SI No'));
    $table->addColumn('name', __('Name'));
    $table->addColumn('code', __('Code'));
    $table->addColumn('description', __('Description'));
    // $table->addColumn('account_code', __('Account Code'));
    // $table->addColumn('bank_name', __('Bank Name'));
    // $table->addColumn('ac_no', __('Account No'));
    
        
    // ACTIONS
    $table->addActionColumn()
        ->addParam('id')
        ->format(function ($facilities, $actions) use ($guid) {
            $actions->addAction('edit', __('Edit'))
                    ->setURL('/modules/Finance/fee_fine_rule_manage_edit.php');

            $actions->addAction('delete', __('Delete'))
                    ->setURL('/modules/Finance/fee_fine_rule_manage_delete.php');
        });

    echo $table->render($yearGroups);

    //echo formatName('', $row['preferredName'], $row['surname'], 'Staff', false, true);
}
