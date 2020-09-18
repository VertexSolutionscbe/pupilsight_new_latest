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
    $URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_POST['address'])."/invoices_manage_issue.php&pupilsightFinanceInvoiceID=$pupilsightFinanceInvoiceID&pupilsightSchoolYearID=$pupilsightSchoolYearID&status=$status&pupilsightFinanceInvoiceeID=$pupilsightFinanceInvoiceeID&monthOfIssue=$monthOfIssue&pupilsightFinanceBillingScheduleID=$pupilsightFinanceBillingScheduleID&pupilsightFinanceFeeCategoryID=$pupilsightFinanceFeeCategoryID";
    $URLSuccess = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_POST['address'])."/invoices_manage.php&pupilsightFinanceInvoiceID=$pupilsightFinanceInvoiceID&pupilsightSchoolYearID=$pupilsightSchoolYearID&status=$status&pupilsightFinanceInvoiceeID=$pupilsightFinanceInvoiceeID&monthOfIssue=$monthOfIssue&pupilsightFinanceBillingScheduleID=$pupilsightFinanceBillingScheduleID&pupilsightFinanceFeeCategoryID=$pupilsightFinanceFeeCategoryID";

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
                $sql = 'LOCK TABLES pupilsightFinanceInvoice WRITE, pupilsightFinanceInvoiceFee WRITE, pupilsightFinanceInvoicee WRITE, pupilsightFinanceFee WRITE, pupilsightFinanceFeeCategory WRITE';
                $result = $connection2->prepare($sql);
                $result->execute($data);
            } catch (PDOException $e) {
                $URL .= '&return=error2';
                header("Location: {$URL}");
                exit();
            }

            try {
                $data = array('pupilsightSchoolYearID' => $pupilsightSchoolYearID, 'pupilsightFinanceInvoiceID' => $pupilsightFinanceInvoiceID);
                $sql = "SELECT * FROM pupilsightFinanceInvoice WHERE pupilsightSchoolYearID=:pupilsightSchoolYearID AND pupilsightFinanceInvoiceID=:pupilsightFinanceInvoiceID AND status='Pending'";
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
                $status = 'Issued';
                $invoiceDueDate = $_POST['invoiceDueDate'];
                if ($row['billingScheduleType'] == 'Scheduled') {
                    $separated = 'Y';
                } else {
                    $separated = null;
                }
                $invoiceIssueDate = date('Y-m-d');

                if ($invoiceDueDate == '') {
                    $URL .= '&return=error1';
                    header("Location: {$URL}");
                } else {
                    //Write to database
                    try {
                        $data = array('status' => $status, 'notes' => $notes, 'separated' => $separated, 'invoiceDueDate' => dateConvert($guid, $invoiceDueDate), 'invoiceIssueDate' => $invoiceIssueDate, 'pupilsightPersonIDUpdate' => $_SESSION[$guid]['pupilsightPersonID'], 'pupilsightFinanceInvoiceID' => $pupilsightFinanceInvoiceID);
                        $sql = "UPDATE pupilsightFinanceInvoice SET status=:status, notes=:notes, separated=:separated, invoiceDueDate=:invoiceDueDate, invoiceIssueDate=:invoiceIssueDate, pupilsightPersonIDUpdate=:pupilsightPersonIDUpdate, timestampUpdate='".date('Y-m-d H:i:s')."' WHERE pupilsightFinanceInvoiceID=:pupilsightFinanceInvoiceID";
                        $result = $connection2->prepare($sql);
                        $result->execute($data);
                    } catch (PDOException $e) {
                        $URL .= '&return=error2';
                        header("Location: {$URL}");
                        exit();
                    }

                    $partialFail = false;
                    $emailFail = false;
                    
                    //Read & Organise Fees
                    $fees = array();
                    $count = 0;
                    //Standard Fees
                    try {
                        $dataFees['pupilsightFinanceInvoiceID'] = $pupilsightFinanceInvoiceID;
                        $sqlFees = "SELECT pupilsightFinanceInvoiceFee.pupilsightFinanceInvoiceFeeID, pupilsightFinanceInvoiceFee.feeType, pupilsightFinanceFeeCategory.name AS category, pupilsightFinanceFee.name AS name, pupilsightFinanceFee.fee AS fee, pupilsightFinanceFee.description AS description, pupilsightFinanceInvoiceFee.pupilsightFinanceFeeID AS pupilsightFinanceFeeID, pupilsightFinanceFee.pupilsightFinanceFeeCategoryID AS pupilsightFinanceFeeCategoryID, sequenceNumber FROM pupilsightFinanceInvoiceFee JOIN pupilsightFinanceFee ON (pupilsightFinanceInvoiceFee.pupilsightFinanceFeeID=pupilsightFinanceFee.pupilsightFinanceFeeID) JOIN pupilsightFinanceFeeCategory ON (pupilsightFinanceFee.pupilsightFinanceFeeCategoryID=pupilsightFinanceFeeCategory.pupilsightFinanceFeeCategoryID) WHERE pupilsightFinanceInvoiceID=:pupilsightFinanceInvoiceID AND feeType='Standard' ORDER BY sequenceNumber";
                        $resultFees = $connection2->prepare($sqlFees);
                        $resultFees->execute($dataFees);
                    } catch (PDOException $e) {
                        $partialFail = true;
                    }
                    while ($rowFees = $resultFees->fetch()) {
                        $fees[$count]['name'] = $rowFees['name'];
                        $fees[$count]['pupilsightFinanceFeeCategoryID'] = $rowFees['pupilsightFinanceFeeCategoryID'];
                        $fees[$count]['fee'] = $rowFees['fee'];
                        $fees[$count]['feeType'] = 'Standard';
                        $fees[$count]['pupilsightFinanceFeeID'] = $rowFees['pupilsightFinanceFeeID'];
                        $fees[$count]['separated'] = 'Y';
                        $fees[$count]['description'] = $rowFees['description'];
                        $fees[$count]['sequenceNumber'] = $rowFees['sequenceNumber'];
                        ++$count;
                    }

                    //Ad Hoc Fees
                    try {
                        $dataFees['pupilsightFinanceInvoiceID'] = $pupilsightFinanceInvoiceID;
                        $sqlFees = "SELECT pupilsightFinanceInvoiceFee.pupilsightFinanceInvoiceFeeID, pupilsightFinanceInvoiceFee.feeType, pupilsightFinanceFeeCategory.name AS category, pupilsightFinanceInvoiceFee.name AS name, pupilsightFinanceInvoiceFee.fee, pupilsightFinanceInvoiceFee.description AS description, NULL AS pupilsightFinanceFeeID, pupilsightFinanceInvoiceFee.pupilsightFinanceFeeCategoryID AS pupilsightFinanceFeeCategoryID, sequenceNumber FROM pupilsightFinanceInvoiceFee JOIN pupilsightFinanceFeeCategory ON (pupilsightFinanceInvoiceFee.pupilsightFinanceFeeCategoryID=pupilsightFinanceFeeCategory.pupilsightFinanceFeeCategoryID) WHERE pupilsightFinanceInvoiceID=:pupilsightFinanceInvoiceID AND feeType='Ad Hoc' ORDER BY sequenceNumber";
                        $resultFees = $connection2->prepare($sqlFees);
                        $resultFees->execute($dataFees);
                    } catch (PDOException $e) {
                        $partialFail = true;
                    }
                    while ($rowFees = $resultFees->fetch()) {
                        $fees[$count]['name'] = $rowFees['name'];
                        $fees[$count]['pupilsightFinanceFeeCategoryID'] = $rowFees['pupilsightFinanceFeeCategoryID'];
                        $fees[$count]['fee'] = $rowFees['fee'];
                        $fees[$count]['feeType'] = 'Ad Hoc';
                        $fees[$count]['pupilsightFinanceFeeID'] = null;
                        $fees[$count]['separated'] = null;
                        $fees[$count]['description'] = $rowFees['description'];
                        $fees[$count]['sequenceNumber'] = $rowFees['sequenceNumber'];
                        ++$count;
                    }

                    //Remove fees
                    try {
                        $data = array('pupilsightFinanceInvoiceID' => $pupilsightFinanceInvoiceID);
                        $sql = 'DELETE FROM pupilsightFinanceInvoiceFee WHERE pupilsightFinanceInvoiceID=:pupilsightFinanceInvoiceID';
                        $result = $connection2->prepare($sql);
                        $result->execute($data);
                    } catch (PDOException $e) {
                        $partialFail = true;
                    }

                    //Add fees to invoice
                    foreach ($fees as $fee) {
                        try {
                            $dataInvoiceFee = array('pupilsightFinanceInvoiceID' => $pupilsightFinanceInvoiceID, 'feeType' => $fee['feeType'], 'pupilsightFinanceFeeID' => $fee['pupilsightFinanceFeeID'], 'name' => $fee['name'], 'description' => $fee['description'], 'pupilsightFinanceFeeCategoryID' => $fee['pupilsightFinanceFeeCategoryID'], 'fee' => $fee['fee'], 'separated' => $fee['separated'], 'sequenceNumber' => $fee['sequenceNumber']);
                            $sqlInvoiceFee = 'INSERT INTO pupilsightFinanceInvoiceFee SET pupilsightFinanceInvoiceID=:pupilsightFinanceInvoiceID, feeType=:feeType, pupilsightFinanceFeeID=:pupilsightFinanceFeeID, name=:name, description=:description, pupilsightFinanceFeeCategoryID=:pupilsightFinanceFeeCategoryID, fee=:fee, separated=:separated, sequenceNumber=:sequenceNumber';
                            $resultInvoiceFee = $connection2->prepare($sqlInvoiceFee);
                            $resultInvoiceFee->execute($dataInvoiceFee);
                        } catch (PDOException $e) {
                            $partialFail = true;
                        }
                    }

                    //Unlock module table
                    try {
                        $sql = 'UNLOCK TABLES';
                        $result = $connection2->query($sql);
                    } catch (PDOException $e) {
                    }

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
                            //Prep message
                            $body = invoiceContents($guid, $connection2, $pupilsightFinanceInvoiceID, $pupilsightSchoolYearID, $_SESSION[$guid]['currency'], true)."<p style='font-style: italic;'>Email sent via ".$_SESSION[$guid]['systemName'].' at '.$_SESSION[$guid]['organisationName'].'.</p>';
                            $bodyPlain = 'This email is not viewable in plain text: enable rich text/HTML in your email client to view the invoice. Please reply to this email if you have any questions.';

                            $mail = $container->get(Mailer::class);
                            $mail->SetFrom($from, sprintf(__('%1$s Finance'), $_SESSION[$guid]['organisationName']));
                            foreach ($emails as $address) {
                                $mail->AddBCC($address);
                            }
                            $mail->CharSet = 'UTF-8';
                            $mail->Encoding = 'base64';
                            $mail->IsHTML(true);
                            $mail->Subject = 'Invoice From '.$_SESSION[$guid]['organisationNameShort'].' via '.$_SESSION[$guid]['systemName'];
                            $mail->Body = $body;
                            $mail->AltBody = $bodyPlain;

                            if (!$mail->Send()) {
                                $emailFail = true;
                            }
                        }
                    }

                    if ($partialFail == true) {
                        $URL .= '&return=error3';
                        header("Location: {$URL}");
                    } elseif ($emailFail == true) {
                        $URLSuccess = $URLSuccess.'&return=success1';
                        header("Location: {$URLSuccess}");
                    } else {
                        $URLSuccess = $URLSuccess.'&return=success0';
                        header("Location: {$URLSuccess}");
                    }
                }
            }
        }
    }
}
