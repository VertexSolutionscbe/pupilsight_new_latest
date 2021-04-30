<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Forms\Form;
use Pupilsight\Tables\DataTable;
use Pupilsight\Services\Format;
use Pupilsight\Forms\DatabaseFormFactory;
use Pupilsight\Domain\Finance\FeesGateway;
use Pupilsight\Domain\Helper\HelperGateway;


if (isActionAccessible($guid, $connection2, '/modules/Finance/fee_collection_manage.php') == false) {
    //Acess denied
    echo "<div class='error'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    $HelperGateway = $container->get(HelperGateway::class);
    $cuid = $_SESSION[$guid]['pupilsightPersonID'];
    $cdate = date('Y-m-d');

    $countersql = 'SELECT id FROM fn_fees_counter ';
    $resultcounter = $connection2->query($countersql);
    $counterData = $resultcounter->fetch();
    
    if(!empty($counterData)){
        $sqlp = 'SELECT a.id FROM fn_fees_counter_map AS a LEFT JOIN fn_fees_counter AS b ON a.fn_fees_counter_id = b.id WHERE a.pupilsightPersonID = "'.$cuid.'" AND a.active_date = "'.$cdate.'" AND b.status = "1" ';
        $resultp = $connection2->query($sqlp);
        $chkcounter = $resultp->fetchAll();
        //print_r($chkcounter);
        if(empty($chkcounter)){
            $returnURL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/Finance/fee_counter_check_add.php';
            header("Location: {$returnURL}");
        }
    }

    //Proceed!
    $page->breadcrumbs
    ->add(__('Finance'), 'fee_collection_manage.php')
    ->add(__('Collection'));

    if (isset($_GET['return'])) {
        returnProcess($guid, $_GET['return'], null, null);
    }

    $pupilsightSchoolYearID = '';
    if (isset($_GET['pupilsightSchoolYearID'])) {
        $pupilsightSchoolYearID = $_GET['pupilsightSchoolYearID'];
    }
    if ($pupilsightSchoolYearID == '' or $pupilsightSchoolYearID == $_SESSION[$guid]['pupilsightSchoolYearID']) {
        $pupilsightSchoolYearID = $_SESSION[$guid]['pupilsightSchoolYearID'];
        $pupilsightSchoolYearName = $_SESSION[$guid]['pupilsightSchoolYearName'];
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

    $sqlq = 'SELECT pupilsightSchoolYearID, name FROM pupilsightSchoolYear ';
    $resultval = $connection2->query($sqlq);
         $rowdata = $resultval->fetchAll();
         $academic=array();
         $ayear = '';
        if(!empty($rowdata)){
            $ayear = $rowdata[0]['name'];
            foreach ($rowdata as $dt) {
                $academic[$dt['pupilsightSchoolYearID']] = $dt['name'];
            }
        }

    $searchby = array(''=>'Search By', 'stu_name'=>'Student Name', 'stu_id'=>'Student Id', 'adm_id'=>'Admission Id', 'father_name'=>'Father Name', 'father_email'=>'Father Email', 'mother_name'=>'Mother Name', 'mother_email'=>'Mother Email');

    // echo '<pre>';
    // print_r($_POST);    
    // echo '</pre>';
    // die();
    if($_POST){
        $_SESSION['search_fields']=$_POST;
        $pupilsightProgramID =  $_POST['pupilsightProgramID'];
        $pupilsightSchoolYearIDpost = $_POST['pupilsightSchoolYearID'];
        $pupilsightYearGroupID =  $_POST['pupilsightYearGroupID'];
        $pupilsightRollGroupID =  $_POST['pupilsightRollGroupID'];
        if(!empty($_POST['simplesearch'])){
            $search =  $_POST['simplesearch'];
            $searchbyPost =  $_POST['simplesearch'];
        } 
        if(!empty($_POST['search'])){
            $search =  $_POST['search'];
            $searchbyPost =  $_POST['search'];
        } 
        $stuId = $_POST['studentId'];
        $classes =  $HelperGateway->getClassByProgram($connection2, $pupilsightProgramID, $pupilsightSchoolYearIDpost);
        $sections =  $HelperGateway->getSectionByProgram($connection2, $pupilsightYearGroupID,  $pupilsightProgramID, $pupilsightSchoolYearIDpost);
    } else {
        $pupilsightProgramID =  '';
        $pupilsightSchoolYearIDpost =  $pupilsightSchoolYearID;
        $pupilsightYearGroupID =  '';
        $pupilsightRollGroupID =  '';
        $searchbyPost =  '';
        $search = '';
        $stuId = '0';
        $classes = array('' => 'Select Class');
        $sections = array('' => 'Select Section');
    }

    
    $searchform = Form::create('searchForm','');
    $searchform->setFactory(DatabaseFormFactory::create($pdo));
    $searchform->addHiddenValue('studentId', '0');

    $row = $searchform->addRow()->setId('normalSearchRow');
    $col = $row->addColumn()->setClass('newdes');    
        //$col->addLabel('', __(''));
        $col->addTextField('simplesearch')->placeholder('Search by Student Name, ID')->addClass('txtfield')->setValue($searchbyPost);

    $col = $row->addColumn()->setClass('newdes');   
    $col->addContent('<a id="searchStudent" class="transactionButton btn btn-primary">Search</a><button id="simplesubmitInvoice" style="display:none;" class="transactionButton btn btn-primary">Submit</button>&nbsp;&nbsp;<a id="advanceSearch" class="transactionButton btn btn-primary" >Advance Search</a>');   
    
    // $col->addContent('<button id="simplesubmitInvoice" style="display:none;" class="transactionButton btn btn-primary">Submit</button>');  
    // $col->addContent('<a id="advanceSearch" class="transactionButton btn btn-primary" style="position:absolute; right:0;">Advance Search</a>');
    // $col->addContent('<i style="font-size:25px; margin:5px 10px;cursor:pointer;" title="Advance Search" id="advanceSearch" class="fas fa-search-plus"></i>');   

    $row = $searchform->addRow()->setId('advanceSearchRow')->setClass('hiddencol');

    $col = $row->addColumn()->setClass('newdes advsrch');
        $col->addLabel('pupilsightSchoolYearID', __('Academic Year'));
        $col->addSelect('pupilsightSchoolYearID')->fromArray($academic)->selected($pupilsightSchoolYearIDpost);   
        
    $col = $row->addColumn()->setClass('newdes advsrch');
        $col->addLabel('pupilsightProgramID', __('Program')); 
        $col->addSelect('pupilsightProgramID')->fromArray($program)->selected($pupilsightProgramID)->placeholder();

     
        
    $col = $row->addColumn()->setClass('newdes advsrch');
        $col->addLabel('pupilsightYearGroupID', __('Class'));
        $col->addSelect('pupilsightYearGroupID')->fromArray($classes)->selected($pupilsightYearGroupID);

    $col = $row->addColumn()->setClass('newdes advsrch');
        $col->addLabel('pupilsightRollGroupID', __('Section'));
        $col->addSelect('pupilsightRollGroupID')->fromArray($sections)->selected($pupilsightRollGroupID);

    // $col = $row->addColumn()->setClass('newdes');
    //     $col->addLabel('searchby', __('Search By'));
    //     $col->addSelect('searchby')->fromArray($searchby)->selected($searchbyPost)->required();    

    $col = $row->addColumn()->setClass('newdes advsrch');    
        $col->addLabel('search', __('Search'));
        $col->addTextField('search')->addClass('txtfield')->setValue($search);

    $col = $row->addColumn()->setClass('newdes');   
    $col->addLabel('', __(''));
    $col->addContent('<a id="searchInvoice" class="transactionButton btn btn-primary">Search</a><button id="submitInvoice" style="display:none;" class="transactionButton btn btn-primary">Submit</button>&nbsp;&nbsp;<a id="normalSearch" class="transactionButton btn btn-primary" style="position:absolute; ">Normal Search</a>');   
    
   
    echo '<input type="hidden" id="searchCollectionType" value="">';
    echo $searchform->getOutput();
    
    echo "<div class ='row fee_hdr hideStudentListContent' style='display:none'><div class='col-md-12'> Students</div></div>";

        echo "<table class='table' cellspacing='0' id='stuListTable' style='width: 100%;display:none' >";
        echo "<thead>";
        echo "<tr class='head'>";
        echo '<th style="width: 10%;">';
        echo __('Select');
        echo '</th>';
        echo '<th style="width: 20%;">';
        echo __('Admission No');
        echo '</th>';
        echo '<th style="width: 20%;">';
        echo __('Student Name');
        echo '</th>';
        
        echo '<th style>';
        echo __('Father Name');
        echo '</th>';
        echo '<th style="width: 20%;">';
        echo __('Mother Name');
        echo '</th>';
        echo '<th style="width: 10%;">';
        echo __('Program');
        echo '</th>';
        echo '<th style="width: 10%;">';
        echo __('Class');
        echo '</th>';
        echo '<th style="width: 10%;">';
        echo __('Section');
        echo '</th>';
        echo "</thead>";
        echo "<tbody id='studentList'>";
        echo "</tbody>";
        echo '</table>';
    
    if($_POST){

        $FeesGateway = $container->get(FeesGateway::class);
        
        $criteria = $FeesGateway->newQueryCriteria()
            ->sortBy(['id'])
            ->fromPOST();

        $sqlh = 'SELECT id, name FROM fn_fees_head ';
        $resulth = $connection2->query($sqlh);
        $feeHead = $resulth->fetchAll();

        $feeHeadData = array();
        $feeHeadData1 = array(''=>'Select Account Head');
        $feeHeadData2 = array();
        foreach ($feeHead as $fd) {
            $feeHeadData2[$fd['id']] = $fd['name'];
        }
        $feeHeadData = $feeHeadData1 + $feeHeadData2;  
        
        
        $sqlrc = 'SELECT id, series_name FROM fn_fee_series WHERE type = "Finance" ';
        $resultrc = $connection2->query($sqlrc);
        $rseries = $resultrc->fetchAll();

        $receipt_series = array();
        $receipt_series1 = array(''=>'Select Series');
        $receipt_series2 = array();
        foreach ($rseries as $fd) {
            $receipt_series2[$fd['id']] = $fd['series_name'];
        }
        $receipt_series = $receipt_series1 + $receipt_series2; 

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

        $sqlda = 'SELECT id, ac_name FROM fn_fees_deposit_account WHERE overpayment_account = "0" ';
        $resultda = $connection2->query($sqlda);
        $cauDataAcct = $resultda->fetchAll();

        $cauData = array();
        $cauData1 = array(''=>'Select');
        $cauData2 = array();
        foreach ($cauDataAcct as $fd) {
            $cauData2[$fd['id']] = $fd['ac_name'];
        }
        $cauData = $cauData1 + $cauData2; 

        $sqlstu = 'SELECT a.pupilsightPersonID, a.officialName, a.admission_no,  d.name as class, e.name as section FROM pupilsightPerson AS a LEFT JOIN pupilsightStudentEnrolment AS b ON a.pupilsightPersonID = b.pupilsightPersonID  LEFT JOIN pupilsightYearGroup AS d ON b.pupilsightYearGroupID = d.pupilsightYearGroupID LEFT JOIN pupilsightRollGroup AS e ON b.pupilsightRollGroupID = e.pupilsightRollGroupID 
        WHERE a.pupilsightPersonID = "'.$stuId.'" ';
       
        $resultstu = $connection2->query($sqlstu);
        $studetails = $resultstu->fetch();
        ?>
        <div id="showPaymentScreen">
<div style="background: lightgray;font-size: 18px;font-weight: 600;color: darkslategray; line-height:50px; margin-bottom:10px;">&nbsp;<span>Admission No :
        <?php echo $studetails['admission_no'];?></span>&nbsp;&nbsp;&nbsp;<span>Student Name :
        <?php echo $studetails['officialName'];?></span>&nbsp;&nbsp;&nbsp;<span>Class/Section :
        <?php echo $studetails['class'].'/'.$studetails['section'];?></span><i id="closePaymentScreen" class="mdi mdi-close-circle mdi-24px" style="float: right;margin: 15px 10px 0 0;cursor: pointer;"></i></div>
<?php
        // echo "<div style='height:50px;margin-top:10px;'><div class='float-left mb-2'>";  
        //          echo "<a  id='' data-type='student' class='chkinvoice btn btn-primary'>Apply Invoice</a></div><div class='float-none'></div></div>";
        
        $form = Form::create('collectionForm','');
        $form->setFactory(DatabaseFormFactory::create($pdo));
    
        $form->addHiddenValue('address', $_SESSION[$guid]['address']);
        $form->addHiddenValue('type','collectionForm_request');
        $form->addHiddenValue('pupilsightPersonID', $stuId);
        $form->addHiddenValue('pupilsightSchoolYearID', $pupilsightSchoolYearIDpost);
        $form->addHiddenValue('invoice_id', '0');
        $form->addHiddenValue('invoice_item_id', '0');
        $form->addHiddenValue('chkamount', '0');
        $form->addHiddenValue('invoice_status', 'Fully Paid');
        $form->addHiddenValue('fineold', '');
        $form->addHiddenValue('invno', '0');
       
     
        $row = $form->addRow();
        $col = $row->addColumn()->setClass('float-left submit_ht');
        //#ebebeb
            
        //$col->addSubmit(__('Make Payment With Discount Fee')); 
       // $col->addLabel('', __(' '));
        // $col->addLabel('pupilsightPersonID', __('&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Student Id : '.$studetails['pupilsightPersonID'].' &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'));
        // $col->addLabel('officialName', __('&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Student Name : '.$studetails['officialName'].' &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'));
        // $col->addLabel('class', __('   '.$studetails['class'].'&nbsp;&nbsp;'));
         $col->addLabel('section', __('Make Payment'));
        $col->addContent('<div style="position:absolute; right:0;"><i style="cursor:pointer; font-size:30px; margin: 0 0 0 700px" id="closePayment" class="fa fa-times" aria-hidden="true"></i><input type="hidden" id="checkmode" name="checkmode" value="0"></div>'); 
        
        
        $row = $form->addRow();
                $col = $row->addColumn()->setClass('newdes hiddencol');
                $col->addLabel('', __('Transaction Id'));
                $col->addTextField('transaction_id')->addClass('txtfield')->readOnly()->setValue('');
    
                // $col = $row->addColumn()->setClass('newdes hiddencol');
                // $col->addLabel('receipt_number', __('Receipt Number'))->addClass('dte');
                // $col->addTextField('receipt_number')->addClass('txtfield') ->required();
            
                $col = $row->addColumn()->setClass('hiddencol');
                $col->addLabel('', __(''));
                $col->addTextField('');   
                
                
            
    
        $col = $row->addColumn()->setClass('newdes');
        $col->addLabel('payment_mode_id', __('Payment Mode'));
        $col->addSelect('payment_mode_id')->setId('paymentMode')->fromArray($paymentmode)->addClass(' txtfield');
        echo "<a href='fullscreen.php?q=/modules/Finance/Multiple_payment.php&width=900'  class='thickbox' id='multiplePayment' style='display:none' >multiplePayment</a>";
        $col = $row->addColumn()->setClass('newdes neft_cls hiddencol');
        $col->addLabel('reference_no', __('Reference No '));
        $col->addTextField('reference_no')->setId('reference_no')->placeholder('Enter Reference No');

        $col = $row->addColumn()->setClass('newdes neft_cls hiddencol');
        $col->addLabel('reference_date', __('Reference Date'))->addClass('dte');
        $col->addDate('reference_date')->setValue(Format::date($date));
        
        $col = $row->addColumn()->setClass('newdes hiddencol ddChequeRow');
        $col->addLabel('bank_id', __('Bank Name'));
        $col->addSelect('bank_id')->fromArray($bank)->addClass(' txtfield');


        
        $col = $row->addColumn()->setClass('newdes hiddencol ddDepositRow');
            $col->addLabel('deposit_account_id', __('Deposit Acount'));
            $col->addSelect('deposit_account_id')->fromArray($cauData)->addClass(' txtfield');

        $col = $row->addColumn()->setClass('newdes ddCashRow hiddencol');
            $col->addLabel('payment_status', __('Payment Status'))->addClass('dte');
            $col->addTextField('payment_status')->setId('cashPaymentStatus')->setValue('Payment Received')->readonly()->addClass('txtfield');
    
        $col = $row->addColumn()->setClass('hiddencol');
        $col->addLabel('', __(''));
        $col->addTextField('');    
        
        $row = $form->addRow();
                
    
    $date = isset($_GET['date']) ? Format::dateConvert($_GET['date']) : date('Y-m-d');
            
        $row = $form->addRow()->setClass('hiddencol ddChequeRow');
            
            $col = $row->addColumn()->setClass('newdes ');
            $col->addLabel('dd_cheque_date', __('DD / Cheque Date'))->addClass('dte');
            $col->addDate('dd_cheque_date');
    
            $col = $row->addColumn()->setClass('newdes ');
            $col->addLabel('dd_cheque_no', __('DD / Cheque No'))->addClass('dte');
            $col->addTextField('dd_cheque_no')->addClass('txtfield');
    
            $col = $row->addColumn()->setClass('newdes ');
            $col->addLabel('dd_cheque_amount', __('DD / Cheque Amount'))->addClass('dte');
            $col->addTextField('dd_cheque_amount')->addClass('txtfield   numfield');
    
            $col = $row->addColumn()->setClass('newdes ');
            $col->addLabel('payment_status', __('Payment Status'))->addClass('dte');
            $chqArray = array('Cheque Received' => 'Cheque Received','Payment Received' => 'Payment Received', 'DD Received' => 'DD Received');
            $col->addSelect('payment_status')->fromArray($chqArray)->addClass('txtfield');

            // $row = $form->addRow();

            // $col = $row->addColumn()->setClass('newdes ');
            // $col->addLabel('instrument_no', __('Instrument No'))->addClass('dte');
            // $col->addTextField('instrument_no')->addClass('txtfield');
            
            // $col = $row->addColumn()->setClass('newdes ');
            // $col->addLabel('instrument_date', __('Instrument Date'))->addClass('dte');
            // $col->addDate('instrument_date');

            // $col = $row->addColumn()->setClass('hiddencol');
            // $col->addLabel('', __(''));
            // $col->addTextField('');
    
           
    
    $row = $form->addRow();
        
        $col = $row->addColumn()->setClass('newdes ');
            $col->addLabel('payment_date', __('Payment Date'))->addClass('dte');
            $col->addDate('payment_date')->setValue(Format::date($date))->required();
            
        $col = $row->addColumn()->setClass('newdes ');
            $col->addLabel('fn_fees_head_id', __('Account Head'))->addClass('dte');
            $col->addSelect('fn_fees_head_id')->fromArray($feeHeadData)->addClass(' txtfield');   
            
        $col = $row->addColumn()->setClass('newdes ');
            $col->addLabel('fn_fees_receipt_series_id', __('Receipt Series'))->addClass('dte');
            $col->addSelect('fn_fees_receipt_series_id')->fromArray($receipt_series)->addClass(' txtfield')->setId('recptSerId');   
            
        
        
        $col = $row->addColumn()->setClass('newdes');

        $col->addLabel('Manual Receipt No', __('Manual Receipt No'))->addClass('dte');
        $col->addCheckbox('is_custom', FALSE)->setId('showManualReceipt')->description('No Auto Generate')->addClass('dte')->setValue('1')->inline(TRUE);    
        //$col->addLabel('is_custom', __('Manual Receipt No (No Auto Generate)'));

        $col = $row->addColumn()->setClass('newdes ');
            $col->addLabel('receipt_number', __('Manual Reciept No'))->addClass('dte');
            $col->addTextField('receipt_number')->setId('divManualReceipt')->addClass('txtfield')->disabled('true');
            
   
    

    $row = $form->addRow();
        $col = $row->addColumn()->setClass('newdes ');
            $col->addLabel('transcation_amount', __('Amount to be Paid'))->addClass('dte');
            $col->addTextField('transcation_amount')->addClass('txtfield   numfield')->required()->readonly();    
            
        $col = $row->addColumn()->setClass('newdes ');
            $col->addLabel('fine', __('Fine'))->addClass('dte');
            $col->addTextField('fine')->addClass('txtfield numfield')->readonly();
            
        $col = $row->addColumn()->setClass('newdes ');
            $col->addLabel('discount', __('Discount'))->addClass('dte');
            $col->addTextField('discount')->addClass('txtfield numfield')->readonly();


        $sqlo = 'SELECT SUM(a.amount) AS creditData FROM fn_fees_collection_deposit AS a LEFT JOIN fn_fees_deposit_account AS b ON a.deposit_account_id = b.id WHERE a.pupilsightPersonID = '.$stuId.' AND a.status = "Credit" AND b.overpayment_account = "1"  ';
        $resulto = $connection2->query($sqlo);
        $overPayCreData = $resulto->fetch();

        $sqld = 'SELECT SUM(a.amount) AS debitData FROM fn_fees_collection_deposit AS a LEFT JOIN fn_fees_deposit_account AS b ON a.deposit_account_id = b.id WHERE a.pupilsightPersonID = '.$stuId.' AND a.status = "Debit" AND b.overpayment_account = "1" ';
        $resultd = $connection2->query($sqld);
        $overPayDebData = $resultd->fetch();

        $depAmount = '';
        if(!empty($overPayCreData)){
            $creditdata = $overPayCreData['creditData'];
            if(!empty($overPayDebData)){
                $debitdata = $overPayDebData['debitData'];
                $depAmount = $creditdata - $debitdata;
            } else {
                $depAmount = $creditdata;
            }
        }

        if(!empty($depAmount)){
            $col = $row->addColumn()->setClass('newdes ');
                $col->addLabel('', __(''))->addClass('dte');
                $col->addCheckbox('overpayment', FALSE)->setId('overPayment')->description('Include Overpayment Amount')->addClass('dte')->setValue('1')->inline(TRUE);  
                

            $col = $row->addColumn()->setClass('newdes ');
                $col->addLabel('', __(''))->addClass('dte');
                $col->addTextField('overamount')->addClass('txtfield   numfield')->setValue($depAmount)->readonly();
        } else {
            $col = $row->addColumn()->setClass('newdes hiddencol');
            $col->addContent("<input type='checkbox' id='overPayment' checked><input type='hidden' id='overamount' name='overamount' value='0'>");   
        }

        $col = $row->addColumn()->setClass('newdes ');
            $col->addLabel('amount_paying', __(' Amount Paying'))->addClass('dte');
            $col->addTextField('amount_paying')->addClass('txtfield   numfield')->setValue('')->required()->readonly();

        $col = $row->addColumn()->setClass('newdes hiddencol');
            $col->addLabel('total_amount_without_fine_discount', __(' Amount Paying'))->addClass('dte');
            $col->addTextField('total_amount_without_fine_discount')->addClass('txtfield   numfield')->setValue('');  
            $col->addContent("<input type='hidden' name='transcation_amount_old' id='transcation_amount_old'><input type='hidden' id='amount_paying_old'>");  
    
        // $col = $row->addColumn()->setClass('newdes ');
        //     $col->addLabel('', __(''));
        //     $col->addCheckbox('is_pay')->description(__('Do you want to do payment for selected fee items)'))->addClass(' dte');
    $row = $form->addRow();        
        $col = $row->addColumn()->setClass('newdes ');
            $col->addLabel('remarks', __('Remarks'))->addClass('dte');
            $col->addTextArea('remarks')->addClass('txtfield')->setRows(1);    
           
            $row = $form->addRow()->setId('Make_Payment');
            $col = $row->addColumn();
            $col->addLabel('', __(''));
            $col->addContent('<a id="collectionFormSubmit" class="transactionButton btn btn-primary">Make Payment</a>'); 
                echo "<a style='display:none' href='fullscreen.php?q=/modules/Finance/view_receipt.php&width=600px'  class='thickbox' id='getRecerptPop'>Receipt</a>";
           // $col->addSubmit(__('Make Payment'));  
    $row = $form->addRow();
            $col = $row->addColumn()->setClass('hiddencol');
            $col->addLabel('', __(''));
            $col->addTextField('');   
            echo $form->getOutput();

            echo "<div class ='row fee_hdr hideFeeItemContent divFeeItem feeitem' data-type='0'><div class='col-md-12'> Fee Items<i class='mdi mdi-arrow-right-thick icon_0 icon_m'></i></div></div>";
            echo"<div style='width: 100%;display:none' class='oCls_0 oClose' >";
            echo "<table class='table' cellspacing='0'  id='FeeItemManage'  >";
            echo "<thead>";
            echo "<tr class='head'>";
            echo '<th>';
            echo __('<input type="checkbox" name="" id="chkAllFeeItem" >');
            echo '</th>';
            echo '<th>';
            echo __('Fee Item');
            echo '</th>';
            echo '<th>';
            echo __('Description');
            echo '</th>';
            //echo '<th>';
            //echo __('Invoice No');
            //echo '</th>';
            echo "<th>";
            echo __('Amount');
            echo '</th>';
            echo '<th>';
            echo __('Tax');
            echo '</th>';
            echo '<th>';
            echo __('Amount with Tax');
            echo '</th>';
            echo '<th>';
            echo __('Discount');
            echo '</th>';
            echo "<th>";
            echo __('Amount Discounted');
            echo '</th>';
            echo '<th>';
            echo __('Final Amount');
            echo '</th>';
            echo '<th>';
            echo __('Amount Paid');
            echo '</th>';
            echo '<th>';
            echo __('Amount Pending');
            echo '</th>';
            echo "<th>";
            echo __('Invoice Status');
            echo '</th>';
            echo '</tr>';
            echo "</thead>";
            echo "<tbody id='getInvoiceFeeItem'>";
            echo "</tbody>";
            echo '</table>';
        echo"</div>";
        echo "<div style='height:50px; margin-top:10px; display:none;' id='showPaymentButton'><div class='float-left mb-2'><a id='makePayment' class='hiddencol btn btn-primary' >Do Payment</a>";  
    echo " </div><div class='float-none'></div></div>"; 
        

      
        // echo '<pre>';
        // print_r($invdata);
        // echo '</pre>';
        // die();

        echo "<div style='height:50px;margin-top:10px;'><div class='float-left mb-2'>";  
        echo "<a href='fullscreen.php?q=/modules/Finance/edit_invoice_collection_form.php&width=1100&height=550' style='display:none' id='edit_invoice_collection_form' class='thickbox'></a>";
        echo "<a href='fullscreen.php?q=/modules/Finance/cancel_invoice_collection.php' style='display:none' id='cancel_invoice_collection' class='thickbox'></a>";
        echo "<a  href='#'  data-type='editInvoiceLink' class=' btn btn-primary btn_invoice_link_collection'>Edit Invoice</a>
        <a href='fullscreen.php?q=/modules/Finance/add_invoice_collections.php&width=1100&height=550' class='thickbox btn btn-primary addInvoiceLinkCollection' data-type='addInvoiceLink'>Add Invoice</a>
        <a  class=' btn btn-primary btn_cancel_invoice_collection' data-type='cancelInvoice'>Cancel Invoice</a>
        <a id='apply_discount_btn'  class=' apply_discount_btn btn btn-primary'>Apply discount</a>
        <a  href='fullscreen.php?q=/modules/Finance/apply_discount.php&width=900'  class='thickbox' id='apply_discount_popup' style='display:none'></a>
        <a  id='' data-type='student' class='chkinvoice btn btn-primary'>Proceed to next</a>
        </div><div class='float-none'></div></div>";
        echo "<div class ='row fee_hdr FeeInvoiceListManage feeitem' data-type='1'><div class='col-md-12'> Invoices <i class='mdi mdi-arrow-down-thick icon_1 icon_m '></i></div></div>";

        echo "<table class='table oCls_1 oClose' cellspacing='0' style='width: 100%;' id='FeeInvoiceListManage'>";
        echo "<thead>";
        echo "<tr class='head'>";
        echo '<th>';
        echo __('<input type="checkbox" name="invoiceid" id="chkAllInvoice" >');
        echo '</th>';
        echo '<th>';
        echo __('Invoice No');
        echo '</th>';
        echo '<th>';
        echo __('Title');
        echo '</th>';
        echo '<th>';
        echo __('Amount');
        echo '</th>';
        echo '<th>';
        echo __('Discount');
        echo '</th>';
        echo '<th>';
        echo __('Amount With Discount');
        echo '</th>';
        echo "<th>";
        echo __('Pending Amount');
        echo '</th>';
        echo "</thead>";
        echo "<tbody id='getInvoiceFeeItem1'>";    
        echo "</tbody>";
        echo '</table>';
        //payment history
        echo "<div class ='row fee_hdr feeitem' data-type='2'><div class='col-md-12'> Payment History <i class='mdi mdi-arrow-right-thick  icon_2 icon_m'></i></div>       
        </div>";
        echo "<div><div class='col-md-12 '><a id='cancelReceiptPaymentHistory' style='display:none; cursor:pointer;'  class='cancelReceiptPaymentHistory btn btn-primary oCls_2 oClose'>Cancel Receipt</a>
       
        <a  href='fullscreen.php?q=/modules/Finance/fee_cancel_receipts.php'  class='thickbox' id='cancelReceiptSubmit' style='display:none'>View Bill Details</a>
        </div>       
        </div>";
        
        echo "<div id='table-wrapper'><div id='table-scroll'><table cellspacing='0' style='display:none;width: 100%;  margin-top: 40px;' class='oCls_2 oClose table' id='FeeInvoiceListManage'>";
        echo "<thead>";
        echo "<tr class='head'>";  
        echo '<th>';
        echo __('<input type="checkbox" name="paymentHistory" id="chkAllPaymentHistory" id="paymentHistory" >');
        echo '</th>';     
         echo '<th>';
        echo __('View Receipt');
        echo '</th>'; 
        echo '<th>';
        echo __('Transaction Id');
        echo '</th>';
        echo '<th>';
        echo __('Receipt No');
        echo '</th>';
        echo '<th>';
        echo __('Invoice No');
        echo '</th>';
        echo '<th>';
        echo __('Amount');
        echo '</th>';
        echo '<th>';
        echo __('Fine');
        echo '</th>';
        echo '<th>';
        echo __('Discount');
        echo '</th>';
        echo '<th>';
        echo __('Total Amount Paid');
        echo '</th>';
        
        echo '<th>';
        echo __('Payment date');
        echo '</th>';
        echo '<th>';
        echo __('Payment mode');
        echo '</th>';
        echo '<th>';
        echo __('Status');
        echo '</th>';
        echo "</thead>";
        echo "<tbody id='getPaymentHistory'>";
        echo "</tbody>";
        echo '</table></div></div>';


        

        $depositDetails = $FeesGateway->getDepositAccountForStudent($criteria, $stuId);
        // echo '<pre>';
        // print_r($depositDetails);
        // echo '</pre>';
        if(!empty($depositDetails->data)){
            $depDate = $depositDetails->data;
            //Deposit Account
            echo "<div class ='row fee_hdr feeitem' data-type='5'><div class='col-md-12'> Deposit Acount <i class='mdi mdi-arrow-right-thick  icon_5 icon_m'></i></div>       
            </div>";

            echo "<div id='table-wrapper'><div id='table-scroll' style='display:none;width: 100%;  margin-top: 40px;' class='oCls_5 oClose table' id=''>";

            echo '
                <table class="table">
                    <thead>
                        <tr>
                            <th>Sl No</th>
                            <th>Fee Item</th>
                            <th>Account Name</th>
                            <th>Account Type</th>
                            <th>Current Balance</th>
                        </tr>
                    </thead>
                    <tbody>';
                    if(!empty($depDate)){
                        $k = 1;
                        foreach($depDate as $dd){
                            if ($dd['overpayment_account'] == '1') {
                                $ret = 'Over Payment Account';
                            } else {
                                $ret = '';
                            }
                            echo '<tr>
                                    <td>'.$k.'</td>
                                    <td>'.$dd['fee_item'].'</td>
                                    <td>'.$dd['ac_name'].'</td>
                                    <td>'.$ret.'</td>
                                    <td><a title="Click Here For Details" href="fullscreen.php?q=/modules/Finance/deposit_account_details.php&id='.$dd['id'].'&stid='.$dd['pupilsightPersonID'].'&width=1100" class="thickbox">'.$dd['amount'].'</a></td>
                                </tr>';
                            $k++;
                        }
                    }
                    echo '</tbody>
                </table>';
            echo "</div></div>";
            
        }


        $waiveoffDetails = $FeesGateway->getWaiveOffForStudent($criteria, $stuId);
        // echo '<pre>';
        // print_r($depositDetails);
        // echo '</pre>';
        if(!empty($waiveoffDetails->data)){
            $wavieDate = $waiveoffDetails->data;
            // echo '<pre>';
            // print_r($waiveoffDetails->data);
            //Deposit Account
            echo "<div class ='row fee_hdr feeitem' data-type='6'><div class='col-md-12'> Waive Off <i class='mdi mdi-arrow-right-thick  icon_6 icon_m'></i></div>       
            </div>";

            echo "<div id='table-wrapper'><div id='table-scroll' style='display:none;width: 100%;  margin-top: 40px;' class='oCls_6 oClose table' id='waiveoffTable'>";

            echo '
                <table class="table">
                    <thead>
                        <tr>
                            <th>Sl No</th>
                            <th>Invoice No</th>
                            <th>Discount</th>
                            <th>Assigned By</th>
                            <th>Date</th>
                            <th>Remarks</th>
                        </tr>
                    </thead>
                    <tbody>';
                    if(!empty($wavieDate)){
                        $i = 1;
                        foreach($wavieDate as $wd){
                            echo '<tr>
                                    <td>'.$i.'</td>
                                    <td>'.$wd['invoice_no'].'</td>
                                    <td>'.$wd['discount_amount'].'</td>
                                    <td>'.$wd['officialName'].'</td>
                                    <td>'.$wd['waive_off_date'].'</td>
                                    <td>'.$wd['remarks'].'</td>
                                </tr>';
                            $i++;
                        }
                    }
                    echo '</tbody>
                </table>';
            echo "</div></div>";
       
        }
    }    
}
?>
 <input type="hidden" class="p_stuId" value="<?php echo $stuId;?>">
 <input type="hidden" class="pSyd" value="<?php echo $pupilsightSchoolYearID;?>">
 </div>
<?php 
echo " <style>
.pagination .dataTable header
{
    display:none !important;
}
.fee_hdr
{
    height: 31px;
    font-size: 15px;
    font-weight: 600;
    color: #6f6767;
    margin-top: 10px;
}
.fee_footer
{
    border: 1px solid #ececec;
    background-color: #f7f7f7;
}

#collectionForm{
    display : none;
}

#FeeItemManage{
    display : none;
}

.hideFeeItemContent{
    display : none;
}
.receipt_none{
    
    pointer-events: none;
}

#table-wrapper {
    position:relative;
  }
  #table-scroll {
    width: 100%;
    overflow:auto;  
    margin-top:20px;
  }
  
  
  #table-wrapper table thead th .text {
    position:absolute;   
    top:-20px;
    z-index:2;
    height:20px;
   
    border:1px solid red;
  }
</style>";
?>
<script>
    var table = $('#expore_tbl').DataTable();
    table.destroy();

   function load(){
    var pstid=$(".p_stuId").val();
    var py=$(".pSyd").val();
    var type="getPaymentHistory";
      if(pstid!="0"){
        $.ajax({
        url: 'ajax_data.php',
        type: 'post',
        data: { val: pstid, py: py,type:type},
        async: true,
        success: function(response) {
            $("#getPaymentHistory").html(response);
            
            $(".oCls_2").show();
            $('.icon_2').removeClass('mdi mdi-arrow-right-thick');
            $('.icon_2').addClass('mdi mdi-arrow-down-thick');
        }
        });
      }
   }
   function loadInvoices(){
    var pstid=$(".p_stuId").val();
    var py=$(".pSyd").val();
    var type="loadInvoicesCollections";
      if(pstid!="0"){
        $.ajax({
            url: 'ajaxSwitch.php',
            type: 'post',
            data: { val: pstid, py: py,type:type},
            async: true,
            success: function(response) {
                $("#getInvoiceFeeItem1").html(response);
            }
        });
      }
   }
   loadInvoices();

    $(document).on('click','.cancel_receipt',function(){
        $("#preloader").show();
        var formData = $("#cancel_receipt_form").serialize();
        $.ajax({
            url: 'ajaxSwitch.php',
            type: 'post',
            data: formData,
            async: true,
            success: function(response) {
                // if(response == "success"){
                    load();
                    loadInvoices();
                    alert("Your Receipt Cancelled Successfully");
                    $("#TB_overlay").remove();
                    $("#TB_window").remove();
                    $("#preloader").hide();
                // } else {
                //     alert(response);
                // }
            }
        });
   });

    $(document).on('change', '#bank_id', function(){
            if($(this).val() != ''){
                $(this).removeClass('erroralert');
            } 
    });

    $(document).on('change', '#dd_cheque_date', function(){
            if($(this).val() != ''){
                $(this).removeClass('erroralert');
            } 
    });

    $(document).on('change', '#dd_cheque_no', function(){

        if($(this).val() != ''){
            var len = $(this).val().length;
            if(len != 6){
                alert('Enter Proper DD / Cheque No');
                $(this).addClass('erroralert');
            } else {
                $(this).removeClass('erroralert');
            }
        } 
    });

    $(document).on('change', '#dd_cheque_amount', function(){
        var ap = $("#amount_paying").val();
        var val = $(this).val();
        if(val != ''){
            if(parseFloat(val) != parseFloat(ap)){
                alert('Please Enter Same Amount as Amount Paying Field!');
                $(this).addClass('erroralert');
                $(this).val('');
                return false;
            } else {
                $(this).removeClass('erroralert');
            }
        } 
    });


    $(document).on('click','#collectionFormSubmit',function(){
            var amtoldpay = $("#amount_paying").val();
            var overamt = $("#overamount").val();
            var amtpay = 0;
            if($("#overPayment").is(':checked')) {
                amtpay = parseFloat(amtoldpay) + parseFloat(overamt);
            } else {
                amtpay = amtoldpay;
            }
                
            var formData = $("#collectionForm").serialize();
            var err=0;
            var chkValidation=0;
            var paymentMode = $("#paymentMode").val();

            if(amtpay == ''){
                alert('You cannot leave Amount Paying field blank!');
                err++;
            }

            if(amtpay <= 1){
                alert('Amount paying to be greater than 0 !');
                err++;
            } 

            var tamtold = $("#transcation_amount_old").val();
            var chkval = $("input[name=chkamount]").val();
            if (amtpay != '') {
                if (Number(amtpay) < Number(chkval)) {
                    if (confirm("Do you want to do partial payment")) {
                        $("#amount_paying").val(amtpay);
                        $("input[name='invoice_status'").val("Partial Paid");
                        //return true;
                    } else {
                        $("input[name='invoice_status'").val("Fully Paid");
                        $("#amount_paying").val("");
                        return false;
                    }
                } 
                
                if (Number(amtpay) > Number(chkval)) {
                    if (confirm("Do you want to do Over payment")) {
                        $("#amount_paying").val(amtpay);
                        $("input[name='invoice_status'").val("Fully Paid");
                        //return true;
                    } else {
                        $("input[name='invoice_status'").val("Fully Paid");
                        $("#amount_paying").val("");
                        return false;
                    }
                }

                // if (Number(amtpay) < Number(tamtold)) {
                //     $("input[name='invoice_status'").val("Partial Paid");
                // }
            }

        


            if(paymentMode==""){
                err++;
                $("#paymentMode").addClass('LV_invalid_field');
                alert("Please Select Payment Mode");
            } else {
                var val = $("#paymentMode option:selected").text();
                val = val.toUpperCase();
                if (val == 'CHEQUE' || val == 'DD') {
                    if($("#bank_id").val() == ''){
                        $("#bank_id").addClass('erroralert');
                        //alert('Please Select Bank Name!');
                        err++;
                        chkValidation++;
                    } else {
                        $("#bank_id").removeClass('erroralert');
                    }
                    if($("#dd_cheque_date").val() == ''){
                        $("#dd_cheque_date").addClass('erroralert');
                        //alert('Please Insert DD/Cheque Date!');
                        err++;
                        chkValidation++;
                    } else {
                        $("#dd_cheque_date").removeClass('erroralert');
                    }
                    if($("#dd_cheque_no").val() == ''){
                        $("#dd_cheque_no").addClass('erroralert');
                        //alert('Please Insert DD/Cheque No!');
                        err++;
                        chkValidation++;
                    } else {
                        $("#dd_cheque_no").removeClass('erroralert');
                    }

                    if($("#dd_cheque_amount").val() == ''){
                        $("#dd_cheque_amount").addClass('erroralert');
                        //alert('Please Insert DD/Cheque Amount!');
                        err++;
                        chkValidation++;
                    } else {
                        $("#dd_cheque_amount").removeClass('erroralert');
                    }
                } else if (val == 'DEPOSIT') {
                    var cauDepId = $("#deposit_account_id").val();
                    if(cauDepId==""){
                        err++;
                        alert("Please Select Deposit Acount");
                    }
                }
                $("#paymentMode").removeClass('LV_invalid_field');
            }
            if(chkValidation != 0){
                alert('Please Enter All Mandatory Fields!');
                return false;
            }
            //alert(err);
            if(err==0){
                
                $("#preloader").show();
                setTimeout(function(){
                    $.ajax({
                        url: 'ajaxSwitch.php',
                        type: 'post',
                        data: formData,
                        async: true,
                        success: function(response) {
                            $("#preloader").hide();
                            
                            $('#getRecerptPop').trigger('click');
                            $("#closePayment").trigger('click');
                            loadInvoices();
                            load();
                            // window.setTimeout(function () {
                            //     location.reload();
                            // }, 10000);
                            
                        }
                    });
                }, 2000);
            }
    });
    
    $(document).on('click', '.feeitem', function() {
        var id = $(this).attr('data-type');
        // icon_0 icon_m
        // $('.icon_m').removeClass('fa-arrow-down');
        // $('.icon_m').addClass('fa-arrow-right');

        if ($(".oCls_" + id).is(":visible")) {
            $('.icon_' + id).removeClass('mdi mdi-arrow-down-thick');
            $('.icon_' + id).addClass('mdi mdi-arrow-right-thick');
            // $('.oClose').hide();
            $(".oCls_" + id).hide();


        } else {
            if(id=="2"){
            load();
            }

            if(id=="1"){
                loadInvoices();
            } 
            
            $(".oCls_" + id).show();
            $('.icon_' + id).removeClass('mdi mdi-arrow-right-thick');
            $('.icon_' + id).addClass('mdi mdi-arrow-down-thick');
        
        }
    });
    
    $(document).on('click','#save_sp_discount',function(){
        var type = $(this).attr('data-type');
        var a_stuid = $("input[name=a_stuid]").val();
        if(type=="invoice_level_dataStore"){
            var favorite = [];
            var dicout_val=[];
            var assgnBy = [];
            var assgnDate=[];
            var assnRem = [];
            var invno = [];
            var feeItemId = $("#fn_fee_item_id").val();
            if(feeItemId){
                $.each($(".chkinvoice_discount:checked"), function() {
                    var id=$(this).val();
                    var val =$('.inid_'+id).val();
                    var assn =$('.assn_'+id).val();
                    var dte =$('.dte_'+id).val();
                    var rem =$('.rem_'+id).val();
                    var inv = $('.invno_'+id).val();
                    favorite.push(id);
                    dicout_val.push(val);
                    assgnBy.push(assn);
                    assgnDate.push(dte);
                    assnRem.push(rem);
                    invno.push(inv);
                });
                if(favorite.length!=0){
                    // $.ajax({
                    //     url: 'ajaxSwitch.php',
                    //     type: 'post',
                    //     data: { type:type,discountVal:dicout_val,invids:favorite,stuid:a_stuid, feeItemId:feeItemId},
                    //     async: true,
                    //     success: function(response) {
                    //         alert("Discount is applied");
                    //         $("#TB_closeWindowButton").click();
                    //         loadInvoices();
                    //     }
                    // });
                    $.ajax({
                        url: 'modules/Finance/invoice_discount.php',
                        type: 'post',
                        data: { discountVal:dicout_val,invids:favorite,stuid:a_stuid, feeItemId:feeItemId, assgnBy:assgnBy, assgnDate:assgnDate, assnRem:assnRem, invno:invno},
                        async: true,
                        success: function(response) {
                            alert("Discount is applied");
                            $("#TB_closeWindowButton").click();
                            loadInvoices();
                        }
                    });
                } else {
                    alert('Atleast give one invoice discount');
                }
            } else {
                alert('Please Select Fee Item!');
            }
        } else {
            var items = [];
            var dicout_val=[];
            $.each($(".a_selFeeItem:checked"), function() {
            var id=$(this).val();
            var val =$('.itid_'+id).val();
            items.push(id);
            dicout_val.push(val);
            });
            if(items.length!=0){
            $.ajax({
                url: 'ajaxSwitch.php',
                type: 'post',
                data: { type:type,discountVal:dicout_val,items:items,stuid:a_stuid},
                async: true,
                success: function(response) {
                    alert("Discount is applied");
                    $("#TB_closeWindowButton").click();
                    loadInvoices();
                }
            });
            } else {
            alert('atleast give one item discount');
            }
        }
    });
    
    $(document).on('click','#addInvoiceStnButton',function(){
        var url = $('#add_invoice_collection_process_form'). attr('action');
        var py=$(".pSyd").val();
        var pstid=$(".p_stuId").val();
        var formData = "pstid="+pstid+"&"+"yid="+py+"&"+$("#add_invoice_collection_process_form").serialize();
        var err=0;
        var title= $("#title").val();
        if(title.trim()!=""){
            $("#title").removeClass('LV_invalid_field');
            $("#title").removeClass('erroralert');
        } else {
            err++;
        $("#title").addClass('LV_invalid_field');
        $("#title").addClass('erroralert');
        }

        if($("#feeStructureItemDisableId").val() == ''){
            err++;
            $("#feeStructureItemDisableId").addClass('erroralert');
        } else {
            $("#feeStructureItemDisableId").removeClass('erroralert');
        }

        if($("#invamt").val() == ''){
            err++;
            $("#invamt").addClass('erroralert');
        } else {
            $("#invamt").removeClass('erroralert');
        }

        if($("#fnFeesHeadId").val() == ''){
            err++;
            $("#fnFeesHeadId").addClass('erroralert');
        } else {
            $("#fnFeesHeadId").removeClass('erroralert');
        }

        if($("#inv_fn_fee_series_id").val() == ''){
            err++;
            $("#inv_fn_fee_series_id").addClass('erroralert');
        } else {
            $("#inv_fn_fee_series_id").removeClass('erroralert');
        }

        if($("#rec_fn_fee_series_id").val() == ''){
            err++;
            $("#rec_fn_fee_series_id").addClass('erroralert');
        } else {
            $("#rec_fn_fee_series_id").removeClass('erroralert');
        }

        
        if(err==0){
            var val = $("#fn_fees_fine_rule_id").val();
            if(val != ''){
                var ddate = $("#due_date").val();
                if(ddate == ''){
                    $("#due_date").addClass('erroralert');
                    alert('You have to Add Due Date');
                    return false;
                } else {
                    $("#due_date").removeClass('erroralert');
                    $.ajax({
                        url: url,
                        type: 'post',
                        data: formData,
                        async: true,
                        success: function(response) {
                        if(response=="success"){
                            alert("Invoice added successfully");
                            $("#TB_closeWindowButton").click();
                            loadInvoices();
                        } else {
                            alert(response);
                        }
                        }
                    });
                }
            } else {
                $.ajax({
                    url: url,
                    type: 'post',
                    data: formData,
                    async: true,
                    success: function(response) {
                    if(response=="success"){
                        alert("Invoice added successfully");
                        $("#TB_closeWindowButton").click();
                        loadInvoices();
                    } else {
                        alert(response);
                    }
                    }
                });
            }
            
        }
    });
    
    $(document).on('click','#updateInvoiceStnButton',function(){
        var url = $('#edit_invoice_save_form'). attr('action');
        var py=$(".pSyd").val();
        var pstid=$(".p_stuId").val();
        var formData = "pstid="+pstid+"&"+"yid="+py+"&"+$("#edit_invoice_save_form").serialize();
        var err=0;
        var title= $("#title").val();
        if(title.trim()!=""){
            $("#title").removeClass('LV_invalid_field');
        } else {
            err++;
        $("#title").addClass('LV_invalid_field');
        }

        
        if($("#fnFeesHeadId").val() == ''){
            err++;
            $("#fnFeesHeadId").addClass('erroralert');
        } else {
            $("#fnFeesHeadId").removeClass('erroralert');
        }

        if($("#inv_fn_fee_series_id").val() == ''){
            err++;
            $("#inv_fn_fee_series_id").addClass('erroralert');
        } else {
            $("#inv_fn_fee_series_id").removeClass('erroralert');
        }

        if($("#rec_fn_fee_series_id").val() == ''){
            err++;
            $("#rec_fn_fee_series_id").addClass('erroralert');
        } else {
            $("#rec_fn_fee_series_id").removeClass('erroralert');
        }
        
        if(err==0){
            var val = $("#fn_fees_fine_rule_id").val();
            if(val != ''){
                var ddate = $("#due_date").val();
                if(ddate == ''){
                    $("#due_date").addClass('erroralert');
                    alert('You have to Add Due Date');
                    return false;
                } else {
                    $("#due_date").removeClass('erroralert');
                    $.ajax({
                        url: url,
                        type: 'post',
                        data: formData,
                        async: true,
                        success: function(response) {
                        if(response=="success"){
                            alert("Invoice updated successfully");
                            $("#TB_closeWindowButton").click();
                            loadInvoices();
                        } else {
                            alert(response);
                        }
                        }
                    });
                }
            } else {
                $.ajax({
                    url: url,
                    type: 'post',
                    data: formData,
                    async: true,
                    success: function(response) {
                    if(response=="success"){
                        alert("Invoice updated successfully");
                        $("#TB_closeWindowButton").click();
                        loadInvoices();
                    } else {
                        alert(response);
                    }
                    }
                });
            }
            
        }
    });
    
    $(document).on('click','#cancel_invoice',function(){
        var url = $('#delect_invoice_collection_form'). attr('action');
        var py=$(".pSyd").val();
        var pstid=$(".p_stuId").val();
        var formData = "pstid="+pstid+"&"+"yid="+py+"&"+$("#delect_invoice_collection_form").serialize();
        var err=0;
        var reason_for_cancel= $("#reason_for_cancel").val();
        if(reason_for_cancel.trim()!=""){
            $("#reason_for_cancel").removeClass('LV_invalid_field');
        } else {
            err++;
        $("#reason_for_cancel").addClass('LV_invalid_field');
        }
        if(err==0){
            $.ajax({
                url: url,
                type: 'post',
                data: formData,
                async: true,
                success: function(response) {
                if(response=="success"){
                    alert("Invoice cancelled successfully");
                    $("#TB_closeWindowButton").click();
                    loadInvoices();
                } else {
                    alert(response);
                }
                }
            });
        }
    });

    $(document).on('keydown', '#search', function (e) {
        if (e.keyCode == 13) {
            $("#searchInvoice")[0].click();
            return false;
        }
    });

    
    
    $(document).on('click', '#closePaymentScreen', function (e) {
        //$("#showPaymentScreen").hide();
        $("#collectionForm").hide();
        $(".hideFeeItemContent").hide();
        loadInvoices();
        $(".oCls_1").show();
        $('.icon_1').removeClass('mdi mdi-arrow-right-thick');
        $('.icon_1').addClass('mdi mdi-arrow-down-thick');
        //var type = $("#searchCollectionType").val();
        // alert(type);
        // if(type == 1){
            //$("#searchStudent").click();
        // } else {
        //     $("#searchInvoice").click();
        // }
    })

    $(document).on('change', '#overPayment', function(){
        var ap = $("#amount_paying").val();
        var oldap = $("#amount_paying_old").val();
        
        if($(this).is(':checked')) {
            var val = $('#overamount').val();
            $("#overamount").attr("readonly", false); 
            if(val != ''){
                var newap = parseFloat(ap) - parseFloat(val);
                $("#amount_paying").val(newap);
            } 
        } else {
            $("#amount_paying").val(oldap);
            $("#overamount").attr("readonly", true); 
        }
    });

    $(document).on('keyup', '#overamount', function(){
        var ap = $("#amount_paying").val();
        var oldap = $("#amount_paying_old").val();
        var val = $(this).val();
        if(val != ''){
            var newap = parseFloat(oldap) - parseFloat(val);
            $("#amount_paying").val(newap);
        } 
    });

    
    $(document).on('change', '#deposit_account_id', function(){
        var ap = $("#amount_paying").val();
        var oldap = $("#amount_paying_old").val();
        var val = $(this).val();
        var type = 'chkCautionAmt';
        $.ajax({
            url: 'ajax_data.php',
            type: 'post',
            data: { type:type,ap:ap,val:val},
            async: true,
            success: function(response) {
                if(response == 'fail'){
                    alert('Amount Balance is Low, You cannot make Payment for Selected Amount!');
                    $("#paymentMode").val('').change();
                }
            }
        });
    });
</script>