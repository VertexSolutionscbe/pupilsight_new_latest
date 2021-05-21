<?php

require('config.php');

session_start();

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
    //$parms = $_SESSION["paypost"];
    $_SESSION['payment_gateway_id'] = $_POST['razorpay_payment_id'];
    //$callback = $_SESSION["paypost"]["callbackurl"];
    $baseurl = "http://" . $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'];
    $callback = "/thirdparty/phpword/receipt_offline.php";
    include $_SERVER['DOCUMENT_ROOT'].'/db.php';

    

    //$conn
    try{
        $dt = $_SESSION["paypost"];

        $sid = $_SESSION['submissionId'];

        $sqlon = 'SELECT transaction_ref_no FROM fn_fee_payment_details WHERE transaction_ref_no = "'.$_POST['razorpay_payment_id'].'" ';
        $resulton = $connection2->query($sqlon);
        $onData = $resulton->fetch();
        if(!empty($onData)){
            header('Location: index.php');
            exit;
        } else {
            $data = array('gateway' => 'RAZORPAY', 'submission_id' => $sid, 'transaction_ref_no' => $_POST['razorpay_payment_id'], 'order_id' => $_SESSION['razorpay_order_id'], 'amount' => $dt["amount"], 'status' => 'S');

            $sql = 'INSERT INTO fn_fee_payment_details SET gateway=:gateway, submission_id=:submission_id, transaction_ref_no=:transaction_ref_no, order_id=:order_id, amount=:amount, status=:status';
            $result = $connection2->prepare($sql);
            $result->execute($data);

           
            $rand = mt_rand(10,99);  
            $t = time();
            $transactionId = $t.$rand;

            $section = "";
            $clss = "";
            if(!empty($dt["sectionid"])){
                $sqcs = "select name from pupilsightRollGroup where pupilsightRollGroupID='".$dt["sectionid"]."'";
                $result = $conn->query($sqcs);
                if ($result->num_rows > 0) {
                    while($row = $result->fetch_assoc()) {
                        $section = $row["name"];
                    }
                }
            }

            if(!empty($dt["classid"])){
                $sqcs = "select name from pupilsightYearGroup where pupilsightYearGroupID='".$dt["classid"]."'";
                $result = $conn->query($sqcs);
                if ($result->num_rows > 0) {
                    while($row = $result->fetch_assoc()) {
                        $clss = $row["name"];
                    }
                }
            }

            

            $class_section = $clss ."".$section;
            

            $invoice_id = $dt["fn_fees_invoice_id"];
            if(!empty($invoice_id)){
                $chksql = 'SELECT fn_fee_structure_id, display_fee_item, title as invoice_title FROM fn_fee_invoice WHERE id = '.$invoice_id.' ';
                $resultchk = $connection2->query($chksql);
                $valuechk = $resultchk->fetch();
                if($valuechk['fn_fee_structure_id'] == ''){
                    $valuech = $valuechk;
                } else {
                    $chsql = 'SELECT b.invoice_title, a.display_fee_item FROM fn_fee_invoice AS a LEFT JOIN fn_fee_structure AS b ON a.fn_fee_structure_id = b.id WHERE a.id= '.$invoice_id.' AND a.fn_fee_structure_id IS NOT NULL ';
                    $resultch = $connection2->query($chsql);
                    $valuech = $resultch->fetch();
                }
                if($valuech['display_fee_item'] == '2'){
                    $sqcs = "select SUM(fi.total_amount) AS tamnt from fn_fee_invoice_item as fi, fn_fee_items as items where fi.fn_fee_item_id = items.id and fi.id in(".$dt["fn_fee_invoice_item_id"].")";
                    $resultfi = $connection2->query($sqcs);
                    $valuefi = $resultfi->fetchAll();
                    if (!empty($valuefi)) {
                        $cnt = 1;
                        foreach($valuefi as $vfi){
                            $dts_receipt_feeitem[] = array(
                                "serial.all"=>$cnt,
                                "particulars.all"=>$valuech['invoice_title'],
                                "amount.all"=>$vfi["tamnt"]
                            );
                            $cnt ++;
                        }
                    }

                } else {
                    $sqcs = "select fi.total_amount, items.name from fn_fee_invoice_item as fi, fn_fee_items as items where fi.fn_fee_item_id = items.id and fi.id in(".$dt["fn_fee_invoice_item_id"].")";
                    $result = $conn->query($sqcs);
                    if ($result->num_rows > 0) {
                        $cnt = 1;
                        while($row = $result->fetch_assoc()) {
                            $dts_receipt_feeitem[] = array(
                                "serial.all"=>$cnt,
                                "particulars.all"=>$row["name"],
                                "amount.all"=>$row["total_amount"]
                            );
                            $cnt ++;
                        }
                    }
                }
            }
            
            $pupilsightPersonID = $_SESSION[$guid]['pupilsightPersonID'];
            
            //$receipt_number = mt_rand(10,99); //tmp fix need to write logic

            $fn_fees_receipt_series_id = $dt["rec_fn_fee_series_id"];
            if (!empty($fn_fees_receipt_series_id)) {
                $sqlrec = 'SELECT id, formatval FROM fn_fee_series WHERE id = "' . $fn_fees_receipt_series_id . '" ';
                $resultrec = $connection2->query($sqlrec);
                $recptser = $resultrec->fetch();

                // $invformat = explode('/',$recptser['format']);
                $invformat = explode('$', $recptser['formatval']);
                $iformat = '';
                $orderwise = 0;
                foreach ($invformat as $inv) {
                    if ($inv == '{AB}') {
                        $datafort = array('fn_fee_series_id' => $fn_fees_receipt_series_id, 'order_wise' => $orderwise, 'type' => 'numberwise');
                        $sqlfort = 'SELECT id, no_of_digit, last_no FROM fn_fee_series_number_format WHERE fn_fee_series_id=:fn_fee_series_id AND order_wise=:order_wise AND type=:type';
                        $resultfort = $connection2->prepare($sqlfort);
                        $resultfort->execute($datafort);
                        $formatvalues = $resultfort->fetch();
                        // $iformat .= $formatvalues['last_no'].'/';

                        $str_length = $formatvalues['no_of_digit'];

                        $iformat .= str_pad($formatvalues['last_no'], $str_length, '0', STR_PAD_LEFT);

                        $lastnoadd = $formatvalues['last_no'] + 1;

                        //$lastno = substr("0000000{$lastnoadd}", -$str_length);
                        $lastno = str_pad($lastnoadd, $str_length, '0', STR_PAD_LEFT);

                        $datafort1 = array('fn_fee_series_id' => $fn_fees_receipt_series_id, 'order_wise' => $orderwise, 'type' => 'numberwise', 'last_no' => $lastno);
                        $sqlfort1 = 'UPDATE fn_fee_series_number_format SET last_no=:last_no WHERE fn_fee_series_id=:fn_fee_series_id AND type=:type AND order_wise=:order_wise';
                        $resultfort1 = $connection2->prepare($sqlfort1);
                        $resultfort1->execute($datafort1);
                    } else {
                        //$iformat .= $inv.'/';
                        $iformat .= $inv;
                    }
                    $orderwise++;
                }

                //    $receipt_number = rtrim($iformat, "/");;
                $receipt_number = $iformat;
            } else {
                $receipt_number = '';
            }

            $sqlrt = 'SELECT b.path FROM fn_fees_head AS a LEFT JOIN fn_fees_receipt_template_master AS b ON a.receipt_template = b.id WHERE a.id = ' . $dt['fn_fees_head_id'] . ' ';
            $resultrt = $connection2->query($sqlrt);
            $recTempData = $resultrt->fetch();
            $receiptTemplate = $recTempData['path'];
            $bank_name = '';
            
            $dts_receipt = array(
                "receipt_no" => $receipt_number,
                "date" => date("d-M-Y"),
                "student_name" => $dt["name"],
                "student_id" => $dt["stuid"],
                "class_section" => $class_section,
                "instrument_date" => "NA",
                "instrument_no" => "NA",
                "transcation_amount" => $dt["amount"],
                "fine_amount" => $dt["fine"],
                "other_amount" => "NA",
                "pay_mode" => "Online",
                "transactionId" => $transactionId,
                "receiptTemplate" => $receiptTemplate,
                "bank_name" => $bank_name
            );

            $_SESSION["dts_receipt_feeitem"] = $dts_receipt_feeitem;
            $_SESSION["dts_receipt"] = $dts_receipt;

            $dates = date('Y-m-d');
            $cdt = date('Y-m-d H:i:s');
            $sq = "INSERT INTO fn_fees_collection (fn_fees_invoice_id, transaction_id,submission_id, pupilsightSchoolYearID,
            receipt_number, pay_gateway_id, payment_status, payment_date, fn_fees_head_id, fn_fees_receipt_series_id, 
            transcation_amount, total_amount_without_fine_discount, amount_paying, fine, discount, status, cdt) ";
            $sq .=" values(
                    '".$dt["fn_fees_invoice_id"]."'
                    ,'".$transactionId."'
                    ,'".$dt["stuid"]."'
                    ,'".$dt["pupilsightSchoolYearID"]."'
                    ,'".$receipt_number."'
                    ,'".$_POST['razorpay_payment_id']."'
                    ,'Payment Received'
                    ,'".$dates."'
                    ,'".$dt["fn_fees_head_id"]."'
                    ,'".$dt["rec_fn_fee_series_id"]."'
                    ,'".$dt["amount"]."'
                    ,'".$dt["total_amount_without_fine_discount"]."'
                    ,'".$dt["amount"]."'
                    ,'".$dt["fine"]."'
                    ,'".$dt["discount"]."'
                    ,'1'
                    ,'".$cdt."'
                    ); ";
            //echo $sq;

            $tsq = "insert into fn_fees_applicant_collection (submission_id, transaction_id, fn_fees_invoice_id, fn_fee_invoice_item_id, invoice_no) values ";
            if(!empty($dt["fn_fee_invoice_item_id"])){
                $dts = explode(",",$dt["fn_fee_invoice_item_id"]);
                $len = count($dts);
                $i = 0;
                $tsq1 = "";
                while($i<$len){
                    $fn_fee_invoice_item_id = $dts[$i];
                    if(!empty($tsq1)){
                        $tsq1 .=",";
                    }
                    $tsq1 .="('".$dt["stuid"]."','".$transactionId."','".$dt["fn_fees_invoice_id"]."','".$fn_fee_invoice_item_id."','".$dt["payid"]."')";
                    $i++;
                }
                $tsq .= $tsq1.";";
            }
                    
                
            if ($conn->query($sq) === TRUE) {
                $conn->query($tsq);
                echo "New record created successfully";
            } else {
                echo "Error: " . $sq . "<br>" . $conn->error;
            }
            
            $conn->close();
        }
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
