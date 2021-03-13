<?php
/*
Pupilsight, Flexible & Open School System
*/

include '../../pupilsight.php';

$URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_POST['address']).'/fee_head_manage.php';

if (isActionAccessible($guid, $connection2, '/modules/Finance/fee_head_manage_add.php') == false) {
    $URL .= '&return=error0';
    header("Location: {$URL}");
} else {
    //Proceed!
    //Validate Inputs
    $name = $_POST['name'];
    $account_code = $_POST['account_code'];
    $pupilsightSchoolYearID = $_POST['pupilsightSchoolYearID'];
    $description = $_POST['description'];
    $bank_name = $_POST['bank_name'];
    $ac_no = $_POST['ac_no'];
    $inv_fee_series_id = $_POST['inv_fee_series_id'];
    $recp_fee_series_id = $_POST['recp_fee_series_id'];
    $recp_fee_series_online_pay = $_POST['recp_fee_series_online_pay'];
    $refund_fee_series_online_pay = $_POST['refund_fee_series_online_pay'];
    $payment_gateway = $_POST['payment_gateway'];
    if(!empty($_POST['payment_gateway_id'])){
        $payment_gateway_id = $_POST['payment_gateway_id'];
    } else {
        $payment_gateway_id = '';
    }
    
    $invoice_template = $_POST['invoice_template'];
    $receipt_template = $_POST['receipt_template'];
    //$invoice_template = '';
    $cdt = date('Y-m-d H:i:s');
    
    if ($name == ''  or $account_code == '' ) {
        $URL .= '&return=error1';
        header("Location: {$URL}");
    } else {
        //Check unique inputs for uniquness
        try {
            $data = array('name' => $name, 'account_code' => $account_code);
            $sql = 'SELECT * FROM fn_fees_head WHERE name=:name OR account_code=:account_code';
            $result = $connection2->prepare($sql);
            $result->execute($data);
        } catch (PDOException $e) {
            $URL .= '&return=error2';
            header("Location: {$URL}");
            exit();
        }
        if ($result->rowCount() > 0) {
            $URL .= '&return=error3';
            header("Location: {$URL}");
        } else {
            //Write to database
            try {
                $data = array('name' => $name, 'account_code' => $account_code, 'pupilsightSchoolYearID' => $pupilsightSchoolYearID, 'description' => $description, 'bank_name' => $bank_name, 'ac_no' => $ac_no, 'inv_fee_series_id' => $inv_fee_series_id, 'recp_fee_series_id' => $recp_fee_series_id, 'recp_fee_series_online_pay' => $recp_fee_series_online_pay, 'refund_fee_series_online_pay' => $refund_fee_series_online_pay, 'payment_gateway' => $payment_gateway, 'payment_gateway_id' => $payment_gateway_id, 'invoice_template' => $invoice_template, 'receipt_template' => $receipt_template, 'cdt' => $cdt);
                $sql = 'INSERT INTO fn_fees_head SET name=:name, account_code=:account_code, pupilsightSchoolYearID=:pupilsightSchoolYearID,description=:description, bank_name=:bank_name, ac_no=:ac_no, inv_fee_series_id=:inv_fee_series_id, recp_fee_series_id=:recp_fee_series_id,recp_fee_series_online_pay=:recp_fee_series_online_pay, refund_fee_series_online_pay=:refund_fee_series_online_pay, payment_gateway=:payment_gateway, payment_gateway_id=:payment_gateway_id, invoice_template=:invoice_template, receipt_template=:receipt_template, cdt=:cdt';
                $result = $connection2->prepare($sql);
                $result->execute($data);
                // echo '<pre>';
                // print_r($data);
                // echo '</pre>';
            } catch (PDOException $e) {
                $URL .= '&return=error3';
                header("Location: {$URL}");
                exit();
            }

            //Last insert ID
            $AI = str_pad($connection2->lastInsertID(), 3, '0', STR_PAD_LEFT);

            $URL .= "&return=success0&editID=$AI";
            header("Location: {$URL}");
        }
    }
}
