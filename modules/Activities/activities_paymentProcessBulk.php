<?php
/*
Pupilsight, Flexible & Open School System
*/

include '../../pupilsight.php';

$action = isset($_POST['action'])? $_POST['action'] : '';

$URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_POST['address']).'/activities_payment.php';

if (isActionAccessible($guid, $connection2, '/modules/Activities/activities_payment.php') == false) {
    $URL .= '&return=error0';
    header("Location: {$URL}");
} else {
    $pupilsightActivityStudentIDList = isset($_POST['pupilsightActivityStudentID'])? $_POST['pupilsightActivityStudentID'] : array();
    $payment = isset($_POST['payment'])? $_POST['payment'] : array();

    $students = array();
    foreach ($pupilsightActivityStudentIDList as $id => $pupilsightActivityStudentID) {
        $students[$id][0] = $pupilsightActivityStudentID;
        $students[$id][1] = isset($payment[$id])? $payment[$id] : 0.00;
    }

    //Proceed!
    //Check if person specified
    if (empty($action) || count($students) <= 0) {
        $URL .= '&return=erorr1';
        header("Location: {$URL}");
        exit;
    } else {
        //LOCK TABLES
        try {
            $data = array();
            $sql = 'LOCK TABLES pupilsightFinanceBillingSchedule WRITE, pupilsightFinanceInvoicee WRITE, pupilsightFinanceInvoice WRITE, pupilsightFinanceInvoiceFee WRITE, pupilsightActivity WRITE, pupilsightActivityStudent WRITE, pupilsightActivity AS pupilsightActivity2 WRITE, pupilsightActivityStudent AS pupilsightActivityStudent2 WRITE, pupilsightActivity AS pupilsightActivity3 WRITE, pupilsightActivityStudent AS pupilsightActivityStudent3 WRITE';
            $result = $connection2->prepare($sql);
            $result->execute($data);
        } catch (PDOException $e) {
            $URL .= '&error=error2';
            header("Location: {$URL}");
            exit();
        }

        $partialFail = false;
        if ($action == 'Generate Invoice - Simulate') {
            foreach ($students as $student) {
                $pupilsightActivityStudentID = $student[0];

                //Write generation back to pupilsightActivityStudent
                try {
                    $data = array('pupilsightActivityStudentID' => $pupilsightActivityStudentID);
                    $sql = "UPDATE pupilsightActivityStudent SET invoiceGenerated='Y' WHERE pupilsightActivityStudentID=:pupilsightActivityStudentID";
                    $result = $connection2->prepare($sql);
                    $result->execute($data);
                } catch (PDOException $e) {
                    $partialFail = true;
                }
            }
        } else {
            // Check billing schedule specified exists in the current year
            $checkFail = false;
            try {
                $dataCheck = array('pupilsightFinanceBillingScheduleID' => $action);
                $sqlCheck = 'SELECT pupilsightFinanceBillingScheduleID FROM pupilsightFinanceBillingSchedule WHERE pupilsightFinanceBillingScheduleID=:pupilsightFinanceBillingScheduleID';
                $resultCheck = $connection2->prepare($sqlCheck);
                $resultCheck->execute($dataCheck);
            } catch (PDOException $e) {
                $checkFail = true;
                $partialFail = true;
            }

            if ($checkFail == false) {
                foreach ($students as $student) {
                    $pupilsightActivityStudentID = $student[0];
                    $payment = $student[1];

                    //Check student is invoicee
                    $checkFail2 = false;
                    try {
                        $dataCheck2 = array('pupilsightActivityStudentID' => $pupilsightActivityStudentID);
                        $sqlCheck2 = 'SELECT * FROM pupilsightFinanceInvoicee WHERE pupilsightPersonID=(SELECT pupilsightPersonID FROM pupilsightActivityStudent WHERE pupilsightActivityStudentID=:pupilsightActivityStudentID)';
                        $resultCheck2 = $connection2->prepare($sqlCheck2);
                        $resultCheck2->execute($dataCheck2);
                    } catch (PDOException $e) {
                        $checkFail2 = true;
                        $partialFail = true;
                    }

                    if ($checkFail2 == false) {
                        if ($resultCheck2->rowCount() != 1) {
                            $partialFail = true;
                        } else {
                            $rowCheck2 = $resultCheck2->fetch();

                            //Check for existing pending invoice for this student in this billing schedule
                            $checkFail3 = false;
                            try {
                                $dataCheck3 = array('pupilsightFinanceBillingScheduleID' => $action, 'pupilsightFinanceInvoiceeID' => $rowCheck2['pupilsightFinanceInvoiceeID']);
                                $sqlCheck3 = "SELECT * FROM pupilsightFinanceInvoice WHERE pupilsightFinanceBillingScheduleID=:pupilsightFinanceBillingScheduleID AND pupilsightFinanceInvoiceeID=:pupilsightFinanceInvoiceeID AND status='Pending'";
                                $resultCheck3 = $connection2->prepare($sqlCheck3);
                                $resultCheck3->execute($dataCheck3);
                            } catch (PDOException $e) {
                                $checkFail3 = true;
                                $partialFail = true;
                            }

                            if ($checkFail3 == false) {
                                if ($resultCheck3->rowCount() == 0) { //No invoice, so create it
                                    //CREATE NEW INVOICE
                                    //Make and store unique code for confirmation
                                    $key = '';
                                    $continue = false;
                                    $count = 0;
                                    while ($continue == false and $count < 100) {
                                        $key = randomPassword(40);
                                        try {
                                            $dataUnique = array('key' => $key);
                                            $sqlUnique = 'SELECT * FROM pupilsightFinanceInvoice WHERE pupilsightFinanceInvoice.`key`=:key';
                                            $resultUnique = $connection2->prepare($sqlUnique);
                                            $resultUnique->execute($dataUnique);
                                        } catch (PDOException $e) {
                                        }

                                        if ($resultUnique->rowCount() == 0) {
                                            $continue = true;
                                        }
                                        ++$count;
                                    }

                                    if ($continue == false) {
                                        $partialFail = true;
                                    } else {
                                        $invoiceFail = false;
                                        try {
                                            $dataInvoice = array('pupilsightSchoolYearID' => $_SESSION[$guid]['pupilsightSchoolYearID'], 'pupilsightFinanceInvoiceeID' => $rowCheck2['pupilsightFinanceInvoiceeID'], 'pupilsightFinanceBillingScheduleID' => $action, 'notes' => '', 'key' => $key, 'pupilsightPersonIDCreator' => $_SESSION[$guid]['pupilsightPersonID']);
                                            $sqlInvoice = "INSERT INTO pupilsightFinanceInvoice SET pupilsightSchoolYearID=:pupilsightSchoolYearID, pupilsightFinanceInvoiceeID=:pupilsightFinanceInvoiceeID, invoiceTo='Family', billingScheduleType='Scheduled', pupilsightFinanceBillingScheduleID=:pupilsightFinanceBillingScheduleID, notes=:notes, `key`=:key, status='Pending', separated='N', pupilsightPersonIDCreator=:pupilsightPersonIDCreator, timeStampCreator='".date('Y-m-d H:i:s')."'";
                                            $resultInvoice = $connection2->prepare($sqlInvoice);
                                            $resultInvoice->execute($dataInvoice);
                                        } catch (PDOException $e) {
                                            $invoiceFail = true;
                                            $partialFail = true;
                                        }

                                        if ($invoiceFail == false) {
                                            //Get invoice ID
                                            $pupilsightFinanceInvoiceID = str_pad($connection2->lastInsertID(), 14, '0', STR_PAD_LEFT);

                                            //Add fees to invoice
                                            $invoiceFail2 = false;
                                            try {
                                                $dataInvoiceFee = array('pupilsightFinanceInvoiceID' => $pupilsightFinanceInvoiceID, 'feeType' => 'Ad Hoc', 'name' => 'Activity Fee', 'pupilsightActivityStudentID' => $pupilsightActivityStudentID, 'pupilsightFinanceFeeCategoryID' => 1, 'fee' => $payment);
                                                $sqlInvoiceFee = 'INSERT INTO pupilsightFinanceInvoiceFee
                                                    SET pupilsightFinanceInvoiceID=:pupilsightFinanceInvoiceID,
                                                        feeType=:feeType,
                                                        name=:name,
                                                        description=(SELECT pupilsightActivity.name FROM pupilsightActivity JOIN pupilsightActivityStudent ON (pupilsightActivityStudent.pupilsightActivityID=pupilsightActivity.pupilsightActivityID) WHERE pupilsightActivityStudentID=:pupilsightActivityStudentID),
                                                        pupilsightFinanceFeeCategoryID=:pupilsightFinanceFeeCategoryID,
                                                        fee=:fee,
                                                        sequenceNumber=0';
                                                $resultInvoiceFee = $connection2->prepare($sqlInvoiceFee);
                                                $resultInvoiceFee->execute($dataInvoiceFee);
                                            } catch (PDOException $e) {
                                                $invoiceFai2 = true;
                                                $partialFail = true;
                                            }

                                            if ($invoiceFail2 == false) {
                                                //Write invoice and generation back to pupilsightActivityStudent
                                                try {
                                                    $data = array('pupilsightFinanceInvoiceID' => $pupilsightFinanceInvoiceID, 'pupilsightActivityStudentID' => $pupilsightActivityStudentID);
                                                    $sql = "UPDATE pupilsightActivityStudent SET invoiceGenerated='Y', pupilsightFinanceInvoiceID=:pupilsightFinanceInvoiceID WHERE pupilsightActivityStudentID=:pupilsightActivityStudentID";
                                                    $result = $connection2->prepare($sql);
                                                    $result->execute($data);
                                                } catch (PDOException $e) {
                                                    $partialFail = true;
                                                }
                                            }
                                        }
                                    }
                                } elseif ($resultCheck3->rowCount() == 1) { //Yes invoice, so update it
                                    $rowCheck3 = $resultCheck3->fetch();

                                    //Get invoice ID
                                    $pupilsightFinanceInvoiceID = $rowCheck3['pupilsightFinanceInvoiceID'];

                                    //Add fees to invoice
                                    $invoiceFail2 = false;
                                    try {
                                        $dataInvoiceFee = array('pupilsightFinanceInvoiceID' => $pupilsightFinanceInvoiceID, 'feeType' => 'Ad Hoc', 'name' => 'Activity Fee', 'pupilsightActivityStudentID' => $pupilsightActivityStudentID, 'pupilsightFinanceFeeCategoryID' => 1, 'pupilsightActivityStudentID2' => $pupilsightActivityStudentID);
                                        $sqlInvoiceFee = 'INSERT INTO pupilsightFinanceInvoiceFee SET pupilsightFinanceInvoiceID=:pupilsightFinanceInvoiceID, feeType=:feeType, name=:name, description=(SELECT pupilsightActivity3.name FROM pupilsightActivity AS pupilsightActivity3 JOIN pupilsightActivityStudent AS pupilsightActivityStudent3 ON (pupilsightActivityStudent3.pupilsightActivityID=pupilsightActivity3.pupilsightActivityID) WHERE pupilsightActivityStudentID=:pupilsightActivityStudentID), pupilsightFinanceFeeCategoryID=:pupilsightFinanceFeeCategoryID, fee=(SELECT pupilsightActivity.payment FROM pupilsightActivity JOIN pupilsightActivityStudent ON (pupilsightActivityStudent.pupilsightActivityID=pupilsightActivity.pupilsightActivityID) WHERE pupilsightActivityStudentID=:pupilsightActivityStudentID2), sequenceNumber=0';
                                        $resultInvoiceFee = $connection2->prepare($sqlInvoiceFee);
                                        $resultInvoiceFee->execute($dataInvoiceFee);
                                    } catch (PDOException $e) {
                                        $invoiceFai2 = true;
                                        $partialFail = true;
                                    }

                                    if ($invoiceFail2 == false) {
                                        //Write invoice and generation back to pupilsightActivityStudent
                                        try {
                                            $data = array('pupilsightFinanceInvoiceID' => $pupilsightFinanceInvoiceID, 'pupilsightActivityStudentID' => $pupilsightActivityStudentID);
                                            $sql = "UPDATE pupilsightActivityStudent SET invoiceGenerated='Y', pupilsightFinanceInvoiceID=:pupilsightFinanceInvoiceID WHERE pupilsightActivityStudentID=:pupilsightActivityStudentID";
                                            $result = $connection2->prepare($sql);
                                            $result->execute($data);
                                        } catch (PDOException $e) {
                                            $partialFail = true;
                                        }
                                    }
                                } else { //Return error
                                    $partialFail = true;
                                }
                            }
                        }
                    }
                }
            }
        }

        //Unlock module table
        try {
            $sql = 'UNLOCK TABLES';
            $result = $connection2->query($sql);
        } catch (PDOException $e) {
        }

        if ($partialFail == true) {
            $URL .= '&return=warning1';
            header("Location: {$URL}");
        } else {
            $URL .= '&return=success0';
            header("Location: {$URL}");
        }
    }
}

