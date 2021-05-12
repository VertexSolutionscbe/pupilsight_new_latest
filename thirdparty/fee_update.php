<?php

//require('config.php');

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

include $_SERVER['DOCUMENT_ROOT'] . '/pupilsight.php';
$dt = $_SESSION["paypost"];

$baseurl = "http://" . $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'];
$callback = "/thirdparty/phpword/receiptOnline.php";
include $_SERVER['DOCUMENT_ROOT'] . '/db.php';

if(!empty($dt)){
    //$conn
    try {
        
        $pupilsightPersonID = $dt["stuid"];

        $fn_fees_receipt_series_id = $dt['receipt_number'];
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

                    $iformat .= $formatvalues['last_no'];

                    $str_length = $formatvalues['no_of_digit'];

                    $lastnoadd = $formatvalues['last_no'] + 1;

                    $lastno = substr("0000000{$lastnoadd}", -$str_length);

                    $datafort1 = array('fn_fee_series_id' => $fn_fees_receipt_series_id, 'order_wise' => $orderwise, 'type' => 'numberwise', 'last_no' => $lastno);
                    $sqlfort1 = 'UPDATE fn_fee_series_number_format SET last_no=:last_no WHERE fn_fee_series_id=:fn_fee_series_id AND type=:type AND order_wise=:order_wise';
                    $resultfort1 = $connection2->prepare($sqlfort1);
                    $resultfort1->execute($datafort1);
                } else {

                    $iformat .= $inv;
                }
                $orderwise++;
            }
            $receipt_number = $iformat;
        } else {
            $receipt_number = '';
        }

        $rand = mt_rand(10, 99);
        $t = time();
        $transactionId = $t . $rand;

        $section = "";
        $clss = "";
        $prog = "";

        if (!empty($dt["pupilsightProgramID"])) {
            $sqcs = "select name from pupilsightProgram where pupilsightProgramID='" . $dt["pupilsightProgramID"] . "'";
            $result = $conn->query($sqcs);
            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    $prog = $row["name"];
                }
            }
        }

        if (!empty($dt["sectionid"])) {
            $sqcs = "select name from pupilsightRollGroup where pupilsightRollGroupID='" . $dt["sectionid"] . "'";
            $result = $conn->query($sqcs);
            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    $section = $row["name"];
                }
            }
        }

        if (!empty($dt["classid"])) {
            $sqcs = "SELECT name from pupilsightYearGroup WHERE pupilsightYearGroupID= ".$dt["classid"]." AND pupilsightSchoolYearID = ".$dt['pupilsightSchoolYearID']." ";
            $result = $conn->query($sqcs);
            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    $clss = $row["name"];
                }
            }
        }

        $sqlfat = "SELECT b.officialName , b.phone1, b.email FROM pupilsightFamilyRelationship AS a LEFT JOIN pupilsightPerson AS b ON a.pupilsightPersonID1 = b.pupilsightPersonID WHERE a.pupilsightPersonID2 = " . $dt["stuid"] . " AND a.relationship = 'Father' ";
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

        $sqlmot = "SELECT b.officialName , b.phone1, b.email FROM pupilsightFamilyRelationship AS a LEFT JOIN pupilsightPerson AS b ON a.pupilsightPersonID1 = b.pupilsightPersonID WHERE a.pupilsightPersonID2 = " . $dt["stuid"] . " AND a.relationship = 'Mother' ";
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

        $sqlinv = 'SELECT GROUP_CONCAT(DISTINCT b.invoice_no) AS invNo, GROUP_CONCAT(c.title) AS invtitle, c.cdt, c.due_date FROM fn_fee_invoice_item AS a LEFT JOIN fn_fee_invoice_student_assign AS b ON a.fn_fee_invoice_id = b.fn_fee_invoice_id LEFT JOIN fn_fee_invoice AS c ON a.fn_fee_invoice_id = c.id WHERE a.id IN (' . $dt["fn_fee_invoice_item_id"] . ') AND b.pupilsightPersonID = ' . $dt["stuid"] . '  ORDER BY b.id ASC';
        $resultinv = $connection2->query($sqlinv);
        $valueinv = $resultinv->fetch();

        $invNo = $valueinv['invNo'];
        $inv_title = $valueinv['invtitle'];
        $inv_date = '';
        if(!empty($valueinv['cdt'])){
            $inv_date = date('d/m/Y', strtotime($valueinv['cdt']));
        }

        $due_date = '';
        if(!empty($valueinv['due_date']) && $valueinv['due_date'] != '1970-01-01'){
            $due_date = date('d/m/Y', strtotime($valueinv['due_date']));
        }

        $sqlrt = 'SELECT a.ac_no, b.path, b.column_start_by FROM fn_fees_head AS a LEFT JOIN fn_fees_receipt_template_master AS b ON a.receipt_template = b.id WHERE a.id = ' . $dt['fn_fees_head_id'] . ' ';
        $resultrt = $connection2->query($sqlrt);
        $recTempData = $resultrt->fetch();
        $receiptTemplate = $recTempData['path'];
        $fee_head_acc_no = $recTempData['ac_no'];
        $column_start_by = $recTempData['column_start_by'];

        $sqlst = 'SELECT admission_no, roll_no FROM pupilsightPerson WHERE pupilsightPersonID = ' . $dt['stuid'] . ' ';
        $resultst = $connection2->query($sqlst);
        $stData = $resultst->fetch();

        $sqlay = 'SELECT name FROM pupilsightSchoolYear WHERE pupilsightSchoolYearID = ' . $dt['pupilsightSchoolYearID'] . ' ';
        $resultay = $connection2->query($sqlay);
        $ayData = $resultay->fetch();

        $academic_year = $ayData["name"];

        $class_section = $clss . " - " . $section;
        $class_name = $clss;
        $section_name = $section;
        $bank_name = '';

        $chkcussql = 'SELECT field_name FROM custom_field WHERE field_name = "correspondence_address" ';
        $chkresultstu = $connection2->query($chkcussql);
        $custDataChk = $chkresultstu->fetch();
        if(!empty($custDataChk)){
            $fieldName = ', a.correspondence_address';
        } else {
            $fieldName = '';
        }

        if(!empty($custDataChk)){
            $coreaddress = $valuestu["correspondence_address"];
        } else {
            $coreaddress = '';
        }
        
        
        $sqlon = 'SELECT id FROM fn_masters WHERE name = "Online" ';
        $resulton = $connection2->query($sqlon);
        $onData = $resulton->fetch();
        $payment_mode_id = '';
        if(!empty($onData)){
            $payment_mode_id = $onData['id'];
        }

        //$invoice_id = $dt["fn_fees_invoice_id"];
        $total = 0;
        $totalTax = 0;
        $totalamtWitoutTaxDis = 0;
        $totalPending = 0;
        $totalDiscount = 0;
        if (!empty($dt["fn_fees_invoice_id"])) {
            $razorpay_payment_id = $_GET['payid'];
        
            $dates = date('Y-m-d');
            $cdt = date('Y-m-d H:i:s');
            $sq = "INSERT INTO fn_fees_collection (fn_fees_invoice_id, transaction_id,pupilsightPersonID, pupilsightSchoolYearID,
            receipt_number, pay_gateway_id, payment_mode_id, payment_status, payment_date, fn_fees_head_id, fn_fees_receipt_series_id, 
            transcation_amount, total_amount_without_fine_discount, amount_paying, fine, discount, status, cdt, invoice_status) ";
            $sq .= " values(
                    '" . $dt["fn_fees_invoice_id"] . "'
                    ,'" . $transactionId . "'
                    ,'" . $dt["stuid"] . "'
                    ,'" . $dt["pupilsightSchoolYearID"] . "'
                    ,'" . $receipt_number . "'
                    ,'" . $razorpay_payment_id . "'
                    ,'" . $payment_mode_id. "'
                    ,'Payment Received'
                    ,'" . $dates . "'
                    ,'" . $dt["fn_fees_head_id"] . "'
                    ,'" . $dt["rec_fn_fee_series_id"] . "'
                    ,'" . $dt["amount"] . "'
                    ,'" . $dt["total_amount_without_fine_discount"] . "'
                    ,'" . $dt["amount"] . "'
                    ,'" . $dt["fine"] . "'
                    ,'" . $dt["discount"] . "'
                    ,'1'
                    ,'" . $cdt . "'
                    ,'Fully Paid'
                    ); ";
           
            if ($conn->query($sq) === TRUE) {
                //$conn->query($tsq);
                if (!empty($dt["fn_fee_invoice_item_id"])) {
                    $itemId = explode(',', $dt["fn_fee_invoice_item_id"]);
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
    
                        $chkpayitem = 'SELECT a.id FROM fn_fees_student_collection AS a LEFT JOIN fn_fees_collection AS b ON a.transaction_id = b.transaction_id WHERE a.fn_fees_invoice_id = ' . $fn_fee_invoice_id . ' AND a.fn_fee_invoice_item_id = ' . $itid . ' AND a.pupilsightPersonID = ' . $pupilsightPersonID . ' AND b.transaction_status = 1 ';
                        $resultcp = $connection2->query($chkpayitem);
                        $valuecp = $resultcp->fetch();
    
                        
    
                        if (!empty($valuecp)) {
                            $datai = array('partial_transaction_id' => $transactionId, 'total_amount_collection' => $itemamount, 'status' => '1', 'id' => $valuecp['id']);
                            $sqli = 'UPDATE fn_fees_student_collection SET partial_transaction_id=:partial_transaction_id, total_amount_collection=:total_amount_collection, status=:status WHERE id=:id';
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
    
                    }
                }

                if (!empty($dt["fn_fees_invoice_id"])) {
                    $invid = explode(',', $dt["fn_fees_invoice_id"]);
                
                    $cnt = 1;
                    foreach ($invid as $iid) {
                        $invoice_id = $iid;
                        // $dataiu = array('invoice_status' => 'Fully Paid',  'pupilsightPersonID' => $dt["stuid"],  'invoice_no' => $dt["payid"]);
                        // $sqliu = 'UPDATE fn_fee_invoice_student_assign SET invoice_status=:invoice_status WHERE pupilsightPersonID=:pupilsightPersonID AND invoice_no=:invoice_no';
                        // $resultiu = $connection2->prepare($sqliu);
                        // $resultiu->execute($dataiu);

                        $dataiu = array('invoice_status' => 'Fully Paid',  'pupilsightPersonID' => $pupilsightPersonID,  'fn_fee_invoice_id' => $invoice_id);
                        $sqliu = 'UPDATE fn_fee_invoice_student_assign SET invoice_status=:invoice_status WHERE pupilsightPersonID=:pupilsightPersonID AND fn_fee_invoice_id=:fn_fee_invoice_id';
                        $resultiu = $connection2->prepare($sqliu);
                        $resultiu->execute($dataiu);

                        $chksql = 'SELECT fn_fee_structure_id, display_fee_item, title as invoice_title FROM fn_fee_invoice WHERE id = ' . $invoice_id . ' ';
                        $resultchk = $connection2->query($chksql);
                        $valuechk = $resultchk->fetch();
                        if (!empty($valuechk['fn_fee_structure_id'])) {
                            $chsql = 'SELECT b.invoice_title, a.display_fee_item FROM fn_fee_invoice AS a LEFT JOIN fn_fee_structure AS b ON a.fn_fee_structure_id = b.id WHERE a.id= ' . $invoice_id . ' AND a.fn_fee_structure_id IS NOT NULL ';
                            $resultch = $connection2->query($chsql);
                            $valuech = $resultch->fetch();
                        } else {
                            $valuech = $valuechk;
                        }

                        $chksql = 'SELECT fn_fee_structure_id, display_fee_item, title as invoice_title, is_concat_invoice FROM fn_fee_invoice WHERE id = ' . $iid . ' ';
                        $resultchk = $connection2->query($chksql);
                        $valuechk = $resultchk->fetch();
                        $is_concat_invoice = $valuechk['is_concat_invoice'];

                        if($is_concat_invoice == '1'){
                            $concatInvId[] = $iid;
                        } else {
                            if ($valuech['display_fee_item'] == '2') {
                                $sqcs = "select SUM(fi.total_amount) AS tamnt, SUM(fi.amount) AS amnt, SUM(fi.tax) AS ttax from fn_fee_invoice_item as fi, fn_fee_items as items where fi.fn_fee_item_id = items.id and fi.fn_fee_invoice_id =  " . $invoice_id . " ";
                                $resultfi = $connection2->query($sqcs);
                                $valuefi = $resultfi->fetchAll();
                                if (!empty($valuefi)) {
                                    //$cnt = 1;
                                    foreach ($valuefi as $vfi) {
                                        $sqcol = "SELECT SUM(total_amount) AS tamntCol , SUM(discount) AS disCol, SUM(total_amount_collection) AS ttamtCol FROM fn_fees_student_collection WHERE fn_fees_invoice_id = ".$invoice_id." AND ( transaction_id = ".$transactionId." OR partial_transaction_id = ".$transactionId." )  ";
                                        $resultcol = $connection2->query($sqcol);
                                        $valuecol = $resultcol->fetch();
                                        $itemAmt    = $valuecol["tamntCol"];
                                        $itemAmtCol = $valuecol["ttamtCol"];

                                        $sqitid = "SELECT GROUP_CONCAT(id) AS itmIds FROM fn_fee_invoice_item WHERE fn_fee_invoice_id = ".$invoice_id." ";
                                        $resultitid = $connection2->query($sqitid);
                                        $valueitid = $resultitid->fetch();
                                        $itmIDS = $valueitid['itmIds'];

                                        $sqdis = "SELECT SUM(discount) AS dis FROM fn_fee_item_level_discount WHERE pupilsightPersonID = ".$dt["stuid"]." AND item_id IN (".$itmIDS.") ";
                                        $resultdis = $connection2->query($sqdis);
                                        $valuedis = $resultdis->fetch();
                                        $disItemAmt = 0;
                                        if(!empty($valuedis)){
                                            $disItemAmt = $valuedis['dis'];
                                            $newItemAmtCol = $itemAmtCol + $disItemAmt;
                                            $itemAmtPen = $itemAmt - $newItemAmtCol;
                                        } else {
                                            $disItemAmt = 0;
                                            $itemAmtPen = $itemAmt - $itemAmtCol;
                                        }


                                        $itemAmtPen = $itemAmt - $itemAmtCol;

                                        $taxamt = 0;
                                        if(!empty($vfi["ttax"])){
                                            $taxamt = ($vfi["ttax"] / 100) * $vfi["amnt"];
                                            $taxamt = number_format($taxamt, 2, '.', '');
                                        }

                                        $dts_receipt_feeitem[] = array(
                                            "serial.all" => $cnt,
                                            "particulars.all" => htmlspecialchars(trim($valuech['invoice_title'])),
                                            "inv_amt.all" => $vfi["amnt"],
                                            "tax.all" => $taxamt,
                                            "amount.all" => $vfi["tamnt"],
                                            //"inv_amt_paid.all" => number_format($vfi["tamnt"],2),
                                            "inv_amt_paid.all" => number_format($itemAmtCol,2),
                                            "inv_amt_pending.all" => number_format($itemAmtPen,2),
                                            "inv_amt_discount.all" => number_format($disItemAmt,2)
                                        );
                                        $total += $vfi["tamnt"];
                                        $totalTax += $taxamt;
                                        $totalamtWitoutTaxDis += $vfi["amnt"];
                                        $totalDiscount += $disItemAmt;
                                        $cnt++;
                                    }
                                }
                            } else {
                            
                                $sqcs = "select fi.total_amount, fi.amount, fi.tax, fi.id, items.name from fn_fee_invoice_item as fi, fn_fee_items as items where fi.fn_fee_item_id = items.id and fi.fn_fee_invoice_id =  " . $invoice_id . " and fi.id in(" . $dt["fn_fee_invoice_item_id"] . ")  ";
                                $resultfi = $connection2->query($sqcs);
                                $valuefi = $resultfi->fetchAll();
                                
                                if (!empty($valuefi)) {
                                    //$cnt = 1;
                                    foreach ($valuefi as $vfi) {
                                        $sqcol = "SELECT * FROM fn_fees_student_collection WHERE fn_fees_invoice_id = ".$iid." AND fn_fee_invoice_item_id =  " . $vfi["id"] . " AND ( transaction_id = ".$transactionId." OR partial_transaction_id = ".$transactionId." )  ";
                                        $resultcol = $connection2->query($sqcol);
                                        $valuecol = $resultcol->fetch();
                                        $itemAmt    = $valuecol["total_amount"];
                                        $itemAmtCol = $valuecol["total_amount_collection"];

                                        $sqdis = "SELECT * FROM fn_fee_item_level_discount WHERE pupilsightPersonID = ".$dt["stuid"]." AND item_id =  " . $vfi["id"] . " ";
                                        $resultdis = $connection2->query($sqdis);
                                        $valuedis = $resultdis->fetch();
                                        $disItemAmt = 0;
                                        if(!empty($valuedis)){
                                            $disItemAmt = $valuedis['discount'];
                                            $newItemAmtCol = $itemAmtCol + $disItemAmt;
                                            $itemAmtPen = $itemAmt - $newItemAmtCol;
                                        } else {
                                            $disItemAmt = 0;
                                            $itemAmtPen = $itemAmt - $itemAmtCol;
                                        }

                                        
                                        $taxamt = '0';
                                        if(!empty($vfi["tax"])){
                                            $taxamt = ($vfi["tax"] / 100) * $vfi["amount"];
                                            $taxamt = number_format($taxamt, 2, '.', '');
                                        }
                                        $dts_receipt_feeitem[] = array(
                                            "serial.all" => $cnt,
                                            "particulars.all" => htmlspecialchars(trim($vfi["name"])),
                                            "inv_amt.all" => $vfi["amount"],
                                            "tax.all" => $taxamt,
                                            "amount.all" => $vfi["total_amount"],
                                            //"inv_amt_paid.all" => number_format($vfi["total_amount"],2),
                                            "inv_amt_paid.all" => number_format($itemAmtCol,2),
                                            "inv_amt_pending.all" => number_format($itemAmtPen,2),
                                            "inv_amt_discount.all" => number_format($disItemAmt,2)
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

                        

                        $dts_receipt = array(
                            "academic_year" => $academic_year,
                            "invoice_no" => $invNo,
                            "receipt_no" => $receipt_number,
                            "date" => date("d-M-Y"),
                            "student_name" => $dt["name"],
                            "student_id" => $dt["stuid"],
                            "admission_no" => $stData["admission_no"],
                            "roll_no" => $stData["roll_no"],
                            "father_name" => $father_name,
                            "mother_name" => $mother_name,
                            "program_name" => $prog,
                            "class_section" => $class_section,
                            "class_name" => $class_name,
                            "section_name" => $section_name,
                            "instrument_date" => "NA",
                            "instrument_no" => "NA",
                            "transcation_amount" => $dt["amount"],
                            "fine_amount" => $dt["fine"],
                            "other_amount" => "NA",
                            "pay_mode" => "Online",
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
                            "total_amount_discount" => number_format($dt["discount"], 2, '.', ''),
                            "total_amount_pending" => number_format($totalPending, 2, '.', '')
                        );
                    }
                }
        
                if(!empty($concatInvId)){
                        
                    $invKountCon = count($concatInvId);
                    $firstInv = reset($concatInvId);
                    $lastInv = end($concatInvId);
                    if($invKountCon > 1){
                        $idsInvCon = $firstInv.','.$lastInv;
                    } else {
                        $idsInvCon = $firstInv;
                    }

                    $sqlconInvNew = 'SELECT GROUP_CONCAT(title SEPARATOR " - ") AS invtitle FROM fn_fee_invoice WHERE id IN ('.$idsInvCon.')';
                    $resultConInvNew = $connection2->query($sqlconInvNew);
                    $valueConInvNew = $resultConInvNew->fetch();
                    $concatInvTitle = $valueConInvNew['invtitle'];

                    $invconids = implode(',', $concatInvId);
                    $sqcs = "select SUM(fi.total_amount) AS tamnt, SUM(fi.amount) AS amnt, SUM(fi.tax) AS ttax from fn_fee_invoice_item as fi, fn_fee_items as items where fi.fn_fee_item_id = items.id and fi.fn_fee_invoice_id IN  (" . $invconids . ") ";
                    $resultfi = $connection2->query($sqcs);
                    $valuefi = $resultfi->fetch();

                    $taxamt = 0;
                    if(!empty($valuefi["ttax"])){
                        $taxamt = ($valuefi["ttax"] / 100) * $valuefi["amnt"];
                        $taxamt = number_format($taxamt, 2, '.', '');
                    }

                    if(!empty($dts_receipt_feeitem)){
                        $kountRow = count($dts_receipt_feeitem);
                    } else {
                        $kountRow = 0;
                    }

                    $sqcol = "SELECT SUM(total_amount) AS tamntCol , SUM(discount) AS disCol, SUM(total_amount_collection) AS ttamtCol FROM fn_fees_student_collection WHERE fn_fees_invoice_id IN  (" . $invconids . ") AND ( transaction_id = ".$transactionId." OR partial_transaction_id = ".$transactionId." )  ";
                    $resultcol = $connection2->query($sqcol);
                    $valuecol = $resultcol->fetch();
                    $itemAmt    = $valuecol["tamntCol"];
                    $itemAmtCol = $valuecol["ttamtCol"];

                    $sqitid = "SELECT GROUP_CONCAT(id) AS itmIds FROM fn_fee_invoice_item WHERE fn_fee_invoice_id IN  (" . $invconids . ") ";
                    $resultitid = $connection2->query($sqitid);
                    $valueitid = $resultitid->fetch();
                    $itmIDS = $valueitid['itmIds'];

                    $sqdis = "SELECT SUM(discount) AS dis FROM fn_fee_item_level_discount WHERE pupilsightPersonID = ".$pupilsightPersonID." AND item_id IN (".$itmIDS.") ";
                    $resultdis = $connection2->query($sqdis);
                    $valuedis = $resultdis->fetch();
                    $disItemAmt = 0;
                    if(!empty($valuedis)){
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
                        "inv_amt_paid.all" => number_format($itemAmtCol,2),
                        "inv_amt_pending.all" => number_format($itemAmtPen,2),
                        "inv_amt_discount.all" => number_format($disItemAmt,2)
                    );
                    $total = $total + $valuefi["tamnt"];
                    $totalTax = $totalTax + $taxamt;
                    $totalamtWitoutTaxDis = $totalamtWitoutTaxDis + $valuefi["amnt"];

                    if(!empty($dts_receipt_feeitem)){
                        array_push($dts_receipt_feeitem, $dts_receipt_feeitem1);
                    } else {
                        $dts_receipt_feeitem[] = $dts_receipt_feeitem1;
                    }

                }
        
                $_SESSION["dts_receipt_feeitem"] = $dts_receipt_feeitem;
                $_SESSION["dts_receipt"] = $dts_receipt;
                echo "New record created successfully";
            } else {
                echo "Error: " . $sq . "<br>" . $conn->error;
            }

            
        }

        // echo '<pre>';
        // print_r($dts_receipt);
        // print_r($dts_receipt_feeitem);
        // die();
       
        $conn->close();
    } catch (Exception $ex) {
        print_r($ex);
    }

    if (isset($callback)) {
        header('Location: ' . $callback);
        exit;
    } else {
        header('Location: index.php');
        exit;
    }
} else {
    header('Location: index.php');
    exit;
}