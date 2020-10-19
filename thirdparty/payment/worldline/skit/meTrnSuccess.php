<?php

/**
 * This Is the Kit File To Be included For Transaction Request/Response
 */

include 'AWLMEAPI.php';
include $_SERVER["DOCUMENT_ROOT"] . '/pupilsight.php';

//create an Object of the above included class
$obj = new AWLMEAPI();

/* This is the response Object */
$resMsgDTO = new ResMsgDTO();

/* This is the request Object */
$reqMsgDTO = new ReqMsgDTO();

//This is the Merchant Key that is used for decryption also
$enc_key = "4d6428bf5c91676b76bb7c447e6546b8";

/* Get the Response from the WorldLine */
$responseMerchant = $_REQUEST['merchantResponse'];

$response = $obj->parseTrnResMsg($responseMerchant, $enc_key);

if ($response->getStatusCode() == 'S') {
	$sid = $response->getAddField1();
	$cid = $response->getAddField2();
	if (!empty($sid) && !empty($cid)) {
		$stuId = $sid;
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
							// $iformat .= $formatvalues['last_no'].'/';
							$iformat .= $formatvalues['last_no'];

							$str_length = $formatvalues['no_of_digit'];

							$lastnoadd = $formatvalues['last_no'] + 1;

							$lastno = substr("0000000{$lastnoadd}", -$str_length);

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
				$payment_date  = date('Y-m-d');
				$fn_fees_head_id = $values['fn_fees_head_id'];
				$fn_fees_receipt_series_id = $values['recp_fee_series_id'];

				$TrnAmt = ($response->getTrnAmt()) / 100;
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
		}
	} else {
	}
} else {
	$backUrl = $_SESSION[$guid]['absoluteURL'] . '/cms/index.php';
	$responseLink = $_SESSION[$guid]['absoluteURL'] . "/thirdparty/payment/worldline/skit/meTrnSuccess.php";

	$sid = $response->getAddField1();
	$cid = $response->getAddField2();

	$random_number = mt_rand(1000, 9999);
	$today = time();
	$orderId = $today . $random_number;

	$sql = "SELECT SUM(b.total_amount) AS amt FROM campaign AS a LEFT JOIN fn_fee_structure_item AS b ON a.fn_fee_structure_id = b.fn_fee_structure_id WHERE a.id = " . $cid . " ";
	$result = $connection2->query($sql);
	$resultData = $result->fetch();
	$applicationAmount = $resultData['amt'] * 100;
?>
	<div style="text-align:center;">
		<div style='color:red;font-size:18px;border:1px red solid;padding:10px;'>Transcation is cancelled, please try again.</div>

		<div style="display:inline-flex;">
			<form id="admissionPay" action="<?php echo $_SESSION[$guid]['absoluteURL'] ?>/thirdparty/payment/worldline/skit/meTrnPay.php" method="post">

				<input type="hidden" value="<?php echo $orderId; ?>" id="OrderId" name="OrderId">
				<input type="hidden" name="amount" value="<?php echo $applicationAmount; ?>">
				<input type="hidden" value="INR" id="currencyName" name="currencyName">
				<input type="hidden" value="S" id="meTransReqType" name="meTransReqType">
				<input type="hidden" name="mid" id="mid" value="WL0000000009424">
				<input type="hidden" name="enckey" id="enckey" value="4d6428bf5c91676b76bb7c447e6546b8">
				<input type="hidden" name="campaignid" value="<?php echo $cid; ?>">
				<input type="hidden" name="sid" value="<?php echo $sid; ?>">
				<input type="hidden" name="responseUrl" id="responseUrl" value="<?php echo $responseLink; ?>" />

				<button type="submit" class="btnPay" id="payAdmissionFee">Pay</button>
			</form>
			<a class="btnBack" href="<?php echo $backUrl; ?>">Back</a>
		</div>
	</div>
<?php
}
?>

<style>
	.btnBack {
		display: inline-block;
		font-weight: bold;
		font-size: 20px;
		width: 200px;
		line-height: 1.4285714;
		text-align: center;
		vertical-align: middle;
		cursor: pointer;
		padding: 0.4375rem 1rem;
		border-radius: 3px;
		color: #ffffff !important;
		background-color: #206bc4;
		border-color: #206bc4;
		margin: 20px 0 16px 0;
		text-decoration: none;

	}

	.btnPay {
		display: inline-block;
		font-weight: bold;
		font-size: 20px;
		width: 200px;
		line-height: 1.4285714;
		text-align: center;
		vertical-align: middle;
		cursor: pointer;
		padding: 0.4375rem 1rem;
		border-radius: 3px;
		color: #ffffff !important;
		background-color: #206bc4;
		border-color: #206bc4;
		margin: 20px 20px 0 0px;
	}
</style>
<?php

/*
?>
<style>
	body {
		font-family: Verdana, sans-serif;
		font-size: :12px;
	}

	.wrapper {
		width: 980px;
		margin: 0 auto;
	}

	table {}

	tr {
		padding: 5px
	}

	td {
		padding: 5px;
	}

	input {
		padding: 5px;
	}
</style>
<form action="testTxnStatus.php" method="POST">
	<center>
		<H3>Transaction Status </H3>
	</center>
	<table>
		<tr>
			<!-- PG transaction reference number-->
			<td><label for="txnRefNo">Transaction Ref No. :</label></td>
			<td><?php echo $response->getPgMeTrnRefNo(); ?></td>
			<!-- Merchant order number-->
			<td><label for="orderId">Order No. :</label></td>
			<td><?php echo $response->getOrderId(); ?> </td>
			<!-- Transaction amount-->
			<td><label for="amount">Amount :</label></td>
			<td><?php echo $response->getTrnAmt(); ?></td>
		</tr>
		<tr>
			<!-- Transaction status code-->
			<td><label for="statusCode">Status Code :</label></td>
			<td><?php echo $response->getStatusCode(); ?></td>

			<!-- Transaction status description-->
			<td><label for="statusDesc">Status Desc :</label></td>
			<td><?php echo $response->getStatusDesc(); ?></td>

			<!-- Transaction date time-->
			<td><label for="txnReqDate">Transaction Request Date :</label></td>
			<td><?php echo $response->getTrnReqDate(); ?></td>
		</tr>
		<tr>
			<!-- Transaction response code-->
			<td><label for="responseCode">Response Code :</label></td>
			<td><?php echo $response->getResponseCode(); ?></td>

			<!-- Bank reference number-->
			<td><label for="statusDesc">RRN :</label></td>
			<td><?php echo $response->getRrn(); ?></td>
			<!-- Authzcode-->
			<td><label for="authZStatus">AuthZCode :</label></td>
			<td><?php echo $response->getAuthZCode(); ?></td>
		</tr>
		<tr>
			<!-- Additional fields for merchant use-->
			<td><label for="addField1">Add Field 1 :</label></td>
			<td><?php echo $response->getAddField1(); ?></td>

			<td><label for="addField2">Add Field 2 :</label></td>
			<td><?php echo $response->getAddField2(); ?></td>

			<td><label for="addField3">Add Field 3 :</label></td>
			<td><?php echo $response->getAddField3(); ?></td>
		</tr>
		<tr>
			<td><label for="addField4">Add Field 4 :</label></td>
			<td><?php echo $response->getAddField4(); ?></td>

			<td><label for="addField5">Add Field 5 :</label></td>
			<td><?php echo $response->getAddField5(); ?></td>

			<td><label for="addField6">Add Field 6 :</label></td>
			<td><?php echo $response->getAddField6(); ?></td>
		</tr>
		<tr>
			<td><label for="addField7">Add Field 7 :</label></td>
			<td><?php echo $response->getAddField7(); ?></td>

			<td><label for="addField8">Add Field 8 :</label></td>
			<td><?php echo $response->getAddField8(); ?></td>
		</tr>

	</table>
</form>
<?php */
