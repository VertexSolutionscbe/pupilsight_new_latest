<?php
include '../../pupilsight.php';
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once $_SERVER["DOCUMENT_ROOT"].'/pupilsight/vendor/phpoffice/phpword/bootstrap.php';
//require_once $_SERVER["DOCUMENT_ROOT"] . '/pdf_convert.php';


$dtsmulti = $_SESSION["dts_receipt"];
$fee_items_multi = $_SESSION["dts_receipt_feeitem"];

// echo '<pre>';
// print_r($dtsmulti);
// print_r($fee_items_multi);
// echo '</pre>';
// die();


foreach($dtsmulti as $k => $dts){

    $file = $dts['receiptTemplate'];

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
        $phpword->setValue('total_in_words', ucwords($total_in_words));
    }

    
    if(!empty($fee_items_multi[$k])){
        try {
            $phpword->cloneRowAndSetValues('serial.all', $fee_items_multi[$k]);
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
        $inFilePath = $_SERVER["DOCUMENT_ROOT"] . "/pupilsight/public/receipts/";
        $savedocsx = $inFilePath . $fileName;
        //$savedocsx = $_SERVER["DOCUMENT_ROOT"]."/public/receipts/".$dts["transactionId"].".docx";
        //echo $savedocsx;
        $phpword->saveAs($savedocsx);

        //convert($fileName, $inFilePath, $inFilePath, FALSE, TRUE);
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
   
    $hyphen      = '-';
    $conjunction = '  ';
    $separator   = ' ';
    $negative    = 'negative ';
    $decimal     = ' point ';
    $dictionary  = array(
        0                   => 'Zero',
        1                   => 'One',
        2                   => 'Two',
        3                   => 'Three',
        4                   => 'Four',
        5                   => 'Five',
        6                   => 'Six',
        7                   => 'Seven',
        8                   => 'Eight',
        9                   => 'Nine',
        10                  => 'Ten',
        11                  => 'Eleven',
        12                  => 'Twelve',
        13                  => 'Thirteen',
        14                  => 'Fourteen',
        15                  => 'Fifteen',
        16                  => 'Sixteen',
        17                  => 'Seventeen',
        18                  => 'Eighteen',
        19                  => 'Nineteen',
        20                  => 'Twenty',
        30                  => 'Thirty',
        40                  => 'Fourty',
        50                  => 'Fifty',
        60                  => 'Sixty',
        70                  => 'Seventy',
        80                  => 'Eighty',
        90                  => 'Ninety',
        100                 => 'Hundred',
        1000                => 'Thousand',
        1000000             => 'Million',
        1000000000          => 'Billion',
        1000000000000       => 'Trillion',
        1000000000000000    => 'Quadrillion',
        1000000000000000000 => 'Quintillion'
    );
   
    if (!is_numeric($number)) {
        return false;
    }
   
    if (($number >= 0 && (int) $number < 0) || (int) $number < 0 - PHP_INT_MAX) {
        // overflow
        trigger_error(
            'convert_number_to_words only accepts numbers between -' . PHP_INT_MAX . ' and ' . PHP_INT_MAX,
            E_USER_WARNING
        );
        return false;
    }
 
    if ($number < 0) {
        return $negative . convert_number_to_words(abs($number));
    }
   
    $string = $fraction = null;
   
    if (strpos($number, '.') !== false) {
        list($number, $fraction) = explode('.', $number);
    }
   
    switch (true) {
        case $number < 21:
            $string = $dictionary[$number];
            break;
        case $number < 100:
            $tens   = ((int) ($number / 10)) * 10;
            $units  = $number % 10;
            $string = $dictionary[$tens];
            if ($units) {
                $string .= $hyphen . $dictionary[$units];
            }
            break;
        case $number < 1000:
            $hundreds  = $number / 100;
            $remainder = $number % 100;
            $string = $dictionary[$hundreds] . ' ' . $dictionary[100];
            if ($remainder) {
                $string .= $conjunction . convert_number_to_words($remainder);
            }
            break;
        default:
            $baseUnit = pow(1000, floor(log($number, 1000)));
            $numBaseUnits = (int) ($number / $baseUnit);
            $remainder = $number % $baseUnit;
            $string = convert_number_to_words($numBaseUnits) . ' ' . $dictionary[$baseUnit];
            if ($remainder) {
                $string .= $remainder < 100 ? $conjunction : $separator;
                $string .= convert_number_to_words($remainder);
            }
            break;
    }
   
    if (null !== $fraction && is_numeric($fraction)) {
        $string .= $decimal;
        $words = array();
        foreach (str_split((string) $fraction) as $number) {
            $words[] = $dictionary[$number];
        }
        $string .= implode(' ', $words);
    }
   
    return $string;
}
?>