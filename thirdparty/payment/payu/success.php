
<?php

error_reporting(E_ERROR | E_PARSE);

include "payu/PayUClient.php";
include $_SERVER['DOCUMENT_ROOT'].'/pupilsight.php';

use payu\PayUClient;

// echo '<pre>';
// print_r($_POST);
// echo '</pre>';
// die();
$status=$_POST["status"];
$firstname=$_POST["firstname"];
$amount=$_POST["amount"]; //Please use the amount value from database
$txnid=$_POST["txnid"];
$posted_hash=$_POST["hash"];
$key=$_POST["key"];
$productinfo=$_POST["productinfo"];
$email=$_POST["email"];
$udf1 = $_POST["udf1"];
$udf2 = $_POST["udf2"];
$udf3 = $_POST["udf3"];
$udf4 = $_POST["udf4"];
$udf5 = $_POST["udf5"];

$returnArr = json_encode($_POST);

// echo $key;


$salt="OEyWyUkn"; //Please change the value with the live salt for production environment
// You should set your key & salt values to the function as below:
$payuClient = new PayUClient($key,$salt);

# Set params as follows
$params = array("status"=>$status,"txnid"=>$txnid,"amount"=>$amount,"productinfo"=>$productinfo,"firstname"=>$firstname,"email"=>$email,"udf1"=>$udf1,"udf2"=>$udf2,"udf3"=>$udf3,"udf4"=>$udf4,"udf5"=>$udf5);

# you can generate payment hash as follows:
$hash = new Hasher();
$reverse_hash = $hash->validate_hash($params);
// echo $reverse_hash;
// echo $posted_hash;

	if ($reverse_hash != $posted_hash) {
		echo "Transaction has been tampered. Please try again";
	}

	try{
		
        $sid = $udf2;
        $cid = $udf1;

        if(!empty($sid) && !empty($cid) && !empty($txnid) && $status == 'success'){

            $data = array('gateway' => 'PAYU', 'submission_id' => $sid, 'transaction_ref_no' => $mihpayid, 'order_id' => $txnid, 'amount' => $amount, 'status' => 'S');

            $sql = 'INSERT INTO fn_fee_payment_details SET gateway=:gateway, submission_id=:submission_id, transaction_ref_no=:transaction_ref_no, order_id=:order_id, amount=:amount, status=:status';
            $result = $connection2->prepare($sql);
            $result->execute($data);

            $crtd =  date('Y-m-d H:i:s');
            $cdt = date('Y-m-d H:i:s');

            $sqlfs = 'SELECT academic_id, pupilsightProgramID, fn_fee_structure_id, fn_fees_receipt_template_id  FROM campaign WHERE id = ' . $cid . ' ';
            $resultfs = $connection2->query($sqlfs);
            $campData = $resultfs->fetch();

            $sqlfs1 = 'SELECT pupilsightProgramID, pupilsightYearGroupID  FROM wp_fluentform_submissions WHERE id = ' . $sid . ' ';
            $resultfs1 = $connection2->query($sqlfs1);
            $submissionData = $resultfs1->fetch();

            $pupilsightProgramID = $campData['pupilsightProgramID'];
            $pupilsightYearGroupID = $submissionData['pupilsightYearGroupID'];

            $sqlchk = 'SELECT id  FROM fn_fee_invoice_applicant_assign WHERE submission_id = ' . $sid . ' ';
            $resultchk = $connection2->query($sqlchk);
            $chkInvoice = $resultchk->fetch();

            if (empty($chkInvoice['id'])) {
                if (!empty($campData['fn_fee_structure_id'])) {
                    $fn_fee_structure_id = $campData['fn_fee_structure_id'];
                    $id = $fn_fee_structure_id;
                    $datas = array('id' => $id);
                    $sqls = 'SELECT a.*, b.formatval FROM fn_fee_structure AS a LEFT JOIN fn_fee_series AS b ON a.inv_fee_series_id = b.id WHERE a.id=:id';
                    $results = $connection2->prepare($sqls);
                    $results->execute($datas);
                    $values = $results->fetch();


                    $datac = array('fn_fee_structure_id' => $id);
                    $sqlc = 'SELECT * FROM fn_fee_structure_item WHERE fn_fee_structure_id=:fn_fee_structure_id';
                    $resultc = $connection2->prepare($sqlc);
                    $resultc->execute($datac);
                    $childvalues = $resultc->fetchAll();


                    $data = array('title' => $values['invoice_title'], 'fn_fee_structure_id' => $id, 'pupilsightSchoolYearID' => $values['pupilsightSchoolYearID'], 'pupilsightSchoolFinanceYearID' => $values['pupilsightSchoolFinanceYearID'], 'inv_fn_fee_series_id' => $values['inv_fee_series_id'], 'rec_fn_fee_series_id' => $values['recp_fee_series_id'], 'fn_fees_head_id' => $values['fn_fees_head_id'], 'fn_fees_fine_rule_id' => $values['fn_fees_fine_rule_id'], 'fn_fees_discount_id' => $values['fn_fees_discount_id'], 'due_date' => $values['due_date'], 'amount_editable' => $values['amount_editable'], 'display_fee_item' => $values['display_fee_item'], 'cdt' => $cdt);

                    $sql = 'INSERT INTO fn_fee_invoice SET title=:title, fn_fee_structure_id=:fn_fee_structure_id, pupilsightSchoolYearID=:pupilsightSchoolYearID, pupilsightSchoolFinanceYearID=:pupilsightSchoolFinanceYearID, inv_fn_fee_series_id=:inv_fn_fee_series_id, rec_fn_fee_series_id=:rec_fn_fee_series_id, fn_fees_head_id=:fn_fees_head_id, fn_fees_fine_rule_id=:fn_fees_fine_rule_id, fn_fees_discount_id=:fn_fees_discount_id, due_date=:due_date, amount_editable=:amount_editable, display_fee_item=:display_fee_item, cdt=:cdt';
                    $result = $connection2->prepare($sql);
                    $result->execute($data);

                    $invId = $connection2->lastInsertID();

                    if (!empty($childvalues)) {
                        foreach ($childvalues as $cv) {
                            $feeitem = $cv['fn_fee_item_id'];
                            $desc = '';
                            $amt = $cv['amount'];
                            $taxdata = $cv['tax_percent'];
                            $disc = '';
                            $tamt = $cv['total_amount'];

                            if (!empty($feeitem) && !empty($amt)) {
                                $data1 = array('fn_fee_invoice_id' => $invId, 'fn_fee_item_id' => $feeitem, 'description' => $desc, 'amount' => $amt, 'tax' => $taxdata, 'discount' => $disc, 'total_amount' => $tamt);
                                $sql1 = "INSERT INTO fn_fee_invoice_item SET fn_fee_invoice_id=:fn_fee_invoice_id, fn_fee_item_id=:fn_fee_item_id, description=:description, amount=:amount,  tax=:tax, discount=:discount, total_amount=:total_amount";
                                $result1 = $connection2->prepare($sql1);
                                $result1->execute($data1);
                            }
                        }
                    }

                    $dataav = array('fn_fee_invoice_id' => $invId, 'pupilsightProgramID' => $pupilsightProgramID, 'pupilsightYearGroupID' => $pupilsightYearGroupID);
                    $sqlav = 'SELECT * FROM fn_fee_invoice_class_assign WHERE fn_fee_invoice_id=:fn_fee_invoice_id AND pupilsightProgramID=:pupilsightProgramID AND pupilsightYearGroupID=:pupilsightYearGroupID';
                    $resultav = $connection2->prepare($sqlav);
                    $resultav->execute($dataav);
                    if ($resultav->rowCount() == 0) {
                        $sql1av = 'INSERT INTO fn_fee_invoice_class_assign SET fn_fee_invoice_id=:fn_fee_invoice_id,pupilsightProgramID=:pupilsightProgramID, pupilsightYearGroupID=:pupilsightYearGroupID';
                        $result1av = $connection2->prepare($sql1av);
                        $result1av->execute($dataav);
                    }

                    $datastu = array('fn_fee_invoice_id' => $invId, 'submission_id' => $sid);
                    $sqlstu = 'SELECT * FROM fn_fee_invoice_applicant_assign WHERE fn_fee_invoice_id=:fn_fee_invoice_id AND submission_id=:submission_id';
                    $resultstu = $connection2->prepare($sqlstu);
                    $resultstu->execute($datastu);

                    if ($resultstu->rowCount() == 0) {
                        $invSeriesId = $values['inv_fee_series_id'];
                        // $invformat = explode('/',$values['format']);
                        $invformat = explode('$', $values['formatval']);
                        $iformat = '';
                        $orderwise = 0;
                        foreach ($invformat as $inv) {
                            if ($inv == '{AB}') {
                                $datafort = array('fn_fee_series_id' => $invSeriesId, 'order_wise' => $orderwise, 'type' => 'numberwise');
                                $sqlfort = 'SELECT id, no_of_digit, last_no FROM fn_fee_series_number_format WHERE fn_fee_series_id=:fn_fee_series_id AND order_wise=:order_wise AND type=:type';
                                $resultfort = $connection2->prepare($sqlfort);
                                $resultfort->execute($datafort);
                                $formatvalues = $resultfort->fetch();

                                $str_length = $formatvalues['no_of_digit'];

                                $iformat .= str_pad($formatvalues['last_no'], $str_length, '0', STR_PAD_LEFT);

                                $lastnoadd = $formatvalues['last_no'] + 1;

                                //$lastno = substr("0000000{$lastnoadd}", -$str_length);
                                $lastno = str_pad($lastnoadd, $str_length, '0', STR_PAD_LEFT);

                                $datafort1 = array('fn_fee_series_id' => $invSeriesId, 'order_wise' => $orderwise, 'type' => 'numberwise', 'last_no' => $lastno);
                                $sqlfort1 = 'UPDATE fn_fee_series_number_format SET last_no=:last_no WHERE fn_fee_series_id=:fn_fee_series_id AND type=:type AND order_wise=:order_wise';
                                $resultfort1 = $connection2->prepare($sqlfort1);
                                $resultfort1->execute($datafort1);
                            } else {
                                // $iformat .= $inv.'/';
                                $iformat .= $inv;
                            }
                            $orderwise++;
                        }

                        // $invoiceno =  rtrim($iformat, "/");
                        $invoiceno =  $iformat;
                        $dataistu = array('fn_fee_invoice_id' => $invId, 'invoice_no' => $invoiceno, 'submission_id' => $sid);
                        $sqlstu1 = 'INSERT INTO fn_fee_invoice_applicant_assign SET fn_fee_invoice_id=:fn_fee_invoice_id,invoice_no=:invoice_no, submission_id=:submission_id';
                        $resultstu1 = $connection2->prepare($sqlstu1);
                        $resultstu1->execute($dataistu);
                    }
                }

                if (!empty($invoiceno)) {

                    $counterid = '0';
                    $invoice_id = $invId;
                    $sqlinv = 'SELECT GROUP_CONCAT(id) as itemid FROM fn_fee_invoice_item WHERE fn_fee_invoice_id = ' . $invId . ' ';
                    $resultinv = $connection2->query($sqlinv);
                    $invitemData = $resultinv->fetch();

                    $invoice_item_id = $invitemData['itemid'];
                    $fn_fees_invoice_id = $invId;
                    $submission_id = $sid;
                    $pupilsightSchoolYearID = $campData['academic_id'];
                    $fn_fees_receipt_template_id = $campData['fn_fees_receipt_template_id'];

                    $is_custom = '';


                    // $payment_mode_id = $_POST['payment_mode_id'];
                    // $bank_id = $_POST['bank_id'];
                    // if (!empty($bank_id)) {
                    //     $sqlbn = 'SELECT name FROM fn_masters WHERE id = ' . $bank_id . ' ';
                    //     $resultbn = $connection2->query($sqlbn);
                    //     $bankNameData = $resultbn->fetch();
                    //     $bank_name = $bankNameData['name'];
                    // } else {
                    //     $bank_name = '';
					// }
					
					$payment_mode_id = '';
					$bank_name = '';
					$bank_id = '';
                    $dd_cheque_no = '';
                    $dd_cheque_date  = '';

                    $dd_cheque_amount = '';
                    $payment_status = 'Payment Received';
                    $payment_date  = date('Y-m-d');
                    $fn_fees_head_id = $values['fn_fees_head_id'];
                    $fn_fees_receipt_series_id = $values['recp_fee_series_id'];

					//$TrnAmt = ($response->getTrnAmt()) / 100;
					$TrnAmt = $amount;
                    $transcation_amount = $TrnAmt;
                    $amount_paying = $TrnAmt;

                    $total_amount_without_fine_discount = $TrnAmt;
                    $deposit = '';
                    $fine = '';
                    $discount = '';
                    $remarks = '';
                    $status = '1';
                    $cdt = date('Y-m-d H:i:s');
                    if (!empty($fn_fees_receipt_series_id)) {
                        $sqlrec = 'SELECT id, formatval FROM fn_fee_series WHERE id = "' . $fn_fees_receipt_series_id . '" ';
                        $resultrec = $connection2->query($sqlrec);
                        $recptser = $resultrec->fetch();

                        // $invformat = explode('/',$recptser['format']);
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
                                // $iformat .= $formatvalues['last_no'].'/';

                                $str_length = $formatvalues['no_of_digit'];

                                $iformat .= str_pad($formatvalues['last_no'], $str_length, '0', STR_PAD_LEFT);

                                $lastnoadd = $formatvalues['last_no'] + 1;

                                //$lastno = substr("0000000{$lastnoadd}", -$str_length);
                                $lastno = str_pad($lastnoadd, $str_length, '0', STR_PAD_LEFT);

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
                        $receipt_number = $_POST['receipt_number'];
                    }

                    $data = array('fn_fees_invoice_id' => $fn_fees_invoice_id, 'submission_id' => $submission_id, 'pupilsightSchoolYearID' => $pupilsightSchoolYearID, 'fn_fees_counter_id' => $counterid, 'receipt_number' => $receipt_number, 'is_custom' => $is_custom, 'payment_mode_id' => $payment_mode_id, 'bank_id' => $bank_id, 'dd_cheque_no' => $dd_cheque_no, 'dd_cheque_date' => $dd_cheque_date, 'dd_cheque_amount' => $dd_cheque_amount, 'payment_status' => $payment_status, 'payment_date' => $payment_date, 'fn_fees_head_id' => $fn_fees_head_id, 'fn_fees_receipt_series_id' => $fn_fees_receipt_series_id, 'transcation_amount' => $transcation_amount, 'total_amount_without_fine_discount' => $total_amount_without_fine_discount, 'amount_paying' => $amount_paying, 'fine' => $fine, 'discount' => $discount, 'remarks' => $remarks, 'status' => $status, 'cdt' => $cdt);

                    $sql = 'INSERT INTO fn_fees_collection SET fn_fees_invoice_id=:fn_fees_invoice_id, submission_id=:submission_id, pupilsightSchoolYearID =:pupilsightSchoolYearID, fn_fees_counter_id=:fn_fees_counter_id, receipt_number=:receipt_number, is_custom=:is_custom, payment_mode_id=:payment_mode_id, bank_id=:bank_id, dd_cheque_no=:dd_cheque_no, dd_cheque_date=:dd_cheque_date, dd_cheque_amount=:dd_cheque_amount, payment_status=:payment_status, payment_date=:payment_date, fn_fees_head_id=:fn_fees_head_id, fn_fees_receipt_series_id=:fn_fees_receipt_series_id, transcation_amount=:transcation_amount, total_amount_without_fine_discount=:total_amount_without_fine_discount, amount_paying=:amount_paying, fine=:fine, discount=:discount, remarks=:remarks, status=:status,cdt=:cdt';
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



                    if (!empty($invoice_item_id)) {
                        $itemId = explode(', ', $invoice_item_id);
                        foreach ($itemId as $itid) {
                            $dataf = array('id' => $itid, 'submission_id' => $submission_id);
                            $sqlf = 'SELECT a.fn_fee_invoice_id,b.invoice_no FROM fn_fee_invoice_item AS a LEFT JOIN fn_fee_invoice_applicant_assign AS b ON a.fn_fee_invoice_id = b.fn_fee_invoice_id WHERE a.id=:id AND b.submission_id=:submission_id';
                            $resultf = $connection2->prepare($sqlf);
                            $resultf->execute($dataf);
                            $values = $resultf->fetch();
                            $fn_fee_invoice_id = $values['fn_fee_invoice_id'];
                            $invoice_no = $values['invoice_no'];

                            $datai = array('submission_id' => $submission_id, 'transaction_id' => $transactionId,  'fn_fees_invoice_id' => $fn_fee_invoice_id, 'fn_fee_invoice_item_id' => $itid, 'invoice_no' => $invoice_no);
                            $sqli = 'INSERT INTO fn_fees_applicant_collection SET submission_id=:submission_id, transaction_id=:transaction_id, fn_fees_invoice_id=:fn_fees_invoice_id, fn_fee_invoice_item_id=:fn_fee_invoice_item_id, invoice_no=:invoice_no';
                            $resulti = $connection2->prepare($sqli);
                            $resulti->execute($datai);
                        }
                    }

                    $sqlrec = 'SELECT b.id, b.formatval FROM campaign AS a LEFT JOIN fn_fee_series AS b ON a.application_series_id = b.id WHERE a.id = "' . $cid . '" ';
                    $resultrec = $connection2->query($sqlrec);
                    $recptser = $resultrec->fetch();
        
                    $seriesId = $recptser['id'];
        
                    if (!empty($seriesId)) {
                        $invformat = explode('$', $recptser['formatval']);
                        $iformat = '';
                        $orderwise = 0;
                        foreach ($invformat as $inv) {
                            if ($inv == '{AB}') {
                                $datafort = array('fn_fee_series_id' => $seriesId, 'type' => 'numberwise');
                                $sqlfort = 'SELECT id, no_of_digit, last_no FROM fn_fee_series_number_format WHERE fn_fee_series_id=:fn_fee_series_id AND type=:type';
                                $resultfort = $connection2->prepare($sqlfort);
                                $resultfort->execute($datafort);
                                $formatvalues = $resultfort->fetch();
        
                                $str_length = $formatvalues['no_of_digit'];
        
                                $iformat .= str_pad($formatvalues['last_no'], $str_length, '0', STR_PAD_LEFT);
        
                                $lastnoadd = $formatvalues['last_no'] + 1;
        
                                $lastno = str_pad($lastnoadd, $str_length, '0', STR_PAD_LEFT);
        
                                $datafort1 = array('fn_fee_series_id' => $seriesId, 'type' => 'numberwise', 'last_no' => $lastno);
                                $sqlfort1 = 'UPDATE fn_fee_series_number_format SET last_no=:last_no WHERE fn_fee_series_id=:fn_fee_series_id AND type=:type ';
                                $resultfort1 = $connection2->prepare($sqlfort1);
                                $resultfort1->execute($datafort1);
                            } else {
                                $iformat .= $inv;
                            }
                            $orderwise++;
                        }
                        $application_id = $iformat;
                    } else {
                        $application_id = '';
                    }
        
                    $datafort12 = array('application_id' => $application_id, 'id' => $sid);
                    $sqlfort12 = 'UPDATE wp_fluentform_submissions SET application_id=:application_id WHERE id=:id';
                    $resultfort12 = $connection2->prepare($sqlfort12);
                    $resultfort12->execute($datafort12);

                    if (!empty($fn_fees_receipt_template_id)) {
                        $sqlstu = "SELECT  a.application_id, b.name as prog,c.name as class FROM wp_fluentform_submissions AS a LEFT JOIN pupilsightProgram AS b ON a.pupilsightProgramID = b.pupilsightProgramID LEFT JOIN pupilsightYearGroup AS c ON a.pupilsightYearGroupID = c.pupilsightYearGroupID WHERE a.id = " . $submission_id . " ";
                        $resultstu = $connection2->query($sqlstu);
                        $valuestu = $resultstu->fetch();


                        $sqlstu = 'SELECT field_value FROM wp_fluentform_entry_details WHERE submission_id = "' . $submission_id . '" AND field_name = "student_name" ';
                        $resultstu = $connection2->query($sqlstu);
                        $studetails = $resultstu->fetch();

                        //$class_section = $valuestu["prog"].' - '.$valuestu["class"];
                        $class_section = $valuestu["class"];
                        $dts_receipt = array(
                            "application_no" => $valuestu["application_id"],
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
                            "pay_mode" => 'Online',
                            "transactionId" => $transactionId,
                            "bank_name" => $bank_name
                        );

                        if (!empty($invoice_id)) {

                            $chsql = 'SELECT b.invoice_title, b.display_fee_item FROM fn_fee_invoice AS a LEFT JOIN fn_fee_structure AS b ON a.fn_fee_structure_id = b.id WHERE a.id= ' . $invoice_id . ' AND a.fn_fee_structure_id IS NOT NULL ';
                            $resultch = $connection2->query($chsql);
                            $valuech = $resultch->fetch();
                            if ($valuech['display_fee_item'] == '2') {
                                $sqcs = "select SUM(fi.total_amount) AS tamnt from fn_fee_invoice_item as fi, fn_fee_items as items where fi.fn_fee_item_id = items.id and fi.id in(" . $invoice_item_id . ")";
                                $resultfi = $connection2->query($sqcs);
                                $valuefi = $resultfi->fetchAll();
                                if (!empty($valuefi)) {
                                    $cnt = 1;
                                    foreach ($valuefi as $vfi) {
                                        $dts_receipt_feeitem[] = array(
                                            "serial.all" => $cnt,
                                            "particulars.all" => $valuech['invoice_title'],
                                            "amount.all" => $vfi["tamnt"]
                                        );
                                        $cnt++;
                                    }
                                }
                            } else {
                                $sqcs = "select fi.total_amount, items.name from fn_fee_invoice_item as fi, fn_fee_items as items where fi.fn_fee_item_id = items.id and fi.id in(" . $invoice_item_id . ")";
                                $resultfi = $connection2->query($sqcs);
                                $valuefi = $resultfi->fetchAll();
                                if (!empty($valuefi)) {
                                    $cnt = 1;
                                    foreach ($valuefi as $vfi) {
                                        $dts_receipt_feeitem[] = array(
                                            "serial.all" => $cnt,
                                            "particulars.all" => $vfi["name"],
                                            "amount.all" => $vfi["total_amount"]
                                        );
                                        $cnt++;
                                    }
                                }
                            }
                        }


                        $base_url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]";

                        $sqlet = "SELECT b.* FROM campaign AS a LEFT JOIN pupilsightTemplate AS b ON a.email_template_id = b.pupilsightTemplateID WHERE a.id = " . $cid . " ";
                        $resulte1 = $connection2->query($sqlet);
                        $resultet = $resulte1->fetch();

                        if (!empty($resultet)) {
                            $emailSubjct_camp = $resultet['subject'];
                            $emailquote = $resultet['description'];
                        } else {
                            $emailSubjct_camp = 'Application Status';
                            $emailquote = 'Your Application Submitted Successfully';
                        }

                        $sqlst = "SELECT b.* FROM campaign AS a LEFT JOIN pupilsightTemplate AS b ON a.sms_template_id = b.pupilsightTemplateID WHERE a.id = " . $cid . " ";
                        $resultst1 = $connection2->query($sqlst);
                        $resultst = $resultst1->fetch();

                        if (!empty($resultst)) {
                            $smsquote = $resultst['description'];
                        } else {
                            $smsquote = 'Your Application Submitted Successfully';
                        }

                        $subid = $sid;
                        $crtd =  date('Y-m-d H:i:s');


                        //$emailAttachment = $_FILES['emailAttachment'];

                        $crtd =  date('Y-m-d H:i:s');

                        $cuid = '001';

                        $sqle = "SELECT response FROM wp_fluentform_submissions WHERE id = " . $subid . " ";
                        $resulte = $connection2->query($sqle);
                        $rowdata = $resulte->fetch();
                        $sd = json_decode($rowdata['response'], TRUE);
                        $email = "";
                        $names = "";
                        $ft_number = '';
                        $mt_number = '';
                        $gt_number = '';
                        $ft_email = '';
                        $mt_email = '';
                        $gt_email = '';

                        if ($sd) {
                            // $names = implode(' ', $sd['student_name']);
                            // $email = $sd['father_email'];
                            if (!empty($sd['father_mobile'])) {
                                $ft_number = $sd['father_mobile'];
                            }
                            if (!empty($sd['mother_mobile'])) {
                                $mt_number = $sd['mother_mobile'];
                            }
                            if (!empty($sd['guardian_mobile'])) {
                                $gt_number = $sd['guardian_mobile'];
                            }

                            if (!empty($sd['father_email'])) {
                                $ft_email = $sd['father_email'];
                            }
                            if (!empty($sd['mother_email'])) {
                                $mt_email = $sd['mother_email'];
                            }
                            if (!empty($sd['guardian_email'])) {
                                $gt_email = $sd['guardian_email'];
                            }
                        }

                        //$email = "it.rakesh@gmail.com";
                        $subject = nl2br($emailSubjct_camp);
                        $body = nl2br($emailquote);
                        $msg = $smsquote;

                        $sqlm = "SELECT field_name,field_value FROM wp_fluentform_entry_details WHERE submission_id = " . $subid . " And field_name = 'numeric-field_1' ";
                        $resultm = $connection2->query($sqlm);
                        $rowdatm = $resultm->fetch();

                        if (!empty($emailquote) && !empty($body)) {

                            if (!empty($ft_email)) {
                                $url = $base_url . '/cms/mailsend.php';
                                $url .= "?to=" . $ft_email;
                                $url .= "&subject=" . rawurlencode($subject);
                                $url .= "&body=" . rawurlencode($body);
                                sendEmail($ft_email, $subject, $body, $subid, $cuid, $connection2, $url);
                            }
                            if (!empty($mt_email)) {
                                $url = $base_url . '/cms/mailsend.php';
                                $url .= "?to=" . $mt_email;
                                $url .= "&subject=" . rawurlencode($subject);
                                $url .= "&body=" . rawurlencode($body);
                                sendEmail($mt_email, $subject, $body, $subid, $cuid, $connection2, $url);
                            }
                            if (!empty($gt_email)) {
                                $url = $base_url . '/cms/mailsend.php';
                                $url .= "?to=" . $gt_email;
                                $url .= "&subject=" . rawurlencode($subject);
                                $url .= "&body=" . rawurlencode($body);
                                sendEmail($gt_email, $subject, $body, $subid, $cuid, $connection2, $url);
                            }
                        }

                        if (!empty($smsquote) && !empty($msg)) {
                            if (!empty($ft_number)) {
                                sendSMS($ft_number, $msg, $subid, $cuid, $connection2);
                            }
                            if (!empty($mt_number)) {
                                sendSMS($mt_number, $msg, $subid, $cuid, $connection2);
                            }
                            if (!empty($gt_number)) {
                                sendSMS($gt_number, $msg, $subid, $cuid, $connection2);
                            }
                        }


                        $_SESSION["dts_receipt_feeitem"] = $dts_receipt_feeitem;
                        $_SESSION["dts_receipt"] = $dts_receipt;
                        $_SESSION["appsubmitionid"] = $sid;

                        $URL = $_SESSION[$guid]['absoluteURL'] . "/cms/status.php";

                        /*
                        if (!empty($transactionId)) {
                            $URL = $_SESSION[$guid]['absoluteURL'] . "/cms/status.php?id=" . $sid;
                        } else {
                            $URL = $_SESSION[$guid]['absoluteURL'] . "/cms/status.php?id=" . $sid;
                        }*/

                        $_SESSION["admin_callback"] = $URL;
                        if (!empty($dts_receipt) && !empty($dts_receipt_feeitem)) {
                            $callback = $_SESSION[$guid]['absoluteURL'] . '/thirdparty/phpword/fee_receipt.php?fid=' . $fn_fees_receipt_template_id;
                            header('Location: ' . $callback);
                        }
                    } else {
                        $URL .= "&return=success0";
                        header('Location: ' . $URL);
                    }
                }
            }
            
        } else {
            $data = array('gateway' => 'PAYU', 'return_data' => $returnArr, 'status' => 'F1');

            $sql = 'INSERT INTO fn_fee_failed_payment_details SET gateway=:gateway, return_data=:return_data, status=:status';
            $result = $connection2->prepare($sql);
            $result->execute($data);
        }



    }catch(Exception $ex){
        print_r($ex);
	}
	die();

    if(isset($callback)){
        header('Location: '.$callback);
        exit;
    }else{
        header('Location: index.php');
        exit;
	}
	

?>


<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta http-equiv="X-UA-Compatible" content="ie=edge">
<title>PayU</title>
<meta name="description" content="">
<link rel="stylesheet" type="text/css" href="css/layout.css">
<link rel="stylesheet" type="text/css" href="css/typography.css">
</head>
<body class="page-bg-gray">
<div class="main">
<header>
<div class="header-main gray-hdr">
<div class="hd-logo"><img src="images/logo.svg" alt="PayU Logo"></div>
<div class="hd-nav">
<ul>
<li><a href="https://github.com/payu-india/payu-sdk-php"><i><img src="images/github-icon.svg"></i>View on Github</a></li>
</ul>
</div>
</div>
</header>

<section>
<div class="common-container">
<div class="container">
<div class="code-main-wrap">
<div class="code-container">
<i class="pay-icon">
<img src="images/success-icon.png">
</i>
<h1>Payment Success</h1>
<p>Your Payment has been successful.></p>
<div class="vs-code-main">
<h2>PAYMENT S0URCE OBJECT</h2>
<div class="code-main">
<code>
<p>{</p>
<p>"txnid": <?php echo $txnid ?>,</p>
<p>"status": <?php echo $status ?>,</p>
<p>"details": {</p>
<!-- <p>"statement_descriptor": <span>null,</span></p>
 --><!-- <p>"native_url": <span>null,</span></p>
<p>"data_string": <span>null</span></p> -->
<!-- <p>},</p> -->
<p>"amount": <?php echo $amount ?>,</p>
<p>"productinfo": <?php $firstname ?>,</p>
<p>"firstname": <?php $firstname ?>,</p>
<p>"emailid": <?php $email ?></p>
<!-- <p>"currency": "eur",</p>
<p>"flow": "redirect",</p>
<p>"livemode": false,</p>
<p>"metadata": {</p>
<p>"paymentIntent": "pi_1GaGJvLqkZN1XDm1WnK8JsLo"</p>
<p>},</p> -->
<p>}</p>
<p>}</p>
</code>
</div>

</div>
</div>

</div>
</section>
</div>

</body>
</html>
