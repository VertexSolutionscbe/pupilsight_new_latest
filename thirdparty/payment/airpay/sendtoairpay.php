<?php
date_default_timezone_set('Asia/Kolkata');
header( 'Expires: Sat, 26 Jul 1997 05:00:00 GMT' );
header( 'Last-Modified: ' . gmdate( 'D, d M Y H:i:s' ) . ' GMT' );
header( 'Cache-Control: no-store, no-cache, must-revalidate' );
header( 'Cache-Control: post-check=0, pre-check=0', false );
header( 'Pragma: no-cache' );

	if (session_status() == PHP_SESSION_NONE) {
		session_start();
  	}

	$buyerEmail = trim($_POST['buyerEmail']);
	$buyerPhone = trim($_POST['buyerPhone']);
	$buyerFirstName = trim($_POST['buyerFirstName']);
	$buyerLastName = trim($_POST['buyerLastName']);
	$buyerAddress = trim($_POST['buyerAddress']);
	$amount = trim($_POST['amount']);
	$buyerCity = trim($_POST['buyerCity']);
	$buyerState = trim($_POST['buyerState']);
	$buyerPinCode = trim($_POST['buyerPinCode']);
	$buyerCountry = trim($_POST['buyerCountry']);
	$orderid = trim($_POST['orderid']); //Your System Generated Order ID

	$payment_gateway_id = $_POST["payment_gateway_id"];
	$_SESSION["payment_gateway_id"] = $payment_gateway_id;

	
	$type = $_POST['ptype'];

	if($type == 'admission'){
		
		if (!empty($_POST['sid'])) {
			$submissionId = $_POST['sid'];
		} else {
			$submissionId = $_SESSION['submissionId'];
		}
		
		$campaignId = trim($_POST['campaignid']);
		$customvar = $submissionId.'-'.$campaignId.'-'.$type;
		
	} else if($type == 'parent_admission'){
		
		$_SESSION["paypost"] = $_POST;
		$submissionId = $_POST['stuid'];
		$campaignId = 'parent';
		$customvar = $submissionId.'-'.$campaignId.'-'.$type;
	} else if($type == 'fee_collection'){
		
		$_SESSION["paypost"] = $_POST;
		$submissionId = $_POST['stuid'];
		$campaignId = 'fees';
		$customvar = $submissionId.'-'.$campaignId.'-'.$type;
	} else if($type == 'multiple_fee_collection'){
		$_SESSION["paypost"] = $_POST;
		$newpost = json_decode($_POST['formdata']);
		
		$namount = 0;
		foreach($newpost as $np){
			$name = $np->name;
			$email = $np->email;
			$phone = $np->phone;
			$payid = $np->payid;
			$callbackurl = $np->callbackurl;
			$stid = $np->stuid;
			$namount += $np->amount;
		}
		//$amount = number_format($namount, 2, '.', '');
		$submissionId = $stid;
		$campaignId = 'fees';
		$customvar = $submissionId.'-'.$campaignId.'-'.$type;
	}
	// echo '<pre>';
	// print_r($_POST);
	// echo '</pre>';
	// echo $amount.'--'.$submissionId;
	// die();


    include('config.php');
    include('checksum.php');
    include('validation.php');
	

	
	$alldata   = $buyerEmail.$buyerFirstName.$buyerLastName.$buyerAddress.$buyerCity.$buyerState.$buyerCountry.$amount.$orderid;
	$privatekey = Checksum::encrypt($username.":|:".$password, $secret);
	$checksum = Checksum::calculateChecksum($alldata.date('Y-m-d'),$privatekey);
  $hiddenmod = "";
	
?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3./org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Airpay</title>
<script type="text/javascript">
function submitForm(){
			var form = document.forms[0];
			form.submit();
		}
</script>
</head>
<body onload="javascript:submitForm()">
<center>
<table width="500px;">
	<tr>
		<td align="center" valign="middle">Do Not Refresh or Press Back <br/> Redirecting to Airpay</td>
	</tr>
	<tr>
		<td align="center" valign="middle">
			<form action="	https://payments.airpay.co.in/pay/index.php" method="post">
                <input type="hidden" name="privatekey" value="<?php echo $privatekey; ?>">
                <input type="hidden" name="mercid" value="<?php echo $mercid; ?>">
				<input type="hidden" name="orderid" value="<?php echo $orderid; ?>">
 		        <input type="hidden" name="currency" value="356">
		        <input type="hidden" name="isocurrency" value="INR">
				<input type="hidden" name="chmod" value="<?php echo $hiddenmod; ?>">			
				<input type="hidden" name="customvar" value="<?php echo $customvar; ?>">
				
				<?php
				Checksum::outputForm($checksum);
				?>

			</form>
		</td>

	</tr>

</table>

</center>
</body>
</html>
