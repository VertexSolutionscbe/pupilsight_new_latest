<?php

require('config.php');

session_start();

require('razorpay/Razorpay.php');
use Razorpay\Api\Api;
use Razorpay\Api\Errors\SignatureVerificationError;
include $_SERVER['DOCUMENT_ROOT'].'/pupilsight/pupilsight.php';

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
    //$parms = $_SESSION["paypost"];
    $_SESSION['payment_gateway_id'] = $_POST['razorpay_payment_id'];
    //$callback = $_SESSION["paypost"]["callbackurl"];
    $baseurl = "http://" . $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'];
    $callback = "/pupilsight/thirdparty/phpword/application_receipt.php";
    include $_SERVER['DOCUMENT_ROOT'].'/pupilsight/db.php';

    

    //$conn
    try{
        $dt = $_SESSION["paypost"];

        $rand = mt_rand(10,99);  
        $t = time();
        $transactionId = $t.$rand;

        if(!empty($dt["classid"])){
            $sqcs = "select name from pupilsightYearGroup where pupilsightYearGroupID='".$dt["classid"]."'";
            $result = $conn->query($sqcs);
            if ($result->num_rows > 0) {
                while($row = $result->fetch_assoc()) {
                    $clss = $row["name"];
                }
            }
        }

        $class_section = $clss;

        $dts_receipt = array(
            "receipt_no" => "NA",
            "date" => date("d-M-Y"),
            "student_name" => $dt["name"],
            "class_section" => $class_section,
            "instrument_date" => "NA",
            "instrument_no" => "NA",
            "transcation_amount" => '300',
            "pay_mode" => "Online",
            "transactionId" => $transactionId
        );

        $cnt = 1;

        $dts_receipt_feeitem[] = array(
            "serial.all"=>$cnt,
            "particulars.all"=>'Application Fee',
            "amount.all"=>'300'
        );

        $_SESSION["dts_receipt_feeitem"] = $dts_receipt_feeitem;
        $_SESSION["dts_receipt"] = $dts_receipt;


        // $pupilsightPersonID = $_SESSION[$guid]['pupilsightPersonID'];
        
        // $dates = date('Y-m-d');
        // $cdt = date('Y-m-d H:i:s');
        // $sq = "INSERT INTO fn_fees_collection (fn_fees_invoice_id, transaction_id,pupilsightPersonID, pupilsightSchoolYearID,
        //  receipt_number, pay_gateway_id, payment_status, payment_date, fn_fees_head_id, fn_fees_receipt_series_id, 
        //  transcation_amount, total_amount_without_fine_discount, amount_paying, fine, discount, status, cdt) ";
        // $sq .=" values(
        //         '".$dt["fn_fees_invoice_id"]."'
        //         ,'".$transactionId."'
        //         ,'".$dt["stuid"]."'
        //         ,'".$dt["pupilsightSchoolYearID"]."'
        //         ,'".$receipt_number."'
        //         ,'".$_POST['razorpay_payment_id']."'
        //         ,'Payment Received'
        //         ,'".$dates."'
        //         ,'".$dt["fn_fees_head_id"]."'
        //         ,'".$dt["rec_fn_fee_series_id"]."'
        //         ,'".$dt["amount"]."'
        //         ,'".$dt["total_amount_without_fine_discount"]."'
        //         ,'".$dt["amount"]."'
        //         ,'".$dt["fine"]."'
        //         ,'".$dt["discount"]."'
        //         ,'1'
        //         ,'".$cdt."'
        //         ); ";
        
                
              
        if ($conn->query($sq) === TRUE) {
            $conn->query($tsq);
            echo "New record created successfully";
        } else {
            echo "Error: " . $sq . "<br>" . $conn->error;
        }
        
        $conn->close();
    }catch(Exception $ex){
        print_r($ex);
    }

    if(isset($callback)){
        header('Location: '.$callback);
        exit;
    }else{
        header('Location: index.php');
        exit;
    }
}
else
{
    $html = "<p>Your payment failed</p>
             <p>{$error}</p>";
}

echo $html;
