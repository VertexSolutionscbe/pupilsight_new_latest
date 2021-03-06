<?php

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

include $_SERVER['DOCUMENT_ROOT'] . '/pupilsight.php';
$sqlo = "SELECT * FROM pupilsight_cms  WHERE title != '' ";
$resulto = $connection2->query($sqlo);
$orgData = $resulto->fetch();

$orgName = $orgData['title'];
$orgLogo = $orgData['logo_image'];

if (!empty($orgLogo)) {
    $logo = '/cms/images/logo/' . $orgLogo;
} else {
    $logo = '';
}

// print_r($orgData);
// die();

unset($_SESSION['payment_gateway_id']);
$payment_gateway_id = $_POST["payment_gateway_id"];
$_SESSION["payment_gateway_id"] = $payment_gateway_id;


require('config.php');
require('razorpay/Razorpay.php');

use Razorpay\Api\Api;


$newpost = json_decode($_POST['formdata']);
// echo '<pre>';
// print_r($_POST['formdata']);
// echo '</pre>';
// die();


$namount = 0;
foreach($newpost as $np){
    $name = $np->name;
    $email = $np->email;
    $phone = $np->phone;
    $payid = $np->payid;
    $callbackurl = $np->callbackurl;
    $namount += $np->amount;
    $className = $np->className;
}

// echo $name.'----'.$payid.'----'.$className;
// echo '<pre>';
// print_r($newpost);
// echo '</pre>';
// die();
// Create the Razorpay Order
$api = new Api($keyId, $keySecret);

$parms = array_merge($_POST, $_GET);
$_SESSION["paypost"] = $parms;

//amount //callbackurl // payid is important
if(empty($namount) || empty($payid) || empty($callbackurl)){
    if(isset($callbackurl)){
        header('Location: '.$callbackurl);
        exit;
    }else{
        header('Location: index.php');
        exit;
    }
}

//
// We create an razorpay order using orders api
// Docs: https://docs.razorpay.com/docs/orders
//

// $name = isset($parms["name"])?$parms["name"]:"";
// $email = isset($parms["email"])?$parms["email"]:"";
// $phone = isset($parms["phone"])?$parms["phone"]:"";
$amount = $namount * 100;

$orderData = [
    'receipt'         => $payid,
    'amount'          => $amount, // 2000 rupees in paise
    'currency'        => 'INR',
    'payment_capture' => 1 // auto capture
];

$razorpayOrder = $api->order->create($orderData);

$razorpayOrderId = $razorpayOrder['id'];

$_SESSION['razorpay_order_id'] = $razorpayOrderId;

$displayAmount = $amount = $orderData['amount'];

$_SESSION['razorpay_amount'] = $orderData['amount'];

$checkout = 'automatic';

/*
if (isset($_GET['checkout']) and in_array($_GET['checkout'], ['automatic', 'manual'], true))
{
    $checkout = $_GET['checkout'];
}*/



$data = [
    "key"               => $keyId,
    "amount"            => $amount,
    "name"              => $orgName,
    "description"       => "Fees Payment",
    "image"             => $logo,
    "student_name"      => $name,
    "invoice_no"        => $payid,
    "class_name"        => $className,
    "prefill"           => [
        "name"              => $name,
        "email"             => $email,
        "contact"           => $phone,
    ],
    "theme"             => [
        "color"             => "#F37254"
    ],
    "order_id"          => $razorpayOrderId,
];

$json = json_encode($data);

require("checkout/{$checkout}.php");
?>

<script src="https://code.jquery.com/jquery-3.5.1.min.js" integrity="sha256-9/aliU8dGd2tb6OSsuzixeV4y/faTqgFtohetphbbj0=" crossorigin="anonymous"></script>

<script>
    jQuery(".razorpay-payment-button").hide()
    setTimeout(function() {
        jQuery(".razorpay-payment-button").click();
    }, 500);
</script>