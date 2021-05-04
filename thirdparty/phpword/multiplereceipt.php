<?php
include '../../pupilsight.php';
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once $_SERVER["DOCUMENT_ROOT"].'/vendor/phpoffice/phpword/bootstrap.php';
require_once $_SERVER["DOCUMENT_ROOT"] . '/pdf_convert.php';


$dtsmulti = $_SESSION["dts_receipt"];
$fee_items_multi = $_SESSION["dts_receipt_feeitem"];

// echo '<pre>';
// print_r($dtsmulti);
// print_r($fee_items_multi);
// echo '</pre>';
// die();


foreach($dtsmulti as $k => $dts){

    $file = $dts['receiptTemplate'];
    $column_start_by = $dts['column_start_by'];

    $phpword = new \PhpOffice\PhpWord\TemplateProcessor($file);
    $dts["total"]=$dts["transcation_amount"];
    foreach ($dts as $key => $value) {
        try {
            if(!empty($value)){
                $phpword->setValue($key, $value);
            } else {
                $phpword->setValue($key, '');
            }
        } catch (Exception $ex) {
        }
    }

    if(!empty($dts["transcation_amount"])){
        $total_in_words=convert_number_to_words($dts["transcation_amount"]);
        $phpword->setValue('total_paid_in_words', ucwords($total_in_words));
    }

    
    if(!empty($fee_items_multi[$k])){
        // try {
        //     $phpword->cloneRowAndSetValues('serial.all', $fee_items_multi[$k]);
        // } catch (Exception $ex) {
        //     //print_r($ex);
        // }
        try {
            if($column_start_by == 'serial_no'){
                $phpword->cloneRowAndSetValues('serial.all', $fee_items_multi[$k]);
            } else {
                $phpword->cloneRowAndSetValues('particulars.all', $fee_items_multi[$k]);
            }
        } catch (Exception $ex) {
            //print_r($ex);
        }
    }

    try {
        $stuName = str_replace(' ', '_', $dts["student_name"]);
        $receiptfilename = $stuName.'_'.$dts["transactionId"];
        $_SESSION['doc_receipt_id']=$receiptfilename;

        $dataiu = array('filename' => $receiptfilename,  'transaction_id' => $dts["transactionId"]);
        $sqliu = 'UPDATE fn_fees_collection SET filename=:filename WHERE transaction_id=:transaction_id';
        $resultiu = $connection2->prepare($sqliu);
        $resultiu->execute($dataiu);

        // $fileName = $dts["transactionId"] . ".docx";
        $fileName = $receiptfilename . ".docx";
        $inFilePath = $_SERVER["DOCUMENT_ROOT"] . "/public/receipts/";
        $savedocsx = $inFilePath . $fileName;
        //$savedocsx = $_SERVER["DOCUMENT_ROOT"]."/public/receipts/".$dts["transactionId"].".docx";
        //echo $savedocsx;
        $phpword->saveAs($savedocsx);

        convert($fileName, $inFilePath, $inFilePath, FALSE, TRUE);
    } catch (Exception $ex) {
    }

    
}

unset($_SESSION['dts_receipt']);
unset($_SESSION['dts_receipt_feeitem']);

// echo 'work';
// die();


$dtall = $_SESSION["paypost"];
$newdt = json_decode($dtall['formdata']);
foreach($newdt as $k=>$dt){
    $callbackurl = $dt->callbackurl;
}    


$admincallback = $_SESSION["admin_callback"];
if(!empty($admincallback)){
    $callback = $admincallback;
} else {
    $callback = $callbackurl.'&success=1';
}

if(isset($callback)){
    header('Location: '.$callback);
    exit;
}else{
    header('Location: index.php');
    exit;
}

function convert_number_to_words($number) {
    $no = round($number);
    $decimal = round($number - ($no = floor($number)), 2) * 100;    
    $digits_length = strlen($no);    
    $i = 0;
    $str = array();
    $words = array(
        0 => '',
        1 => 'One',
        2 => 'Two',
        3 => 'Three',
        4 => 'Four',
        5 => 'Five',
        6 => 'Six',
        7 => 'Seven',
        8 => 'Eight',
        9 => 'Nine',
        10 => 'Ten',
        11 => 'Eleven',
        12 => 'Twelve',
        13 => 'Thirteen',
        14 => 'Fourteen',
        15 => 'Fifteen',
        16 => 'Sixteen',
        17 => 'Seventeen',
        18 => 'Eighteen',
        19 => 'Nineteen',
        20 => 'Twenty',
        30 => 'Thirty',
        40 => 'Forty',
        50 => 'Fifty',
        60 => 'Sixty',
        70 => 'Seventy',
        80 => 'Eighty',
        90 => 'Ninety');
    $digits = array('', 'Hundred', 'Thousand', 'Lakh', 'Crore');
    while ($i < $digits_length) {
        $divider = ($i == 2) ? 10 : 100;
        $number = floor($no % $divider);
        $no = floor($no / $divider);
        $i += $divider == 10 ? 1 : 2;
        if ($number) {
            $plural = (($counter = count($str)) && $number > 9) ? 's' : null;            
            $str [] = ($number < 21) ? $words[$number] . ' ' . $digits[$counter] . $plural : $words[floor($number / 10) * 10] . ' ' . $words[$number % 10] . ' ' . $digits[$counter] . $plural;
        } else {
            $str [] = null;
        }  
    }
    
    $Rupees = implode(' ', array_reverse($str));
    $paise = ($decimal) ? "And " . ($words[$decimal - $decimal%10]) ." Paise " .($words[$decimal%10])  : '';
    return ($Rupees ? ' ' . $Rupees : '') . $paise . " Only";
}
?>