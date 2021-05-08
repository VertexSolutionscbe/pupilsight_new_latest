<?php

require('config.php');

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require('razorpay/Razorpay.php');
use Razorpay\Api\Api;
use Razorpay\Api\Errors\SignatureVerificationError;
include $_SERVER['DOCUMENT_ROOT'].'/pupilsight.php';

$success = true;

$error = "Payment Failed";

if (empty($_POST['razorpay_payment_id']) === false)
{
    $api = new Api($keyId, $keySecret);

    try
    {
        // Please note that the razorpay order ID must
        // come from a trusted source (session here, but
        // could be database or something else)
        $attributes = array(
            'razorpay_order_id' => $_SESSION['razorpay_order_id'],
            'razorpay_payment_id' => $_POST['razorpay_payment_id'],
            'razorpay_signature' => $_POST['razorpay_signature']
        );

        $api->utility->verifyPaymentSignature($attributes);
    }
    catch(SignatureVerificationError $e)
    {
        $success = false;
        $error = 'Razorpay Error : ' . $e->getMessage();
    }
}

if ($success === true)
{
    $html = "<p>Your payment was successful</p>
             <p>Payment ID: {$_POST['razorpay_payment_id']}</p>";
    
    //$conn
    try{
        $dtall = $_SESSION["paypost"];
        $newdt = json_decode($dtall['formdata']);
        $stuID = $newdt[0]->stuid;
       
        $amountR = $_SESSION['razorpay_amount'] / 100;

        $sqlon = 'SELECT transaction_ref_no FROM fn_fee_payment_details WHERE transaction_ref_no = "'.$_POST['razorpay_payment_id'].'" ';
        $resulton = $connection2->query($sqlon);
        $onData = $resulton->fetch();
        if(!empty($onData)){
            header('Location: index.php');
            exit;
        } else {
            $data = array('gateway' => 'RAZORPAY', 'pupilsightPersonID' => $stuID, 'transaction_ref_no' => $_POST['razorpay_payment_id'], 'order_id' => $_SESSION['razorpay_order_id'], 'amount' => $amountR, 'status' => 'S');

            $sql = 'INSERT INTO fn_fee_payment_details SET gateway=:gateway, pupilsightPersonID=:pupilsightPersonID, transaction_ref_no=:transaction_ref_no, order_id=:order_id, amount=:amount, status=:status';
            $result = $connection2->prepare($sql);
            $result->execute($data);

            if(!empty($newdt)){
                $invData = array();
                $feeData = array();
                $amount = 0;
                $total_amount_without_fine_discount = 0;
                $fine = 0;
                $discount = 0;
                foreach($newdt as $ndt){
                    $invData['fn_fees_invoice_id'][] = $ndt->fn_fees_invoice_id;
                    $invData['fn_fee_invoice_item_id'][] = $ndt->fn_fee_invoice_item_id;
                    $invData['payid'][] = $ndt->payid;
                    $total_amount_without_fine_discount +=  $ndt->total_amount_without_fine_discount;
                    $fine +=  $ndt->fine;
                    $discount +=  $ndt->discount;
                    $amount +=  $ndt->amount;
                    $pupilsightSchoolYearID = $ndt->pupilsightSchoolYearID;
                    $pupilsightProgramID = $ndt->pupilsightProgramID;
                    $classid = $ndt->classid;
                    $className = $ndt->className;
                    $sectionid = $ndt->sectionid;
                    $payment_gateway_id = $ndt->payment_gateway_id;
                    $fn_fees_head_id = $ndt->fn_fees_head_id;
                    $rec_fn_fee_series_id = $ndt->rec_fn_fee_series_id;
                    $receipt_number = $ndt->receipt_number;
                    $name = $ndt->name;
                    $email = $ndt->email;
                    $phone = $ndt->phone;
                    $stuid = $ndt->stuid;
                    $callbackurl = $ndt->callbackurl;
                    $organisationName = $ndt->organisationName;
                    $organisationLogo = $ndt->organisationLogo;
                    
                }
                if(!empty($invData['fn_fees_invoice_id'])){
                    $fn_fees_invoice_id = implode(',', $invData['fn_fees_invoice_id']);
                }

                if(!empty($invData['fn_fee_invoice_item_id'])){
                    $fn_fee_invoice_item_id = implode(',', $invData['fn_fee_invoice_item_id']);
                }

                if(!empty($invData['payid'])){
                    $payid = implode(',', $invData['payid']);
                }

                $feeData['fn_fees_invoice_id'] = $fn_fees_invoice_id;
                $feeData['fn_fee_invoice_item_id'] = $fn_fee_invoice_item_id;
                $feeData['payid'] = $payid;
                $feeData['total_amount_without_fine_discount'] = $total_amount_without_fine_discount;
                $feeData['fine'] = $fine;
                $feeData['discount'] = $discount;
                $feeData['amount'] = $amount;
                $feeData['pupilsightSchoolYearID'] = $pupilsightSchoolYearID;
                $feeData['pupilsightProgramID'] = $pupilsightProgramID;
                $feeData['classid'] = $classid;
                $feeData['className'] = $className;
                $feeData['sectionid'] = $sectionid;
                $feeData['payment_gateway_id'] = $payment_gateway_id;
                $feeData['fn_fees_head_id'] = $fn_fees_head_id;
                $feeData['rec_fn_fee_series_id'] = $rec_fn_fee_series_id;
                $feeData['receipt_number'] = $receipt_number;
                $feeData['name'] = $name;
                $feeData['email'] = $email;
                $feeData['phone'] = $phone;
                $feeData['stuid'] = $stuid;
                $feeData['callbackurl'] = $callbackurl;
                $feeData['organisationName'] = $organisationName;
                $feeData['organisationLogo'] = $organisationLogo;
                unset($_SESSION["paypost"]);
                $_SESSION["paypost"] = $feeData;
            }
            

            $callback = $_SESSION[$guid]['absoluteURL'] . '/thirdparty/fee_update.php?payid=' . $_POST['razorpay_payment_id'];
            header('Location: '.$callback);
            exit;
        }

    }catch(Exception $ex){
        print_r($ex);
        die();
    }

    
}
else
{
    $html = "<p>Your payment failed</p>
             <p>{$error}</p>";
}

echo $html;
