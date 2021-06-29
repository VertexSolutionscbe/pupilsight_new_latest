<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Forms\Form;
use Pupilsight\Tables\DataTable;
use Pupilsight\Services\Format;
use Pupilsight\Forms\DatabaseFormFactory;
use Pupilsight\Domain\Finance\FeesGateway;


if (isActionAccessible($guid, $connection2, '/modules/Finance/fee_payment_history.php') == false) {
    //Acess denied
    echo "<div class='error'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {

    
    

    //Proceed!
    $page->breadcrumbs->add(__('Fee Payment History'));

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

    $transactionId = '';
    if (isset($_GET['tid'])) {
        $transactionId = $_GET['tid'];
    }

    $sqlhistory = 'SELECT a.*, b.name as paymentmode, c.name as bankname, d.name as headname, e.series_name, GROUP_CONCAT(DISTINCT f.fn_fees_invoice_id) AS invids FROM fn_fees_collection AS a LEFT JOIN fn_masters AS b ON a.payment_mode_id = b.id LEFT JOIN fn_masters AS c ON a.bank_id = c.id LEFT JOIN fn_fees_head AS d ON a.fn_fees_head_id = d.id LEFT JOIN fn_fee_series AS e ON a.fn_fees_receipt_series_id = e.id LEFT JOIN fn_fees_student_collection AS f ON a.transaction_id = f.transaction_id WHERE a.transaction_id = "'.$transactionId.'" GROUP BY a.transaction_id';
    $resulthis = $connection2->query($sqlhistory);
    $history = $resulthis->fetch();

    $invoice_ids = $history['fn_fees_invoice_id'];
    $sqlp = 'SELECT pupilsightProgramID, name FROM pupilsightProgram ';
    $resultp = $connection2->query($sqlp);
    $rowdataprog = $resultp->fetchAll();

    $program=array();  
    $program2=array();  
    $program1=array(''=>'Select Program');
    foreach ($rowdataprog as $dt) {
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

    

    
   
    
    
    if(!empty($history)){

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
        
        
        $form = Form::create('historyForm', $_SESSION[$guid]['absoluteURL'].'/modules/'.$_SESSION[$guid]['module'].'/fee_payment_history_addProcess.php');
        $form->setFactory(DatabaseFormFactory::create($pdo));
    
        // $form->addHiddenValue('address', $_SESSION[$guid]['address']);
        // $form->addHiddenValue('pupilsightPersonID', $stuId);
        // $form->addHiddenValue('pupilsightSchoolYearID', $pupilsightSchoolYearIDpost);
        // $form->addHiddenValue('invoice_id', '0');
        // $form->addHiddenValue('invoice_item_id', '0');
        echo "<div class ='row fee_hdr '><div class='col-md-12'> Payment History</div></div>";
        $row = $form->addRow();
        $col = $row->addColumn()->setClass('float-left submit_ht');
        
        
        $row = $form->addRow();
                $col = $row->addColumn()->setClass('newdes ');
                $col->addLabel('', __('Transaction Id'));
                $col->addContent($history['transaction_id']);
    
                $col = $row->addColumn()->setClass('newdes');
                $col->addLabel('receipt_number', __('Receipt Number'))->addClass('dte');
                $col->addContent($history['receipt_number']);
            
                $col = $row->addColumn()->setClass('hiddencol');
                $col->addLabel('', __(''));
                $col->addTextField('');   
                
                
            
    
        $col = $row->addColumn()->setClass('newdes');
        $col->addLabel('payment_mode_id', __('Payment Mode'));
        $col->addContent($history['paymentmode']);
        if(!empty($history['bankname'])){
                $col = $row->addColumn()->setClass('newdes');
                $col->addLabel('bank_id', __('Bank Name'));
                $col->addContent($history['bankname']);
        }
        if(!empty($history['reference_no'])){
                $col = $row->addColumn()->setClass('newdes');
                $col->addLabel('bank_id', __('Reference No '));
                $col->addContent($history['reference_no']);
        } 
        if($history['reference_date']!="0000-00-00"){
                $col = $row->addColumn()->setClass('newdes');
                $col->addLabel('bank_id', __('Reference Date '));
                $col->addContent($history['reference_date']);
        }    
    $date = isset($_GET['date']) ? Format::dateConvert($_GET['date']) : date('Y-m-d');

    if(!empty($history['dd_cheque_no']) && !empty($history['dd_cheque_amount'])){
            
        $row = $form->addRow();
            
            $col = $row->addColumn()->setClass('newdes ');
            $col->addLabel('dd_cheque_date', __('DD / Cheque Date'))->addClass('dte');
            $col->addContent($history['dd_cheque_date']);
    
            $col = $row->addColumn()->setClass('newdes ');
            $col->addLabel('dd_cheque_no', __('DD / Cheque No'))->addClass('dte');
            $col->addContent($history['dd_cheque_no']);
    
            $col = $row->addColumn()->setClass('newdes ');
            $col->addLabel('dd_cheque_amount', __('DD / Cheque Amount'))->addClass('dte');
            $col->addContent($history['dd_cheque_amount']);
    
            $col = $row->addColumn()->setClass('newdes ');
            $col->addLabel('payment_status', __('Payment Status'))->addClass('dte');
            $col->addContent($history['payment_status']);
    }
    $row = $form->addRow();
        
        $col = $row->addColumn()->setClass('newdes ');
            $col->addLabel('payment_date', __('Payment Date'))->addClass('dte');
            $col->addContent($history['payment_date']);
            
        $col = $row->addColumn()->setClass('newdes ');
            $col->addLabel('fn_fees_head_id', __('Account Head'))->addClass('dte');
            $col->addContent($history['headname']);
            
        $col = $row->addColumn()->setClass('newdes ');
            $col->addLabel('fn_fees_receipt_series_id', __('Receipt Series'))->addClass('dte');
            $col->addContent($history['series_name']);  
            
        $col = $row->addColumn()->setClass('newdes ');
            $col->addLabel('receipt_number', __('Manual Reciept No'))->addClass('dte');
            if(empty($history['headname'])){
                $col->addContent($history['receipt_number']);
            }
            
        
        $col = $row->addColumn()->setClass('newdes ');
            $col->addLabel('is_custom', __('Manual Receipt No(No Auto Generate)'));
            $col->addCheckbox('is_custom')->addClass('dte')->checked($history['is_custom'])->setValue($history['is_custom'])->disabled('1');    
   
    

    $row = $form->addRow();
        $col = $row->addColumn()->setClass('newdes ');
            $col->addLabel('transcation_amount', __('Transaction Amount'))->addClass('dte');
            $col->addContent($history['transcation_amount']);    
            
        $col = $row->addColumn()->setClass('newdes ');
            $col->addLabel('fine', __('Fine'))->addClass('dte');
            $col->addContent($history['fine']);
            
        $col = $row->addColumn()->setClass('newdes ');
            $col->addLabel('discount', __('Discount'))->addClass('dte');
            $col->addContent($history['discount']);

        $col = $row->addColumn()->setClass('newdes ');
            $col->addLabel('amount_paying', __(' Amount Paying'))->addClass('dte');
            $col->addContent($history['amount_paying']);

        $col = $row->addColumn()->setClass('newdes hiddencol');
            $col->addLabel('total_amount_without_fine_discount', __(' Amount Paying'))->addClass('dte');
            $col->addContent($history['total_amount_without_fine_discount']);   
    
        // $col = $row->addColumn()->setClass('newdes ');
        //     $col->addLabel('', __(''));
        //     $col->addCheckbox('is_pay')->description(__('Do you want to do payment for selected fee items)'))->addClass(' dte');
    $row = $form->addRow();        
        $col = $row->addColumn()->setClass('newdes ');
            $col->addLabel('remarks', __('Remarks'))->addClass('dte');
            $col->addContent($history['remarks']);     
    
           
    $row = $form->addRow();
            $col = $row->addColumn()->setClass('hiddencol');
            $col->addLabel('', __(''));
            $col->addTextField('');   
        
        // $feeItems = $FeesGateway->getInvoiceFeeItems($criteria);
            // $table = DataTable::createPaginated('FeeItemManage', $criteria);
            //  $table->addCheckboxColumn('feeItemid',__(''))
            //  ->setClass('chkbox')
            //      ->context('Select')
            //      ->notSortable();
            //  $table->addColumn('item', __('Fee Item'));
            //  $table->addColumn('desc', __('Description'));
            //  $table->addColumn('0', __('Invoice No'));
            //  $table->addColumn('1', __(' Amount'));
            //  $table->addColumn('2', __('Tax'));
            //  $table->addColumn('3', __('Final Amount'));
            //  $table->addColumn('4', __('Discount'));
            //  $table->addColumn('5', __('Amount Discounted'));
            //  $table->addColumn('6', __('Amount Paid'));
            //  $table->addColumn('7', __('Amount Pending'));
            //  $table->addColumn('7', __('Invoice Status'));
            echo $form->getOutput();

            echo "<div class ='row fee_hdr '><div class='col-md-12'> Fee Items</div></div>";

            echo "<table class='table' cellspacing='0' style='width: 100%' id=''>";
            echo "<thead>";
            echo "<tr class='head'>";
           
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
            
            echo '</tr>';
            echo "</thead>";
            echo "<tbody>";
            
           
            // $sqli = 'SELECT a.*,a.id as itemid, b.*, b.id as ifid, b.name as feeitemname, c.id AS invoiceid, d.format, e.invoice_no as stu_invoice_no, f.item_type, f.name, f.min_invoice, f.max_invoice, f.amount_in_percent, f.amount_in_number  FROM fn_fee_invoice_item AS a LEFT JOIN fn_fee_items AS b ON a.fn_fee_item_id = b.id LEFT JOIN fn_fee_invoice AS c ON a.fn_fee_invoice_id = c.id LEFT JOIN fn_fee_series AS d ON c.inv_fn_fee_series_id = d.id LEFT JOIN fn_fee_invoice_student_assign AS e ON c.id = e.fn_fee_invoice_id  LEFT JOIN fn_fee_discount_item as f ON c.fn_fees_discount_id = f.fn_fees_discount_id AND a.fn_fee_item_id = f.fn_fee_item_id WHERE a.fn_fee_invoice_id IN ('.$history['invids'].') GROUP BY a.id';
            $sqli = 'SELECT e.pupilsightPersonID,a.*,a.id as itemid, b.*, b.id as ifid, b.name as feeitemname, c.id AS invoiceid, c.transport_schedule_id, d.format, e.invoice_no as stu_invoice_no, f.item_type, f.name, f.min_invoice, f.max_invoice, f.amount_in_percent, f.amount_in_number, GROUP_CONCAT(DISTINCT asg.route_id) as routes, GROUP_CONCAT(DISTINCT asg.transport_type) as routetype  FROM fn_fee_invoice_item AS a LEFT JOIN fn_fee_items AS b ON a.fn_fee_item_id = b.id LEFT JOIN fn_fee_invoice AS c ON a.fn_fee_invoice_id = c.id LEFT JOIN fn_fee_series AS d ON c.inv_fn_fee_series_id = d.id LEFT JOIN fn_fee_invoice_student_assign AS e ON c.id = e.fn_fee_invoice_id  LEFT JOIN fn_fee_discount_item as f ON c.fn_fees_discount_id = f.fn_fees_discount_id AND a.fn_fee_item_id = f.fn_fee_item_id LEFT JOIN trans_route_assign AS asg ON e.pupilsightPersonID = asg.pupilsightPersonID WHERE a.fn_fee_invoice_id IN ('.$history['invids'].') AND e.pupilsightPersonID = '.$history['pupilsightPersonID'].' GROUP BY a.id';
            $resulti = $connection2->query($sqli);
            $feeItem = $resulti->fetchAll();
            
            $data = '';
            // echo '<pre>';
            // print_r($feeItem);
            // echo '</pre>';
            $paidColl = 0;
            foreach($feeItem as $fI){
                $invNo = $fI['stu_invoice_no'];
                if(!empty($fI['transport_schedule_id'])){
                    $routes = explode(',',$fI['routes']);
                    foreach($routes as $rt){
                        $sqlsc = 'SELECT * FROM trans_route_price WHERE schedule_id = '.$fI['transport_schedule_id'].' AND route_id = '.$rt.' ';
                        $resultsc = $connection2->query($sqlsc);
                        $datasc = $resultsc->fetch();
                        if($fI['routetype'] == 'oneway'){
                            $price = $datasc['oneway_price'];
                            $tax = $datasc['tax'];
                            $amtperc = ($tax / 100) * $price;
                            $tranamount = $price + $amtperc;
                        } else {
                            $price = $datasc['twoway_price'];
                            $tax = $datasc['tax'];
                            $amtperc = ($tax / 100) * $price;
                            $tranamount = $price + $amtperc;
                        }
                    }
                    $totalamount = $tranamount;
                } else {
                    $totalamount = $fI['total_amount'];
                }
    
    
                 $sqlchk = 'SELECT COUNT(a.id) as kount, a.id, a.total_amount, a.total_amount_collection FROM fn_fees_student_collection AS a LEFT JOIN fn_fees_collection AS b ON a.transaction_id = b.transaction_id WHERE a.fn_fee_invoice_item_id = '.$fI['itemid'].' AND a.pupilsightPersonID = '.$history['pupilsightPersonID'].' AND a.transaction_id = '.$transactionId.' AND b.transaction_status = "1" ';
                
                //$sqlchk = 'SELECT COUNT(a.id) as kount, total_amount, SUM(a.total_amount_collection) as tot_coll FROM fn_fees_student_collection AS a LEFT JOIN fn_fees_collection AS b ON a.transaction_id = b.transaction_id WHERE a.fn_fee_invoice_item_id = '.$fI['itemid'].' AND a.pupilsightPersonID = '.$history['pupilsightPersonID'].' AND a.invoice_no = "' . $invNo . '" AND b.transaction_status = "1" ';
                $resultchk = $connection2->query($sqlchk);
                $itemchk = $resultchk->fetch();

                $stuColId = $itemchk['id'];
                
                $sqlchk1 = 'SELECT SUM(a.total_amount_collection) as tot_coll FROM fn_fees_student_collection AS a LEFT JOIN fn_fees_collection AS b ON a.transaction_id = b.transaction_id WHERE a.fn_fee_invoice_item_id = '.$fI['itemid'].' AND a.pupilsightPersonID = '.$history['pupilsightPersonID'].' AND a.invoice_no = "' . $invNo . '" AND a.id <= "'.$stuColId.'" AND b.transaction_status = "1" ';
                $resultchk1 = $connection2->query($sqlchk1);
                $itemchk1 = $resultchk1->fetch();
                if(!empty($itemchk1['tot_coll'])){
                    $paidColl = $itemchk1['tot_coll'];
                }
                // echo $sqlchk1.'</br>';
                // echo $paidColl.'</br>';
    
                // $inid = '000'.$id;
                // $invno = str_replace("0001",$inid,$fI['format']);
                

                if($fI['item_type'] == 'Fixed'){
                    $discount = $fI['amount_in_number'];
                    $discountamt = $fI['amount_in_number'];
                } else {
                    $discount = $fI['amount_in_percent'].'%';
                    $discountamt = ($fI['amount_in_percent'] / 100) * $totalamount;
                }
                $amtdiscount = $totalamount - $discountamt;
                if($itemchk['kount'] == '1'){
                    $cls = '';
                    $checked = 'checked disabled';
                    $paidamt = $amtdiscount;
                    $unpaidamt = 0;
                } else {
                    $cls = 'selFeeItem';
                    $checked = '';
                    $paidamt = 0;
                    $unpaidamt = $amtdiscount;
                }

                if(!empty($itemchk)){
                    $totalAmt = number_format($itemchk['total_amount'], 2);
                    $paidAmt = number_format($itemchk['total_amount_collection'], 2);
                    //$collection = $itemchk['total_amount_collection'] + $paidColl;
                    $pendingAmt = $itemchk['total_amount'] - $paidColl;
                    if($pendingAmt < 0){
                        $pendingAmt = 0;
                    } else {
                        $pendingAmt = number_format($pendingAmt, 2);
                    }
                }

                $discountItem = 0;
                if (!empty($fI['discount'])) {
                    $discountItem = $fI['discount'] + $discountamt;
                } else {
                    $discountItem = $discountamt;
                }
    
                $data .= '<tr class="odd invrow" role="row">
                      
                
                <td class="p-2 sm:p-3">
                   '.$fI['feeitemname'].'     
                </td>
                 
                <td class="p-2 sm:p-3 hidden-1 sm:table-cell">
                '.$fI['description'].'
                </td>
                 
                <td class="p-2 sm:p-3 hidden-1 md:table-cell">
                '.$fI['amount'].'  
                </td>
                 
                <td class="p-2 sm:p-3 hidden-1 md:table-cell">
                '.$fI['tax'].'% 
                </td>
                 
                <td class="p-2 sm:p-3 hidden-1 md:table-cell">
                '.$totalamount.'   
                </td>
                 
                <td class="p-2 sm:p-3 hidden-1 md:table-cell">
                '.$discount.'     
                </td>
                 
                <td class="p-2 sm:p-3 hidden-1 md:table-cell">
                   '.$discountItem.'
                </td>
                <td class="p-2 sm:p-3 hidden-1 md:table-cell">
                '.$totalAmt.'   
                </td>
                <td class="p-2 sm:p-3 hidden-1 md:table-cell">
                '.$paidAmt.'
                </td>
                 
                <td class="p-2 sm:p-3 hidden-1 md:table-cell">
                '.$pendingAmt.'
                </td>
                
                 
            </tr>';
            }
            echo $data;
            echo "</tbody>";
            echo '</table>';
        
        
        //echo $table->render($feeItems);
    //     echo "<div style='height:50px;'></div>";
        
    //     echo "<div class='row'><div class='col-md-6' style='height:50px;'><div class='float-left mb-2'><input type='text' name='invoice_serach' id='invoice_serach' placeholder='search'> <button style='width: 35px;
    //     margin-left: 2px;border-radius: 3px;color: #fff;line-height: 30px;
    //     background: #2196f3;'><span  style=' margin-left: -22px;'><i class='flaticon-search'></i></span></button></div></div>";
        
    // echo '<div class="col-md-6" style="float-right"><select id="paid_staus">
    // <option value="1">Not Paid</option>
    // <option value="0">paid</option>

    // </select></div></div>';

        
        echo "<div style='height:50px; margin-top:10px; display:none;' id='showPaymentButton'><div class='float-left mb-2'><a id='makePayment' class=' btn btn-primary' >Do Payment</a>";  
    echo " </div><div class='float-none'></div></div>"; 
        
        
        // $invoices = $FeesGateway->getCollectionInvoice($criteria, $stuId);
        // $table1 = DataTable::createPaginated('FeeInvoiceListManage', $criteria);
        
        // $table1->addCheckboxColumn('invoiceid',__(''))
        //     ->setClass('chkbox')
        //     ->notSortable();
        // $table1->addColumn('stu_invoice_no', __('Invoice No')); 
        // $table1->addColumn('title', __('Title'));
        // $table1->addColumn('totalamount', __('Amount'));
        // $table1->addColumn('pendingamount', __('Pending Amount'));
        

        // echo $table1->render($invoices);

        // $invoices = 'SELECT fn_fee_invoice.*,fn_fee_invoice.id as invoiceid,SUM(fn_fee_invoice_item.total_amount) as totalamount, fn_fee_invoice_student_assign.invoice_no as stu_invoice_no, g.fine_type, g.rule_type FROM fn_fee_invoice LEFT JOIN pupilsightStudentEnrolment ON fn_fee_invoice.pupilsightSchoolYearID=pupilsightStudentEnrolment.pupilsightSchoolYearID LEFT JOIN pupilsightPerson ON pupilsightStudentEnrolment.pupilsightPersonID=pupilsightPerson.pupilsightPersonID LEFT JOIN fn_fee_invoice_item ON fn_fee_invoice.id=fn_fee_invoice_item.fn_fee_invoice_id LEFT JOIN fn_fee_invoice_student_assign ON pupilsightPerson.pupilsightPersonID=fn_fee_invoice_student_assign.pupilsightPersonID AND fn_fee_invoice.id = fn_fee_invoice_student_assign.fn_fee_invoice_id LEFT JOIN fn_fees_fine_rule AS g ON fn_fee_invoice.fn_fees_fine_rule_id = g.id WHERE pupilsightPerson.pupilsightPersonID = "'.$history['pupilsightPersonID'].'" GROUP BY fn_fee_invoice.id';

        // $invoices = 'SELECT fn_fee_invoice.*,fn_fee_invoice.id as invoiceid, fn_fee_invoice_student_assign.invoice_no as stu_invoice_no, g.fine_type, g.rule_type, GROUP_CONCAT(DISTINCT asg.route_id) as routes, GROUP_CONCAT(DISTINCT asg.transport_type) as routetype FROM fn_fee_invoice LEFT JOIN pupilsightStudentEnrolment ON fn_fee_invoice.pupilsightSchoolYearID=pupilsightStudentEnrolment.pupilsightSchoolYearID LEFT JOIN pupilsightPerson ON pupilsightStudentEnrolment.pupilsightPersonID=pupilsightPerson.pupilsightPersonID RIGHT JOIN fn_fee_invoice_student_assign ON pupilsightPerson.pupilsightPersonID=fn_fee_invoice_student_assign.pupilsightPersonID AND fn_fee_invoice.id = fn_fee_invoice_student_assign.fn_fee_invoice_id LEFT JOIN fn_fees_fine_rule AS g ON fn_fee_invoice.fn_fees_fine_rule_id = g.id LEFT JOIN trans_route_assign AS asg ON pupilsightPerson.pupilsightPersonID = asg.pupilsightPersonID WHERE pupilsightPerson.pupilsightPersonID = "'.$history['pupilsightPersonID'].'" GROUP BY fn_fee_invoice.id';
        // $resultinv = $connection2->query($invoices);
        // $invdata = $resultinv->fetchAll();
  
        // foreach($invdata as $k => $d){
        //     $sqlamt = 'SELECT SUM(fn_fee_invoice_item.total_amount) as totalamount FROM fn_fee_invoice_item WHERE fn_fee_invoice_id = '.$d['invoiceid'].' '; 
        //     $resultamt = $connection2->query($sqlamt);
        //     $dataamt = $resultamt->fetch();


        //     //unset($invdata[$k]['finalamount']);
        //     if(!empty($d['transport_schedule_id'])){
        //         $routes = explode(',',$d['routes']);
        //         foreach($routes as $rt){
        //             $sqlsc = 'SELECT * FROM trans_route_price WHERE schedule_id = '.$d['transport_schedule_id'].' AND route_id = '.$rt.' ';
        //             $resultsc = $connection2->query($sqlsc);
        //             $datasc = $resultsc->fetch();
        //             if($d['routetype'] == 'oneway'){
        //                 $price = $datasc['oneway_price'];
        //                 $tax = $datasc['tax'];
        //                 $amtperc = ($tax / 100) * $price;
        //                 $tranamount = $price + $amtperc;
        //             } else {
        //                 $price = $datasc['twoway_price'];
        //                 $tax = $datasc['tax'];
        //                 $amtperc = ($tax / 100) * $price;
        //                 $tranamount = $price + $amtperc;
        //             }
        //         }
        //         $totalamount = $tranamount;
        //     } else {
        //         $totalamount = $dataamt['totalamount'];
        //     }
        //     $invdata[$k]['finalamount'] = $totalamount;
           


        //     $date = date('Y-m-d');
        //     $curdate = strtotime($date);
        //     $duedate = strtotime($d['due_date']);
        //     $fineId = $d['fn_fees_fine_rule_id'];

        //     if(!empty($fineId) && $curdate > $duedate){
        //         $finetype = $d['fine_type'];
        //         $ruletype = $d['rule_type'];
        //         if($finetype == '1' && $ruletype == '1'){
        //             $sqlf = 'SELECT * FROM fn_fees_rule_type WHERE fn_fees_fine_rule_id	= "'.$fineId.'" AND fine_type = "'.$finetype.'" AND rule_type = "'.$ruletype.'" ';
        //             $resultf = $connection2->query($sqlf);
        //             $finedata = $resultf->fetch();
        //             $amtper = $finedata['amount_in_percent'];
        //             $type = 'percent';
        //         } elseif($finetype == '1' && $ruletype == '2'){
        //             $sqlf = 'SELECT * FROM fn_fees_rule_type WHERE fn_fees_fine_rule_id	= "'.$fineId.'" AND fine_type = "'.$finetype.'" AND rule_type = "'.$ruletype.'" ';
        //             $resultf = $connection2->query($sqlf);
        //             $finedata = $resultf->fetch();
        //             $amtper = $finedata['amount_in_number'];
        //             $type = 'num';
        //         } elseif($finetype == '1' && $ruletype == '3'){
        //             $sqlf = 'SELECT * FROM fn_fees_rule_type WHERE fn_fees_fine_rule_id	= "'.$fineId.'" AND fine_type = "'.$finetype.'" AND rule_type = "'.$ruletype.'" AND from_date <= "'.$date.'" AND to_date >= "'.$date.'" ';
        //             $resultf = $connection2->query($sqlf);
        //             $finedata = $resultf->fetch();
        //             if(!empty($finedata)){
        //                 $amtper = $finedata['amount_in_number'];
        //                 $type = 'num';
        //             } else {
        //                 $amtper = '';
        //                 $type = '';
        //             }
        //         } elseif($finetype == '2' && $ruletype == '1'){
        //             $sqlf = 'SELECT * FROM fn_fees_rule_type WHERE fn_fees_fine_rule_id	= "'.$fineId.'" AND fine_type = "'.$finetype.'" AND rule_type = "'.$ruletype.'" ';
        //             $resultf = $connection2->query($sqlf);
        //             $finedata = $resultf->fetch();
        //             $amtper = $finedata['amount_in_percent'];
        //             $type = 'percent';
        //         } elseif($finetype == '2' && $ruletype == '2'){
        //             $sqlf = 'SELECT * FROM fn_fees_rule_type WHERE fn_fees_fine_rule_id	= "'.$fineId.'" AND fine_type = "'.$finetype.'" AND rule_type = "'.$ruletype.'" ';
        //             $resultf = $connection2->query($sqlf);
        //             $finedata = $resultf->fetch();
        //             $amtper = $finedata['amount_in_number'];
        //             $type = 'num';
        //         } elseif($finetype == '3' && $ruletype == '1'){
        //             $sqlf = 'SELECT * FROM fn_fees_rule_type WHERE fn_fees_fine_rule_id	= "'.$fineId.'" AND fine_type = "'.$finetype.'" AND rule_type = "'.$ruletype.'" ';
        //             $resultf = $connection2->query($sqlf);
        //             $finedata = $resultf->fetch();
        //             $amtper = $finedata['amount_in_percent'];
        //             $type = 'percent';
        //         } elseif($finetype == '3' && $ruletype == '2'){
        //             $sqlf = 'SELECT * FROM fn_fees_rule_type WHERE fn_fees_fine_rule_id	= "'.$fineId.'" AND fine_type = "'.$finetype.'" AND rule_type = "'.$ruletype.'" ';
        //             $resultf = $connection2->query($sqlf);
        //             $finedata = $resultf->fetch();
        //             $amtper = $finedata['amount_in_number'];
        //             $type = 'num';
        //         } elseif($finetype == '3' && $ruletype == '4'){
        //             $date1 = strtotime($d['due_date']);  
        //             $date2 = strtotime($date); 
        //             $diff = abs($date2 - $date1);
        //             $years = floor($diff / (365*60*60*24));  
        //             $months = floor(($diff - $years * 365*60*60*24) / (30*60*60*24));   
        //             $days = floor(($diff - $years * 365*60*60*24 - $months*30*60*60*24)/ (60*60*24));

        //             $sqlf = 'SELECT * FROM fn_fees_rule_type WHERE fn_fees_fine_rule_id	= "'.$fineId.'" AND fine_type = "'.$finetype.'" AND rule_type = "'.$ruletype.'" AND from_day <= "'.$days.'" AND to_day >= "'.$days.'" ';
        //             $resultf = $connection2->query($sqlf);
        //             $finedata = $resultf->fetch();
        //             $amtper = $finedata['amount_in_number'];
        //             $type = 'num';
        //         } else {
        //             $amtper = '';
        //             $type = '';
        //         }
        //     } else {
        //         $amtper = '';
        //         $type = '';
        //     }
        //     $invdata[$k]['amtper'] = $amtper;
        //     $invdata[$k]['type'] = $type;


        //     $invid =  $d['invoiceid'];
        //     $invno =  $d['stu_invoice_no'];
        //     $sqla = 'SELECT GROUP_CONCAT(a.fn_fee_invoice_item_id) AS invitemid FROM fn_fees_student_collection AS a LEFT JOIN fn_fees_collection AS b ON  a.transaction_id = b.transaction_id WHERE a.invoice_no = "'.$invno.'" AND b.transaction_status = "1" ';
        //     $resulta = $connection2->query($sqla);
        //     $inv = $resulta->fetch();
        //     if(!empty($inv['invitemid'])){
        //         if(!empty($d['transport_schedule_id'])){
        //             $invdata[$k]['paidamount'] = $totalamount;
        //             $pendingamount = 0;
        //             $invdata[$k]['pendingamount'] = $pendingamount;
        //             $invdata[$k]['chkpayment'] = 'Paid';
        //         } else {    
        //             $itemids = $inv['invitemid'];
        //             $sqlp = 'SELECT SUM(total_amount) as paidtotalamount FROM fn_fee_invoice_item WHERE id IN ('.$itemids.') ';
        //             $resultp = $connection2->query($sqlp);
        //             $amt = $resultp->fetch();
        //             $totalpaidamt = $amt['paidtotalamount'];
        //             if(!empty($totalpaidamt)){
        //                 $invdata[$k]['paidamount'] = $totalpaidamt;
        //                 $pendingamount = $totalamount- $totalpaidamt;
        //                 $invdata[$k]['pendingamount'] = $pendingamount;
        //                 if($pendingamount == ''){
        //                     $invdata[$k]['chkpayment'] = 'Paid';
        //                 }
                        
        //             } 
        //         }
        //     } else {
        //         $invdata[$k]['paidamount'] = '0';
        //         $pendingamount = $totalamount;
        //         $invdata[$k]['pendingamount'] = $pendingamount;
        //         $invdata[$k]['chkpayment'] = 'UnPaid';
        //     }
        // }

        echo "<div class ='row fee_hdr FeeInvoiceListManage'><div class='col-md-12'> Invoices</div></div>";

        echo "<table class='table' cellspacing='0' style='width: 100%' id='FeeInvoiceListManage'>";
        echo "<thead>";
        echo "<tr class='head'>";
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
        echo __('Amount with Discount');
        echo '</th>';
        echo "<th>";
        echo __('Pending Amount');
        echo '</th>';
        echo "</thead>";
        echo "<tbody id='getInvoiceFeeItem'>";

        $stuId = $history['pupilsightPersonID'];

        $invoices = 'SELECT fn_fee_invoice.*,fn_fee_invoice.id as invoiceid, fn_fee_invoice_student_assign.invoice_no as stu_invoice_no, fn_fee_invoice_student_assign.id as invid, g.is_fine_editable, g.fine_type, g.rule_type, GROUP_CONCAT(DISTINCT asg.route_id) as routes, GROUP_CONCAT(DISTINCT asg.transport_type) as routetype FROM fn_fee_invoice LEFT JOIN pupilsightStudentEnrolment ON fn_fee_invoice.pupilsightSchoolYearID=pupilsightStudentEnrolment.pupilsightSchoolYearID LEFT JOIN pupilsightPerson ON pupilsightStudentEnrolment.pupilsightPersonID=pupilsightPerson.pupilsightPersonID RIGHT JOIN fn_fee_invoice_student_assign ON pupilsightPerson.pupilsightPersonID=fn_fee_invoice_student_assign.pupilsightPersonID AND fn_fee_invoice.id = fn_fee_invoice_student_assign.fn_fee_invoice_id LEFT JOIN fn_fees_fine_rule AS g ON fn_fee_invoice.fn_fees_fine_rule_id = g.id LEFT JOIN trans_route_assign AS asg ON pupilsightPerson.pupilsightPersonID = asg.pupilsightPersonID WHERE fn_fee_invoice.pupilsightSchoolYearID = "' . $pupilsightSchoolYearID . '" AND fn_fee_invoice.id IN ('.$invoice_ids.') AND pupilsightPerson.pupilsightPersonID = "' . $stuId . '" AND fn_fee_invoice_student_assign.status = 1  GROUP BY fn_fee_invoice.id ORDER BY fn_fee_invoice_student_assign.id ASC';
            $resultinv = $connection2->query($invoices);
            $invdata = $resultinv->fetchAll();
            // echo '<pre>';
            // print_r($invdata);
            // echo '</pre>';
            // die();
            $totalamount = 0;
            foreach ($invdata as $k => $d) {
                $sqlsd = 'SELECT b.name FROM fn_fee_invoice_item AS a LEFT JOIN fn_fee_items AS b ON a.fn_fee_item_id = b.id WHERE a.fn_fee_invoice_id = ' . $d['invoiceid'] . ' AND b.name = "Staff Discount"  ';
                $resultsd = $connection2->query($sqlsd);
                $dataSD = $resultsd->fetch();
                if(!empty($dataSD)){
                    $invdata[$k]['sdDis'] = $dataSD['name'];
                } else {
                    $invdata[$k]['sdDis'] = '';
                }
                

                $sqlamt = 'SELECT SUM(fn_fee_invoice_item.total_amount) as totalamount, SUM(fn_fee_invoice_item.discount) as disamount FROM fn_fee_invoice_item WHERE fn_fee_invoice_id = ' . $d['invoiceid'] . ' ';
                $resultamt = $connection2->query($sqlamt);
                $dataamt = $resultamt->fetch();
                $sql_dis = "SELECT discount FROM fn_invoice_level_discount WHERE pupilsightPersonID = " . $stuId . "  AND invoice_id='" . $d['invoiceid'] . "' ";
                $result_dis = $connection2->query($sql_dis);
                $special_dis = $result_dis->fetch();

                $sp_item_sql = "SELECT SUM(discount.discount) as sp_discount
                FROM fn_fee_invoice_item as fee_item
                LEFT JOIN fn_fee_item_level_discount as discount
                ON fee_item.id = discount.item_id WHERE fee_item.fn_fee_invoice_id= ".$d['invoiceid']." AND pupilsightPersonID = ".$stuId."  ";
                $result_sp_item = $connection2->query($sp_item_sql);
                $sp_item_dis = $result_sp_item->fetch();
                //unset($invdata[$k]['finalamount']);

                if (!empty($d['transport_schedule_id']) && $d['transport_schedule_id'] != '') {
                    $routes = explode(',', $d['routes']);
                    if(!empty($routes)){
                        $tranamount = 0;
                        foreach ($routes as $rt) {
                            if(!empty($rt)){
                                $sqlsc = 'SELECT * FROM trans_route_price WHERE schedule_id = ' . $d['transport_schedule_id'] . ' AND route_id = ' . $rt . ' ';
                                $resultsc = $connection2->query($sqlsc);
                                $datasc = $resultsc->fetch();
                                if ($d['routetype'] == 'oneway') {
                                    $price = $datasc['oneway_price'];
                                    $tax = $datasc['tax'];
                                    $amtperc = ($tax / 100) * $price;
                                    $tranamount = $price + $amtperc;
                                } else {
                                    $price = $datasc['twoway_price'];
                                    $tax = $datasc['tax'];
                                    $amtperc = ($tax / 100) * $price;
                                    $tranamount = $price + $amtperc;
                                }
                            }
                        }
                        $totalamount = $tranamount;
                    }
                    
                } else {
                    $totalamount = $dataamt['totalamount'];
                }

                
                $tot_amt_without_dis = $totalamount;
                $invdata[$k]['finalamount'] = $tot_amt_without_dis;
                if (!empty($special_dis['discount']) || !empty($sp_item_dis['sp_discount'])) {
                    $invdata[$k]['finalamount_with_des'] = $totalamount - $special_dis['discount'] - $sp_item_dis['sp_discount'];
                    $totalamount = $totalamount - $special_dis['discount'] - $sp_item_dis['sp_discount'];
                    $dis_item_inv = $special_dis['discount'] + $sp_item_dis['sp_discount'];

                } else {
                    $invdata[$k]['finalamount_with_des'] = $totalamount;
                    $dis_item_inv = 0;
                }

                if (!empty($d['fn_fees_discount_id'])) {
                    $std_query = "SELECT fee_category_id FROM `pupilsightPerson` WHERE `pupilsightPersonID` = " . $stuId . " ";
                    $std_exe = $connection2->query($std_query);
                    $std_data = $std_exe->fetch();
                    $fee_category_id = $std_data['fee_category_id'];

                    $dissql = "SELECT * FROM fn_fee_discount_item WHERE fn_fees_discount_id = " . $d['fn_fees_discount_id'] . " AND name = " . $fee_category_id . " ";
                    $resultdisitem = $connection2->query($dissql);
                    $disamtdata = $resultdisitem->fetch();

                    if (!empty($disamtdata)) {
                        if ($disamtdata['item_type'] == 'Fixed') {
                            $totalamount = $totalamount - $disamtdata['amount_in_number'];
                            $invdata[$k]['finalamount'] = $totalamount;
                        } else {
                            $totalamount = $totalamount / 100 * $disamtdata['amount_in_percent'];
                            $invdata[$k]['finalamount'] = $totalamount;
                        }
                    }
                }

                $totalamount = number_format($totalamount, 2, '.', '');
                //    echo $totalamount;
                //    die();
                $date = date('Y-m-d');
                $curdate = strtotime($date);
                $duedate = strtotime($d['due_date']);
                $fineId = $d['fn_fees_fine_rule_id'];

                if (!empty($fineId) && $curdate > $duedate) {
                    $sqlschday = "SELECT GROUP_CONCAT(pupilsightDaysOfWeekID) as daysid, GROUP_CONCAT(name) as weekend FROM pupilsightDaysOfWeek WHERE schoolDay = 'N' ";
                    $resultschday = $connection2->query($sqlschday);
                    $weekenddata = $resultschday->fetch();
                    $weekendDaysId = $weekenddata['daysid'];

                    $datediff = $curdate - $duedate;
                    $dd = round($datediff / (60 * 60 * 24));

                    $finetype = $d['fine_type'];
                    $ruletype = $d['rule_type'];
                    if ($finetype == '1' && $ruletype == '1') {
                        $sqlf = 'SELECT * FROM fn_fees_rule_type WHERE fn_fees_fine_rule_id = "' . $fineId . '" AND fine_type = "' . $finetype . '" AND rule_type = "' . $ruletype . '" ';
                        $resultf = $connection2->query($sqlf);
                        $finedata = $resultf->fetch();
                        $amtper = $finedata['amount_in_percent'];
                        $type = 'percent';
                    } elseif ($finetype == '1' && $ruletype == '2') {
                        $sqlf = 'SELECT * FROM fn_fees_rule_type WHERE fn_fees_fine_rule_id = "' . $fineId . '" AND fine_type = "' . $finetype . '" AND rule_type = "' . $ruletype . '" ';
                        $resultf = $connection2->query($sqlf);
                        $finedata = $resultf->fetch();
                        $amtper = $finedata['amount_in_number'];
                        $type = 'num';
                    } elseif ($finetype == '1' && $ruletype == '3') {
                        $sqlf = 'SELECT * FROM fn_fees_rule_type WHERE fn_fees_fine_rule_id = "' . $fineId . '" AND fine_type = "' . $finetype . '" AND rule_type = "' . $ruletype . '" AND from_date <= "' . $date . '" AND to_date >= "' . $date . '" ';
                        $resultf = $connection2->query($sqlf);
                        $finedata = $resultf->fetch();
                        if (!empty($finedata)) {
                            if ($finedata['amount_type'] == 'Fixed') {
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
                    } elseif ($finetype == '2' && $ruletype == '1') {
                        if ($d['due_date'] != '1970-01-01') {
                            $sqlf = 'SELECT * FROM fn_fees_rule_type WHERE fn_fees_fine_rule_id = "' . $fineId . '" AND fine_type = "' . $finetype . '" AND rule_type = "' . $ruletype . '" ';
                            $resultf = $connection2->query($sqlf);
                            $finedata = $resultf->fetch();
                            $no = 0;
                            if (!empty($finedata['ignore_holiday'])) {
                                $cdate = $date;
                                $ddate = $d['due_date'];
                                $no = countholidays($cdate, $ddate, $weekendDaysId);
                            }

                            if ($no != '0') {
                                $nday = $dd - $no;
                            } else {
                                $nday = $dd;
                            }

                            if (!empty($nday)) {
                                $amtper = $finedata['amount_in_percent'] * $nday;
                            } else {
                                $amtper = $finedata['amount_in_percent'];
                            }
                            $type = 'percent';
                        }
                    } elseif ($finetype == '2' && $ruletype == '2') {
                        if ($d['due_date'] != '1970-01-01') {
                            $sqlf = 'SELECT * FROM fn_fees_rule_type WHERE fn_fees_fine_rule_id = "' . $fineId . '" AND fine_type = "' . $finetype . '" AND rule_type = "' . $ruletype . '" ';
                            $resultf = $connection2->query($sqlf);
                            $finedata = $resultf->fetch();
                            $no = 0;
                            if (!empty($finedata['ignore_holiday'])) {
                                $cdate = $date;
                                $ddate = $d['due_date'];
                                $no = countholidays($cdate, $ddate, $weekendDaysId);
                            }

                            if ($no != '0') {
                                $nday = $dd - $no;
                            } else {
                                $nday = $dd;
                            }

                            if (!empty($nday)) {
                                $amtper = $finedata['amount_in_number'] * $nday;
                            } else {
                                $amtper = $finedata['amount_in_number'];
                            }

                            $type = 'num';
                        }
                    } elseif ($finetype == '3' && $ruletype == '1') {
                        $sqlf = 'SELECT * FROM fn_fees_rule_type WHERE fn_fees_fine_rule_id = "' . $fineId . '" AND fine_type = "' . $finetype . '" AND rule_type = "' . $ruletype . '" ';
                        $resultf = $connection2->query($sqlf);
                        $finedata = $resultf->fetch();
                        $amtper = $finedata['amount_in_percent'];
                        $type = 'percent';
                    } elseif ($finetype == '3' && $ruletype == '2') {
                        $sqlf = 'SELECT * FROM fn_fees_rule_type WHERE fn_fees_fine_rule_id = "' . $fineId . '" AND fine_type = "' . $finetype . '" AND rule_type = "' . $ruletype . '" ';
                        $resultf = $connection2->query($sqlf);
                        $finedata = $resultf->fetch();
                        $amtper = $finedata['amount_in_number'];
                        $type = 'num';
                    } elseif ($finetype == '3' && $ruletype == '4') {
                        $date1 = strtotime($d['due_date']);
                        $date2 = strtotime($date);
                        $diff = abs($date2 - $date1);
                        $years = floor($diff / (365 * 60 * 60 * 24));
                        $months = floor(($diff - $years * 365 * 60 * 60 * 24) / (30 * 60 * 60 * 24));
                        $days = floor(($diff - $years * 365 * 60 * 60 * 24 - $months * 30 * 60 * 60 * 24) / (60 * 60 * 24));

                        $sqlf = 'SELECT * FROM fn_fees_rule_type WHERE fn_fees_fine_rule_id = "' . $fineId . '" AND fine_type = "' . $finetype . '" AND rule_type = "' . $ruletype . '" AND from_day <= "' . $days . '" AND to_day >= "' . $days . '" ';
                        $resultf = $connection2->query($sqlf);
                        $finedata = $resultf->fetch();

                        $no = 0;
                        if (!empty($finedata['ignore_holiday'])) {
                            $cdate = $date;
                            $ddate = $d['due_date'];
                            $no = countholidays($cdate, $ddate, $weekendDaysId);
                        }

                        if ($no != '0') {
                            $nday = $dd - $no;
                        } else {
                            $nday = $dd;
                        }

                        // if(!empty($nday)){
                        //     if($finedata['amount_type'] == 'Fixed'){
                        //         $amtper = $finedata['amount_in_number']  * $nday;
                        //         $type = 'num';
                        //     } else {
                        //         $amtper = $finedata['amount_in_percent']  * $nday;
                        //         $type = 'percent';
                        //     }
                        // } else {
                        //     if($finedata['amount_type'] == 'Fixed'){
                        //         $amtper = $finedata['amount_in_number'];
                        //         $type = 'num';
                        //     } else {
                        //         $amtper = $finedata['amount_in_percent'];
                        //         $type = 'percent';
                        //     }
                        // }

                        if ($finedata['amount_type'] == 'Fixed') {
                            $amtper = $finedata['amount_in_number'];
                            $type = 'num';
                        } else {
                            $amtper = $finedata['amount_in_percent'];
                            $type = 'percent';
                        }

                        //$amtper = $dd.'-'.$nday;

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
                $sqla = 'SELECT GROUP_CONCAT(a.fn_fee_invoice_item_id) AS invitemid, b.invoice_status, b.transaction_id FROM fn_fees_student_collection AS a LEFT JOIN fn_fees_collection AS b ON  a.transaction_id = b.transaction_id WHERE a.invoice_no = "' . $invno . '" AND b.transaction_status IN (1,3) ';
                $resulta = $connection2->query($sqla);
                $inv = $resulta->fetch();
                $invdata[$k]['chkpayment'] = '';
                $invdata[$k]['pendingamount'] = '';


                $invdata[$k]['invno'] = $invno;

                $disamount = $dataamt['disamount'];
                $totamtdisamount = $tot_amt_without_dis + $dataamt['disamount'];
                $invdata[$k]['totamtdisamount'] = $totamtdisamount;
                $invdata[$k]['disamount'] = $disamount + $dis_item_inv;
                

                $sqlchkInv = 'SELECT count(b.id) as kount FROM fn_fees_student_collection AS a LEFT JOIN fn_fees_collection AS b ON  a.transaction_id = b.transaction_id WHERE a.invoice_no = "' . $invno . '" AND b.invoice_status = "Fully Paid" AND b.transaction_status IN (1,3) ';
                $resultchkInv = $connection2->query($sqlchkInv);
                $invChk = $resultchkInv->fetch();

                // if ($inv['invoice_status'] == 'Fully Paid') {
                if (!empty($invChk) && $invChk['kount'] >= 1) {
                    $invdata[$k]['paidamount'] = $totalamount;
                    $pendingamount = 0;
                    $invdata[$k]['pendingamount'] = $pendingamount;
                    $invdata[$k]['chkpayment'] = 'Paid';
                } else {
                    if (!empty($inv['invitemid'])) {
                        $stTransId = $inv['transaction_id'];
                        if (!empty($d['transport_schedule_id'])) {
                            $invdata[$k]['paidamount'] = $totalamount;
                            $pendingamount = 0;
                            $invdata[$k]['pendingamount'] = $pendingamount;
                            $invdata[$k]['chkpayment'] = 'Paid';
                        } else {
                            $itemids = $inv['invitemid'];
                            // $sqlp = 'SELECT SUM(total_amount_collection) as paidtotalamount FROM fn_fees_student_collection WHERE pupilsightPersonID = ' . $stuId . ' AND transaction_id = ' . $stTransId . ' AND fn_fee_invoice_item_id IN (' . $itemids . ') ';
                            $sqlp = 'SELECT SUM(total_amount_collection) as paidtotalamount FROM fn_fees_student_collection WHERE pupilsightPersonID = ' . $stuId . ' AND invoice_no = "' . $invno . '" AND fn_fee_invoice_item_id IN (' . $itemids . ') AND is_active = "1" ';
                            $resultp = $connection2->query($sqlp);
                            $amt = $resultp->fetch();
                            $totalpaidamt = $amt['paidtotalamount'];
                            if (!empty($totalpaidamt)) {
                                $invdata[$k]['paidamount'] = $totalpaidamt;
                                $pendingamount = $totalamount - $totalpaidamt;
                                if ($pendingamount < 0) {
                                    $pendingamount = abs($pendingamount) . "(Fine paid)";
                                }
                                $invdata[$k]['pendingamount'] = $pendingamount;
                                if ($pendingamount <= 0) {
                                    $invdata[$k]['chkpayment'] = 'Paid';
                                } else {
                                    $invdata[$k]['chkpayment'] = 'Half Paid';
                                }
                            }
                        }
                    } else {
                        $invdata[$k]['paidamount'] = '0';
                        $pendingamount = $totalamount;
                        $invdata[$k]['pendingamount'] = $pendingamount;
                        $invdata[$k]['chkpayment'] = 'UnPaid';
                    }
                }
                //die();
            }
            if (!empty($invdata)) {
                foreach ($invdata as $ind) {
                    
                    $totAmt = number_format($ind['finalamount'], 2, '.', '');
                    $totAmt_with_dis = number_format($ind['finalamount_with_des'], 2, '.', '');
                    $totAmtdisAmt = number_format($ind['totamtdisamount'], 2, '.', '');
                    $totDisAmt = number_format($ind['disamount'], 2, '.', '');
                    $sqlp = 'SELECT id FROM fn_fee_waive_off WHERE invoice_no = "'.$ind['stu_invoice_no'].'" ';
                    $resultp = $connection2->query($sqlp);
                    $wfdata = $resultp->fetch();
                    $dsc = '';
                    if(!empty($wfdata)){
                        $dsc = '(WF)';
                    }

                    if(!empty($ind['sdDis'])){
                        $dsc = '(SD)';
                    }

                    if(!empty($ind['sdDis']) && !empty($wfdata)){
                        $dsc = '(SD,WF)';
                    }

                    if ($ind['chkpayment'] == 'Paid') {
                        //$cls = 'value="0" checked disabled';
                        echo '<tr><td>' . $ind['stu_invoice_no'] . '</td><td>' . $ind['title'] . '</td><td>' . $totAmtdisAmt . '</td><td>' . $totDisAmt .' '.$dsc. '</td><td>' . $totAmt_with_dis . '</td><td>' . number_format($ind['pendingamount'], 2) . '</td></tr>';
                    } else {
                        $cls = 'value="' . $ind['invoiceid'] . '"';
                        echo '<tr><td>' . $ind['stu_invoice_no'] . '</td><td>' . $ind['title'] . '</td><td>' . $totAmtdisAmt . '</td><td>' . $totDisAmt .' '.$dsc.  '</td><td>' . $totAmt_with_dis . '</td><td>' . number_format($ind['pendingamount'], 2) . '</td></tr>';
                    }
                }
            } else {
                echo "<tr><td colspan='4'>No invoices found</td></tr>";
            }
        echo "</tbody>";
        echo '</table>';

        // echo '<pre>';
        // print_r($invdata);
        // echo '</pre>';
        // die();

        /*
        echo "<div class ='row fee_hdr FeeInvoiceListManage'><div class='col-md-12'> Invoices</div></div>";

        echo "<table class='table' cellspacing='0' style='width: 100%' id='FeeInvoiceListManage'>";
        echo "<thead>";
        echo "<tr class='head'>";
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
        echo "</thead>";
        echo "<tbody id='getInvoiceFeeItem'>";
        if(!empty($invdata)){
            foreach($invdata as $ind){
                echo '<tr><td>'.$ind['stu_invoice_no'].'</td><td>'.$ind['title'].'</td><td>'.$ind['finalamount'].'</td><td>'.$ind['pendingamount'].'</td></tr>';
            }
        }
        echo "</tbody>";
        echo '</table>';
        */

    }    
}
echo "<style>
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
</style>";