<?php
/*
Pupilsight, Flexible & Open School System
*/
use Pupilsight\Forms\Form;
use Pupilsight\Forms\DatabaseFormFactory;
use Pupilsight\Tables\DataTable;
use Pupilsight\Services\Format;
use Pupilsight\Domain\Finance\FeesGateway;

if (isActionAccessible($guid, $connection2, '/modules/Finance/fee_transaction_refund_manage.php') == false) {
    //Acess denied
    echo "<div class='error'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    //Proceed!
    $page->breadcrumbs->add(__('Search Refund Transaction '));

    if (isset($_GET['return'])) {
        returnProcess($guid, $_GET['return'], null, null);
    }

   
    

    
    $sqldr = 'SELECT * FROM fn_masters ';
    $resultdr = $connection2->query($sqldr);
    $master = $resultdr->fetchAll();

    $paymentmode = array();
    $paymentmode1 = array(''=>'Select Payment Mode');
    $paymentmode2 = array();

    $bank = array();
    $bank1 = array(''=>'Select Bank');
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

    $transaction = array(
        '' => __('Select'),
        
    );
    
    
    $tran_status =  array(
        ''=>__('Select Transaction Status')
    );

    $pupilsightSchoolYearID = '';
    if (isset($_GET['pupilsightSchoolYearID'])) {
        $pupilsightSchoolYearID = $_GET['pupilsightSchoolYearID'];
    }
    if ($pupilsightSchoolYearID == '' or $pupilsightSchoolYearID == $_SESSION[$guid]['pupilsightSchoolYearID']) {
        $pupilsightSchoolYearID = $_SESSION[$guid]['pupilsightSchoolYearID'];
        $pupilsightSchoolYearName = $_SESSION[$guid]['pupilsightSchoolYearName'];
    }
    
    $FeesGateway = $container->get(FeesGateway::class);

    // QUERY
    $criteria = $FeesGateway->newQueryCriteria()
        //->sortBy(['id'])
        ->fromPOST();

    
    if($_POST){
        $input = $_POST; 
        $admission_no =  $_POST['admission_no'];
        $student_name = $_POST['student_name'];
        $receipt_number =  $_POST['receipt_number'];
        $transaction_id =  $_POST['transaction_id'];
        $instrument_no =  $_POST['instrument_no'];
        $bank_id =  $_POST['bank_id'];
        $startdate = $_POST['startdate'];
        $enddate =  $_POST['enddate'];
    } else {
        $input = ''; 
        $admission_no =  '';
        $student_name = '';
        $receipt_number =  '';
        $transaction_id =  '';
        $instrument_no =  '';
        $bank_id =  '';
        $startdate = '';
        $enddate =  '';
        unset($_SESSION['ref_trnsaction_search']);
    }

    if(!empty($input)){
        $_SESSION['ref_trnsaction_search'] = $input;
    }

    //if($_POST){

        $feeTransaction = $FeesGateway->getFeesRefundTransaction($criteria, $input, $pupilsightSchoolYearID);
        // echo "<pre>";
        //     print_r($feeTransaction);
        $c_query = $FeesGateway->getFeesRefundTransactionTotal($criteria, $input, $pupilsightSchoolYearID);
        //$total = $FeesGateway->getFeesCancelTransactionTotalCount($criteria, $input, $pupilsightSchoolYearID);
        $sqldr =$c_query;
        $resultdr = $connection2->query($sqldr);
        $FeesRefund = $resultdr->fetchAll();
        $t_amount=0;
        foreach($FeesRefund as $am){
            $t_amount+=$am['refund_amount'];
        }
        $kountTransaction = count($FeesRefund);
    // } else {
    //     $kountTransaction = 0;
    //     $t_amount = 0;
    // }
    
    $form = Form::create('program', $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.$_SESSION[$guid]['module'].'/fee_transaction_refund_manage.php')->addClass('newform');
    $form->setFactory(DatabaseFormFactory::create($pdo));

    $form->addHiddenValue('address', $_SESSION[$guid]['address']);

    
    $row = $form->addRow();
        
            $col = $row->addColumn()->setClass('newdes');
            $col->addLabel('admission_no', __('Admission No'));
            $col->addTextField('admission_no')->setValue($admission_no)->addClass('txtfield');
            
            $col = $row->addColumn()->setClass('newdes');
            $col->addLabel('student_name', __('Student Name'));
            $col->addTextField('student_name')->setValue($student_name)->addClass('txtfield');
            
            $col = $row->addColumn()->setClass('newdes');
            $col->addLabel('receipt_number', __('Refund Receipt No'));
            $col->addTextField('receipt_number')->setValue($receipt_number)->addClass('txtfield');
            
            $col = $row->addColumn()->setClass('newdes');
            $col->addLabel('transaction_id', __('Refund Transaction Id'));
            $col->addTextField('transaction_id')->setValue($transaction_id)->addClass('txtfield');

            $col = $row->addColumn()->setClass('newdes');
            $col->addLabel('instrument_no', __('Instrument No'));
            $col->addTextField('instrument_no')->setValue($instrument_no)->addClass('txtfield');
        

    $row = $form->addRow();


            $col = $row->addColumn()->setClass('newdes');
            $col->addLabel('bank_id', __(' Bank'));
            $col->addSelect('bank_id')->selected($bank_id)->fromArray($bank);
            
            
            $col = $row->addColumn()->setClass('newdes');
            $col->addLabel('startdate', __('From Date'))->addClass('dte');
            $col->addDate('startdate')->setValue($startdate)->addClass('txtfield'); 

            $col = $row->addColumn()->setClass('newdes');
            $col->addLabel('enddate', __('End Date'))->addClass('dte');
            $col->addDate('enddate')->setValue($enddate)->addClass('txtfield');

            $col = $row->addColumn()->setClass('newdes');
            $col->addLabel(' ', __(' '));
            $col->addContent('<button class=" btn btn-primary">Search</button>&nbsp;&nbsp;<a style="color:#666;cursor:pointer;" id="export_refund_transaction" class="btn btn-primary">Export</a>');
            
            

        $row = $form->addRow()->addClass('tran_tbl');
        $col = $row->addColumn()->setClass('newdes');
        $col->addLabel('', __('No of Transaction : '.$kountTransaction));

        $col = $row->addColumn()->setClass('newdes');
        $col->addLabel('', __('Total Refund Transaction Amount : '.number_format($t_amount,2)));
              
        // $col = $row->addColumn()->setClass('newdes');
        // $col->addContent('<button style="color:#666" id="export_transaction"><span  style="position: absolute;  left: 220px;
        // bottom: 1px;"><i class="fas fa-file-export"></i>Export</span></button>');

        // $col = $row->addColumn()->setClass('newdes');
        // $col->addLabel('', __('Export:'));
       



    echo $form->getOutput();



    // DATA TABLE
    $table = DataTable::createPaginated('FeeCounterManage', $criteria);


    $table->addCheckboxColumn('id',__(''))->notSortable();
    $table->addColumn('refund_receipt_number', __('Receipt No'));
    $table->addColumn('invoice_no', __('Invoice No'));
    $table->addColumn('student_name', __('Name'));
    $table->addColumn('pupilsightPersonID', __('Student ID'));
    $table->addColumn('transaction_id', __('Transaction ID'));
    $table->addColumn('refund_date', __('Refund Date'))
    ->format(function ($feeTransaction) {
        if ($feeTransaction['refund_date'] == '1970-01-01') {
            return '';
        } else {
            $dt = date('d/m/Y', strtotime($feeTransaction['refund_date']));
           return $dt;
        }
        return $feeTransaction['refund_date'];
    });
    $table->addColumn('paymentmode', __('Refund Mode'));
    $table->addColumn('dd_cheque_no', __('DD/Cheque No'));
    $table->addColumn('bankname', __('Bank name'));
    $table->addColumn('dd_cheque_date', __('DD/Cheque Date'))
    ->format(function ($feeTransaction) {
        if ($feeTransaction['dd_cheque_date'] == '0000-00-00') {
            return '';
        } else {
            $dt = date('d/m/Y', strtotime($feeTransaction['dd_cheque_date']));
           return $dt;
        }
        return $feeTransaction['dd_cheque_date'];
    });
    $table->addColumn('remarks', __('Remarks'));
    $table->addColumn('transaction_amount', __('Transaction Amount'));
    $table->addColumn('refund_amount', __('Refund Amount'))->addClass('bikashrefund');
    
    $table->addColumn('transaction_pending_amount', __('Pending Transaction Amount'))
    ->format(function ($feeTransaction) {
        if (!empty($feeTransaction['transaction_pending_amount'])) {
            $pendingAmt = $feeTransaction['transaction_amount'] - $feeTransaction['refund_amount'];
            return number_format($pendingAmt, 2);
        } else {
            return number_format($feeTransaction['transaction_amount'], 2);
        }
        return $feeTransaction['transaction_pending_amount'];
    });

    $table->addColumn('refundby', __('Refund By'));
    $table->addColumn('print', __('Print Receipt'))
    ->format(function ($feeTransaction) {
        if (!empty($feeTransaction['transaction_id'])) {
            return '<a href="public/refundreceipts/'.$feeTransaction['transaction_id'].'.docx"  download><i class="mdi mdi-download mdi-24px"></i></a>';
        } else {
           return 'Stoped';
        }
        return $feeTransaction['status'];
});


        
    // ACTIONS
    // $table->addActionColumn()
    //     ->addParam('id')
    //     ->format(function ($facilities, $actions) use ($guid) {
    //         $actions->addAction('editnew', __('Edit'))
    //                 ->setURL('/modules/Finance/fee_series_manage_edit.php');

    //         $actions->addAction('delete', __('Delete'))
    //                 ->setURL('/modules/Finance/fee_series_manage_delete.php');
    //     });

    //if($_POST){
        echo $table->render($feeTransaction);
    //}

    //echo formatName('', $row['preferredName'], $row['surname'], 'Staff', false, true);
}



?>
<style>
   
    .table-responsive {
        height : 500px !important;
    }

</style>

<script>

    $(document).ready(function() {
        $('#expore_tbl').find("input[name='id[]']").each(function() {
            $(this).addClass('include_cell');
            $(this).closest('tr').addClass('rm_cell');
            
        });


        $(document).on('change', '.include_cell', function() {
            if ($(this).is(":checked")) {
                $(this).closest('tr').removeClass('rm_cell');
            } else {
                $(this).closest('tr').addClass('rm_cell');
            }
        });
    });

    $(document).on('click', '#export_refund_transaction', function () {
        var submit_ids = [];
        $.each($("input[name='id[]']:checked"), function () {
            submit_ids.push($(this).val());
        });
        var submt_id = submit_ids.join(",");

        if (submt_id == '') {
            alert('You Have to Select Transaction.');
        } else {
            $('#expore_tbl tr').find('td:eq(0),th:eq(0)').remove();
            $("#expore_tbl").table2excel({
                name: "Worksheet Name",
                filename: "refund_transaction.xls",
                fileext: ".xls",
                exclude: ".checkall",
                exclude: ".rm_cell",
                exclude_inputs: true,
                columns: [0, 1, 2, 3, 4, 5]

            });
            location.reload();
        }
    });
</script>