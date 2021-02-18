<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Contracts\Comms\Mailer;

include '../../pupilsight.php';

include './moduleFunctions.php';

$URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/Finance/invoices_payOnline.php';

$paid = null;
if (isset($_GET['paid'])) {
    $paid = $_GET['paid'];
}

if ($paid != 'Y') { //IF PAID IS NOT Y, LET'S REDIRECT TO MAKE PAYMENT
    //Get variables
    $pupilsightFinanceInvoiceID = '';
    if (isset($_POST['pupilsightFinanceInvoiceID'])) {
        $pupilsightFinanceInvoiceID = $_POST['pupilsightFinanceInvoiceID'];
    }
    $key = '';
    if (isset($_POST['key'])) {
        $key = $_POST['key'];
    }

    //Check variables
    if ($pupilsightFinanceInvoiceID == '' or $key == '') {
        $URL .= '&return=error1';
        header("Location: {$URL}");
    } else {
        //Check for record
        $keyReadFail = false;
        try {
            $dataKeyRead = array('pupilsightFinanceInvoiceID' => $pupilsightFinanceInvoiceID, 'key' => $key);
            $sqlKeyRead = "SELECT * FROM pupilsightFinanceInvoice WHERE pupilsightFinanceInvoiceID=:pupilsightFinanceInvoiceID AND `key`=:key AND status='Issued'";
            $resultKeyRead = $connection2->prepare($sqlKeyRead);
            $resultKeyRead->execute($dataKeyRead);
        } catch (PDOException $e) {
            $URL .= '&return=error2';
            header("Location: {$URL}");
            exit();
        }

        if ($resultKeyRead->rowCount() != 1) { //If not exists, report error
            $URL .= '&return=error2';
            header("Location: {$URL}");
            exit();
        } else {    //If exists check confirmed
            $rowKeyRead = $resultKeyRead->fetch();

            //Get value of the invoice.
            $feeOK = true;
            try {
                $dataFees['pupilsightFinanceInvoiceID'] = $pupilsightFinanceInvoiceID;
                $sqlFees = 'SELECT pupilsightFinanceInvoiceFee.pupilsightFinanceInvoiceFeeID, pupilsightFinanceInvoiceFee.feeType, pupilsightFinanceFeeCategory.name AS category, pupilsightFinanceInvoiceFee.name AS name, pupilsightFinanceInvoiceFee.fee, pupilsightFinanceInvoiceFee.description AS description, NULL AS pupilsightFinanceFeeID, pupilsightFinanceInvoiceFee.pupilsightFinanceFeeCategoryID AS pupilsightFinanceFeeCategoryID, sequenceNumber FROM pupilsightFinanceInvoiceFee JOIN pupilsightFinanceFeeCategory ON (pupilsightFinanceInvoiceFee.pupilsightFinanceFeeCategoryID=pupilsightFinanceFeeCategory.pupilsightFinanceFeeCategoryID) WHERE pupilsightFinanceInvoiceID=:pupilsightFinanceInvoiceID ORDER BY sequenceNumber';
                $resultFees = $connection2->prepare($sqlFees);
                $resultFees->execute($dataFees);
            } catch (PDOException $e) {
                $feeOK = false;
                $URL .= '&return=error2';
                header("Location: {$URL}");
                exit();
            }

            if ($feeOK == true) {
                $feeTotal = 0;
                while ($rowFees = $resultFees->fetch()) {
                    $feeTotal += $rowFees['fee'];
                }

                $currency = getSettingByScope($connection2, 'System', 'currency');
                $enablePayments = getSettingByScope($connection2, 'System', 'enablePayments');
                $paypalAPIUsername = getSettingByScope($connection2, 'System', 'paypalAPIUsername');
                $paypalAPIPassword = getSettingByScope($connection2, 'System', 'paypalAPIPassword');
                $paypalAPISignature = getSettingByScope($connection2, 'System', 'paypalAPISignature');

                if ($enablePayments == 'Y' and $paypalAPIUsername != '' and $paypalAPIPassword != '' and $paypalAPISignature != '' and $feeTotal > 0) {
                    $financeOnlinePaymentEnabled = getSettingByScope($connection2, 'Finance', 'financeOnlinePaymentEnabled');
                    $financeOnlinePaymentThreshold = getSettingByScope($connection2, 'Finance', 'financeOnlinePaymentThreshold');
                    if ($financeOnlinePaymentEnabled == 'Y') {
                        if ($financeOnlinePaymentThreshold == '' or $financeOnlinePaymentThreshold >= $feeTotal) {
                            //Let's call for the payment to be done!
                            $_SESSION[$guid]['gatewayCurrencyNoSupportReturnURL'] = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/Finance/invoices_payOnline.php&return=error3';
                            $URL = $_SESSION[$guid]['absoluteURL']."/lib/paypal/expresscheckout.php?Payment_Amount=$feeTotal&return=".urlencode("modules/Finance/invoices_payOnlineProcess.php?return=success1&paid=Y&feeTotal=$feeTotal&pupilsightFinanceInvoiceID=$pupilsightFinanceInvoiceID&key=$key").'&fail='.urlencode("modules/Finance/invoices_payOnlineProcess?return=success2&paid=N&feeTotal=$feeTotal&pupilsightFinanceInvoiceID=$pupilsightFinanceInvoiceID&key=$key");
                            header("Location: {$URL}");
                        } else {
                            $URL .= '&return=error2';
                            header("Location: {$URL}");
                            exit();
                        }
                    } else {
                        $URL .= '&return=error2';
                        header("Location: {$URL}");
                        exit();
                    }
                } else {
                    $URL .= '&return=error2';
                    header("Location: {$URL}");
                    exit();
                }
            }
        }
    }
} else { //IF PAID IS Y WE ARE JUST RETURNING TO FINALISE PAYMENT AND RECORD OF PAYMENT, SO LET'S DO IT.
    //Get returned paypal tokens, ids, etc
    $paymentMade = 'N';
    if ($_GET['return'] == 'success1') {
        $paymentMade = 'Y';
    }
    $paymentToken = null;
    if (isset($_GET['token'])) {
        $paymentToken = $_GET['token'];
    }
    $paymentPayerID = null;
    if (isset($_GET['PayerID'])) {
        $paymentPayerID = $_GET['PayerID'];
    }
    $feeTotal = null;
    if (isset($_GET['feeTotal'])) {
        $feeTotal = $_GET['feeTotal'];
    }
    $pupilsightFinanceInvoiceID = '';
    if (isset($_GET['pupilsightFinanceInvoiceID'])) {
        $pupilsightFinanceInvoiceID = $_GET['pupilsightFinanceInvoiceID'];
    }
    $key = '';
    if (isset($_GET['key'])) {
        $key = $_GET['key'];
    }

    $pupilsightFinanceInvoiceeID = '';
    $invoiceTo = '';
    $pupilsightSchoolYearID = '';
    try {
        $dataKeyRead = array('pupilsightFinanceInvoiceID' => $pupilsightFinanceInvoiceID, 'key' => $key);
        $sqlKeyRead = 'SELECT * FROM pupilsightFinanceInvoice WHERE pupilsightFinanceInvoiceID=:pupilsightFinanceInvoiceID AND `key`=:key';
        $resultKeyRead = $connection2->prepare($sqlKeyRead);
        $resultKeyRead->execute($dataKeyRead);
    } catch (PDOException $e) {
    }
    if ($resultKeyRead->rowCount() == 1) {
        $rowKeyRead = $resultKeyRead->fetch();
        $pupilsightFinanceInvoiceeID = $rowKeyRead['pupilsightFinanceInvoiceeID'];
        $invoiceTo = $rowKeyRead['invoiceTo'];
        $pupilsightSchoolYearID = $rowKeyRead['pupilsightSchoolYearID'];
    }

    //Check return values to see if we can proceed
    if ($paymentToken == '' or $feeTotal == '' or $pupilsightFinanceInvoiceID == '' or $key == '' or $pupilsightFinanceInvoiceeID == '' or $invoiceTo = '' or $pupilsightSchoolYearID == '') {
        //Success $URL.="&addReturn=success2&pupilsightFinanceInvoiceID=$pupilsightFinanceInvoiceID&key=$key" ;
        header("Location: {$URL}");
        exit();
    } else {
        //PROCEED AND FINALISE PAYMENT
        require '../../lib/paypal/paypalfunctions.php';

        //Ask paypal to finalise the payment
        $confirmPayment = confirmPayment($guid, $feeTotal, $paymentToken, $paymentPayerID);

        $ACK = $confirmPayment['ACK'];
        $paymentTransactionID = $confirmPayment['PAYMENTINFO_0_TRANSACTIONID'];
        $paymentReceiptID = $confirmPayment['PAYMENTINFO_0_RECEIPTID'];

        //Payment was successful. Yeah!
        if ($ACK == 'Success') {
            $updateFail = false;

            //Save payment details to pupilsightPayment
            $pupilsightPaymentID = setPaymentLog($connection2, $guid, 'pupilsightFinanceInvoice', $pupilsightFinanceInvoiceID, 'Online', 'Complete', $feeTotal, 'Paypal', 'Success', $paymentToken, $paymentPayerID, $paymentTransactionID, $paymentReceiptID);

            //Link pupilsightPayment record to pupilsightApplicationForm, and make note that payment made
            if ($pupilsightPaymentID != '') {
                try {
                    $data = array('paidDate' => date('Y-m-d'), 'paidAmount' => $feeTotal, 'pupilsightPaymentID' => $pupilsightPaymentID, 'pupilsightFinanceInvoiceID' => $pupilsightFinanceInvoiceID);
                    $sql = "UPDATE pupilsightFinanceInvoice SET status='Paid', paidDate=:paidDate, paidAmount=:paidAmount, pupilsightPaymentID=:pupilsightPaymentID WHERE pupilsightFinanceInvoiceID=:pupilsightFinanceInvoiceID";
                    $result = $connection2->prepare($sql);
                    $result->execute($data);
                } catch (PDOException $e) {
                    $updateFail = true;
                }
            } else {
                $updateFail = true;
            }

            if ($updateFail == true) {
                $URL .= "&addReturn=success3&pupilsightFinanceInvoiceID=$pupilsightFinanceInvoiceID&key=$key";
                header("Location: {$URL}");
                exit;
            }

            //EMAIL RECEIPT (no error reporting)
            //Populate to email.
            $emails = array();
            $emailsCount = 0;
            if ($invoiceTo == 'Company') {
                try {
                    $dataCompany = array('pupilsightFinanceInvoiceeID' => $pupilsightFinanceInvoiceeID);
                    $sqlCompany = 'SELECT * FROM pupilsightFinanceInvoicee WHERE pupilsightFinanceInvoiceeID=:pupilsightFinanceInvoiceeID';
                    $resultCompany = $connection2->prepare($sqlCompany);
                    $resultCompany->execute($dataCompany);
                } catch (PDOException $e) {
                }
                if ($resultCompany->rowCount() != 1) {
                } else {
                    $rowCompany = $resultCompany->fetch();
                    if ($rowCompany['companyEmail'] != '' and $rowCompany['companyContact'] != '' and $rowCompany['companyName'] != '') {
                        $emails[$emailsCount] = $rowCompany['companyEmail'];
                        ++$emailsCount;
                        $rowCompany['companyCCFamily'];
                        if ($rowCompany['companyCCFamily'] == 'Y') {
                            try {
                                $dataParents = array('pupilsightFinanceInvoiceeID' => $pupilsightFinanceInvoiceeID);
                                $sqlParents = "SELECT parent.title, parent.surname, parent.preferredName, parent.email, parent.address1, parent.address1District, parent.address1Country, homeAddress, homeAddressDistrict, homeAddressCountry FROM pupilsightFinanceInvoicee JOIN pupilsightPerson AS student ON (pupilsightFinanceInvoicee.pupilsightPersonID=student.pupilsightPersonID) JOIN pupilsightFamilyChild ON (pupilsightFamilyChild.pupilsightPersonID=student.pupilsightPersonID) JOIN pupilsightFamily ON (pupilsightFamilyChild.pupilsightFamilyID=pupilsightFamily.pupilsightFamilyID) JOIN pupilsightFamilyAdult ON (pupilsightFamily.pupilsightFamilyID=pupilsightFamilyAdult.pupilsightFamilyID) JOIN pupilsightPerson AS parent ON (pupilsightFamilyAdult.pupilsightPersonID=parent.pupilsightPersonID) WHERE pupilsightFinanceInvoiceeID=:pupilsightFinanceInvoiceeID AND (contactPriority=1 OR (contactPriority=2 AND contactEmail='Y')) ORDER BY contactPriority, surname, preferredName";
                                $resultParents = $connection2->prepare($sqlParents);
                                $resultParents->execute($dataParents);
                            } catch (PDOException $e) {
                                $emailFail = true;
                            }
                            if ($resultParents->rowCount() < 1) {
                                $emailFail = true;
                            } else {
                                while ($rowParents = $resultParents->fetch()) {
                                    if ($rowParents['preferredName'] != '' and $rowParents['surname'] != '' and $rowParents['email'] != '') {
                                        $emails[$emailsCount] = $rowParents['email'];
                                        ++$emailsCount;
                                    }
                                }
                            }
                        }
                    } else {
                        $emailFail = true;
                    }
                }
            } else {
                try {
                    $dataParents = array('pupilsightFinanceInvoiceeID' => $pupilsightFinanceInvoiceeID);
                    $sqlParents = "SELECT parent.title, parent.surname, parent.preferredName, parent.email, parent.address1, parent.address1District, parent.address1Country, homeAddress, homeAddressDistrict, homeAddressCountry FROM pupilsightFinanceInvoicee JOIN pupilsightPerson AS student ON (pupilsightFinanceInvoicee.pupilsightPersonID=student.pupilsightPersonID) JOIN pupilsightFamilyChild ON (pupilsightFamilyChild.pupilsightPersonID=student.pupilsightPersonID) JOIN pupilsightFamily ON (pupilsightFamilyChild.pupilsightFamilyID=pupilsightFamily.pupilsightFamilyID) JOIN pupilsightFamilyAdult ON (pupilsightFamily.pupilsightFamilyID=pupilsightFamilyAdult.pupilsightFamilyID) JOIN pupilsightPerson AS parent ON (pupilsightFamilyAdult.pupilsightPersonID=parent.pupilsightPersonID) WHERE pupilsightFinanceInvoiceeID=:pupilsightFinanceInvoiceeID AND (contactPriority=1 OR (contactPriority=2 AND contactEmail='Y')) ORDER BY contactPriority, surname, preferredName";
                    $resultParents = $connection2->prepare($sqlParents);
                    $resultParents->execute($dataParents);
                } catch (PDOException $e) {
                    $emailFail = true;
                }
                if ($resultParents->rowCount() < 1) {
                    $emailFail = true;
                } else {
                    while ($rowParents = $resultParents->fetch()) {
                        if ($rowParents['preferredName'] != '' and $rowParents['surname'] != '' and $rowParents['email'] != '') {
                            $emails[$emailsCount] = $rowParents['email'];
                            ++$emailsCount;
                        }
                    }
                }
            }

            //Send emails
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
                $mail->SetFrom(getSettingByScope($connection2, 'Finance', 'email'), sprintf(__('%1$s Finance'), $_SESSION[$guid]['organisationName']));
                foreach ($emails as $address) {
                    $mail->AddBCC($address);
                }
                $mail->CharSet = 'UTF-8';
                $mail->Encoding = 'base64';
                $mail->IsHTML(true);
                $mail->Subject = 'Receipt From '.$_SESSION[$guid]['organisationNameShort'].' via '.$_SESSION[$guid]['systemName'];
                $mail->Body = $body;
                $mail->AltBody = $bodyPlain;

                $mail->Send();
                foreach ($emails as $individualemail) {
                    $data=array('email'=>$individualemail);
                    $sql="SELECT pupilsightPersonID FROM pupilsightPerson WHERE email=:email";
                    $result = $connection2->prepare($sql);
                    $result->execute($data);
                    while ($rowppid = $result->fetch()) {
                        $ppid=$rowppid['pupilsightPersonID'];


                        $msgby=$_SESSION[$guid]["pupilsightPersonID"];
                        $msgto=$ppid;
                        //$emailreportp=$sms->updateMessengerTableforEmail($msgto,$subject,$body,$msgby);

                        $sqlAI = "SHOW TABLE STATUS LIKE 'pupilsightMessenger'";
                        $resultAI = $connection2->query($sqlAI);
                        $rowAI = $resultAI->fetch();
                        $AI = str_pad($rowAI['Auto_increment'], 12, "0", STR_PAD_LEFT);

                        $email = "Y";
                        $messageWall = "N";
                        $sms = "N";
                        $date1 = date('Y-m-d');
                        $data = array("email" => $email, "messageWall" => $messageWall, "messageWall_date1" => $date1, "sms" => $sms, "subject" => $subject, "body" => $body,  "pupilsightPersonID" => $msgby, "category" => 'Other', "timestamp" => date("Y-m-d H:i:s"));
                        $sql = "INSERT INTO pupilsightMessenger SET email=:email, messageWall=:messageWall, messageWall_date1=:messageWall_date1, sms=:sms, subject=:subject, body=:body, pupilsightPersonID=:pupilsightPersonID,messengercategory=:category, timestamp=:timestamp";
                        $result = $connection2->prepare($sql);
                        $result->execute($data);

                        $data = array("AI" => $AI, "t" => $msgto);
                        $sql = "INSERT INTO pupilsightMessengerTarget SET pupilsightMessengerID=:AI, type='Individuals', id=:t";
                        $result = $connection2->prepare($sql);
                        $result->execute($data);
                    }
                }
            }

            $URL .= "&return=success1&pupilsightFinanceInvoiceID=$pupilsightFinanceInvoiceID&key=$key";
            header("Location: {$URL}");
        } else {
            $updateFail = false;

            //Save payment details to pupilsightPayment
            $pupilsightPaymentID = setPaymentLog($connection2, $guid, 'pupilsightFinanceInvoice', $pupilsightFinanceInvoiceID, 'Online', 'Failure', $feeTotal, 'Paypal', 'Failure', $paymentToken, $paymentPayerID, $paymentTransactionID, $paymentReceiptID);

            //Link pupilsightPayment record to pupilsightApplicationForm, and make note that payment made
            if ($pupilsightPaymentID != '') {
                try {
                    $data = array('pupilsightPaymentID' => $pupilsightPaymentID, 'pupilsightFinanceInvoiceID' => $pupilsightFinanceInvoiceID);
                    $sql = 'UPDATE pupilsightFinanceInvoice pupilsightPaymentID=:pupilsightPaymentID WHERE pupilsightFinanceInvoiceID=:pupilsightFinanceInvoiceID';
                    $result = $connection2->prepare($sql);
                    $result->execute($data);
                } catch (PDOException $e) {
                    $updateFail = true;
                }
            } else {
                $updateFail = true;
            }

            if ($updateFail == true) {
                //Success 2
                $URL .= "&return=success2&pupilsightFinanceInvoiceID=$pupilsightFinanceInvoiceID&key=$key";
                header("Location: {$URL}");
                exit;
            }

            //Success 2
            $URL .= "&return=success2&pupilsightFinanceInvoiceID=$pupilsightFinanceInvoiceID&key=$key";
            header("Location: {$URL}");
        }
    }
}
