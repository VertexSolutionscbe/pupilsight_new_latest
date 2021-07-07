<?php
include '../../pupilsight.php';
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once $_SERVER["DOCUMENT_ROOT"].'/vendor/phpoffice/phpword/bootstrap.php';
require_once $_SERVER["DOCUMENT_ROOT"] . '/pdf_convert.php';

try {

    $id = $_GET['invid'];

    $sql = 'SELECT a.invoice_no as invNo, a.submission_id, b.*, b.title as invoice_title, d.path, d.column_start_by, c.ac_no FROM fn_fee_invoice_applicant_assign AS a LEFT JOIN fn_fee_invoice AS b ON a.fn_fee_invoice_id = b.id LEFT JOIN fn_fees_head AS c ON b.fn_fees_head_id = c.id LEFT JOIN fn_fees_receipt_template_master AS d ON c.invoice_template = d.id WHERE a.id = '.$id.' ';
    $result = $connection2->query($sql);
    $invData = $result->fetch();
    // echo '<pre>';
    // print_r($invData);

    $inv_title = $invData['title'];
    $invoiceId = $invData['id'];
    $invoice_no = $invData['invNo'];
    $fee_head_acc_no = $invData['ac_no'];

    $inv_date = '';
    if(!empty($invData['cdt'])){
        $inv_date = date('d/m/Y', strtotime($invData['cdt']));
    }

    $due_date = '';
    if(!empty($invData['due_date']) && $invData['due_date'] != '1970-01-01'){
        $due_date = date('d/m/Y', strtotime($invData['due_date']));
    }
    
    $submission_id = $invData['submission_id'];
    $file = $invData['path'];
    $column_start_by = $invData['column_start_by'];
    
    if(!empty($file)){

        $chkcussql = 'SELECT field_name FROM custom_field WHERE field_name = "correspondence_address" ';
        $chkresultstu = $connection2->query($chkcussql);
        $custDataChk = $chkresultstu->fetch();
        if(!empty($custDataChk)){
            $fieldName = ', a.correspondence_address';
        } else {
            $fieldName = '';
        }

        // $sqlstu = "SELECT a.officialName , a.admission_no, b.name as class, c.name as section ".$fieldName." FROM pupilsightPerson AS a LEFT JOIN pupilsightStudentEnrolment AS d ON a.pupilsightPersonID = d.pupilsightPersonID LEFT JOIN pupilsightYearGroup AS b ON d.pupilsightYearGroupID = b.pupilsightYearGroupID LEFT JOIN pupilsightRollGroup AS c ON d.pupilsightRollGroupID = c.pupilsightRollGroupID WHERE a.pupilsightPersonID = " . $pupilsightPersonID . " ";

        $sqlstu = "SELECT  b.name as prog,c.name as class FROM wp_fluentform_submissions AS a LEFT JOIN pupilsightProgram AS b ON a.pupilsightProgramID = b.pupilsightProgramID LEFT JOIN pupilsightYearGroup AS c ON a.pupilsightYearGroupID = c.pupilsightYearGroupID WHERE a.id = ".$submission_id." ";
        $resultstu = $connection2->query($sqlstu);
        $valuestu = $resultstu->fetch();

        $sqlstu = 'SELECT * FROM wp_fluentform_entry_details WHERE submission_id = "'.$submission_id.'" ';
        $resultstu = $connection2->query($sqlstu);
        $dataApplicant = $resultstu->fetchAll();
        //print_r($dataApplicant);

        $student_name = '';
        $father_name = '';
        $mother_name = '';
        if(!empty($dataApplicant)){
            $len = count($dataApplicant);
            $i = 0;
            $dt = array();
            while($i<$len){
                $dt[$dataApplicant[$i]["field_name"]] = $dataApplicant[$i]["field_value"];
                $i++;
            }

            $student_name = $dt["student_name"];
            $father_name = $dt["father_name"];
            $mother_name = $dt["mother_name"];
        }

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
                    if($column_start_by == 'serial_no'){
                        $dts_receipt_feeitem[] = array(
                            "serial.all" => $cnt,
                            "particulars.all" => htmlspecialchars(trim($invData['invoice_title'])),
                            "inv_amt.all" => $vfi["amnt"],
                            "tax.all" => $taxamt,
                            "amount.all" => $vfi["tamnt"]
                        );
                    } else {
                        $dts_receipt_feeitem[] = array(
                            "particulars.all" => htmlspecialchars(trim($invData['invoice_title'])),
                            "inv_amt.all" => $vfi["amnt"],
                            "tax.all" => $taxamt,
                            "amount.all" => $vfi["tamnt"]
                        );
                    }
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
                    $taxamt = '0';
                    if(!empty($vfi["tax"])){
                        $taxamt = ($vfi["tax"] / 100) * $vfi["amount"];
                        $taxamt = number_format($taxamt, 2, '.', '');
                    }
                    if($column_start_by == 'serial_no'){
                        $dts_receipt_feeitem[] = array(
                            "serial.all" => $cnt,
                            "particulars.all" => htmlspecialchars(trim($vfi["name"])),
                            "inv_amt.all" => $vfi["amount"],
                            "tax.all" => $taxamt,
                            "amount.all" => $vfi["total_amount"]
                        );
                    } else {
                        $dts_receipt_feeitem[] = array(
                            "particulars.all" => htmlspecialchars(trim($vfi["name"])),
                            "inv_amt.all" => $vfi["amount"],
                            "tax.all" => $taxamt,
                            "amount.all" => $vfi["total_amount"]
                        );
                    }
                    $total += $vfi["total_amount"];
                    $totalTax += $taxamt;
                    $totalamtWitoutTaxDis += $vfi["amount"];
                    $cnt++;
                }
            }
        }

        
        $class_section = $valuestu["class"];
        $date = date('d-m-Y');

        if(!empty($custDataChk)){
            $coreaddress = $valuestu["correspondence_address"];
        } else {
            $coreaddress = '';
        }

        $dts_receipt = array(
            "inv_title" => htmlspecialchars($inv_title),
            "invoice_no" => $invoice_no,
            "fee_head_acc_no" => $fee_head_acc_no,
            "date" => $date,
            "student_name" => $student_name,
            "student_id" => $submission_id,
            "admission_no" => $valuestu["admission_no"],
            "father_name" => $father_name,
            "mother_name" => $mother_name,
            "program_name" => $valuestu["prog"],
            "class_name" => $valuestu["class"],
            "class_section" => $class_section,
            "total_amount" => number_format($total, 2, '.', ''),
            "inv_date" => $inv_date,
            "due_date" => $due_date,
            "address" => htmlspecialchars($coreaddress),
            "total_tax" => number_format($totalTax, 2, '.', ''),
            "inv_total" => number_format($totalamtWitoutTaxDis, 2, '.', '')
        );


        $dts = $dts_receipt;
        $fee_items = $dts_receipt_feeitem;
        // echo '<pre>';
        // print_r($dts_receipt);
        // print_r($fee_items);
        // echo '</pre>';

        
        //$file = $_SERVER["DOCUMENT_ROOT"]."/pupilsight/thirdparty/phpword/templates/invoice_template.docx";


        $phpword = new \PhpOffice\PhpWord\TemplateProcessor($file);


        $dts["total"]=$dts["total_amount"];

        for ($x = 1; $x <= 5; $x++) {
            foreach ($dts as $key => $value) {
                try {
                    if(!empty($value)){
                        $phpword->setValue($key, $value);
                    } else {
                        $phpword->setValue($key, '');
                    }
                    
                } catch (Exception $ex) {
                    //print_r($ex);
                }
            }

            if(!empty($dts["total_amount"])){
                /*$nf = new NumberFormatter("en", NumberFormatter::SPELLOUT);
                $total_in_words = $nf->format($dts["transcation_amount"]);*/
                $total_in_words=convert_number_to_words($dts["total_amount"]);
                $invoice_total_in_words = convert_number_to_words($dts["total_amount"]);
                $phpword->setValue('total_in_words', htmlspecialchars(ucwords($total_in_words)));
                $phpword->setValue('invoice_total_in_words', htmlspecialchars(ucwords($invoice_total_in_words)));
            }

            if(!empty($fee_items)){
                try {
                    if($column_start_by == 'serial_no'){
                        $phpword->cloneRowAndSetValues('serial.all', $fee_items);
                    } else {
                        $phpword->cloneRowAndSetValues('particulars.all', $fee_items);
                    }

                } catch (Exception $ex) {
                    print_r($ex);
                }
            }
        }

        try {
            $invoice_no = str_replace("/","-",$invoice_no);
            
            $fileName = $invoice_no . ".docx";
            $inFilePath = $_SERVER["DOCUMENT_ROOT"] . "/public/invoice_receipts/";
            $savedocsx = $inFilePath . $fileName;
            $phpword->saveAs($savedocsx);
            
            convert($fileName, $inFilePath, $inFilePath, TRUE, TRUE);

            $fileNameNew = $invoice_no . ".pdf";
            $savedocsx = $inFilePath . $fileNameNew;

            $contenttype = "application/force-download";
            header("Content-Type: " . $contenttype);
            header("Content-Disposition: attachment; filename=\"" . basename($fileNameNew) . "\";");
            readfile($savedocsx);

            unlink($savedocsx);
        } catch (Exception $ex) {
        }
    }

} catch (Exception $ex) {
    print_r($ex);
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

