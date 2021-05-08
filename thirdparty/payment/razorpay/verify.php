<?php

require('config.php');

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require('razorpay/Razorpay.php');

use Razorpay\Api\Api;
use Razorpay\Api\Errors\SignatureVerificationError;

include $_SERVER['DOCUMENT_ROOT'] . '/pupilsight.php';

$success = true;

$error = "Payment Failed";

if (empty($_POST['razorpay_payment_id']) === false) {
    $api = new Api($keyId, $keySecret);

    try {
        // Please note that the razorpay order ID must
        // come from a trusted source (session here, but
        // could be database or something else)
        $attributes = array(
            'razorpay_order_id' => $_SESSION['razorpay_order_id'],
            'razorpay_payment_id' => $_POST['razorpay_payment_id'],
            'razorpay_signature' => $_POST['razorpay_signature']
        );

        $api->utility->verifyPaymentSignature($attributes);
    } catch (SignatureVerificationError $e) {
        $success = false;
        $error = 'Razorpay Error : ' . $e->getMessage();
    }
}

if ($success === true) {
    $html = "<p>Your payment was successful</p>
             <p>Payment ID: {$_POST['razorpay_payment_id']}</p>";
    //$parms = $_SESSION["paypost"];
    //$_SESSION['payment_gateway_id'] = $_POST['razorpay_payment_id'];
    //$callback = $_SESSION["paypost"]["callbackurl"];
    // $baseurl = "http://" . $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'];
    // $callback = "/thirdparty/phpword/receiptOnline.php";
    // include $_SERVER['DOCUMENT_ROOT'] . '/db.php';



    //$conn
    try {
        $dt = $_SESSION["paypost"];

        $pupilsightPersonID = $dt["stuid"];

        $sqlon = 'SELECT transaction_ref_no FROM fn_fee_payment_details WHERE transaction_ref_no = "'.$_POST['razorpay_payment_id'].'" ';
        $resulton = $connection2->query($sqlon);
        $onData = $resulton->fetch();
        if(!empty($onData)){
            header('Location: index.php');
            exit;
        } else {
            $data = array('gateway' => 'RAZORPAY', 'pupilsightPersonID' => $dt["stuid"], 'transaction_ref_no' => $_POST['razorpay_payment_id'], 'order_id' => $_SESSION['razorpay_order_id'], 'amount' => $dt["amount"], 'status' => 'S');

            $sql = 'INSERT INTO fn_fee_payment_details SET gateway=:gateway, pupilsightPersonID=:pupilsightPersonID, transaction_ref_no=:transaction_ref_no, order_id=:order_id, amount=:amount, status=:status';
            $result = $connection2->prepare($sql);
            $result->execute($data);

            $callback = $_SESSION[$guid]['absoluteURL'] . '/thirdparty/fee_update.php?payid=' . $_POST['razorpay_payment_id'];
            header('Location: '.$callback);
            exit;
        }

        
        
    } catch (Exception $ex) {
        print_r($ex);
    }

    
} else {
    $html = "<p>Your payment failed</p>
             <p>{$error}</p>";
}

echo $html;
