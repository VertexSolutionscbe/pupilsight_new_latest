<?php
/*
Pupilsight, Flexible & Open School System
*/

include '../../pupilsight.php';

$URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_POST['address']).'/fee_transaction_manage.php';

if (isActionAccessible($guid, $connection2, '/modules/Finance/fee_transaction_refund_manage.php') == false) {
    $URL .= '&return=error0';
    header("Location: {$URL}");
} else {
    //Proceed!
    //Validate Inputs
    // echo "<pre>";
    // print_r($_POST);exit;
    $remarks = $_POST['remarks'];
    $trans_id = $_POST['trans_id'];
    $transaction_id = $_POST['transaction_id'];
    $payment_mode_id = $_POST['payment_mode_id'];
    $pupilsightPersonID = $_POST['pupilsightPersonID'];
    $bank_id = $_POST['bank_id'];
    $receipt_number = $_POST['receipt_number'];
    $refund_receipt_series_id = $_POST['refund_receipt_series_id'];
    $dd_cheque_no = $_POST['dd_cheque_no'];
    $reference_no = $_POST['reference_no'];
    if($_POST['reference_date']){
        $fd = explode('/', $_POST['reference_date']);
        $reference_date  = date('Y-m-d', strtotime(implode('-', array_reverse($fd))));
    }else{
      
        $reference_date  = '';
    }

    if($_POST['dd_cheque_date']){
        $fd = explode('/', $_POST['dd_cheque_date']);
        $dd_cheque_date  = date('Y-m-d', strtotime(implode('-', array_reverse($fd))));
    }else{
      
        $dd_cheque_date  = '';
    }
  // print_r($dd_cheque_date);die();
    $dd_cheque_amount = $_POST['refund_amount'];
    $refund_date = $_POST['refund_date'];
    $refund_amount = $_POST['refund_amount'];
    $cuid = $_SESSION[$guid]['pupilsightPersonID'];
    $cdt = date('Y-m-d H:i:s');
    
    if ($trans_id == '' or $refund_amount == '') {
        $URL .= '&return=error1';
        header("Location: {$URL}");
    } else {
        //Check unique inputs for uniquness
            //Write to database
            try {

                if(!empty($refund_receipt_series_id)){
                    $sqlrec = 'SELECT id, formatval FROM fn_fee_series WHERE id = "'.$refund_receipt_series_id.'" ';
                    $resultrec = $connection2->query($sqlrec);
                    $recptser = $resultrec->fetch();
            
                    $invformat = explode('$',$recptser['formatval']);
                    $iformat = '';
                    $orderwise = 0;
                    foreach($invformat as $inv){
                        if($inv == '{AB}'){
                            $datafort = array('fn_fee_series_id'=>$refund_receipt_series_id,'order_wise' => $orderwise, 'type' => 'numberwise');
                            $sqlfort = 'SELECT id, no_of_digit, last_no FROM fn_fee_series_number_format WHERE fn_fee_series_id=:fn_fee_series_id AND order_wise=:order_wise AND type=:type';
                            $resultfort = $connection2->prepare($sqlfort);
                            $resultfort->execute($datafort);
                            $formatvalues = $resultfort->fetch();
                            $iformat .= $formatvalues['last_no'];
                            
                            $str_length = $formatvalues['no_of_digit'];
            
                            $lastnoadd = $formatvalues['last_no'] + 1;
            
                            $lastno = substr("0000000{$lastnoadd}", -$str_length); 
            
                            $datafort1 = array('fn_fee_series_id'=>$refund_receipt_series_id,'order_wise' => $orderwise, 'type' => 'numberwise' , 'last_no' => $lastno);
                            $sqlfort1 = 'UPDATE fn_fee_series_number_format SET last_no=:last_no WHERE fn_fee_series_id=:fn_fee_series_id AND type=:type AND order_wise=:order_wise';
                            $resultfort1 = $connection2->prepare($sqlfort1);
                            $resultfort1->execute($datafort1);
            
                        } else {
                            $iformat .= $inv;
                        }
                        $orderwise++;
                    }
                    
                   $refund_receipt_number = $iformat;
                } else {
                    $refund_receipt_number = '';
                }

                    $data = array('fn_fees_collection_id' => $trans_id, 'transaction_id' => $transaction_id, 'payment_mode_id' => $payment_mode_id, 'pupilsightPersonID' => $pupilsightPersonID, 'bank_id' => $bank_id, 'receipt_number' => $receipt_number, 'refund_receipt_series_id' => $refund_receipt_series_id, 'refund_receipt_number' => $refund_receipt_number, 'dd_cheque_no' => $dd_cheque_no, 'dd_cheque_date' => $dd_cheque_date, 'dd_cheque_amount' => $dd_cheque_amount, 'refund_date' => $refund_date, 'refund_amount' => $refund_amount, 'remarks' => $remarks, 'refund_by' => $cuid, 'cdt' => $cdt,'reference_no'=>$reference_no,'reference_date'=>$reference_date);

                    
                   $sql = 'INSERT INTO fn_fees_collection_refund SET fn_fees_collection_id=:fn_fees_collection_id, transaction_id=:transaction_id, payment_mode_id=:payment_mode_id, pupilsightPersonID=:pupilsightPersonID, bank_id=:bank_id, receipt_number=:receipt_number, refund_receipt_series_id=:refund_receipt_series_id, refund_receipt_number=:refund_receipt_number, dd_cheque_no=:dd_cheque_no, dd_cheque_date=:dd_cheque_date, dd_cheque_amount=:dd_cheque_amount, refund_date=:refund_date, refund_amount=:refund_amount, remarks=:remarks, refund_by=:refund_by, cdt=:cdt,reference_no=:reference_no,reference_date=:reference_date';
                   $result = $connection2->prepare($sql);
                   $result->execute($data);

                    $datau = array('transaction_status' => '3', 'id' => $trans_id);
                    $sqlu = 'UPDATE fn_fees_collection SET transaction_status=:transaction_status WHERE id=:id';
                    $resultu = $connection2->prepare($sqlu);
                    $resultu->execute($datau);

                    $sqlstu = "SELECT a.officialName , b.name as class, c.name as section FROM pupilsightPerson AS a LEFT JOIN pupilsightStudentEnrolment AS d ON a.pupilsightPersonID = d.pupilsightPersonID LEFT JOIN pupilsightYearGroup AS b ON d.pupilsightYearGroupID = b.pupilsightYearGroupID LEFT JOIN pupilsightRollGroup AS c ON d.pupilsightRollGroupID = c.pupilsightRollGroupID WHERE a.pupilsightPersonID = ".$pupilsightPersonID." ";
                    $resultstu = $connection2->query($sqlstu);
                    $valuestu = $resultstu->fetch();

                    $sqlpt = "SELECT name FROM fn_masters WHERE id = ".$payment_mode_id." ";
                    $resultpt = $connection2->query($sqlpt);
                    $valuept = $resultpt->fetch();

                    $class_section = $valuestu["class"] ." ".$valuestu["section"];
                    $dts_receipt = array(
                        "receipt_no" => $refund_receipt_number,
                        "date" => date("d-M-Y"),
                        "student_name" => $valuestu["officialName"],
                        "student_id" => $pupilsightPersonID,
                        "class_section" => $class_section,
                        "instrument_date" => "NA",
                        "instrument_no" => "NA",
                        "transcation_amount" => $refund_amount,
                        "pay_mode" => $valuept['name'],
                        "transactionId" => $transaction_id,
                        "reason" => $remarks
                    );

                    $dts_receipt_feeitem[] = array(
                        "serial.all"=>'1',
                        "particulars.all"=>$remarks,
                        "amount.all"=>$refund_amount
                    );

                    if(!empty($dts_receipt) && !empty($dts_receipt_feeitem)){ 
                        $callback = $_SESSION[$guid]['absoluteURL'].'/thirdparty/phpword/refund_receipt.php';
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
                    

            } catch (PDOException $e) {
                //$URL .= '&return=error2';
                //header("Location: {$URL}");
                exit();
            }

            //Last insert ID
            $AI = str_pad($connection2->lastInsertID(), 3, '0', STR_PAD_LEFT);

            // $URL .= "&return=success0&editID=$AI";
            // header("Location: {$URL}");
    }
}
