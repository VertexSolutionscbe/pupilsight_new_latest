<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Tables\DataTable;
use Pupilsight\Services\Format;
use Pupilsight\Domain\Finance\FeesGateway;


if (isActionAccessible($guid, $connection2, '/modules/Finance/fee_quick_cash_payment.php') == false) {
    //Acess denied
    echo "<div class='error'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    //Proceed!
    $page->breadcrumbs->add(__('Manage Fee Quick Cash Payment'));

    if (isset($_GET['return'])) {
        returnProcess($guid, $_GET['return'], null, null);
    }

    $FeesGateway = $container->get(FeesGateway::class);

    // QUERY
    //$id = $_GET['id'];
   
    $criteria = $FeesGateway->newQueryCriteria()
        ->sortBy(['stuid'])
        ->fromPOST();

    $yearGroups = $FeesGateway->getStudentlist_quick_cashpayment($criteria);

    // DATA TABLE
    $table = DataTable::createPaginated('FeeQuickCashPayment', $criteria);

    // $table->addHeaderAction('add', __('Add'))
    //     ->setURL('/modules/Finance/program_manage_add.php')
    //     ->displayLabel();
    
    echo "<div style='height:50px; display:none;'><div class='float-right mb-2'><a id='clickQuickcashpaymentPage' href='fullscreen.php?q=/modules/Finance/fee_quick_cash_payment_add.php'  class='thickbox btn btn-primary'>Quick Cash Payment</a></div><div class='float-none'></div></div>";  
    echo "<div style='height:50px;'><div class='float-right mb-2'><a id='quickpaymentpage' class=' btn btn-primary'>Quick Cash Payment</a></div><div class='float-none'></div></div>";  

    
    $table->addCheckboxColumn('stuid',__(''))
    ->setClass('chkbox')
        ->context('Select')
        ->notSortable();
    $table->addColumn('student_name', __('Student Name'));
   
    $table->addColumn('studentid', __('Student ID'));
    $table->addColumn('0', __('INV No'));
    $table->addColumn('1', __('INV Amount'));
    $table->addColumn('2', __('Tax'));
    $table->addColumn('3', __('Paid'));
    $table->addColumn('4', __('Pending'));
    $table->addColumn('5', __('Due Date'));
    $table->addColumn('6', __('Status'));
    $table->addColumn('7', __('Title'));
  
   
    // $table->addColumn('account_code', __('Account Code'));
    // $table->addColumn('bank_name', __('Bank Name'));
    // $table->addColumn('ac_no', __('Account No'));
    
        
    // ACTIONS
   

    echo $table->render($yearGroups);
}
