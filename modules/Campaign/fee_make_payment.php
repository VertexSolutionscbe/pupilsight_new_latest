<?php
/*
Pupilsight, Flexible & Open School System
*/
use Pupilsight\Forms\Form;
use Pupilsight\Tables\DataTable;
use Pupilsight\Services\Format;
use Pupilsight\Domain\Finance\FeesGateway;
use Pupilsight\Forms\DatabaseFormFactory;

if (isActionAccessible($guid, $connection2, '/modules/Campaign/fee_make_payment.php') == false) {
    //Acess denied
    echo "<div class='error'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    //Proceed!
   // $page->breadcrumbs->add(__('Manage Fee Structure'));
  
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
    
    

    $FeesGateway = $container->get(FeesGateway::class);
    $criteria = $FeesGateway->newQueryCriteria()
        ->sortBy(['id'])
        ->fromPOST();

    //$yearGroups = $FeesGateway->getFeeStructure($criteria, $pupilsightSchoolYearID);
    //print_r($yearGroups);
    $table = DataTable::createPaginated('FeeStructureManage', $criteria);

    $cid = $_GET['cid'];
    $sid = $_GET['sid'];
    $stuId = $sid;

    // $sqlp = 'SELECT b.fn_fee_admission_setting_ids FROM campaign_form_status AS a LEFT JOIN workflow_transition AS b ON a.state_id = b.id ';
    // $resultp = $connection2->query($sqlp);
    // $rowdataprog = $resultp->fetchAll();
    

        $invoices = 'SELECT fn_fee_invoice.*,fn_fee_invoice.id as invoiceid,g.is_fine_editable, g.fine_type, g.rule_type, fn_fee_invoice_applicant_assign.id as invid, fn_fee_invoice_applicant_assign.invoice_no as stu_invoice_no FROM fn_fee_invoice LEFT JOIN fn_fees_fine_rule AS g ON fn_fee_invoice.fn_fees_fine_rule_id = g.id LEFT JOIN fn_fee_invoice_applicant_assign ON fn_fee_invoice.id = fn_fee_invoice_applicant_assign.fn_fee_invoice_id  WHERE fn_fee_invoice.pupilsightSchoolYearID = "'.$pupilsightSchoolYearID.'" AND fn_fee_invoice_applicant_assign.submission_id = "'.$sid.'"';
        $resultinv = $connection2->query($invoices);
        $invdata = $resultinv->fetchAll();
        // echo '<pre>';
        // print_r($invdata);
        // echo '</pre>';
        // die();
        $totalamount = 0;
        foreach($invdata as $k => $d){
            $sqlamt = 'SELECT SUM(fn_fee_invoice_item.total_amount) as totalamount FROM fn_fee_invoice_item WHERE fn_fee_invoice_id = '.$d['invoiceid'].' '; 
            $resultamt = $connection2->query($sqlamt);
            $dataamt = $resultamt->fetch();

            $totalamount = $dataamt['totalamount'];
            $invdata[$k]['finalamount'] = $totalamount;
           
            $date = date('Y-m-d');
            $curdate = strtotime($date);
            $duedate = strtotime($d['due_date']);
            $fineId = $d['fn_fees_fine_rule_id'];

            if(!empty($fineId) && $curdate > $duedate){
                $finetype = $d['fine_type'];
                $ruletype = $d['rule_type'];
                if($finetype == '1' && $ruletype == '1'){
                    $sqlf = 'SELECT * FROM fn_fees_rule_type WHERE fn_fees_fine_rule_id	= "'.$fineId.'" AND fine_type = "'.$finetype.'" AND rule_type = "'.$ruletype.'" ';
                    $resultf = $connection2->query($sqlf);
                    $finedata = $resultf->fetch();
                    $amtper = $finedata['amount_in_percent'];
                    $type = 'percent';
                } elseif($finetype == '1' && $ruletype == '2'){
                    $sqlf = 'SELECT * FROM fn_fees_rule_type WHERE fn_fees_fine_rule_id	= "'.$fineId.'" AND fine_type = "'.$finetype.'" AND rule_type = "'.$ruletype.'" ';
                    $resultf = $connection2->query($sqlf);
                    $finedata = $resultf->fetch();
                    $amtper = $finedata['amount_in_number'];
                    $type = 'num';
                } elseif($finetype == '1' && $ruletype == '3'){
                    $sqlf = 'SELECT * FROM fn_fees_rule_type WHERE fn_fees_fine_rule_id	= "'.$fineId.'" AND fine_type = "'.$finetype.'" AND rule_type = "'.$ruletype.'" AND from_date <= "'.$date.'" AND to_date >= "'.$date.'" ';
                    $resultf = $connection2->query($sqlf);
                    $finedata = $resultf->fetch();
                    if(!empty($finedata)){
                        if($finedata['amount_type'] == 'Fixed'){
                            $amtper = $finedata['amount_in_number'];
                            $type = 'num';
                        } else {
                            $amtper = $finedata['amount_in_percent'];
                            $type = 'percent';
                        }
                        
                    } else {
                        $amtper = '';
                        $type = '';
                    }
                } elseif($finetype == '2' && $ruletype == '1'){
                    $sqlf = 'SELECT * FROM fn_fees_rule_type WHERE fn_fees_fine_rule_id	= "'.$fineId.'" AND fine_type = "'.$finetype.'" AND rule_type = "'.$ruletype.'" ';
                    $resultf = $connection2->query($sqlf);
                    $finedata = $resultf->fetch();
                    $amtper = $finedata['amount_in_percent'];
                    $type = 'percent';
                } elseif($finetype == '2' && $ruletype == '2'){
                    $sqlf = 'SELECT * FROM fn_fees_rule_type WHERE fn_fees_fine_rule_id	= "'.$fineId.'" AND fine_type = "'.$finetype.'" AND rule_type = "'.$ruletype.'" ';
                    $resultf = $connection2->query($sqlf);
                    $finedata = $resultf->fetch();
                    $amtper = $finedata['amount_in_number'];
                    $type = 'num';
                } elseif($finetype == '3' && $ruletype == '1'){
                    $sqlf = 'SELECT * FROM fn_fees_rule_type WHERE fn_fees_fine_rule_id	= "'.$fineId.'" AND fine_type = "'.$finetype.'" AND rule_type = "'.$ruletype.'" ';
                    $resultf = $connection2->query($sqlf);
                    $finedata = $resultf->fetch();
                    $amtper = $finedata['amount_in_percent'];
                    $type = 'percent';
                } elseif($finetype == '3' && $ruletype == '2'){
                    $sqlf = 'SELECT * FROM fn_fees_rule_type WHERE fn_fees_fine_rule_id	= "'.$fineId.'" AND fine_type = "'.$finetype.'" AND rule_type = "'.$ruletype.'" ';
                    $resultf = $connection2->query($sqlf);
                    $finedata = $resultf->fetch();
                    $amtper = $finedata['amount_in_number'];
                    $type = 'num';
                } elseif($finetype == '3' && $ruletype == '4'){
                    $date1 = strtotime($d['due_date']);  
                    $date2 = strtotime($date); 
                    $diff = abs($date2 - $date1);
                    $years = floor($diff / (365*60*60*24));  
                    $months = floor(($diff - $years * 365*60*60*24) / (30*60*60*24));   
                    $days = floor(($diff - $years * 365*60*60*24 - $months*30*60*60*24)/ (60*60*24));

                    $sqlf = 'SELECT * FROM fn_fees_rule_type WHERE fn_fees_fine_rule_id	= "'.$fineId.'" AND fine_type = "'.$finetype.'" AND rule_type = "'.$ruletype.'" AND from_day <= "'.$days.'" AND to_day >= "'.$days.'" ';
                    $resultf = $connection2->query($sqlf);
                    $finedata = $resultf->fetch();
                    if($finedata['amount_type'] == 'Fixed'){
                        $amtper = $finedata['amount_in_number'];
                        $type = 'num';
                    } else {
                        $amtper = $finedata['amount_in_percent'];
                        $type = 'percent';
                    }
                   
                } else {
                    $amtper = '';
                    $type = '';
                }
            } else {
                $amtper = '';
                $type = '';
            }
            $invdata[$k]['amtper'] = $amtper;
            $invdata[$k]['type'] = $type;

           
            $invid =  $d['invoiceid'];
            $invno =  $d['stu_invoice_no'];
            $sqla = 'SELECT GROUP_CONCAT(a.fn_fee_invoice_item_id) AS invitemid, a.transaction_id, b.filename FROM fn_fees_applicant_collection AS a LEFT JOIN fn_fees_collection AS b ON  a.transaction_id = b.transaction_id WHERE a.invoice_no = "'.$invno.'" AND b.transaction_status = "1" ';
            $resulta = $connection2->query($sqla);
            $inv = $resulta->fetch();
            
            if(!empty($inv['invitemid'])){
                //echo $inv['invitemid'];
                if(!empty($d['transport_schedule_id'])){
                    $invdata[$k]['paidamount'] = $totalamount;
                    $pendingamount = 0;
                    $invdata[$k]['pendingamount'] = $pendingamount;
                    $invdata[$k]['chkpayment'] = 'Paid';
                } else {    
                    $itemids = $inv['invitemid'];
                    $sqlp = 'SELECT SUM(total_amount) as paidtotalamount FROM fn_fee_invoice_item WHERE id IN ('.$itemids.') ';
                    $resultp = $connection2->query($sqlp);
                    $amt = $resultp->fetch();
                    $totalpaidamt = $amt['paidtotalamount'];
                    if(!empty($totalpaidamt)){
                        $invdata[$k]['paidamount'] = $totalpaidamt;
                        $pendingamount = $totalamount- $totalpaidamt;
                        $invdata[$k]['pendingamount'] = $pendingamount;
                        if($pendingamount == ''){
                            $invdata[$k]['chkpayment'] = 'Paid';
                        } else {
                            $invdata[$k]['chkpayment'] = 'Half Paid';
                        }
                        
                    } 
                }
                $invdata[$k]['transaction_id'] = $inv['transaction_id'];
                $invdata[$k]['filename'] = $inv['filename'];
            } else {
                $invdata[$k]['paidamount'] = '0';
                $pendingamount = $totalamount;
                $invdata[$k]['pendingamount'] = $pendingamount;
                $invdata[$k]['chkpayment'] = 'UnPaid';
                $invdata[$k]['transaction_id'] = '';
                $invdata[$k]['filename'] = '';
            }
        }

        // echo '<pre>';
        // print_r($invdata);
        // echo '</pre>';
        // die();
        echo "<div class ='row fee_hdr FeeInvoiceListManage'><div class='col-md-12'> Invoices <span style='float:right;margin: -8px 0px 0px 0px;'><button class='btn btn-primary' id='editInvoice'>Edit Invoice</button>&nbsp;&nbsp;<button class='btn btn-primary' id='makeAdmissionPayment'>Make Payment</button><span></div></div>";

        echo "<table class='table' id='FeeInvoiceListManage'>";
        echo "<thead>";
        echo "<tr class='head'>";
        echo '<th>';
        // echo __('<input type="checkbox" name="invoiceid" id="chkAllInvoiceApplicant" >');
        echo __('Select');
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
        echo "<th>";
        echo __('Pending Amount');
        echo '</th>';
        echo "<th>";
        echo __('Receipt');
        echo '</th>';
        echo "</thead>";
        
        echo "<tbody id=''>";
        if(!empty($invdata)){
            foreach($invdata as $ind){
                $totAmt = number_format($ind['finalamount'], 2, '.', '');
                if (!empty($ind['filename'])) {
                    $receipt = '<a href="public/receipts/'.$ind['filename'].'.pdf"  download><i class="mdi mdi-receipt mdi-24px"></i></a>';
                } else if (!empty($ind['transaction_id'])) {
                    $receipt =  '<a href="public/receipts/'.$ind['transaction_id'].'.pdf"  download><i class="mdi mdi-receipt mdi-24px"></i></a>';
                }
                if($ind['chkpayment'] == 'Paid'){
                    //$cls = 'value="0" checked disabled';
                    echo '<tr><td><input type="checkbox" class=" invoice'.$ind['id'].'" name="invoiceid[]" data-h="'.$ind['fn_fees_head_id'].'" data-se="'.$ind['rec_fn_fee_series_id'].'" id="allfeeItemid" data-stu="'.$stuId.'" data-fper="'.$ind['amtper'].'" data-ftype="'.$ind['type'].'" data-inv="'.$ind['invid'].'" data-ife="'.$ind['is_fine_editable'].'" value="0" checked disabled ></td><td>'.$ind['stu_invoice_no'].'</td><td>'.$ind['title'].'</td><td>'.$totAmt.'</td><td>'.$ind['pendingamount'].'</td><td>'.$receipt.'</td></tr>';
                } else {
                    $cls = 'value="'.$ind['invoiceid'].'"'; 
                     echo '<tr><td><input type="checkbox" class="chkinvoiceApplicant invoice'.$ind['id'].'" name="invoiceid[]" data-h="'.$ind['fn_fees_head_id'].'" data-se="'.$ind['rec_fn_fee_series_id'].'" id="allfeeItemid" data-stu="'.$stuId.'" data-fper="'.$ind['amtper'].'" data-ftype="'.$ind['type'].'"  '.$cls.'  data-amtedt="'.$ind['amount_editable'].'" data-inv="'.$ind['invid'].'" data-ife="'.$ind['is_fine_editable'].'"></td><td>'.$ind['stu_invoice_no'].'</td><td>'.$ind['title'].'</td><td>'.$ind['finalamount'].'</td><td>'.$ind['pendingamount'].'</td><td></td></tr>';
                    
                }
            }
        }
        
        echo "</tbody>";
        echo '</table>';
    
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

        $sqlstu = 'SELECT field_value FROM wp_fluentform_entry_details WHERE submission_id = "'.$sid.'" AND sub_field_name = "first_name" ';
        $resultstu = $connection2->query($sqlstu);
        $studetails = $resultstu->fetch();
        
        
        $form = Form::create('collectionForm', $_SESSION[$guid]['absoluteURL'].'/modules/'.$_SESSION[$guid]['module'].'/fee_make_payment_addProcess.php');
        $form->setFactory(DatabaseFormFactory::create($pdo));
    
        $form->addHiddenValue('address', $_SESSION[$guid]['address']);
        $form->addHiddenValue('submission_id', $sid);
        $form->addHiddenValue('cid', $cid);
        $form->addHiddenValue('pupilsightSchoolYearID', $pupilsightSchoolYearID);
        $form->addHiddenValue('invoice_id', '0');
        $form->addHiddenValue('invoice_item_id', '0');
        $form->addHiddenValue('chkamount', '0');
    
        $row = $form->addRow();
        $col = $row->addColumn()->setClass('float-left submit_ht');
        //#ebebeb
            
        //$col->addSubmit(__('Make Payment With Discount Fee')); 
       // $col->addLabel('', __(' '));
        
        $col->addLabel('officialName', __('&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Student Name : '.$studetails['field_value'].' &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'));
        // $col->addLabel('pupilsightPersonID', __('&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Student Id : "Student Id" &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'));
        $col->addContent('<div style="position:absolute; right:0;"><i style="cursor:pointer; font-size:30px; margin: 0 0 0 700px" id="closePayment" class="fa fa-times" aria-hidden="true"></i></div>'); 
        
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
        $col->addSelect('payment_mode_id')->setId('paymentMode')->fromArray($paymentmode)->addClass(' txtfield')->required();
        
        $col = $row->addColumn()->setClass('newdes hiddencol ddChequeRow');
        $col->addLabel('bank_id', __('Bank Name'));
        $col->addSelect('bank_id')->fromArray($bank)->addClass(' txtfield');
    
        $col = $row->addColumn()->setClass('hiddencol');
        $col->addLabel('', __(''));
        $col->addTextField('');    
        
        $row = $form->addRow();
                
    
    $date = isset($_GET['date']) ? Format::dateConvert($_GET['date']) : date('Y-m-d');
            
        $row = $form->addRow()->setClass('hiddencol ddChequeRow');
            
            $col = $row->addColumn()->setClass('newdes ');
            $col->addLabel('dd_cheque_date', __('DD / Cheque Date'))->addClass('dte');
            $col->addDate('dd_cheque_date')->setValue(Format::date($date));
    
            $col = $row->addColumn()->setClass('newdes ');
            $col->addLabel('dd_cheque_no', __('DD / Cheque No'))->addClass('dte');
            $col->addTextField('dd_cheque_no')->addClass('txtfield');
    
            $col = $row->addColumn()->setClass('newdes ');
            $col->addLabel('dd_cheque_amount', __('DD / Cheque Amount'))->addClass('dte');
            $col->addTextField('dd_cheque_amount')->addClass('txtfield   numfield');
    
            $col = $row->addColumn()->setClass('newdes ');
            $col->addLabel('payment_status', __('Payment Status'))->addClass('dte');
            $col->addTextField('payment_status')->setValue('Payment Received')->readonly()->addClass('txtfield');
    
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
            
        $col = $row->addColumn()->setClass('newdes ');
            $col->addLabel('receipt_number', __('Manual Reciept No'))->addClass('dte');
            $col->addTextField('receipt_number')->addClass('txtfield');
        
        $col = $row->addColumn()->setClass('newdes');

        $col->addLabel('Manual Receipt No', __('Manual Receipt No'))->addClass('dte');
        $col->addCheckbox('is_custom', FALSE)->description('No Auto Generate')->addClass('dte')->setValue('1')->inline(TRUE);    
        //$col->addLabel('is_custom', __('Manual Receipt No (No Auto Generate)'));
            
   
    

    $row = $form->addRow();
        $col = $row->addColumn()->setClass('newdes ');
            $col->addLabel('transcation_amount', __('Transaction Amount'))->addClass('dte');
            $col->addTextField('transcation_amount')->addClass('txtfield   numfield')->required()->readonly();    
            
        $col = $row->addColumn()->setClass('newdes ');
            $col->addLabel('fine', __('Fine'))->addClass('dte');
            $col->addTextField('fine')->addClass('txtfield numfield')->readonly();
            
        $col = $row->addColumn()->setClass('newdes ');
            $col->addLabel('discount', __('Discount'))->addClass('dte');
            $col->addTextField('discount')->addClass('txtfield numfield')->readonly();

        $col = $row->addColumn()->setClass('newdes ');
            $col->addLabel('amount_paying', __(' Amount Paying'))->addClass('dte');
            $col->addTextField('amount_paying')->addClass('txtfield   numfield')->setValue('')->required();

        $col = $row->addColumn()->setClass('newdes hiddencol');
            $col->addLabel('total_amount_without_fine_discount', __(' Amount Paying'))->addClass('dte');
            $col->addTextField('total_amount_without_fine_discount')->addClass('txtfield   numfield')->setValue('');    
    
        // $col = $row->addColumn()->setClass('newdes ');
        //     $col->addLabel('', __(''));
        //     $col->addCheckbox('is_pay')->description(__('Do you want to do payment for selected fee items)'))->addClass(' dte');
    $row = $form->addRow();        
        $col = $row->addColumn()->setClass('newdes ');
            $col->addLabel('remarks', __('Remarks'))->addClass('dte');
            $col->addTextArea('remarks')->addClass('txtfield')->setRows(1);    
           
            $row = $form->addRow();
            $col = $row->addColumn();
            $col->addLabel('', __(''));
            $col->addSubmit(__('Make Payment'));  
    
           
    $row = $form->addRow();
            $col = $row->addColumn()->setClass('hiddencol');
            $col->addLabel('', __(''));
            $col->addTextField('');   
        
        echo $form->getOutput();

            echo "<div style='display:none;'><div class ='row fee_hdr hideFeeItemContent'><div class='col-md-12'> Fee Items</div></div>";

            echo "<table cellspacing='0' style='width: 100%' id='FeeItemManage'>";
            echo "<thead>";
            echo "<tr class='head'>";
            echo '<th>';
            echo __('<input type="checkbox" name="" id="chkAllFeeItem" >');
            echo '</th>';
            echo '<th>';
            echo __('Fee Item');
            echo '</th>';
            echo '<th>';
            echo __('Invoice No');
            echo '</th>';
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
            echo '</table></div>';
}
?>

<div id="invoiceEdit">
    
</div>

<style>
.pagination ,.dataTable header
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
</style>


<script>
    
    

    $(document).on('click', '#makeAdmissionPayment', function () {
        $("#collectionForm")[0].reset();
        $(".ddChequeRow").addClass('hiddencol');
        var favorite = [];
        var account_heads = [];
        var series = [];
        var aedt = [];
        var ife = [];
        $.each($(".chkinvoiceApplicant:checked"), function () {
            favorite.push($(this).val());
            account_heads.push($(this).attr("data-h"));
            series.push($(this).attr("data-se"));
            aedt.push($(this).attr("data-amtedt"));
            ife.push($(this).attr("data-ife"));
        });
        var newData = removeDuplicates(account_heads);
        var length1 = newData.length;
        var chkStatus = false;
        if (favorite.length != 0) {
            var sid = $("input[name=submission_id]").val();
            if (length1 == "1") {
                chkStatus = true;
            } else {
                var r = confirm("Selected invoice receipt series are different.\n Do you want to make payment?");
                if (r == true) {
                    chkStatus = true;
                } else {
                    chkStatus = false;
                }
            }

            //ajax request
            if (chkStatus == true) {
                var invids = favorite.join(", ");
                var type = 'applicantInvoiceFeeItem';
                $.ajax({
                    url: 'ajax_data.php',
                    type: 'post',
                    data: { val: invids, type: type, sid: sid },
                    async: true,
                    success: function (response) {
                        $(".btn_invoice_link_collection").hide();
                        $(".addInvoiceLinkCollection").hide();
                        $(".btn_cancel_invoice_collection").hide();
                        $(".chkinvoice").hide();
                        $(".apply_discount_btn").hide();
                        $("#getInvoiceFeeItem").html('');
                        $("#getInvoiceFeeItem").append(response);
                        $("input[name=invoice_id]").val(invids);
                        $("#collectionForm").show();
                        $("#FeeItemManage").show();
                        $(".hideFeeItemContent").show();
                        $('#fn_fees_head_id').val(account_heads[0]);
                        $('#recptSerId').val(series[0]);
                        $(".oCls_0").hide();
                        $('.icon_0').removeClass('fa-arrow-down');
                        $('.icon_0').addClass('fa-arrow-right');
                        $(".oCls_1").hide();
                        $('.icon_1').removeClass('fa-arrow-down');
                        $('.icon_1').addClass('fa-arrow-right');

                        if (aedt[0] == '1') {
                            $("#amount_paying").attr("readonly", false);
                        } else {
                            $("#amount_paying").attr("readonly", true);
                        }

                        if (ife[0] == '1') {
                            $("#fine").attr("readonly", false);
                        } else {
                            $("#fine").attr("readonly", true);
                        }

                        setTimeout(function () {
                            $("#chkAllFeeItem").prop("checked", true).trigger("change");
                        }, 1000);

                    }
                });
            }
            //ends request
        } else {
            alert('Please select atleast one invoice');
            $("#chkAllInvoice").prop('checked', false);
            $(".invrow" + invid).remove();
            addInvoiceFeeAmt();
            $("input[name=invoice_id]").val(invids);
        }
    });
</script>