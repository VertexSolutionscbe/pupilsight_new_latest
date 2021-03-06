<?php
/*
Pupilsight, Flexible & Open School System
*/

include '../../pupilsight.php';
$session = $container->get('session');
$URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_POST['address']).'/fee_transaction_manage.php';

if (isActionAccessible($guid, $connection2, '/modules/Finance/fee_transaction_cancel.php') == false) {
    $URL .= '&return=error0';
    header("Location: {$URL}");
} else {
    //Proceed!
    //Validate Inputs
    $remarks = $_POST['remarks'];
    $trans_id = explode(',', $_POST['trans_id']);
    $cuid = $_SESSION[$guid]['pupilsightPersonID'];
    $cdt = date('Y-m-d H:i:s');
    
    if ($remarks == ''  or $trans_id == '') {
        $URL .= '&return=error1';
        header("Location: {$URL}");
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
                    //$result->execute($data);

                    $datau = array('transaction_status' => '2', 'id' => $ts);
                    $sqlu = 'UPDATE fn_fees_collection SET transaction_status=:transaction_status WHERE id=:id';
                    $resultu = $connection2->prepare($sqlu);
                    //$resultu->execute($datau);

                    $collectionId = $ts;
                    
                    $sqlstu = "SELECT e.*, a.officialName , b.name as class, c.name as section, GROUP_CONCAT(DISTINCT f.fn_fees_invoice_id) as invoice_id, GROUP_CONCAT(f.fn_fee_invoice_item_id) as invoice_item_id FROM fn_fees_collection AS e LEFT JOIN pupilsightPerson AS a ON e.pupilsightPersonID = a.pupilsightPersonID LEFT JOIN pupilsightStudentEnrolment AS d ON e.pupilsightPersonID = d.pupilsightPersonID LEFT JOIN pupilsightYearGroup AS b ON d.pupilsightYearGroupID = b.pupilsightYearGroupID LEFT JOIN pupilsightRollGroup AS c ON d.pupilsightRollGroupID = c.pupilsightRollGroupID LEFT JOIN fn_fees_student_collection AS f ON e.transaction_id = f.transaction_id WHERE e.id = ".$collectionId." GROUP BY e.id ";
                    $resultstu = $connection2->query($sqlstu);
                    $valuestu = $resultstu->fetch();
                    // echo '<pre>';
                    // print_r($valuestu);
                    // echo '</pre>';
                    // die();
                    
                    $sqlinv = "SELECT * FROM fn_fees_student_collection WHERE transaction_id = ".$valuestu['transaction_id']." ";
                    $resultinv = $connection2->query($sqlinv);
                    $valueinvData = $resultinv->fetchAll();

                    // echo '<pre>';
                    // print_r($valueinvData);
                    // echo '</pre>';
                    // die();

                    $datau = array('is_active' => '2', 'transaction_id' => $valuestu['transaction_id']);
                    $sqlu = 'UPDATE fn_fees_student_collection SET is_active=:is_active WHERE transaction_id=:transaction_id';
                    $resultu = $connection2->prepare($sqlu);
                    $resultu->execute($datau);

                    
                    $sqlpt = "SELECT name FROM fn_masters WHERE id = ".$valuestu['payment_mode_id']." ";
                    $resultpt = $connection2->query($sqlpt);
                    $valuept = $resultpt->fetch();
    
                    $class_section = $valuestu["class"] ." ".$valuestu["section"];
                    $dts_receipt = array(
                        "receipt_no" => $valuestu['receipt_number'],
                        "date" => date("d-M-Y"),
                        "student_name" => $valuestu["officialName"],
                        "student_id" => $valuestu['pupilsightPersonID'],
                        "class_section" => $class_section,
                        "instrument_date" => "NA",
                        "instrument_no" => "NA",
                        "transcation_amount" => $valuestu['amount_paying'],
                        "fine_amount" => $valuestu['fine'],
                        "other_amount" => "NA",
                        "pay_mode" => $valuept['name'],
                        "transactionId" => $valuestu['transaction_id'],
                        "reason" => $remarks
                    );
                    $invoice_id = $valuestu["invoice_id"];
                    $invoice_item_id = $valuestu["invoice_item_id"];
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

                    // echo '<pre>';
                    // print_r($dts_receipt_feeitem);
                    // echo '</pre>';
                    // die();

                    if(!empty($valueinvData)){
                        foreach($valueinvData as $valueinv){
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
                   
            
                    if(!empty($dts_receipt) && !empty($dts_receipt_feeitem)){ 
                        $callback = $_SESSION[$guid]['absoluteURL'].'/thirdparty/phpword/cancel_receipt.php';
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
                }
               
            } catch (PDOException $e) {
                // $URL .= '&return=error2';
                // header("Location: {$URL}");
                exit();
            }

            

            //Last insert ID
            // $AI = str_pad($connection2->lastInsertID(), 3, '0', STR_PAD_LEFT);

            // $URL .= "&return=success0";
            // header("Location: {$URL}");
    }

    
}
