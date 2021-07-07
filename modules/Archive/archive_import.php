<?php
/*
Pupilsight, Flexible & Open School System
 */

use Pupilsight\Domain\Helper\HelperGateway;
use Pupilsight\Domain\Archive\ArchiveGateway;

include $_SERVER["DOCUMENT_ROOT"] . '/db.php';
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

set_time_limit(0);

// for($i=0;$i<10;$i++){
//     echo $key = createSuperKey().'</br>';
// }
// resetSuperKey();
// die();

function getDomain()
{
    if (isset($_SERVER['HTTPS'])) {
        $protocol = ($_SERVER['HTTPS'] && $_SERVER['HTTPS'] != "off") ? "https" : "http";
    } else {
        $protocol = 'http';
    }
    //return $protocol . "://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
    return $protocol . "://" . $_SERVER['HTTP_HOST'];
}

function expandDirectories($base_dir)
{
    $directories = array();
    foreach (scandir($base_dir) as $file) {
        if ($file == '.' || $file == '..') continue;
        $dir = $base_dir . DIRECTORY_SEPARATOR . $file;
        if (is_dir($dir)) {
            $directories = array_merge($directories, expandDirectories($dir));
        } else {
            if (strstr($dir, ".pdf")) {
                $directories[] = $dir;
            }
        }
    }
    return $directories;
}

$baseurl = getDomain();

$accessFlag = true;
if ($accessFlag == false) {
    //Acess denied
    echo "<div class='alert alert-danger'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    $roleid = $_SESSION[$guid]["pupilsightRoleIDPrimary"];
    $page->breadcrumbs->add(__('Import Archives'));

    if ($_POST) {
        $type = $_POST['type'];
        if ($type == 1) {
            importFeeStructure($connection2, $conn);
        } else if ($type == 2) {
            importFeeTransaction($connection2, $conn);
        }
    }
?>

    <!----Report Details---->
    <div class="my-2" id='reportList'>
        <?php
        try {

            $helperGateway = $container->get(HelperGateway::class);
            $res = $helperGateway->getArchiveReport($connection2);
            $archiveGateway = $container->get(ArchiveGateway::class);
        } catch (Exception $ex) {
            echo $ex->getMessage();
        }
        ?>

        <form method="post">
            <button type="submit" href="#feeTransactions" class="btn btn-primary" name="type" value="1">Step 1 - Fee Invoice</button>
            <button type="submit" href="#feeTransactions" class="btn btn-primary" name="type" value="2">Step 2 - Fee Transaction</button>
        </form>
    </div>
<?php
}

function importFeeStructure($connection2, $conn)
{
    $type = '1';
    $archiveData = getArchiveData($connection2, $type);
    if (!empty($archiveData)) {
        $academicYearData = getAllAcademicYearID($connection2);
        $programData = getAllProgramID($connection2);
        $studentAllData = getAllStudentData($connection2);
        $classData = getAllClassID($connection2);
        $allFeeHeadData = getAllFeeHead($connection2);
        $allFineData = getAllFine($connection2);
        $allFeeItemData = getAllFeeItem($connection2);
        // echo '<pre>';
        // print_r($archiveData);
        // echo '</pre>';
        // die();
        $fn_fee_structure_id = getLastFeeStuctId($connection2);
        $invId = getLastFeeInvoiceId($connection2);

        $sqFeeStruct = "INSERT INTO fn_fee_structure (id, name, pupilsightSchoolYearID, invoice_title,  fn_fees_head_id, inv_fee_series_id, recp_fee_series_id, fn_fees_fine_rule_id, fn_fees_discount_id, due_date, cdt) values";
        $sqFeeStructItem = "INSERT INTO fn_fee_structure_item (fn_fee_structure_id, fn_fee_item_id, amount,  tax, tax_percent, total_amount) VALUES ";
        $sqFeeStAssign = "INSERT INTO fn_fees_student_assign (fn_fee_structure_id, pupilsightPersonID) VALUES ";
        $sqFeeInvoice = "INSERT INTO fn_fee_invoice (id,title, fn_fee_structure_id, pupilsightSchoolYearID, inv_fn_fee_series_id, rec_fn_fee_series_id, fn_fees_head_id, fn_fees_fine_rule_id, fn_fees_discount_id, due_date, cdt) VALUES";
        $sqlFeeInvoiceItem = "INSERT INTO fn_fee_invoice_item (fn_fee_invoice_id, fn_fee_item_id, amount,  tax, discount, total_amount) VALUES";
        $sqlFeeInvoiceClassAssign = "INSERT INTO fn_fee_invoice_class_assign (fn_fee_invoice_id, pupilsightProgramID, pupilsightYearGroupID) VALUES";
        $sqFeeInvoiceStAssign = "INSERT INTO fn_fee_invoice_student_assign (fn_fee_invoice_id, fn_fee_structure_id, pupilsightPersonID, invoice_no, invoice_status) VALUES";
        $sqArchiveFeeInvoices = "";

        $sqAppendFlag = false;
        $feeItem = array();
        $fn_fees_fine_rule_id = 0;
        foreach ($archiveData as $arData) {

            $invoice_status = trim($arData['invoice_status']);
            if ($invoice_status != 'CANCELED') {
                $pupilsightPersonID = array_search($arData['StudentID'], $studentAllData);
                if (!empty($pupilsightPersonID)) {
                    $pupilsightSchoolYearID = array_search($arData['AcademicYear'], $academicYearData);
                    $pupilsightProgramID = array_search($arData['Program'], $programData);
                    $pupilsightYearGroupID = getClassID($classData, $pupilsightSchoolYearID, $arData['Term']);
                    $feeHeadData = getFeeHead($allFeeHeadData, $pupilsightSchoolYearID, $arData['Account_Head']);
                    if (!empty($arData['fine_name'])) {
                        $fn_fees_fine_rule_id = getFineData($allFineData, $arData['fine_name']);
                    }

                    $invoice_no = $arData['invoice_no'];
                    //echo $invoice_no.'--'.$pupilsightSchoolYearID.'--'.$pupilsightProgramID.'--'.$pupilsightYearGroupID.'<br>';
                    if (!empty($invoice_no) && !empty($pupilsightSchoolYearID) && !empty($pupilsightProgramID) && !empty($pupilsightYearGroupID) && !empty($feeHeadData)) {
                        
                        $fn_fee_structure_id++;
                        $invoice_title = $arData['invoice_title'];
                        $final_amount = $arData['final_amount'];
                        $amount = $arData['amount'];
                        $tax = $arData['tax'];
                        $invoice_generated_date = $arData['invoice_generated_date'];
                        $amount_paid = $arData['amount_paid'];
                        $amount_pending = $arData['amount_pending'];
                        $due_date = date('Y-m-d', strtotime($arData['due_date']));
                        $fine_name = $arData['fine_name'];
                        $fn_fees_head_id = $feeHeadData['id'];
                        $inv_fee_series_id = $feeHeadData['inv_fee_series_id'];
                        $recp_fee_series_id = $feeHeadData['recp_fee_series_id'];
                        $fn_fees_discount_id = 0;
                        $cdt = date('Y-m-d h:i:s', strtotime($arData['invoice_generated_date']));
                        if ($invoice_status == 'NOT_PAID') {
                            $invoiceStatus = 'Not Paid';
                        } else if ($invoice_status == 'PARTIALY_PAID') {
                            $invoiceStatus = 'Partial Paid';
                        } else if ($invoice_status == 'FULLY_PAID') {
                            $invoiceStatus = 'Fully Paid';
                        }

                        $feeItem[0]['invoice_no']    = $arData['invoice_no'];
                        $feeItem[0]['fee_item_name'] = $arData['fee_item_name'];
                        $feeItem[0]['fee_item_amount'] = $arData['fee_item_amount'];
                        $feeItem[0]['fee_item_discount'] = $arData['fee_item_discount'];
                        $feeItem[0]['FeeItemAmountPaid'] = $arData['FeeItemAmountPaid'];
                        $feeItem[0]['item_amount_discounted'] = $arData['item_amount_discounted'];
                        $feeItem[0]['FeeItemAmountPending'] = $arData['FeeItemAmountPending'];
                        $feeItem[0]['invoice_item_status'] = $arData['invoice_item_status'];
                        $feeItem[0]['fee_item_tax'] = $arData['fee_item_tax'];
                        $feeItem[0]['FeeItemFinalAmount'] = $arData['FeeItemFinalAmount'];
                        $feeItem[0]['FeeItemOrder'] = $arData['FeeItemOrder'];

                        if (!empty($arData['feeItem'])) {
                            $feeitems = array_merge($feeItem, $arData['feeItem']);
                        } else {
                            $feeitems = $feeItem;
                        }

                        //Fee Structure Insert Start Here
                        try {
                            if ($sqAppendFlag) {
                                $sqFeeStruct .= ",";
                            }

                            $sqFeeStruct .= "('" . $fn_fee_structure_id . "','" . $invoice_title . "','" . $pupilsightSchoolYearID . "','" . $invoice_title . "','" . $fn_fees_head_id . "','" . $inv_fee_series_id . "','" . $recp_fee_series_id . "','" . $fn_fees_fine_rule_id . "','" . $fn_fees_discount_id . "','" . $due_date . "','" . $cdt . "')";


                            if (!empty($feeitems) && !empty($fn_fee_structure_id)) {
                                foreach ($feeitems as $feeitem) {
                                    $fn_fee_item_id = getFeeItemData($allFeeItemData, $pupilsightSchoolYearID, $feeitem['fee_item_name']);
                                    if (!empty($fn_fee_item_id)) {
                                        $fn_fee_item_id = $fn_fee_item_id;
                                    } else {
                                        $fn_fee_item_id = 0;
                                    }
                                    $amt = $feeitem['fee_item_amount'];
                                    $taxdata = 'N';
                                    $taxpr = $feeitem['fee_item_tax'];
                                    $total_amount = $feeitem['FeeItemFinalAmount'];


                                    if ($sqAppendFlag) {
                                        $sqFeeStructItem .= ",";
                                    }
                                    $sqFeeStructItem .= "('" . $fn_fee_structure_id . "','" . $fn_fee_item_id . "','" . $amt . "','" . $taxdata . "','" . $taxpr . "','" . $total_amount . "')";
                                }

                                if ($sqAppendFlag) {
                                    $sqFeeStAssign .= ",";
                                }
                                $sqFeeStAssign .= "(" . $fn_fee_structure_id . "," . $pupilsightPersonID . ")";
                            }
                        } catch (Exception $ex) {
                            echo $ex->getMessage();
                        }
                        //Fee Structure Insert End Here

                        //Invoice Insert Start Here
                        try {
                            if ($sqAppendFlag) {
                                $sqFeeInvoice .= ",";
                            }
                            $invId++;
                            $sqFeeInvoice .= "('" . $invId . "','" . $invoice_title . "','" . $fn_fee_structure_id . "','" . $pupilsightSchoolYearID . "','" . $inv_fee_series_id . "','" . $recp_fee_series_id . "','" . $fn_fees_head_id . "','" . $fn_fees_fine_rule_id . "','" . $fn_fees_discount_id . "','" . $due_date . "','" . $cdt . "')";

                            if (!empty($feeitems) && !empty($invId)) {
                                foreach ($feeitems as $feeitem) {
                                    $fn_fee_item_id = getFeeItemData($allFeeItemData, $pupilsightSchoolYearID, $feeitem['fee_item_name']);
                                    if (!empty($fn_fee_item_id)) {
                                        $fn_fee_item_id = $fn_fee_item_id;
                                    } else {
                                        $fn_fee_item_id = 0;
                                    }
                                    $amt = $feeitem['fee_item_amount'];
                                    $taxdata = 'N';
                                    $taxpr = $feeitem['fee_item_tax'];

                                    $item_amount_discounted = $feeitem['item_amount_discounted'];
                                    if (!empty($item_amount_discounted)) {
                                        $total_amount = $feeitem['FeeItemFinalAmount'] - $item_amount_discounted;
                                    } else {
                                        $total_amount = $feeitem['FeeItemFinalAmount'];
                                    }

                                    $fee_item_discount = $feeitem['fee_item_discount'];
                                    if (!empty($fee_item_discount)) {
                                        //$item_amount_discounted = $fee_item_discount;
                                        $total_amount = '-' . $fee_item_discount;
                                    }

                                    if ($sqAppendFlag) {
                                        $sqlFeeInvoiceItem .= ",";
                                    }
                                    $sqlFeeInvoiceItem .= "(" . $invId . "," . $fn_fee_item_id . "," . $amt . "," . $taxpr . "," . $fee_item_discount . "," . $total_amount . ")";
                                    // $sqlFeeInvoiceItem .= "(" . $invId . "," . $fn_fee_item_id . "," . $amt . "," . $taxpr . "," . $item_amount_discounted . "," . $total_amount . ")";
                                }


                                if ($sqAppendFlag) {
                                    $sqlFeeInvoiceClassAssign .= ",";
                                }
                                $sqlFeeInvoiceClassAssign .= "(" . $invId . "," . $pupilsightProgramID . "," . $pupilsightYearGroupID . ")";

                                if ($sqAppendFlag) {
                                    $sqFeeInvoiceStAssign .= ",";
                                }
                                $sqFeeInvoiceStAssign .= "('" . $invId . "','" . $fn_fee_structure_id . "','" . $pupilsightPersonID . "','" . $invoice_no . "','" . $invoiceStatus . "')";
                            }
                        } catch (Exception $ex) {
                            echo $ex->getMessage();
                        }
                        //Invoice Insert End Here

                        //Update Archive Invoice Start Here
                        // try {

                        //     $sqArchiveFeeInvoices .= "UPDATE archive_feeInvoices SET structure_status= '1', invoice_stauts= '1' WHERE invoice_no= '" . $invoice_no . "'; ";
                        // } catch (Exception $ex) {
                        //     echo $ex->getMessage();
                        // }
                        //Update Archive Invoice End Here
                        //die();

                        // echo '<pre>';
                        // print_r($feeitems);
                        // echo '</pre>';
                        $sqAppendFlag = TRUE;
                    }
                }
            }
        }

        // echo $sqFeeStruct;
        // echo "<hr>\n<br>";
        // echo $sqFeeStructItem;
        // echo "<hr>\n<br>";
        // echo $sqFeeStAssign;
        // echo "<hr>\n<br>";
        // echo $sqFeeInvoice;
        // echo "<hr>\n<br>";
        // echo $sqlFeeInvoiceItem;
        // echo "<hr>\n<br>";
        //  echo $sqlFeeInvoiceClassAssign;
        //  echo "<hr>\n<br>";
        // echo $sqFeeInvoiceStAssign;
        // echo "<hr>\n<br>";
        // echo $sqArchiveFeeInvoices;
        // echo "<hr>\n<br>";

        try {
            $conn->autocommit(FALSE);
            $conn->query($sqFeeStruct);
            $conn->query($sqFeeStructItem);
            $conn->query($sqFeeStAssign);
            $conn->query($sqFeeInvoice);
            $conn->query($sqlFeeInvoiceItem);
            $conn->query($sqlFeeInvoiceClassAssign);
            $conn->query($sqFeeInvoiceStAssign);
            //$conn->query($sqArchiveFeeInvoices);
            $sqArchiveFeeInvoices = "UPDATE archive_feeInvoices SET structure_status= '1', invoice_stauts= '1'";
            $conn->query($sqArchiveFeeInvoices);
            $conn->commit();
        } catch (Exception $ex) {
            $conn->rollback();
        }

    }
}

function importFeeTransaction($connection2, $conn)
{
    $type = '1';
    $archiveData = getArchiveCollectionData($connection2);
    if (!empty($archiveData)) {
        // echo '<pre>';
        // print_r($archiveData);
        // echo '</pre>';
        // die();
        $academicYearData = getAllAcademicYearID($connection2);
        $payModeData = getAllPaymentMode($connection2);
        $studentAllData = getAllStudentData($connection2);
        $allFeeItemData = getAllFeeItem($connection2);
        //$allInvoiceData = getAllInvoiceData($connection2);
        $allInvoiceItemData = getAllInvoiceItemData($connection2);



        $sqFeeCollection = "INSERT INTO fn_fees_collection (transaction_id,fn_fees_invoice_id, pupilsightPersonID, pupilsightSchoolYearID, receipt_number, payment_mode_id, bank_id, dd_cheque_no, dd_cheque_date, payment_status, payment_date, fn_fees_head_id, fn_fees_receipt_series_id, transcation_amount, total_amount_without_fine_discount, amount_paying, over_payment, fine, discount, remarks, cdt,instrument_no,instrument_date,invoice_status) values";

        $sqFeeStuCollection = "INSERT INTO fn_fees_student_collection (pupilsightPersonID, transaction_id, fn_fees_invoice_id, fn_fee_invoice_item_id, invoice_no, total_amount, discount, total_amount_collection, status) VALUES ";

        $sqArchiveFeeInvoices = "";


        $feeItem = array();
        $fn_fees_fine_rule_id = 0;
        $payment_mode_id = 0;
        $bank_id = 0;
        $amount_paying = 0;
        $transcation_amount = 0;
        $sqAppendFlag = false;
        $arData = array();
        foreach ($archiveData as $tkey => $arDataAll) {
            $transaction_id = $tkey;
            $invIds = array();
            $invoiceStatus = array();
            $final_total_amount = 0;
            $final_total_amount_paid = 0;
            $total_discount = 0;
            $k = 1;
            foreach ($arDataAll as $ikey => $arDataInv) {
                $invoice_no = $ikey;
                $arData = $arDataInv[0];
                /*
                echo '<pre>';
                print_r($arData);
                echo '</pre>';
                die();
                */
                $invoice_status = trim($arData['invoice_status']);
                if ($invoice_status != 'CANCELED') {

                    $pupilsightPersonID = array_search($arData['StudentID'], $studentAllData);
                    // echo $pupilsightPersonID.'<br>';
                    if (!empty($pupilsightPersonID)) {
                        $pupilsightSchoolYearID = array_search($arData['AcademicYear'], $academicYearData);
                        $payment_mode_id = getPaymentModeData($payModeData, $arData['payment_mode']);
                        $bank_id = getPaymentModeData($payModeData, $arData['bank_name']);
                        //$invoice_no = $arData['invoice_no'];
                        //echo $invoice_no.'--'.$pupilsightSchoolYearID.'<br>';
                        if (!empty($invoice_no) && !empty($pupilsightSchoolYearID)) {
                            //$fn_fee_structure_id++;
                            //$transaction_id = $arData['transaction_id'];
                            $receipt_number = $arData['receipt_no'];
                            $instrument_amount = $arData['instrument_amount'];
                            $dd_cheque_no = $arData['instrument_no'];
                            $instrument_no = $arData['instrument_no'];
                            if ($k == 1) {
                                $instrument_date = '';
                                $dd_cheque_date = '';
                                if (!empty($arData['instrument_date'])) {
                                    $instrument_date = date('Y-m-d', strtotime($arData['instrument_date']));
                                    $dd_cheque_date = date('Y-m-d', strtotime($arData['instrument_date']));
                                }
                            }
                            $payment_status = $arData['payment_status'];

                            $amount_paying = $arData['transaction_amount'];
                            $payment_date = "";
                            $cdt = "";
                            $tmDate = $arData['payment_received_date'];
                            if(!empty($arData['payment_received_date'])){
                                $tmv = $tmDate[0] . $tmDate[1];
                            } else {
                                $tmv = '';
                            }
                            

                            if (!empty($tmDate)) {
                                if ($tmv != "00") {
                                    $payment_date = date('Y-m-d', strtotime($tmDate));
                                    $cdt = date('Y-m-d h:i:s', strtotime($tmDate));
                                }
                            }

                            // $dd_cheque_date = '';
                            // if(!empty($arData['cheque_received_date'])){
                            //     $dd_cheque_date = date('Y-m-d', strtotime($arData['cheque_received_date']));
                            // }
                            $remarks = $arData['remarks'];
                            $manual_receipt_number = $arData['manual_receipt_number'];
                            if (!empty($manual_receipt_number)) {
                                $receipt_number = $manual_receipt_number;
                            }
                            $fine = $arData['total_fine_amount'];
                            $over_payment = $arData['overpayment_amount'];
                            // $discount = 0;
                            // if(!empty(trim($arData['discount_amount']))){
                            //     $discount = $arData['discount_amount'];
                            // }

                            //$total_amount_without_fine_discount = $transcation_amount - ($fine + $discount);

                            $fn_fees_discount_id = 0;

                            //Fee Collection Insert Start Here
                            try {

                                if (!empty($arDataInv) && !empty($transaction_id)) {
                                    $stChk = 0;
                                    $discount_amount = 0;
                                    $discount = 0;
                                    foreach ($arDataInv as $feeitem) {
                                        $invoice_amount = $feeitem['invoice_amount'];
                                        $is_discount_trans = $feeitem['is_discount_trans'];

                                        if (!empty(trim($feeitem['discount_amount']))) {
                                            $total_discount += $feeitem['discount_amount'];
                                        }

                                        if ($is_discount_trans == 'Y' && !empty($invoice_amount)) {
                                            $discount_amount += $feeitem['discount_amount'];
                                        } else {


                                            if (!empty(trim($discount_amount)) && !empty($invoice_amount)) {
                                                //echo $discount_amount.'<br>';
                                                $discount += $discount_amount;
                                                $discount_amount = 0;
                                            } else {
                                                $discount = $feeitem['discount_amount'];
                                            }

                                            // $invoiceData = getInvoiceData($allInvoiceData, $feeitem['invoice_no']);
                                            $invoiceData = getInvoiceDataByInvNo($connection2, $feeitem['invoice_no']);
                                            $fn_fees_invoice_id = $invoiceData['id'];
                                            if (!empty($fn_fees_invoice_id)) {
                                                $invIds[] = $invoiceData['id'];
                                                $fn_fees_head_id = $invoiceData['fn_fees_head_id'];
                                                $fn_fees_receipt_series_id = $invoiceData['rec_fn_fee_series_id'];

                                                $inv_status = trim($feeitem['invoice_status']);
                                                if ($inv_status == 'NOT_PAID') {
                                                    $invoiceStatus[] = 'Not Paid';
                                                    $status = 0;
                                                } else if ($inv_status == 'PARTIALY_PAID') {
                                                    $stChk = 1;
                                                    $invoiceStatus[] = 'Partial Paid';
                                                    $status = 2;
                                                } else if ($inv_status == 'FULLY_PAID') {
                                                    $invoiceStatus[] = 'Fully Paid';
                                                    $status = 1;
                                                }


                                                $fn_fee_item_id = getFeeItemData($allFeeItemData, $pupilsightSchoolYearID, $feeitem['fee_item_name']);
                                                if (!empty($fn_fee_item_id)) {
                                                    $fn_fee_item_id = $fn_fee_item_id;
                                                } else {
                                                    $fn_fee_item_id = 0;
                                                }

                                                $fn_fee_invoice_item_id = getInvoiceItemData($allInvoiceItemData, $fn_fees_invoice_id, $fn_fee_item_id);




                                                $total_amount = 00;
                                                $total_amount_collection = 00;
                                                if (!empty($feeitem['FeeItemAmount'])) {
                                                    $final_total_amount += $feeitem['FeeItemAmount'];
                                                    $total_amount = $feeitem['FeeItemAmount'];
                                                }
                                                if (!empty($feeitem['FeeItemAmountPaid'])) {
                                                    $final_total_amount_paid += $feeitem['FeeItemAmountPaid'];
                                                    $total_amount_collection = $feeitem['FeeItemAmountPaid'];
                                                }
                                                $discount = '';
                                                if (!empty($discount)) {
                                                    $discount = $discount;
                                                } 

                                                
                                                $sqFeeStuCollection .= "(
                                                    '" . $pupilsightPersonID . "',
                                                    '" . $transaction_id . "',
                                                    '" . $fn_fees_invoice_id . "',
                                                    '" . $fn_fee_invoice_item_id . "',
                                                    '" . $invoice_no . "',
                                                    '" . $total_amount . "',";
                                                    if (empty($discount)) {
                                                        $sqFeeStuCollection .= "NULL";
                                                    } else {
                                                        $sqFeeStuCollection .= "'" . $discount . "'";
                                                    }
                                                $sqFeeStuCollection .= ",'" . $total_amount_collection . "',
                                                    '" . $status . "'),";
                                            }
                                        }
                                    }
                                }
                            } catch (Exception $ex) {
                                echo $ex->getMessage();
                            }
                        }
                    }
                }
                $k++;
            }
            if (!empty($pupilsightPersonID)) {
                try {

                    array_unique($invoiceStatus);
                    $ids = array_unique($invIds);
                    // echo $final_total_amount;
                    // echo '<pre>';
                    // print_r($ids);
                    $fn_fees_invoice_ids = implode(',', $ids);
                    if (in_array("Partial Paid", $invoiceStatus)) {
                        $invoiceStatusNew = 'Partial Paid';
                    } else {
                        $invoiceStatusNew = 'Fully Paid';
                    }

                    if (!empty($fine)) {
                        $fine = $fine;
                    } else {
                        $fine = "0.00";
                    }

                    if (!empty($total_discount)) {
                        $total_discount = $total_discount;
                    } else {
                        $total_discount = "0.00";
                    }

                    if (!empty($over_payment)) {
                        $over_payment = $over_payment;
                    } else {
                        $over_payment = "0.00";
                    }

                    //$transcation_amount = ($final_total_amount + $fine) - $total_discount;
                    $total_amount_without_fine_discount = $final_total_amount - ($fine + $total_discount);
                    $amount_paying = $final_total_amount_paid;
                    $transcation_amount = $final_total_amount_paid;

                    if (!empty($fn_fees_invoice_ids)) {
                        $sqFeeCollection .= "(
                            '" . $transaction_id . "',
                            '" . $fn_fees_invoice_ids . "',
                            '" . $pupilsightPersonID . "',
                            '" . $pupilsightSchoolYearID . "',
                            '" . $receipt_number . "',
                            '" . $payment_mode_id . "',
                            '" . $bank_id . "',
                            '" . $dd_cheque_no . "',";
                        if (empty($dd_cheque_date)) {
                            $sqFeeCollection .= "NULL";
                        } else {
                            $sqFeeCollection .= "'" . $dd_cheque_date . "'";
                        }
                        $sqFeeCollection .= ",'" . $payment_status . "',";
                        if (empty($payment_date)) {
                            $sqFeeCollection .= "NULL";
                        } else {
                            $sqFeeCollection .= "'" . $payment_date . "'";
                        }

                        $sqFeeCollection .= ",'" . $fn_fees_head_id . "',
                            '" . $fn_fees_receipt_series_id . "',
                            '" . $transcation_amount . "',
                            '" . $total_amount_without_fine_discount . "',
                            '" . $amount_paying . "',
                            '" . $over_payment . "',
                            '" . $fine . "',
                            '" . $total_discount . "',
                            '" . $remarks . "',";
                        if (empty($payment_date)) {
                            $sqFeeCollection .= "NULL";
                        } else {
                            $sqFeeCollection .= "'" . $cdt . "'";
                        }
                        $sqFeeCollection .= ",'" . $instrument_no . "', ";
                        if (empty($instrument_date)) {
                            $sqFeeCollection .= "NULL";
                        } else {
                            $sqFeeCollection .= "'" . $instrument_date . "'";
                        }
                        $sqFeeCollection .= ",'" . $invoiceStatusNew . "'),";
                    }
                } catch (Exception $ex) {
                    echo $ex->getMessage();
                }
            }
            $sqAppendFlag = TRUE;
            //echo $sqFeeStuCollection;
            //echo "<hr>\n<br>";
            //echo $sqFeeCollection;
            //echo "<hr>\n<br>";
            //die();
        }

        $sqFeeCollection = rtrim($sqFeeCollection, ", ");
        $sqFeeStuCollection = rtrim($sqFeeStuCollection, ", ");

        // echo $sqFeeCollection;
        // echo "<hr>\n<br>";

        // echo $sqFeeStuCollection;
        // echo "<hr>\n<br>";

        // echo $sqFeeStAssign;
        // echo "<hr>\n<br>";
        // echo $sqFeeInvoice;
        // echo "<hr>\n<br>";
        // echo $sqlFeeInvoiceItem;
        // echo "<hr>\n<br>";
        // echo $sqlFeeInvoiceClassAssign;
        // echo "<hr>\n<br>";
        // echo $sqFeeInvoiceStAssign;
        // echo "<hr>\n<br>";
        // echo $sqArchiveFeeInvoices;
        // echo "<hr>\n<br>";

        try {

            $conn->autocommit(FALSE);
            $conn->query($sqFeeCollection);
            $conn->query($sqFeeStuCollection);
            $conn->commit();
        } catch (Exception $ex) {
            echo "RollBack: " . $ex->getMessage();
            $conn->rollback();
        }
    }
}

function getLastFeeCollectionId($connection2)
{
    $sq = "SELECT id FROM fn_fees_collection ORDER BY id DESC LIMIT 1";
    $result = $connection2->query($sq);
    $res = $result->fetch();
    return $res["id"];
}

function getLastFeeStuctId($connection2)
{
    $sq = "SELECT id FROM fn_fee_structure ORDER BY id DESC LIMIT 1";
    $result = $connection2->query($sq);
    $res = $result->fetch();
    return $res["id"];
}

function getLastFeeInvoiceId($connection2)
{
    $sq = "SELECT id FROM fn_fee_invoice ORDER BY id DESC LIMIT 1";
    $result = $connection2->query($sq);
    $res = $result->fetch();
    return $res["id"];
}

function importFeeInvoice($connection2)
{
    $type = '2';
    $archiveData = getArchiveData($connection2, $type);
}

function getArchiveData($connection2, $type)
{
    // $sql = 'SELECT * FROM archive_feeInvoices WHERE invoice_status != "FULLY_PAID" ';
    $sql = 'SELECT * FROM archive_feeInvoices WHERE invoice_status != "CANCELED"';
    if ($type == 1) {
        $sql .= ' AND structure_status = "0" ';
    } else if ($type == 2) {
        $sql .= ' AND invoice_stauts = "0" ';
    } else if ($type == 3) {
        $sql .= ' AND transaction_status = "0" ';
    }
    //echo $sql;
    $result = $connection2->query($sql);
    $archiveData = $result->fetchAll();
    if (!empty($archiveData)) {
        $i = 1;
        $invoiceData = array();
        $j = 0;
        $key = '';
        foreach ($archiveData as $k => $ad) {
            $FeeItemOrder = $ad['FeeItemOrder'];
            $feeOrder = $FeeItemOrder;
            if ($feeOrder == 1) {
                $invoice_no = $ad['invoice_no'];
                $key = $k;
            }

            if ($invoice_no == $ad['invoice_no'] && $feeOrder != '1') {
                $invoiceData[$key]['feeItem'][$j] = $ad;
                $j++;
            } else {
                $invoiceData[$key] = $ad;
                $j = 0;
            }
            $i++;
        }
    }

    return array_values($invoiceData);
}

function getArchiveCollectionData($connection2)
{
    //$sql = 'SELECT * FROM archive_fee_transactions_backup where transaction_id=89339138720';
    $sql = 'SELECT * FROM archive_fee_transactions_backup WHERE invoice_status != "CANCELED" ';
    $result = $connection2->query($sql);
    $archiveData = $result->fetchAll();
    if (!empty($archiveData)) {
        $trans = array();
        $i = 0;
        foreach ($archiveData as $k => $ad) {
            $transID = $ad['transaction_id'];
            $invoiceNo = $ad['invoice_no'];
            $trans[$transID][$invoiceNo][] = $ad;
        }
    }

    return $trans;
}

function getAllAcademicYearID($connection2)
{
    $sql = 'SELECT pupilsightSchoolYearID, name FROM pupilsightSchoolYear  ';
    $result = $connection2->query($sql);
    $academicData = $result->fetchAll();
    if (!empty($academicData)) {
        $academic = array();
        foreach ($academicData as $ad) {
            $academic[$ad['pupilsightSchoolYearID']] = $ad['name'];
        }
    }
    return $academic;
}

function getAllFeeHead($connection2)
{
    $sql = 'SELECT * FROM fn_fees_head';
    $result = $connection2->query($sql);
    $feeHeadData = $result->fetchAll();
    return $feeHeadData;
}

function getFeeHead($allFeeHeadData, $pupilsightSchoolYearID, $feeHead)
{
    $feeHeadData = array();
    if (!empty($allFeeHeadData)) {
        foreach ($allFeeHeadData as $cd) {
            if ($cd['pupilsightSchoolYearID'] == $pupilsightSchoolYearID && $cd['name'] == trim($feeHead)) {
                $feeHeadData = $cd;
            }
        }
    }
    return $feeHeadData;
}

function getAllProgramID($connection2)
{
    $sql = 'SELECT pupilsightProgramID, name FROM pupilsightProgram  ';
    $result = $connection2->query($sql);
    $programData = $result->fetchAll();
    if (!empty($programData)) {
        $program = array();
        foreach ($programData as $ad) {
            $program[$ad['pupilsightProgramID']] = $ad['name'];
        }
    }
    return $program;
}

function getAllClassID($connection2)
{
    $sql = 'SELECT pupilsightSchoolYearID, pupilsightYearGroupID, name FROM pupilsightYearGroup  ';
    $result = $connection2->query($sql);
    $classData = $result->fetchAll();
    return $classData;
}

function getClassID($classData, $pupilsightSchoolYearID, $className)
{
    $pupilsightYearGroupID = '';
    if (!empty($classData)) {
        foreach ($classData as $cd) {
            if ($cd['pupilsightSchoolYearID'] == $pupilsightSchoolYearID && $cd['name'] == trim($className)) {
                $pupilsightYearGroupID = $cd['pupilsightYearGroupID'];
            }
        }
    }
    return $pupilsightYearGroupID;
}

function getAllStudentData($connection2)
{
    $sql = 'SELECT pupilsightPersonID, old_pupilpod_id  FROM pupilsightPerson WHERE pupilsightRoleIDPrimary = "003" AND old_pupilpod_id != "" ';
    $result = $connection2->query($sql);
    $studentData = $result->fetchAll();
    if (!empty($studentData)) {
        $students = array();
        foreach ($studentData as $ad) {
            $students[$ad['pupilsightPersonID']] = $ad['old_pupilpod_id'];
        }
    }
    return $students;
}

function getAllFine($connection2)
{
    $sql = 'SELECT * FROM fn_fees_fine_rule';
    $result = $connection2->query($sql);
    $fineData = $result->fetchAll();
    return $fineData;
}

function getFineData($allFineData, $fine_name)
{
    $fineData = array();
    if (!empty($allFineData)) {
        foreach ($allFineData as $cd) {
            if ($cd['name'] == trim($fine_name)) {
                $fineId = $cd['id'];
            }
        }
    }
    return $fineId;
}

function getAllFeeItem($connection2)
{
    $sql = 'SELECT * FROM fn_fee_items';
    $result = $connection2->query($sql);
    $fineData = $result->fetchAll();
    return $fineData;
}

function getFeeItemData($allFeeItemData, $pupilsightSchoolYearID, $fee_item_name)
{
    $fee_item_id = "";
    if (!empty($allFeeItemData)) {
        foreach ($allFeeItemData as $cd) {
            // if($cd['pupilsightSchoolYearID'] == $pupilsightSchoolYearID && $cd['name'] == trim($fee_item_name)){
            if ($cd['name'] == trim($fee_item_name)) {
                $fee_item_id = $cd['id'];
            }
        }
    }
    return $fee_item_id;
}

function getAllPaymentMode($connection2)
{
    $sql = 'SELECT * FROM fn_masters';
    $result = $connection2->query($sql);
    $payModeData = $result->fetchAll();
    return $payModeData;
}

function getPaymentModeData($allpayModeData, $name)
{
    $id = 0;
    if (!empty($allpayModeData)) {
        foreach ($allpayModeData as $cd) {
            if ($cd['name'] == trim($name)) {
                $id = $cd['id'];
            }
        }
    }
    return $id;
}

function getAllInvoiceData($connection2)
{
    $sql = 'SELECT a.id,a.fn_fees_head_id,a.rec_fn_fee_series_id, b.invoice_no FROM fn_fee_invoice AS a LEFT JOIN fn_fee_invoice_student_assign AS b ON a.id = b.fn_fee_invoice_id';
    $result = $connection2->query($sql);
    $invoiceData = $result->fetchAll();
    return $invoiceData;
}

function getInvoiceData($allInvoiceData, $invoice_no)
{
    $invoiceData = array();
    if (!empty($allInvoiceData)) {
        foreach ($allInvoiceData as $cd) {
            if ($cd['invoice_no'] == trim($invoice_no)) {
                $invoiceData = $cd;
            }
        }
    }
    return $invoiceData;
}

function getInvoiceDataByInvNo($connection2, $invoice_no)
{
    $sql = 'SELECT a.id,a.fn_fees_head_id,a.rec_fn_fee_series_id, b.invoice_no FROM fn_fee_invoice AS a LEFT JOIN fn_fee_invoice_student_assign AS b ON a.id = b.fn_fee_invoice_id WHERE b.invoice_no = "' . $invoice_no . '" ';
    $result = $connection2->query($sql);
    $invoiceData = $result->fetch();
    return $invoiceData;
}

function getAllInvoiceItemData($connection2)
{
    $sql = 'SELECT id,fn_fee_invoice_id,fn_fee_item_id FROM fn_fee_invoice_item';
    $result = $connection2->query($sql);
    $invItemData = $result->fetchAll();
    return $invItemData;
}

function getInvoiceItemData($allInvoiceItemData, $fn_fees_invoice_id, $fn_fee_item_id)
{
    $id = 0;
    if (!empty($allInvoiceItemData)) {
        foreach ($allInvoiceItemData as $cd) {
            if ($cd['fn_fee_invoice_id'] == $fn_fees_invoice_id && $cd['fn_fee_item_id'] == $fn_fee_item_id) {
                $id = $cd['id'];
            }
        }
    }
    return $id;
}
