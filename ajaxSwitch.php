<?php
/*
Pupilsight, Flexible & Open School System
*/
include 'pupilsight.php';
$session = $container->get('session');
if (isset($_POST['type'])) {
    $type = trim($_POST['type']);
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
                    foreach ($trans_id as $ts) {
                        unset($dts_receipt_feeitem);
                        unset($dts_receipt);

                        $sqlpt = "SELECT transaction_id FROM fn_fees_collection WHERE id = " . $ts . " ";
                        $resultpt = $connection2->query($sqlpt);
                        $valuept = $resultpt->fetch();
                        $transId = $valuept['transaction_id'];


                        $data = array('remarks' => $remarks, 'fn_fees_collection_id' => $ts, 'canceled_by' => $cuid, 'cdt' => $cdt);
                        $sql = 'INSERT INTO fn_fees_cancel_collection SET remarks=:remarks, fn_fees_collection_id=:fn_fees_collection_id, canceled_by=:canceled_by, cdt=:cdt';
                        $result = $connection2->prepare($sql);
                        $result->execute($data);

                        $datau = array('transaction_status' => '2', 'id' => $ts);
                        $sqlu = 'UPDATE fn_fees_collection SET transaction_status=:transaction_status WHERE id=:id';
                        $resultu = $connection2->prepare($sqlu);
                        $resultu->execute($datau);

                        $datau = array('is_active' => '2', 'transaction_id' => $transId);
                        $sqlu = 'UPDATE fn_fees_student_collection SET is_active=:is_active WHERE transaction_id=:transaction_id';
                        $resultu = $connection2->prepare($sqlu);
                        $resultu->execute($datau);

                        $sqlinv = "SELECT * FROM fn_fees_student_collection WHERE transaction_id = " . $transId . " ";
                        $resultinv = $connection2->query($sqlinv);
                        $valueinvData = $resultinv->fetchAll();

                        if (!empty($valueinvData)) {
                            foreach ($valueinvData as $valueinv) {
                                $dataui = array('invoice_status' => 'Not Paid', 'invoice_no' => $valueinv['invoice_no']);
                                $sqlui = 'UPDATE fn_fee_invoice_student_assign SET invoice_status=:invoice_status WHERE invoice_no=:invoice_no';
                                $resultui = $connection2->prepare($sqlui);
                                $resultui->execute($dataui);

                                $datai = array('pupilsightPersonID' => $valueinv['pupilsightPersonID'], 'transaction_id' => $valueinv['transaction_id'],  'fn_fees_invoice_id' => $valueinv['fn_fees_invoice_id'], 'fn_fee_invoice_item_id' => $valueinv['fn_fee_invoice_item_id'], 'invoice_no' => $valueinv['invoice_no'], 'total_amount' => $valueinv['total_amount'], 'discount' => $valueinv['discount'], 'total_amount_collection' => $valueinv['total_amount_collection'], 'status' => $valueinv['status']);
                                $sqli = 'INSERT INTO fn_fees_student_cancel_collection SET pupilsightPersonID=:pupilsightPersonID, transaction_id=:transaction_id, fn_fees_invoice_id=:fn_fees_invoice_id, fn_fee_invoice_item_id=:fn_fee_invoice_item_id, invoice_no=:invoice_no, total_amount=:total_amount, discount=:discount,  total_amount_collection=:total_amount_collection, status=:status';
                                $resulti = $connection2->prepare($sqli);
                                $resulti->execute($datai);

                                // $data = array('id' => $valueinv['id']);
                                // $sql = 'DELETE FROM fn_fees_student_collection WHERE id=:id';
                                // $result = $connection2->prepare($sql);
                                // $result->execute($data);
                            }
                        }


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
                    echo "Error" . $e;
                    exit();
                }
                echo "success";
            }
            break;
        case "collectionForm_request":

            // echo '<pre>';
            // print_r($_POST);
            // echo '</pre>';
            // die();
            $checkmode = $_POST['checkmode'];
            $counterid = $session->get('counterid');
            $invoice_id = $_POST['invoice_id'];
            $invoice_item_id = $_POST['invoice_item_id'];
            $fn_fees_invoice_id = $_POST['invoice_id'];
            $pupilsightPersonID = $_POST['pupilsightPersonID'];
            $pupilsightSchoolYearID = $_POST['pupilsightSchoolYearID'];
            $reference_no = $_POST['reference_no'];

            if (!empty($_POST['reference_date'])) {
                $fd = explode('/', $_POST['reference_date']);
                $reference_date  = date('Y-m-d', strtotime(implode('-', array_reverse($fd))));
            } else {
                $reference_date  = '';
            }

            if (!empty($_POST['is_custom'])) {
                $is_custom = $_POST['is_custom'];
            } else {
                $is_custom = '';
            }

            $payment_mode_id = $_POST['payment_mode_id'];
            $bank_id = $_POST['bank_id'];
            if (!empty($bank_id)) {
                $sqlbn = 'SELECT name FROM fn_masters WHERE id = ' . $bank_id . ' ';
                $resultbn = $connection2->query($sqlbn);
                $bankNameData = $resultbn->fetch();
                $bank_name = $bankNameData['name'];
            } else {
                $bank_name = '';
            }

            $dd_cheque_no = $_POST['dd_cheque_no'];
            if (!empty($_POST['dd_cheque_date'])) {
                $fd = explode('/', $_POST['dd_cheque_date']);
                $dd_cheque_date  = date('Y-m-d', strtotime(implode('-', array_reverse($fd))));
            } else {
                $dd_cheque_date  = '';
            }
            $dd_cheque_amount = $_POST['dd_cheque_amount'];
            $payment_status = $_POST['payment_status'];
            if (!empty($_POST['payment_date'])) {
                $pd = explode('/', $_POST['payment_date']);
                $payment_date  = date('Y-m-d', strtotime(implode('-', array_reverse($pd))));
            } else {
                $payment_date  = '';
            }

            $instrument_no = $_POST['dd_cheque_no'];
            if (!empty($_POST['dd_cheque_date'])) {
                $insd = explode('/', $_POST['dd_cheque_date']);
                $instrument_date  = date('Y-m-d', strtotime(implode('-', array_reverse($insd))));
            } else {
                $instrument_date  = '';
            }

            $fn_fees_head_id = $_POST['fn_fees_head_id'];
            $sqlrt = 'SELECT a.ac_no, b.path, b.column_start_by FROM fn_fees_head AS a LEFT JOIN fn_fees_receipt_template_master AS b ON a.receipt_template = b.id WHERE a.id = ' . $fn_fees_head_id . ' ';
            $resultrt = $connection2->query($sqlrt);
            $recTempData = $resultrt->fetch();
            $receiptTemplate = $recTempData['path'];
            $fee_head_acc_no = $recTempData['ac_no'];
            $column_start_by = $recTempData['column_start_by'];


            $fn_fees_receipt_series_id = $_POST['fn_fees_receipt_series_id'];


            $transcation_amount = $_POST['transcation_amount'];
            //$transcation_amount = $_POST['transcation_amount_old'];
            $amount_paying = $_POST['amount_paying'];
            $over_payment = $_POST['overamount'];
            $deposit_fee_item_account_id = $_POST['deposit_account_id'];
            $total_amount_without_fine_discount = $_POST['total_amount_without_fine_discount'];
            if ($amount_paying > $transcation_amount) {
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

                    if (!empty($_POST['receipt_number'])) {
                        $receipt_number = $_POST['receipt_number'];
                    } else {
                        if (!empty($fn_fees_receipt_series_id)) {
                            $sqlrec = 'SELECT id, formatval FROM fn_fee_series WHERE id = "' . $fn_fees_receipt_series_id . '" ';
                            $resultrec = $connection2->query($sqlrec);
                            $recptser = $resultrec->fetch();

                            $invformat = explode('$', $recptser['formatval']);
                            $iformat = '';
                            $orderwise = 0;
                            foreach ($invformat as $inv) {
                                if ($inv == '{AB}') {
                                    $datafort = array('fn_fee_series_id' => $fn_fees_receipt_series_id, 'order_wise' => $orderwise, 'type' => 'numberwise');
                                    $sqlfort = 'SELECT id, no_of_digit, last_no FROM fn_fee_series_number_format WHERE fn_fee_series_id=:fn_fee_series_id AND order_wise=:order_wise AND type=:type';
                                    $resultfort = $connection2->prepare($sqlfort);
                                    $resultfort->execute($datafort);
                                    $formatvalues = $resultfort->fetch();
                                    //$iformat .= $formatvalues['last_no'].'/';
                                    $iformat .= $formatvalues['last_no'];

                                    $str_length = $formatvalues['no_of_digit'];

                                    $lastnoadd = $formatvalues['last_no'] + 1;

                                    $lastno = substr("0000000{$lastnoadd}", -$str_length);

                                    $datafort1 = array('fn_fee_series_id' => $fn_fees_receipt_series_id, 'order_wise' => $orderwise, 'type' => 'numberwise', 'last_no' => $lastno);
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

                    $data = array('fn_fees_invoice_id' => $fn_fees_invoice_id, 'pupilsightPersonID' => $pupilsightPersonID, 'pupilsightSchoolYearID' => $pupilsightSchoolYearID, 'fn_fees_counter_id' => $counterid, 'receipt_number' => $receipt_number, 'is_custom' => $is_custom, 'payment_mode_id' => $payment_mode_id, 'bank_id' => $bank_id, 'dd_cheque_no' => $dd_cheque_no, 'dd_cheque_date' => $dd_cheque_date, 'dd_cheque_amount' => $dd_cheque_amount, 'payment_status' => $payment_status, 'payment_date' => $payment_date, 'fn_fees_head_id' => $fn_fees_head_id, 'fn_fees_receipt_series_id' => $fn_fees_receipt_series_id, 'transcation_amount' => $transcation_amount, 'total_amount_without_fine_discount' => $total_amount_without_fine_discount, 'amount_paying' => $amount_paying, 'over_payment' => $over_payment, 'fine' => $fine, 'discount' => $discount, 'remarks' => $remarks, 'status' => $status, 'cdt' => $cdt, 'reference_no' => $reference_no, 'reference_date' => $reference_date, 'instrument_no' => $instrument_no, 'instrument_date' => $instrument_date, 'invoice_status' => $invoice_status);
                    // echo '<pre>';
                    // print_r($data);
                    // echo '</pre>';

                    $sql = 'INSERT INTO fn_fees_collection SET fn_fees_invoice_id=:fn_fees_invoice_id, pupilsightPersonID=:pupilsightPersonID, pupilsightSchoolYearID =:pupilsightSchoolYearID, fn_fees_counter_id=:fn_fees_counter_id, receipt_number=:receipt_number, is_custom=:is_custom, payment_mode_id=:payment_mode_id, bank_id=:bank_id, dd_cheque_no=:dd_cheque_no, dd_cheque_date=:dd_cheque_date, dd_cheque_amount=:dd_cheque_amount, payment_status=:payment_status, payment_date=:payment_date, fn_fees_head_id=:fn_fees_head_id, fn_fees_receipt_series_id=:fn_fees_receipt_series_id, transcation_amount=:transcation_amount, total_amount_without_fine_discount=:total_amount_without_fine_discount, amount_paying=:amount_paying, over_payment=:over_payment, fine=:fine, discount=:discount, remarks=:remarks, status=:status,cdt=:cdt,reference_no=:reference_no,reference_date=:reference_date,instrument_no=:instrument_no,instrument_date=:instrument_date,invoice_status=:invoice_status';
                    $result = $connection2->prepare($sql);
                    $result->execute($data);

                    $collectionId = $connection2->lastInsertID();

                    $rand = mt_rand(10, 99);
                    $t = time();
                    $transactionId = $t . $rand;

                    $datau = array('transaction_id' => $transactionId,  'id' => $collectionId);
                    $sqlu = 'UPDATE fn_fees_collection SET transaction_id=:transaction_id WHERE id=:id';
                    $resultu = $connection2->prepare($sqlu);
                    $resultu->execute($datau);

                    //$paidInv = array();
                    if (!empty($over_payment)) {
                        $amtPaid = $amount_paying + $over_payment;
                    } else {
                        $amtPaid = $amount_paying;
                    }
                    if ($amtPaid < $transcation_amount) {
                        $isql = 'SELECT a.id, a.fn_fee_invoice_id, a.total_amount,b.invoice_no, a.fn_fee_item_id, d.name as item_type_name FROM fn_fee_invoice_item AS a LEFT JOIN fn_fee_invoice_student_assign AS b ON a.fn_fee_invoice_id = b.fn_fee_invoice_id LEFT JOIN fn_fee_items AS c ON a.fn_fee_item_id = c.id LEFT JOIN fn_fee_item_type AS d ON c.fn_fee_item_type_id = d.id  WHERE a.id IN (' . $invoice_item_id . ') AND b.pupilsightPersonID = ' . $pupilsightPersonID . '  ORDER BY b.id ASC';
                        $resultip = $connection2->query($isql);
                        $valuesip = $resultip->fetchAll();

                        if (!empty($valuesip)) {
                            $chkamount = $amtPaid;
                            //$i = 1;
                            $fnInvId = '';
                            $itLevDis = 0;
                            $collAmt = 0;
                            foreach ($valuesip as $itmid) {
                                $fn_fee_item_id = $itmid['fn_fee_item_id'];
                                $fn_fee_invoice_id = $itmid['fn_fee_invoice_id'];
                                $invoice_no = $itmid['invoice_no'];
                                $itemamount = $itmid['total_amount'];
                                $itid = $itmid['id'];
                                $item_type_name = trim($itmid['item_type_name']);

                                $chkcolsql = 'SELECT total_amount, SUM(total_amount_collection) as tot_amt_col FROM fn_fees_student_collection WHERE pupilsightPersonID = ' . $pupilsightPersonID . ' AND invoice_no = "' . $invoice_no . '" AND total_amount >= 0 AND is_active = "1" ';
                                $resultcolchk = $connection2->query($chkcolsql);
                                $collData = $resultcolchk->fetch();


                                if ($item_type_name == 'Discount') {
                                    $datai = array('pupilsightPersonID' => $pupilsightPersonID, 'transaction_id' => $transactionId,  'fn_fees_invoice_id' => $fn_fee_invoice_id, 'fn_fee_invoice_item_id' => $itid, 'invoice_no' => $invoice_no, 'total_amount' => $itemamount, 'status' => 1);
                                    //print_r($datai);
                                    $sqli = 'INSERT INTO fn_fees_student_collection SET pupilsightPersonID=:pupilsightPersonID, transaction_id=:transaction_id, fn_fees_invoice_id=:fn_fees_invoice_id, fn_fee_invoice_item_id=:fn_fee_invoice_item_id, invoice_no=:invoice_no, total_amount=:total_amount, status=:status';
                                    $resulti = $connection2->prepare($sqli);
                                    $resulti->execute($datai);
                                } else {

                                    if ($fnInvId != $fn_fee_invoice_id) {
                                        $sqlchkdis = 'SELECT SUM(a.discount) AS invoice_level_discount FROM fn_fee_invoice_item AS a LEFT JOIN fn_fee_items AS b ON a.fn_fee_item_id = b.id LEFT JOIN fn_fee_item_type AS c ON b.fn_fee_item_type_id = c.id WHERE a.fn_fee_invoice_id = ' . $fn_fee_invoice_id . ' AND c.name = "Discount" ';
                                        $resultds = $connection2->query($sqlchkdis);
                                        $valueds = $resultds->fetch();
                                        $invoice_level_discount = 0;
                                        if (!empty($valueds)) {
                                            $invoice_level_discount = $valueds['invoice_level_discount'];
                                            $fnInvId = $fn_fee_invoice_id;

                                            if ($invoice_level_discount < $itemamount) {
                                                $itemamount = $itemamount - $invoice_level_discount;
                                                $itLevDis = 0;
                                                $discount_value = $invoice_level_discount;
                                            } else {
                                                $itLevDis = $invoice_level_discount - $itemamount;
                                                $discount_value = $itLevDis;
                                            }
                                        }
                                    }

                                    if (!empty($itLevDis)) {
                                        if ($itLevDis < $itemamount) {
                                            $itemamount = $itemamount - $itLevDis;
                                            $itLevDis = 0;
                                            $discount_value = $itLevDis;
                                        } else {
                                            $itLevDis = $itLevDis - $itemamount;
                                            $discount_value = $itLevDis;
                                        }
                                    }

                                    echo '<pre>';
                                    print_r($collData);
                                    if (!empty($collData['tot_amt_col'])) {
                                        $collAmt = $collData['tot_amt_col'];
                                        $collItemAmt = $collData['total_amount'];
                                        $pendAmt = $collItemAmt - $collAmt;
                                        echo 'collAmt' . $collAmt . '--- chkamount' . $chkamount . '-- pendamt' . $pendAmt;
                                        if ($pendAmt < $chkamount) {
                                            //$paidInv[] = $fn_fee_invoice_id;
                                            $status = '1';
                                            $sqdis = "SELECT * FROM fn_fee_item_level_discount WHERE pupilsightPersonID = " . $pupilsightPersonID . " AND item_id =  " . $itid . " AND fn_fee_invoice_id=" . $fn_fee_invoice_id . " AND status = '0' ";
                                            $resultdis = $connection2->query($sqdis);
                                            $valuedis = $resultdis->fetch();
                                            if (!empty($valuedis['discount'])) {
                                                $paidamount = $pendAmt - $valuedis['discount'];
                                                $chkamount = $chkamount - $paidamount;

                                                $datafu = array('status' => "1",  'pupilsightPersonID' => $pupilsightPersonID,  'item_id' => $itid, 'fn_fee_invoice_id' => $fn_fee_invoice_id);
                                                $sqlfu = 'UPDATE fn_fee_item_level_discount SET status=:status WHERE pupilsightPersonID=:pupilsightPersonID AND item_id=:item_id AND fn_fee_invoice_id=:fn_fee_invoice_id';
                                                $resultfu = $connection2->prepare($sqlfu);
                                                $resultfu->execute($datafu);
                                            } else {
                                                $paidamount = $pendAmt;
                                                $chkamount = $chkamount - $paidamount;
                                            }
                                            echo '</br>first' . $chkamount;
                                        } else {
                                            $status = '2';
                                            if ($chkamount > 0) {
                                                $paidamount = $chkamount;
                                            } else {
                                                $paidamount = '';
                                            }
                                            $chkamount = $chkamount - $paidamount;
                                        }
                                    } else {
                                        echo '</br>second' . $chkamount;
                                        if ($itemamount < $chkamount) {
                                            //$paidInv[] = $fn_fee_invoice_id;
                                            $status = '1';
                                            $sqdis = "SELECT * FROM fn_fee_item_level_discount WHERE pupilsightPersonID = " . $pupilsightPersonID . " AND item_id =  " . $itid . " AND fn_fee_invoice_id=" . $fn_fee_invoice_id . " AND status = '0' ";
                                            $resultdis = $connection2->query($sqdis);
                                            $valuedis = $resultdis->fetch();
                                            if (!empty($valuedis['discount'])) {
                                                $paidamount = $itemamount - $valuedis['discount'];
                                                $chkamount = $chkamount - $paidamount;

                                                $datafu = array('status' => "1",  'pupilsightPersonID' => $pupilsightPersonID,  'item_id' => $itid, 'fn_fee_invoice_id' => $fn_fee_invoice_id);
                                                $sqlfu = 'UPDATE fn_fee_item_level_discount SET status=:status WHERE pupilsightPersonID=:pupilsightPersonID AND item_id=:item_id AND fn_fee_invoice_id=:fn_fee_invoice_id';
                                                $resultfu = $connection2->prepare($sqlfu);
                                                $resultfu->execute($datafu);
                                            } else {
                                                $paidamount = $itemamount;
                                                $chkamount = $chkamount - $itemamount;
                                            }
                                            echo '</br>third' . $chkamount;
                                        } else {
                                            echo '</br>last1' . $chkamount;
                                            $status = '2';
                                            if ($chkamount > 0) {
                                                $paidamount = $chkamount;
                                            } else {
                                                $paidamount = '';
                                            }
                                            $chkamount = $chkamount - $itemamount;
                                            echo '</br>last' . $chkamount;
                                        }
                                    }


                                    // $leftAmt = $chkamount - $paidamount; 
                                    // $balanceAmt = $leftAmt;

                                    $datai = array('pupilsightPersonID' => $pupilsightPersonID, 'transaction_id' => $transactionId,  'fn_fees_invoice_id' => $fn_fee_invoice_id, 'fn_fee_invoice_item_id' => $itid, 'invoice_no' => $invoice_no, 'total_amount' => $itemamount, 'discount' => $discount_value, 'total_amount_collection' => $paidamount, 'status' => $status);
                                    print_r($datai);
                                    $sqli = 'INSERT INTO fn_fees_student_collection SET pupilsightPersonID=:pupilsightPersonID, transaction_id=:transaction_id, fn_fees_invoice_id=:fn_fees_invoice_id, fn_fee_invoice_item_id=:fn_fee_invoice_item_id, invoice_no=:invoice_no, total_amount=:total_amount, discount=:discount, total_amount_collection=:total_amount_collection, status=:status';
                                    $resulti = $connection2->prepare($sqli);
                                    $resulti->execute($datai);
                                }

                                $desql = 'SELECT id FROM fn_fees_deposit_account WHERE fn_fee_item_id = ' . $fn_fee_item_id . ' AND overpayment_account != "1" ';
                                $resultdp = $connection2->query($desql);
                                $depData = $resultdp->fetch();

                                if (!empty($depData)) {
                                    $deposit_account_id = $depData['id'];
                                    $datad = array('deposit_account_id' => $deposit_account_id, 'pupilsightPersonID' => $pupilsightPersonID, 'pupilsightSchoolYearID' => $pupilsightSchoolYearID,  'amount' => $itemamount, 'transaction_id' => $transactionId, 'status' => 'Credit', 'cdt' => $cdt);
                                    $sqld = 'INSERT INTO fn_fees_collection_deposit SET deposit_account_id=:deposit_account_id, pupilsightPersonID=:pupilsightPersonID, pupilsightSchoolYearID=:pupilsightSchoolYearID, amount=:amount, transaction_id=:transaction_id, status=:status, cdt=:cdt';
                                    $resultd = $connection2->prepare($sqld);
                                    $resultd->execute($datad);
                                }

                                //$i++;
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
                        if (!empty($invoice_item_id)) {
                            $itemId = explode(', ', $invoice_item_id);
                            $chkamount = $amtPaid;
                            foreach ($itemId as $itid) {
                                $dataf = array('id' => $itid, 'pupilsightPersonID' => $pupilsightPersonID);
                                $sqlf = 'SELECT a.fn_fee_invoice_id,a.total_amount,b.invoice_no, a.fn_fee_item_id FROM fn_fee_invoice_item AS a LEFT JOIN fn_fee_invoice_student_assign AS b ON a.fn_fee_invoice_id = b.fn_fee_invoice_id WHERE a.id=:id AND b.pupilsightPersonID=:pupilsightPersonID';
                                $resultf = $connection2->prepare($sqlf);
                                $resultf->execute($dataf);
                                $values = $resultf->fetch();
                                $fn_fee_invoice_id = $values['fn_fee_invoice_id'];
                                $invoice_no = $values['invoice_no'];
                                $itemamount = $values['total_amount'];
                                $fn_fee_item_id = $values['fn_fee_item_id'];

                                $chkcolsql = 'SELECT total_amount, SUM(total_amount_collection) as tot_amt_col FROM fn_fees_student_collection WHERE pupilsightPersonID = ' . $pupilsightPersonID . ' AND invoice_no = "' . $invoice_no . '" AND total_amount >= 0 AND is_active = "1" ';
                                $resultcolchk = $connection2->query($chkcolsql);
                                $collData = $resultcolchk->fetch();

                                /*
                                $chkpayitem = 'SELECT a.id, a.total_amount_collection FROM fn_fees_student_collection AS a LEFT JOIN fn_fees_collection AS b ON a.transaction_id = b.transaction_id WHERE a.fn_fees_invoice_id = ' . $fn_fee_invoice_id . ' AND a.fn_fee_invoice_item_id = ' . $itid . ' AND a.pupilsightPersonID = ' . $pupilsightPersonID . ' AND b.transaction_status = 1 ';
                                $resultcp = $connection2->query($chkpayitem);
                                $valuecp = $resultcp->fetch();
                                //echo $valuecp['total_amount_collection'].'---'.$itemamount.'</br>';
                                

                                if (!empty($valuecp)) {
                                    $paidAmt = $itemamount - $valuecp['total_amount_collection'];
                                    // $datai = array('partial_transaction_id' => $transactionId, 'total_amount_collection' => $itemamount, 'total_amount_partial_paid' => $paidAmt, 'status' => '1', 'id' => $valuecp['id']);
                                    // $sqli = 'UPDATE fn_fees_student_collection SET partial_transaction_id=:partial_transaction_id, total_amount_collection=:total_amount_collection, total_amount_partial_paid=:total_amount_partial_paid, status=:status WHERE id=:id';
                                    // $resulti = $connection2->prepare($sqli);
                                    // $resulti->execute($datai);

                                    $sqdis = "SELECT * FROM fn_fee_item_level_discount WHERE pupilsightPersonID = ".$pupilsightPersonID." AND item_id =  " . $itid . " ";
                                    $resultdis = $connection2->query($sqdis);
                                    $valuedis = $resultdis->fetch();
                                    if(!empty($valuedis)){
                                        $paidamount = $amtPaid - $valuedis['discount'];
                                    } else {
                                        $paidamount = $amtPaid;
                                    }
                                    
                                    $datai = array('pupilsightPersonID' => $pupilsightPersonID, 'transaction_id' => $transactionId,  'fn_fees_invoice_id' => $fn_fee_invoice_id, 'fn_fee_invoice_item_id' => $itid, 'invoice_no' => $invoice_no, 'total_amount' => $itemamount, 'total_amount_collection' => $paidamount, 'status' => '1');
                                    $sqli = 'INSERT INTO fn_fees_student_collection SET pupilsightPersonID=:pupilsightPersonID, transaction_id=:transaction_id, fn_fees_invoice_id=:fn_fees_invoice_id, fn_fee_invoice_item_id=:fn_fee_invoice_item_id, invoice_no=:invoice_no, total_amount=:total_amount, total_amount_collection=:total_amount_collection, status=:status';
                                    $resulti = $connection2->prepare($sqli);
                                    $resulti->execute($datai);
                                } else {

                                    $sqdis = "SELECT * FROM fn_fee_item_level_discount WHERE pupilsightPersonID = ".$pupilsightPersonID." AND item_id =  " . $itid . " ";
                                    $resultdis = $connection2->query($sqdis);
                                    $valuedis = $resultdis->fetch();
                                    if(!empty($valuedis)){
                                        $paidamount = $itemamount - $valuedis['discount'];
                                    } else {
                                        $paidamount = $itemamount;
                                    }
                                    
                                    $datai = array('pupilsightPersonID' => $pupilsightPersonID, 'transaction_id' => $transactionId,  'fn_fees_invoice_id' => $fn_fee_invoice_id, 'fn_fee_invoice_item_id' => $itid, 'invoice_no' => $invoice_no, 'total_amount' => $itemamount, 'total_amount_collection' => $paidamount, 'status' => '1');
                                    $sqli = 'INSERT INTO fn_fees_student_collection SET pupilsightPersonID=:pupilsightPersonID, transaction_id=:transaction_id, fn_fees_invoice_id=:fn_fees_invoice_id, fn_fee_invoice_item_id=:fn_fee_invoice_item_id, invoice_no=:invoice_no, total_amount=:total_amount, total_amount_collection=:total_amount_collection, status=:status';
                                    $resulti = $connection2->prepare($sqli);
                                    $resulti->execute($datai);
                                }
                                */

                                if (!empty($collData['tot_amt_col'])) {
                                    $collAmt = $collData['tot_amt_col'];
                                    $collItemAmt = $collData['total_amount'];
                                    $pendAmt = $collItemAmt - $collAmt;
                                    echo 'collAmt' . $collAmt . '--- chkamount' . $chkamount . '-- pendamt' . $pendAmt;
                                    if ($pendAmt < $chkamount) {
                                        //$paidInv[] = $fn_fee_invoice_id;
                                        $status = '1';
                                        $sqdis = "SELECT * FROM fn_fee_item_level_discount WHERE pupilsightPersonID = " . $pupilsightPersonID . " AND item_id =  " . $itid . " AND fn_fee_invoice_id=" . $fn_fee_invoice_id . " AND status = '0' ";
                                        $resultdis = $connection2->query($sqdis);
                                        $valuedis = $resultdis->fetch();
                                        if (!empty($valuedis['discount'])) {
                                            $paidamount = $pendAmt - $valuedis['discount'];
                                            $chkamount = $chkamount - $paidamount;

                                            $datafu = array('status' => "1",  'pupilsightPersonID' => $pupilsightPersonID,  'item_id' => $itid, 'fn_fee_invoice_id' => $fn_fee_invoice_id);
                                            $sqlfu = 'UPDATE fn_fee_item_level_discount SET status=:status WHERE pupilsightPersonID=:pupilsightPersonID AND item_id=:item_id AND fn_fee_invoice_id=:fn_fee_invoice_id';
                                            $resultfu = $connection2->prepare($sqlfu);
                                            $resultfu->execute($datafu);
                                        } else {
                                            $paidamount = $pendAmt;
                                            $chkamount = $chkamount - $paidamount;
                                        }
                                        echo '</br>first' . $chkamount;
                                    } else {
                                        $status = '2';
                                        if ($chkamount > 0) {
                                            $paidamount = $chkamount;
                                        } else {
                                            $paidamount = '';
                                        }
                                        $chkamount = $chkamount - $paidamount;
                                    }
                                } else {
                                    echo '</br>second' . $chkamount;
                                    if ($itemamount < $chkamount) {
                                        //$paidInv[] = $fn_fee_invoice_id;
                                        $status = '1';
                                        $sqdis = "SELECT * FROM fn_fee_item_level_discount WHERE pupilsightPersonID = " . $pupilsightPersonID . " AND item_id =  " . $itid . " AND fn_fee_invoice_id=" . $fn_fee_invoice_id . " AND status = '0' ";
                                        $resultdis = $connection2->query($sqdis);
                                        $valuedis = $resultdis->fetch();
                                        if (!empty($valuedis['discount'])) {
                                            $paidamount = $itemamount - $valuedis['discount'];
                                            $chkamount = $chkamount - $paidamount;

                                            $datafu = array('status' => "1",  'pupilsightPersonID' => $pupilsightPersonID,  'item_id' => $itid, 'fn_fee_invoice_id' => $fn_fee_invoice_id);
                                            $sqlfu = 'UPDATE fn_fee_item_level_discount SET status=:status WHERE pupilsightPersonID=:pupilsightPersonID AND item_id=:item_id AND fn_fee_invoice_id=:fn_fee_invoice_id';
                                            $resultfu = $connection2->prepare($sqlfu);
                                            $resultfu->execute($datafu);
                                        } else {
                                            $paidamount = $itemamount;
                                            $chkamount = $chkamount - $itemamount;
                                        }
                                        echo '</br>third' . $chkamount;
                                    } else {
                                        echo '</br>last1' . $chkamount;
                                        $status = '2';
                                        if ($chkamount > 0) {
                                            $paidamount = $chkamount;
                                        } else {
                                            $paidamount = '';
                                        }
                                        $chkamount = $chkamount - $itemamount;
                                        echo '</br>last' . $chkamount;
                                    }
                                }

                                $datai = array('pupilsightPersonID' => $pupilsightPersonID, 'transaction_id' => $transactionId,  'fn_fees_invoice_id' => $fn_fee_invoice_id, 'fn_fee_invoice_item_id' => $itid, 'invoice_no' => $invoice_no, 'total_amount' => $itemamount, 'total_amount_collection' => $paidamount, 'status' => '1');
                                $sqli = 'INSERT INTO fn_fees_student_collection SET pupilsightPersonID=:pupilsightPersonID, transaction_id=:transaction_id, fn_fees_invoice_id=:fn_fees_invoice_id, fn_fee_invoice_item_id=:fn_fee_invoice_item_id, invoice_no=:invoice_no, total_amount=:total_amount, total_amount_collection=:total_amount_collection, status=:status';
                                $resulti = $connection2->prepare($sqli);
                                $resulti->execute($datai);

                                $desql = 'SELECT id FROM fn_fees_deposit_account WHERE fn_fee_item_id = ' . $fn_fee_item_id . ' AND overpayment_account = "0" ';
                                $resultdp = $connection2->query($desql);
                                $depData = $resultdp->fetch();

                                if (!empty($depData)) {
                                    $deposit_account_id = $depData['id'];
                                    $datad = array('deposit_account_id' => $deposit_account_id, 'pupilsightPersonID' => $pupilsightPersonID, 'pupilsightSchoolYearID' => $pupilsightSchoolYearID,  'amount' => $itemamount, 'transaction_id' => $transactionId, 'status' => 'Credit', 'cdt' => $cdt);
                                    $sqld = 'INSERT INTO fn_fees_collection_deposit SET deposit_account_id=:deposit_account_id, pupilsightPersonID=:pupilsightPersonID, pupilsightSchoolYearID=:pupilsightSchoolYearID, amount=:amount, transaction_id=:transaction_id, status=:status, cdt=:cdt';
                                    $resultd = $connection2->prepare($sqld);
                                    $resultd->execute($datad);
                                }
                            }
                        }
                    }
                    //die();



                    if (!empty($deposit)) {
                        $desql = 'SELECT id FROM fn_fees_deposit_account WHERE overpayment_account = "1" ';
                        $resultcp = $connection2->query($desql);
                        $valuecp = $resultcp->fetch();
                        $deposit_account_id = $valuecp['id'];

                        $datad = array('deposit_account_id' => $deposit_account_id, 'pupilsightPersonID' => $pupilsightPersonID, 'pupilsightSchoolYearID' => $pupilsightSchoolYearID,  'amount' => $deposit, 'transaction_id' => $transactionId, 'status' => 'Credit', 'cdt' => $cdt);
                        $sqld = 'INSERT INTO fn_fees_collection_deposit SET deposit_account_id=:deposit_account_id, pupilsightPersonID=:pupilsightPersonID, pupilsightSchoolYearID=:pupilsightSchoolYearID, amount=:amount, transaction_id=:transaction_id, status=:status, cdt=:cdt';
                        $resultd = $connection2->prepare($sqld);
                        $resultd->execute($datad);
                    }

                    if (!empty($over_payment)) {
                        $desql = 'SELECT id FROM fn_fees_deposit_account WHERE overpayment_account = "1" ';
                        $resultcp = $connection2->query($desql);
                        $valuecp = $resultcp->fetch();
                        $deposit_account_id = $valuecp['id'];

                        $datad = array('deposit_account_id' => $deposit_account_id, 'pupilsightPersonID' => $pupilsightPersonID, 'pupilsightSchoolYearID' => $pupilsightSchoolYearID,  'amount' => $over_payment, 'transaction_id' => $transactionId, 'status' => 'Debit', 'cdt' => $cdt);
                        $sqld = 'INSERT INTO fn_fees_collection_deposit SET deposit_account_id=:deposit_account_id, pupilsightPersonID=:pupilsightPersonID, pupilsightSchoolYearID=:pupilsightSchoolYearID, amount=:amount, transaction_id=:transaction_id, status=:status, cdt=:cdt';
                        $resultd = $connection2->prepare($sqld);
                        $resultd->execute($datad);
                    }

                    if (!empty($deposit_fee_item_account_id)) {
                        $datad = array('deposit_account_id' => $deposit_fee_item_account_id, 'pupilsightPersonID' => $pupilsightPersonID, 'pupilsightSchoolYearID' => $pupilsightSchoolYearID,  'amount' => $amount_paying, 'transaction_id' => $transactionId, 'status' => 'Debit', 'cdt' => $cdt);
                        $sqld = 'INSERT INTO fn_fees_collection_deposit SET deposit_account_id=:deposit_account_id, pupilsightPersonID=:pupilsightPersonID, pupilsightSchoolYearID=:pupilsightSchoolYearID, amount=:amount, transaction_id=:transaction_id, status=:status, cdt=:cdt';
                        $resultd = $connection2->prepare($sqld);
                        $resultd->execute($datad);
                    }

                    if ($checkmode == 'multiple') {
                        $mdata = $session->get('m_data');
                        // savePaymentModeData($transactionId, $mdata);
                        $t_id = $transactionId;

                        $pmode = $mdata['payment_mode_id'];
                        //$mcredit = $mdata['credit_id'];
                        $bank_id = $mdata['bank_id'];
                        $amount = $mdata['amount'];
                        $mrefno = $mdata['reference_no'];
                        $minstruDate = $mdata['instrument_date'];
                        $l = sizeof($pmode);
                        $i = 1;
                        for ($i = 0; $i < $l; $i++) {
                            $datam = array('transaction_id' => $t_id, 'payment_mode_id' => $pmode[$i],  'bank_id' => $bank_id[$i],    'amount' => $amount[$i], 'reference_no' => $mrefno[$i], 'instrument_date' => $minstruDate[$i]);
                            $sqlm = 'INSERT INTO fn_multi_payment_mode SET transaction_id=:transaction_id, payment_mode_id=:payment_mode_id, bank_id=:bank_id,amount=:amount,reference_no=:reference_no,instrument_date=:instrument_date';
                            $resultm = $connection2->prepare($sqlm);
                            $resultm->execute($datam);

                            if (!empty($mrefno[$i])) {
                                $instrument_no = $mrefno[$i];
                            }

                            if (!empty($minstruDate[$i])) {
                                $instrument_date = $minstruDate[$i];
                            }

                            if (!empty($bank_id[$i])) {
                                $sqlbn = 'SELECT name FROM fn_masters WHERE id = ' . $bank_id[$i] . ' ';
                                $resultbn = $connection2->query($sqlbn);
                                $bankNameData = $resultbn->fetch();
                                $bank_name = $bankNameData['name'];
                            } else {
                                $bank_name = '';
                            }
                        }

                        $pmId = implode(',', $pmode);
                        $sqlpt = "SELECT GROUP_CONCAT(name) AS modeName FROM fn_masters WHERE id IN (" . $pmId . ") ";
                        $resultpt = $connection2->query($sqlpt);
                        $valuept = $resultpt->fetch();
                        $paymentModeName = 'Multiple (' . $valuept['modeName'] . ')';
                    } else {
                        $sqlpt = "SELECT name FROM fn_masters WHERE id = " . $payment_mode_id . " ";
                        $resultpt = $connection2->query($sqlpt);
                        $valuept = $resultpt->fetch();
                        $paymentModeName = $valuept['name'];
                    }


                    $chkcussql = 'SELECT field_name FROM custom_field WHERE field_name = "correspondence_address" ';
                    $chkresultstu = $connection2->query($chkcussql);
                    $custDataChk = $chkresultstu->fetch();
                    if (!empty($custDataChk)) {
                        $fieldName = ', a.correspondence_address';
                    } else {
                        $fieldName = '';
                    }

                    $sqlstu = "SELECT a.officialName , a.admission_no, a.roll_no, sc.name as academic_year, p.name as progname, b.name as class, c.name as section " . $fieldName . " FROM pupilsightPerson AS a LEFT JOIN pupilsightStudentEnrolment AS d ON a.pupilsightPersonID = d.pupilsightPersonID LEFT JOIN pupilsightSchoolYear AS sc ON d.pupilsightSchoolYearID = sc.pupilsightSchoolYearID LEFT JOIN pupilsightProgram AS p ON d.pupilsightProgramID = p.pupilsightProgramID LEFT JOIN pupilsightYearGroup AS b ON d.pupilsightYearGroupID = b.pupilsightYearGroupID LEFT JOIN pupilsightRollGroup AS c ON d.pupilsightRollGroupID = c.pupilsightRollGroupID WHERE a.pupilsightPersonID = " . $pupilsightPersonID . " AND d.pupilsightSchoolYearID = " . $pupilsightSchoolYearID . " ";
                    $resultstu = $connection2->query($sqlstu);
                    $valuestu = $resultstu->fetch();

                    $academic_year = $valuestu['academic_year'];

                    $sqlfat = "SELECT b.officialName , b.phone1, b.email FROM pupilsightFamilyRelationship AS a LEFT JOIN pupilsightPerson AS b ON a.pupilsightPersonID1 = b.pupilsightPersonID WHERE a.pupilsightPersonID2 = " . $pupilsightPersonID . " AND a.relationship = 'Father' ";
                    $resultfat = $connection2->query($sqlfat);
                    $valuefat = $resultfat->fetch();

                    $father_name = '';
                    $father_email = '';
                    $father_phone = '';
                    if (!empty($valuefat)) {
                        $father_name = $valuefat['officialName'];
                        $father_email = $valuefat['email'];
                        $father_phone = $valuefat['phone1'];
                    }

                    $sqlmot = "SELECT b.officialName , b.phone1, b.email FROM pupilsightFamilyRelationship AS a LEFT JOIN pupilsightPerson AS b ON a.pupilsightPersonID1 = b.pupilsightPersonID WHERE a.pupilsightPersonID2 = " . $pupilsightPersonID . " AND a.relationship = 'Mother' ";
                    $resultmot = $connection2->query($sqlmot);
                    $valuemot = $resultmot->fetch();

                    $mother_name = '';
                    $mother_email = '';
                    $mother_phone = '';
                    if (!empty($valuemot)) {
                        $mother_name = $valuemot['officialName'];
                        $mother_email = $valuemot['email'];
                        $mother_phone = $valuemot['phone1'];
                    }


                    $sqlinv = 'SELECT GROUP_CONCAT(DISTINCT b.invoice_no) AS invNo, b.*, GROUP_CONCAT(c.title) AS invtitle, c.cdt, c.due_date FROM fn_fee_invoice_item AS a LEFT JOIN fn_fee_invoice_student_assign AS b ON a.fn_fee_invoice_id = b.fn_fee_invoice_id LEFT JOIN fn_fee_invoice AS c ON a.fn_fee_invoice_id = c.id WHERE a.id IN (' . $invoice_item_id . ') AND b.pupilsightPersonID = ' . $pupilsightPersonID . '  ORDER BY b.id ASC';
                    $resultinv = $connection2->query($sqlinv);
                    $valueinv = $resultinv->fetch();

                    $invNo = $valueinv['invNo'];
                    $inv_title = $valueinv['invtitle'];
                    $inv_date = '';
                    if (!empty($valueinv['cdt'])) {
                        $inv_date = date('d/m/Y', strtotime($valueinv['cdt']));
                    }

                    $due_date = '';
                    if (!empty($valueinv['due_date']) && $valueinv['due_date'] != '1970-01-01') {
                        $due_date = date('d/m/Y', strtotime($valueinv['due_date']));
                    }




                    $class_section = $valuestu["class"] . " - " . $valuestu["section"];
                    $class_name = $valuestu["class"];
                    $section_name = $valuestu["section"];
                    $payment_receipt_date = date('d-m-Y', strtotime($payment_date));
                    if (!empty($instrument_date)) {
                        $instrument_receipt_date = date('d-m-Y', strtotime($instrument_date));
                    } else {
                        $instrument_receipt_date = '';
                    }

                    if (!empty($custDataChk)) {
                        $coreaddress = $valuestu["correspondence_address"];
                    } else {
                        $coreaddress = '';
                    }


                    $stuName = str_replace(' ', '_', $valuestu["officialName"]);
                    $filename = $stuName . '_' . $transactionId;
                    $session->forget(['doc_receipt_id']);
                    $session->set('doc_receipt_id', $filename);
                    $total = 0;
                    $totalTax = 0;
                    $totalamtWitoutTaxDis = 0;
                    $totalPending = 0;
                    $totalDiscount = 0;
                    if (!empty($invoice_id)) {
                        $invid = explode(',', $invoice_id);
                        $invKount = count($invid);
                        $first = reset($invid);
                        $last = end($invid);
                        if ($invKount > 1) {
                            $idsInv = $first . ',' . $last;
                        } else {
                            $idsInv = $first;
                        }

                        $sqlconInv = 'SELECT GROUP_CONCAT(title SEPARATOR " - ") AS invtitle FROM fn_fee_invoice WHERE id IN (' . $idsInv . ')';
                        $resultConInv = $connection2->query($sqlconInv);
                        $valueConInv = $resultConInv->fetch();
                        $concatInvoiceTitle = $valueConInv['invtitle'];

                        $concatInvId = array();

                        $cnt = 1;
                        foreach ($invid as $iid) {
                            // $datau = array('invoice_status' => $invoice_status, 'fn_fees_invoice_id' => $iid,  'pupilsightPersonID' => $pupilsightPersonID);
                            // $sqlu = 'UPDATE fn_fees_collection SET invoice_status=:invoice_status WHERE fn_fees_invoice_id=:fn_fees_invoice_id AND pupilsightPersonID=:pupilsightPersonID';
                            // $resultu = $connection2->prepare($sqlu);
                            // $resultu->execute($datau);

                            $dataiu = array('invoice_status' => $invoice_status,  'pupilsightPersonID' => $pupilsightPersonID,  'fn_fee_invoice_id' => $iid);
                            $sqliu = 'UPDATE fn_fee_invoice_student_assign SET invoice_status=:invoice_status WHERE pupilsightPersonID=:pupilsightPersonID AND fn_fee_invoice_id=:fn_fee_invoice_id';
                            $resultiu = $connection2->prepare($sqliu);
                            $resultiu->execute($dataiu);

                            $chksql = 'SELECT fn_fee_structure_id, display_fee_item, title as invoice_title, is_concat_invoice FROM fn_fee_invoice WHERE id = ' . $iid . ' ';
                            $resultchk = $connection2->query($chksql);
                            $valuechk = $resultchk->fetch();
                            $is_concat_invoice = $valuechk['is_concat_invoice'];

                            $chkinvno = 'SELECT invoice_no FROM fn_fee_invoice_student_assign WHERE pupilsightPersonID = ' . $pupilsightPersonID . ' AND fn_fee_invoice_id = ' . $iid . ' ';
                            $resultchkinvno = $connection2->query($chkinvno);
                            $valueresultchkinvno = $resultchkinvno->fetch();
                            $stu_inv_no = $valueresultchkinvno['invoice_no'];

                            if ($is_concat_invoice == '1') {
                                $concatInvId[] = $iid;
                            } else {
                                if (!empty($valuechk['fn_fee_structure_id'])) {
                                    $chsql = 'SELECT b.invoice_title, a.display_fee_item FROM fn_fee_invoice AS a LEFT JOIN fn_fee_structure AS b ON a.fn_fee_structure_id = b.id WHERE a.id= ' . $iid . ' AND a.fn_fee_structure_id IS NOT NULL ';
                                    $resultch = $connection2->query($chsql);
                                    $valuech = $resultch->fetch();
                                } else {
                                    $valuech = $valuechk;
                                }

                                if ($valuech['display_fee_item'] == '2') {
                                    $sqcs = "select SUM(fi.total_amount) AS tamnt, SUM(fi.amount) AS amnt, SUM(fi.tax) AS ttax from fn_fee_invoice_item as fi, fn_fee_items as items where fi.fn_fee_item_id = items.id and fi.fn_fee_invoice_id =  " . $iid . " ";
                                    $resultfi = $connection2->query($sqcs);
                                    $valuefi = $resultfi->fetchAll();
                                    if (!empty($valuefi)) {
                                        //$cnt = 1;
                                        foreach ($valuefi as $vfi) {
                                            $sqcol = "SELECT SUM(total_amount) AS tamntCol , SUM(discount) AS disCol, SUM(total_amount_collection) AS ttamtCol, SUM(total_amount_partial_paid) AS ttamtPartCol FROM fn_fees_student_collection WHERE fn_fees_invoice_id = " . $iid . " AND ( transaction_id = " . $transactionId . " OR partial_transaction_id = " . $transactionId . " ) AND total_amount >=0 AND is_active = '1' ";
                                            $resultcol = $connection2->query($sqcol);
                                            $valuecol = $resultcol->fetch();


                                            $sqcol1 = "SELECT SUM(total_amount_collection) AS tot_coll FROM fn_fees_student_collection WHERE fn_fees_invoice_id = " . $iid . " AND invoice_no = '" . $stu_inv_no . "' AND is_active = '1' ";
                                            $resultcol1 = $connection2->query($sqcol1);
                                            $valuecol1 = $resultcol1->fetch();
                                            $tot_coll = $valuecol1['tot_coll'];

                                            $itemAmt    = $valuecol["tamntCol"];
                                            $itemAmtCol = $valuecol["ttamtCol"];
                                            //$itemAmtCol = $tot_coll;
                                            $ttamtPartCol = $valuecol["ttamtPartCol"];
                                            if (!empty($ttamtPartCol)) {
                                                $itemAmtCol = $ttamtPartCol;
                                            }

                                            $sqitid = "SELECT GROUP_CONCAT(id) AS itmIds FROM fn_fee_invoice_item WHERE fn_fee_invoice_id = " . $iid . " ";
                                            $resultitid = $connection2->query($sqitid);
                                            $valueitid = $resultitid->fetch();
                                            $itmIDS = $valueitid['itmIds'];

                                            $sqdis = "SELECT SUM(discount) AS dis FROM fn_fee_item_level_discount WHERE pupilsightPersonID = " . $pupilsightPersonID . " AND item_id IN (" . $itmIDS . ") ";
                                            $resultdis = $connection2->query($sqdis);
                                            $valuedis = $resultdis->fetch();
                                            $disItemAmt = 0;

                                            if (!empty($ttamtPartCol)) {
                                                $itemAmtPen = $itemAmt - $valuecol["ttamtCol"];
                                            } else {
                                                if (!empty($valuedis)) {
                                                    $disItemAmt = $valuedis['dis'];
                                                    // $newItemAmtCol = $itemAmtCol + $disItemAmt;
                                                    // $itemAmtPen = $itemAmt - $newItemAmtCol;
                                                    $newItemAmtCol = $tot_coll + $disItemAmt;
                                                    $itemAmtPen = $itemAmt - $newItemAmtCol;
                                                    echo '1--' . $itemAmtPen . '---itemAmt' . $itemAmt . '---tot_coll' . $tot_coll . '</br>';
                                                } else {
                                                    $disItemAmt = 0;
                                                    //$itemAmtPen = $itemAmt - $itemAmtCol;
                                                    $itemAmtPen = $itemAmt - $tot_coll;
                                                    echo '1--' . $itemAmtPen . '---itemAmt' . $itemAmt . '---tot_coll' . $tot_coll . '</br>';
                                                }
                                            }


                                            $taxamt = 0;
                                            if (!empty($vfi["ttax"])) {
                                                $taxamt = ($vfi["ttax"] / 100) * $vfi["amnt"];
                                                $taxamt = number_format($taxamt, 2, '.', '');
                                            }
                                            $dts_receipt_feeitem[] = array(
                                                "serial.all" => $cnt,
                                                "particulars.all" => htmlspecialchars(trim($valuech['invoice_title'])),
                                                "inv_amt.all" => $vfi["amnt"],
                                                "tax.all" => $taxamt,
                                                "amount.all" => $vfi["tamnt"],
                                                "inv_amt_paid.all" => number_format($itemAmtCol, 2),
                                                "inv_amt_pending.all" => number_format($itemAmtPen, 2),
                                                "inv_amt_discount.all" => number_format($disItemAmt, 2)
                                            );
                                            $total += $vfi["tamnt"];
                                            $totalTax += $taxamt;
                                            $totalamtWitoutTaxDis += $vfi["amnt"];
                                            $totalPending += $itemAmtPen;
                                            $totalDiscount += $disItemAmt;
                                            $cnt++;
                                        }
                                    }
                                } else {
                                    $sqcs = "select fi.total_amount, fi.amount, fi.tax, fi.id, items.name from fn_fee_invoice_item as fi, fn_fee_items as items where fi.fn_fee_item_id = items.id and fi.fn_fee_invoice_id =  " . $iid . " and fi.id in(" . $invoice_item_id . ")  ";
                                    $resultfi = $connection2->query($sqcs);
                                    $valuefi = $resultfi->fetchAll();

                                    if (!empty($valuefi)) {
                                        //$cnt = 1;
                                        foreach ($valuefi as $vfi) {
                                            $sqcol = "SELECT * FROM fn_fees_student_collection WHERE fn_fees_invoice_id = " . $iid . " AND fn_fee_invoice_item_id =  " . $vfi["id"] . " AND ( transaction_id = " . $transactionId . " OR partial_transaction_id = " . $transactionId . " ) AND total_amount >= 0  AND is_active = '1' ";
                                            $resultcol = $connection2->query($sqcol);
                                            $valuecol = $resultcol->fetch();

                                            $sqcol1 = "SELECT SUM(total_amount_collection) AS tot_coll FROM fn_fees_student_collection WHERE fn_fees_invoice_id = " . $iid . " AND invoice_no = '" . $stu_inv_no . "' AND is_active = '1' ";
                                            $resultcol1 = $connection2->query($sqcol1);
                                            $valuecol1 = $resultcol1->fetch();
                                            $tot_coll = $valuecol1['tot_coll'];


                                            $itemAmt    = $valuecol["total_amount"];
                                            $itemAmtCol = $valuecol["total_amount_collection"];
                                            $itemAmtCol = $tot_coll;
                                            $ttamtPartCol = $valuecol["total_amount_partial_paid"];
                                            if (!empty($ttamtPartCol)) {
                                                $itemAmtCol = $ttamtPartCol;
                                            }

                                            $sqdis = "SELECT * FROM fn_fee_item_level_discount WHERE pupilsightPersonID = " . $pupilsightPersonID . " AND item_id =  " . $vfi["id"] . " ";
                                            $resultdis = $connection2->query($sqdis);
                                            $valuedis = $resultdis->fetch();
                                            $disItemAmt = 0;

                                            if (!empty($ttamtPartCol)) {
                                                $itemAmtPen = $itemAmt - $valuecol["total_amount_collection"];
                                            } else {
                                                if (!empty($valuedis)) {
                                                    $disItemAmt = $valuedis['discount'];
                                                    // $newItemAmtCol = $itemAmtCol + $disItemAmt;
                                                    // $itemAmtPen = $itemAmt - $newItemAmtCol;
                                                    $newItemAmtCol = $tot_coll + $disItemAmt;
                                                    $itemAmtPen = $itemAmt - $newItemAmtCol;
                                                } else {
                                                    $disItemAmt = 0;
                                                    //$itemAmtPen = $itemAmt - $itemAmtCol;
                                                    $itemAmtPen = $itemAmt - $tot_coll;
                                                }
                                            }




                                            $taxamt = '0';
                                            if (!empty($vfi["tax"])) {
                                                $taxamt = ($vfi["tax"] / 100) * $vfi["amount"];
                                                $taxamt = number_format($taxamt, 2, '.', '');
                                            }
                                            $dts_receipt_feeitem[] = array(
                                                "serial.all" => $cnt,
                                                "particulars.all" => htmlspecialchars(trim($vfi["name"])),
                                                "inv_amt.all" => $vfi["amount"],
                                                "tax.all" => $taxamt,
                                                "amount.all" => $vfi["total_amount"],
                                                "inv_amt_paid.all" => number_format($itemAmtCol, 2),
                                                "inv_amt_pending.all" => number_format($itemAmtPen, 2),
                                                "inv_amt_discount.all" => number_format($disItemAmt, 2)
                                            );
                                            $total += $vfi["total_amount"];
                                            $totalTax += $taxamt;
                                            $totalamtWitoutTaxDis += $vfi["amount"];
                                            $totalPending += $itemAmtPen;
                                            $totalDiscount += $disItemAmt;
                                            $cnt++;
                                        }
                                    }
                                }
                            }
                        }
                    }


                    if (!empty($concatInvId)) {

                        $invKountCon = count($concatInvId);
                        $firstInv = reset($concatInvId);
                        $lastInv = end($concatInvId);
                        if ($invKountCon > 1) {
                            $idsInvCon = $firstInv . ',' . $lastInv;
                        } else {
                            $idsInvCon = $firstInv;
                        }

                        $sqlconInvNew = 'SELECT GROUP_CONCAT(title SEPARATOR " - ") AS invtitle FROM fn_fee_invoice WHERE id IN (' . $idsInvCon . ')';
                        $resultConInvNew = $connection2->query($sqlconInvNew);
                        $valueConInvNew = $resultConInvNew->fetch();
                        $concatInvTitle = $valueConInvNew['invtitle'];

                        $invconids = implode(',', $concatInvId);
                        $sqcs = "select SUM(fi.total_amount) AS tamnt, SUM(fi.amount) AS amnt, SUM(fi.tax) AS ttax from fn_fee_invoice_item as fi, fn_fee_items as items where fi.fn_fee_item_id = items.id and fi.fn_fee_invoice_id IN  (" . $invconids . ") ";
                        $resultfi = $connection2->query($sqcs);
                        $valuefi = $resultfi->fetch();

                        $taxamt = 0;
                        if (!empty($valuefi["ttax"])) {
                            $taxamt = ($valuefi["ttax"] / 100) * $valuefi["amnt"];
                            $taxamt = number_format($taxamt, 2, '.', '');
                        }

                        if (!empty($dts_receipt_feeitem)) {
                            $kountRow = count($dts_receipt_feeitem);
                        } else {
                            $kountRow = 0;
                        }

                        $sqcol = "SELECT SUM(total_amount) AS tamntCol , SUM(discount) AS disCol, SUM(total_amount_collection) AS ttamtCol FROM fn_fees_student_collection WHERE fn_fees_invoice_id IN  (" . $invconids . ") AND ( transaction_id = " . $transactionId . " OR partial_transaction_id = " . $transactionId . " ) AND is_active = '1' ";
                        $resultcol = $connection2->query($sqcol);
                        $valuecol = $resultcol->fetch();
                        $itemAmt    = $valuecol["tamntCol"];
                        $itemAmtCol = $valuecol["ttamtCol"];

                        $sqitid = "SELECT GROUP_CONCAT(id) AS itmIds FROM fn_fee_invoice_item WHERE fn_fee_invoice_id IN  (" . $invconids . ") ";
                        $resultitid = $connection2->query($sqitid);
                        $valueitid = $resultitid->fetch();
                        $itmIDS = $valueitid['itmIds'];

                        $sqdis = "SELECT SUM(discount) AS dis FROM fn_fee_item_level_discount WHERE pupilsightPersonID = " . $pupilsightPersonID . " AND item_id IN (" . $itmIDS . ") ";
                        $resultdis = $connection2->query($sqdis);
                        $valuedis = $resultdis->fetch();
                        $disItemAmt = 0;
                        if (!empty($valuedis)) {
                            $disItemAmt = $valuedis['dis'];
                            $newItemAmtCol = $itemAmtCol + $disItemAmt;
                            $itemAmtPen = $itemAmt - $newItemAmtCol;
                        } else {
                            $disItemAmt = 0;
                            $itemAmtPen = $itemAmt - $itemAmtCol;
                        }


                        $itemAmtPen = $itemAmt - $itemAmtCol;

                        $dts_receipt_feeitem1 = array(
                            "serial.all" => $kountRow + 1,
                            "particulars.all" => htmlspecialchars(trim($concatInvTitle)),
                            "inv_amt.all" => $valuefi["amnt"],
                            "tax.all" => $taxamt,
                            "amount.all" => $valuefi["tamnt"],
                            "inv_amt_paid.all" => number_format($itemAmtCol, 2),
                            "inv_amt_pending.all" => number_format($itemAmtPen, 2),
                            "inv_amt_discount.all" => number_format($disItemAmt, 2)
                        );
                        $total = $total + $valuefi["tamnt"];
                        $totalTax = $totalTax + $taxamt;
                        $totalamtWitoutTaxDis = $totalamtWitoutTaxDis + $valuefi["amnt"];

                        if (!empty($dts_receipt_feeitem)) {
                            array_push($dts_receipt_feeitem, $dts_receipt_feeitem1);
                        } else {
                            $dts_receipt_feeitem[] = $dts_receipt_feeitem1;
                        }
                    }

                    $dts_receipt = array(
                        "academic_year" => $academic_year,
                        "invoice_no" => $invNo,
                        "receipt_no" => $receipt_number,
                        "date" => $payment_receipt_date,
                        "student_name" => $valuestu["officialName"],
                        "student_id" => $pupilsightPersonID,
                        "admission_no" => $valuestu["admission_no"],
                        "roll_no" => $valuestu["roll_no"],
                        "father_name" => $father_name,
                        "mother_name" => $mother_name,
                        "program_name" => $valuestu["progname"],
                        "class_section" => $class_section,
                        "class_name" => $class_name,
                        "section_name" => $section_name,
                        "instrument_date" => $instrument_receipt_date,
                        "instrument_no" => $instrument_no,
                        "transcation_amount" => number_format($amount_paying, 2, '.', ''),
                        "fine_amount" => $fine,
                        "other_amount" => "NA",
                        "pay_mode" => $paymentModeName,
                        "transactionId" => $transactionId,
                        "receiptTemplate" => $receiptTemplate,
                        "bank_name" => $bank_name,
                        "fee_head_acc_no" => $fee_head_acc_no,
                        "column_start_by" => $column_start_by,
                        "address" => htmlspecialchars($coreaddress),
                        "inv_title" => htmlspecialchars($inv_title),
                        "inv_date" => $inv_date,
                        "due_date" => $due_date,
                        "total_tax" => number_format($totalTax, 2, '.', ''),
                        "inv_total" => number_format($totalamtWitoutTaxDis, 2, '.', ''),
                        "concat_invoice_title" => htmlspecialchars($concatInvoiceTitle),
                        "total_amount_discount" => number_format($discount, 2, '.', ''),
                        "total_amount_pending" => number_format($totalPending, 2, '.', ''),
                        "remarks" => $remarks
                    );

                    // echo '<pre>';
                    // print_r($dts_receipt_feeitem);
                    // print_r($dts_receipt);
                    // echo '</pre>';
                    // die();

                    if (!empty($dts_receipt) && !empty($dts_receipt_feeitem) && !empty($receiptTemplate)) {
                        $callback = $_SESSION[$guid]['absoluteURL'] . '/thirdparty/phpword/receiptNew.php';
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
                        ));
                        $context  = stream_context_create($opts);
                        $result = file_get_contents($callback, false, $context);
                    }
                    echo 1;
                } catch (PDOException $e) {
                    echo $e;
                    echo "Internal server error";
                    //$URL .= '&return=error9';
                    //header("Location: {$URL}");
                    exit();
                }
            }
            break;
        case "loadInvoicesCollections":
            $stuId = $_POST['val'];
            $pupilsightSchoolYearID = $_POST['py'];
            $invoices = 'SELECT fn_fee_invoice.*,fn_fee_invoice.id as invoiceid, fn_fee_invoice_student_assign.invoice_no as stu_invoice_no, fn_fee_invoice_student_assign.id as invid, g.is_fine_editable, g.fine_type, g.rule_type, GROUP_CONCAT(DISTINCT asg.route_id) as routes, GROUP_CONCAT(DISTINCT asg.transport_type) as routetype FROM fn_fee_invoice LEFT JOIN pupilsightStudentEnrolment ON fn_fee_invoice.pupilsightSchoolYearID=pupilsightStudentEnrolment.pupilsightSchoolYearID LEFT JOIN pupilsightPerson ON pupilsightStudentEnrolment.pupilsightPersonID=pupilsightPerson.pupilsightPersonID RIGHT JOIN fn_fee_invoice_student_assign ON pupilsightPerson.pupilsightPersonID=fn_fee_invoice_student_assign.pupilsightPersonID AND fn_fee_invoice.id = fn_fee_invoice_student_assign.fn_fee_invoice_id LEFT JOIN fn_fees_fine_rule AS g ON fn_fee_invoice.fn_fees_fine_rule_id = g.id LEFT JOIN trans_route_assign AS asg ON pupilsightPerson.pupilsightPersonID = asg.pupilsightPersonID WHERE fn_fee_invoice.pupilsightSchoolYearID = "' . $pupilsightSchoolYearID . '" AND pupilsightPerson.pupilsightPersonID = "' . $stuId . '" AND fn_fee_invoice_student_assign.status = 1  GROUP BY fn_fee_invoice.id ORDER BY fn_fee_invoice_student_assign.id ASC';
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
                if (!empty($dataSD)) {
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
                ON fee_item.id = discount.item_id WHERE fee_item.fn_fee_invoice_id= " . $d['invoiceid'] . " AND pupilsightPersonID = " . $stuId . "  ";
                $result_sp_item = $connection2->query($sp_item_sql);
                $sp_item_dis = $result_sp_item->fetch();
                //unset($invdata[$k]['finalamount']);

                if (!empty($d['transport_schedule_id']) && $d['transport_schedule_id'] != '') {
                    $routes = explode(',', $d['routes']);
                    if (!empty($routes)) {
                        $tranamount = 0;
                        foreach ($routes as $rt) {
                            if (!empty($rt)) {
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
                    $sqlp = 'SELECT id FROM fn_fee_waive_off WHERE invoice_no = "' . $ind['stu_invoice_no'] . '" ';
                    $resultp = $connection2->query($sqlp);
                    $wfdata = $resultp->fetch();
                    $dsc = '';
                    if (!empty($wfdata)) {
                        $dsc = '(WF)';
                    }

                    if (!empty($ind['sdDis'])) {
                        $dsc = '(SD)';
                    }

                    if (!empty($ind['sdDis']) && !empty($wfdata)) {
                        $dsc = '(SD,WF)';
                    }

                    if ($ind['chkpayment'] == 'Paid') {
                        echo 'working';
                        //$cls = 'value="0" checked disabled';
                        echo '<tr><td><input type="checkbox" class=" invoice' . $ind['id'] . '" name="invoiceid[]" data-h="' . $ind['fn_fees_head_id'] . '" data-se="' . $ind['rec_fn_fee_series_id'] . '" id="allfeeItemid" data-stu="' . $stuId . '" data-fper="' . $ind['amtper'] . '" data-ftype="' . $ind['type'] . '" data-inv="' . $ind['invid'] . '" data-ife="' . $ind['is_fine_editable'] . '" value="0" checked disabled ></td><td>' . $ind['stu_invoice_no'] . '</td><td>' . $ind['title'] . '</td><td>' . $totAmtdisAmt . '</td><td>' . $totDisAmt . ' ' . $dsc . '</td><td>' . $totAmt_with_dis . '</td><td>' . number_format($ind['pendingamount'], 2) . '</td></tr>';
                    } else {
                        $cls = 'value="' . $ind['invoiceid'] . '"';
                        echo '<tr><td><input type="checkbox" class="chkinvoiceM invoice' . $ind['id'] . '" name="invoiceid[]" data-h="' . $ind['fn_fees_head_id'] . '" data-se="' . $ind['rec_fn_fee_series_id'] . '" id="allfeeItemid" data-stu="' . $stuId . '" data-fper="' . $ind['amtper'] . '" data-ftype="' . $ind['type'] . '"  ' . $cls . '  data-amtedt="' . $ind['amount_editable'] . '" data-inv="' . $ind['invid'] . '" data-ife="' . $ind['is_fine_editable'] . '" ></td><td>' . $ind['stu_invoice_no'] . '</td><td>' . $ind['title'] . '</td><td>' . $totAmtdisAmt . '</td><td>' . $totDisAmt . ' ' . $dsc .  '</td><td>' . $totAmt_with_dis . '</td><td>' . number_format($ind['pendingamount'], 2) . '</td></tr>';
                    }
                }
            } else {
                echo "<tr><td colspan='4'>No invoices found</td></tr>";
            }

            break;
        case "setEditTemplateValues":
            $name = $_POST['name'];
            $file = $_POST['file'];
            $session->forget(['file_name_tmp']);
            $session->forget(['file_doc_tmp']);
            $session->set('file_name_tmp', $name);
            $session->set('file_doc_tmp', $file);
            break;
        case "update_template_for_receipt":
            if (!empty($_FILES["file_upload"]["name"])) {
                $old_file = trim($_POST['old_file']);
                $fileData = pathinfo(basename($_FILES["file_upload"]["name"]));
                @$extension = end(explode(".", $_FILES["file_upload"]["name"]));
                $NewNameFile = $old_file . '.' . $fileData['extension'];
                $sourcePath = $_FILES['file_upload']['tmp_name'];
                $uploaddir = 'thirdparty/phpword/templates/';
                //rename
                $o_name = "thirdparty/phpword/templates/" . $old_file . ".docx";
                $r_name = "thirdparty/phpword/templates/updated_at/" . date('Y_m_d') . "_" . $old_file . ".docx";
                @rename($o_name, $r_name);
                //rename
                $del = "thirdparty/phpword/templates/" . $old_file . ".docx";
                @unlink($del);
                $uploadfile = $uploaddir . $NewNameFile;
                if (move_uploaded_file($sourcePath, $uploadfile)) {
                    echo "Template updated successfully";
                } else {
                    echo "No";
                }
            } else {
                echo "Empty file uploaded";
            }
            break;
        case "apply_discount_session":
            $a_stuid = $_POST['p_stuId'];
            $a_yid = $_POST['pSyd'];
            $a_invoices_ids = $_POST['ids'];
            $session->forget(['a_stuid']);
            $session->forget(['a_yid']);
            $session->forget(['a_invoices_ids']);
            $session->set('a_stuid', $a_stuid);
            $session->set('a_yid', $a_yid);
            $session->set('a_invoices_ids', $a_invoices_ids);
            break;
        case "get_dicount_type_change":
            $d_type = $_POST['d_type'];
            $stuId = $_POST['sid'];
            $yid = $_POST['yid'];
            $ids = $_POST['ids'];
            if (!empty($d_type)) {
                if ($d_type == "1") {
                    $invoices = 'SELECT fn_fee_invoice.*,fn_fee_invoice.id as invoiceid, fn_fee_invoice_student_assign.invoice_no as stu_invoice_no, g.fine_type, g.rule_type, GROUP_CONCAT(DISTINCT asg.route_id) as routes, GROUP_CONCAT(DISTINCT asg.transport_type) as routetype FROM fn_fee_invoice LEFT JOIN pupilsightStudentEnrolment ON fn_fee_invoice.pupilsightSchoolYearID=pupilsightStudentEnrolment.pupilsightSchoolYearID LEFT JOIN pupilsightPerson ON pupilsightStudentEnrolment.pupilsightPersonID=pupilsightPerson.pupilsightPersonID RIGHT JOIN fn_fee_invoice_student_assign ON pupilsightPerson.pupilsightPersonID=fn_fee_invoice_student_assign.pupilsightPersonID AND fn_fee_invoice.id = fn_fee_invoice_student_assign.fn_fee_invoice_id LEFT JOIN fn_fees_fine_rule AS g ON fn_fee_invoice.fn_fees_fine_rule_id = g.id LEFT JOIN trans_route_assign AS asg ON pupilsightPerson.pupilsightPersonID = asg.pupilsightPersonID WHERE fn_fee_invoice.pupilsightSchoolYearID = "' . $yid . '" AND pupilsightPerson.pupilsightPersonID = "' . $stuId . '" AND  fn_fee_invoice.id IN(' . $ids . ') GROUP BY fn_fee_invoice.id';
                    $resultinv = $connection2->query($invoices);
                    $invdata = $resultinv->fetchAll();
                    $totalamount = 0;
                    foreach ($invdata as $k => $d) {

                        $sqlamt = 'SELECT SUM(fn_fee_invoice_item.amount) as tot_amount, SUM(fn_fee_invoice_item.total_amount) as totalamount, SUM(fn_fee_invoice_item.discount) as disamount FROM fn_fee_invoice_item WHERE fn_fee_invoice_id = ' . $d['invoiceid'] . ' ';
                        $resultamt = $connection2->query($sqlamt);
                        $dataamt = $resultamt->fetch();
                        $sql_dis = "SELECT discount FROM fn_invoice_level_discount WHERE pupilsightPersonID = " . $stuId . "  AND invoice_id=" . $d['invoiceid'] . " ";
                        $result_dis = $connection2->query($sql_dis);
                        $special_dis = $result_dis->fetch();

                        $sp_item_sql = "SELECT SUM(discount.discount) as sp_discount
                        FROM fn_fee_invoice_item as fee_item
                        LEFT JOIN fn_fee_item_level_discount as discount
                        ON fee_item.id = discount.item_id WHERE fee_item.fn_fee_invoice_id= " . $d['invoiceid'] . " AND pupilsightPersonID = " . $stuId . "  ";
                        $result_sp_item = $connection2->query($sp_item_sql);
                        $sp_item_dis = $result_sp_item->fetch();
                        //unset($invdata[$k]['finalamount']);

                        if (!empty($d['transport_schedule_id']) && $d['transport_schedule_id'] != '') {
                            $routes = explode(',', $d['routes']);
                            if (!empty($routes)) {
                                $tranamount = 0;
                                foreach ($routes as $rt) {
                                    if (!empty($rt)) {
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

                        $invdata[$k]['tot_amount'] = $dataamt['tot_amount'];


                        if (!empty($special_dis['discount']) || !empty($sp_item_dis['sp_discount'])) {
                            $invdata[$k]['finalamount'] = $totalamount - $special_dis['discount'] - $sp_item_dis['sp_discount'];
                            $totalamount = $totalamount - $special_dis['discount'] - $sp_item_dis['sp_discount'];
                        } else {
                            $invdata[$k]['finalamount'] = $totalamount;
                        }

                        // $sqlamt = 'SELECT SUM(fn_fee_invoice_item.total_amount) as totalamount FROM fn_fee_invoice_item WHERE fn_fee_invoice_id = ' . $d['invoiceid'] . ' ';
                        // $resultamt = $connection2->query($sqlamt);
                        // $dataamt = $resultamt->fetch();


                        // //unset($invdata[$k]['finalamount']);

                        // if (!empty($d['transport_schedule_id']) && $d['transport_schedule_id'] != '') {
                        //     $routes = explode(',', $d['routes']);
                        //     foreach ($routes as $rt) {
                        //         $sqlsc = 'SELECT * FROM trans_route_price WHERE schedule_id = ' . $d['transport_schedule_id'] . ' AND route_id = ' . $rt . ' ';
                        //         $resultsc = $connection2->query($sqlsc);
                        //         $datasc = $resultsc->fetch();
                        //         if ($d['routetype'] == 'oneway') {
                        //             $price = $datasc['oneway_price'];
                        //             $tax = $datasc['tax'];
                        //             $amtperc = ($tax / 100) * $price;
                        //             $tranamount = $price + $amtperc;
                        //         } else {
                        //             $price = $datasc['twoway_price'];
                        //             $tax = $datasc['tax'];
                        //             $amtperc = ($tax / 100) * $price;
                        //             $tranamount = $price + $amtperc;
                        //         }
                        //     }
                        //     $totalamount = $tranamount;
                        // } else {
                        //     $totalamount = $dataamt['totalamount'];
                        // }
                        // $invdata[$k]['finalamount'] = $totalamount;

                        $date = date('Y-m-d');
                        $curdate = strtotime($date);
                        $duedate = strtotime($d['due_date']);
                        $fineId = $d['fn_fees_fine_rule_id'];

                        if (!empty($fineId) && $curdate > $duedate) {
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
                                $sqlf = 'SELECT * FROM fn_fees_rule_type WHERE fn_fees_fine_rule_id = "' . $fineId . '" AND fine_type = "' . $finetype . '" AND rule_type = "' . $ruletype . '" ';
                                $resultf = $connection2->query($sqlf);
                                $finedata = $resultf->fetch();
                                $amtper = $finedata['amount_in_percent'];
                                $type = 'percent';
                            } elseif ($finetype == '2' && $ruletype == '2') {
                                $sqlf = 'SELECT * FROM fn_fees_rule_type WHERE fn_fees_fine_rule_id = "' . $fineId . '" AND fine_type = "' . $finetype . '" AND rule_type = "' . $ruletype . '" ';
                                $resultf = $connection2->query($sqlf);
                                $finedata = $resultf->fetch();
                                $amtper = $finedata['amount_in_number'];
                                $type = 'num';
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
                        } else {
                            $amtper = '';
                            $type = '';
                        }
                        $invdata[$k]['amtper'] = $amtper;
                        $invdata[$k]['type'] = $type;


                        $invid =  $d['invoiceid'];
                        $invno =  $d['stu_invoice_no'];
                        $sqla = 'SELECT GROUP_CONCAT(a.fn_fee_invoice_item_id) AS invitemid, b.transaction_id FROM fn_fees_student_collection AS a LEFT JOIN fn_fees_collection AS b ON  a.transaction_id = b.transaction_id WHERE a.invoice_no = "' . $invno . '" AND b.transaction_status = "1" ';
                        $resulta = $connection2->query($sqla);
                        $inv = $resulta->fetch();


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
                            $stTransId = $inv['transaction_id'];
                            if (!empty($inv['invitemid'])) {
                                if (!empty($d['transport_schedule_id'])) {
                                    $invdata[$k]['paidamount'] = $totalamount;
                                    $pendingamount = 0;
                                    $invdata[$k]['pendingamount'] = $pendingamount;
                                    $invdata[$k]['chkpayment'] = 'Paid';
                                } else {
                                    $itemids = $inv['invitemid'];
                                    $sqlp = 'SELECT SUM(total_amount_collection) as paidtotalamount FROM fn_fees_student_collection WHERE pupilsightPersonID = ' . $stuId . ' AND transaction_id = ' . $stTransId . ' AND fn_fee_invoice_item_id IN (' . $itemids . ') AND is_active = "1" ';
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
                    }

                    $sqlstaff = 'SELECT b.officialName , b.pupilsightPersonID FROM pupilsightStaff AS a LEFT JOIN pupilsightPerson AS b ON a.pupilsightPersonID = b.pupilsightPersonID';
                    $resultstaff = $connection2->query($sqlstaff);
                    $staffData = $resultstaff->fetchAll();

                    echo "<div>";
?>
                    <table class="table" cellspacing="0" style="width: 100%;">
                        <thead>
                            <tr class="head">
                                <th>Sl.No</th>
                                <th>Invoice No</th>
                                <th>Invoice Amount</th>
                                <th>Pending Amount</th>
                                <th>Discout Amount</th>
                                <th>Select</th>
                                <th class="waiveClass" style="display:none">Assigned By</th>
                                <th class="waiveClass" style="display:none">Date</th>
                                <th class="waiveClass" style="display:none">Remark</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            if (!empty($invdata)) {
                                $i = 1;
                                foreach ($invdata as $ind) {

                                    $total = $ind['tot_amount'];
                                    $pending = $ind['pendingamount'];

                                    echo '<tr>
                        <td  width="5%">' . $i++ . '</td>
                        <td  width="10%">' . $ind['stu_invoice_no'] . '</td>
                        <td  width="5%">' . $total . '</td>
                        <td  width="5%">' . $pending . '</td>
                        <td  width="5%"><input type="text" name="discount_a[]" value="' . $special_dis['discount'] . '" readonly class="form-control inid_' . $ind['invoiceid'] . '" ></td>
                        <td  width="1%"><input type="checkbox"  class="chkinvoice_discount invoice' . $ind['invoiceid'] . '" name="invoiceid[]" value="' . $ind['invoiceid'] . '" data-id="' . $ind['invoiceid'] . '" ></td>
                        <td class="waiveClass" style="display:none" width="10%">
                            <select name="assigned_by[]" class="form-control assn_' . $ind['invoiceid'] . '">
                                <option value="">Select</option>';
                                    if (!empty($staffData)) {
                                        foreach ($staffData as $std) {
                                            echo '<option value=' . $std['pupilsightPersonID'] . '>' . $std['officialName'] . '</option>';
                                        }
                                    }
                                    echo '</select>
                        </td>
                        <td class="waiveClass" style="display:none" width="5%"><input type="date" name="date[]" value=""  class="form-control dte_' . $ind['invoiceid'] . '" ></td>
                        <td class="waiveClass" style="display:none" width="5%"><input type="text" name="remark[]" value=""  class="form-control rem_' . $ind['invoiceid'] . '" ><input type="hidden" name="invoice_no[]" value="' . $ind['stu_invoice_no'] . '"  class="form-control invno_' . $ind['invoiceid'] . '" ></td>
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
                            $std_query = "SELECT fee_category_id FROM `pupilsightPerson` WHERE `pupilsightPersonID` = '" . $stuId . "'";
                            $std_exe = $connection2->query($std_query);
                            $std_data = $std_exe->fetch();
                            $fee_category_id = $std_data['fee_category_id'];
                            $sqli = 'SELECT e.pupilsightPersonID,a.*,a.id as itemid, b.*, b.id as ifid, b.name as feeitemname, c.id AS invoiceid, c.transport_schedule_id, d.format, e.invoice_no as stu_invoice_no, f.item_type, f.name, f.min_invoice, f.max_invoice, f.amount_in_percent, f.amount_in_number, GROUP_CONCAT(DISTINCT asg.route_id) as routes, GROUP_CONCAT(DISTINCT asg.transport_type) as routetype  FROM fn_fee_invoice_item AS a LEFT JOIN fn_fee_items AS b ON a.fn_fee_item_id = b.id LEFT JOIN fn_fee_invoice AS c ON a.fn_fee_invoice_id = c.id LEFT JOIN fn_fee_series AS d ON c.inv_fn_fee_series_id = d.id LEFT JOIN fn_fee_invoice_student_assign AS e ON c.id = e.fn_fee_invoice_id  LEFT JOIN fn_fee_discount_item as f ON c.fn_fees_discount_id = f.fn_fees_discount_id LEFT JOIN trans_route_assign AS asg ON e.pupilsightPersonID = asg.pupilsightPersonID WHERE a.fn_fee_invoice_id IN (' . $ids . ') AND e.pupilsightPersonID = ' . $stuId . ' GROUP BY a.id';
                            $resulti = $connection2->query($sqli);
                            $feeItem = $resulti->fetchAll();
                            $data = '';
                            $i = 1;
                            foreach ($feeItem as $fI) {
                                $discountamt = 0;
                                $discount = 0;
                                $sql_dis = "SELECT SUM(discount) AS discount FROM fn_fee_item_level_discount WHERE pupilsightPersonID = " . $stuId . "  AND item_id='" . $fI['itemid'] . "' ";
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

                                if ($fee_category_id == $fI['name']) {
                                    if ($fI['item_type'] == 'Fixed') {
                                        $discount = $fI['amount_in_number'];
                                        $discountamt = $fI['amount_in_number'];
                                    } else {
                                        $discount = $fI['amount_in_percent'] . '%';
                                        $discountamt = ($fI['amount_in_percent'] / 100) * $totalamount;
                                    }
                                }
                                if (!empty($special_dis['discount'])) {
                                    $discount += $special_dis['discount'];
                                    $discountamt += $special_dis['discount'];
                                }
                                $amtdiscount = $totalamount - $discountamt;
                                $data .= '<tr class="odd invrow" role="row">
                  
            <td width="5%">
                ' . $i++ . ' 
            </td>
             
            <td width="10%">
               ' . $fI['feeitemname'] . '     
            </td> 
            <td width="5%">
            ' . $fI['amount'] . '  
            </td>                          
            <td width="10%">
               <input type="number" class="form-control itid_' . $fI['itemid'] . '" value="' . $special_dis['discount'] . '" readonly>
            </td>
             <td width="10%"><label class="leading-normal" for="feeItemid"></label> <input type="checkbox" class="' . $cls . '" id="feeItemid" data-id="' . $fI['itemid'] . '" value="' . $fI['itemid'] . '" ' . $checked . '></td>
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
            $stid = trim($_POST['stuid']);
            $discountVal = $_POST['discountVal'];
            $invids = $_POST['invids'];
            if (!empty($stid) && !empty($invids)) {
                $count = sizeof($invids);
                for ($i = 0; $i < $count; $i++) {
                    $sqlpt = "SELECT id FROM fn_invoice_level_discount WHERE pupilsightPersonID = " . $stid . "  AND invoice_id='" . $invids[$i] . "' ";
                    $resultpt = $connection2->query($sqlpt);
                    $valuept = $resultpt->fetch();
                    if (empty($valuept['id'])) {
                        $datau = array('pupilsightPersonID' => $stid, 'invoice_id' => $invids[$i], 'discount' => $discountVal[$i]);
                        $sql = 'INSERT INTO fn_invoice_level_discount SET pupilsightPersonID=:pupilsightPersonID, invoice_id=:invoice_id, discount=:discount';
                        $result = $connection2->prepare($sql);
                        $result->execute($datau);
                    } else {
                        $datau = array('pupilsightPersonID' => $stid, 'discount' => $discountVal[$i], 'invoice_id' => $invids[$i]);
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
            $stid = trim($_POST['stuid']);
            $discountVal = $_POST['discountVal'];
            $items = $_POST['items'];
            if (!empty($stid) && !empty($items)) {
                $count = sizeof($items);
                for ($i = 0; $i < $count; $i++) {
                    $sqlpt = "SELECT id FROM fn_fee_item_level_discount WHERE pupilsightPersonID = " . $stid . "  AND item_id='" . $items[$i] . "' ";
                    $resultpt = $connection2->query($sqlpt);
                    $valuept = $resultpt->fetch();
                    if (empty($valuept['id'])) {
                        $datau = array('pupilsightPersonID' => $stid, 'item_id' => $items[$i], 'discount' => $discountVal[$i]);
                        $sql = 'INSERT INTO fn_fee_item_level_discount SET pupilsightPersonID=:pupilsightPersonID, item_id=:item_id, discount=:discount';
                        $result = $connection2->prepare($sql);
                        $result->execute($datau);
                    } else {
                        $datau = array('pupilsightPersonID' => $stid, 'discount' => $discountVal[$i], 'item_id' => $items[$i]);
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
            $pupilsightSchoolYearID = $_POST['pupilsightSchoolYearID'];
            $test_sql = 'SELECT  examinationTestMaster.id,examinationTestMaster.name FROM `examinationTestMaster` LEFT JOIN `pupilsightSchoolYear` ON `examinationTestMaster`.`pupilsightSchoolYearID`=`pupilsightSchoolYear`.`pupilsightSchoolYearID` WHERE `examinationTestMaster`.`pupilsightSchoolYearID` = "' . $pupilsightSchoolYearID . '" ORDER BY examinationTestMaster.name ASC';
            $test_res = $connection2->query($test_sql);
            $tests = $test_res->fetchAll();
            $options = '<option value="">Select program</option>';
            foreach ($tests as $val) {
                $options .= "<option value='" . $val['id'] . "'>" . $val['name'] . "</option>";
            }
            echo $options;
            break;
        case "loadTestByProgram":
            $test_id = $_POST['test_id'];
            $test_sql = 'SELECT p.pupilsightProgramID,p.name
            FROM pupilsightProgram as p
            LEFT JOIN examinationTestAssignClass as examTAC 
            ON p.pupilsightProgramID = examTAC.pupilsightProgramID
            WHERE examTAC.test_master_id = "' . $test_id . '" GROUP BY examTAC.pupilsightProgramID';
            $test_res = $connection2->query($test_sql);
            $tests = $test_res->fetchAll();
            $options = '<option value="">Select program</option>';
            foreach ($tests as $val) {
                $options .= "<option value='" . $val['pupilsightProgramID'] . "'>" . $val['name'] . "</option>";
            }
            echo $options;
            break;
        case "loadClassesByTest":
            $test_id = $_POST['test_id'];
            $pupilsightProgramID = $_POST['pupilsightProgramID'];
            $test_sql = 'SELECT g.pupilsightYearGroupID,g.name
            FROM pupilsightYearGroup as g 
            LEFT JOIN examinationTestAssignClass as examTAC
            ON g.pupilsightYearGroupID = examTAC.pupilsightYearGroupID 
            WHERE examTAC.test_master_id ="' . $test_id . '" AND examTAC.pupilsightProgramID="' . $pupilsightProgramID . '"  GROUP BY examTAC.pupilsightYearGroupID ORDER bY g.name ASC';
            $test_res = $connection2->query($test_sql);
            $tests = $test_res->fetchAll();
            $options = '<option value="">Select Class</option>';
            foreach ($tests as $val) {
                $options .= "<option value='" . $val['pupilsightYearGroupID'] . "'>" . $val['name'] . "</option>";
            }
            echo $options;
            break;
        case "getSectionM":
            $pid = $_POST['pupilsightProgramID'];
            $cid = implode(',', $_POST['pupilsightYearGroup']);
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
            $pupilsightYearGroupID = implode(',', $_POST['pupilsightYearGroupID']);
            $sq = "select pupilsightDepartmentID, subject_display_name, di_mode from subjectToClassCurriculum where pupilsightYearGroupID IN(" . $pupilsightYearGroupID . ")  GROUP BY subject_display_name order by subject_display_name asc";
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
            $cls = $_POST['cls'];
            $prg = $_POST['program'];
            $sqlr = "SELECT * FROM subject_skill_descriptive_indicator_config WHERE pupilsightDepartmentID = " . $sub_id . " AND pupilsightYearGroupID = " . $cls . " AND pupilsightProgramID = " . $prg . " ";
            $resultr = $connection2->query($sqlr);
            $rowdatar = $resultr->fetchAll();

            foreach ($rowdatar as $row) {
                echo  '<tr>
        <td>
        <div class="dte mb-1"></div><div class="  txtfield mb-1"><div class="flex-1 relative"><input type="checkbox" class="selectGrdCheck" name="Selectone_byOne[]" id="' . $row['id'] . '" value="' . $row['id'] . '" ></div></div></td>
         <td>
        <div class="input-group stylish-input-group">
            <div class="dte mb-1"></div><div class="  txtfield mb-1"><div class="flex-1 relative"> <input type="text" name="remarksname" class="remarks_id" value="' . $row['remark_description'] . '" style="    width: 500px;" ></div></div>
        </div></td></tr>';
            }
            break;
        case "load_remarks_grade_descriptive_indicator_config":
            $sub_id = $_POST['sub'];
            $cls = $_POST['cls'];
            $prg = $_POST['program'];
            $sqlr = "SELECT * FROM subject_skill_descriptive_indicator_config WHERE pupilsightDepartmentID = " . $sub_id . " AND pupilsightYearGroupID = " . $cls . " AND pupilsightProgramID = " . $prg . " ";
            $resultr = $connection2->query($sqlr);
            $rowdatar = $resultr->fetchAll();

            foreach ($rowdatar as $row) {
                echo  '<tr>
       
         <td>
            <div class="input-group stylish-input-group">
                <div class="dte mb-1"></div><div class="  txtfield mb-1"><div class="flex-1 relative">' . $row['grade'] . '</div></div>
            </div></td>
            <td>
                <div class="input-group stylish-input-group w-full">
                    <div class="dte mb-1"></div><div class="  txtfield mb-1"><div class="flex-1 relative">
                    <input type="text" name="remarksname" class="remarks_id" value="' . $row['remark_description'] . '" style="width: 500px;" >
                    </div></div>
                </div>
            </td>
            <td>
                <div class="input-group stylish-input-group">
                    <div class="dte mb-1"></div><div class="  txtfield mb-1"><div class="flex-1 relative">' . strlen($row['remark_description']) . '
                   
                    
                    </div></div>
                </div>
            </td>
            <td>
            <div class="input-group stylish-input-group">
                <div class="dte mb-1"></div><div class="  txtfield mb-1"><div class="flex-1 relative"><input type="checkbox" class="selectGrdCheck" name="Selectone_byOne[]" id="' . $row['id'] . '" value="' . $row['id'] . '"></div></div>
            </div>
        </td>
            </tr>';
            }
            break;
        case "load_test_groups":
            $data = ' ';
            if (!empty($_POST['pupilsightRollGroupID'])) {
                $pupilsightRollGroupID = implode(',', $_POST['pupilsightRollGroupID']);
                $pupilsightProgramID = $_POST['pupilsightProgramID'];
                $pupilsightSchoolYearID = $_SESSION[$guid]['pupilsightSchoolYearID'];
                $pupilsightYearGroupID = $_POST['pupilsightYearGroupID'];
                $sql = 'SELECT  examinationTest.* FROM `examinationTest` LEFT JOIN `examinationTestAssignClass` ON `examinationTest`.`id`=`examinationTestAssignClass`.`test_id` LEFT JOIN `pupilsightSchoolYear` ON `examinationTest`.`pupilsightSchoolYearID`=`pupilsightSchoolYear`.`pupilsightSchoolYearID` WHERE `examinationTest`.`pupilsightSchoolYearID` = "' . $pupilsightSchoolYearID . '" AND `examinationTestAssignClass`.`pupilsightProgramID` = "' . $pupilsightProgramID . '" AND `examinationTestAssignClass`.`pupilsightYearGroupID` = "' . $pupilsightYearGroupID . '" AND `examinationTestAssignClass`.`pupilsightRollGroupID` IN (' . $pupilsightRollGroupID . ')  ORDER BY `examinationTest`.`id` DESC';
                $result = $connection2->query($sql);
                $test = $result->fetchAll();
                if (!empty($test)) {
                    foreach ($test as $cl) {
                        $data .= '<tr><td>
                <input class="slt_test" type ="checkbox" name="testID[]" value="' . $cl['id'] . '">' . " </td>
                <td>" . $cl['name'] . "</td></tr>";
                    }
                }
            } else {
                $data .= "<tr><td colspan='2'>No data</td></tr>";
            }
            echo $data;
            break;
        case "load_tests_subjects":
            $data = ' ';
            if (!empty($_POST['testID'])) {

                $testID = implode(',', $_POST['testID']);
                $sqls = 'SELECT a.id,a.test_id,a.pupilsightDepartmentID,a.skill_id,i.subject_display_name as subname,j.name as skill, et.name as test_name FROM examinationSubjectToTest AS a LEFT JOIN examinationTest as et ON a.test_id = et.id LEFT JOIN subjectToClassCurriculum as i ON a.pupilsightDepartmentID =i.pupilsightDepartmentID LEFT JOIN ac_manage_skill as j ON a.skill_id = j.id WHERE a.test_id IN(' . $testID . ') AND a.is_tested = "1"  GROUP BY a.id  ORDER BY i.pos ASC, a.id  ';

                $results = $connection2->query($sqls);
                $rowdatas = $results->fetchAll();
                // echo '<pre>';
                // print_r($rowdatas);
                // echo '</pre>';
                if (!empty($rowdatas)) {
                    foreach ($rowdatas as $cl) {
                        $data .= '<tr><td>
                            <input class="slt_test_1" type ="checkbox" name="subjectSkillId[]" value="' . $cl['test_id'] . '-' . $cl['pupilsightDepartmentID'] . '-' . $cl['skill_id'] . '">' . " </td>
                            <td>" . $cl['test_name'] . "</td>
                            <td>" . $cl['subname'] . "</td><td>" . $cl['skill'] . "</td></tr>";
                    }
                }
            } else {
                $data .= "<tr><td colspan='3'>No data</td></tr>";
            }
            echo $data;
            break;
        case 'studentMarks_excel':
            $program = $_POST['program'];
            $cls = $_POST['cls'];
            $section = $_POST['section'];
            $testId = implode(',', $_POST['testId']);
            $sql = 'SELECT b.officialName, b.pupilsightPersonID, d.name as classname, e.name as sectionname FROM `examinationMarksEntrybySubject` AS a LEFT JOIN pupilsightPerson AS b ON a.pupilsightPersonID = b.pupilsightPersonID LEFT JOIN pupilsightStudentEnrolment AS c ON a.pupilsightPersonID = c.pupilsightPersonID LEFT JOIN pupilsightYearGroup AS d ON c.pupilsightYearGroupID = d.pupilsightYearGroupID LEFT JOIN pupilsightRollGroup AS e ON c.pupilsightRollGroupID = e.pupilsightRollGroupID WHERE   a.test_id = ' . $testId . ' GROUP BY a.pupilsightPersonID';
            $result = $connection2->query($sql);
            $data = $result->fetchAll();
            foreach ($data as $k => $dt) {
                $sqlm = 'SELECT a.*, b.name as subject_name, c.skill_display_name,m.max_marks FROM examinationMarksEntrybySubject AS a LEFT JOIN pupilsightDepartment AS b ON a.pupilsightDepartmentID = b.pupilsightDepartmentID LEFT JOIN subjectSkillMapping AS c ON a.skill_id = c.skill_id
                    LEFT JOIN examinationSubjectToTest AS m ON a.pupilsightDepartmentID = m.pupilsightDepartmentID
                    WHERE a.test_id = ' . $testId . ' AND a.pupilsightPersonID = ' . $dt['pupilsightPersonID'] . ' GROUP by a.pupilsightDepartmentID,c.skill_id';
                $resultm = $connection2->query($sqlm);
                $datam = $resultm->fetchAll();
                if (!empty($datam)) {
                    $data[$k]['marks'] = $datam;
                }
            }
            $sql1 = 'SELECT a.*, b.name as subject_name, c.skill_display_name,m.max_marks FROM examinationMarksEntrybySubject AS a LEFT JOIN pupilsightDepartment AS b ON a.pupilsightDepartmentID = b.pupilsightDepartmentID LEFT JOIN subjectSkillMapping AS c ON a.skill_id = c.skill_id
            LEFT JOIN examinationSubjectToTest AS m ON a.pupilsightDepartmentID = m.pupilsightDepartmentID
            WHERE a.test_id = ' . $testId . ' GROUP by a.pupilsightDepartmentID,c.skill_id';
            $resu_h = $connection2->query($sql1);
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
                                <th><?php echo $m['subject_name'] . "-" . $m['skill_display_name'] . "/" . ceil($m['max_marks']); ?>
                                </th>
                            <?php
                            }
                            ?>
                        </tr>

                        <?php foreach ($data as $row) {
                            echo "<tr>
    <td>" . $row['officialName'] . "</td>
    <td>" . $row['pupilsightPersonID'] . "</td>
    <td>" . $row['classname'] . "</td>
    <td>" . $row['sectionname'] . "</td>";
                            $marks = $row['marks'];
                            foreach ($data as $val) {
                                $marks = $val['marks'];
                                foreach ($marks as $m) {
                                    $sql = 'SELECT grade_name FROM  examinationGradeSystemConfiguration WHERE id="' . $m['gradeId'] . '"';
                                    $result = $connection2->query($sql);
                                    $gradeName = $result->fetch();
                                    $grade_name = '';

                                    if (!empty($gradeName['grade_name'])) {
                                        $grade_name = $gradeName['grade_name'];
                                    }
                                    if ($row['pupilsightPersonID'] == $val['pupilsightPersonID']) {

                                        $marks = str_replace(".00", "", $m['marks_obtained']);
                                        if ($marks == 0) {
                                            if ($m['marks_abex']) {
                                                $marks = $m['marks_abex'];
                                            }
                                        }
                                        if (!empty($grade_name)) {
                                            echo "<td>" . $marks . "(" . $grade_name . ")</td>";
                                        } else {
                                            echo "<td>" . $marks . "</td>";
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

            case 'studentMarks_excel_new':
                $program = $_POST['program'];
                $cls = $_POST['cls'];
                $section = $_POST['section'];
                $testId = implode(',', $_POST['testId']);
                $stu_id = $_POST['stu_id'];
                $sql = 'SELECT b.officialName, b.pupilsightPersonID, d.name as classname, e.name as sectionname FROM `examinationMarksEntrybySubject` AS a LEFT JOIN pupilsightPerson AS b ON a.pupilsightPersonID = b.pupilsightPersonID LEFT JOIN pupilsightStudentEnrolment AS c ON a.pupilsightPersonID = c.pupilsightPersonID LEFT JOIN pupilsightYearGroup AS d ON c.pupilsightYearGroupID = d.pupilsightYearGroupID LEFT JOIN pupilsightRollGroup AS e ON c.pupilsightRollGroupID = e.pupilsightRollGroupID WHERE   a.test_id = ' . $testId . ' AND a.pupilsightPersonID IN (' . $stu_id . ')  GROUP BY a.pupilsightPersonID';
                $result = $connection2->query($sql);
                $data = $result->fetchAll();
                foreach ($data as $k => $dt) {
                    $sqlm = 'SELECT a.*, b.name as subject_name, c.skill_display_name,m.max_marks FROM examinationMarksEntrybySubject AS a LEFT JOIN pupilsightDepartment AS b ON a.pupilsightDepartmentID = b.pupilsightDepartmentID LEFT JOIN subjectSkillMapping AS c ON a.skill_id = c.skill_id
                            LEFT JOIN examinationSubjectToTest AS m ON a.pupilsightDepartmentID = m.pupilsightDepartmentID
                            WHERE a.test_id = ' . $testId . ' AND a.pupilsightPersonID = ' . $dt['pupilsightPersonID'] . ' GROUP by a.pupilsightDepartmentID,c.skill_id';
                    $resultm = $connection2->query($sqlm);
                    $datam = $resultm->fetchAll();
                    if (!empty($datam)) {
                        $data[$k]['marks'] = $datam;
                    }
                }
                $sql1 = 'SELECT a.*, b.name as subject_name, c.skill_display_name,m.max_marks FROM examinationMarksEntrybySubject AS a LEFT JOIN pupilsightDepartment AS b ON a.pupilsightDepartmentID = b.pupilsightDepartmentID LEFT JOIN subjectSkillMapping AS c ON a.skill_id = c.skill_id
                    LEFT JOIN examinationSubjectToTest AS m ON a.pupilsightDepartmentID = m.pupilsightDepartmentID
                    WHERE a.test_id = ' . $testId . ' GROUP by a.pupilsightDepartmentID,c.skill_id';
                $resu_h = $connection2->query($sql1);
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
                                <th><?php echo $m['subject_name'] . "-" . $m['skill_display_name'] . "/" . ceil($m['max_marks']); ?>
                                </th>
                            <?php
                            }
                            ?>
                        </tr>

                        <?php foreach ($data as $row) {
                            echo "<tr>
                            <td>" . $row['officialName'] . "</td>
                            <td>" . $row['pupilsightPersonID'] . "</td>
                            <td>" . $row['classname'] . "</td>
                            <td>" . $row['sectionname'] . "</td>";
                            $marks = $row['marks'];
                            foreach ($data as $val) {
                                $marks = $val['marks'];
                                foreach ($marks as $m) {
                                    $sql = 'SELECT grade_name FROM  examinationGradeSystemConfiguration WHERE id="' . $m['gradeId'] . '"';
                                    $result = $connection2->query($sql);
                                    $gradeName = $result->fetch();
                                    $grade_name = '';

                                    if (!empty($gradeName['grade_name'])) {
                                        $grade_name = $gradeName['grade_name'];
                                    }
                                    if ($row['pupilsightPersonID'] == $val['pupilsightPersonID']) {

                                        $marks = str_replace(".00", "", $m['marks_obtained']);
                                        if ($marks == 0) {
                                            if ($m['marks_abex']) {
                                                $marks = $m['marks_abex'];
                                            }
                                        }
                                        if (!empty($grade_name)) {
                                            echo "<td>" . $marks . "(" . $grade_name . ")</td>";
                                        } else {
                                            echo "<td>" . $marks . "</td>";
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
                $testId = implode(',', $_POST['testId']);
                //   $sql = "SELECT a.* ,b.skill_display_name ,d.name as test,c.name as subject,GROUP_CONCAT(DISTINCT b.skill_id SEPARATOR ', ') as skill_ids,GROUP_CONCAT(DISTINCT b.skill_display_name SEPARATOR ', ') as skillname FROM examinationMarksEntrybySubject AS a LEFT JOIN subjectSkillMapping AS b ON a.`skill_id` = b.skill_id LEFT JOIN pupilsightDepartment as c ON a.pupilsightDepartmentID=c.pupilsightDepartmentID LEFT JOIN  examinationTest as d ON a.test_id = d.id LEFT JOIN examinationTest as e ON a.test_id = e.id  WHERE   a.test_id = ".$testId." GROUP BY a.pupilsightPersonID";
                $sql = 'SELECT b.officialName, b.pupilsightPersonID, d.name as classname, e.name as sectionname FROM `examinationMarksEntrybySubject` AS a LEFT JOIN pupilsightPerson AS b ON a.pupilsightPersonID = b.pupilsightPersonID LEFT JOIN pupilsightStudentEnrolment AS c ON a.pupilsightPersonID = c.pupilsightPersonID LEFT JOIN pupilsightYearGroup AS d ON c.pupilsightYearGroupID = d.pupilsightYearGroupID LEFT JOIN pupilsightRollGroup AS e ON c.pupilsightRollGroupID = e.pupilsightRollGroupID WHERE   a.test_id = ' . $testId . ' GROUP BY a.pupilsightPersonID';
                $result = $connection2->query($sql);
                $data = $result->fetchAll();
                // echo '<pre>';
                //  print_r($data);
                // echo '</pre>';
                foreach ($data as $k => $dt) {
                    $sqlm = 'SELECT a.*, b.name as subject_name, c.skill_display_name,m.max_marks FROM examinationMarksEntrybySubject AS a LEFT JOIN pupilsightDepartment AS b ON a.pupilsightDepartmentID = b.pupilsightDepartmentID LEFT JOIN subjectSkillMapping AS c ON a.skill_id = c.skill_id
                    LEFT JOIN examinationSubjectToTest AS m ON a.pupilsightDepartmentID = m.pupilsightDepartmentID
                    WHERE a.test_id = ' . $testId . ' AND a.pupilsightPersonID = ' . $dt['pupilsightPersonID'] . ' GROUP by a.pupilsightDepartmentID,c.skill_id';
                    $resultm = $connection2->query($sqlm);
                    $datam = $resultm->fetchAll();
                    if (!empty($datam)) {
                        $data[$k]['marks'] = $datam;
                    }
                }

                $sql1 = 'SELECT a.*, b.name as subject_name, c.skill_display_name,m.max_marks FROM examinationMarksEntrybySubject AS a LEFT JOIN pupilsightDepartment AS b ON a.pupilsightDepartmentID = b.pupilsightDepartmentID LEFT JOIN subjectSkillMapping AS c ON a.skill_id = c.skill_id
            LEFT JOIN examinationSubjectToTest AS m ON a.pupilsightDepartmentID = m.pupilsightDepartmentID
            WHERE a.test_id = ' . $testId . ' GROUP by a.pupilsightDepartmentID,c.skill_id';
                $resu_h = $connection2->query($sql1);
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
                                <th><?php echo $m['subject_name'] . "-" . $m['skill_display_name'] . "/" . ceil($m['max_marks']); ?>
                                </th>
                            <?php
                            }
                            ?>
                        </tr>

                        <?php foreach ($data as $row) {
                            echo "<tr>
    <td>" . $row['officialName'] . "</td>
    <td>" . $row['pupilsightPersonID'] . "</td>
    <td>" . $row['classname'] . "</td>
    <td>" . $row['sectionname'] . "</td>";
                            $marks = $row['marks'];
                            foreach ($data as $val) {
                                $marks = $val['marks'];
                                foreach ($marks as $m) {
                                    $sql = 'SELECT grade_name FROM  examinationGradeSystemConfiguration WHERE id="' . $m['gradeId'] . '"';
                                    $result = $connection2->query($sql);
                                    $gradeName = $result->fetch();
                                    $grade_name = '';

                                    if (!empty($gradeName['grade_name'])) {
                                        $grade_name = $gradeName['grade_name'];
                                    }

                                    if ($row['pupilsightPersonID'] == $val['pupilsightPersonID']) {

                                        $marks = str_replace(".00", "", $m['marks_obtained']);
                                        if ($marks == 0) {
                                            if ($m['marks_abex']) {
                                                $marks = $m['marks_abex'];
                                            }
                                        }
                                        if (!empty($grade_name)) {
                                            echo "<td>" . $marks . "(" . $grade_name . ")</td>";
                                        } else {
                                            echo "<td>" . $marks . "</td>";
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
                echo '<option value="">Select Student</option>';
                if (!empty($sections)) {
                    foreach ($sections as $k => $cl) {
                        echo '<option value="' . $cl['pupilsightPersonID'] . '">' . $cl['officialName'] . '</option>';
                    }
                }
                break;

            case 'subjectMarks_excelNew';
                $testId = implode(',', $_POST['testId']);
                //   $sql = "SELECT a.* ,b.skill_display_name ,d.name as test,c.name as subject,GROUP_CONCAT(DISTINCT b.skill_id SEPARATOR ', ') as skill_ids,GROUP_CONCAT(DISTINCT b.skill_display_name SEPARATOR ', ') as skillname FROM examinationMarksEntrybySubject AS a LEFT JOIN subjectSkillMapping AS b ON a.`skill_id` = b.skill_id LEFT JOIN pupilsightDepartment as c ON a.pupilsightDepartmentID=c.pupilsightDepartmentID LEFT JOIN  examinationTest as d ON a.test_id = d.id LEFT JOIN examinationTest as e ON a.test_id = e.id  WHERE   a.test_id = ".$testId." GROUP BY a.pupilsightPersonID";

                $sql = 'SELECT a.test_id, b.officialName, b.pupilsightPersonID, d.name as classname, e.name as sectionname FROM `examinationMarksEntrybySubject` AS a LEFT JOIN pupilsightPerson AS b ON a.pupilsightPersonID = b.pupilsightPersonID LEFT JOIN pupilsightStudentEnrolment AS c ON a.pupilsightPersonID = c.pupilsightPersonID LEFT JOIN pupilsightYearGroup AS d ON c.pupilsightYearGroupID = d.pupilsightYearGroupID LEFT JOIN pupilsightRollGroup AS e ON c.pupilsightRollGroupID = e.pupilsightRollGroupID WHERE   a.test_id IN (' . $testId . ') AND a.pupilsightDepartmentID = ' . $_POST['sub'] . ' GROUP BY a.pupilsightPersonID';
                $result = $connection2->query($sql);
                $data = $result->fetchAll();
                // echo '<pre>';
                //  print_r($data);
                // echo '</pre>';
                // die();

                $sql1 = 'SELECT a.*, b.name as subject_name, c.skill_display_name,m.max_marks, t.name as test_name FROM examinationMarksEntrybySubject AS a LEFT JOIN pupilsightDepartment AS b ON a.pupilsightDepartmentID = b.pupilsightDepartmentID LEFT JOIN subjectSkillMapping AS c ON a.skill_id = c.skill_id
                        LEFT JOIN examinationSubjectToTest AS m ON a.pupilsightDepartmentID = m.pupilsightDepartmentID AND a.test_id = m.test_id 
                        LEFT JOIN examinationTest AS t ON a.test_id = t.id
                        WHERE a.test_id IN (' . $testId . ') AND a.pupilsightDepartmentID = ' . $_POST['sub'] . '  GROUP by a.test_id';
                $resu_h = $connection2->query($sql1);
                $datam_h = $resu_h->fetchAll();
                // echo '<pre>';
                // print_r($datam_h);
                // echo '</pre>';
                ?>

                    <table id="subexcelexport">
                        <tr>
                            <th colspan="4">Student Details</th>
                            <?php
                            foreach ($datam_h as $m) {
                            ?>
                                <th colspan="2" style="text-align:center"><?php echo $m['test_name']; ?>
                                </th>

                            <?php
                            }
                            ?>
                        </tr>
                        <tr>
                            <th>Student Name</th>
                            <th>Student ID</th>
                            <th>Class</th>
                            <th>Section</th>
                            <?php
                            foreach ($datam_h as $m) {
                            ?>
                                <th><?php echo $m['subject_name'] . "-" . $m['skill_display_name'] . "/" . ceil($m['max_marks']); ?>
                                </th>
                                <th>Grade</th>
                                <!-- <th></th> -->
                            <?php
                            }
                            ?>
                        </tr>

                        <?php foreach ($data as $k => $row) {
                            echo "<tr>
                                            <td>" . $row['officialName'] . "</td>
                                            <td>" . $row['pupilsightPersonID'] . "</td>
                                            <td>" . $row['classname'] . "</td>
                                            <td>" . $row['sectionname'] . "</td>";
                            //$marks = $row['marks'];
                            //foreach ($data as $k => $dt) {
                            echo $sqlm = 'SELECT a.*, b.name as subject_name, c.skill_display_name,m.max_marks FROM examinationMarksEntrybySubject AS a LEFT JOIN pupilsightDepartment AS b ON a.pupilsightDepartmentID = b.pupilsightDepartmentID LEFT JOIN subjectSkillMapping AS c ON a.skill_id = c.skill_id
                                            LEFT JOIN examinationSubjectToTest AS m ON a.pupilsightDepartmentID = m.pupilsightDepartmentID
                                            WHERE a.test_id IN (' . $testId . ') AND a.pupilsightPersonID = ' . $row['pupilsightPersonID'] . ' AND a.pupilsightDepartmentID = ' . $_POST['sub'] . ' GROUP by a.test_id,c.skill_id';
                            $resultm = $connection2->query($sqlm);
                            $datam = $resultm->fetchAll();
                            echo '<pre>';
                            print_r($datam);
                            echo '</pre>';
                            // if (!empty($datam)) {
                            //     $markdata[$k]['marks'] = $datam;
                            // }
                            //}
                            //foreach ($datam as $val) {
                            if (!empty($datam)) {
                                //$marks = $val['marks'];
                                foreach ($datam as $m) {
                                    $sql = 'SELECT grade_name FROM  examinationGradeSystemConfiguration WHERE id="' . $m['gradeId'] . '"';
                                    $result = $connection2->query($sql);
                                    $gradeName = $result->fetch();
                                    $grade_name = '';

                                    if (!empty($gradeName['grade_name'])) {
                                        $grade_name = $gradeName['grade_name'];
                                    }

                                    if ($row['pupilsightPersonID'] == $m['pupilsightPersonID']) {

                                        $marks = str_replace(".00", "", $m['marks_obtained']);
                                        if ($marks == 0) {
                                            if ($m['marks_abex']) {
                                                $marks = $m['marks_abex'];
                                            }
                                        }
                                        if (!empty($grade_name)) {
                                            echo "<td>" . $marks . "</td>";
                                            echo "<td>" . $grade_name . "</td>";
                                        } else {
                                            echo "<td>" . $marks . "</td>";
                                            echo "<td></td>";
                                        }
                                    }
                                }
                            } else {
                                echo "<td></td>";
                                echo "<td></td>";
                            }
                            //}
                        ?>
                            </tr>
                        <?php } ?>
                    </table>

        <?php
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

    function countholidays($cdate, $ddate, $weekendDaysId)
    {
        $no = 0;
        $start = new DateTime($ddate);
        $end   = new DateTime($cdate);
        $interval = DateInterval::createFromDateString('1 day');
        $period = new DatePeriod($start, $interval, $end);
        foreach ($period as $dt) {
            if (!empty($weekendDaysId)) {
                $weekIds = explode(',', $weekendDaysId);
                foreach ($weekIds as $wid) {
                    if ($dt->format('N') == $wid) {
                        $no++;
                    }
                }
            }
        }
        return $no;
    }

        ?>