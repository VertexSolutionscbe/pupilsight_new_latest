<?php
/*
Pupilsight, Flexible & Open School System
*/

include '../../pupilsight.php';

$session = $container->get('session');

$URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_POST['address']).'/fee_collection_manage.php';

if (isActionAccessible($guid, $connection2, '/modules/Finance/fee_collection_manage.php') == false) {
    $URL .= '&return=error0';
    header("Location: {$URL}");
} else {
    $counterid = $session->get('counterid');
    $invoice_id = $_POST['invoice_id'];
    $invoice_item_id = $_POST['invoice_item_id'];
    $fn_fees_invoice_id = $_POST['invoice_id'];
    $pupilsightPersonID = $_POST['pupilsightPersonID'];
    $pupilsightSchoolYearID = $_POST['pupilsightSchoolYearID'];
    $reference_no=$_POST['reference_no'];

     if(!empty($_POST['reference_date'])){
        $fd = explode('/', $_POST['reference_date']);
        $reference_date  = date('Y-m-d', strtotime(implode('-', array_reverse($fd))));
    } else {
        $reference_date  = '';
    }
    
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

    
    
    if ($pupilsightPersonID == '' or $transcation_amount == '') {
        $URL .= '&return=error1';
        header("Location: {$URL}");
    } else {
        //Check unique inputs for uniquness
        /*
        try {
            $data = array('fn_fees_invoice_id' => $fn_fees_invoice_id, 'pupilsightPersonID' => $pupilsightPersonID);
            $sql = 'SELECT * FROM fn_fees_collection WHERE fn_fees_invoice_id=:fn_fees_invoice_id AND pupilsightPersonID=:pupilsightPersonID';
            $result = $connection2->prepare($sql);
            $result->execute($data);
        } catch (PDOException $e) {
            $URL .= '&return=error2';
            header("Location: {$URL}");
            exit();
        }

        print_r($result);
        print_r($result->rowCount());
        echo "\n<br>pupilsightPersonID: ".$pupilsightPersonID." fn_fees_invoice_id: ".$fn_fees_invoice_id;
        

        if ($result->rowCount() > 0) {
            $URL .= '&return=error3';
            header("Location: {$URL}");
        } else {*/
            //Write to database
            try {
                if(!empty($fn_fees_receipt_series_id)){
                    $sqlrec = 'SELECT id, formatval FROM fn_fee_series WHERE id = "'.$fn_fees_receipt_series_id.'" ';
                    $resultrec = $connection2->query($sqlrec);
                    $recptser = $resultrec->fetch();
            
                    //$invformat = explode('/',$recptser['format']);
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
                            //$iformat .= $formatvalues['last_no'].'/';
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
                    
                   //$receipt_number = rtrim($iformat, "/");;
                   $receipt_number = $iformat;
                } else {
                    if(!empty($_POST['receipt_number'])){
                        $receipt_number = $_POST['receipt_number'];
                    } else {
                        $receipt_number = '';
                    }
                    
                }
                
                $data = array('fn_fees_invoice_id' => $fn_fees_invoice_id, 'pupilsightPersonID' => $pupilsightPersonID, 'pupilsightSchoolYearID' => $pupilsightSchoolYearID, 'fn_fees_counter_id' =>$counterid, 'receipt_number' => $receipt_number, 'is_custom' => $is_custom, 'payment_mode_id' => $payment_mode_id, 'bank_id' => $bank_id, 'dd_cheque_no' => $dd_cheque_no, 'dd_cheque_date' => $dd_cheque_date, 'dd_cheque_amount' => $dd_cheque_amount, 'payment_status' => $payment_status, 'payment_date' => $payment_date, 'fn_fees_head_id' => $fn_fees_head_id, 'fn_fees_receipt_series_id' => $fn_fees_receipt_series_id, 'transcation_amount' => $transcation_amount, 'total_amount_without_fine_discount' => $total_amount_without_fine_discount, 'amount_paying' => $amount_paying, 'fine' => $fine, 'discount' =>$discount, 'remarks' => $remarks, 'status' => $status, 'cdt' => $cdt,'reference_no'=>$reference_no,'reference_date'=>$reference_date);
                
                $sql = 'INSERT INTO fn_fees_collection SET fn_fees_invoice_id=:fn_fees_invoice_id, pupilsightPersonID=:pupilsightPersonID, pupilsightSchoolYearID =:pupilsightSchoolYearID, fn_fees_counter_id=:fn_fees_counter_id, receipt_number=:receipt_number, is_custom=:is_custom, payment_mode_id=:payment_mode_id, bank_id=:bank_id, dd_cheque_no=:dd_cheque_no, dd_cheque_date=:dd_cheque_date, dd_cheque_amount=:dd_cheque_amount, payment_status=:payment_status, payment_date=:payment_date, fn_fees_head_id=:fn_fees_head_id, fn_fees_receipt_series_id=:fn_fees_receipt_series_id, transcation_amount=:transcation_amount, total_amount_without_fine_discount=:total_amount_without_fine_discount, amount_paying=:amount_paying, fine=:fine, discount=:discount, remarks=:remarks, status=:status,cdt=:cdt,reference_no=:reference_no,reference_date=:reference_date';
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
                    foreach($itemId as $itid){     
                        $dataf = array('id' => $itid, 'pupilsightPersonID'=>$pupilsightPersonID);
                        $sqlf = 'SELECT a.fn_fee_invoice_id,b.invoice_no FROM fn_fee_invoice_item AS a LEFT JOIN fn_fee_invoice_student_assign AS b ON a.fn_fee_invoice_id = b.fn_fee_invoice_id WHERE a.id=:id AND b.pupilsightPersonID=:pupilsightPersonID';
                        $resultf = $connection2->prepare($sqlf);
                        $resultf->execute($dataf);
                        $values = $resultf->fetch();
                        $fn_fee_invoice_id = $values['fn_fee_invoice_id'];
                        $invoice_no = $values['invoice_no'];

                        $datai = array('pupilsightPersonID'=>$pupilsightPersonID,'transaction_id' => $transactionId,  'fn_fees_invoice_id' => $fn_fee_invoice_id, 'fn_fee_invoice_item_id' => $itid, 'invoice_no' => $invoice_no);
                        $sqli = 'INSERT INTO fn_fees_student_collection SET pupilsightPersonID=:pupilsightPersonID, transaction_id=:transaction_id, fn_fees_invoice_id=:fn_fees_invoice_id, fn_fee_invoice_item_id=:fn_fee_invoice_item_id, invoice_no=:invoice_no';
                        $resulti = $connection2->prepare($sqli);
                        $resulti->execute($datai);
                    }
                }


                if(!empty($deposit)){
                    $datad = array('pupilsightPersonID'=>$pupilsightPersonID,'pupilsightSchoolYearID' => $pupilsightSchoolYearID,  'deposit' => $deposit, 'cdt' => $cdt);
                    $sqld = 'INSERT INTO fn_fees_collection_deposit SET pupilsightPersonID=:pupilsightPersonID, pupilsightSchoolYearID=:pupilsightSchoolYearID, deposit=:deposit, cdt=:cdt';
                    $resultd = $connection2->prepare($sqld);
                    $resultd->execute($datad);
                }


                $sqlstu = "SELECT a.officialName , b.name as class, c.name as section FROM pupilsightPerson AS a LEFT JOIN pupilsightStudentEnrolment AS d ON a.pupilsightPersonID = d.pupilsightPersonID LEFT JOIN pupilsightYearGroup AS b ON d.pupilsightYearGroupID = b.pupilsightYearGroupID LEFT JOIN pupilsightRollGroup AS c ON d.pupilsightRollGroupID = c.pupilsightRollGroupID WHERE a.pupilsightPersonID = ".$pupilsightPersonID." ";
                $resultstu = $connection2->query($sqlstu);
                $valuestu = $resultstu->fetch();

                $sqlpt = "SELECT name FROM fn_masters WHERE id = ".$payment_mode_id." ";
                $resultpt = $connection2->query($sqlpt);
                $valuept = $resultpt->fetch();

                $class_section = $valuestu["class"] ." ".$valuestu["section"];
                $dts_receipt = array(
                    "receipt_no" => $receipt_number,
                    "date" => date("d-M-Y"),
                    "student_name" => $valuestu["officialName"],
                    "student_id" => $pupilsightPersonID,
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
                    $callback = $_SESSION[$guid]['absoluteURL'].'/thirdparty/phpword/receiptNew.php';
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