<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Contracts\Comms\Mailer;

include '../../pupilsight.php';

//Module includes
include './moduleFunctions.php';

$pupilsightSchoolYearID = $_GET['pupilsightSchoolYearID'];
$pupilsightFinanceInvoiceID = $_POST['pupilsightFinanceInvoiceID'];
$status = $_GET['status'];
$pupilsightFinanceInvoiceeID = $_GET['pupilsightFinanceInvoiceeID'];
$monthOfIssue = $_GET['monthOfIssue'];
$pupilsightFinanceBillingScheduleID = $_GET['pupilsightFinanceBillingScheduleID'];
$pupilsightFinanceFeeCategoryID = $_GET['pupilsightFinanceFeeCategoryID'];

if ($pupilsightFinanceInvoiceID == '' or $pupilsightSchoolYearID == '') { echo 'Fatal error loading this page!';
} else {
    $URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_POST['address'])."/invoices_manage_edit.php&pupilsightFinanceInvoiceID=$pupilsightFinanceInvoiceID&pupilsightSchoolYearID=$pupilsightSchoolYearID&status=$status&pupilsightFinanceInvoiceeID=$pupilsightFinanceInvoiceeID&monthOfIssue=$monthOfIssue&pupilsightFinanceBillingScheduleID=$pupilsightFinanceBillingScheduleID&pupilsightFinanceFeeCategoryID=$pupilsightFinanceFeeCategoryID";

    if (isActionAccessible($guid, $connection2, '/modules/Finance/invoices_manage_edit.php') == false) {
        $URL .= '&return=error0';
        header("Location: {$URL}");
    } else {
        //Proceed!
        //Check if person specified
        if ($pupilsightFinanceInvoiceID == '') {
            $URL .= '&return=error1';
            header("Location: {$URL}");
        } else {
            //LOCK INVOICE TABLES
            try {
                $data = array();
                $sql = 'LOCK TABLES pupilsightFinanceInvoice WRITE, pupilsightFinanceInvoiceFee WRITE, pupilsightFinanceInvoicee WRITE, pupilsightPayment READ';
                $result = $connection2->prepare($sql);
                $result->execute($data);
            } catch (PDOException $e) {
                $URL .= '&return=error2';
                header("Location: {$URL}");
                exit();
            }

            try {
                $data = array('pupilsightSchoolYearID' => $pupilsightSchoolYearID, 'pupilsightFinanceInvoiceID' => $pupilsightFinanceInvoiceID);
                $sql = 'SELECT * FROM pupilsightFinanceInvoice WHERE pupilsightSchoolYearID=:pupilsightSchoolYearID AND pupilsightFinanceInvoiceID=:pupilsightFinanceInvoiceID';
                $result = $connection2->prepare($sql);
                $result->execute($data);
            } catch (PDOException $e) {
                $URL .= '&return=error2';
                header("Location: {$URL}");
                exit();
            }

            if ($result->rowCount() != 1) {
                $URL .= '&return=error2';
                header("Location: {$URL}");
            } else {
                $row = $result->fetch();
                $notes = $_POST['notes'];
                $status = $row['status'];
                if ($status != 'Pending') {
                    $status = $_POST['status'];
                    if ($status == 'Paid - Complete') {
                        $status = 'Paid';
                    }
                }
                $order = null;
                if (isset($_POST['order'])) {
                    $order = $_POST['order'];
                }
                if ($_POST['status'] == 'Paid' or $_POST['status'] == 'Paid - Partial' or $_POST['status'] == 'Paid - Complete') {
                    $paidDate = dateConvert($guid, $_POST['paidDate']);
                } else if ($_POST['status'] == 'Refunded') {
                    $paidDate = $row['paidDate'];
                } else {
                    $paidDate = null;
                }
                if ($_POST['status'] == 'Paid' or $_POST['status'] == 'Paid - Partial' or $_POST['status'] == 'Paid - Complete') {
                    $paidAmountLog = $_POST['paidAmount'];
                    $paidAmount = $_POST['paidAmount'];
                    //If some paid already, work out amount, and add it to total
                    $alreadyPaid = getAmountPaid($connection2, $guid, 'pupilsightFinanceInvoice', $pupilsightFinanceInvoiceID);
                    $paidAmount += $alreadyPaid;
                } else if ($_POST['status'] == 'Refunded') {
                    $paidAmount = $row['paidAmount'];
                } else {
                    $paidAmount = null;
                }
                $paymentType = null;
                if ($_POST['status'] == 'Paid' or $_POST['status'] == 'Paid - Partial' or $_POST['status'] == 'Paid - Complete') {
                    $paymentType = $_POST['paymentType'];
                }
                $paymentTransactionID = null;
                if ($_POST['status'] == 'Paid' or $_POST['status'] == 'Paid - Partial' or $_POST['status'] == 'Paid - Complete') {
                    $paymentTransactionID = $_POST['paymentTransactionID'];
                }
                if ($row['billingScheduleType'] == 'Ad Hoc' and ($row['status'] == 'Pending' or $row['status'] == 'Issued')) {
                    $invoiceDueDate = dateConvert($guid, $_POST['invoiceDueDate']);
                } else {
                    $invoiceDueDate = $row['invoiceDueDate'];
                }

                //Write to database
                try {
                    $data = array('status' => $status, 'notes' => $notes, 'paidDate' => $paidDate, 'paidAmount' => $paidAmount, 'invoiceDueDate' => $invoiceDueDate, 'pupilsightPersonIDUpdate' => $_SESSION[$guid]['pupilsightPersonID'], 'pupilsightFinanceInvoiceID' => $pupilsightFinanceInvoiceID);
                    $sql = "UPDATE pupilsightFinanceInvoice SET status=:status, notes=:notes, paidDate=:paidDate, paidAmount=:paidAmount, invoiceDueDate=:invoiceDueDate, pupilsightPersonIDUpdate=:pupilsightPersonIDUpdate, timestampUpdate='".date('Y-m-d H:i:s')."' WHERE pupilsightFinanceInvoiceID=:pupilsightFinanceInvoiceID";
                    $result = $connection2->prepare($sql);
                    $result->execute($data);
                } catch (PDOException $e) {
                    $URL .= '&return=error2';
                    header("Location: {$URL}");
                    exit();
                }

                $partialFail = false;

                if ($status == 'Pending') {
                    if (is_null($order)) {
                        $partialFail = true;
                    } else {
                        //Remove fees
                        try {
                            $data = array('pupilsightFinanceInvoiceID' => $pupilsightFinanceInvoiceID);
                            $sql = 'DELETE FROM pupilsightFinanceInvoiceFee WHERE pupilsightFinanceInvoiceID=:pupilsightFinanceInvoiceID';
                            $result = $connection2->prepare($sql);
                            $result->execute($data);
                        } catch (PDOException $e) {
                            $partialFail = true;
                        }

                        //Organise Fees
                        $fees = array();
                        $pupilsightFinanceFeeCategoryIDList = '';
                        foreach ($order as $fee) {
                            $fees[$fee]['name'] = $_POST['name'.$fee];
                            $fees[$fee]['pupilsightFinanceFeeCategoryID'] = $_POST['pupilsightFinanceFeeCategoryID'.$fee];
                            $fees[$fee]['fee'] = $_POST['fee'.$fee];
                            $fees[$fee]['feeType'] = $_POST['feeType'.$fee];
                            $fees[$fee]['pupilsightFinanceFeeID'] = $_POST['pupilsightFinanceFeeID'.$fee];
                            $fees[$fee]['description'] = $_POST['description'.$fee];

                            $pupilsightFinanceFeeCategoryIDList .= $_POST['pupilsightFinanceFeeCategoryID'.$fee].",";
                        }
                        $pupilsightFinanceFeeCategoryIDList = substr($pupilsightFinanceFeeCategoryIDList, 0, -1);

                        //Write to fee categories
                        try {
                            $dataTemp = array('pupilsightFinanceFeeCategoryIDList' => $pupilsightFinanceFeeCategoryIDList, 'pupilsightFinanceInvoiceID' => $pupilsightFinanceInvoiceID);
                            $sqlTemp = "UPDATE pupilsightFinanceInvoice SET pupilsightFinanceFeeCategoryIDList=:pupilsightFinanceFeeCategoryIDList WHERE pupilsightFinanceInvoiceID=:pupilsightFinanceInvoiceID";
                            $resultTemp = $connection2->prepare($sqlTemp);
                            $resultTemp->execute($dataTemp);
                        } catch (PDOException $e) {
                            $partialFail = true;
                        }

                        //Add fees to invoice
                        $count = 0;
                        foreach ($fees as $fee) {
                            ++$count;
                            try {
                                if ($fee['feeType'] == 'Standard') {
                                    $dataInvoiceFee = array('pupilsightFinanceInvoiceID' => $pupilsightFinanceInvoiceID, 'feeType' => $fee['feeType'], 'pupilsightFinanceFeeID' => $fee['pupilsightFinanceFeeID']);
                                    $sqlInvoiceFee = "INSERT INTO pupilsightFinanceInvoiceFee SET pupilsightFinanceInvoiceID=:pupilsightFinanceInvoiceID, feeType=:feeType, pupilsightFinanceFeeID=:pupilsightFinanceFeeID, separated='N', sequenceNumber=$count";
                                } else {
                                    $dataInvoiceFee = array('pupilsightFinanceInvoiceID' => $pupilsightFinanceInvoiceID, 'feeType' => $fee['feeType'], 'name' => $fee['name'], 'description' => $fee['description'], 'pupilsightFinanceFeeCategoryID' => $fee['pupilsightFinanceFeeCategoryID'], 'fee' => $fee['fee']);
                                    $sqlInvoiceFee = "INSERT INTO pupilsightFinanceInvoiceFee SET pupilsightFinanceInvoiceID=:pupilsightFinanceInvoiceID, feeType=:feeType, name=:name, description=:description, pupilsightFinanceFeeCategoryID=:pupilsightFinanceFeeCategoryID, fee=:fee, sequenceNumber=$count";
                                }
                                $resultInvoiceFee = $connection2->prepare($sqlInvoiceFee);
                                $resultInvoiceFee->execute($dataInvoiceFee);
                            } catch (PDOException $e) {
                                $partialFail = true;
                            }
                        }
                    }
                }

                //Unlock tables
                try {
                    $sql = 'UNLOCK TABLES';
                    $result = $connection2->query($sql);
                } catch (PDOException $e) {
                }

                $emailFail = false;
                //Email Receipt
                if (isset($_POST['emailReceipt'])) {
                    if ($_POST['emailReceipt'] == 'Y') {
                        $from = $_POST['email'];
                        if ($partialFail == false and $from != '') {
                            //Send emails
                            $emails = array() ;
                            if (isset($_POST['emails'])) {
                                $emails = $_POST['emails'];
                                for ($i = 0; $i < count($emails); ++$i) {
                                    $emailsInner = explode(',', $emails[$i]);
                                    for ($n = 0; $n < count($emailsInner); ++$n) {
                                        if ($n == 0) {
                                            $emails[$i] = trim($emailsInner[$n]);
                                        } else {
                                            array_push($emails, trim($emailsInner[$n]));
                                        }
                                    }
                                }
                            }
                            if (count($emails) > 0) {
                                //Get receipt number
                                try {
                                    $dataPayments = array('foreignTable' => 'pupilsightFinanceInvoice', 'foreignTableID' => $pupilsightFinanceInvoiceID);
                                    $sqlPayments = 'SELECT pupilsightPayment.*, surname, preferredName FROM pupilsightPayment JOIN pupilsightPerson ON (pupilsightPayment.pupilsightPersonID=pupilsightPerson.pupilsightPersonID) WHERE foreignTable=:foreignTable AND foreignTableID=:foreignTableID ORDER BY timestamp, pupilsightPaymentID';
                                    $resultPayments = $connection2->prepare($sqlPayments);
                                    $resultPayments->execute($dataPayments);
                                } catch (PDOException $e) {
                                }
                                $receiptCount = $resultPayments->rowCount();

                                //Prep message
                                $body = receiptContents($guid, $connection2, $pupilsightFinanceInvoiceID, $pupilsightSchoolYearID, $_SESSION[$guid]['currency'], true, $receiptCount)."<p style='font-style: italic;'>Email sent via ".$_SESSION[$guid]['systemName'].' at '.$_SESSION[$guid]['organisationName'].'.</p>';
                                $bodyPlain = 'This email is not viewable in plain text: enable rich text/HTML in your email client to view the receipt. Please reply to this email if you have any questions.';

                                $mail = $container->get(Mailer::class);
                                $mail->SetFrom($from, sprintf(__('%1$s Finance'), $_SESSION[$guid]['organisationName']));
                                foreach ($emails as $address) {
                                    $mail->AddBCC($address);
                                }
                                $mail->CharSet = 'UTF-8';
                                $mail->Encoding = 'base64';
                                $mail->IsHTML(true);
                                $mail->Subject = 'Receipt From '.$_SESSION[$guid]['organisationNameShort'].' via '.$_SESSION[$guid]['systemName'];
                                $mail->Body = $body;
                                $mail->AltBody = $bodyPlain;

                                if (!$mail->Send()) {
                                    $emailFail = true;
                                }
                            } else {
                                $emailFail = true;
                            }
                        }
                    }
                }
                //Email reminder
                if (isset($_POST['emailReminder'])) {
                    if ($_POST['emailReminder'] == 'Y') {
                        $from = $_POST['email'];
                        if ($partialFail == false and $from != '') {
                            //Send emails
                            $emails = array() ;
                            if (isset($_POST['emails'])) {
                                $emails = $_POST['emails'];
                                for ($i = 0; $i < count($emails); ++$i) {
                                    $emailsInner = explode(',', $emails[$i]);
                                    for ($n = 0; $n < count($emailsInner); ++$n) {
                                        if ($n == 0) {
                                            $emails[$i] = trim($emailsInner[$n]);
                                        } else {
                                            array_push($emails, trim($emailsInner[$n]));
                                        }
                                    }
                                }
                            }


                            if (count($emails) > 0) {
                                $body = '';
                                //Prep message
                                if ($row['reminderCount'] == '0') {
                                    $reminderText = getSettingByScope($connection2, 'Finance', 'reminder1Text');
                                } elseif ($row['reminderCount'] == '1') {
                                    $reminderText = getSettingByScope($connection2, 'Finance', 'reminder2Text');
                                } elseif ($row['reminderCount'] >= '2') {
                                    $reminderText = getSettingByScope($connection2, 'Finance', 'reminder3Text');
                                }
                                if ($reminderText != '') {
                                    $reminderOutput = $row['reminderCount'] + 1;
                                    if ($reminderOutput > 3) {
                                        $reminderOutput = '3+';
                                    }
                                    $body .= '<p>Reminder '.$reminderOutput.': '.$reminderText.'</p><br/>';
                                }
                                $body .= invoiceContents($guid, $connection2, $pupilsightFinanceInvoiceID, $pupilsightSchoolYearID, $_SESSION[$guid]['currency'], true)."<p style='font-style: italic;'>Email sent via ".$_SESSION[$guid]['systemName'].' at '.$_SESSION[$guid]['organisationName'].'.</p>';
                                $bodyPlain = 'This email is not viewable in plain text: enable rich text/HTML in your email client to view the reminder. Please reply to this email if you have any questions.';

                                //Update reminder count
                                if ($row['reminderCount'] < 3) {
                                    try {
                                        $data = array('pupilsightFinanceInvoiceID' => $pupilsightFinanceInvoiceID);
                                        $sql = 'UPDATE pupilsightFinanceInvoice SET reminderCount='.($row['reminderCount'] + 1).' WHERE pupilsightFinanceInvoiceID=:pupilsightFinanceInvoiceID';
                                        $result = $connection2->prepare($sql);
                                        $result->execute($data);
                                    } catch (PDOException $e) {
                                    }
                                }

                                $mail = $container->get(Mailer::class);
                                $mail->SetFrom($from, sprintf(__('%1$s Finance'), $_SESSION[$guid]['organisationName']));
                                foreach ($emails as $address) {
                                    $mail->AddBCC($address);
                                }
                                $mail->CharSet = 'UTF-8';
                                $mail->Encoding = 'base64';
                                $mail->IsHTML(true);
                                $mail->Subject = 'Reminder From '.$_SESSION[$guid]['organisationNameShort'].' via '.$_SESSION[$guid]['systemName'];
                                $mail->Body = $body;
                                $mail->AltBody = $bodyPlain;

                                if (!$mail->Send()) {
                                    $emailFail = true;
                                }
                            } else {
                                $emailFail = true;
                            }
                        }
                    }
                }

                if ($status == 'Paid' or $status == 'Paid - Partial') {
                    if ($_POST['status'] == 'Paid') {
                        $statusLog = 'Complete';
                    } elseif ($_POST['status'] == 'Paid - Partial') {
                        $statusLog = 'Partial';
                    } elseif ($_POST['status'] == 'Paid - Complete') {
                        $statusLog = 'Final';
                    }
                    $logFail = setPaymentLog($connection2, $guid, 'pupilsightFinanceInvoice', $pupilsightFinanceInvoiceID, $paymentType, $statusLog, $paidAmountLog, null, null, null, null, $paymentTransactionID, null, $paidDate);
                    if ($logFail == false) {
                        $partialFail = true;
                    }
                }

                if ($partialFail == true) {
                    $URL .= '&return=error3';
                    header("Location: {$URL}");
                } elseif ($emailFail == true) {
                    $URL .= '&return=success1';
                    header("Location: {$URL}");
                } else {
                    $URL .= '&return=success0';
                    header("Location: {$URL}");
                }
            }
        }
    }
}
