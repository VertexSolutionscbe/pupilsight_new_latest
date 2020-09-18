<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Forms\Form;
use Pupilsight\Tables\DataTable;
use Pupilsight\Services\Format;
use Pupilsight\Forms\DatabaseFormFactory;
use Pupilsight\Domain\Finance\FeesGateway;

if (isActionAccessible($guid, $connection2, '/modules/Finance/fees_make_payment.php') == false) {
    //Acess denied
    echo "<div class='error'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    //Proceed!
    $page->breadcrumbs
        ->add(__('Make Payment'), 'fees_make_payment.php')
        ->add(__(''));
/*
    $editLink = '';
    if (isset($_GET['editID'])) {
        $editLink = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/Finance/fee_discount_rule_manage_edit.php&id='.$_GET['editID'];
    }
    if (isset($_GET['return'])) {
        returnProcess($guid, $_GET['return'], $editLink, null);
    }
    */
    echo '<h2>';
    echo __('Make Payment');
    echo '</h2>';

    /*
    $pupilsightSchoolYearID = $_SESSION[$guid]['pupilsightSchoolYearID'];
    $sqla = 'SELECT pupilsightSchoolYearID, name FROM pupilsightSchoolYear ';
    $resulta = $connection2->query($sqla);
    $academic = $resulta->fetchAll();

    $academicData = array();
    foreach ($academic as $dt) {
        $academicData[$dt['pupilsightSchoolYearID']] = $dt['name'];
    }

    $sqli = 'SELECT id, name FROM fn_fee_items ';
    $resulti = $connection2->query($sqli);
    $feeItem = $resulti->fetchAll();

    $feeItemData = array();
    foreach ($feeItem as $dt) {
        $feeItemData[$dt['id']] = $dt['name'];
    }

    */

    $FeesGateway = $container->get(FeesGateway::class);
    $criteria = $FeesGateway->newQueryCriteria()
    ->sortBy(['stuid'])
    ->fromPOST();

$yearGroups = $FeesGateway->getStudentlist_quick_cashpayment($criteria);

// DATA TABLE
$table = DataTable::createPaginated('FeeQuickCashPayment', $criteria);
  //  echo "<div style='height:50px;'><div class='float-left mb-2'><button id='payment_actn' type='submit' class=' btn btn-primary'>Make Payment</button>";  

  //  echo "&nbsp;&nbsp;<button id='payment_actn_discnt' type='submit' class=' btn btn-primary'>Make Payment With Discount Fee</button>";

    
    
  //  echo " </div><div class='float-none'></div></div>";

    $form = Form::create('program', $_SESSION[$guid]['absoluteURL'].'/modules/'.$_SESSION[$guid]['module'].'/');
    $form->setFactory(DatabaseFormFactory::create($pdo));

    $form->addHiddenValue('address', $_SESSION[$guid]['address']);

    $row = $form->addRow();
    $col = $row->addColumn()->setClass('float-left submit_ht');
     
    $col->addSubmit(__('Make Payment'));       
    $col->addSubmit(__('Make Payment With Discount Fee')); 
        

    $row = $form->addRow();
            $col = $row->addColumn()->setClass('newdes');
            $col->addLabel('', __('Transaction Id'));
            $col->addTextField('transaction_id')->addClass('txtfield')->readOnly()->setValue('');

            $col = $row->addColumn()->setClass('newdes ');
            $col->addLabel('receipt_number', __('Receipt Number'))->addClass('dte');
            $col->addTextField('receipt_number')->addClass('txtfield') ->required();
        
             $col = $row->addColumn()->setClass('hiddencol');
             $col->addLabel('', __(''));
             $col->addTextField('');    
    $paymentmode = array(''=>'Select','1'=>'Cash','2'=>'Cheque');    

    $col = $row->addColumn()->setClass('newdes');
    $col->addLabel('payment_mode', __('Payment Mode'));
    $col->addSelect('payment_mode')->fromArray($paymentmode)->addClass(' txtfield')->required();
    $bankname =array(''=>'Select');   
    $col = $row->addColumn()->setClass('newdes');
    $col->addLabel('bank_name', __('Bank Name'));
    $col->addSelect('bank_name')->fromArray($bankname)->addClass(' txtfield')->required();

    $col = $row->addColumn()->setClass('hiddencol');
    $col->addLabel('', __(''));
    $col->addTextField('');    
      
   $row = $form->addRow();
           

 $date = isset($_GET['date']) ? Format::dateConvert($_GET['date']) : date('Y-m-d');
       
 $row = $form->addRow();
       
        
        $col = $row->addColumn()->setClass('newdes ');
        $col->addLabel('instrument_date', __('Instrument Date'))->addClass('dte');
        $col->addDate('instrument_date')->setValue(Format::date($date));

        $col = $row->addColumn()->setClass('newdes ');
        $col->addLabel('instrument_number', __('Instrument No'))->addClass('dte');
        $col->addTextField('instrument_number')->addClass('txtfield');

        $col = $row->addColumn()->setClass('newdes ');
        $col->addLabel('instrument_amt', __('Instrument Amount'))->addClass('dte');
        $col->addTextField('instrument_amt')->addClass('txtfield   numfield');

        $col = $row->addColumn()->setClass('newdes ');
        $col->addLabel('payment_status', __('Payment Status'))->addClass('dte');
        $col->addTextField('payment_status')->addClass('txtfield');
 $row = $form->addRow();
       
    
       

        $col = $row->addColumn()->setClass('newdes ');
        $col->addLabel('payment_date', __('Payment Date'))->addClass('dte');
        $col->addDate('payment_date')->setValue(Format::date($date))->required();
        $accnt_head =array(''=>'Select');   
        $col = $row->addColumn()->setClass('newdes ');
        $col->addLabel('accnt_head', __('Account Head'))->addClass('dte');
        $col->addSelect('accnt_head')->fromArray($accnt_head)->addClass(' txtfield');   
        $receipt_series =array(''=>'Select'); 
        $col = $row->addColumn()->setClass('newdes ');
        $col->addLabel('receipt_series', __('Receipt Series'))->addClass('dte');
        $col->addSelect('receipt_series')->fromArray($receipt_series)->addClass(' txtfield');   
        $col = $row->addColumn()->setClass('newdes ');
        $col->addLabel('remarks', __('Remarks'))->addClass('dte');
        $col->addTextField('remarks')->addClass('txtfield');

$row = $form->addRow();
       
    
       
        $col = $row->addColumn()->setClass('newdes ');
        $col->addLabel('payment_status', __('Manual Reciept No'))->addClass('dte');
        $col->addTextField('payment_status')->addClass('txtfield');
        $col = $row->addColumn()->setClass('newdes ');
        $col->addLabel('', __(''));
        $col->addCheckbox('no_auto_gen_rn')->description(__('Enter custom receipt number(No auto generate)'))->addClass(' dte');
        $col = $row->addColumn()->setClass('newdes ');
        $col->addLabel('transaction_amt', __('Transaction Amount'))->addClass('dte');
        $col->addTextField('transaction_amt')->addClass('txtfield   numfield')->required();
 $row = $form->addRow();
       
         
        $col = $row->addColumn()->setClass('newdes ');
        $col->addLabel('fine', __('Fine'))->addClass('dte');
        $col->addTextField('fine')->addClass('txtfield numfield');
        $col = $row->addColumn()->setClass('newdes ');
        $col->addLabel('amnt_paying', __(' Amount Paying'))->addClass('dte');
        $col->addTextField('amnt_paying')->addClass('txtfield   numfield')->readOnly()->setValue('');

        $col = $row->addColumn()->setClass('newdes ');
        $col->addLabel('', __(''));
        $col->addCheckbox('is_pay')->description(__('Do you want to do payment for selected fee items)'))->addClass(' dte');

        $row = $form->addRow();
           
       
 $row = $form->addRow();
        $col = $row->addColumn()->setClass('hiddencol');
        $col->addLabel('', __(''));
        $col->addTextField('');   
   

        $table->addCheckboxColumn('stuid',__(''))
        ->setClass('chkbox')
            ->context('Select')
            ->notSortable();
        $table->addColumn('', __('Fee Item'));
       
        $table->addColumn('', __('Description'));
        $table->addColumn('0', __('Invoice No'));
        $table->addColumn('1', __(' Amount'));
        $table->addColumn('2', __('Tax'));
        $table->addColumn('3', __('Final Amount'));
        $table->addColumn('4', __('Discount'));
        $table->addColumn('5', __('Amount Discounted'));
        $table->addColumn('6', __('Amount Paid'));
        $table->addColumn('7', __('Amount Pending'));
        $table->addColumn('7', __('Invoice Status'));
      
      
       

    echo $form->getOutput();
    echo "<div class ='row fee_hdr'><div class='col-md-12'> Fee Items</div></div>";
    echo $table->render($yearGroups);

echo "<div class='fee_footer'>";

    echo "<div class ='row fee_hdr'>
    <div class='col-md-4'> Amount</div>
    <div class='col-md-4'> Tax</div>
    <div class='col-md-4'> Discounts</div>
    </div>";

    echo "<div class ='row fee_hdr'>
    
    <div class='col-md-4'>  Grand Total</div>
    <div class='col-md-4'> </div>
    <div class='col-md-4'> Amount Pending</div>
   </div>";
    echo "<div class ='row fee_hdr'><div class='col-md-12'> Rupees Only</div></div>";

    echo "</div>";
}

echo "<style>
.pagination ,.dataTable header
{
    display:none !important;
}
.fee_hdr
{
    height: 27px;
    font-size: 15px;
    font-weight: 600;
    color: #6f6767;
}
.fee_footer
{
    border: 1px solid #ececec;
    background-color: #f7f7f7;
}
.submit_ht 
{
    height: 46px !important;
}
</style>";