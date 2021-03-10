<?php

include $_SERVER['DOCUMENT_ROOT'] . '/pupilsight/pupilsight.php';

$sql = 'SELECT * FROM fn_fee_payment_gateway WHERE name = "RAZORPAY" ';
$result = $connection2->query($sql);
$value = $result->fetch();

if (!empty($value)) {
    $keyId = $value['key_id'];
    $keySecret = $value['key_secret'];
} else {
    $keyId = 'rzp_test_0VvfTtnAI840VO';
    $keySecret = 'Wml5p4NJvohYygIeYfvsNtCo';
}

$displayCurrency = 'INR';

//These should be commented out in production
// This is for error reporting
// Add it to config.php to report any errors
error_reporting(E_ALL);
ini_set('display_errors', 1);
