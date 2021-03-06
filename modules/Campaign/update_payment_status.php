<?php
/*
Pupilsight, Flexible & Open School System
*/
include '../../pupilsight.php';
use Pupilsight\Forms\Form;
use Pupilsight\Forms\DatabaseFormFactory;

if (isActionAccessible($guid, $connection2, '/modules/Campaign/ajax_add_wf_transitions.php') == false) {
    //Acess denied
    echo "<div class='error'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {  
    $cid = $_GET['cid'];
    $sid = $_GET['sid'];
    $crtd =  date('Y-m-d H:i:s');
	$cdt = date('Y-m-d H:i:s');
// die();
    // foreach($subID as $sid){
        $sqlfs = 'SELECT academic_id, pupilsightProgramID, fn_fee_structure_id, fn_fees_receipt_template_id  FROM campaign WHERE id = ' . $cid . ' ';
		$resultfs = $connection2->query($sqlfs);
		$campData = $resultfs->fetch();

		$sqlfs1 = 'SELECT pupilsightProgramID, pupilsightYearGroupID, created_at  FROM wp_fluentform_submissions WHERE id = ' . $sid . ' ';
		$resultfs1 = $connection2->query($sqlfs1);
		$submissionData = $resultfs1->fetch();

		$feeReceiptdate = date('d-M-Y', strtotime($submissionData['created_at']));
		

		$pupilsightProgramID = $campData['pupilsightProgramID'];
		$pupilsightYearGroupID = $submissionData['pupilsightYearGroupID'];

		$sqlchk = 'SELECT id  FROM fn_fee_invoice_applicant_assign WHERE submission_id = ' . $sid . ' ';
		$resultchk = $connection2->query($sqlchk);
        $chkInvoice = $resultchk->fetch();
        
        if (!empty($chkInvoice['id'])) {
            $data = array('submission_id' => $sid);
            $sql = 'DELETE FROM fn_fee_invoice_applicant_assign WHERE submission_id=:submission_id';
            $result = $connection2->prepare($sql);
            $result->execute($data);

            $sql1 = 'DELETE FROM fn_fees_collection WHERE submission_id=:submission_id';
            $result1 = $connection2->prepare($sql1);
            $result1->execute($data);

            $sql2 = 'DELETE FROM fn_fees_applicant_collection WHERE submission_id=:submission_id';
            $result2 = $connection2->prepare($sql2);
            $result2->execute($data);
        }

		//if (empty($chkInvoice['id'])) {
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
				$dd_cheque_no = '';
				$dd_cheque_date  = '';

				$dd_cheque_amount = '';
				$payment_status = 'Payment Received';
				// $payment_date  = date('Y-m-d');
				$payment_date  = '2020-10-28';
				$fn_fees_head_id = $values['fn_fees_head_id'];
				$fn_fees_receipt_series_id = $values['recp_fee_series_id'];

				$TrnAmt = 300;
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
						"date" => $feeReceiptdate,
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


					$_SESSION["dts_receipt_feeitem"] = $dts_receipt_feeitem;
					$_SESSION["dts_receipt"] = $dts_receipt;
					if (!empty($transactionId)) {
						$URL = $_SESSION[$guid]['absoluteURL'] . "/cms/status.php?id=" . $sid;
					} else {
						$URL = $_SESSION[$guid]['absoluteURL'] . "/cms/status.php?id=" . $sid;
					}

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
        //} 
    // }
}
?>