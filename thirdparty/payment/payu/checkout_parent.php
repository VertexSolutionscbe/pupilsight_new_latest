<?php
#remove below if you have latest version of php,it will not show warnings
error_reporting(E_ERROR | E_PARSE);

include "payu/PayUClient.php";

use payu\PayUClient;

include $_SERVER['DOCUMENT_ROOT'].'/pupilsight.php';

if (session_status() == PHP_SESSION_NONE) {
  session_start();
}

unset($_SESSION['payment_gateway_id']);
$payment_gateway_id = $_POST["payment_gateway_id"];
$_SESSION["payment_gateway_id"] = $payment_gateway_id;

$sql = 'SELECT * FROM fn_fee_payment_gateway WHERE name = "PAYU" AND id = '.$payment_gateway_id.' ';
$result = $connection2->query($sql);
$value = $result->fetch();

if(!empty($value)){
  $KEY = $value['key_id'];
  $SALT = $value['key_secret'];
} else {
  $KEY = "gtKFFx";
  $SALT = "eCwWELxi";
}

//$PAYU_BASE_URL = "https://test.payu.in";
//$KEY = "gtKFFx"; //Please change this value with live KEY for production
//$SALT = "eCwWELxi"; //Please change this value with live SALT for production

//$PAYU_BASE_URL = "https://test.payu.in"; // for test server

$PAYU_BASE_URL = "https://secure.payu.in";  // for Live server
$action = $PAYU_BASE_URL . '/_payment';
// $action = "http://localhost:8888/PHP_SAMPLE_APP 2/checkout.php";

$txnid = substr(hash('sha256', mt_rand() . microtime()), 0, 20);

$_SESSION["paypost"] = $_POST;

$amount = $_POST['amount'] / 100;

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
  $phone = '9000000009';
}

//$campaignid = $_POST['campaignid'];


$submissionId = $_POST['stuid'];



// $campaignid = 1;
// $submissionId = 111;

$productinfo = "Admission Fee";
$udf1 = "";
$udf2 = $submissionId;
$udf3 = "";
$udf4 = "";
$udf5 = "";

# You should set your KEY & SALT values to the function as below:
$payuClient = new PayUClient($KEY, $SALT);


# Set params as follows
$params = array("txnid" => $txnid, "amount" => $amount, "productinfo" => $productinfo, "firstname" => $firstname, "email" => $email, "udf1" => $udf1, "udf2" => $udf2, "udf3" => $udf3, "udf4" => $udf4, "udf5" => $udf5);

# you can generate payment hash as follows:
$hash = new Hasher();
$payment_hash = $hash->generate_hash($params);


$base_url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]";

$surl = $base_url . '/thirdparty/payment/payu/success_parent.php';
$furl = $base_url . '/thirdparty/payment/payu/failure.php';
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

      h1 {
        text-align: center;
      }

      p {
        text-align: center;
      }

      /* div {text-align: center;} */
    }
  </style>
  <script>
    function load() {
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




  <form action="<?php echo $action; ?>" name="payuform" id="payuform" method="post">

    <input type="hidden" name="key" value="<?php echo $key ?>" />
    <input type="hidden" name="txnid" value="<?php echo $txnid ?>" />
    <input type="hidden" name="amount" value="<?php echo $amount ?>" />
    <input type="hidden" name="firstname" value="<?php echo $firstname ?>" />
    <input type="hidden" name="email" value="<?php echo $email ?>" />
    <input type="hidden" name="productinfo" value="<?php echo $productinfo ?>" />
    <input type="hidden" name="hash" value="<?php echo $payment_hash ?>" />
    <input type="hidden" name="surl" value="<?php echo $surl;?>" />
    <!--Please change this parameter value with your success page absolute url like http://mywebsite.com/response.php. -->
    <input type="hidden" name="furl" value="<?php echo $furl;?>" />
    <!--Please change this parameter value with your failure page absolute url like http://mywebsite.com/response.php. -->
    <input name="curl" type="hidden" value="" />
    <input name="udf1" type="hidden" value="" />
    <input name="udf2" type="hidden" value="<?php echo $udf2?>" />
    <input name="udf3" type="hidden" value="" />
    <input name="udf4" type="hidden" value="" />
    <input name="udf5" type="hidden" value="" />

    <input type="hidden" name="phone" value="<?php echo $phone ?>"/>
   
    

</body>

</html>