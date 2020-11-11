<?php
/*
Pupilsight, Flexible & Open School System
*/

include '../../pupilsight.php';


$session = $container->get('session');

$cid = $_POST['cid'];
$URL = $_SESSION[$guid]['absoluteURL'] . '/index.php?q=/modules/Campaign/offline_campaignFormList.php&id=' . $cid;

if (isActionAccessible($guid, $connection2, '/modules/Campaign/fee_make_payment.php') == false) {
    $URL .= '&return=error0';
    header("Location: {$URL}");
} else {
    //Proceed!
    //Validate Inputs
    // echo '<pre>';
    // print_r($_POST);
    // echo '</pre>';
    // die();
    $counterid = $session->get('counterid');
    $invoice_id = $_POST['invoice_id'];
    $invoice_item_id = $_POST['invoice_item_id'];
    $fn_fees_invoice_id = '';
    $submission_id = $_POST['submission_id'];
    $pupilsightSchoolYearID = $_POST['pupilsightSchoolYearID'];
    $fn_fees_receipt_template_id = $_POST['fn_fees_receipt_template_id'];

    if (!empty($_POST['is_custom'])) {
        $is_custom = $_POST['is_custom'];
    } else {
        $is_custom = '';
    }

    $payment_mode_id = $_POST['payment_mode_id'];
    $bank_id = $_POST['bank_id'];
    if (!empty($bank_id)) {
        $sqlbn = 'SELECT name FROM fn_masters WHERE id = ' . $bank_id . ' ';
        $resultbn = $connection2->query($sqlbn);
        $bankNameData = $resultbn->fetch();
        $bank_name = $bankNameData['name'];
    } else {
        $bank_name = '';
    }
    $dd_cheque_no = $_POST['dd_cheque_no'];
    if (!empty($_POST['dd_cheque_date'])) {
        $fd = explode('/', $_POST['dd_cheque_date']);
        $dd_cheque_date  = date('Y-m-d', strtotime(implode('-', array_reverse($fd))));
    } else {
        $dd_cheque_date  = '';
    }
    $dd_cheque_amount = $_POST['dd_cheque_amount'];
    $payment_status = $_POST['payment_status'];
    if (!empty($_POST['payment_date'])) {
        $pd = explode('/', $_POST['payment_date']);
        $payment_date  = date('Y-m-d', strtotime(implode('-', array_reverse($pd))));
    } else {
        $payment_date  = '';
    }

    $fn_fees_head_id = $_POST['fn_fees_head_id'];
    $fn_fees_receipt_series_id = $_POST['fn_fees_receipt_series_id'];


    $transcation_amount = $_POST['transcation_amount'];
    $amount_paying = $_POST['amount_paying'];
    $total_amount_without_fine_discount = $_POST['total_amount_without_fine_discount'];
    if ($amount_paying > $transcation_amount) {
        $deposit = $amount_paying - $transcation_amount;
    } else {
        $deposit = '';
    }
    $fine = $_POST['fine'];
    $discount = $_POST['discount'];
    $remarks = $_POST['remarks'];
    $status = '1';
    $cdt = date('Y-m-d H:i:s');



    if ($submission_id == '' or $transcation_amount == '') {
        $URL .= '&return=error1';
        header("Location: {$URL}");
    } else {

        //Write to database
        try {

                $sqlrec = 'SELECT b.id, b.formatval FROM campaign AS a LEFT JOIN fn_fee_series AS b ON a.application_series_id = b.id WHERE a.id = "' . $cid . '" ';
                $resultrec = $connection2->query($sqlrec);
                $recptser = $resultrec->fetch();

                $seriesId = $recptser['id'];

                if (!empty($seriesId)) {
                    $invformat = explode('$', $recptser['formatval']);
                    $iformat = '';
                    $orderwise = 0;
                    foreach ($invformat as $inv) {
                        if ($inv == '{AB}') {
                            $datafort = array('fn_fee_series_id' => $seriesId, 'type' => 'numberwise');
                            $sqlfort = 'SELECT id, no_of_digit, last_no FROM fn_fee_series_number_format WHERE fn_fee_series_id=:fn_fee_series_id AND type=:type';
                            $resultfort = $connection2->prepare($sqlfort);
                            $resultfort->execute($datafort);
                            $formatvalues = $resultfort->fetch();

                            $str_length = $formatvalues['no_of_digit'];

                            $iformat .= str_pad($formatvalues['last_no'], $str_length, '0', STR_PAD_LEFT);

                            $lastnoadd = $formatvalues['last_no'] + 1;

                            $lastno = str_pad($lastnoadd, $str_length, '0', STR_PAD_LEFT);

                            $datafort1 = array('fn_fee_series_id' => $seriesId, 'type' => 'numberwise', 'last_no' => $lastno);
                            $sqlfort1 = 'UPDATE fn_fee_series_number_format SET last_no=:last_no WHERE fn_fee_series_id=:fn_fee_series_id AND type=:type ';
                            $resultfort1 = $connection2->prepare($sqlfort1);
                            $resultfort1->execute($datafort1);
                        } else {
                            $iformat .= $inv;
                        }
                        $orderwise++;
                    }
                    $application_id = $iformat;
                } else {
                    $application_id = '';
                }
            
            $datafort12 = array('application_id' => $application_id, 'id' => $submission_id);
            $sqlfort12 = 'UPDATE wp_fluentform_submissions SET application_id=:application_id WHERE id=:id';
            $resultfort12 = $connection2->prepare($sqlfort12);
            $resultfort12->execute($datafort12);





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
                $receipt_number = $_POST['receipt_number'];
            }

            $data = array('fn_fees_invoice_id' => $fn_fees_invoice_id, 'submission_id' => $submission_id, 'pupilsightSchoolYearID' => $pupilsightSchoolYearID, 'fn_fees_counter_id' => $counterid, 'receipt_number' => $receipt_number, 'is_custom' => $is_custom, 'payment_mode_id' => $payment_mode_id, 'bank_id' => $bank_id, 'dd_cheque_no' => $dd_cheque_no, 'dd_cheque_date' => $dd_cheque_date, 'dd_cheque_amount' => $dd_cheque_amount, 'payment_status' => $payment_status, 'payment_date' => $payment_date, 'fn_fees_head_id' => $fn_fees_head_id, 'fn_fees_receipt_series_id' => $fn_fees_receipt_series_id, 'transcation_amount' => $transcation_amount, 'total_amount_without_fine_discount' => $total_amount_without_fine_discount, 'amount_paying' => $amount_paying, 'fine' => $fine, 'discount' => $discount, 'remarks' => $remarks, 'status' => $status, 'cdt' => $cdt);

            $sql = 'INSERT INTO fn_fees_collection SET fn_fees_invoice_id=:fn_fees_invoice_id, submission_id=:submission_id, pupilsightSchoolYearID =:pupilsightSchoolYearID, fn_fees_counter_id=:fn_fees_counter_id, receipt_number=:receipt_number, is_custom=:is_custom, payment_mode_id=:payment_mode_id, bank_id=:bank_id, dd_cheque_no=:dd_cheque_no, dd_cheque_date=:dd_cheque_date, dd_cheque_amount=:dd_cheque_amount, payment_status=:payment_status, payment_date=:payment_date, fn_fees_head_id=:fn_fees_head_id, fn_fees_receipt_series_id=:fn_fees_receipt_series_id, transcation_amount=:transcation_amount, total_amount_without_fine_discount=:total_amount_without_fine_discount, amount_paying=:amount_paying, fine=:fine, discount=:discount, remarks=:remarks, status=:status,cdt=:cdt';
            $result = $connection2->prepare($sql);
            $result->execute($data);

            $collectionId = $connection2->lastInsertID();

            $rand = mt_rand(10, 99);
            $t = time();
            $transactionId = $t . $rand;

            $datau = array('transaction_id' => $transactionId,  'id' => $collectionId);
            $sqlu = 'UPDATE fn_fees_collection SET transaction_id=:transaction_id WHERE id=:id';
            $resultu = $connection2->prepare($sqlu);
            $resultu->execute($datau);



            if (!empty($invoice_item_id)) {
                $itemId = explode(', ', $invoice_item_id);
                foreach ($itemId as $itid) {
                    $dataf = array('id' => $itid, 'submission_id' => $submission_id);
                    $sqlf = 'SELECT a.fn_fee_invoice_id,b.invoice_no FROM fn_fee_invoice_item AS a LEFT JOIN fn_fee_invoice_applicant_assign AS b ON a.fn_fee_invoice_id = b.fn_fee_invoice_id WHERE a.id=:id AND b.submission_id=:submission_id';
                    $resultf = $connection2->prepare($sqlf);
                    $resultf->execute($dataf);
                    $values = $resultf->fetch();
                    $fn_fee_invoice_id = $values['fn_fee_invoice_id'];
                    $invoice_no = $values['invoice_no'];

                    $datai = array('submission_id' => $submission_id, 'transaction_id' => $transactionId,  'fn_fees_invoice_id' => $fn_fee_invoice_id, 'fn_fee_invoice_item_id' => $itid, 'invoice_no' => $invoice_no);
                    $sqli = 'INSERT INTO fn_fees_applicant_collection SET submission_id=:submission_id, transaction_id=:transaction_id, fn_fees_invoice_id=:fn_fees_invoice_id, fn_fee_invoice_item_id=:fn_fee_invoice_item_id, invoice_no=:invoice_no';
                    $resulti = $connection2->prepare($sqli);
                    $resulti->execute($datai);
                }
            }

            if (!empty($fn_fees_receipt_template_id)) {
                $sqlstu = "SELECT  a.application_id, b.name as prog,c.name as class FROM wp_fluentform_submissions AS a LEFT JOIN pupilsightProgram AS b ON a.pupilsightProgramID = b.pupilsightProgramID LEFT JOIN pupilsightYearGroup AS c ON a.pupilsightYearGroupID = c.pupilsightYearGroupID WHERE a.id = " . $submission_id . " ";
                $resultstu = $connection2->query($sqlstu);
                $valuestu = $resultstu->fetch();

                $sqlpt = "SELECT name FROM fn_masters WHERE id = " . $payment_mode_id . " ";
                $resultpt = $connection2->query($sqlpt);
                $valuept = $resultpt->fetch();

                $sqlstu = 'SELECT field_value FROM wp_fluentform_entry_details WHERE submission_id = "' . $submission_id . '" AND field_name = "student_name" ';
                $resultstu = $connection2->query($sqlstu);
                $studetails = $resultstu->fetch();

                //$class_section = $valuestu["prog"].' - '.$valuestu["class"];
                $class_section = $valuestu["class"];
                $applicationAmount = $amount_paying;
                $dts_receipt = array(
                    "application_no" => $valuestu["application_id"],
                    "receipt_no" => $receipt_number,
                    "date" => date("d-M-Y"),
                    "student_name" => $studetails['field_value'],
                    "student_id" => $submission_id,
                    "class_section" => $class_section,
                    "instrument_date" => "NA",
                    "instrument_no" => "NA",
                    "transcation_amount" => $applicationAmount,
                    "fine_amount" => $fine,
                    "other_amount" => "NA",
                    "pay_mode" => $valuept['name'],
                    "transactionId" => $transactionId,
                    "bank_name" => $bank_name
                );

                if (!empty($invoice_id)) {
                    $invid = explode(',', $invoice_id);
                    foreach ($invid as $iid) {
                        $chsql = 'SELECT b.invoice_title, b.display_fee_item FROM fn_fee_invoice AS a LEFT JOIN fn_fee_structure AS b ON a.fn_fee_structure_id = b.id WHERE a.id= ' . $iid . ' AND a.fn_fee_structure_id IS NOT NULL ';
                        $resultch = $connection2->query($chsql);
                        $valuech = $resultch->fetch();
                        if ($valuech['display_fee_item'] == '2') {
                            $sqcs = "select SUM(fi.total_amount) AS tamnt from fn_fee_invoice_item as fi, fn_fee_items as items where fi.fn_fee_item_id = items.id and fi.id in(" . $invoice_item_id . ")";
                            $resultfi = $connection2->query($sqcs);
                            $valuefi = $resultfi->fetchAll();
                            if (!empty($valuefi)) {
                                $cnt = 1;
                                foreach ($valuefi as $vfi) {
                                    $dts_receipt_feeitem[] = array(
                                        "serial.all" => $cnt,
                                        "particulars.all" => $valuech['invoice_title'],
                                        "amount.all" => $vfi["tamnt"]
                                    );
                                    $cnt++;
                                }
                            }
                        } else {
                            $sqcs = "select fi.total_amount, items.name from fn_fee_invoice_item as fi, fn_fee_items as items where fi.fn_fee_item_id = items.id and fi.id in(" . $invoice_item_id . ")";
                            $resultfi = $connection2->query($sqcs);
                            $valuefi = $resultfi->fetchAll();
                            if (!empty($valuefi)) {
                                $cnt = 1;
                                foreach ($valuefi as $vfi) {
                                    $dts_receipt_feeitem[] = array(
                                        "serial.all" => $cnt,
                                        "particulars.all" => $vfi["name"],
                                        "amount.all" => $vfi["total_amount"]
                                    );
                                    $cnt++;
                                }
                            }
                        }
                    }
                }

                $base_url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]";

                $sqlet = "SELECT b.* FROM campaign AS a LEFT JOIN pupilsightTemplate AS b ON a.email_template_id = b.pupilsightTemplateID WHERE a.id = " . $cid . " ";
                $resulte1 = $connection2->query($sqlet);
                $resultet = $resulte1->fetch();

                if(!empty($resultet)){
                    $emailSubjct_camp = $resultet['subject'];
                    $emailquote = $resultet['description'];
                } else {
                    $emailSubjct_camp = 'Application Status';
                    $emailquote = 'Your Application Submitted Successfully';
                }

                $sqlst = "SELECT b.* FROM campaign AS a LEFT JOIN pupilsightTemplate AS b ON a.sms_template_id = b.pupilsightTemplateID WHERE a.id = " . $cid . " ";
                $resultst1 = $connection2->query($sqlst);
                $resultst = $resultst1->fetch();

                if(!empty($resultst)){
                    $smsquote = $resultst['description'];
                } else {
                    $smsquote = 'Your Application Submitted Successfully';
                }

                $subid = $submission_id;
                $crtd =  date('Y-m-d H:i:s');
                
                
                //$emailAttachment = $_FILES['emailAttachment'];
                
                $crtd =  date('Y-m-d H:i:s');

                $cuid = '001';

                $sqle = "SELECT response FROM wp_fluentform_submissions WHERE id = " . $subid . " ";
                $resulte = $connection2->query($sqle);
                $rowdata = $resulte->fetch();
                $sd = json_decode($rowdata['response'], TRUE);
                $email = "";
                $names = "";
                $ft_number = '';
                $mt_number = '';
                $gt_number = '';
                $ft_email = '';
                $mt_email = '';
                $gt_email = '';

                if ($sd) {
                    // $names = implode(' ', $sd['student_name']);
                    // $email = $sd['father_email'];
                    if (!empty($sd['father_mobile'])) {
                        $ft_number = $sd['father_mobile'];
                    } 
                    if (!empty($sd['mother_mobile'])) {
                        $mt_number = $sd['mother_mobile'];
                    } 
                    if (!empty($sd['guardian_mobile'])) {
                        $gt_number = $sd['guardian_mobile'];
                    } 

                    if (!empty($sd['father_email'])) {
                        $ft_email = $sd['father_email'];
                    } 
                    if (!empty($sd['mother_email'])) {
                        $mt_email = $sd['mother_email'];
                    } 
                    if (!empty($sd['guardian_email'])) {
                        $gt_email = $sd['guardian_email'];
                    } 
                }

                //$email = "it.rakesh@gmail.com";
                $subject = nl2br($emailSubjct_camp);
                $body = nl2br($emailquote);
                $msg = $smsquote;

                $sqlm = "SELECT field_name,field_value FROM wp_fluentform_entry_details WHERE submission_id = " . $subid . " And field_name = 'numeric-field_1' ";
                $resultm = $connection2->query($sqlm);
                $rowdatm = $resultm->fetch();

                if (!empty($emailquote) && !empty($body)) {
                    
                    if(!empty($ft_email)){
                        $url = $base_url.'/cms/mailsend.php';
                        $url .= "?to=" . $ft_email;
                        $url .= "&subject=" . rawurlencode($subject);
                        $url .= "&body=" . rawurlencode($body);
                        sendEmail($ft_email, $subject, $body, $subid, $cuid, $connection2, $url);
                    }
                    if(!empty($mt_email)){
                        $url = $base_url.'/cms/mailsend.php';
                        $url .= "?to=" . $mt_email;
                        $url .= "&subject=" . rawurlencode($subject);
                        $url .= "&body=" . rawurlencode($body);
                        echo $url;
                        sendEmail($mt_email, $subject, $body, $subid, $cuid, $connection2, $url);
                    }
                    if(!empty($gt_email)){
                        $url = $base_url.'/cms/mailsend.php';
                        $url .= "?to=" . $gt_email;
                        $url .= "&subject=" . rawurlencode($subject);
                        $url .= "&body=" . rawurlencode($body);
                        sendEmail($gt_email, $subject, $body, $subid, $cuid, $connection2, $url);
                    }
                }

                if (!empty($smsquote) && !empty($msg)) {
                    if(!empty($ft_number)){
                        sendSMS($ft_number, $msg, $subid, $cuid, $connection2);
                    }
                    if(!empty($mt_number)){
                        sendSMS($mt_number, $msg, $subid, $cuid, $connection2);
                    }
                    if(!empty($gt_number)){
                        sendSMS($gt_number, $msg, $subid, $cuid, $connection2);
                    }
                }




                $_SESSION["dts_receipt_feeitem"] = $dts_receipt_feeitem;
                $_SESSION["dts_receipt"] = $dts_receipt;
                if (!empty($transactionId)) {
                    $URL .= "&tid=" . $transactionId . "&return=success0";
                } else {
                    $URL .= "&return=success0";
                }

                $_SESSION["admin_callback"] = $URL;
                if (!empty($dts_receipt) && !empty($dts_receipt_feeitem)) {
                    $callback = $_SESSION[$guid]['absoluteURL'] . '/thirdparty/phpword/fee_receipt.php?fid=' . $fn_fees_receipt_template_id;
                    header('Location: ' . $callback);
                }
            } else {
                $URL .= "&return=success0";
                header('Location: ' . $URL);
            }
        } catch (PDOException $e) {
            $URL .= '&return=error9';
            header("Location: {$URL}");
            exit();
        }

        //Last insert ID
        // $AI = str_pad($connection2->lastInsertID(), 3, '0', STR_PAD_LEFT);

        // $URL .= "&return=success0";
        // header("Location: {$URL}");
        //}
    }
}

function sendSMS($number, $msg, $subid, $cuid, $connection2){
    $urls = "https://enterprise.smsgupshup.com/GatewayAPI/rest?method=SendMessage";
    $urls .= "&send_to=" . $number;
    $urls .= "&msg=" . rawurlencode($msg);
    $urls .= "&msg_type=TEXT&userid=2000185422&auth_scheme=plain&password=StUX6pEkz&v=1.1&format=text";
    $resms = file_get_contents($urls);

    $sq = "INSERT INTO campaign_email_sms_sent_details SET  submission_id = " . $subid . ", phone=" . $number . ", description='" . stripslashes($msg) . "', pupilsightPersonID=" . $cuid . " ";
    $connection2->query($sq);
}

function sendEMail($to, $subject, $body, $subid, $cuid, $connection2, $url){
   
    //sending attachment

        $res = file_get_contents($url);
        $sq = "INSERT INTO campaign_email_sms_sent_details SET  submission_id = " . $subid . ", email='" . $to . "', subject='" . $subject . "', description='" . $body . "', pupilsightPersonID=" . $cuid . " ";
        $connection2->query($sq);
    
}
