<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Contracts\Comms\Mailer;

include '../../pupilsight.php';

$from = getSettingByScope($connection2, 'Finance', 'email');

//Module includes
include './moduleFunctions.php';

$action = $_POST['action'];
$pupilsightSchoolYearID = $_GET['pupilsightSchoolYearID'];
$status = $_GET['status'];
$pupilsightFinanceInvoiceeID = $_GET['pupilsightFinanceInvoiceeID'];
$monthOfIssue = $_GET['monthOfIssue'];
$pupilsightFinanceBillingScheduleID = $_GET['pupilsightFinanceBillingScheduleID'];
$pupilsightFinanceFeeCategoryID = $_GET['pupilsightFinanceFeeCategoryID'];

if ($pupilsightSchoolYearID == '' or $action == '') { echo 'Fatal error loading this page!';
} else {
    if ($action == 'issue' or $action == 'issueNoEmail') {
        $URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_POST['address'])."/invoices_manage.php&pupilsightSchoolYearID=$pupilsightSchoolYearID&status=Issued&pupilsightFinanceInvoiceeID=$pupilsightFinanceInvoiceeID&monthOfIssue=$monthOfIssue&pupilsightFinanceBillingScheduleID=$pupilsightFinanceBillingScheduleID";
    } else {
        $URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_POST['address'])."/invoices_manage.php&pupilsightSchoolYearID=$pupilsightSchoolYearID&status=$status&pupilsightFinanceInvoiceeID=$pupilsightFinanceInvoiceeID&monthOfIssue=$monthOfIssue&pupilsightFinanceBillingScheduleID=$pupilsightFinanceBillingScheduleID&pupilsightFinanceFeeCategoryID=$pupilsightFinanceFeeCategoryID";
    }

    if (isActionAccessible($guid, $connection2, '/modules/Finance/invoices_manage.php') == false) {
        $URL .= '&return=error0';
        header("Location: {$URL}");
    } else {
        $pupilsightFinanceInvoiceIDs = $_POST['pupilsightFinanceInvoiceIDs'];
        if (count($pupilsightFinanceInvoiceIDs) < 1) {
            $URL .= '&return=error1';
            header("Location: {$URL}");
        } else {
            $partialFail = false;
            //DELETE
            if ($action == 'delete') {
                foreach ($pupilsightFinanceInvoiceIDs as $pupilsightFinanceInvoiceID) {
                    try {
                        $data = array('pupilsightFinanceInvoiceID' => $pupilsightFinanceInvoiceID);
                        $sql = 'DELETE FROM pupilsightFinanceInvoice WHERE pupilsightFinanceInvoiceID=:pupilsightFinanceInvoiceID';
                        $result = $connection2->prepare($sql);
                        $result->execute($data);
                    } catch (PDOException $e) {
                        $partialFail = true;
                    }

                    try {
                        $data = array('pupilsightFinanceInvoiceID' => $pupilsightFinanceInvoiceID);
                        $sql = 'DELETE FROM pupilsightFinanceInvoiceFee WHERE pupilsightFinanceInvoiceID=:pupilsightFinanceInvoiceID';
                        $result = $connection2->prepare($sql);
                        $result->execute($data);
                    } catch (PDOException $e) {
                        $partialFail = true;
                    }
                }
                if ($partialFail == true) {
                    $URL .= '&return=warning1';
                    header("Location: {$URL}");
                } else {
                    $URL .= '&return=success0';
                    header("Location: {$URL}");
                }
            }
            //ISSUE
            elseif ($action == 'issue' or $action == 'issueNoEmail') {
                $thisLockFail = false;
                //LOCK INVOICE TABLES
                try {
                    $data = array();
                    $sql = 'LOCK TABLES pupilsightFinanceInvoice WRITE, pupilsightFinanceInvoiceFee WRITE, pupilsightFinanceInvoicee WRITE, pupilsightFinanceFee WRITE, pupilsightFinanceFeeCategory WRITE, pupilsightFinanceBillingSchedule WRITE';
                    $result = $connection2->prepare($sql);
                    $result->execute($data);
                } catch (PDOException $e) {
                    $partialFail = true;
                    $thisLockFail = true;
                }

                if ($thisLockFail == false) {
                    $emailFail = false;
                    foreach ($pupilsightFinanceInvoiceIDs as $pupilsightFinanceInvoiceID) {
                        try {
                            $data = array('pupilsightSchoolYearID' => $pupilsightSchoolYearID, 'pupilsightFinanceInvoiceID' => $pupilsightFinanceInvoiceID);
                            $sql = "SELECT pupilsightFinanceInvoice.*, pupilsightFinanceBillingSchedule.invoiceDueDate AS invoiceDueDateScheduled FROM pupilsightFinanceInvoice LEFT JOIN pupilsightFinanceBillingSchedule ON (pupilsightFinanceInvoice.pupilsightFinanceBillingScheduleID=pupilsightFinanceBillingSchedule.pupilsightFinanceBillingScheduleID) WHERE pupilsightFinanceInvoice.pupilsightSchoolYearID=:pupilsightSchoolYearID AND pupilsightFinanceInvoiceID=:pupilsightFinanceInvoiceID AND status='Pending'";
                            $result = $connection2->prepare($sql);
                            $result->execute($data);
                        } catch (PDOException $e) {
                        }

                        if ($result->rowCount() != 1) {
                            $partialFail = true;
                        } else {
                            $row = $result->fetch();
                            $status = 'Issued';
                            if ($row['billingScheduleType'] == 'Scheduled') {
                                $separated = 'Y';
                                $invoiceDueDate = $row['invoiceDueDateScheduled'];
                            } else {
                                $separated = null;
                                $invoiceDueDate = $row['invoiceDueDate'];
                            }
                            $invoiceIssueDate = date('Y-m-d');

                            if ($invoiceDueDate == '') {
                                $partialFail = true;
                            } else {
                                //Write to database
                                try {
                                    $data = array('status' => $status, 'separated' => $separated, 'invoiceDueDate' => $invoiceDueDate, 'invoiceIssueDate' => $invoiceIssueDate, 'pupilsightPersonIDUpdate' => $_SESSION[$guid]['pupilsightPersonID'], 'pupilsightFinanceInvoiceID' => $pupilsightFinanceInvoiceID);
                                    $sql = "UPDATE pupilsightFinanceInvoice SET status=:status, separated=:separated, invoiceDueDate=:invoiceDueDate, invoiceIssueDate=:invoiceIssueDate, pupilsightPersonIDUpdate=:pupilsightPersonIDUpdate, timestampUpdate='".date('Y-m-d H:i:s')."' WHERE pupilsightFinanceInvoiceID=:pupilsightFinanceInvoiceID";
                                    $result = $connection2->prepare($sql);
                                    $result->execute($data);
                                } catch (PDOException $e) {
                                    $URL .= '&return=error2';
                                    header("Location: {$URL}");
                                    exit();
                                }

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
                            }
                        }
                    }
                }

                //Unlock invoice table
                try {
                    $sql = 'UNLOCK TABLES';
                    $result = $connection2->query($sql);
                } catch (PDOException $e) {}

                if ($action == 'issue') {
                    //Loop through invoices again, this time to send invoices....they can not be sent in first loop due to table locking issues.
                    foreach ($pupilsightFinanceInvoiceIDs as $pupilsightFinanceInvoiceID) {
                        try {
                            $data = array('pupilsightSchoolYearID' => $pupilsightSchoolYearID, 'pupilsightFinanceInvoiceID' => $pupilsightFinanceInvoiceID);
                            $sql = 'SELECT pupilsightFinanceInvoice.*, pupilsightFinanceBillingSchedule.invoiceDueDate AS invoiceDueDateScheduled FROM pupilsightFinanceInvoice LEFT JOIN pupilsightFinanceBillingSchedule ON (pupilsightFinanceInvoice.pupilsightFinanceBillingScheduleID=pupilsightFinanceBillingSchedule.pupilsightFinanceBillingScheduleID) WHERE pupilsightFinanceInvoice.pupilsightSchoolYearID=:pupilsightSchoolYearID AND pupilsightFinanceInvoiceID=:pupilsightFinanceInvoiceID';
                            $result = $connection2->prepare($sql);
                            $result->execute($data);
                        } catch (PDOException $e) {
                        }

                        $emails = array();
                        $emailsCount = 0;

                        if ($result->rowCount() != 1) {
                            $emailFail = true;
                        } else {
                            $row = $result->fetch();

                            //DEAL WITH EMAILS
                            if ($row['invoiceTo'] == 'Company') {
                                try {
                                    $dataCompany = array('pupilsightFinanceInvoiceeID' => $row['pupilsightFinanceInvoiceeID']);
                                    $sqlCompany = 'SELECT * FROM pupilsightFinanceInvoicee WHERE pupilsightFinanceInvoiceeID=:pupilsightFinanceInvoiceeID';
                                    $resultCompany = $connection2->prepare($sqlCompany);
                                    $resultCompany->execute($dataCompany);
                                } catch (PDOException $e) {
                                    $emailFail = true;
                                }
                                if ($resultCompany->rowCount() != 1) {
                                    $emailFail = true;
                                } else {
                                    $rowCompany = $resultCompany->fetch();
                                    if ($rowCompany['companyEmail'] != '' and $rowCompany['companyContact'] != '' and $rowCompany['companyName'] != '') {
                                        $emailsInner = explode(',', $rowCompany['companyEmail']);
                                        for ($n = 0; $n < count($emailsInner); ++$n) {
                                            if ($n == 0) {
                                                $emails[$emailsCount] = trim($emailsInner[$n]);
                                                ++$emailsCount;
                                            } else {
                                                array_push($emails, trim($emailsInner[$n]));
                                                ++$emailsCount;
                                            }
                                        }
                                        if ($rowCompany['companyCCFamily'] == 'Y') {
                                            try {
                                                $dataParents = array('pupilsightFinanceInvoiceeID' => $row['pupilsightFinanceInvoiceeID']);
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
                                    $dataParents = array('pupilsightFinanceInvoiceeID' => $row['pupilsightFinanceInvoiceeID']);
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

                            if ($from == '' or count($emails) < 1) {
                                $emailFail = true;
                            } else {
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
                                    //Set log
                                    $pupilsightModuleID=getModuleIDFromName($connection2, 'Finance') ;
                                    $logArray=array() ;
                                    $logArray['recipients'] = is_array($emails) ? implode(',', $emails) : '' ;
                                    setLog($connection2, $_SESSION[$guid]["pupilsightSchoolYearID"], $pupilsightModuleID, $_SESSION[$guid]["pupilsightPersonID"], 'Finance - Bulk Invoice Issue Email Failure', $logArray) ;
                                }
                            }
                        }
                    }
                }

                if ($partialFail == true) {
                    $URL .= '&return=warning1';
                    header("Location: {$URL}");
                } elseif ($emailFail == true) {
                    $URL .= '&return=success1';
                    header("Location: {$URL}");
                } else {
                    $URL .= '&return=success0';
                    header("Location: {$URL}");
                }
            }
            //REMINDERS
            elseif ($action == 'reminders') {
                foreach ($pupilsightFinanceInvoiceIDs as $pupilsightFinanceInvoiceID) {
                    try {
                        $data = array('pupilsightSchoolYearID' => $pupilsightSchoolYearID, 'pupilsightFinanceInvoiceID' => $pupilsightFinanceInvoiceID);
                        $sql = "SELECT pupilsightFinanceInvoice.*, pupilsightFinanceBillingSchedule.invoiceDueDate AS invoiceDueDateScheduled FROM pupilsightFinanceInvoice LEFT JOIN pupilsightFinanceBillingSchedule ON (pupilsightFinanceInvoice.pupilsightFinanceBillingScheduleID=pupilsightFinanceBillingSchedule.pupilsightFinanceBillingScheduleID) WHERE pupilsightFinanceInvoice.pupilsightSchoolYearID=:pupilsightSchoolYearID AND pupilsightFinanceInvoiceID=:pupilsightFinanceInvoiceID AND (status='Issued' OR status='Paid - Partial')";
                        $result = $connection2->prepare($sql);
                        $result->execute($data);
                    } catch (PDOException $e) {
                    }

                    $emailFail = false;
                    $emails = array();
                    $emailsCount = 0;

                    if ($result->rowCount() != 1) {
                        $partialFail = true;
                    } else {
                        $row = $result->fetch();

                        //DEAL WITH EMAILS
                        if ($row['invoiceTo'] == 'Company') {
                            try {
                                $dataCompany = array('pupilsightFinanceInvoiceeID' => $row['pupilsightFinanceInvoiceeID']);
                                $sqlCompany = 'SELECT * FROM pupilsightFinanceInvoicee WHERE pupilsightFinanceInvoiceeID=:pupilsightFinanceInvoiceeID';
                                $resultCompany = $connection2->prepare($sqlCompany);
                                $resultCompany->execute($dataCompany);
                            } catch (PDOException $e) {
                                $emailFail = true;
                            }
                            if ($resultCompany->rowCount() != 1) {
                                $emailFail = true;
                            } else {
                                $rowCompany = $resultCompany->fetch();
                                if ($rowCompany['companyEmail'] != '' and $rowCompany['companyContact'] != '' and $rowCompany['companyName'] != '') {
                                    $emails[$emailsCount] = $rowCompany['companyEmail'];
                                    ++$emailsCount;
                                    if ($rowCompany['companyCCFamily'] == 'Y') {
                                        try {
                                            $dataParents = array('pupilsightFinanceInvoiceeID' => $row['pupilsightFinanceInvoiceeID']);
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
                                $dataParents = array('pupilsightFinanceInvoiceeID' => $row['pupilsightFinanceInvoiceeID']);
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
                    }

                    if ($from == '' or count($emails) < 1) {
                        $emailFail = true;
                    } else {
                        //Prep message
                        $body = '';
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
                            //Set log
                            $pupilsightModuleID=getModuleIDFromName($connection2, 'Finance') ;
                            $logArray=array() ;
                            $logArray['recipients'] = is_array($emails) ? implode(',', $emails) : '' ;
                            setLog($connection2, $_SESSION[$guid]["pupilsightSchoolYearID"], $pupilsightModuleID, $_SESSION[$guid]["pupilsightPersonID"], 'Finance - Bulk Invoice Reminder Email Failure', $logArray) ;
                        }
                    }
                }

                if ($partialFail == true) {
                    $URL .= '&return=warning1';
                    header("Location: {$URL}");
                } elseif ($emailFail == true) {
                    $URL .= '&return=success1';
                    header("Location: {$URL}");
                } else {
                    $URL .= '&return=success0';
                    header("Location: {$URL}");
                }
            }
            //Export
            elseif ($action == 'export') {
                $_SESSION[$guid]['financeInvoiceExportIDs'] = $pupilsightFinanceInvoiceIDs;

				include ('./invoices_manage_processBulkExportContents.php');
            }
            // Mark as Paid
            elseif ($action == 'paid') {
                $paymentType = isset($_POST['paymentType'])? $_POST['paymentType'] : '';
                $paidDate = isset($_POST['paidDate'])?dateConvert($guid, $_POST['paidDate']) : '';

                if (empty($paymentType) || empty($paidDate)) {
                    $URL .= '&return=error1';
                    header("Location: {$URL}");
                    exit;
                }

                $partialFail = false;
                foreach ($pupilsightFinanceInvoiceIDs as $pupilsightFinanceInvoiceID) {
                    $totalFee = getInvoiceTotalFee($pdo, $pupilsightFinanceInvoiceID, 'Issued');
                    $alreadyPaid = getAmountPaid($connection2, $guid, 'pupilsightFinanceInvoice', $pupilsightFinanceInvoiceID);

                    $paidAmount = $totalFee - $alreadyPaid;

                    if (empty($paidAmount) || $paidAmount <= 0) {
                        $partialFail = true;
                    } else {
                        $logFail = setPaymentLog($connection2, $guid, 'pupilsightFinanceInvoice', $pupilsightFinanceInvoiceID, $paymentType, 'Complete', $paidAmount, null, null, null, null, null, null, $paidDate);
                        if ($logFail == false) {
                            $partialFail = true;
                        } else {
                            try {
                                $data = array('pupilsightFinanceInvoiceID' => $pupilsightFinanceInvoiceID, 'paidDate' => $paidDate, 'paidAmount' => $paidAmount, 'timestampUpdate' => date('Y-m-d H:i:s'), 'pupilsightPersonIDUpdate' => $_SESSION[$guid]['pupilsightPersonID']);
                                $sql = "UPDATE pupilsightFinanceInvoice SET status='Paid', paidDate=:paidDate, paidAmount=:paidAmount, pupilsightPersonIDUpdate=:pupilsightPersonIDUpdate, timestampUpdate=:timestampUpdate WHERE pupilsightFinanceInvoiceID=:pupilsightFinanceInvoiceID";
                                $result = $connection2->prepare($sql);
                                $result->execute($data);
                            } catch (PDOException $e) {
                                $partialFail = true;
                            }
                        }
                    }
                }

                if ($partialFail == true) {
                    $URL .= '&return=warning1';
                    header("Location: {$URL}");
                } else {
                    $URL .= '&return=success0';
                    header("Location: {$URL}");
                }

            } else {
                $URL .= '&return=error1';
                header("Location: {$URL}");
            }
        }
    }
}
