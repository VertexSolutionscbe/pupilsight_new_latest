
<?php

error_reporting(E_ERROR | E_PARSE);

include "payu/PayUClient.php";
include $_SERVER['DOCUMENT_ROOT'].'/pupilsight.php';

use payu\PayUClient;

// echo '<pre>';
// print_r($_POST);
// echo '</pre>';
// die();
$status=$_POST["status"];
$firstname=$_POST["firstname"];
$amount=$_POST["amount"]; //Please use the amount value from database
$txnid=$_POST["txnid"];
$posted_hash=$_POST["hash"];
$key=$_POST["key"];
$productinfo=$_POST["productinfo"];
$email=$_POST["email"];
$udf1 = $_POST["udf1"];
$udf2 = $_POST["udf2"];
$udf3 = $_POST["udf3"];
$udf4 = $_POST["udf4"];
$udf5 = $_POST["udf5"];

// echo $key;


$salt="OEyWyUkn"; //Please change the value with the live salt for production environment
// You should set your key & salt values to the function as below:
$payuClient = new PayUClient($key,$salt);

# Set params as follows
$params = array("status"=>$status,"txnid"=>$txnid,"amount"=>$amount,"productinfo"=>$productinfo,"firstname"=>$firstname,"email"=>$email,"udf1"=>$udf1,"udf2"=>$udf2,"udf3"=>$udf3,"udf4"=>$udf4,"udf5"=>$udf5);

# you can generate payment hash as follows:
$hash = new Hasher();
$reverse_hash = $hash->validate_hash($params);
// echo $reverse_hash;
// echo $posted_hash;

	if ($reverse_hash != $posted_hash) {
		echo "Transaction has been tampered. Please try again";
	}

	try{


        $dt = $_SESSION["paypost"];

        $sid = $udf2;

        $data = array('gateway' => 'PAYU', 'submission_id' => $sid, 'transaction_ref_no' => $mihpayid, 'order_id' => $txnid, 'amount' => $amount, 'status' => 'S');

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
        $dts_receipt = array(
            "receipt_no" => $dt["payid"],
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
            "transactionId" => $transactionId
        );

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
        
       

        $_SESSION["dts_receipt_feeitem"] = $dts_receipt_feeitem;
        $_SESSION["dts_receipt"] = $dts_receipt;


        $pupilsightPersonID = $_SESSION[$guid]['pupilsightPersonID'];
        
        $receipt_number = mt_rand(10,99); //tmp fix need to write logic
        $dates = date('Y-m-d');
        $cdt = date('Y-m-d H:i:s');
        //$sql = "INSERT INTO fn_fees_collection SET fn_fees_invoice_id=:fn_fees_invoice_id, pupilsightPersonID=:pupilsightPersonID, pupilsightSchoolYearID =:pupilsightSchoolYearID, fn_fees_counter_id=:fn_fees_counter_id, receipt_number=:receipt_number, is_custom=:is_custom, payment_mode_id=:payment_mode_id, bank_id=:bank_id, dd_cheque_no=:dd_cheque_no, dd_cheque_date=:dd_cheque_date, dd_cheque_amount=:dd_cheque_amount, payment_status=:payment_status, payment_date=:payment_date, fn_fees_head_id=:fn_fees_head_id, fn_fees_receipt_series_id=:fn_fees_receipt_series_id, transcation_amount=:transcation_amount, total_amount_without_fine_discount=:total_amount_without_fine_discount, amount_paying=:amount_paying, fine=:fine, discount=:discount, remarks=:remarks, status=:status,cdt=:cdt";
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
	

?>


<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta http-equiv="X-UA-Compatible" content="ie=edge">
<title>PayU</title>
<meta name="description" content="">
<link rel="stylesheet" type="text/css" href="css/layout.css">
<link rel="stylesheet" type="text/css" href="css/typography.css">
</head>
<body class="page-bg-gray">
<div class="main">
<header>
<div class="header-main gray-hdr">
<div class="hd-logo"><img src="images/logo.svg" alt="PayU Logo"></div>
<div class="hd-nav">
<ul>
<li><a href="https://github.com/payu-india/payu-sdk-php"><i><img src="images/github-icon.svg"></i>View on Github</a></li>
</ul>
</div>
</div>
</header>

<section>
<div class="common-container">
<div class="container">
<div class="code-main-wrap">
<div class="code-container">
<i class="pay-icon">
<img src="images/success-icon.png">
</i>
<h1>Payment Success</h1>
<p>Your Payment has been successful.></p>
<div class="vs-code-main">
<h2>PAYMENT S0URCE OBJECT</h2>
<div class="code-main">
<code>
<p>{</p>
<p>"txnid": <?php echo $txnid ?>,</p>
<p>"status": <?php echo $status ?>,</p>
<p>"details": {</p>
<!-- <p>"statement_descriptor": <span>null,</span></p>
 --><!-- <p>"native_url": <span>null,</span></p>
<p>"data_string": <span>null</span></p> -->
<!-- <p>},</p> -->
<p>"amount": <?php echo $amount ?>,</p>
<p>"productinfo": <?php $firstname ?>,</p>
<p>"firstname": <?php $firstname ?>,</p>
<p>"emailid": <?php $email ?></p>
<!-- <p>"currency": "eur",</p>
<p>"flow": "redirect",</p>
<p>"livemode": false,</p>
<p>"metadata": {</p>
<p>"paymentIntent": "pi_1GaGJvLqkZN1XDm1WnK8JsLo"</p>
<p>},</p> -->
<p>}</p>
<p>}</p>
</code>
</div>

</div>
</div>

</div>
</section>
</div>

</body>
</html>