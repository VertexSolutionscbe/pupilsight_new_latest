<?php
/*
Pupilsight, Flexible & Open School System
*/
use Pupilsight\Forms\Form;
use Pupilsight\Forms\DatabaseFormFactory;
use Pupilsight\Tables\DataTable;
use Pupilsight\Services\Format;
use Pupilsight\Domain\Finance\FeesGateway;
use Pupilsight\Domain\Helper\HelperGateway;

if (isActionAccessible($guid, $connection2, '/modules/Finance/fee_transaction_manage.php') == false) {
    //Acess denied
    echo "<div class='error'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    $HelperGateway = $container->get(HelperGateway::class);
    //Proceed!
    $page->breadcrumbs->add(__('Search Transaction'));

    if (isset($_GET['return'])) {
        returnProcess($guid, $_GET['return'], null, null);
    }

   
    $sections = array(
        'Sectionsa' => __('Sections A'),
        'Sectionsb' => __('Sections B'),
        'Sectionsc' => __('Sections C'),
         'Sectionsd' => __('Sections D'),
    );
    $school_list = array(
        '' => __('select School Name'),
        'School name' => __('School name A'),
        'Sectionsb' => __('School name B'),
        'Sectionsc' => __('School name C'),
         'Sectionsd' => __('School name D'),
    );

    
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
        '' => __('Select Payment Status'),
        'Payment Received' => __('Payment Received')
        
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
        //->pageSize(1)
        ->sortBy(['id'])
        ->fromPOST();

    if($_POST){
        $pupilsightProgramID = $_POST['pupilsightProgramID'];
        $input = $_POST;
        $pupilsightYearGroupID =  $_POST['pupilsightYearGroupID'];
        $pupilsightRollGroupID =  $_POST['pupilsightRollGroupID'];
        $pupilsightPersonID =  $_POST['pupilsightPersonID'];
        $student_name = $_POST['student_name'];
        $receipt_number =  $_POST['receipt_number'];
        $transaction_id =  $_POST['transaction_id'];
        $bank_id =  $_POST['bank_id'];
        $payment_mode_id =  $_POST['payment_mode_id'];
        $startdate = $_POST['startdate'];
        $enddate =  $_POST['enddate'];

        $classes =  $HelperGateway->getClassByProgram($connection2, $pupilsightProgramID);
        $sections =  $HelperGateway->getSectionByProgram($connection2, $pupilsightYearGroupID,  $pupilsightProgramID);
    } else {
        $classes = array('' => 'Select Class');
        $sections = array('' => 'Select Section');
        $input = ''; 
        $pupilsightYearGroupID =  '';
        $pupilsightRollGroupID =  '';
        $pupilsightPersonID =  '';
        $student_name = '';
        $receipt_number =  '';
        $transaction_id =  '';
        $bank_id =  '';
        $payment_mode_id =  '';
        $startdate = '';
        $enddate =  '';
        unset($_SESSION['trnsaction_search']);
    }

    if(!empty($pupilsightProgramID)){
        $_SESSION['trnsaction_search'] = $input;
    }
    
    $sqlp = 'SELECT pupilsightProgramID, name FROM pupilsightProgram ';
    $resultp = $connection2->query($sqlp);
    $rowdataprog = $resultp->fetchAll();
    $program=array();  
    $program2=array();  
    $program1=array(''=>'Select Program');
    foreach ($rowdataprog as $dt) {
        $program2[$dt['pupilsightProgramID']] = $dt['name'];
    }
    $program= $program1 + $program2;  

    $feeTransaction = $FeesGateway->getFeesTransaction($criteria, $input, $pupilsightSchoolYearID);

    $c_query = $FeesGateway->getFeesTransactiontotal($criteria, $input, $pupilsightSchoolYearID);
    //$total = $FeesGateway->getFeesCancelTransactionTotalCount($criteria, $input, $pupilsightSchoolYearID);
    $sqldr =$c_query;
    $resultdr = $connection2->query($sqldr);
    $master = $resultdr->fetchAll();
    $t_amount=0;
    foreach($master as $am){
        $t_amount+=$am['transcation_amount'];
    }
    $kountTransaction = count($master);

    $form = Form::create('program', $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.$_SESSION[$guid]['module'].'/fee_transaction_manage.php')->addClass('newform');
    $form->setFactory(DatabaseFormFactory::create($pdo));

    $form->addHiddenValue('address', $_SESSION[$guid]['address']);

   
    $row = $form->addRow();
        $col = $row->addColumn()->setClass('newdes');
        $col->addLabel('pupilsightProgramID', __('Program'));
        $col->addSelect('pupilsightProgramID')->fromArray($program)->selected($pupilsightProgramID)->required()->placeholder();

            $col = $row->addColumn()->setClass('newdes');
            $col->addLabel('pupilsightYearGroupID', __('Class'));
            $col->addSelect('pupilsightYearGroupID')->fromArray($classes)->selected($pupilsightYearGroupID);

            $col = $row->addColumn()->setClass('newdes');
            $col->addLabel('pupilsightRollGroupID', __('Section'));
            $col->addSelect('pupilsightRollGroupID')->fromArray($sections)->selected($pupilsightRollGroupID);

            $col = $row->addColumn()->setClass('newdes');
            $col->addLabel('pupilsightPersonID', __('Student Id'));
            $col->addTextField('pupilsightPersonID')->setValue($pupilsightPersonID)->addClass('txtfield');
            
            $col = $row->addColumn()->setClass('newdes');
            $col->addLabel('student_name', __('Student Name'));
            $col->addTextField('student_name')->setValue($student_name)->addClass('txtfield');
            
            $col = $row->addColumn()->setClass('newdes');
            $col->addLabel('receipt_number', __('Receipt No'));
            $col->addTextField('receipt_number')->setValue($receipt_number)->addClass('txtfield');
         
        
        

    $row = $form->addRow();

   
            $col = $row->addColumn()->setClass('newdes');
            $col->addLabel('transaction_id', __('Transaction Id'));
            $col->addTextField('transaction_id')->setValue($transaction_id)->addClass('txtfield');

            // $col = $row->addColumn()->setClass('newdes');
            // $col->addLabel('schoolnlist', __('Schools List'));
            // $col->addSelect('schoolnlist')->fromArray($school_list);

            $col = $row->addColumn()->setClass('newdes');
            $col->addLabel('bank_id', __(' Bank'));
            $col->addSelect('bank_id')->selected($bank_id)->fromArray($bank);
            
            $col = $row->addColumn()->setClass('newdes');
            $col->addLabel('payment_mode_id', __('Payment Mode'));
            $col->addSelect('payment_mode_id')->selected($payment_mode_id)->fromArray($paymentmode);
            
            // $col = $row->addColumn()->setClass('newdes');
            // $col->addLabel('select transaction status', __('Transaction'));
            // $col->addSelect('tran_status')->fromArray($tran_status);
            
            $col = $row->addColumn()->setClass('newdes');
            $col->addLabel('startdate', __('From Date'))->addClass('dte');
            $col->addDate('startdate')->setValue($startdate)->addClass('txtfield'); 

            $col = $row->addColumn()->setClass('newdes');
            $col->addLabel('enddate', __('End Date'))->addClass('dte');
            $col->addDate('enddate')->setValue($enddate)->addClass('txtfield');

            $col = $row->addColumn()->setClass('newdes');
            $col->addLabel(' ', __(' '));
            $col->addContent('<button class=" btn btn-primary">Search</button>');
            
            
            // $col = $row->addColumn()->setClass('hiddencol');
            // $col->addLabel('', __(''));
            // $col->addTextField('');  

            $row = $form->addRow();
            //$col = $row->addColumn()->setClass('newdes');
            // $col->addLabel('select', __('Instrument No'));
            //$col->addSelect('transaction_status')->setId('transStatus')->fromArray($transaction);

            //$col = $row->addColumn()->setClass('newdes');
          //  $col->addLabel('enddate', __('Select End Date'))->addClass('dte');
            //$col->addDate('date')->addClass('txtfield')->readonly()->required();

            $col = $row->addColumn()->setClass('newdes');
                $col->addContent('<a id="transactionStatusChange" class=" btn btn-primary">Update Transaction Status</a> <a id="updateTransactionStatus" href="fullscreen.php?q=/modules/Finance/fee_transaction_update.php" class="thickbox  btn btn-primary" style="display:none;">Update Transaction Status</a>&nbsp;&nbsp;<a id="cancelTransaction" class=" btn btn-primary">Cancel</a><a style="display:none;" id="cancelTransactionSubmit" href="fullscreen.php?q=/modules/Finance/fee_transaction_cancel.php"  class="thickbox " >CancelSubmit</a>&nbsp;&nbsp;<a id="refundTransaction"  class=" btn btn-primary" >Refund</a> <a style="display:none;" class="thickbox " id="refundTransactionSubmit" href="fullscreen.php?q=/modules/Finance/fee_transaction_refund.php&width=800" >RefundSubmit</a>');

            // $col = $row->addColumn()->setClass('newdes');
            //     $col->addContent('<a id="cancelTransaction" class=" btn btn-primary">Cancel</a><a style="display:none;" id="cancelTransactionSubmit" href="fullscreen.php?q=/modules/Finance/fee_transaction_cancel.php"  class="thickbox " >CancelSubmit</a>');
            // $col = $row->addColumn()->setClass('newdes');
            //     $col->addContent('<a id="refundTransaction"  class=" btn btn-primary" >Refund</a> <a style="display:none;" class="thickbox " id="refundTransactionSubmit" href="fullscreen.php?q=/modules/Finance/fee_transaction_refund.php&width=800" >RefundSubmit</a>');

            $col = $row->addColumn()->setClass('newdes');
                $col->addContent('<a id="receipt_export" class=" btn btn-primary">Download Receipts <i class="fas fa-download" aria-hidden="true"></i></a><a id="downloadLink" data-hrf="index.php?q=/modules/Finance/ajaxfile.php&id=" href="index.php?q=/modules/Finance/ajaxfile.php" class="" style="display:none;">Download Receipts</a>');    
            
        $row = $form->addRow()->addClass('tran_tbl');
            $col = $row->addColumn()->setClass('newdes');
            $col->addLabel('', __('No of Transaction : '.$kountTransaction));

        $col = $row->addColumn()->setClass('newdes');
        $col->addLabel('', __('Total Transaction Amount: '.number_format($t_amount,2)));
              
        $col = $row->addColumn()->setClass('newdes');
        $col->addContent('<a style="color:#666;cursor:pointer;" id="export_transaction"><span style="position: absolute;  left: 220px;"><i class="fas fa-file-export"></i>Export</span></a>');

            // $col = $row->addColumn()->setClass('newdes');
            // $col->addLabel('', __('Export:'));
        



    echo $form->getOutput();



    // DATA TABLE
    $table = DataTable::createPaginated('FeeCounterManage', $criteria);


    $table->addCheckboxColumn('collection_id',__(''))->notSortable();
    $table->addColumn('receipt_number', __('Receipt No'));
    $table->addColumn('invoice_no', __('Invoice No'));
    $table->addColumn('student_name', __('Name'));
    $table->addColumn('stu_id', __('Student ID'));
    $table->addColumn('transaction_id', __('Transaction Id'));
    $table->addColumn('paymentmode', __('payment Mode'));
    $table->addColumn('bankname', __('Bank name'));
    $table->addColumn('total_amount_without_fine_discount', __('Amount'));
    $table->addColumn('fine', __('Fine Amount'));
    $table->addColumn('discount', __('Discount'));
   
    $table->addColumn('transcation_amount', __('Transaction Amount'));
    $table->addColumn('amount_paying', __('Amount Paid'));
    $table->addColumn('payment_date', __('Payment Date'));
    $table->addColumn('payment_status', __('Payment Status'));
    $table->addColumn('print', __('Print Receipt'))
         ->format(function ($dataSet) {
             if (!empty($dataSet['transaction_id'])) {
                 return '<a href="public/receipts/'.$dataSet['transaction_id'].'.docx"  download><i class="fas fa-receipt"></i></a>';
             } else {
                return 'Stoped';
             }
             return $dataSet['status'];
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

    echo $table->render($feeTransaction);

    //echo formatName('', $row['preferredName'], $row['surname'], 'Staff', false, true);
}

?>
<style>
    .download_icon {
        float: right;
        font-size: 30px;
        color: green;
        margin: 6px;
        cursor: pointer;
    }
</style>

<script>

    // $(document).on('click', '#receipt_export', function() {
    //    var checked = $("input[name='collection_id[]']:checked").length;
    //     if (checked >= 1) {
    //         var favorite = [];
    //         $.each($("input[name='collection_id[]']:checked"), function() {
    //             var id = $(this).val();
    //             var nme = $("#"+id+"-id").val();
    //             var link = document.createElement('a');
    //             link.href = "public/receipts/"+nme+".docx";
    //             link.download = nme+".docx";
    //             link.click();
    //         });
    //     } else {
    //         alert('You Have to Select Transction');
    //     }
    // });

    $(document).on('click', '#receipt_export', function() {
       var checked = $("input[name='collection_id[]']:checked").length;
        if (checked >= 1) {
            var favorite = [];
            $.each($("input[name='collection_id[]']:checked"), function() {
                favorite.push($(this).val());
            });
            var collid = favorite.join(",");
            var url = $("#downloadLink").attr('data-hrf');
            var newurl = url+collid;
            if(collid != ''){
                $("#downloadLink").attr('href',newurl);
                window.setTimeout(function() {
                    $("#downloadLink")[0].click();
                }, 100);
            }
        } else {
            alert('You Have to Select Transction');
        }
    });


    
</script>
