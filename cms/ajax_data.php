<?php
error_reporting(E_ERROR | E_PARSE);
include_once 'w2f/adminLib.php';
include '../pupilsight.php';

use Pupilsight\Contracts\Comms\SMS;

$adminlib = new adminlib();
session_start();
//$input = $_SESSION['campaignuserdata'];
$type = $_POST['type'];
if ($type == 'insertcampaigndetails') {
	$campid = $_POST['val'];
	$pupilsightProgramID = $_POST['pid'];
	$form_id = $_POST['fid'];
	$pupilsightYearGroupID = $_POST['clid'];
	$submissionId = $_SESSION['submissionId'];
	$chkfeeSett = $_POST['chkfeeSett'];
	if (!empty($pupilsightYearGroupID) && !empty($submissionId)) {
		//$insert = $adminlib->createCampaignRegistration($input, $campid);

		$sqlchfee = "SELECT is_fee_generate FROM campaign WHERE id = " . $campid . " ";
		$resultchkfee = database::doSelectOne($sqlchfee);
		$chkfeeSettNew = $resultchkfee['is_fee_generate'];

		// if($chkfeeSett == '2'){
		if ($chkfeeSettNew == '2') {
			$insert = $adminlib->updateApplicantData($submissionId, $pupilsightProgramID, $pupilsightYearGroupID);
		} else {
			$sql = "SELECT b.id, b.formatval FROM campaign AS a LEFT JOIN fn_fee_series AS b ON a.application_series_id = b.id WHERE a.id = " . $campid . " ";
			$result = database::doSelectOne($sql);

			if (!empty($result['formatval'])) {
				$seriesId = $result['id'];
				$invformat = explode('$', $result['formatval']);
				$iformat = '';
				$orderwise = 0;
				foreach ($invformat as $inv) {
					if ($inv == '{AB}') {
						$sqlfort = 'SELECT id, no_of_digit, last_no FROM fn_fee_series_number_format WHERE fn_fee_series_id=' . $seriesId . ' AND type= "numberwise"';
						$formatvalues = database::doSelectOne($sqlfort);


						$str_length = $formatvalues['no_of_digit'];

						$iformat .= str_pad($formatvalues['last_no'], $str_length, '0', STR_PAD_LEFT);

						$lastnoadd = $formatvalues['last_no'] + 1;

						$lastno = str_pad($lastnoadd, $str_length, '0', STR_PAD_LEFT);

						$sql1 = "UPDATE fn_fee_series_number_format SET last_no= " . $lastno . " WHERE fn_fee_series_id= " . $seriesId . " AND type= 'numberwise'  ";
						$result1 = database::doUpdate($sql1);
					} else {
						$iformat .= $inv;
					}
					$orderwise++;
				}
				$application_id = $iformat;
			} else {
				$application_id = '';
			}

			$insert = $adminlib->updateApplicantData2($submissionId, $pupilsightProgramID, $pupilsightYearGroupID, $application_id);

			$sqlfs = 'SELECT academic_id, pupilsightProgramID, fn_fee_structure_id, fn_fees_receipt_template_id  FROM campaign WHERE id = ' . $campid . ' ';
			$resultfs = $connection2->query($sqlfs);
			$campData = $resultfs->fetch();

			if (!empty($campData['fn_fee_structure_id'])) {
				$crtd =  date('Y-m-d H:i:s');
				$cdt = date('Y-m-d H:i:s');
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

				$datastu = array('fn_fee_invoice_id' => $invId, 'submission_id' => $submissionId);
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
					$dataistu = array('fn_fee_invoice_id' => $invId, 'invoice_no' => $invoiceno, 'submission_id' => $submissionId);
					$sqlstu1 = 'INSERT INTO fn_fee_invoice_applicant_assign SET fn_fee_invoice_id=:fn_fee_invoice_id,invoice_no=:invoice_no, submission_id=:submission_id';
					$resultstu1 = $connection2->prepare($sqlstu1);
					$resultstu1->execute($dataistu);
				}
			}

			$base_url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]";

			$sqlet = "SELECT b.* FROM campaign AS a LEFT JOIN pupilsightTemplate AS b ON a.email_template_id = b.pupilsightTemplateID WHERE a.id = " . $campid . " ";
			$resulte1 = $connection2->query($sqlet);
			$resultet = $resulte1->fetch();

			if (!empty($resultet)) {
				$emailSubjct_camp = $resultet['subject'];
				$emailquote = $resultet['description'];
			} else {
				$emailSubjct_camp = 'Application Status';
				$emailquote = 'Your Application Submitted Successfully';
			}

			$sqlst = "SELECT b.* FROM campaign AS a LEFT JOIN pupilsightTemplate AS b ON a.sms_template_id = b.pupilsightTemplateID WHERE a.id = " . $campid . " ";
			$resultst1 = $connection2->query($sqlst);
			$resultst = $resultst1->fetch();

			if (!empty($resultst)) {
				$smsquote = $resultst['description'];
			} else {
				$smsquote = 'Your Application Submitted Successfully';
			}

			$subid = $submissionId;
			$crtd =  date('Y-m-d H:i:s');
			$crtd =  date('Y-m-d H:i:s');
			$cuid = '001';

			$sqle = "SELECT response, application_id FROM wp_fluentform_submissions WHERE id = " . $subid . " ";
			$resulte = $connection2->query($sqle);
			$rowdata = $resulte->fetch();
			$sd = json_decode($rowdata['response'], TRUE);
			$email = "";
			$names = "";
			$st_name = '';
			$ft_name = '';
			$mt_name = '';
			$ft_number = '';
			$mt_number = '';
			$gt_number = '';
			$ft_email = '';
			$mt_email = '';
			$gt_email = '';
			$application_no = $rowdata['application_id'];

			if ($sd) {
				// $names = implode(' ', $sd['student_name']);
				// $email = $sd['father_email'];
				if (!empty($sd['father_name'])) {
					$ft_name = $sd['father_name'];
				}
				if (!empty($sd['mother_name'])) {
					$mt_name = $sd['mother_name'];
				}
				if (!empty($sd['student_name'])) {
					$st_name = $sd['student_name'];
				}

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

			$emailquote = str_replace("@student_name", $st_name, $emailquote);
			$emailquote = str_replace("@father_name", $ft_name, $emailquote);
			$emailquote = str_replace("@mother_name", $mt_name, $emailquote);
			
			$smsquote = str_replace("@student_name", $st_name, $smsquote);
			$smsquote = str_replace("@father_name", $ft_name, $smsquote);
			$smsquote = str_replace("@mother_name", $mt_name, $smsquote);
			
			if(!empty($application_no)){
				$emailquote = str_replace("@application_no", $application_no, $emailquote);
				$smsquote = str_replace("@application_no", $application_no, $smsquote);
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
					//echo $url;
					sendEmail($ft_email, $subject, $body, $subid, $cuid, $connection2, $url);
				}
				if (!empty($mt_email)) {
					$url = $base_url . '/cms/mailsend.php';
					$url .= "?to=" . $mt_email;
					$url .= "&subject=" . rawurlencode($subject);
					$url .= "&body=" . rawurlencode($body);
					//echo $url;
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
					sendSMS($ft_number, $msg, $subid, $cuid, $connection2, $container);
				}
				if (!empty($mt_number)) {
					sendSMS($mt_number, $msg, $subid, $cuid, $connection2, $container);
				}
				if (!empty($gt_number)) {
					sendSMS($gt_number, $msg, $subid, $cuid, $connection2, $container);
				}
			}
			unset($_SESSION["submissionId"]);
		}
	}
}

if ($type == 'saveApplicantForm') {
	$submissionId = $_SESSION['submissionId'];
	$data = base64_decode($_POST['pdf']);
	// print_r($data);
	file_put_contents("../public/applicationpdf/" . $submissionId . "-application.pdf", $data);
}

if ($type == 'chkPreviousSubmission') {
	$val = $_POST['val'];
	$form_id = $_POST['fid'];
	$sql = 'SELECT id FROM wp_fluentform_entry_details WHERE form_id=' . $form_id . ' AND field_value = "' . $val . '" ';
	$result = database::doSelectOne($sql);
	if (!empty($result)) {
		echo '1';
	} else {
		echo '2';
	}
}

if ($type == 'getCampClass') {
	$val = $_POST['val'];
	$cid = $_POST['cid'];
	$sql = 'SELECT a.id, b.pupilsightYearGroupID, b.name FROM campaign_prog_class AS a LEFT JOIN pupilsightYearGroup AS b ON a.pupilsightYearGroupID = b.pupilsightYearGroupID WHERE a.pupilsightProgramId=' . $val . ' AND a.campaign_id = "' . $cid . '" ';
	$result = database::doSelect($sql);
	$data = '<option value="">Select Class</option>';
	if (!empty($result)) {
		foreach ($result as $k => $cl) {
			$data .= '<option value="' . $cl['pupilsightYearGroupID'] . '">' . $cl['name'] . '</option>';
		}
	}
	echo $data;
}

function sendSMS($number, $msg, $subid, $cuid, $connection2, $container)
{
	// $urls = "https://enterprise.smsgupshup.com/GatewayAPI/rest?method=SendMessage";
	// $urls .= "&send_to=" . $number;
	// $urls .= "&msg=" . rawurlencode($msg);
	// $urls .= "&msg_type=TEXT&userid=2000185422&auth_scheme=plain&password=StUX6pEkz&v=1.1&format=text";
	// $resms = file_get_contents($urls);

	$sms = $container->get(SMS::class);
    $res = $sms->sendSMSPro($number, $msg);

	$sq = "INSERT INTO campaign_email_sms_sent_details SET  submission_id = " . $subid . ", phone=" . $number . ", description='" . stripslashes($msg) . "', pupilsightPersonID=" . $cuid . " ";
	$connection2->query($sq);
}

function sendEMail($to, $subject, $body, $subid, $cuid, $connection2, $url)
{
	//sending attachment
	$res = file_get_contents($url);
	$sq = "INSERT INTO campaign_email_sms_sent_details SET  submission_id = " . $subid . ", email='" . $to . "', subject='" . $subject . "', description='" . $body . "', pupilsightPersonID=" . $cuid . " ";
	$connection2->query($sq);
}
//echo $msg;
