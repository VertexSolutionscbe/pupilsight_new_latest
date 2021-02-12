<?php
/*
Pupilsight, Flexible & Open School System
*/

include '../../pupilsight.php';

$session = $container->get('session');

$cid = $_POST['cid'];
$URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/Campaign/campaignFormList.php&id='.$cid;

if (isActionAccessible($guid, $connection2, '/modules/Campaign/fee_make_payment.php') == false) {
    $URL .= '&return=error0';
    header("Location: {$URL}");
} else {
    //Proceed!
    //Validate Inputs
    // echo '<pre>';
    // print_r($_POST);
    // echo '</pre>';
    // die();
    $counterid = $session->get('counterid');
    $invoice_id = $_POST['invoice_id'];
    $invoice_item_id = $_POST['invoice_item_id'];
    $fn_fees_invoice_id = '';
    $submission_id = $_POST['submission_id'];
    $pupilsightSchoolYearID = $_POST['pupilsightSchoolYearID'];
    
    if(!empty($_POST['is_custom'])){
        $is_custom = $_POST['is_custom'];
    } else {
        $is_custom = '';
    }
    
    $payment_mode_id = $_POST['payment_mode_id'];
    $bank_id = $_POST['bank_id'];
    $dd_cheque_no = $_POST['dd_cheque_no'];
    if(!empty($_POST['dd_cheque_date'])){
        $fd = explode('/', $_POST['dd_cheque_date']);
        $dd_cheque_date  = date('Y-m-d', strtotime(implode('-', array_reverse($fd))));
    } else {
        $dd_cheque_date  = '';
    }
    $dd_cheque_amount = $_POST['dd_cheque_amount'];
    $payment_status = $_POST['payment_status'];
    if(!empty($_POST['payment_date'])){
        $pd = explode('/', $_POST['payment_date']);
        $payment_date  = date('Y-m-d', strtotime(implode('-', array_reverse($pd))));
    } else {
        $payment_date  = '';
    }
    
    $fn_fees_head_id = $_POST['fn_fees_head_id'];
    $fn_fees_receipt_series_id = $_POST['fn_fees_receipt_series_id'];
    
    
    $transcation_amount = $_POST['transcation_amount'];
    $amount_paying = $_POST['amount_paying'];
    $total_amount_without_fine_discount = $_POST['total_amount_without_fine_discount'];
    if($amount_paying > $transcation_amount){
        $deposit = $amount_paying - $transcation_amount;
    } else {
        $deposit = '';
    }
    $fine = $_POST['fine'];
    $discount = $_POST['discount'];
    $remarks = $_POST['remarks'];
    $status = '1';
    $cdt = date('Y-m-d H:i:s');
    $cuid = $_SESSION[$guid]['pupilsightPersonID'];

    
    
    if ($submission_id == '' or $transcation_amount == '') {
        $URL .= '&return=error1';
        header("Location: {$URL}");
    } else {
       
            //Write to database
            try {
                if(!empty($fn_fees_receipt_series_id)){
                    $sqlrec = 'SELECT id, formatval FROM fn_fee_series WHERE id = "'.$fn_fees_receipt_series_id.'" ';
                    $resultrec = $connection2->query($sqlrec);
                    $recptser = $resultrec->fetch();
            
                    // $invformat = explode('/',$recptser['format']);
                    $invformat = explode('$',$recptser['formatval']);
                    $iformat = '';
                    $orderwise = 0;
                    foreach($invformat as $inv){
                        if($inv == '{AB}'){
                            $datafort = array('fn_fee_series_id'=>$fn_fees_receipt_series_id,'order_wise' => $orderwise, 'type' => 'numberwise');
                            $sqlfort = 'SELECT id, no_of_digit, last_no FROM fn_fee_series_number_format WHERE fn_fee_series_id=:fn_fee_series_id AND order_wise=:order_wise AND type=:type';
                            $resultfort = $connection2->prepare($sqlfort);
                            $resultfort->execute($datafort);
                            $formatvalues = $resultfort->fetch();
                            // $iformat .= $formatvalues['last_no'].'/';
                            $iformat .= $formatvalues['last_no'];
                            
                            $str_length = $formatvalues['no_of_digit'];
            
                            $lastnoadd = $formatvalues['last_no'] + 1;
            
                            $lastno = substr("0000000{$lastnoadd}", -$str_length); 
            
                            $datafort1 = array('fn_fee_series_id'=>$fn_fees_receipt_series_id,'order_wise' => $orderwise, 'type' => 'numberwise' , 'last_no' => $lastno);
                            $sqlfort1 = 'UPDATE fn_fee_series_number_format SET last_no=:last_no WHERE fn_fee_series_id=:fn_fee_series_id AND type=:type AND order_wise=:order_wise';
                            $resultfort1 = $connection2->prepare($sqlfort1);
                            $resultfort1->execute($datafort1);
            
                        } else {
                            //$iformat .= $inv.'/';
                            $iformat .= $inv;
                        }
                        $orderwise++;
                    }
                    
                //    $receipt_number = rtrim($iformat, "/");;
                    $receipt_number = $iformat;
                } else {
                    $receipt_number = $_POST['receipt_number'];
                }
                
                $data = array('fn_fees_invoice_id' => $fn_fees_invoice_id, 'submission_id' => $submission_id, 'pupilsightSchoolYearID' => $pupilsightSchoolYearID, 'fn_fees_counter_id' =>$counterid, 'receipt_number' => $receipt_number, 'is_custom' => $is_custom, 'payment_mode_id' => $payment_mode_id, 'bank_id' => $bank_id, 'dd_cheque_no' => $dd_cheque_no, 'dd_cheque_date' => $dd_cheque_date, 'dd_cheque_amount' => $dd_cheque_amount, 'payment_status' => $payment_status, 'payment_date' => $payment_date, 'fn_fees_head_id' => $fn_fees_head_id, 'fn_fees_receipt_series_id' => $fn_fees_receipt_series_id, 'transcation_amount' => $transcation_amount, 'total_amount_without_fine_discount' => $total_amount_without_fine_discount, 'amount_paying' => $amount_paying, 'fine' => $fine, 'discount' =>$discount, 'remarks' => $remarks, 'status' => $status, 'cdt' => $cdt);
                
                $sql = 'INSERT INTO fn_fees_collection SET fn_fees_invoice_id=:fn_fees_invoice_id, submission_id=:submission_id, pupilsightSchoolYearID =:pupilsightSchoolYearID, fn_fees_counter_id=:fn_fees_counter_id, receipt_number=:receipt_number, is_custom=:is_custom, payment_mode_id=:payment_mode_id, bank_id=:bank_id, dd_cheque_no=:dd_cheque_no, dd_cheque_date=:dd_cheque_date, dd_cheque_amount=:dd_cheque_amount, payment_status=:payment_status, payment_date=:payment_date, fn_fees_head_id=:fn_fees_head_id, fn_fees_receipt_series_id=:fn_fees_receipt_series_id, transcation_amount=:transcation_amount, total_amount_without_fine_discount=:total_amount_without_fine_discount, amount_paying=:amount_paying, fine=:fine, discount=:discount, remarks=:remarks, status=:status,cdt=:cdt';
                $result = $connection2->prepare($sql);
                $result->execute($data);
                
                $collectionId = $connection2->lastInsertID();
                
                $rand = mt_rand(10,99);    
                $t=time();
                $transactionId = $t.$rand;

                $datau = array('transaction_id' => $transactionId,  'id' => $collectionId);
                $sqlu = 'UPDATE fn_fees_collection SET transaction_id=:transaction_id WHERE id=:id';
                $resultu = $connection2->prepare($sqlu);
                $resultu->execute($datau);

                

                if(!empty($invoice_item_id)){
                    $itemId = explode(', ', $invoice_item_id);
                    $state_id = '';
                    foreach($itemId as $itid){     
                        $dataf = array('id' => $itid, 'submission_id'=>$submission_id);
                        $sqlf = 'SELECT a.fn_fee_invoice_id,b.invoice_no, b.state_id FROM fn_fee_invoice_item AS a LEFT JOIN fn_fee_invoice_applicant_assign AS b ON a.fn_fee_invoice_id = b.fn_fee_invoice_id   WHERE a.id=:id AND b.submission_id=:submission_id';
                        $resultf = $connection2->prepare($sqlf);
                        $resultf->execute($dataf);
                        $values = $resultf->fetch();
                        $fn_fee_invoice_id = $values['fn_fee_invoice_id'];
                        $invoice_no = $values['invoice_no'];
                        $state_id = $values['state_id'];

                        $datai = array('submission_id'=>$submission_id,'transaction_id' => $transactionId,  'fn_fees_invoice_id' => $fn_fee_invoice_id, 'fn_fee_invoice_item_id' => $itid, 'invoice_no' => $invoice_no);
                        $sqli = 'INSERT INTO fn_fees_applicant_collection SET submission_id=:submission_id, transaction_id=:transaction_id, fn_fees_invoice_id=:fn_fees_invoice_id, fn_fee_invoice_item_id=:fn_fee_invoice_item_id, invoice_no=:invoice_no';
                        $resulti = $connection2->prepare($sqli);
                        $resulti->execute($datai);
 
                    }
                }

                if(!empty($state_id)){
                    // $sqlchk1 = "SELECT b.state_id FROM workflow_transition AS a LEFT JOIN fn_fee_admission_settings AS b ON a.fn_fee_admission_setting_ids = b.id WHERE a.id = ".$state_id." ";
                    // $resultchk1 = $connection2->query($sqlchk1);
                    // $valuechk1 = $resultchk1->fetch();

                    $sqlchk1 = "SELECT fn_fee_admission_setting_ids FROM workflow_transition  WHERE id = ".$state_id." ";
                    $resultchk1 = $connection2->query($sqlchk1);
                    $getSettIds = $resultchk1->fetch();

                    if(!empty($getSettIds)){
                        $sqlchk1 = "SELECT state_id FROM fn_fee_admission_settings WHERE id IN (".$getSettIds['fn_fee_admission_setting_ids'].") ";
                        $resultchk1 = $connection2->query($sqlchk1);
                        $valuechk1 = $resultchk1->fetch();
                        

                        $sqlchk = "SELECT a.campaign_id, a.transition_display_name, a.id, c.form_id FROM workflow_transition AS a LEFT JOIN campaign AS c ON a.campaign_id = c.id WHERE a.to_state = ".$valuechk1['state_id']." ";
                        $resultchk = $connection2->query($sqlchk);
                        $valuechk = $resultchk->fetch();
                        if(!empty($valuechk)){
                            $chgState_id = $valuechk['id'];
                            $data = array('campaign_id' => $valuechk['campaign_id'],'form_id' => $valuechk['form_id'], 'submission_id' => $submission_id, 'state' => $valuechk['transition_display_name'],  'state_id' => $chgState_id, 'status' => '1', 'pupilsightPersonID' => $cuid, 'cdt' => $cdt);
                    
                            $sql = "INSERT INTO campaign_form_status SET campaign_id=:campaign_id,form_id=:form_id, submission_id=:submission_id,state=:state,state_id=:state_id, status=:status, pupilsightPersonID=:pupilsightPersonID, cdt=:cdt";
                            $result = $connection2->prepare($sql);
                            $result->execute($data);
                        }
                    }
                }

                $sqlstu = "SELECT  b.name as prog,c.name as class FROM wp_fluentform_submissions AS a LEFT JOIN pupilsightProgram AS b ON a.pupilsightProgramID = b.pupilsightProgramID LEFT JOIN pupilsightYearGroup AS c ON a.pupilsightYearGroupID = c.pupilsightYearGroupID WHERE a.id = ".$submission_id." ";
                $resultstu = $connection2->query($sqlstu);
                $valuestu = $resultstu->fetch();

                $sqlpt = "SELECT name FROM fn_masters WHERE id = ".$payment_mode_id." ";
                $resultpt = $connection2->query($sqlpt);
                $valuept = $resultpt->fetch();

                $sqlstu = 'SELECT field_value FROM wp_fluentform_entry_details WHERE submission_id = "'.$submission_id.'" AND sub_field_name = "first_name" ';
                $resultstu = $connection2->query($sqlstu);
                $studetails = $resultstu->fetch();

                $class_section = $valuestu["prog"].' - '.$valuestu["class"];
                $dts_receipt = array(
                    "receipt_no" => $receipt_number,
                    "date" => date("d-M-Y"),
                    "student_name" => $studetails['field_value'],
                    "student_id" => $submission_id,
                    "class_section" => $class_section,
                    "instrument_date" => "NA",
                    "instrument_no" => "NA",
                    "transcation_amount" => $amount_paying,
                    "fine_amount" => $fine,
                    "other_amount" => "NA",
                    "pay_mode" => $valuept['name'],
                    "transactionId" => $transactionId
                );
                
                if(!empty($invoice_id)){
                    $invid = explode(',', $invoice_id);
                    foreach($invid as $iid){
                        $chsql = 'SELECT b.invoice_title, b.display_fee_item FROM fn_fee_invoice AS a LEFT JOIN fn_fee_structure AS b ON a.fn_fee_structure_id = b.id WHERE a.id= '.$iid.' AND a.fn_fee_structure_id IS NOT NULL ';
                        $resultch = $connection2->query($chsql);
                        $valuech = $resultch->fetch();
                        if($valuech['display_fee_item'] == '2'){
                            $sqcs = "select SUM(fi.total_amount) AS tamnt from fn_fee_invoice_item as fi, fn_fee_items as items where fi.fn_fee_item_id = items.id and fi.id in(".$invoice_item_id.")";
                            $resultfi = $connection2->query($sqcs);
                            $valuefi = $resultfi->fetchAll();
                            if (!empty($valuefi)) {
                                $cnt = 1;
                                foreach($valuefi as $vfi){
                                    $dts_receipt_feeitem[] = array(
                                        "serial.all"=>$cnt,
                                        "particulars.all"=>$valuech['invoice_title'],
                                        "amount.all"=>$vfi["tamnt"]
                                    );
                                    $cnt ++;
                                }
                            }

                        } else {
                            $sqcs = "select fi.total_amount, items.name from fn_fee_invoice_item as fi, fn_fee_items as items where fi.fn_fee_item_id = items.id and fi.id in(".$invoice_item_id.")";
                            $resultfi = $connection2->query($sqcs);
                            $valuefi = $resultfi->fetchAll();
                            if (!empty($valuefi)) {
                                $cnt = 1;
                                foreach($valuefi as $vfi){
                                    $dts_receipt_feeitem[] = array(
                                        "serial.all"=>$cnt,
                                        "particulars.all"=>$vfi["name"],
                                        "amount.all"=>$vfi["total_amount"]
                                    );
                                    $cnt ++;
                                }
                            }
                        }
                    }
                }
                
               
        
                $_SESSION["dts_receipt_feeitem"] = $dts_receipt_feeitem;
                $_SESSION["dts_receipt"] = $dts_receipt;
                $URL .= "&return=success0";
                $_SESSION["admin_callback"] = $URL;
                if(!empty($dts_receipt) && !empty($dts_receipt_feeitem)){ 
                    $callback = $_SESSION[$guid]['absoluteURL'].'/thirdparty/phpword/receipt.php';
                    header('Location: '.$callback);
                }  

            } catch (PDOException $e) {
                $URL .= '&return=error9';
                header("Location: {$URL}");
                exit();
            }

            //Last insert ID
            // $AI = str_pad($connection2->lastInsertID(), 3, '0', STR_PAD_LEFT);

            // $URL .= "&return=success0";
            // header("Location: {$URL}");
        //}
    }
}