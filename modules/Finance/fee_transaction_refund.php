<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Forms\Form;
use Pupilsight\Forms\DatabaseFormFactory;
use Pupilsight\Tables\DataTable;
use Pupilsight\Domain\Finance\FeesGateway;

$session = $container->get('session');
$transids = $session->get('transaction_ids');

if (isActionAccessible($guid, $connection2, '/modules/Finance/fee_transaction_refund.php') == false) {
    //Acess denied
    echo "<div class='error'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    //Proceed!
    $page->breadcrumbs
        ->add(__('Manage Fee Refund Transaction'), 'fee_transaction_manage.php')
        ->add(__('Add Fee Refund Transaction'));

    $editLink = '';
    if (isset($_GET['editID'])) {
        $editLink = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/Finance/fee_item_manage_edit.php&id='.$_GET['editID'];
    }
    if (isset($_GET['return'])) {
        returnProcess($guid, $_GET['return'], $editLink, null);
    }
    echo '<h2>';
    echo __('Refund Fee Transaction');
    echo '</h2>';

   
     
    $sqldr = 'SELECT * FROM fn_masters ';
    $resultdr = $connection2->query($sqldr);
    $master = $resultdr->fetchAll();

    $paymentmode = array();
    $paymentmode1 = array(''=>'Select Payment Mode');
    $paymentmode2 = array();

    $bank = array();
    $bank1 = array(''=>'Select Bank Name');
    $bank2 = array();
    foreach ($master as $dr) {
        if($dr['type'] == 'payment_mode'){
            $paymentmode2[$dr['id']] = $dr['name'];
        } else {
            $bank2[$dr['id']] = $dr['name'];
        }
        
    }
    $bank = $bank1 + $bank2;
    $paymentmode = $paymentmode1 + $paymentmode2; 

    $sqlrc = 'SELECT id, series_name FROM fn_fee_series ';
    $resultrc = $connection2->query($sqlrc);
    $rseries = $resultrc->fetchAll();

    $receipt_series = array();
    $receipt_series1 = array(''=>'Select Receipt Series');
    $receipt_series2 = array();
    foreach ($rseries as $fd) {
        $receipt_series2[$fd['id']] = $fd['series_name'];
    }
    $receipt_series = $receipt_series1 + $receipt_series2; 

    $sqlt = 'SELECT * FROM fn_fees_collection WHERE id IN ('.$transids.') ';
    $resultt = $connection2->query($sqlt);
    $trans = $resultt->fetch();
    $transactionId = $trans['transaction_id'];
    $recptNo = $trans['receipt_number'];
    $stuId = $trans['pupilsightPersonID'];
    $refundDate = date('Y-m-d');

     

    $form = Form::create('refundAmount', $_SESSION[$guid]['absoluteURL'].'/modules/'.$_SESSION[$guid]['module'].'/fee_transaction_refundProcess.php');
    $form->setFactory(DatabaseFormFactory::create($pdo));

    $form->addHiddenValue('address', $_SESSION[$guid]['address']);
    $form->addHiddenValue('trans_id', $transids);
    $form->addHiddenValue('pupilsightPersonID', $stuId);

    $row = $form->addRow();
        $col = $row->addColumn()->setClass('float-left submit_ht');
        //$col->addSubmit(__('Refund'));  
        $col->addContent('<a id="chkRefundTransaction" style="margin:0px 4px;" class="transactionButton btn btn-primary">Refund</a>');   

    $row = $form->addRow();
        $col = $row->addColumn()->setClass('newdes');
        $col->addLabel('ref_amount', __('Total Refunded Transaction Amount '))->addClass('dte');
        $col->addTextField('ref_amount')->setValue($trans['amount_paying'])->readonly()->addClass('txtfield');

        $col = $row->addColumn()->setClass('newdes');
        $col->addLabel('transaction_id', __('Transaction Id'))->addClass('dte');
        $col->addTextField('transaction_id')->setValue($transactionId)->readonly()->addClass('txtfield');

        $col = $row->addColumn()->setClass('newdes');
        $col->addLabel('receipt_number', __('Receipt No'))->addClass('dte');
        $col->addTextField('receipt_number')->setValue($recptNo)->readonly()->addClass('txtfield');

        
    $row = $form->addRow();
        $col = $row->addColumn()->setClass('newdes');
        $col->addLabel('payment_mode_id', __('Payment Mode'));
        $col->addSelect('payment_mode_id')->setId('paymentMode')->fromArray($paymentmode)->addClass('txtfield');

        $col = $row->addColumn()->setClass('newdes');
        $col->addLabel('bank_id', __('Select Bank Name'));
        $col->addSelect('bank_id')->setId('bankId')->fromArray($bank)->addClass('txtfield');
        
    $row = $form->addRow()->setId('ddChequeRow');


        $col = $row->addColumn()->setClass('newdes hiddencol neft_cls');
        $col->addLabel('reference_no', __('Reference No'));
        $col->addTextField('reference_no')->addClass('txtfield')->placeholder('Enter Reference No');

        $col = $row->addColumn()->setClass('newdes hiddencol neft_cls');
        $col->addLabel('reference_date', __('Reference Date'))->addClass('dte');
        $col->addDate('reference_date');
        
        $col = $row->addColumn()->setClass('newdes hiddencol ddChequeRow');
        $col->addLabel('dd_cheque_no', __('DD/Cheque No'));
        $col->addTextField('dd_cheque_no')->addClass('txtfield');

        $col = $row->addColumn()->setClass('newdes hiddencol ddChequeRow');
        $col->addLabel('dd_cheque_date', __('DD/Cheque Date'))->addClass('dte');
        $col->addDate('dd_cheque_date');

        // $col = $row->addColumn()->setClass('newdes ');
        // $col->addLabel('dd_cheque_amount', __('DD/Cheque Amount'))->addClass('dte');
        // $col->addTextField('dd_cheque_amount')->addClass('txtfield');

    $row = $form->addRow();
        $col = $row->addColumn()->setClass('newdes ');
        $col->addLabel('refund_receipt_series_id', __('Refund Receipt Series'))->addClass('dte');
        $col->addSelect('refund_receipt_series_id')->fromArray($receipt_series)->addClass(' txtfield')->required(); 
        
        $col = $row->addColumn()->setClass('newdes ');
        $col->addLabel('refund_date', __('Refund Date'))->addClass('dte');
        $col->addTextField('refund_date')->setValue($refundDate);

        $col = $row->addColumn()->setClass('newdes');
        $col->addLabel('refund_amount', __('Refunded Amount'));
        $col->addTextField('refund_amount')->addClass('numfield')->required();

      

    $row = $form->addRow();    
        $col = $row->addColumn()->setClass('newdes');
        $col->addLabel('remarks', __('Remarks'));
        $col->addTextArea('remarks');

    echo $form->getOutput();

    $FeesGateway = $container->get(FeesGateway::class);
    $criteria = $FeesGateway->newQueryCriteria()
        ->sortBy(['id'])
        ->fromPOST();


    $invoices = $FeesGateway->getCollectionInvoiceRefund($criteria, $transids);
        $table1 = DataTable::createPaginated('FeeInvoiceListManage', $criteria);
        
        $table1->addColumn('title', __('Invoice Title'));
        $table1->addColumn('invoice_no', __('Invoice No'));
        $table1->addColumn('invoiceamount', __('Invoice Amount'));
        //$table1->addColumn('total_amount', __('Invoice Transaction'));
        $table1->addColumn('fine', __('Fine Amount'));
        $table1->addColumn('discount', __('Discount Amount'));
        $table1->addColumn('totalamount', __('Total Amount'));
        $table1->addColumn('payment_status', __('Invoice Status'));
        

        echo $table1->render($invoices);

}

?>

<script>

$(document).on('click','#chkRefundTransaction',function(){

        
        
        var err=0;
        var paymentMode = $("#paymentMode").val();

        var ramt = $("#refund_amount").val();
        var pamt = $("#ref_amount").val();
        if (Number(ramt) > Number(pamt)) {
            return false;
            err++;
        }

        if($("#refund_receipt_series_id").val() == ''){
            $("#refund_receipt_series_id").addClass('erroralert');
            alert('Please Select Refund Receipt Series!');
            err++;
        } else {
            $("#refund_receipt_series_id").removeClass('erroralert');
        }

        if($("#refund_amount").val() == ''){
            $("#refund_amount").addClass('erroralert');
            alert('Please Enter Refund Amount!');
            err++;
        } else {
            $("#refund_amount").removeClass('erroralert');
        }

        if(paymentMode==""){
            err++;
            $("#paymentMode").addClass('LV_invalid_field');
            alert("Please select payment mode");
        } else {
            
            var val = $("#paymentMode option:selected").text();
            val = val.toUpperCase();
            if (val == 'CHEQUE' || val == 'DD') {
                if($("#bankId").val() == ''){
                    $("#bankId option:selected").addClass('erroralert');
                    alert('Please Select Bank Name!');
                    err++;
                } else {
                    $("#bankId").removeClass('erroralert');
                }
                if($("#dd_cheque_date").val() == ''){
                    $("#dd_cheque_date").addClass('erroralert');
                    alert('Please Insert DD/Cheque Date!');
                    err++;
                } else {
                    $("#dd_cheque_date").removeClass('erroralert');
                }
                if($("#dd_cheque_no").val() == ''){
                    $("#dd_cheque_no").addClass('erroralert');
                    alert('Please Insert DD/Cheque No!');
                    err++;
                } else {
                    $("#dd_cheque_no").removeClass('erroralert');
                }

                if($("#dd_cheque_amount").val() == ''){
                    $("#dd_cheque_amount").addClass('erroralert');
                    alert('Please Insert DD/Cheque Amount!');
                    err++;
                } else {
                    $("#dd_cheque_amount").removeClass('erroralert');
                }
            }    
            $("#paymentMode").removeClass('LV_invalid_field');

            
        }
        if(err==0){
            $("#refundAmount").submit();
        }
   });
  
</script>