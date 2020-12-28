<?php
#remove below if you have latest version of php,it will not show warnings
error_reporting(E_ERROR | E_PARSE);

include "payu/PayUClient.php";

use payu\PayUClient;

include $_SERVER['DOCUMENT_ROOT'].'/pupilsight/pupilsight.php';

if (session_status() == PHP_SESSION_NONE) {
  session_start();
}

$sql = 'SELECT * FROM fn_fee_payment_gateway WHERE name = "PAYU" ';
$result = $connection2->query($sql);
$value = $result->fetch();

if(!empty($value)){
  $key = $value['key_id'];
  $salt = $value['key_secret'];
} else {
  $key = "gtKFFx";
  $salt = "eCwWELxi";
}

$PAYU_BASE_URL = "https://test.payu.in";
//$KEY = "GSGpsd"; //Please change this value with live key for production
//$salt = "r1lVR3od"; //Please change this value with live salt for production

$PAYU_BASE_URL = "https://test.payu.in";
 $action = $PAYU_BASE_URL . '/_payment';
// $action = "http://localhost:8888/PHP_SAMPLE_APP 2/checkout.php";

$txnid = substr(hash('sha256', mt_rand() . microtime()), 0, 20);
// $amount = $_POST['amount'];
$amount = '10.00';
// $campaignid = $_POST['campaignid'];

// if (!empty($_REQUEST['sid'])) {
// 	$submissionId = $_REQUEST['sid'];
// } else {
	// $submissionId = $_SESSION['submissionId'];
// }


if(!empty($_POST['name'])){
  $firstname = $_POST['name'];
} else {
  $firstname = 'test';
}

if(!empty($_POST['email'])){
  $email = $_POST['email'];
} else {
  $email = 'test@gmail.com';
}

if(!empty($_POST['phone'])){
  $phone = $_POST['phone'];
} else {
  $phone = '9883928942';
}

$productinfo = "Admission Fee";
$udf1 = "";
$udf2 = "";
$udf3 = "";
$udf4 = "";
$udf5 = "";

# You should set your key & salt values to the function as below:
$payuClient = new PayUClient($key,$salt);


# Set params as follows
$params = array("txnid"=>$txnid,"amount"=>$amount,"firstname"=>$firstname,"email"=>$email,"udf1"=>$udf1,"udf2"=>$udf2,"udf3"=>$udf3,"udf4"=>$udf4,"udf5"=>$udf5);
// print_r($params);
// die();
# you can generate payment hash as follows:
$hash = new Hasher();
$payment_hash = $hash->generate_hash($params);
?>


<html>
<head>
<style>
div {
    height: 200px;
    width: 400px;
    background: white;

    position: fixed;
    top: 90%;
    left: 55%;
    margin-top: -100px;
    margin-left: -200px;
    h1 {text-align: center;}
p {text-align: center;}
/* div {text-align: center;} */
}
</style>
<script>
function load()
{
// document.payuform.submit()
// var payuForm = document.forms.payuForm;
//       payuForm.submit();
document.payuform.submit();
}
</script>
</head>


<body onload="load()">

<div> 
<h1>Redirecting...</h1>
<p>Wait while we redirect to you</p>

</div>

<form action="<?php echo $action; ?>"  name="payuform" id="payuform" method="post">

  <input type="hidden" name="key" value="<?php echo $key ?>" />
  <input type="hidden" name="txnid" value="<?php echo $txnid ?>" />
  <input type="hidden" name="amount" value="<?php echo $amount ?>" />
  <input type="hidden" name="firstname" value="<?php echo $firstname ?>" />
  <input type="hidden" name="email" value="<?php echo $email ?>"/>
  <input type="hidden" name="phone" value="<?php echo $phone ?>"/>
<? /*  <input type="hidden" name="campaign_id" value="<?php echo $campaignid ?>"/>
  <input type="hidden" name="submission_id" value="<?php echo $submissionId ?>"/>
*/ ?>
  <input type="hidden" name="productinfo" value="<?php  echo $productinfo;   ?>" />
  <input type="hidden" name="hash" value="<?php echo $payment_hash ?>"/>
  <input type="hidden" name="surl" value="https://success-url.herokuapp.com/" />   <!--Please change this parameter value with your success page absolute url like http://mywebsite.com/response.php. -->
  <input type="hidden" name="furl" value="https://failure-url.herokuapp.com/" /><!--Please change this parameter value with your failure page absolute url like http://mywebsite.com/response.php. -->
  <input name="curl" type= "hidden" value="" />
  <input name="udf1" type= "hidden" value=""/>
  <input name="udf2" type= "hidden" value="" />
  <input name="udf3" type= "hidden" value="" />
  <input name="udf4" type= "hidden" value="" />
  <input name="udf5" type= "hidden" value="" />

</body>
</html>
