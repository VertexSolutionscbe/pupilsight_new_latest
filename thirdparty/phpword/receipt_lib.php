<?php

require_once $_SERVER["DOCUMENT_ROOT"].'/vendor/phpoffice/phpword/bootstrap.php';

$file = $_SERVER["DOCUMENT_ROOT"]."/thirdparty/phpword/templates/receipt_1.docx";

$phpword = new \PhpOffice\PhpWord\TemplateProcessor($file);

$dts = array(
    "receipt_no" => $receipt_no,
    "date" => $date,
    "student_name" => $student_name,
    "student_id" => $student_id,
    "class_section" => $class_section,
    "instrument_date" => $instrument_date,
    "instrument_no" => $instrument_no,
    "transcation_amount" => $transcation_amount,
    "fine_amount" => $fine_amount,
    "other_amount" => $other_amount,
    "pay_mode" => $pay_mode
);

foreach ($dts as $key => $value) {
    // $arr[3] will be updated with each value from $arr...
    //echo "{$key} => {$value} ";
    //print_r($arr);
    $phpword->setValue($key, $value);
}
/*
$phpword->setValue('receipt_no','123456');
$phpword->setValue('date','10-Mar-2020');
$phpword->setValue('student_name','Shivansh Kumar');
$phpword->setValue('student_id','12345');
$phpword->setValue('class_section','1A');
$phpword->setValue('instrument_date','10-Mar-2020');
$phpword->setValue('instrument_no','inno11');
$phpword->setValue('transcation_amount','1212');
$phpword->setValue('fine_amount','1010');
$phpword->setValue('other_amount','1231');
$phpword->setValue('pay_mode','1231');*/


$nf = new NumberFormatter("en", NumberFormatter::SPELLOUT);
$total_in_words = $nf->format($dts["transcation_amount"]);

$phpword->setValue('total_in_words', $total_in_words);

/*
$values = [
    ['serial.all' => 1, 'particulars.all' => 'Tution Fee', 'amount.all' => '4000'],
    ['serial.all' => 2, 'particulars.all' => 'Transport Fee', 'amount.all' => '2000'],
    ['serial.all' => 3, 'particulars.all' => 'Library Fee', 'amount.all' => '100']
];
*/

if(!empty($fee_items)){
    $phpword->cloneRowAndSetValues('serial.all', $fee_items);
}


$savedocsx = $_SERVER["DOCUMENT_ROOT"]."/public/gns_receipt_edited.docx";
$phpword->saveAs($savedocsx);

?>