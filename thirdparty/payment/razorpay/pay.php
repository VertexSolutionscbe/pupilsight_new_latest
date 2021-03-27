<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

unset($_SESSION['payment_gateway_id']);
$payment_gateway_id = $_POST["payment_gateway_id"];
$_SESSION["payment_gateway_id"] = $payment_gateway_id;


require('config.php');
require('razorpay/Razorpay.php');

use Razorpay\Api\Api;


//session_start();

// Create the Razorpay Order
$api = new Api($keyId, $keySecret);

$parms = array_merge($_POST, $_GET);




$_SESSION["paypost"] = $parms;
// echo '<pre>';
// print_r($parms);
// echo '</pre>';
// die();
//amount //callbackurl // payid is important
if (empty($parms["amount"]) || empty($parms["payid"]) || empty($parms["callbackurl"])) {
    if (isset($parms["callbackurl"])) {
        header('Location: ' . $parms["callbackurl"]);
        exit;
    } else {
        header('Location: index.php');
        exit;
    }
}

//
// We create an razorpay order using orders api
// Docs: https://docs.razorpay.com/docs/orders
//

$name = isset($parms["name"]) ? $parms["name"] : "";
$email = isset($parms["email"]) ? $parms["email"] : "";
$phone = isset($parms["phone"]) ? $parms["phone"] : "";
$amount = $parms["amount"] * 100;
$orgName = $parms["organisationName"];
$orgLogo = $parms["organisationLogo"];

$orderData = [
    'receipt'         => $parms["payid"],
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

if (!empty($orgLogo)) {
    $logo = '/cms/images/logo/' . $orgLogo;
} else {
    $logo = '';
}



$data = [
    "key"               => $keyId,
    "amount"            => $amount,
    "name"              => $orgName,
    "description"       => "Fees Payment",
    "image"             => $logo,
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