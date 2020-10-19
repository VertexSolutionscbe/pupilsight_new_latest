<?php

include_once 'w2f/adminLib.php';
$adminlib = new adminlib();
$data = $_POST['val'];
$camdata = $adminlib->getApplist($data);
// echo '<pre>'; 
// print_r($camdata); 
// die();
if (!empty($camdata)) {
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
			echo $statedata;
			echo '</td>';
			echo '<td>';
			if (!empty($row['application_no'])) {
				$base_url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]";
				$link = $base_url . '/public/applicationpdf/parent/' . $row['application_no'];
				echo '<a href="' . $link . '" download><img title="Download" src="' . $base_url . '/cms/assets/css/img/download-box.png"></img></a>';
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

				if(!empty($cid) && !empty($sid)){
					$sqlchk = 'SELECT fn_fee_structure_id FROM campaign WHERE id = '.$cid.' ';
					$resultchk = database::doSelectOne($sqlchk);

					if(!empty($resultchk['fn_fee_structure_id'])){
						$sql = "SELECT SUM(total_amount) AS amt FROM fn_fee_structure_item WHERE fn_fee_structure_id = " . $resultchk['fn_fee_structure_id'] . " ";
						$result = database::doSelectOne($sql);
						$applicationAmount = $result['amt'] * 100;	
			?>
				<form id="admissionPay" action="<?php echo $base_url;?>/thirdparty/payment/worldline/skit/meTrnPay.php" method="post">
					<input type="hidden" value="" id="OrderId" name="OrderId">
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
			<?php } } }
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

<style>
    .btnPay {
        display: inline-block;
        font-weight: bold;
        font-size: 15px;
        width: 50px;
        line-height: 1.4285714;
        text-align: center;
        vertical-align: middle;
        cursor: pointer;
        /* padding: 0.4375rem 1rem; */
        border-radius: 3px;
        color: #ffffff !important;
        background-color: #206bc4;
        border-color: #206bc4;
    }
</style>