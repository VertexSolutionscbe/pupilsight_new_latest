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
    $page->breadcrumbs->add(__('Manage Deposit Account Details'));

    if (isset($_GET['return'])) {
        returnProcess($guid, $_GET['return'], null, null);
    }

    $id = $_GET['id'];

    $FeesGateway = $container->get(FeesGateway::class);

    // QUERY
    $criteria = $FeesGateway->newQueryCriteria()
        ->sortBy(['id'])
        ->pageSize(100)
        ->fromPOST();

    $depositDetails = $FeesGateway->getDepositAccountDetails($criteria, $id);

    // DATA TABLE
    $table = DataTable::createPaginated('DepositAccountManage', $criteria);

    // $table->addHeaderAction('add', __('Add'))
    //     ->setURL('/modules/Finance/program_manage_add.php')
    //     ->displayLabel();
    
    
    $table->addColumn('serial_number', __('SI No'));
    $table->addColumn('officialName', __('Student Name'));
    $table->addColumn('ac_name', __('Account Name'));
    $table->addColumn('transaction_id', __('Transaction Id'));
    $table->addColumn('invoice_no', __('Invoice No'));
    $table->addColumn('receipt_number', __('Receipt No'));
    $table->addColumn('paymentMode', __('Payment Mode'));
    $table->addColumn('amount', __('Amount'));
    $table->addColumn('status', __('Transaction Type'));
    $table->addColumn('cdt', __('Transaction Date'));


    echo $table->render($depositDetails);

    //echo formatName('', $row['preferredName'], $row['surname'], 'Staff', false, true);
}
