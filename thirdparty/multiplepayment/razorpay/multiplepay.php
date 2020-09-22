<?php
require('config.php');
require('razorpay/Razorpay.php');

use Razorpay\Api\Api;

session_start();

$newpost = json_decode($_POST['formdata']);
// echo '<pre>';
// print_r($newpost);
// echo '</pre>';
//die();


$namount = 0;
foreach($newpost as $np){
    $name = $np->name;
    $email = $np->email;
    $phone = $np->phone;
    $payid = $np->payid;
    $callbackurl = $np->callbackurl;
    $namount += $np->amount;
}

//echo $name.'----'.$namount;
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

$checkout = 'automatic';

/*
if (isset($_GET['checkout']) and in_array($_GET['checkout'], ['automatic', 'manual'], true))
{
    $checkout = $_GET['checkout'];
}*/



$data = [
    "key"               => $keyId,
    "amount"            => $amount,
    "name"              => "Parentof Solutions Pvt. Ltd.",
    "description"       => "Fees Payment",
    "image"             => "http://pupilsight.pupiltalk.com/themes/Default/img/logo.png",
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