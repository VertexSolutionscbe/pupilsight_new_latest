<?php

include_once 'w2f/adminLib.php';
$adminlib = new adminlib();

$data = $_POST['val'];
$camdata = $adminlib->getApplist($data);

if (!empty($camdata)) {
?>
	<?php
	$cnt = 1;
	foreach ($camdata as $row) {
		if (!empty($row['id']) || $row['form_id'] || $row['submission_id']) {
			$statedata = $adminlib->getstatedata($row['id'], $row['form_id'], $row['submission_id']);
			echo "<tr>";
			echo '<td>';
			echo $cnt;
			echo '</td>';
			echo '<td>';
			echo $row["username"];
			echo '</td>';
			echo '<td>';
			echo $row["name"];
			echo '</td>';
			echo '<td>';
			echo $row['created_at'];
			echo '</td>';
			echo '<td>';

			if ($row['is_fee_generate'] == '2') {
				if (!empty($row['transaction_id'])) {
					echo "Submitted";
				} else {
					echo "Created";
				}
			} else {
				echo "Submitted";
			}
			// if (!empty($row['transaction_id'])) {
			// 	echo $statedata;
			// } else {
			// 	if ($row['is_fee_generate'] == '2') {
			// 		echo "Created";
			// 	} else {
			// 		echo "Submitted";
			// 	}
			// }

			echo '</td>';
			echo '<td>';
			if (!empty($row['application_no']) && !empty($row['transaction_id'])) {
				$base_url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]";
				$fname = trim(str_replace("/", "_", $row['application_no']));
				//$link = $base_url . '/public/applicationpdf/parent/' . $fname;
				$link = $base_url . '/cms/ajaxfile.php?cid=' . $row['id'] . "&submissionId=" . $row['submission_id'];
				echo '<a href="' . $link . '"><img title="Download" src="' . $base_url . '/cms/assets/css/img/download-box.png"></img></a>';
			} else {
				echo "NA";
			}
			echo '</td>';
			echo '<td>';
			if (!empty($row['transaction_id'])) {
				$base_url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]";
				$link = $base_url . '/public/receipts/' . $row['transaction_id'];
				echo '<a href="' . $link . '" download><img title="Download" src="' . $base_url . '/cms/assets/css/img/download-box.png"></img></a>';
			} else {
				$base_url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]";
				$responseLink = $base_url . "/thirdparty/payment/worldline/skit/meTrnSuccess.php";
				$sid = $row['submission_id'];
				$cid = $row['id'];

				if (!empty($cid) && !empty($sid)) {
					$sqlchk = 'SELECT fn_fee_structure_id FROM campaign WHERE id = ' . $cid . ' ';
					$resultchk = database::doSelectOne($sqlchk);

					if (!empty($resultchk['fn_fee_structure_id'])) {
						$sql = "SELECT SUM(total_amount) AS amt FROM fn_fee_structure_item WHERE fn_fee_structure_id = " . $resultchk['fn_fee_structure_id'] . " ";
						$result = database::doSelectOne($sql);
						$applicationAmount = $result['amt'] * 100;

						$random_number = mt_rand(1000, 9999);
						$today = time();
						$OrderId = $today . $random_number;

						$sqlchk = "SELECT name FROM fn_fee_payment_gateway";
						$resultchk = database::doSelectOne($sqlchk);
						$gateway = $resultchk['name'];

						if (!empty($gateway)) {
							if ($gateway == 'WORLDLINE') {
	?>
								<form id="admissionPay" action="<?php echo $base_url; ?>/thirdparty/payment/worldline/skit/meTrnPay.php" method="post">
									<input type="hidden" value="<?php echo $OrderId; ?>" id="OrderId" name="OrderId">
									<input type="hidden" name="amount" value="<?php echo $applicationAmount; ?>">
									<input type="hidden" value="INR" id="currencyName" name="currencyName">
									<input type="hidden" value="S" id="meTransReqType" name="meTransReqType">
									<input type="hidden" name="mid" id="mid" value="WL0000000009424">
									<input type="hidden" name="enckey" id="enckey" value="4d6428bf5c91676b76bb7c447e6546b8">
									<input type="hidden" name="campaignid" value="<?php echo $cid; ?>">
									<input type="hidden" name="sid" value="<?php echo $sid; ?>">
									<input type="hidden" name="responseUrl" id="responseUrl" value="<?php echo $responseLink; ?>" />
									<button type="submit" class="btn btn-primary" id="payAdmissionFee">Pay</button>
								</form>
							<?php } elseif ($gateway == 'RAZORPAY') {
								$sqlo = "SELECT * FROM pupilsight_cms  WHERE title != '' ";
								$orgData = database::doSelectOne($sqlo);

								$base_url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]";
								$responseLink = $base_url . "/cms/index.php?return=1";
							?>

								<form id="admissionPay" action="<?php echo $base_url; ?>/thirdparty/paymentadm/razorpay/pay.php" method="post">
									<input type="hidden" value="<?php echo $OrderId; ?>" id="OrderId" name="OrderId">
									<input type="hidden" name="amount" value="<?php echo $applicationAmount; ?>">
									<input type="hidden" value="INR" id="currencyName" name="currencyName">
									<input type="hidden" value="S" id="meTransReqType" name="meTransReqType">
									<input type="hidden" name="mid" id="mid" value="WL0000000009424">
									<input type="hidden" name="enckey" id="enckey" value="4d6428bf5c91676b76bb7c447e6546b8">
									<input type="hidden" name="campaignid" value="<?php echo $cid; ?>">
									<input type="hidden" name="sid" value="<?php echo $sid; ?>">
									<input type="hidden" name="responseUrl" id="responseUrl" value="<?php echo $responseLink; ?>" />

									<input type="hidden" value="<?php echo $orgData['title']; ?>" id="organisationName" name="organisationName">
									<input type="hidden" value="<?php echo $orgData['logo_image']; ?>" id="organisationLogo" name="organisationLogo">
									<button type="submit" class="btn btn-primary" id="payAdmissionFee">Pay</button>
								</form>

							<?php } elseif ($gateway == 'PAYU') { ?>
								<form id="admissionPay" action="<?php echo $base_url; ?>/thirdparty/payment/payu/checkout.php" method="post">
									<input type="hidden" value="<?php echo $OrderId; ?>" id="OrderId" name="OrderId">
									<input type="hidden" name="amount" value="<?php echo $applicationAmount; ?>">
									<input type="hidden" value="INR" id="currencyName" name="currencyName">
									<input type="hidden" value="S" id="meTransReqType" name="meTransReqType">

									<input type="hidden" class="applicantName" name="name" value="<?php echo $row['username'] ?>">
									<input type="hidden" class="applicantEmail" name="email" value="<?php echo $row['email'] ?>">
									<input type="hidden" class="applicantPhone" name="phone" value="<?php echo $row['phone'] ?>">
									<input type="hidden" name="campaignid" value="<?php echo $cid; ?>">
									<input type="hidden" name="sid" value="<?php echo $sid; ?>">
									<input type="hidden" name="responseUrl" id="responseUrl" value="<?php echo $responseLink; ?>" />
									<button type="submit" class="btn btn-primary" id="payAdmissionFee">Pay</button>
								</form>
						<?php }
						} ?>
<?php
					}
				}
			}
			echo '</td>';
			echo '</tr>';
		} else {
			echo "<tr>";
			echo '<td colspan="4">No Record Found</td>';
			echo '</tr>';
		}
		$cnt++;
	}
}
?>