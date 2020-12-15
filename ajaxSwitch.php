<?php 
/*
Pupilsight, Flexible & Open School System
*/
include 'pupilsight.php';
$session = $container->get('session');
if(isset($_POST['type'])){
    $type=trim($_POST['type']);
    switch ($type) {
    case "cancel_receipt_request":
        $remarks = $_POST['remarks'];
        $trans_id = explode(',', $_POST['trans_id']);
        $cuid = $_SESSION[$guid]['pupilsightPersonID'];
        $cdt = date('Y-m-d H:i:s');

        if ($remarks == ''  or $trans_id == '') {
          echo " Please enter remarks";
        } else {
        //Check unique inputs for uniquness

        //Write to database
        try {
        foreach($trans_id as $ts){
            unset($dts_receipt_feeitem);
            unset($dts_receipt);

            $data = array('remarks' => $remarks, 'fn_fees_collection_id' => $ts, 'canceled_by' => $cuid, 'cdt' => $cdt);
            $sql = 'INSERT INTO fn_fees_cancel_collection SET remarks=:remarks, fn_fees_collection_id=:fn_fees_collection_id, canceled_by=:canceled_by, cdt=:cdt';
            $result = $connection2->prepare($sql);
            $result->execute($data);

            $datau = array('transaction_status' => '2', 'id' => $ts);
            $sqlu = 'UPDATE fn_fees_collection SET transaction_status=:transaction_status WHERE id=:id';
            $resultu = $connection2->prepare($sqlu);
            $resultu->execute($datau);

            $collectionId = $ts;
            
        //    $sqlstu = "SELECT e.*, a.officialName , a.admission_no,  b.name as class, c.name as section, GROUP_CONCAT(DISTINCT f.fn_fees_invoice_id) as invoice_id, GROUP_CONCAT(f.fn_fee_invoice_item_id) as invoice_item_id, fm.name as bank_name FROM fn_fees_collection AS e LEFT JOIN pupilsightPerson AS a ON e.pupilsightPersonID = a.pupilsightPersonID LEFT JOIN pupilsightStudentEnrolment AS d ON e.pupilsightPersonID = d.pupilsightPersonID LEFT JOIN pupilsightYearGroup AS b ON d.pupilsightYearGroupID = b.pupilsightYearGroupID LEFT JOIN pupilsightRollGroup AS c ON d.pupilsightRollGroupID = c.pupilsightRollGroupID LEFT JOIN fn_fees_student_collection AS f ON e.transaction_id = f.transaction_id LEFT JOIN fn_masters AS fm ON e.bank_id = fm.id WHERE e.id = ".$collectionId." GROUP BY e.id ";
        //     $resultstu = $connection2->query($sqlstu);
        //     $valuestu = $resultstu->fetch(); 

            // $sqlpt = "SELECT name FROM fn_masters WHERE id = ".$valuestu['payment_mode_id']." ";
            // $resultpt = $connection2->query($sqlpt);
            // $valuept = $resultpt->fetch();

            // $class_section = $valuestu["class"] ." ".$valuestu["section"];
            // if(!empty($valuestu['admission_no'])){
            //     $admissionNo = $valuestu['admission_no'];
            // } else {
            //     $admissionNo = $valuestu['pupilsightPersonID'];
            // }
            // $dts_receipt = array(
            //     "receipt_no" => $valuestu['receipt_number'],
            //     "date" => date("d-M-Y"),
            //     "student_name" => $valuestu["officialName"],
            //     "student_id" => $admissionNo,
            //     "class_section" => $class_section,
            //     "bank_name" => $valuestu["bank_name"],
            //     "instrument_date" => "NA",
            //     "instrument_no" => "NA",
            //     "transcation_amount" => $valuestu['amount_paying'],
            //     "fine_amount" => $valuestu['fine'],
            //     "other_amount" => "NA",
            //     "pay_mode" => $valuept['name'],
            //     "transactionId" => $valuestu['transaction_id'],
            //     "reason" => $remarks
            // );
            // //$_SESSION['doc_receipt_id']=$valuestu['transaction_id'];
            // $invoice_id = $valuestu["invoice_id"];
            // $invoice_item_id = $valuestu["invoice_item_id"];
            // if(!empty($invoice_id)){
            //     $invid = explode(',', $invoice_id);
            //     foreach($invid as $iid){
            //         $chsql = 'SELECT b.invoice_title, b.display_fee_item FROM fn_fee_invoice AS a LEFT JOIN fn_fee_structure AS b ON a.fn_fee_structure_id = b.id WHERE a.id= '.$iid.' AND a.fn_fee_structure_id IS NOT NULL ';
            //         $resultch = $connection2->query($chsql);
            //         $valuech = $resultch->fetch();
            //         if($valuech['display_fee_item'] == '2'){
            //             $sqcs = "select SUM(fi.total_amount) AS tamnt from fn_fee_invoice_item as fi, fn_fee_items as items where fi.fn_fee_item_id = items.id and fi.id in(".$invoice_item_id.")";
            //             $resultfi = $connection2->query($sqcs);
            //             $valuefi = $resultfi->fetchAll();
            //             if (!empty($valuefi)) {
            //                 $cnt = 1;
            //                 foreach($valuefi as $vfi){
            //                     $dts_receipt_feeitem[] = array(
            //                         "serial.all"=>$cnt,
            //                         "particulars.all"=>$valuech['invoice_title'],
            //                         "amount.all"=>$vfi["tamnt"]
            //                     );
            //                     $cnt ++;
            //                 }
            //             }

            //         } else {
            //             $sqcs = "select fi.id,fi.total_amount, items.name from fn_fee_invoice_item as fi, fn_fee_items as items where fi.fn_fee_item_id = items.id and fi.id in(".$invoice_item_id.")";
            //             $resultfi = $connection2->query($sqcs);
            //             $valuefi = $resultfi->fetchAll();
            //             if (!empty($valuefi)) {
            //                 $cnt = 1;
            //                 foreach($valuefi as $vfi){
            //                      $sql_sp_item_dis="SELECT discount FROM `fn_fee_item_level_discount` WHERE  item_id='".$vfi['id']."'";
            //                      $sp_item_dis = $connection2->query($sql_sp_item_dis);
            //                      $sp_item_val = $sp_item_dis->fetch();
            //                      $item_amount= $vfi["total_amount"];
            //                      if(!empty($sp_item_val['discount'])){
            //                       $item_amount=$item_amount-$sp_item_val['discount'];
            //                      }
            //                     $dts_receipt_feeitem[] = array(
            //                         "serial.all"=>$cnt,
            //                         "particulars.all"=>$vfi["name"],
            //                         "amount.all"=>$item_amount
            //                     );
            //                     $cnt ++;
            //                 }
            //             }
            //         }
            //     }
            // }
            // if(!empty($dts_receipt) && !empty($dts_receipt_feeitem)){ 
            //     $callback = $_SESSION[$guid]['absoluteURL'].'/thirdparty/phpword/cancel_receipt.php';
            //     $datamerge = array_merge($dts_receipt, $dts_receipt_feeitem);
            //     $postdata = http_build_query(
            //         array(
            //             'dts_receipt' => $dts_receipt,
            //             'dts_receipt_feeitem' => $dts_receipt_feeitem
            //         )
            //     );
                
            //     $opts = array('http' =>
            //         array(
            //             'method'  => 'POST',
            //             'header'  => 'Content-Type: application/x-www-form-urlencoded',
            //             'content' => $postdata
            //         )
            //     );
            //     $context  = stream_context_create($opts);
            //     $result = file_get_contents($callback, false, $context);
            // } 
        }

        } catch (PDOException $e) {
        //$URL .= '&return=error2';
        echo "Error".$e;
        exit();
        }
        echo "success";
        }
      break;
      case "collectionForm_request":
        $checkmode = $_POST['checkmode'];
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
    if(!empty($bank_id)){
        $sqlbn = 'SELECT name FROM fn_masters WHERE id = '.$bank_id.' ';
        $resultbn = $connection2->query($sqlbn);
        $bankNameData = $resultbn->fetch();
        $bank_name = $bankNameData['name'];
    } else {
        $bank_name = '';
    }

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

    $instrument_no = $_POST['dd_cheque_no'];
    if(!empty($_POST['dd_cheque_date'])){
        $insd = explode('/', $_POST['dd_cheque_date']);
        $instrument_date  = date('Y-m-d', strtotime(implode('-', array_reverse($insd))));
    } else {
        $instrument_date  = '';
    }
    
    $fn_fees_head_id = $_POST['fn_fees_head_id'];
    $sqlrt = 'SELECT b.path FROM fn_fees_head AS a LEFT JOIN fn_fees_receipt_template_master AS b ON a.receipt_template = b.id WHERE a.id = '.$fn_fees_head_id.' ';
    $resultrt = $connection2->query($sqlrt);
    $recTempData = $resultrt->fetch();
    $receiptTemplate = $recTempData['path'];




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
    $invoice_status = $_POST['invoice_status'];

    
    
    if ($pupilsightPersonID == '' or $transcation_amount == '') {
        //$URL .= '&return=error1';
        echo "Something went wrong";
        //header("Location: {$URL}");
    } else {
        
            try {
                
                if(!empty($_POST['receipt_number'])){
                    $receipt_number = $_POST['receipt_number'];
                } else {
                    if(!empty($fn_fees_receipt_series_id)){
                        $sqlrec = 'SELECT id, formatval FROM fn_fee_series WHERE id = "'.$fn_fees_receipt_series_id.'" ';
                        $resultrec = $connection2->query($sqlrec);
                        $recptser = $resultrec->fetch();
                
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
                        
                        //    $receipt_number = rtrim($iformat, "/");;
                        $receipt_number = $iformat;
                    } else {
                        $receipt_number = '';
                    }
                }
                
                // echo $receipt_number;
                // die();
                
                $data = array('fn_fees_invoice_id' => $fn_fees_invoice_id, 'pupilsightPersonID' => $pupilsightPersonID, 'pupilsightSchoolYearID' => $pupilsightSchoolYearID, 'fn_fees_counter_id' =>$counterid, 'receipt_number' => $receipt_number, 'is_custom' => $is_custom, 'payment_mode_id' => $payment_mode_id, 'bank_id' => $bank_id, 'dd_cheque_no' => $dd_cheque_no, 'dd_cheque_date' => $dd_cheque_date, 'dd_cheque_amount' => $dd_cheque_amount, 'payment_status' => $payment_status, 'payment_date' => $payment_date, 'fn_fees_head_id' => $fn_fees_head_id, 'fn_fees_receipt_series_id' => $fn_fees_receipt_series_id, 'transcation_amount' => $transcation_amount, 'total_amount_without_fine_discount' => $total_amount_without_fine_discount, 'amount_paying' => $amount_paying, 'fine' => $fine, 'discount' =>$discount, 'remarks' => $remarks, 'status' => $status, 'cdt' => $cdt,'reference_no'=>$reference_no,'reference_date'=>$reference_date,'instrument_no'=>$instrument_no,'instrument_date'=>$instrument_date,'invoice_status'=>$invoice_status);
                // echo '<pre>';
                // print_r($data);
                // echo '</pre>';
                
                $sql = 'INSERT INTO fn_fees_collection SET fn_fees_invoice_id=:fn_fees_invoice_id, pupilsightPersonID=:pupilsightPersonID, pupilsightSchoolYearID =:pupilsightSchoolYearID, fn_fees_counter_id=:fn_fees_counter_id, receipt_number=:receipt_number, is_custom=:is_custom, payment_mode_id=:payment_mode_id, bank_id=:bank_id, dd_cheque_no=:dd_cheque_no, dd_cheque_date=:dd_cheque_date, dd_cheque_amount=:dd_cheque_amount, payment_status=:payment_status, payment_date=:payment_date, fn_fees_head_id=:fn_fees_head_id, fn_fees_receipt_series_id=:fn_fees_receipt_series_id, transcation_amount=:transcation_amount, total_amount_without_fine_discount=:total_amount_without_fine_discount, amount_paying=:amount_paying, fine=:fine, discount=:discount, remarks=:remarks, status=:status,cdt=:cdt,reference_no=:reference_no,reference_date=:reference_date,instrument_no=:instrument_no,instrument_date=:instrument_date,invoice_status=:invoice_status';
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

                $datau = array('invoice_status'=>$invoice_status, 'fn_fees_invoice_id' => $fn_fees_invoice_id,  'pupilsightPersonID' => $pupilsightPersonID);
                $sqlu = 'UPDATE fn_fees_collection SET invoice_status=:invoice_status WHERE fn_fees_invoice_id=:fn_fees_invoice_id AND pupilsightPersonID=:pupilsightPersonID';
                $resultu = $connection2->prepare($sqlu);
                $resultu->execute($datau);

                $dataiu = array('invoice_status' => $invoice_status,  'pupilsightPersonID' => $pupilsightPersonID,  'fn_fee_invoice_id' => $fn_fees_invoice_id);
                $sqliu = 'UPDATE fn_fee_invoice_student_assign SET invoice_status=:invoice_status WHERE pupilsightPersonID=:pupilsightPersonID AND fn_fee_invoice_id=:fn_fee_invoice_id';
                $resultiu = $connection2->prepare($sqliu);
                $resultiu->execute($dataiu);
                
                
               

                
                if($amount_paying < $total_amount_without_fine_discount){
                    $isql = 'SELECT a.id, a.fn_fee_invoice_id, a.total_amount,b.invoice_no FROM fn_fee_invoice_item AS a LEFT JOIN fn_fee_invoice_student_assign AS b ON a.fn_fee_invoice_id = b.fn_fee_invoice_id WHERE a.id IN ('.$invoice_item_id.') AND b.pupilsightPersonID = '.$pupilsightPersonID.'  ORDER BY b.id ASC';
                    $resultip = $connection2->query($isql);
                    $valuesip = $resultip->fetchAll();

                    if(!empty($valuesip)){
                        $chkamount = $amount_paying;
                        foreach($valuesip as $itmid){
                            $fn_fee_invoice_id = $itmid['fn_fee_invoice_id'];
                            $invoice_no = $itmid['invoice_no'];
                            $itemamount = $itmid['total_amount'];
                            $itid = $itmid['id'];
                            if($itemamount < $chkamount){
                                $status = '1';
                                $paidamount = $itemamount;
                            } else {
                                $status = '2';
                                $paidamount = $chkamount;
                            }
                                
                                $datai = array('pupilsightPersonID'=>$pupilsightPersonID,'transaction_id' => $transactionId,  'fn_fees_invoice_id' => $fn_fee_invoice_id, 'fn_fee_invoice_item_id' => $itid, 'invoice_no' => $invoice_no, 'total_amount' => $itemamount, 'total_amount_collection' => $paidamount, 'status' => $status);
                                $sqli = 'INSERT INTO fn_fees_student_collection SET pupilsightPersonID=:pupilsightPersonID, transaction_id=:transaction_id, fn_fees_invoice_id=:fn_fees_invoice_id, fn_fee_invoice_item_id=:fn_fee_invoice_item_id, invoice_no=:invoice_no, total_amount=:total_amount, total_amount_collection=:total_amount_collection, status=:status';
                                $resulti = $connection2->prepare($sqli);
                                $resulti->execute($datai);
                                $chkamount = $chkamount - $itemamount;
                            // } else {
                            //     $paidamount = $chkamount;
                            //     $datai = array('pupilsightPersonID'=>$pupilsightPersonID,'transaction_id' => $transactionId,  'fn_fees_invoice_id' => $fn_fee_invoice_id, 'fn_fee_invoice_item_id' => $itid, 'invoice_no' => $invoice_no, 'total_amount' => $itemamount, 'total_amount_collection' => $paidamount, 'status' => '2');
                            //     $sqli = 'INSERT INTO fn_fees_student_collection SET pupilsightPersonID=:pupilsightPersonID, transaction_id=:transaction_id, fn_fees_invoice_id=:fn_fees_invoice_id, fn_fee_invoice_item_id=:fn_fee_invoice_item_id, invoice_no=:invoice_no, total_amount=:total_amount, total_amount_collection=:total_amount_collection, status=:status';
                            //     $resulti = $connection2->prepare($sqli);
                            //     $resulti->execute($datai);
                            //     $chkamount = $chkamount - $itemamount;
                            // }
                        }
                    }
                } else {
                    if(!empty($invoice_item_id)){
                        $itemId = explode(', ', $invoice_item_id);
                        foreach($itemId as $itid){     
                            $dataf = array('id' => $itid, 'pupilsightPersonID'=>$pupilsightPersonID);
                            $sqlf = 'SELECT a.fn_fee_invoice_id,a.total_amount,b.invoice_no FROM fn_fee_invoice_item AS a LEFT JOIN fn_fee_invoice_student_assign AS b ON a.fn_fee_invoice_id = b.fn_fee_invoice_id WHERE a.id=:id AND b.pupilsightPersonID=:pupilsightPersonID';
                            $resultf = $connection2->prepare($sqlf);
                            $resultf->execute($dataf);
                            $values = $resultf->fetch();
                            $fn_fee_invoice_id = $values['fn_fee_invoice_id'];
                            $invoice_no = $values['invoice_no'];
                            $itemamount = $values['total_amount'];

                            $chkpayitem = 'SELECT id FROM fn_fees_student_collection WHERE fn_fees_invoice_id = '.$fn_fee_invoice_id.' AND fn_fee_invoice_item_id = '.$itid.' AND pupilsightPersonID = '.$pupilsightPersonID.' ';
                            $resultcp = $connection2->query($chkpayitem);
                            $valuecp = $resultcp->fetch();

                            if(!empty($valuecp)){
                                $datai = array('partial_transaction_id' => $transactionId,'total_amount_collection' => $itemamount, 'status' => '1', 'id' => $valuecp['id']);
                                $sqli = 'UPDATE fn_fees_student_collection SET partial_transaction_id=:partial_transaction_id, total_amount_collection=:total_amount_collection, status=:status WHERE id=:id';
                                $resulti = $connection2->prepare($sqli);
                                $resulti->execute($datai);
                            } else {
                                $datai = array('pupilsightPersonID'=>$pupilsightPersonID,'transaction_id' => $transactionId,  'fn_fees_invoice_id' => $fn_fee_invoice_id, 'fn_fee_invoice_item_id' => $itid, 'invoice_no' => $invoice_no, 'total_amount' => $itemamount, 'total_amount_collection' => $itemamount, 'status' => '1');
                                $sqli = 'INSERT INTO fn_fees_student_collection SET pupilsightPersonID=:pupilsightPersonID, transaction_id=:transaction_id, fn_fees_invoice_id=:fn_fees_invoice_id, fn_fee_invoice_item_id=:fn_fee_invoice_item_id, invoice_no=:invoice_no, total_amount=:total_amount, total_amount_collection=:total_amount_collection, status=:status';
                                $resulti = $connection2->prepare($sqli);
                                $resulti->execute($datai);
                            }
    
                            
                        }
                    }
                }
                //die();
                


                if(!empty($deposit)){
                    $datad = array('pupilsightPersonID'=>$pupilsightPersonID,'pupilsightSchoolYearID' => $pupilsightSchoolYearID,  'deposit' => $deposit, 'cdt' => $cdt);
                    $sqld = 'INSERT INTO fn_fees_collection_deposit SET pupilsightPersonID=:pupilsightPersonID, pupilsightSchoolYearID=:pupilsightSchoolYearID, deposit=:deposit, cdt=:cdt';
                    $resultd = $connection2->prepare($sqld);
                    $resultd->execute($datad);
                }


                $sqlstu = "SELECT a.officialName , a.admission_no, b.name as class, c.name as section FROM pupilsightPerson AS a LEFT JOIN pupilsightStudentEnrolment AS d ON a.pupilsightPersonID = d.pupilsightPersonID LEFT JOIN pupilsightYearGroup AS b ON d.pupilsightYearGroupID = b.pupilsightYearGroupID LEFT JOIN pupilsightRollGroup AS c ON d.pupilsightRollGroupID = c.pupilsightRollGroupID WHERE a.pupilsightPersonID = ".$pupilsightPersonID." ";
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
                    "student_id" => $valuestu["admission_no"],
                    "class_section" => $class_section,
                    "instrument_date" => $instrument_date,
                    "instrument_no" => $instrument_no,
                    "transcation_amount" => $amount_paying,
                    "fine_amount" => $fine,
                    "other_amount" => "NA",
                    "pay_mode" => $valuept['name'],
                    "transactionId" => $transactionId,
                    "receiptTemplate" => $receiptTemplate,
                    "bank_name" => $bank_name
                );

                // echo '<pre>';
                // print_r($dts_receipt);
                // echo '</pre>';
                // die();
                $session->forget(['doc_receipt_id']);
                $session->set('doc_receipt_id',$transactionId);
                if(!empty($invoice_id)){
                    $invid = explode(',', $invoice_id);
                    foreach($invid as $iid){
                        $chksql = 'SELECT fn_fee_structure_id, display_fee_item, title as invoice_title FROM fn_fee_invoice WHERE id = '.$iid.' ';
                        $resultchk = $connection2->query($chksql);
                        $valuechk = $resultchk->fetch();
                        if($valuechk['fn_fee_structure_id'] == ''){
                            $valuech = $valuechk;
                        } else {
                            $chsql = 'SELECT b.invoice_title, a.display_fee_item FROM fn_fee_invoice AS a LEFT JOIN fn_fee_structure AS b ON a.fn_fee_structure_id = b.id WHERE a.id= '.$iid.' AND a.fn_fee_structure_id IS NOT NULL ';
                            $resultch = $connection2->query($chsql);
                            $valuech = $resultch->fetch();
                        }

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
               
                if($checkmode == 'multiple'){
                    $mdata = $session->get('m_data');
                   // savePaymentModeData($transactionId, $mdata);
                    $t_id = $transactionId;
                    
                    $pmode=$mdata['payment_mode_id'];
                    $mcredit = $mdata['credit_id'];
                    $bank_id = $mdata['bank_id'];
                    $amount = $mdata['amount'];
                    $mrefno = $mdata['reference_no'];
                    $minstruDate = $mdata['instrument_date'];
                    $l=sizeof($pmode);
                    $i=1;
                    for($i=0;$i<$l;$i++){
                        $datam = array('transaction_id'=>$t_id,'payment_mode_id' => $pmode[$i],  'credit_id' => $mcredit[$i], 'bank_id' => $bank_id[$i],    'amount' =>$amount[$i],'reference_no'=>$mrefno[$i],'instrument_date' =>$minstruDate[$i]);
                        $sqlm = 'INSERT INTO fn_multi_payment_mode SET transaction_id=:transaction_id, payment_mode_id=:payment_mode_id, credit_id=:credit_id, bank_id=:bank_id,amount=:amount,reference_no=:reference_no,instrument_date=:instrument_date';
                        $resultm = $connection2->prepare($sqlm);
                        $resultm->execute($datam);
                
                    }
                }
                if(!empty($dts_receipt) && !empty($dts_receipt_feeitem)){ 
                    $callback = $_SESSION[$guid]['absoluteURL'].'/thirdparty/phpword/receiptNew.php';
                    $datamerge = array_merge($dts_receipt, $dts_receipt_feeitem);
                    $postdata = http_build_query(
                    array(
                    'dts_receipt' => $dts_receipt,
                    'dts_receipt_feeitem' => $dts_receipt_feeitem
                    )
                    );

                    $opts = array('http' =>
                    array(
                    'method'  => 'POST',
                    'header'  => 'Content-Type: application/x-www-form-urlencoded',
                    'content' => $postdata
                    )
                    );
                    $context  = stream_context_create($opts);
                    $result = file_get_contents($callback, false, $context);
                }  
              echo 1;
            } catch (PDOException $e) {
                  echo "Internal server error";
                //$URL .= '&return=error9';
                //header("Location: {$URL}");
                exit();
            }
         }
      break;
      case "loadInvoicesCollections":
        $stuId=$_POST['val'];
        $pupilsightSchoolYearID=$_POST['py'];
        $invoices = 'SELECT fn_fee_invoice.*,fn_fee_invoice.id as invoiceid, fn_fee_invoice_student_assign.invoice_no as stu_invoice_no, fn_fee_invoice_student_assign.id as invid, g.is_fine_editable, g.fine_type, g.rule_type, GROUP_CONCAT(DISTINCT asg.route_id) as routes, GROUP_CONCAT(DISTINCT asg.transport_type) as routetype FROM fn_fee_invoice LEFT JOIN pupilsightStudentEnrolment ON fn_fee_invoice.pupilsightSchoolYearID=pupilsightStudentEnrolment.pupilsightSchoolYearID LEFT JOIN pupilsightPerson ON pupilsightStudentEnrolment.pupilsightPersonID=pupilsightPerson.pupilsightPersonID RIGHT JOIN fn_fee_invoice_student_assign ON pupilsightPerson.pupilsightPersonID=fn_fee_invoice_student_assign.pupilsightPersonID AND fn_fee_invoice.id = fn_fee_invoice_student_assign.fn_fee_invoice_id LEFT JOIN fn_fees_fine_rule AS g ON fn_fee_invoice.fn_fees_fine_rule_id = g.id LEFT JOIN trans_route_assign AS asg ON pupilsightPerson.pupilsightPersonID = asg.pupilsightPersonID WHERE fn_fee_invoice.pupilsightSchoolYearID = "'.$pupilsightSchoolYearID.'" AND pupilsightPerson.pupilsightPersonID = "'.$stuId.'" AND fn_fee_invoice_student_assign.status = 1  GROUP BY fn_fee_invoice.id ORDER BY fn_fee_invoice_student_assign.id ASC';
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
            $sql_dis = "SELECT discount FROM fn_invoice_level_discount WHERE pupilsightPersonID = ".$stuId."  AND invoice_id='".$d['invoiceid']."' ";
            $result_dis = $connection2->query($sql_dis);
            $special_dis = $result_dis->fetch();

            $sp_item_sql="SELECT SUM(discount.discount) as sp_discount
            FROM fn_fee_invoice_item as fee_item
            LEFT JOIN fn_fee_item_level_discount as discount
            ON fee_item.id = discount.item_id WHERE fee_item.fn_fee_invoice_id='".$d['invoiceid']."'";
            $result_sp_item = $connection2->query($sp_item_sql);
            $sp_item_dis = $result_sp_item->fetch();
            //unset($invdata[$k]['finalamount']);
            
            if(!empty($d['transport_schedule_id']) && $d['transport_schedule_id'] != ''){
                $routes = explode(',',$d['routes']);
                foreach($routes as $rt){
                    $sqlsc = 'SELECT * FROM trans_route_price WHERE schedule_id = '.$d['transport_schedule_id'].' AND route_id = '.$rt.' ';
                    $resultsc = $connection2->query($sqlsc);
                    $datasc = $resultsc->fetch();
                    if($d['routetype'] == 'oneway'){
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
                $totalamount = $dataamt['totalamount'];
            }
            if(!empty($special_dis['discount']) || !empty($sp_item_dis['sp_discount'])){
                $invdata[$k]['finalamount'] = $totalamount-$special_dis['discount']-$sp_item_dis['sp_discount'];
                $totalamount=$totalamount-$special_dis['discount']-$sp_item_dis['sp_discount'];
            } else {
                $invdata[$k]['finalamount'] = $totalamount;
            }

            if(!empty($d['fn_fees_discount_id'])){
                $std_query="SELECT fee_category_id FROM `pupilsightPerson` WHERE `pupilsightPersonID` = ".$stuId." ";
                $std_exe = $connection2->query($std_query);
                $std_data=$std_exe->fetch();
                $fee_category_id=$std_data['fee_category_id'];

                $dissql="SELECT * FROM fn_fee_discount_item WHERE fn_fees_discount_id = ".$d['fn_fees_discount_id']." AND name = ".$fee_category_id." ";
                $resultdisitem = $connection2->query($dissql);
                $disamtdata = $resultdisitem->fetch();

                if(!empty($disamtdata)){
                    if($disamtdata['item_type'] == 'Fixed'){
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
          
            if(!empty($fineId) && $curdate > $duedate){
                $sqlschday = "SELECT GROUP_CONCAT(pupilsightDaysOfWeekID) as daysid, GROUP_CONCAT(name) as weekend FROM pupilsightDaysOfWeek WHERE schoolDay = 'N' ";
                $resultschday = $connection2->query($sqlschday);
                $weekenddata = $resultschday->fetch();
                $weekendDaysId = $weekenddata['daysid'];

                $datediff = $curdate - $duedate;
                $dd = round($datediff / (60 * 60 * 24));

                $finetype = $d['fine_type'];
                $ruletype = $d['rule_type'];
                if($finetype == '1' && $ruletype == '1'){
                    $sqlf = 'SELECT * FROM fn_fees_rule_type WHERE fn_fees_fine_rule_id = "'.$fineId.'" AND fine_type = "'.$finetype.'" AND rule_type = "'.$ruletype.'" ';
                    $resultf = $connection2->query($sqlf);
                    $finedata = $resultf->fetch();
                    $amtper = $finedata['amount_in_percent'];
                    $type = 'percent';
                } elseif($finetype == '1' && $ruletype == '2'){
                    $sqlf = 'SELECT * FROM fn_fees_rule_type WHERE fn_fees_fine_rule_id = "'.$fineId.'" AND fine_type = "'.$finetype.'" AND rule_type = "'.$ruletype.'" ';
                    $resultf = $connection2->query($sqlf);
                    $finedata = $resultf->fetch();
                    $amtper = $finedata['amount_in_number'];
                    $type = 'num';
                } elseif($finetype == '1' && $ruletype == '3'){
                    $sqlf = 'SELECT * FROM fn_fees_rule_type WHERE fn_fees_fine_rule_id = "'.$fineId.'" AND fine_type = "'.$finetype.'" AND rule_type = "'.$ruletype.'" AND from_date <= "'.$date.'" AND to_date >= "'.$date.'" ';
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
                    if($d['due_date'] != '1970-01-01'){
                        $sqlf = 'SELECT * FROM fn_fees_rule_type WHERE fn_fees_fine_rule_id = "'.$fineId.'" AND fine_type = "'.$finetype.'" AND rule_type = "'.$ruletype.'" ';
                        $resultf = $connection2->query($sqlf);
                        $finedata = $resultf->fetch();
                        $no = 0;
                        if(!empty($finedata['ignore_holiday'])){
                            $cdate = $date;
                            $ddate = $d['due_date'];
                            $no = countholidays($cdate,$ddate,$weekendDaysId);
                        }

                        if($no != '0'){
                            $nday = $dd - $no;
                        } else {
                            $nday = $dd;
                        }
                        
                        if(!empty($nday)){
                            $amtper = $finedata['amount_in_percent'] * $nday;
                        } else {
                            $amtper = $finedata['amount_in_percent'];
                        }
                        $type = 'percent';
                    }
                } elseif($finetype == '2' && $ruletype == '2'){
                    if($d['due_date'] != '1970-01-01'){
                        $sqlf = 'SELECT * FROM fn_fees_rule_type WHERE fn_fees_fine_rule_id = "'.$fineId.'" AND fine_type = "'.$finetype.'" AND rule_type = "'.$ruletype.'" ';
                        $resultf = $connection2->query($sqlf);
                        $finedata = $resultf->fetch();
                        $no = 0;
                        if(!empty($finedata['ignore_holiday'])){
                            $cdate = $date;
                            $ddate = $d['due_date'];
                            $no = countholidays($cdate,$ddate,$weekendDaysId);
                        }

                        if($no != '0'){
                            $nday = $dd - $no;
                        } else {
                            $nday = $dd;
                        }
                        
                        if(!empty($nday)){
                            $amtper = $finedata['amount_in_number'] * $nday;
                        } else {
                            $amtper = $finedata['amount_in_number'];
                        }
                        
                        $type = 'num';
                    }
                } elseif($finetype == '3' && $ruletype == '1'){
                    $sqlf = 'SELECT * FROM fn_fees_rule_type WHERE fn_fees_fine_rule_id = "'.$fineId.'" AND fine_type = "'.$finetype.'" AND rule_type = "'.$ruletype.'" ';
                    $resultf = $connection2->query($sqlf);
                    $finedata = $resultf->fetch();
                    $amtper = $finedata['amount_in_percent'];
                    $type = 'percent';
                } elseif($finetype == '3' && $ruletype == '2'){
                    $sqlf = 'SELECT * FROM fn_fees_rule_type WHERE fn_fees_fine_rule_id = "'.$fineId.'" AND fine_type = "'.$finetype.'" AND rule_type = "'.$ruletype.'" ';
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

                    $sqlf = 'SELECT * FROM fn_fees_rule_type WHERE fn_fees_fine_rule_id = "'.$fineId.'" AND fine_type = "'.$finetype.'" AND rule_type = "'.$ruletype.'" AND from_day <= "'.$days.'" AND to_day >= "'.$days.'" ';
                    $resultf = $connection2->query($sqlf);
                    $finedata = $resultf->fetch();

                    $no = 0;
                    if(!empty($finedata['ignore_holiday'])){
                        $cdate = $date;
                        $ddate = $d['due_date'];
                        $no = countholidays($cdate,$ddate,$weekendDaysId);
                    }

                    if($no != '0'){
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

                    if($finedata['amount_type'] == 'Fixed'){
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
            $sqla = 'SELECT GROUP_CONCAT(a.fn_fee_invoice_item_id) AS invitemid, b.invoice_status, b.transaction_id FROM fn_fees_student_collection AS a LEFT JOIN fn_fees_collection AS b ON  a.transaction_id = b.transaction_id WHERE a.invoice_no = "'.$invno.'" AND b.transaction_status IN (1,3) ';
            $resulta = $connection2->query($sqla);
            $inv = $resulta->fetch();
            $invdata[$k]['chkpayment'] = '';
            $invdata[$k]['pendingamount'] = '';
            
          
            $invdata[$k]['chkpayment'] = '';
            $invdata[$k]['pendingamount'] = '';
            if($inv['invoice_status'] == 'Fully Paid'){
                $invdata[$k]['paidamount'] = $totalamount;
                $pendingamount = 0;
                $invdata[$k]['pendingamount'] = $pendingamount;
                $invdata[$k]['chkpayment'] = 'Paid';
            } else {
                if(!empty($inv['invitemid'])){
                    $stTransId = $inv['transaction_id'];
                    if(!empty($d['transport_schedule_id'])){
                        $invdata[$k]['paidamount'] = $totalamount;
                        $pendingamount = 0;
                        $invdata[$k]['pendingamount'] = $pendingamount;
                        $invdata[$k]['chkpayment'] = 'Paid';
                    } else {    
                        $itemids = $inv['invitemid'];
                        $sqlp = 'SELECT SUM(total_amount_collection) as paidtotalamount FROM fn_fees_student_collection WHERE pupilsightPersonID = '.$stuId.' AND transaction_id = '.$stTransId.' AND fn_fee_invoice_item_id IN ('.$itemids.') ';
                        $resultp = $connection2->query($sqlp);
                        $amt = $resultp->fetch();
                        $totalpaidamt = $amt['paidtotalamount'];
                        if(!empty($totalpaidamt)){
                            $invdata[$k]['paidamount'] = $totalpaidamt;
                            $pendingamount = $totalamount- $totalpaidamt;
                            if($pendingamount<0){
                                $pendingamount=abs($pendingamount)."(Fine paid)";
                            }
                            $invdata[$k]['pendingamount'] = $pendingamount;
                            if($pendingamount<=0){
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
            
        }
        if(!empty($invdata)){
            foreach($invdata as $ind){
                $totAmt = number_format($ind['finalamount'], 2, '.', '');
                if($ind['chkpayment'] == 'Paid'){
                    //$cls = 'value="0" checked disabled';
                    echo '<tr><td><input type="checkbox" class=" invoice'.$ind['id'].'" name="invoiceid[]" data-h="'.$ind['fn_fees_head_id'].'" data-se="'.$ind['rec_fn_fee_series_id'].'" id="allfeeItemid" data-stu="'.$stuId.'" data-fper="'.$ind['amtper'].'" data-ftype="'.$ind['type'].'" data-inv="'.$ind['invid'].'" data-ife="'.$ind['is_fine_editable'].'" value="0" checked disabled ></td><td>'.$ind['stu_invoice_no'].'</td><td>'.$ind['title'].'</td><td>'.$totAmt.'</td><td>'.$ind['pendingamount'].'</td></tr>';
                } else {
                    $cls = 'value="'.$ind['invoiceid'].'"'; 
                     echo '<tr><td><input type="checkbox" class="chkinvoiceM invoice'.$ind['id'].'" name="invoiceid[]" data-h="'.$ind['fn_fees_head_id'].'" data-se="'.$ind['rec_fn_fee_series_id'].'" id="allfeeItemid" data-stu="'.$stuId.'" data-fper="'.$ind['amtper'].'" data-ftype="'.$ind['type'].'"  '.$cls.'  data-amtedt="'.$ind['amount_editable'].'" data-inv="'.$ind['invid'].'" data-ife="'.$ind['is_fine_editable'].'"></td><td>'.$ind['stu_invoice_no'].'</td><td>'.$ind['title'].'</td><td>'.$totAmt.'</td><td>'.$ind['pendingamount'].'</td></tr>';
                    
                }
            }
        } else {
            echo "<tr><td colspan='4'>No invoices found</td></tr>";
        }

      break;
      case "setEditTemplateValues":
            $name=$_POST['name'];
            $file=$_POST['file'];
            $session->forget(['file_name_tmp']);
            $session->forget(['file_doc_tmp']);
            $session->set('file_name_tmp', $name);
            $session->set('file_doc_tmp', $file);
      break;
      case "update_template_for_receipt":
      if(!empty($_FILES["file_upload"]["name"]))  
        { 
            $old_file=trim($_POST['old_file']);
            $fileData = pathinfo(basename($_FILES["file_upload"]["name"]));
            @$extension = end(explode(".", $_FILES["file_upload"]["name"]));    
            $NewNameFile = $old_file. '.'.$fileData['extension'];
            $sourcePath = $_FILES['file_upload']['tmp_name'];
            $uploaddir = 'thirdparty/phpword/templates/';
            //rename
            $o_name="thirdparty/phpword/templates/".$old_file.".docx";
            $r_name="thirdparty/phpword/templates/updated_at/".date('Y_m_d')."_".$old_file.".docx";
            @rename($o_name,$r_name);
            //rename
            $del="thirdparty/phpword/templates/".$old_file.".docx";
            @unlink($del);
            $uploadfile = $uploaddir .$NewNameFile;
            if(move_uploaded_file($sourcePath,$uploadfile)){
              echo "Template updated successfully";
            } else {
                echo "No";
            }
        } else {
            echo "Empty file uploaded";
        }
      break;
      case "apply_discount_session":
          $a_stuid=$_POST['p_stuId'];
          $a_yid=$_POST['pSyd'];
          $a_invoices_ids=$_POST['ids'];
          $session->forget(['a_stuid']);
          $session->forget(['a_yid']);
          $session->forget(['a_invoices_ids']);
          $session->set('a_stuid', $a_stuid);
          $session->set('a_yid', $a_yid);
          $session->set('a_invoices_ids', $a_invoices_ids);
      break;
      case "get_dicount_type_change":
        $d_type=$_POST['d_type'];
        $stuId=$_POST['sid'];
        $yid=$_POST['yid'];
        $ids=$_POST['ids'];
        if(!empty($d_type)){
            if($d_type=="1"){
                $invoices = 'SELECT fn_fee_invoice.*,fn_fee_invoice.id as invoiceid, fn_fee_invoice_student_assign.invoice_no as stu_invoice_no, g.fine_type, g.rule_type, GROUP_CONCAT(DISTINCT asg.route_id) as routes, GROUP_CONCAT(DISTINCT asg.transport_type) as routetype FROM fn_fee_invoice LEFT JOIN pupilsightStudentEnrolment ON fn_fee_invoice.pupilsightSchoolYearID=pupilsightStudentEnrolment.pupilsightSchoolYearID LEFT JOIN pupilsightPerson ON pupilsightStudentEnrolment.pupilsightPersonID=pupilsightPerson.pupilsightPersonID RIGHT JOIN fn_fee_invoice_student_assign ON pupilsightPerson.pupilsightPersonID=fn_fee_invoice_student_assign.pupilsightPersonID AND fn_fee_invoice.id = fn_fee_invoice_student_assign.fn_fee_invoice_id LEFT JOIN fn_fees_fine_rule AS g ON fn_fee_invoice.fn_fees_fine_rule_id = g.id LEFT JOIN trans_route_assign AS asg ON pupilsightPerson.pupilsightPersonID = asg.pupilsightPersonID WHERE fn_fee_invoice.pupilsightSchoolYearID = "'.$yid.'" AND pupilsightPerson.pupilsightPersonID = "'.$stuId.'" AND  fn_fee_invoice.id IN('.$ids.') GROUP BY fn_fee_invoice.id';
        $resultinv = $connection2->query($invoices);
        $invdata = $resultinv->fetchAll();
        $totalamount = 0;
        foreach($invdata as $k => $d){ 
            $sqlamt = 'SELECT SUM(fn_fee_invoice_item.total_amount) as totalamount FROM fn_fee_invoice_item WHERE fn_fee_invoice_id = '.$d['invoiceid'].' '; 
            $resultamt = $connection2->query($sqlamt);
            $dataamt = $resultamt->fetch();


            //unset($invdata[$k]['finalamount']);
            
            if(!empty($d['transport_schedule_id']) && $d['transport_schedule_id'] != ''){
                $routes = explode(',',$d['routes']);
                foreach($routes as $rt){
                    $sqlsc = 'SELECT * FROM trans_route_price WHERE schedule_id = '.$d['transport_schedule_id'].' AND route_id = '.$rt.' ';
                    $resultsc = $connection2->query($sqlsc);
                    $datasc = $resultsc->fetch();
                    if($d['routetype'] == 'oneway'){
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
                $totalamount = $dataamt['totalamount'];
            }
            $invdata[$k]['finalamount'] = $totalamount;
           
            $date = date('Y-m-d');
            $curdate = strtotime($date);
            $duedate = strtotime($d['due_date']);
            $fineId = $d['fn_fees_fine_rule_id'];

            if(!empty($fineId) && $curdate > $duedate){
                $finetype = $d['fine_type'];
                $ruletype = $d['rule_type'];
                if($finetype == '1' && $ruletype == '1'){
                    $sqlf = 'SELECT * FROM fn_fees_rule_type WHERE fn_fees_fine_rule_id = "'.$fineId.'" AND fine_type = "'.$finetype.'" AND rule_type = "'.$ruletype.'" ';
                    $resultf = $connection2->query($sqlf);
                    $finedata = $resultf->fetch();
                    $amtper = $finedata['amount_in_percent'];
                    $type = 'percent';
                } elseif($finetype == '1' && $ruletype == '2'){
                    $sqlf = 'SELECT * FROM fn_fees_rule_type WHERE fn_fees_fine_rule_id = "'.$fineId.'" AND fine_type = "'.$finetype.'" AND rule_type = "'.$ruletype.'" ';
                    $resultf = $connection2->query($sqlf);
                    $finedata = $resultf->fetch();
                    $amtper = $finedata['amount_in_number'];
                    $type = 'num';
                } elseif($finetype == '1' && $ruletype == '3'){
                    $sqlf = 'SELECT * FROM fn_fees_rule_type WHERE fn_fees_fine_rule_id = "'.$fineId.'" AND fine_type = "'.$finetype.'" AND rule_type = "'.$ruletype.'" AND from_date <= "'.$date.'" AND to_date >= "'.$date.'" ';
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
                    $sqlf = 'SELECT * FROM fn_fees_rule_type WHERE fn_fees_fine_rule_id = "'.$fineId.'" AND fine_type = "'.$finetype.'" AND rule_type = "'.$ruletype.'" ';
                    $resultf = $connection2->query($sqlf);
                    $finedata = $resultf->fetch();
                    $amtper = $finedata['amount_in_percent'];
                    $type = 'percent';
                } elseif($finetype == '2' && $ruletype == '2'){
                    $sqlf = 'SELECT * FROM fn_fees_rule_type WHERE fn_fees_fine_rule_id = "'.$fineId.'" AND fine_type = "'.$finetype.'" AND rule_type = "'.$ruletype.'" ';
                    $resultf = $connection2->query($sqlf);
                    $finedata = $resultf->fetch();
                    $amtper = $finedata['amount_in_number'];
                    $type = 'num';
                } elseif($finetype == '3' && $ruletype == '1'){
                    $sqlf = 'SELECT * FROM fn_fees_rule_type WHERE fn_fees_fine_rule_id = "'.$fineId.'" AND fine_type = "'.$finetype.'" AND rule_type = "'.$ruletype.'" ';
                    $resultf = $connection2->query($sqlf);
                    $finedata = $resultf->fetch();
                    $amtper = $finedata['amount_in_percent'];
                    $type = 'percent';
                } elseif($finetype == '3' && $ruletype == '2'){
                    $sqlf = 'SELECT * FROM fn_fees_rule_type WHERE fn_fees_fine_rule_id = "'.$fineId.'" AND fine_type = "'.$finetype.'" AND rule_type = "'.$ruletype.'" ';
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

                    $sqlf = 'SELECT * FROM fn_fees_rule_type WHERE fn_fees_fine_rule_id = "'.$fineId.'" AND fine_type = "'.$finetype.'" AND rule_type = "'.$ruletype.'" AND from_day <= "'.$days.'" AND to_day >= "'.$days.'" ';
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
            $sqla = 'SELECT GROUP_CONCAT(a.fn_fee_invoice_item_id) AS invitemid FROM fn_fees_student_collection AS a LEFT JOIN fn_fees_collection AS b ON  a.transaction_id = b.transaction_id WHERE a.invoice_no = "'.$invno.'" AND b.transaction_status = "1" ';
            $resulta = $connection2->query($sqla);
            $inv = $resulta->fetch();
          
            
            if(!empty($inv['invitemid'])){
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
                        if($pendingamount<0){
                            $pendingamount=abs($pendingamount)."(Fine paid)";
                        }
                        $invdata[$k]['pendingamount'] = $pendingamount;
                        if($pendingamount<=0){
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
        echo"<div>";
        ?>
        <table class="table" cellspacing="0" style="width: 100%;">
            <thead>
            <tr class="head">
            <th>Sl.No</th>
            <th>Invoice No</th>
            <th>Invoice Amount</th>
            <th>Discout Amount</th>
            <th>Select</th>
            </tr>
            </thead>
            <tbody>
                        <?php 
                        if(!empty($invdata)){
                            $i=1;
                        foreach($invdata as $ind){
                            $sql_dis = "SELECT discount FROM fn_invoice_level_discount WHERE pupilsightPersonID = ".$stuId."  AND invoice_id='".$ind['invoiceid']."' ";
                            $result_dis = $connection2->query($sql_dis);
                            $special_dis = $result_dis->fetch();
                            if(!empty($special_dis['discount'])){
                             $total=$ind['finalamount']-$special_dis['discount'];
                            } else {
                                $total=$ind['finalamount'];
                            }
                        echo '<tr>
                        <td  width="5%">'.$i++.'</td>
                        <td  width="10%">'.$ind['stu_invoice_no'].'</td>
                        <td  width="5%">'.$total.'</td>
                        <td  width="5%"><input type="number" name="discount_a[]" value="'.$special_dis['discount'].'" readonly class="form-control inid_'.$ind['invoiceid'].'" ></td>
                        <td  width="10%"><input type="checkbox"  class="chkinvoice_discount invoice'.$ind['invoiceid'].'" name="invoiceid[]" value="'.$ind['invoiceid'].'" data-id="'.$ind['invoiceid'].'" ></td>
                        </tr>';

                       
                        }
                        } else {
                        echo "<tr><td colspan='4'>No invoices found</td></tr>";
                        }
                        ?>
    </tbody>
  </table>
  <a href="#" class="btn btn-primary save_sp_discount" id="save_sp_discount" data-type="invoice_level_dataStore">Apply</a>
</div>
  <?php  
    } else {
        ?>
        <table class="table" cellspacing="0" style="width: 100%;">
            <thead>
            <tr class="head">
            <th>Sl.No</th>
            <th>Fee Item</th>
            <th>Amount</th>
            <th>Discount</th>
            <th>Select</th>
            </tr>
            </thead>
            <tbody>
        <?php
        $std_query="SELECT fee_category_id FROM `pupilsightPerson` WHERE `pupilsightPersonID` = '".$stuId."'";
        $std_exe = $connection2->query($std_query);
        $std_data=$std_exe->fetch();
        $fee_category_id=$std_data['fee_category_id'];
        $sqli = 'SELECT e.pupilsightPersonID,a.*,a.id as itemid, b.*, b.id as ifid, b.name as feeitemname, c.id AS invoiceid, c.transport_schedule_id, d.format, e.invoice_no as stu_invoice_no, f.item_type, f.name, f.min_invoice, f.max_invoice, f.amount_in_percent, f.amount_in_number, GROUP_CONCAT(DISTINCT asg.route_id) as routes, GROUP_CONCAT(DISTINCT asg.transport_type) as routetype  FROM fn_fee_invoice_item AS a LEFT JOIN fn_fee_items AS b ON a.fn_fee_item_id = b.id LEFT JOIN fn_fee_invoice AS c ON a.fn_fee_invoice_id = c.id LEFT JOIN fn_fee_series AS d ON c.inv_fn_fee_series_id = d.id LEFT JOIN fn_fee_invoice_student_assign AS e ON c.id = e.fn_fee_invoice_id  LEFT JOIN fn_fee_discount_item as f ON c.fn_fees_discount_id = f.fn_fees_discount_id LEFT JOIN trans_route_assign AS asg ON e.pupilsightPersonID = asg.pupilsightPersonID WHERE a.fn_fee_invoice_id IN (' . $ids . ') AND e.pupilsightPersonID = ' . $stuId . ' GROUP BY a.id';
        $resulti = $connection2->query($sqli);
        $feeItem = $resulti->fetchAll();
        $data = '';
      $i=1;
    foreach ($feeItem as $fI) {
         $discountamt=0;
         $discount=0;
        $sql_dis = "SELECT discount FROM fn_fee_item_level_discount WHERE pupilsightPersonID = ".$stuId."  AND item_id='".$fI['itemid']."' ";
        $result_dis = $connection2->query($sql_dis);
        $special_dis = $result_dis->fetch();
        if (!empty($fI['transport_schedule_id'])) {
            $routes = explode(',', $fI['routes']);
            foreach ($routes as $rt) {
                $sqlsc = 'SELECT * FROM trans_route_price WHERE schedule_id = ' . $fI['transport_schedule_id'] . ' AND route_id = ' . $rt . ' ';
                $resultsc = $connection2->query($sqlsc);
                $datasc = $resultsc->fetch();
                if ($fI['routetype'] == 'oneway') {
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


        $sqlchk = 'SELECT COUNT(a.id) as kount FROM fn_fees_student_collection AS a LEFT JOIN fn_fees_collection AS b ON a.transaction_id = b.transaction_id WHERE a.fn_fee_invoice_item_id = ' . $fI['itemid'] . ' AND a.pupilsightPersonID = ' . $stuId . ' AND b.transaction_status = "1" ';

        $resultchk = $connection2->query($sqlchk);
        $itemchk = $resultchk->fetch();

        if ($itemchk['kount'] == '1') {
            $cls = '';
            $checked = 'checked disabled';
        } else {
            $cls = 'a_selFeeItem';
            $checked = '';
        }

        // $inid = '000'.$id;
        // $invno = str_replace("0001",$inid,$fI['format']);

        if($fee_category_id==$fI['name']){
        if ($fI['item_type'] == 'Fixed') {
        $discount = $fI['amount_in_number'];
        $discountamt = $fI['amount_in_number'];
        } else {
        $discount = $fI['amount_in_percent'] . '%';
        $discountamt = ($fI['amount_in_percent'] / 100) * $totalamount;
        }
        }
        if(!empty($special_dis['discount'])){
          $discount+=$special_dis['discount'];
          $discountamt+=$special_dis['discount'];
        } 
        $amtdiscount = $totalamount - $discountamt;
        $data .= '<tr class="odd invrow" role="row">
                  
            <td width="5%">
                '.$i++.' 
            </td>
             
            <td width="10%">
               ' . $fI['feeitemname'] . '     
            </td> 
            <td width="5%">
            ' . $fI['amount'] . '  
            </td>                          
            <td width="10%">
               <input type="number" class="form-control itid_'.$fI['itemid'].'" value="'.$special_dis['discount'].'" readonly>
            </td>
             <td width="10%"><label class="leading-normal" for="feeItemid"></label> <input type="checkbox" class="' . $cls . '" id="feeItemid" data-id="'.$fI['itemid'].'" value="' . $fI['itemid'] . '" ' . $checked . '></td>
        </tr>';
    }
    echo $data;
    echo "</tbody>";
    echo "</table>";
?>
   <a href="#" class="btn btn-primary save_sp_discount" id="save_sp_discount" data-type="fee_item_level_dataStore">Apply</a>
<?php
    }
    } else {
    echo "<center>Select apply discount </center>";
    }
      break;
      case "invoice_level_dataStore":
        $stid=trim($_POST['stuid']);
        $discountVal=$_POST['discountVal'];
        $invids=$_POST['invids'];
        if(!empty($stid) && !empty($invids)){
        $count=sizeof($invids);
        for($i=0;$i<$count;$i++){
            $sqlpt = "SELECT id FROM fn_invoice_level_discount WHERE pupilsightPersonID = ".$stid."  AND invoice_id='".$invids[$i]."' ";
            $resultpt = $connection2->query($sqlpt);
            $valuept = $resultpt->fetch();
            if(empty($valuept['id'])){
               $datau = array('pupilsightPersonID' => $stid, 'invoice_id'=>$invids[$i],'discount' => $discountVal[$i]);
            $sql = 'INSERT INTO fn_invoice_level_discount SET pupilsightPersonID=:pupilsightPersonID, invoice_id=:invoice_id, discount=:discount';
            $result = $connection2->prepare($sql);
            $result->execute($datau);
            } else {
                $datau = array('pupilsightPersonID' => $stid, 'discount' => $discountVal[$i],'invoice_id'=>$invids[$i]);
                $sqlu = 'UPDATE fn_invoice_level_discount SET pupilsightPersonID=:pupilsightPersonID,discount=:discount WHERE invoice_id=:invoice_id';
                $resultu = $connection2->prepare($sqlu);
                $resultu->execute($datau);
            }
        }
        echo "success";
    } else { 
        echo "Some parametters missing error.";
    }
      break;
      case "fee_item_level_dataStore":
        $stid=trim($_POST['stuid']);
        $discountVal=$_POST['discountVal'];
        $items=$_POST['items'];
        if(!empty($stid) && !empty($items)){
        $count=sizeof($items);
        for($i=0;$i<$count;$i++){
            $sqlpt = "SELECT id FROM fn_fee_item_level_discount WHERE pupilsightPersonID = ".$stid."  AND item_id='".$items[$i]."' ";
            $resultpt = $connection2->query($sqlpt);
            $valuept = $resultpt->fetch();
            if(empty($valuept['id'])){
               $datau = array('pupilsightPersonID' => $stid, 'item_id'=>$items[$i],'discount' => $discountVal[$i]);
            $sql = 'INSERT INTO fn_fee_item_level_discount SET pupilsightPersonID=:pupilsightPersonID, item_id=:item_id, discount=:discount';
            $result = $connection2->prepare($sql);
            $result->execute($datau);
            } else {
                $datau = array('pupilsightPersonID' => $stid, 'discount' => $discountVal[$i],'item_id'=>$items[$i]);
                $sqlu = 'UPDATE fn_fee_item_level_discount SET pupilsightPersonID=:pupilsightPersonID,discount=:discount WHERE item_id=:item_id';
                $resultu = $connection2->prepare($sqlu);
                $resultu->execute($datau);
            }
        }
        echo "success";
    } else { 
        echo "Some parametters missing error.";
    }
break;
     case "multipaymentformDetails":
        $session->set('m_data', $_POST);
      break;
      case "setMarkHistoryVal":
        $session->forget(['stid']);
        $session->forget(['tid']);
        $session->forget(['skil_id']);
        $session->set('stid', $_POST['stid']);
        $session->set('tid', $_POST['tid']);
        $session->set('skil_id', $_POST['skil_id']);
      break;
      case "loadYearByTests":
        $pupilsightSchoolYearID=$_POST['pupilsightSchoolYearID'];
        $test_sql = 'SELECT  examinationTestMaster.id,examinationTestMaster.name FROM `examinationTestMaster` LEFT JOIN `pupilsightSchoolYear` ON `examinationTestMaster`.`pupilsightSchoolYearID`=`pupilsightSchoolYear`.`pupilsightSchoolYearID` WHERE `examinationTestMaster`.`pupilsightSchoolYearID` = "'.$pupilsightSchoolYearID.'" ORDER BY examinationTestMaster.name ASC';
        $test_res = $connection2->query($test_sql);
        $tests = $test_res->fetchAll();
        $options='<option value="">Select program</option>';
        foreach ($tests as $val) {
           $options.="<option value='".$val['id']."'>".$val['name']."</option>";
        }
        echo $options;
      break;
      case "loadTestByProgram":
            $test_id=$_POST['test_id'];
            $test_sql = 'SELECT p.pupilsightProgramID,p.name
            FROM pupilsightProgram as p
            LEFT JOIN examinationTestAssignClass as examTAC 
            ON p.pupilsightProgramID = examTAC.pupilsightProgramID
            WHERE examTAC.test_master_id = "'.$test_id.'" GROUP BY examTAC.pupilsightProgramID';
            $test_res = $connection2->query($test_sql);
            $tests = $test_res->fetchAll();
            $options='<option value="">Select program</option>';
            foreach ($tests as $val) {
            $options.="<option value='".$val['pupilsightProgramID']."'>".$val['name']."</option>";
            }
            echo $options;
      break;
      case "loadClassesByTest":
            $test_id=$_POST['test_id'];
            $pupilsightProgramID=$_POST['pupilsightProgramID'];
            $test_sql = 'SELECT g.pupilsightYearGroupID,g.name
            FROM pupilsightYearGroup as g 
            LEFT JOIN examinationTestAssignClass as examTAC
            ON g.pupilsightYearGroupID = examTAC.pupilsightYearGroupID 
            WHERE examTAC.test_master_id ="'.$test_id.'" AND examTAC.pupilsightProgramID="'.$pupilsightProgramID.'"  GROUP BY examTAC.pupilsightYearGroupID ORDER bY g.name ASC';
            $test_res = $connection2->query($test_sql);
            $tests = $test_res->fetchAll();
            $options='<option value="">Select program</option>';
            foreach ($tests as $val) {
            $options.="<option value='".$val['pupilsightYearGroupID']."'>".$val['name']."</option>";
            }
            echo $options;
      break;
       case "getSectionM":
        $pid=$_POST['pupilsightProgramID'];
        $cid =implode(',',$_POST['pupilsightYearGroup']);
        $sql = 'SELECT a.*, b.name FROM pupilsightProgramClassSectionMapping AS a LEFT JOIN pupilsightRollGroup AS b ON a.pupilsightRollGroupID = b.pupilsightRollGroupID WHERE a.pupilsightProgramID = "' . $pid . '" AND a.pupilsightYearGroupID IN(' . $cid . ') GROUP BY a.pupilsightRollGroupID';
        $result = $connection2->query($sql);
        $sections = $result->fetchAll();
        $data = '<option value="">Select Section</option>';
        if (!empty($sections)) {
        foreach ($sections as $k => $cl) {
        $data .= '<option value="' . $cl['pupilsightRollGroupID'] . '">' . $cl['name'] . '</option>';
        }
        }
        echo $data;
    break;
    case "getSubjectbasedonclassByM":
       $pupilsightYearGroupID=implode(',',$_POST['pupilsightYearGroupID']);
        $sq = "select pupilsightDepartmentID, subject_display_name, di_mode from subjectToClassCurriculum where pupilsightYearGroupID IN(".$pupilsightYearGroupID.")  GROUP BY subject_display_name order by subject_display_name asc";
        $result = $connection2->query($sq);
        $rowdata = $result->fetchAll();
        $returndata = '<option value="">Select Subject</option>';
        foreach ($rowdata as $row) {
        $returndata .= '<option value=' . $row['pupilsightDepartmentID'] . '  data-dimode=' . $row['di_mode'] . '>' . $row['subject_display_name'] . '</option>';
        }
        echo $returndata;
    break;
         
          case "load_remarks_descriptive_indicator_config":
            $sub_id = $_POST['sub'];
            $cls =$_POST['cls'];
            $prg = $_POST['program'];
            $sqlr = "SELECT * FROM subject_skill_descriptive_indicator_config WHERE pupilsightDepartmentID = ".$sub_id." AND pupilsightYearGroupID = ".$cls." AND pupilsightProgramID = ".$prg." " ;
            $resultr = $connection2->query($sqlr);
            $rowdatar = $resultr->fetchAll();

     foreach($rowdatar as $row) {  
        echo  '<tr>
        <td>
        <div class="dte mb-1"></div><div class="  txtfield mb-1"><div class="flex-1 relative"><input type="checkbox" class="selectGrdCheck" name="Selectone_byOne[]" id="'.$row['id'].'" value="'.$row['id'].'" ></div></div></td>
         <td>
        <div class="input-group stylish-input-group">
            <div class="dte mb-1"></div><div class="  txtfield mb-1"><div class="flex-1 relative"> <input type="text" name="remarksname" class="remarks_id" value="'.$row['remark_description'].'" style="    width: 500px;" ></div></div>
        </div></td></tr>';
    }
        break;
        case "load_remarks_grade_descriptive_indicator_config":
            $sub_id = $_POST['sub'];
            $cls =$_POST['cls'];
            $prg = $_POST['program'];
            $sqlr = "SELECT * FROM subject_skill_descriptive_indicator_config WHERE pupilsightDepartmentID = ".$sub_id." AND pupilsightYearGroupID = ".$cls." AND pupilsightProgramID = ".$prg." " ;
            $resultr = $connection2->query($sqlr);
            $rowdatar = $resultr->fetchAll();
            
     foreach($rowdatar as $row) {  
        echo  '<tr>
       
         <td>
            <div class="input-group stylish-input-group">
                <div class="dte mb-1"></div><div class="  txtfield mb-1"><div class="flex-1 relative">'.$row['grade'].'</div></div>
            </div></td>
            <td>
                <div class="input-group stylish-input-group w-full">
                    <div class="dte mb-1"></div><div class="  txtfield mb-1"><div class="flex-1 relative">
                    <input type="text" name="remarksname" class="remarks_id" value="'.$row['remark_description'].'" style="width: 500px;" >
                    </div></div>
                </div>
            </td>
            <td>
                <div class="input-group stylish-input-group">
                    <div class="dte mb-1"></div><div class="  txtfield mb-1"><div class="flex-1 relative">'.strlen($row['remark_description']).'
                   
                    
                    </div></div>
                </div>
            </td>
            <td>
            <div class="input-group stylish-input-group">
                <div class="dte mb-1"></div><div class="  txtfield mb-1"><div class="flex-1 relative"><input type="checkbox" class="selectGrdCheck" name="Selectone_byOne[]" id="'.$row['id'].'" value="'.$row['id'].'"></div></div>
            </div>
        </td>
            </tr>';
    }
        break;
        case "load_test_groups":
        $data = ' ';
            if(!empty($_POST['pupilsightRollGroupID'])){
             $pupilsightRollGroupID=implode(',',$_POST['pupilsightRollGroupID']);
             $pupilsightProgramID=$_POST['pupilsightProgramID'];
             $pupilsightSchoolYearID = $_SESSION[$guid]['pupilsightSchoolYearID'];
              $sql='SELECT SQL_CALC_FOUND_ROWS examinationTest.* FROM `examinationTest` LEFT JOIN `examinationTestAssignClass` ON `examinationTest`.`id`=`examinationTestAssignClass`.`test_id` LEFT JOIN `pupilsightSchoolYear` ON `examinationTest`.`pupilsightSchoolYearID`=`pupilsightSchoolYear`.`pupilsightSchoolYearID` WHERE `examinationTest`.`pupilsightSchoolYearID` = "'.$pupilsightSchoolYearID.'" AND `examinationTestAssignClass`.`pupilsightProgramID` = "'.$pupilsightProgramID.'" AND `examinationTestAssignClass`.`pupilsightYearGroupID` = "001" AND `examinationTestAssignClass`.`pupilsightRollGroupID` IN ('.$pupilsightRollGroupID.')  ORDER BY `examinationTest`.`id` DESC';
                $result = $connection2->query($sql);
                $test = $result->fetchAll();
                if (!empty($test)) {
                foreach ($test as $cl) {
                $data .= '<tr><td>
                <input class="slt_test" type ="checkbox" name="testID[]" value="' . $cl['id'] . '">'. " </td>
                <td>". $cl['name']."</td></tr>" ;
                }
                }
            } else { 
               $data.="<tr><td colspan='2'>No data</td></tr>";
            }
                echo $data;
        break;
        case "load_tests_subjects":
             $testID=implode(',', $_POST['testID']);
            $sqls ="SELECT a.*,b.*,c.name AS test,c.max_marks as maxMarks,e.name AS section,b.marks_obtained ,f.name as class,i.pupilsightDepartmentID,i.subject_display_name as subname,j.name as skill,j.id as skill_id FROM pupilsightPerson AS a LEFT JOIN examinationMarksEntrybySubject AS b ON a.pupilsightPersonID = b.pupilsightPersonID LEFT JOIN  examinationTest as c ON b.test_id = c.id 
        LEFT JOIN pupilsightStudentEnrolment AS d ON a.pupilsightPersonID = d.pupilsightPersonID LEFT JOIN pupilsightRollGroup AS e ON d.pupilsightRollGroupID = e.pupilsightRollGroupID LEFT JOIN pupilsightYearGroup AS f ON d.pupilsightYearGroupID = f.pupilsightYearGroupID  LEFT JOIN pupilsightProgram as h ON d.pupilsightProgramID = h.pupilsightProgramID LEFT JOIN subjectToClassCurriculum as i ON b.pupilsightDepartmentID =i.pupilsightDepartmentID LEFT JOIN ac_manage_skill as j ON b.skill_id = j.id WHERE   c.id IN(209) GROUP BY a.pupilsightPersonID";
            $results = $connection2->query($sqls);
            $rowdatas = $results->fetchAll();
        break;
        case 'studentMarks_excel':
       $program=$_POST['program'];
       $cls=$_POST['cls'];
       $section=$_POST['section'];
       $testId=implode(',',$_POST['testId']);
       $sql = 'SELECT b.officialName, b.pupilsightPersonID, d.name as classname, e.name as sectionname FROM `examinationMarksEntrybySubject` AS a LEFT JOIN pupilsightPerson AS b ON a.pupilsightPersonID = b.pupilsightPersonID LEFT JOIN pupilsightStudentEnrolment AS c ON a.pupilsightPersonID = c.pupilsightPersonID LEFT JOIN pupilsightYearGroup AS d ON c.pupilsightYearGroupID = d.pupilsightYearGroupID LEFT JOIN pupilsightRollGroup AS e ON c.pupilsightRollGroupID = e.pupilsightRollGroupID WHERE   a.test_id = '.$testId.' GROUP BY a.pupilsightPersonID';
        $result = $connection2->query($sql);
        $data = $result->fetchAll();
         foreach($data as $k => $dt){
            $sqlm='SELECT a.*, b.name as subject_name, c.skill_display_name,m.max_marks FROM examinationMarksEntrybySubject AS a LEFT JOIN pupilsightDepartment AS b ON a.pupilsightDepartmentID = b.pupilsightDepartmentID LEFT JOIN subjectSkillMapping AS c ON a.skill_id = c.skill_id
                    LEFT JOIN examinationSubjectToTest AS m ON a.pupilsightDepartmentID = m.pupilsightDepartmentID
                    WHERE a.test_id = '.$testId.' AND a.pupilsightPersonID = '.$dt['pupilsightPersonID'].' GROUP by a.pupilsightDepartmentID,c.skill_id';
                    $resultm = $connection2->query($sqlm);
                    $datam = $resultm->fetchAll();
                    if(!empty($datam)){
                    $data[$k]['marks'] = $datam;
                    }
              } 
          $sql1='SELECT a.*, b.name as subject_name, c.skill_display_name,m.max_marks FROM examinationMarksEntrybySubject AS a LEFT JOIN pupilsightDepartment AS b ON a.pupilsightDepartmentID = b.pupilsightDepartmentID LEFT JOIN subjectSkillMapping AS c ON a.skill_id = c.skill_id
            LEFT JOIN examinationSubjectToTest AS m ON a.pupilsightDepartmentID = m.pupilsightDepartmentID
            WHERE a.test_id = '.$testId.' GROUP by a.pupilsightDepartmentID,c.skill_id';
            $resu_h= $connection2->query($sql1);
            $datam_h = $resu_h->fetchAll(); 
        ?>
<table id="excelexport">
  <tr>
    <th>Student Name</th>
    <th>Student ID</th>
    <th>Class</th>
    <th>Section</th>
     <?php 
         foreach ($datam_h as $m) {
            ?>
            <th><?php echo $m['subject_name']."-".$m['skill_display_name']."/".ceil($m['max_marks']);?></th>
            <?php
         }
    ?>
  </tr>
  
  <?php foreach($data as $row) {  
  echo "<tr>
    <td>".$row['officialName']."</td>
    <td>".$row['pupilsightPersonID']."</td>
    <td>".$row['classname']."</td>
    <td>".$row['sectionname']."</td>";
    $marks=$row['marks'];
    foreach ($data as $val) { 
         $marks=$val['marks'];
         foreach ($marks as $m) {
            $sql='SELECT grade_name FROM  examinationGradeSystemConfiguration WHERE id="'.$m['gradeId'].'"';
            $result = $connection2->query($sql);
            $gradeName = $result->fetch();
            $grade_name='';

            if(!empty($gradeName['grade_name'])){
              $grade_name=$gradeName['grade_name'];
            }
            if($row['pupilsightPersonID']==$val['pupilsightPersonID']){

                $marks = str_replace(".00","",$m['marks_obtained']);
                if($marks==0){
                    if($m['marks_abex']){
                        $marks = $m['marks_abex'];
                    }
                }
                if(!empty($grade_name)){
                    echo "<td>".$marks."(".$grade_name.")</td>";
                } else {
                    echo "<td>".$marks."</td>";
                }
                
            }
        }
    } 
    ?>
</tr>
<?php } ?>
</table>
            <?php
             break;
             case 'subjectMarks_excel';
             $testId=implode(',', $_POST['testId']);
            //   $sql = "SELECT a.* ,b.skill_display_name ,d.name as test,c.name as subject,GROUP_CONCAT(DISTINCT b.skill_id SEPARATOR ', ') as skill_ids,GROUP_CONCAT(DISTINCT b.skill_display_name SEPARATOR ', ') as skillname FROM examinationMarksEntrybySubject AS a LEFT JOIN subjectSkillMapping AS b ON a.`skill_id` = b.skill_id LEFT JOIN pupilsightDepartment as c ON a.pupilsightDepartmentID=c.pupilsightDepartmentID LEFT JOIN  examinationTest as d ON a.test_id = d.id LEFT JOIN examinationTest as e ON a.test_id = e.id  WHERE   a.test_id = ".$testId." GROUP BY a.pupilsightPersonID";
            $sql = 'SELECT b.officialName, b.pupilsightPersonID, d.name as classname, e.name as sectionname FROM `examinationMarksEntrybySubject` AS a LEFT JOIN pupilsightPerson AS b ON a.pupilsightPersonID = b.pupilsightPersonID LEFT JOIN pupilsightStudentEnrolment AS c ON a.pupilsightPersonID = c.pupilsightPersonID LEFT JOIN pupilsightYearGroup AS d ON c.pupilsightYearGroupID = d.pupilsightYearGroupID LEFT JOIN pupilsightRollGroup AS e ON c.pupilsightRollGroupID = e.pupilsightRollGroupID WHERE   a.test_id = '.$testId.' GROUP BY a.pupilsightPersonID';
            $result = $connection2->query($sql);
            $data = $result->fetchAll();
            // echo '<pre>';
            //  print_r($data);
            // echo '</pre>';
         foreach($data as $k => $dt){
            $sqlm='SELECT a.*, b.name as subject_name, c.skill_display_name,m.max_marks FROM examinationMarksEntrybySubject AS a LEFT JOIN pupilsightDepartment AS b ON a.pupilsightDepartmentID = b.pupilsightDepartmentID LEFT JOIN subjectSkillMapping AS c ON a.skill_id = c.skill_id
                    LEFT JOIN examinationSubjectToTest AS m ON a.pupilsightDepartmentID = m.pupilsightDepartmentID
                    WHERE a.test_id = '.$testId.' AND a.pupilsightPersonID = '.$dt['pupilsightPersonID'].' GROUP by a.pupilsightDepartmentID,c.skill_id';
                    $resultm = $connection2->query($sqlm);
                    $datam = $resultm->fetchAll();
                    if(!empty($datam)){
                    $data[$k]['marks'] = $datam;
                    }
              }    
              
          $sql1='SELECT a.*, b.name as subject_name, c.skill_display_name,m.max_marks FROM examinationMarksEntrybySubject AS a LEFT JOIN pupilsightDepartment AS b ON a.pupilsightDepartmentID = b.pupilsightDepartmentID LEFT JOIN subjectSkillMapping AS c ON a.skill_id = c.skill_id
            LEFT JOIN examinationSubjectToTest AS m ON a.pupilsightDepartmentID = m.pupilsightDepartmentID
            WHERE a.test_id = '.$testId.' GROUP by a.pupilsightDepartmentID,c.skill_id';
            $resu_h= $connection2->query($sql1);
            $datam_h = $resu_h->fetchAll();    
        ?>
            
<table id="subexcelexport">
  <tr>
    <th>Student Name</th>
    <th>Student ID</th>
    <th>Class</th>
    <th>Section</th>
   <?php 
         foreach ($datam_h as $m) {
            ?>
            <th><?php echo $m['subject_name']."-".$m['skill_display_name']."/".ceil($m['max_marks']);?></th>
            <?php
         }
    ?>
  </tr>
  
  <?php foreach($data as $row) {  
  echo "<tr>
    <td>".$row['officialName']."</td>
    <td>".$row['pupilsightPersonID']."</td>
    <td>".$row['classname']."</td>
    <td>".$row['sectionname']."</td>";
    $marks=$row['marks'];
    foreach ($data as $val) { 
         $marks=$val['marks'];
         foreach ($marks as $m) {
            $sql='SELECT grade_name FROM  examinationGradeSystemConfiguration WHERE id="'.$m['gradeId'].'"';
            $result = $connection2->query($sql);
            $gradeName = $result->fetch();
            $grade_name='';

            if(!empty($gradeName['grade_name'])){
              $grade_name=$gradeName['grade_name'];
            }

            if($row['pupilsightPersonID']==$val['pupilsightPersonID']){

                $marks = str_replace(".00","",$m['marks_obtained']);
                if($marks==0){
                    if($m['marks_abex']){
                        $marks = $m['marks_abex'];
                    }
                }
                if(!empty($grade_name)){
                    echo "<td>".$marks."(".$grade_name.")</td>";
                } else {
                    echo "<td>".$marks."</td>";
                }
                
            }
        }
    }  
    ?>
</tr>
<?php } ?>
</table>
 <?php
    break;
     case "load_Student_data":
        $cid = $_POST['val'];
        $pupilsightSchoolYearID = $_POST['yid'];
        $pupilsightProgramID = $_POST['pid'];
        $pupilsightYearGroupID = $_POST['cid'];
        $sql = 'SELECT a.*, b.officialName FROM  pupilsightStudentEnrolment AS a LEFT JOIN pupilsightPerson AS b ON a.pupilsightPersonID = b.pupilsightPersonID WHERE a.pupilsightSchoolYearID = "' . $pupilsightSchoolYearID . '" AND a.pupilsightProgramID = "' . $pupilsightProgramID . '" AND a.pupilsightYearGroupID = "' . $pupilsightYearGroupID . '" AND a.pupilsightRollGroupID = "' . $cid . '" AND pupilsightRoleIDPrimary=003 GROUP BY b.pupilsightPersonID';
        $result = $connection2->query($sql);
        $sections = $result->fetchAll();
        echo'<option value="">Select Student</option>';
        if (!empty($sections)) {
            foreach ($sections as $k => $cl) {
            echo '<option value="' . $cl['pupilsightPersonID'] . '">' . $cl['officialName'] . '</option>';
            }
        }
     break;
    default:
      echo "Invalid request";
    }
} else {
  echo "Request type is missing";
}

/*function rename_win($oldfile,$newfile) {
   if (!rename($oldfile,$newfile)) {
      if (copy ($oldfile,$newfile)) {
         unlink($oldfile);
         return TRUE;
      }
      return FALSE;
   }
   return TRUE;
}*/

// function savePaymentModeData($tid,$mdata){
    // $t_id = $tid;
    // $mdata = $mdata;
    // $pmode=$mdata['payment_mode_id'];
    // $mcredit = $mdata['credit_id'];
    // $bank_id = $mdata['bank_id'];
    // $amount = $mdata['amount'];
    // $mrefno = $mdata['reference_no'];
    // $minstruDate = $mdata['instrument_date'];
    // $l=sizeof($pmode);
    // for($i=0;$i<$l;$i++){
    //     $datam = array('transaction_id'=>$t_id,'payment_mode_id' => $pmode[$i],  'credit_card' => $mcredit[$i], 'bank_id' => $bank_id[$i],   'amount' =>$amount[$i],'reference_no'=>$mrefno,'instrument_date' =>$minstruDate[$i]);
    //     $sqlm = 'INSERT INTO fn_multi_payment_mode SET transaction_id=:transaction_id, payment_mode_id=:payment_mode_id, credit_card=:credit_card, bank_id=:bank_id,amount=:amount,reference_no=:reference_no,instrument_date=:instrument_date';
    //     $resultm = $connection2->prepare($sqlm);
    //     $resultm->execute($datam);

    // }

// }

function countholidays($cdate,$ddate,$weekendDaysId){
    $no = 0;
    $start = new DateTime($ddate);
    $end   = new DateTime($cdate);
    $interval = DateInterval::createFromDateString('1 day');
    $period = new DatePeriod($start, $interval, $end);
    foreach ($period as $dt)
    {
        if(!empty($weekendDaysId)){
            $weekIds = explode(',', $weekendDaysId);
            foreach($weekIds as $wid){
                if ($dt->format('N') == $wid)
                {
                    $no++;
                }
            }
        }
    }
    return $no;
}

?>


