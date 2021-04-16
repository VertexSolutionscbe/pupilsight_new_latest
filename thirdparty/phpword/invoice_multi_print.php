<?php
include '../../pupilsight.php';
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once $_SERVER["DOCUMENT_ROOT"].'/vendor/phpoffice/phpword/bootstrap.php';
require_once $_SERVER["DOCUMENT_ROOT"] . '/pdf_convert.php';


try {

    $ids = explode(',', $_GET['invid']);
    
    if(!empty($ids)){
        foreach($ids as $id){
            $sql = 'SELECT a.invoice_no as invNo, a.pupilsightPersonID, b.*, d.path, d.column_start_by FROM fn_fee_invoice_student_assign AS a LEFT JOIN fn_fee_invoice AS b ON a.fn_fee_invoice_id = b.id LEFT JOIN fn_fees_head AS c ON b.fn_fees_head_id = c.id LEFT JOIN fn_fees_receipt_template_master AS d ON c.invoice_template = d.id WHERE a.id = '.$id.' ';
            $result = $connection2->query($sql);
            $invData = $result->fetch();
            //print_r($invData);

            $inv_title = $invData['title'];
            $invoiceId = $invData['id'];
            $invoice_no = $invData['invNo'];
            $pupilsightPersonID = $invData['pupilsightPersonID'];
            $file = $invData['path'];
            $column_start_by = $invData['column_start_by'];

            $inv_date = '';
            if(!empty($invData['cdt'])){
                $inv_date = date('d/m/Y', strtotime($invData['cdt']));
            }

            $due_date = '';
            if(!empty($invData['due_date']) && $invData['due_date'] != '1970-01-01'){
                $due_date = date('d/m/Y', strtotime($invData['due_date']));
            }
           
            
            if(!empty($file)){

                $chkcussql = 'SELECT field_name FROM custom_field WHERE field_name = "correspondence_address" ';
                $chkresultstu = $connection2->query($chkcussql);
                $custDataChk = $chkresultstu->fetch();
                if(!empty($custDataChk)){
                    $fieldName = ', a.correspondence_address';
                } else {
                    $fieldName = '';
                }

                $sqlstu = "SELECT a.officialName , a.admission_no, b.name as class, c.name as section ".$fieldName." FROM pupilsightPerson AS a LEFT JOIN pupilsightStudentEnrolment AS d ON a.pupilsightPersonID = d.pupilsightPersonID LEFT JOIN pupilsightYearGroup AS b ON d.pupilsightYearGroupID = b.pupilsightYearGroupID LEFT JOIN pupilsightRollGroup AS c ON d.pupilsightRollGroupID = c.pupilsightRollGroupID WHERE a.pupilsightPersonID = " . $pupilsightPersonID . " ";
                $resultstu = $connection2->query($sqlstu);
                $valuestu = $resultstu->fetch();

                $total = 0;
                $totalTax = 0;
                $totalamtWitoutTaxDis = 0;
                if ($invData['display_fee_item'] == '2') {
                    $sqcs = "select SUM(fi.total_amount) AS tamnt, SUM(fi.amount) AS amnt, SUM(fi.tax) AS ttax from fn_fee_invoice_item as fi, fn_fee_items as items where fi.fn_fee_item_id = items.id and fi.fn_fee_invoice_id =  " . $invoiceId . " ";
                    $resultfi = $connection2->query($sqcs);
                    $valuefi = $resultfi->fetchAll();
                    if (!empty($valuefi)) {
                        $cnt = 1;
                        foreach ($valuefi as $vfi) {
                            $taxamt = 0;
                            if(!empty($vfi["ttax"])){
                                $taxamt = ($vfi["ttax"] / 100) * $vfi["amnt"];
                                $taxamt = number_format($taxamt, 2, '.', '');
                            }
                            $dts_receipt_feeitem[] = array(
                                "serial.all" => $cnt,
                                "particulars.all" => $invData['invoice_title'],
                                "inv_amt.all" => $vfi["amnt"],
                                "tax.all" => $taxamt,
                                "amount.all" => $vfi["tamnt"]
                            );
                            $total += $vfi["tamnt"];
                            $totalTax += $taxamt;
                            $totalamtWitoutTaxDis += $vfi["amnt"];
                            $cnt++;
                        }
                    }
                    
                } else {
                    $sqcs = "select fi.total_amount, fi.amount, fi.tax, items.name from fn_fee_invoice_item as fi, fn_fee_items as items where fi.fn_fee_item_id = items.id and fi.fn_fee_invoice_id =  " . $invoiceId . " ";
                    $resultfi = $connection2->query($sqcs);
                    $valuefi = $resultfi->fetchAll();

                    
                    if (!empty($valuefi)) {
                        $cnt = 1;
                        foreach ($valuefi as $vfi) {
                            $taxamt = 0;
                            if(!empty($vfi["tax"])){
                                $taxamt = ($vfi["tax"] / 100) * $vfi["amount"];
                                $taxamt = number_format($taxamt, 2, '.', '');
                            }
                            $dts_receipt_feeitem[] = array(
                                "serial.all" => $cnt,
                                "particulars.all" => $vfi["name"],
                                "inv_amt.all" => $vfi["amount"],
                                "tax.all" => $taxamt,
                                "amount.all" => $vfi["total_amount"]
                            );
                            $total += $vfi["total_amount"];
                            $totalTax += $taxamt;
                            $totalamtWitoutTaxDis += $vfi["amount"];
                            $cnt++;
                        }
                    }
                }

                $sqlfat = "SELECT b.officialName , b.phone1, b.email FROM pupilsightFamilyRelationship AS a LEFT JOIN pupilsightPerson AS b ON a.pupilsightPersonID1 = b.pupilsightPersonID WHERE a.pupilsightPersonID2 = " . $pupilsightPersonID . " AND a.relationship = 'Father' ";
                $resultfat = $connection2->query($sqlfat);
                $valuefat = $resultfat->fetch();

                $father_name = '';
                $father_email = '';
                $father_phone = '';
                if(!empty($valuefat)){
                    $father_name = $valuefat['officialName'];
                    $father_email = $valuefat['email'];
                    $father_phone = $valuefat['phone1'];
                }

                $sqlmot = "SELECT b.officialName , b.phone1, b.email FROM pupilsightFamilyRelationship AS a LEFT JOIN pupilsightPerson AS b ON a.pupilsightPersonID1 = b.pupilsightPersonID WHERE a.pupilsightPersonID2 = " . $pupilsightPersonID . " AND a.relationship = 'Mother' ";
                $resultmot = $connection2->query($sqlmot);
                $valuemot = $resultmot->fetch();

                $mother_name = '';
                $mother_email = '';
                $mother_phone = '';
                if(!empty($valuemot)){
                    $mother_name = $valuemot['officialName'];
                    $mother_email = $valuemot['email'];
                    $mother_phone = $valuemot['phone1'];
                }



                $class_section = $valuestu["class"] . " " . $valuestu["section"];
                $date = date('d-m-Y');

                if(!empty($custDataChk)){
                    $coreaddress = $valuestu["correspondence_address"];
                } else {
                    $coreaddress = '';
                }

                $dts_receipt = array(
                    "inv_title" => htmlspecialchars($inv_title),
                    "invoice_no" => $invoice_no,
                    "date" => $date,
                    "student_name" => $valuestu["officialName"],
                    "student_id" => $pupilsightPersonID,
                    "admission_no" => $valuestu["admission_no"],
                    "father_name" => $father_name,
                    "mother_name" => $mother_name,
                    "class_section" => $class_section,
                    "total_amount" => number_format($total, 2, '.', ''),
                    "inv_date" => $inv_date,
                    "due_date" => $due_date,
                    "address" => $coreaddress,
                    "total_tax" => number_format($totalTax, 2, '.', ''),
                    "inv_total" => number_format($totalamtWitoutTaxDis, 2, '.', '')
                );


                $dts = $dts_receipt;
                $fee_items = $dts_receipt_feeitem;


                
                //$file = $_SERVER["DOCUMENT_ROOT"]."/pupilsight/thirdparty/phpword/templates/invoice_template.docx";


                $phpword = new \PhpOffice\PhpWord\TemplateProcessor($file);


                $dts["total"]=$dts["total_amount"];

            for ($x = 1; $x <= 3; $x++) {    
                foreach ($dts as $key => $value) {
                    try {
                        if(!empty($value)){
                            $phpword->setValue($key, $value);
                        } else {
                            $phpword->setValue($key, '');
                        }
                        
                    } catch (Exception $ex) {
                        print_r($ex);
                    }
                }

                if(!empty($dts["total_amount"])){
                    /*$nf = new NumberFormatter("en", NumberFormatter::SPELLOUT);
                    $total_in_words = $nf->format($dts["transcation_amount"]);*/
                    $total_in_words=convert_number_to_words($dts["total_amount"]);
                    $phpword->setValue('total_in_words', ucwords($total_in_words));
                }

                if(!empty($fee_items)){
                    try {
                        if($column_start_by == 'serial_no'){
                            $phpword->cloneRowAndSetValues('serial.all', $fee_items);
                        } else {
                            $phpword->cloneRowAndSetValues('particulars.all', $fee_items);
                        }
                    } catch (Exception $ex) {
                        //print_r($ex);
                    }
                }
            }

                try {
                    $invoice_no = str_replace("/","-",$invoice_no);
                    
                    $fileName = $invoice_no . ".docx";
                    $inFilePath = $_SERVER["DOCUMENT_ROOT"] . "/public/invoice_receipts/";
                    $savedocsx = $inFilePath . $fileName;
                    //$savedocsx = $_SERVER["DOCUMENT_ROOT"]."/public/receipts/".$dts["transactionId"].".docx";
                    $phpword->saveAs($savedocsx);
                    
                    convert($fileName, $inFilePath, $inFilePath, FALSE, TRUE);

                    

                    // $contenttype = "application/force-download";
                    // header("Content-Type: " . $contenttype);
                    // header("Content-Disposition: attachment; filename=\"" . basename($fileName) . "\";");
                    // readfile($savedocsx);
                } catch (Exception $ex) {
                }
            }
        }
        $callback = $_SESSION[$guid]['absoluteURL']."/zipinvoice.php?zipname=invoices";

        header('Location: '.$callback);
        exit;
    }

} catch (Exception $ex) {
    //print_r($ex);
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