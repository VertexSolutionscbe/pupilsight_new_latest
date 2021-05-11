<?php
date_default_timezone_set('Asia/Kolkata');

header( 'Expires: Sat, 26 Jul 1997 05:00:00 GMT' );
header( 'Last-Modified: ' . gmdate( 'D, d M Y H:i:s' ) . ' GMT' );
header( 'Cache-Control: no-store, no-cache, must-revalidate' );
header( 'Cache-Control: post-check=0, pre-check=0', false );
header( 'Pragma: no-cache' );

include('config.php');
include $_SERVER['DOCUMENT_ROOT'].'/pupilsight.php';

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// This is landing page where you will receive response from airpay. 
// The name of the page should be as per you have configured in airpay system
// All columns are mandatory

$TRANSACTIONID = trim($_POST['TRANSACTIONID']);
$APTRANSACTIONID  = trim($_POST['APTRANSACTIONID']);
$AMOUNT  = trim($_POST['AMOUNT']);
$TRANSACTIONSTATUS  = trim($_POST['TRANSACTIONSTATUS']);
$MESSAGE  = trim($_POST['MESSAGE']);
$ap_SecureHash = trim($_POST['ap_SecureHash']);
$CUSTOMVAR  = trim($_POST['CUSTOMVAR']);




$error_msg = '';
if(empty($TRANSACTIONID) || empty($APTRANSACTIONID) || empty($AMOUNT) || empty($TRANSACTIONSTATUS) || empty($ap_SecureHash)){
// Reponse has been compromised. So treat this transaction as failed.
if(empty($TRANSACTIONID)){ $error_msg = 'TRANSACTIONID '; } 
if(empty($APTRANSACTIONID)){ $error_msg .=  ' APTRANSACTIONID'; }
if(empty($AMOUNT)){ $error_msg .=  ' AMOUNT'; }
if(empty($TRANSACTIONSTATUS)){ $error_msg .=  ' TRANSACTIONSTATUS'; }
if(empty($ap_SecureHash)){ $error_msg .=  ' ap_SecureHash'; }
$error_msg .= '<tr><td>Variable(s) '. $error_msg.' is/are empty.</td></tr>';

//exit();
}

//THIS IS ADDITIONAL VALIDATION, YOU MAY USE IT.
//$SYSTEM_AMOUNT is amount you will fetch from your database/system against $TRANSACTIONID
//if( $AMOUNT != $SYSTEM_AMOUNT){
// Reponse has been compromised. So treat this transaction as failed.
//$error_msg .= '<tr><td>Amount mismatch in the system.</td></tr>';
//exit();
//}

// Generating Secure Hash
// $mercid = 	Merchant Id, $username = username
// You will find above two keys on the settings page, which we have defined here in config.php
$merchant_secure_hash = sprintf("%u", crc32 ($TRANSACTIONID.':'.$APTRANSACTIONID.':'.$AMOUNT.':'.$TRANSACTIONSTATUS.':'.$MESSAGE.':'.$mercid.':'.$username));

//comparing Secure Hash with Hash sent by Airpay
if($ap_SecureHash != $merchant_secure_hash){
// Reponse has been compromised. So treat this transaction as failed.
$error_msg .= '<tr><td>Secure Hash mismatch.</td></tr>';
//exit();
}

if($error_msg){
echo '<table><font color="red"><b>ERROR:</b> '.$error_msg.'</font></table>';
echo '<table>
<tr><td>Variable Name</td><td> Value</td></tr>
<tr><td>TRANSACTIONID:</td><td> '.$TRANSACTIONID.'</td></tr>
<tr><td>APTRANSACTIONID:</td><td> '.$APTRANSACTIONID.'</td></tr>
<tr><td>AMOUNT:</td><td> '.$AMOUNT.'</td></tr>
<tr><td>TRANSACTIONSTATUS:</td><td> '.$TRANSACTIONSTATUS.'</td></tr>
<tr><td>CUSTOMVAR:</td><td> '.$CUSTOMVAR.'</td></tr>

</table>';

exit();
}//if($error_msg)


if($TRANSACTIONSTATUS == 200){
// echo '<pre>';
// print_r($_POST);
// echo '</pre>';
// echo '<table><tr><td>Success Transaction</td></tr></table>
// <table>
// <tr><td>Variable Name</td><td> Value</td></tr>
// <tr><td>TRANSACTIONID:</td><td> '.$TRANSACTIONID.'</td></tr>
// <tr><td>APTRANSACTIONID:</td><td> '.$APTRANSACTIONID.'</td></tr>
// <tr><td>AMOUNT:</td><td> '.$AMOUNT.'</td></tr>
// <tr><td>TRANSACTIONSTATUS:</td><td> '.$TRANSACTIONSTATUS.'</td></tr>
// <tr><td>MESSAGE:</td><td> '.$MESSAGE.'</td></tr>
// <tr><td>CUSTOMVAR:</td><td> '.$CUSTOMVAR.'</td></tr>

// </table>';

$txnid = $TRANSACTIONID;
$mihpayid = $APTRANSACTIONID;
$amount = $AMOUNT;
$custVar = explode('-',$CUSTOMVAR);
$sid = $custVar[0];
$cid = $custVar[1];
$ptype = $custVar[2];


if(!empty($sid) && !empty($cid) && !empty($txnid)){

    

    if($ptype == 'admission'){
        $data = array('gateway' => 'AIRPAY', 'submission_id' => $sid, 'transaction_ref_no' => $mihpayid, 'order_id' => $txnid, 'amount' => $amount, 'status' => 'S');

        $sql = 'INSERT INTO fn_fee_payment_details SET gateway=:gateway, submission_id=:submission_id, transaction_ref_no=:transaction_ref_no, order_id=:order_id, amount=:amount, status=:status';
        $result = $connection2->prepare($sql);
        $result->execute($data);

        $callback = $_SESSION[$guid]['absoluteURL'] . '/thirdparty/payment/airpay/admission_success.php?sid=' . $sid.'&cid='.$cid.'&amt='.$amount;
        header('Location: '.$callback);
    } else if($ptype == 'parent_admission'){
        $data = array('gateway' => 'AIRPAY', 'submission_id' => $sid, 'transaction_ref_no' => $mihpayid, 'order_id' => $txnid, 'amount' => $amount, 'status' => 'S');

        $sql = 'INSERT INTO fn_fee_payment_details SET gateway=:gateway, submission_id=:submission_id, transaction_ref_no=:transaction_ref_no, order_id=:order_id, amount=:amount, status=:status';
        $result = $connection2->prepare($sql);
        $result->execute($data);

        $callback = $_SESSION[$guid]['absoluteURL'] . '/thirdparty/payment/airpay/parent_admission_success.php?sid=' . $sid.'&amt='.$amount;
        header('Location: '.$callback);
    } else if($ptype == 'fee_collection'){

        $sqlon = 'SELECT transaction_ref_no FROM fn_fee_payment_details WHERE transaction_ref_no = "'.$mihpayid.'" ';
        $resulton = $connection2->query($sqlon);
        $onData = $resulton->fetch();
        if(!empty($onData)){
            header('Location: index.php');
            exit;
        } else {
            $data = array('gateway' => 'AIRPAY', 'pupilsightPersonID' => $sid, 'transaction_ref_no' => $mihpayid, 'order_id' => $txnid, 'amount' => $amount, 'status' => 'S');

            $sql = 'INSERT INTO fn_fee_payment_details SET gateway=:gateway, pupilsightPersonID=:pupilsightPersonID, transaction_ref_no=:transaction_ref_no, order_id=:order_id, amount=:amount, status=:status';
            $result = $connection2->prepare($sql);
            $result->execute($data);

            $callback = $_SESSION[$guid]['absoluteURL'] . '/thirdparty/fee_update.php?payid=' . $mihpayid;

            // $callback = $_SESSION[$guid]['absoluteURL'] . '/thirdparty/payment/airpay/fee_collection_success.php?sid=' . $sid.'&amt='.$amount;
            header('Location: '.$callback);
        }
    } else if($ptype == 'multiple_fee_collection'){
        $sqlon = 'SELECT transaction_ref_no FROM fn_fee_payment_details WHERE transaction_ref_no = "'.$mihpayid.'" ';
        $resulton = $connection2->query($sqlon);
        $onData = $resulton->fetch();
        if(!empty($onData)){
            header('Location: index.php');
            exit;
        } else {
            $data = array('gateway' => 'AIRPAY', 'pupilsightPersonID' => $sid, 'transaction_ref_no' => $mihpayid, 'order_id' => $txnid, 'amount' => $amount, 'status' => 'S');

            $sql = 'INSERT INTO fn_fee_payment_details SET gateway=:gateway, pupilsightPersonID=:pupilsightPersonID, transaction_ref_no=:transaction_ref_no, order_id=:order_id, amount=:amount, status=:status';
            $result = $connection2->prepare($sql);
            $result->execute($data);

            $dtall = $_SESSION["paypost"];
            $newdt = json_decode($dtall['formdata']);
            $stuID = $newdt[0]->stuid;

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
            

            $callback = $_SESSION[$guid]['absoluteURL'] . '/thirdparty/fee_update.php?payid=' . $mihpayid;

            //$callback = $_SESSION[$guid]['absoluteURL'] . '/thirdparty/payment/airpay/multiple_fee_collection_success.php?sid=' . $sid.'&amt='.$amount;
            header('Location: '.$callback);
        }
    }
}

// Process Successfull transaction
}else{
echo '<table><tr><td>Failed Transaction</td></tr></table>
<table>
<tr><td>Variable Name</td><td> Value</td></tr>
<tr><td>TRANSACTIONID:</td><td> '.$TRANSACTIONID.'</td></tr>
<tr><td>APTRANSACTIONID:</td><td> '.$APTRANSACTIONID.'</td></tr>
<tr><td>AMOUNT:</td><td> '.$AMOUNT.'</td></tr>
<tr><td>TRANSACTIONSTATUS:</td><td> '.$TRANSACTIONSTATUS.'</td></tr>
<tr><td>MESSAGE:</td><td> '.$MESSAGE.'</td></tr>


</table></br>
<a class="btn btn-primary" href='.$_SERVER['DOCUMENT_ROOT'].'>Back</a>
';
// Process Failed Transaction
    $returnArr = json_encode($_POST);
    $data = array('gateway' => 'AIRPAY', 'return_data' => $returnArr, 'status' => 'F1');

    $sql = 'INSERT INTO fn_fee_failed_payment_details SET gateway=:gateway, return_data=:return_data, status=:status';
    $result = $connection2->prepare($sql);
    $result->execute($data);
}


?>