<?php
	/**
	 * This Is the Kit File To Be Included For Transaction Request/Reponse
	 */
	include 'AWLMEAPI.php';

	//create an Object of the above included class
	$obj = new AWLMEAPI();

	/* This is the request Object */
	$reqMsgDTO = new ReqMsgDTO();
	
	/* Populate the mandatory fields for the request*/
	// Merchant Unieue order id
	$reqMsgDTO->setOrderId($_REQUEST['orderId']);
	// PG MID
	$reqMsgDTO->setMid($_REQUEST['mId']);
	// Transaction remarks
	$reqMsgDTO->setTrnRemarks($_REQUEST['txnRemark']);
	// Merchant Transaction currency
	$reqMsgDTO->setTrnCurrency($_REQUEST['txnCurrency']);
	// Transaction Amount in paisa 
	$reqMsgDTO->setTrnAmt($_REQUEST['txnAmt']);
	// Merchant Encryption Key
	$reqMsgDTO->setEnckey($_REQUEST['encKey']);
	//Merchant Transaction type (S/P/R)
	$reqMsgDTO->setMeTransReqType($_REQUEST['meReqTxnType']);
	// Recurring period,If merchnat transaction type R,
	$reqMsgDTO->setRecurrPeriod($_REQUEST['recurrPeriod']);
	// Recurring day,If merchnat transaction type R,
	$reqMsgDTO->setRecurrDay($_REQUEST['recurringDay']);
	// No of Recurring,If merchnat transaction type R,
	$reqMsgDTO->setNoOfRecurring($_REQUEST['NoOfRecurring']);
	// Debit / Credit Card Number
	$reqMsgDTO->setCardNumber($_REQUEST['cardNo']);
	// Card Expiry date
	$reqMsgDTO->setExpiryDate($_REQUEST['expiryDate']);
	// Card cvv
	$reqMsgDTO->setCvv($_REQUEST['cvv']);
	//Name on Card
	$reqMsgDTO->setNameOnCard($_REQUEST['nameOnCard']);
	// Card Type DC-Debit Card / CC- Credit Card
	$reqMsgDTO->setPayTypeCode($_REQUEST['payTypeCode']);
	// Merchant response type
	$reqMsgDTO->setResponseUrl($_REQUEST['respUrl']);
	// Optional addtional fields for Merchant
	$reqMsgDTO->setAddField1($_REQUEST['addField1']);
	$reqMsgDTO->setAddField2($_REQUEST['addField2']);
	$reqMsgDTO->setAddField3($_REQUEST['addField3']);
	$reqMsgDTO->setAddField4($_REQUEST['addField4']);
	$reqMsgDTO->setAddField5($_REQUEST['addField5']);
	$reqMsgDTO->setAddField6($_REQUEST['addField6']);
	$reqMsgDTO->setAddField7($_REQUEST['addField7']);
	$reqMsgDTO->setAddField8($_REQUEST['addField8']);
	// Generate transaction request for PG
	$reqMsgDTO = $obj->generateTrnReqMsgWithCard($reqMsgDTO);
	
	$merchantRequest = "";
	
	if ($reqMsgDTO->getStatusDesc() == "Success"){
		$merchantRequest = $reqMsgDTO->getReqMsg();
	}
?>

<form action="https://cgt.in.worldline.com/ipg/doMEPayRequestCard" method="post" name="txnSubmitFrm">
	<h4 align="center">Redirecting To Payment Please Wait..</h4>
	<h4 align="center">Please Do Not Press Back Button OR Refresh Page</h4>
	<input type="hidden" size="200" name="merchantRequest" id="merchantRequest" value="<?php echo $merchantRequest; ?>"  />
	<input type="hidden" name="MID" id="MID" value="<?php echo $reqMsgDTO->getMid(); ?>"/>
</form>
<script  type="text/javascript">
	//submit the form to the worldline
	document.txnSubmitFrm.submit();
</script>

