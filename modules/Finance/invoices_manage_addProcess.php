<?php
/*
Pupilsight, Flexible & Open School System
*/

include '../../pupilsight.php';

$pupilsightSchoolYearID = $_GET['pupilsightSchoolYearID'];
$status = $_GET['status'];
$pupilsightFinanceInvoiceeID = $_GET['pupilsightFinanceInvoiceeID'];
$monthOfIssue = $_GET['monthOfIssue'];
$pupilsightFinanceBillingScheduleID = $_GET['pupilsightFinanceBillingScheduleID'];
$pupilsightFinanceFeeCategoryID = $_GET['pupilsightFinanceFeeCategoryID'];

if ($pupilsightSchoolYearID == '') { echo 'Fatal error loading this page!';
} else {
    $URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_POST['address'])."/invoices_manage_add.php&pupilsightSchoolYearID=$pupilsightSchoolYearID&status=$status&pupilsightFinanceInvoiceeID=$pupilsightFinanceInvoiceeID&monthOfIssue=$monthOfIssue&pupilsightFinanceBillingScheduleID=$pupilsightFinanceBillingScheduleID&pupilsightFinanceFeeCategoryID=$pupilsightFinanceFeeCategoryID";

    if (isActionAccessible($guid, $connection2, '/modules/Finance/invoices_manage_add.php') == false) {
        $URL .= '&return=error0';
        header("Location: {$URL}");
    } else {
        $pupilsightFinanceInvoiceeIDs = $_POST['pupilsightFinanceInvoiceeIDs'];
        $scheduling = $_POST['scheduling'];
        if ($scheduling == 'Scheduled') {
            $pupilsightFinanceBillingScheduleID = $_POST['pupilsightFinanceBillingScheduleID'];
            $invoiceDueDate = null;
        } elseif ($scheduling == 'Ad Hoc') {
            $pupilsightFinanceBillingScheduleID = null;
            $invoiceDueDate = $_POST['invoiceDueDate'];
        }
        $notes = $_POST['notes'];
        $order = isset($_POST['order'])? $_POST['order'] : array();

        if (count($pupilsightFinanceInvoiceeIDs) == 0 or $scheduling == '' or ($scheduling == 'Scheduled' and $pupilsightFinanceBillingScheduleID == '') or ($scheduling == 'Ad Hoc' and $invoiceDueDate == '') or count($order) == 0) {
            $URL .= '&return=error1';
            header("Location: {$URL}");
        } else {
            $studentFailCount = 0;
            $invoiceFailCount = 0;
            $invoiceFeeFailCount = 0;
            $feeFail = false;

            //PROCESS FEES
            $fees = array();
            foreach ($order as $fee) {
                $fees[$fee]['name'] = $_POST['name'.$fee];
                $fees[$fee]['pupilsightFinanceFeeCategoryID'] = $_POST['pupilsightFinanceFeeCategoryID'.$fee];
                $fees[$fee]['fee'] = $_POST['fee'.$fee];
                $fees[$fee]['feeType'] = $_POST['feeType'.$fee];
                $fees[$fee]['pupilsightFinanceFeeID'] = $_POST['pupilsightFinanceFeeID'.$fee];
                $fees[$fee]['description'] = $_POST['description'.$fee];

                if ($fees[$fee]['name'] == '' or $fees[$fee]['pupilsightFinanceFeeCategoryID'] == '' or $fees[$fee]['fee'] == '' or is_numeric($fees[$fee]['fee']) == false or $fees[$fee]['feeType'] == '' or ($fees[$fee]['feeType'] == 'Standard' and $fees[$fee]['pupilsightFinanceFeeID'] == '')) {
                    $feeFail = true;
                }
            }

            if ($feeFail == true) {
                $URL .= '&return=error1';
                header("Location: {$URL}");
                exit();
            } else {
                //LOCK INVOICE TABLES
                try {
                    $data = array();
                    $sql = 'LOCK TABLES pupilsightFinanceInvoice WRITE, pupilsightFinanceInvoiceFee WRITE, pupilsightFinanceInvoicee WRITE';
                    $result = $connection2->prepare($sql);
                    $result->execute($data);
                } catch (PDOException $e) {
                    $URL .= '&return=error2';
                    header("Location: {$URL}");
                    exit();
                }

                //CYCLE THROUGH STUDENTS
                foreach ($pupilsightFinanceInvoiceeIDs as $pupilsightFinanceInvoiceeID) {
                    $thisStudentFailed = false;
                    $invoiceTo = '';
                    $companyAll = '';
                    $pupilsightFinanceFeeCategoryIDList2 = '';

                    //GET INVOICE RECORD, set $invoiceTo and $companyCategories if required
                    try {
                        $data = array('pupilsightFinanceInvoiceeID' => $pupilsightFinanceInvoiceeID);
                        $sql = 'SELECT * FROM pupilsightFinanceInvoicee WHERE pupilsightFinanceInvoiceeID=:pupilsightFinanceInvoiceeID';
                        $result = $connection2->prepare($sql);
                        $result->execute($data);
                    } catch (PDOException $e) {
                        ++$studentFailCount;
                        $thisStudentFailed = true;
                    }
                    if ($result->rowCount() != 1) {
                        if ($thisStudentFailed != true) {
                            ++$studentFailCount;
                            $thisStudentFailed = true;
                        }
                    } else {
                        $row = $result->fetch();
                        $invoiceTo = $row['invoiceTo'];
                        if ($invoiceTo != 'Family' and $invoiceTo != 'Company') {
                            ++$studentFailCount;
                            $thisStudentFailed = true;
                        } else {
                            if ($invoiceTo == 'Company') {
                                $companyAll = $row['companyAll'];
                                if ($companyAll == 'N') {
                                    $pupilsightFinanceFeeCategoryIDList2 = $row['pupilsightFinanceFeeCategoryIDList'];
                                    if ($pupilsightFinanceFeeCategoryIDList2 != '') {
                                        $pupilsightFinanceFeeCategoryIDs = explode(',', $pupilsightFinanceFeeCategoryIDList2);
                                    } else {
                                        $pupilsightFinanceFeeCategoryIDs = null;
                                    }
                                }

                                $companyFamily = false; //This holds true when company is set, companyAll=N and there are some fees for the family to pay...
                                foreach ($fees as $fee) {
                                    if ($invoiceTo == 'Company' and $companyAll == 'N' and strpos($pupilsightFinanceFeeCategoryIDList2, $fee['pupilsightFinanceFeeCategoryID']) === false) {
                                        $companyFamily = true;
                                    }
                                }
                                $companyFamilyCompanyHasCharges = false; //This holds true when company is set, companyAll=N and there are some fees for the company to pay...e.g.  they are not all held by the family
                                if ($invoiceTo == 'Company' and $companyAll == 'N') {
                                    foreach ($fees as $fee) {
                                        if ($invoiceTo == 'Company' and $companyAll == 'N' and is_numeric(strpos($pupilsightFinanceFeeCategoryIDList2, $fee['pupilsightFinanceFeeCategoryID']))) {
                                            $companyFamilyCompanyHasCharges = true;
                                        }
                                    }
                                }
                            }
                        }
                    }

                    if ($thisStudentFailed == false) {
                        //CHECK FOR INVOICE AND UPDATE/ADD FOR FAMILY (INC WHEN COMPANY IS PAYING ONLY SOME FEES)
                        if ($invoiceTo == 'Family' or $companyFamily == true) {
                            $thisInvoiceFailed = false;
                            try {
                                if ($scheduling == 'Scheduled') {
                                    $dataInvoice = array('pupilsightSchoolYearID' => $pupilsightSchoolYearID, 'pupilsightFinanceInvoiceeID' => $pupilsightFinanceInvoiceeID, 'pupilsightFinanceBillingScheduleID' => $pupilsightFinanceBillingScheduleID);
                                    $sqlInvoice = "SELECT * FROM pupilsightFinanceInvoice WHERE pupilsightSchoolYearID=:pupilsightSchoolYearID AND pupilsightFinanceInvoiceeID=:pupilsightFinanceInvoiceeID AND invoiceTo='Family' AND billingScheduleType='Scheduled' AND pupilsightFinanceBillingScheduleID=:pupilsightFinanceBillingScheduleID AND status='Pending'";
                                } else {
                                    $dataInvoice = array('pupilsightSchoolYearID' => $pupilsightSchoolYearID, 'pupilsightFinanceInvoiceeID' => $pupilsightFinanceInvoiceeID);
                                    $sqlInvoice = "SELECT * FROM pupilsightFinanceInvoice WHERE pupilsightSchoolYearID=:pupilsightSchoolYearID AND pupilsightFinanceInvoiceeID=:pupilsightFinanceInvoiceeID AND invoiceTo='Family' AND billingScheduleType='Ad Hoc' AND status='Pending'";
                                }
                                $resultInvoice = $connection2->prepare($sqlInvoice);
                                $resultInvoice->execute($dataInvoice);
                            } catch (PDOException $e) {
                                ++$invoiceFailCount;
                                $thisInvoiceFailed = true;
                            }
                            if ($resultInvoice->rowCount() == 0 and $thisInvoiceFailed == false) {
                                //ADD INVOICE
                                //Get next autoincrement
                                try {
                                    $dataAI = array();
                                    $sqlAI = "SHOW TABLE STATUS LIKE 'pupilsightFinanceInvoice'";
                                    $resultAI = $connection2->prepare($sqlAI);
                                    $resultAI->execute($dataAI);
                                } catch (PDOException $e) {
                                    ++$invoiceFailCount;
                                    $thisInvoiceFailed = true;
                                }
                                if ($resultAI->rowCount() == 1) {
                                    $rowAI = $resultAI->fetch();
                                    $AI = str_pad($rowAI['Auto_increment'], 14, '0', STR_PAD_LEFT);
                                }

                                if ($AI == '') {
                                    if ($thisInvoiceFailed == false) {
                                        ++$invoiceFailCount;
                                        $thisInvoiceFailed = true;
                                    }
                                } else {
                                    //Add invoice
                                    //Make and store unique code for confirmation. add it to email text.
                                    $key = '';

                                    //Let's go! Create key, send the invite
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
                                        $URL .= '&return=error2';
                                        header("Location: {$URL}");
                                        exit();
                                    } else {
                                        try {
                                            if ($scheduling == 'Scheduled') {
                                                $dataInvoiceAdd = array('pupilsightSchoolYearID' => $pupilsightSchoolYearID, 'pupilsightFinanceInvoiceeID' => $pupilsightFinanceInvoiceeID, 'pupilsightFinanceBillingScheduleID' => $pupilsightFinanceBillingScheduleID, 'notes' => $notes, 'key' => $key, 'pupilsightPersonIDCreator' => $_SESSION[$guid]['pupilsightPersonID']);
                                                $sqlInvoiceAdd = "INSERT INTO pupilsightFinanceInvoice SET pupilsightSchoolYearID=:pupilsightSchoolYearID, pupilsightFinanceInvoiceeID=:pupilsightFinanceInvoiceeID, invoiceTo='Family', billingScheduleType='Scheduled', pupilsightFinanceBillingScheduleID=:pupilsightFinanceBillingScheduleID, notes=:notes, `key`=:key, status='Pending', separated='N', pupilsightPersonIDCreator=:pupilsightPersonIDCreator, timeStampCreator='".date('Y-m-d H:i:s')."'";
                                            } else {
                                                $dataInvoiceAdd = array('pupilsightSchoolYearID' => $pupilsightSchoolYearID, 'pupilsightFinanceInvoiceeID' => $pupilsightFinanceInvoiceeID, 'invoiceDueDate' => dateConvert($guid, $invoiceDueDate), 'notes' => $notes, 'key' => $key, 'pupilsightPersonIDCreator' => $_SESSION[$guid]['pupilsightPersonID']);
                                                $sqlInvoiceAdd = "INSERT INTO pupilsightFinanceInvoice SET pupilsightSchoolYearID=:pupilsightSchoolYearID, pupilsightFinanceInvoiceeID=:pupilsightFinanceInvoiceeID, invoiceTo='Family', billingScheduleType='Ad Hoc', status='Pending', invoiceDueDate=:invoiceDueDate, notes=:notes, `key`=:key, pupilsightPersonIDCreator=:pupilsightPersonIDCreator, timeStampCreator='".date('Y-m-d H:i:s')."'";
                                            }
                                            $resultInvoiceAdd = $connection2->prepare($sqlInvoiceAdd);
                                            $resultInvoiceAdd->execute($dataInvoiceAdd);
                                        } catch (PDOException $e) {
                                            echo $e->getMessage();
                                            ++$invoiceFailCount;
                                            $thisInvoiceFailed = true;
                                        }
                                        if ($thisInvoiceFailed == false) {
                                            //Add fees to invoice
                                            $count = 0;
                                            foreach ($fees as $fee) {
                                                ++$count;
                                                if ($invoiceTo == 'Family' or ($invoiceTo == 'Company' and $companyAll == 'N' and strpos($pupilsightFinanceFeeCategoryIDList2, $fee['pupilsightFinanceFeeCategoryID']) === false)) {
                                                    try {
                                                        if ($fee['feeType'] == 'Standard') {
                                                            $dataInvoiceFee = array('pupilsightFinanceInvoiceID' => $AI, 'feeType' => $fee['feeType'], 'pupilsightFinanceFeeID' => $fee['pupilsightFinanceFeeID']);
                                                            $sqlInvoiceFee = "INSERT INTO pupilsightFinanceInvoiceFee SET pupilsightFinanceInvoiceID=:pupilsightFinanceInvoiceID, feeType=:feeType, pupilsightFinanceFeeID=:pupilsightFinanceFeeID, separated='N', sequenceNumber=$count";
                                                        } else {
                                                            $dataInvoiceFee = array('pupilsightFinanceInvoiceID' => $AI, 'feeType' => $fee['feeType'], 'name' => $fee['name'], 'description' => $fee['description'], 'pupilsightFinanceFeeCategoryID' => $fee['pupilsightFinanceFeeCategoryID'], 'fee' => $fee['fee']);
                                                            $sqlInvoiceFee = "INSERT INTO pupilsightFinanceInvoiceFee SET pupilsightFinanceInvoiceID=:pupilsightFinanceInvoiceID, feeType=:feeType, name=:name, description=:description, pupilsightFinanceFeeCategoryID=:pupilsightFinanceFeeCategoryID, fee=:fee, sequenceNumber=$count";
                                                        }
                                                        $resultInvoiceFee = $connection2->prepare($sqlInvoiceFee);
                                                        $resultInvoiceFee->execute($dataInvoiceFee);
                                                    } catch (PDOException $e) {
                                                        ++$invoiceFeeFailCount;
                                                    }
                                                }
                                            }
                                        }
                                    }
                                }
                            } elseif ($resultInvoice->rowCount() == 1 and $thisInvoiceFailed == false) {
                                $rowInvoice = $resultInvoice->fetch();

                                //Add fees to invoice
                                $count = 0;
                                foreach ($fees as $fee) {
                                    ++$count;
                                    if ($invoiceTo == 'Family' or ($invoiceTo == 'Company' and $companyAll == 'N' and strpos($pupilsightFinanceFeeCategoryIDList2, $fee['pupilsightFinanceFeeCategoryID']) === false)) {
                                        try {
                                            if ($fee['feeType'] == 'Standard') {
                                                $dataInvoiceFee = array('pupilsightFinanceInvoiceID' => $rowInvoice['pupilsightFinanceInvoiceID'], 'feeType' => $fee['feeType'], 'pupilsightFinanceFeeID' => $fee['pupilsightFinanceFeeID']);
                                                $sqlInvoiceFee = "INSERT INTO pupilsightFinanceInvoiceFee SET pupilsightFinanceInvoiceID=:pupilsightFinanceInvoiceID, feeType=:feeType, pupilsightFinanceFeeID=:pupilsightFinanceFeeID, separated='N', sequenceNumber=$count";
                                            } else {
                                                $dataInvoiceFee = array('pupilsightFinanceInvoiceID' => $rowInvoice['pupilsightFinanceInvoiceID'], 'feeType' => $fee['feeType'], 'name' => $fee['name'], 'description' => $fee['description'], 'pupilsightFinanceFeeCategoryID' => $fee['pupilsightFinanceFeeCategoryID'], 'fee' => $fee['fee']);
                                                $sqlInvoiceFee = "INSERT INTO pupilsightFinanceInvoiceFee SET pupilsightFinanceInvoiceID=:pupilsightFinanceInvoiceID, feeType=:feeType, name=:name, description=:description, pupilsightFinanceFeeCategoryID=:pupilsightFinanceFeeCategoryID, fee=:fee, sequenceNumber=$count";
                                            }
                                            $resultInvoiceFee = $connection2->prepare($sqlInvoiceFee);
                                            $resultInvoiceFee->execute($dataInvoiceFee);
                                        } catch (PDOException $e) {
                                            ++$invoiceFeeFailCount;
                                        }
                                    }
                                }

                                //Update invoice
                                try {
                                    if ($scheduling == 'Scheduled') {
                                        $dataInvoiceAdd = array('pupilsightPersonIDUpdate' => $_SESSION[$guid]['pupilsightPersonID'], 'notes' => $rowInvoice['notes'].' '.$notes, 'pupilsightFinanceInvoiceID' => $rowInvoice['pupilsightFinanceInvoiceID']);
                                        $sqlInvoiceAdd = "UPDATE pupilsightFinanceInvoice SET pupilsightPersonIDUpdate=:pupilsightPersonIDUpdate, notes=:notes, timeStampUpdate='".date('Y-m-d H:i:s')."' WHERE pupilsightFinanceInvoiceID=:pupilsightFinanceInvoiceID";
                                    } else {
                                        $dataInvoiceAdd = array('invoiceDueDate' => dateConvert($guid, $invoiceDueDate), 'pupilsightPersonIDUpdate' => $_SESSION[$guid]['pupilsightPersonID'], 'notes' => $rowInvoice['notes'].' '.$notes, 'pupilsightFinanceInvoiceID' => $rowInvoice['pupilsightFinanceInvoiceID']);
                                        $sqlInvoiceAdd = "UPDATE pupilsightFinanceInvoice SET invoiceDueDate=:invoiceDueDate, pupilsightPersonIDUpdate=:pupilsightPersonIDUpdate, notes=:notes, timeStampUpdate='".date('Y-m-d H:i:s')."' WHERE pupilsightFinanceInvoiceID=:pupilsightFinanceInvoiceID";
                                    }
                                    $resultInvoiceAdd = $connection2->prepare($sqlInvoiceAdd);
                                    $resultInvoiceAdd->execute($dataInvoiceAdd);
                                } catch (PDOException $e) {
                                    ++$invoiceFailCount;
                                    $thisInvoiceFailed = true;
                                }
                            } else {
                                if ($thisInvoiceFailed == false) {
                                    ++$invoiceFailCount;
                                    $thisInvoiceFailed = true;
                                }
                            }
                        }

                        //CHECK FOR INVOICE AND UPDATE/ADD FOR COMPANY
                        if (($invoiceTo == 'Company' and $companyAll == 'Y') or ($invoiceTo == 'Company' and $companyAll == 'N' and $companyFamilyCompanyHasCharges == true)) {
                            $thisInvoiceFailed = false;
                            try {
                                if ($scheduling == 'Scheduled') {
                                    $dataInvoice = array('pupilsightSchoolYearID' => $pupilsightSchoolYearID, 'pupilsightFinanceInvoiceeID' => $pupilsightFinanceInvoiceeID, 'pupilsightFinanceBillingScheduleID' => $pupilsightFinanceBillingScheduleID);
                                    $sqlInvoice = "SELECT * FROM pupilsightFinanceInvoice WHERE pupilsightSchoolYearID=:pupilsightSchoolYearID AND pupilsightFinanceInvoiceeID=:pupilsightFinanceInvoiceeID AND invoiceTo='Company' AND billingScheduleType='Scheduled' AND pupilsightFinanceBillingScheduleID=:pupilsightFinanceBillingScheduleID AND status='Pending'";
                                } else {
                                    $dataInvoice = array('pupilsightSchoolYearID' => $pupilsightSchoolYearID, 'pupilsightFinanceInvoiceeID' => $pupilsightFinanceInvoiceeID);
                                    $sqlInvoice = "SELECT * FROM pupilsightFinanceInvoice WHERE pupilsightSchoolYearID=:pupilsightSchoolYearID AND pupilsightFinanceInvoiceeID=:pupilsightFinanceInvoiceeID AND invoiceTo='Company' AND billingScheduleType='Ad Hoc' AND status='Pending'";
                                }
                                $resultInvoice = $connection2->prepare($sqlInvoice);
                                $resultInvoice->execute($dataInvoice);
                            } catch (PDOException $e) {
                                ++$invoiceFailCount;
                                $thisInvoiceFailed = true;
                            }
                            if ($resultInvoice->rowCount() == 0 and $thisInvoiceFailed == false) {
                                //ADD INVOICE
                                //Get next autoincrement
                                try {
                                    $dataAI = array();
                                    $sqlAI = "SHOW TABLE STATUS LIKE 'pupilsightFinanceInvoice'";
                                    $resultAI = $connection2->prepare($sqlAI);
                                    $resultAI->execute($dataAI);
                                } catch (PDOException $e) {
                                    ++$invoiceFailCount;
                                    $thisInvoiceFailed = true;
                                }
                                if ($resultAI->rowCount() == 1) {
                                    $rowAI = $resultAI->fetch();
                                    $AI = str_pad($rowAI['Auto_increment'], 14, '0', STR_PAD_LEFT);
                                }

                                if ($AI == '') {
                                    if ($thisInvoiceFailed == false) {
                                        ++$invoiceFailCount;
                                        $thisInvoiceFailed = true;
                                    }
                                } else {
                                    //Add invoice
                                    //Make and store unique code for confirmation. add it to email text.
                                    $key = '';

                                    //Let's go! Create key, send the invite
                                    $continue = false;
                                    $count = 0;
                                    while ($continue == false and $count < 100) {
                                        $key = randomPassword(40);
                                        try {
                                            $dataUnique = array('key' => $key);
                                            $sqlUnique = 'SELECT * FROM pupilsightFinanceInvoice WHERE pupilsightFinanceInvoice.`key`=key';
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
                                        $URL .= '&return=error2';
                                        header("Location: {$URL}");
                                        exit();
                                    } else {
                                        try {
                                            if ($scheduling == 'Scheduled') {
                                                $dataInvoiceAdd = array('pupilsightSchoolYearID' => $pupilsightSchoolYearID, 'pupilsightFinanceInvoiceeID' => $pupilsightFinanceInvoiceeID, 'pupilsightFinanceBillingScheduleID' => $pupilsightFinanceBillingScheduleID, 'notes' => $notes, 'key' => $key, 'pupilsightPersonIDCreator' => $_SESSION[$guid]['pupilsightPersonID']);
                                                $sqlInvoiceAdd = "INSERT INTO pupilsightFinanceInvoice SET pupilsightSchoolYearID=:pupilsightSchoolYearID, pupilsightFinanceInvoiceeID=:pupilsightFinanceInvoiceeID, invoiceTo='Company', billingScheduleType='Scheduled', pupilsightFinanceBillingScheduleID=:pupilsightFinanceBillingScheduleID, notes=:notes, `key`=:key, status='Pending', separated='N', pupilsightPersonIDCreator=:pupilsightPersonIDCreator, timeStampCreator='".date('Y-m-d H:i:s')."'";
                                            } else {
                                                $dataInvoiceAdd = array('pupilsightSchoolYearID' => $pupilsightSchoolYearID, 'pupilsightFinanceInvoiceeID' => $pupilsightFinanceInvoiceeID, 'invoiceDueDate' => dateConvert($guid, $invoiceDueDate), 'notes' => $notes, 'key' => $key, 'pupilsightPersonIDCreator' => $_SESSION[$guid]['pupilsightPersonID']);
                                                $sqlInvoiceAdd = "INSERT INTO pupilsightFinanceInvoice SET pupilsightSchoolYearID=:pupilsightSchoolYearID, pupilsightFinanceInvoiceeID=:pupilsightFinanceInvoiceeID, invoiceTo='Company', billingScheduleType='Ad Hoc', status='Pending', invoiceDueDate=:invoiceDueDate, notes=:notes, `key`=:key, pupilsightPersonIDCreator=:pupilsightPersonIDCreator, timeStampCreator='".date('Y-m-d H:i:s')."'";
                                            }
                                            $resultInvoiceAdd = $connection2->prepare($sqlInvoiceAdd);
                                            $resultInvoiceAdd->execute($dataInvoiceAdd);
                                        } catch (PDOException $e) {
                                            echo $e->getMessage();
                                            ++$invoiceFailCount;
                                            $thisInvoiceFailed = true;
                                        }
                                        if ($thisInvoiceFailed == false) {
                                            //Add fees to invoice
                                            $count = 0;
                                            foreach ($fees as $fee) {
                                                ++$count;
                                                if (($invoiceTo == 'Company' and $companyAll == 'Y') or ($invoiceTo == 'Company' and $companyAll == 'N' and is_numeric(strpos($pupilsightFinanceFeeCategoryIDList2, $fee['pupilsightFinanceFeeCategoryID'])))) {
                                                    try {
                                                        if ($fee['feeType'] == 'Standard') {
                                                            $dataInvoiceFee = array('pupilsightFinanceInvoiceID' => $AI, 'feeType' => $fee['feeType'], 'pupilsightFinanceFeeID' => $fee['pupilsightFinanceFeeID']);
                                                            $sqlInvoiceFee = "INSERT INTO pupilsightFinanceInvoiceFee SET pupilsightFinanceInvoiceID=:pupilsightFinanceInvoiceID, feeType=:feeType, pupilsightFinanceFeeID=:pupilsightFinanceFeeID, separated='N', sequenceNumber=$count";
                                                        } else {
                                                            $dataInvoiceFee = array('pupilsightFinanceInvoiceID' => $AI, 'feeType' => $fee['feeType'], 'name' => $fee['name'], 'description' => $fee['description'], 'pupilsightFinanceFeeCategoryID' => $fee['pupilsightFinanceFeeCategoryID'], 'fee' => $fee['fee']);
                                                            $sqlInvoiceFee = "INSERT INTO pupilsightFinanceInvoiceFee SET pupilsightFinanceInvoiceID=:pupilsightFinanceInvoiceID, feeType=:feeType, name=:name, description=:description, pupilsightFinanceFeeCategoryID=:pupilsightFinanceFeeCategoryID, fee=:fee, sequenceNumber=$count";
                                                        }
                                                        $resultInvoiceFee = $connection2->prepare($sqlInvoiceFee);
                                                        $resultInvoiceFee->execute($dataInvoiceFee);
                                                    } catch (PDOException $e) {
                                                        ++$invoiceFeeFailCount;
                                                    }
                                                }
                                            }
                                        }
                                    }
                                }
                            } elseif ($resultInvoice->rowCount() == 1 and $thisInvoiceFailed == false) {
                                $rowInvoice = $resultInvoice->fetch();

                                //Add fees to invoice
                                $count = 0;
                                foreach ($fees as $fee) {
                                    ++$count;
                                    if (($invoiceTo == 'Company' and $companyAll == 'Y') or ($invoiceTo == 'Company' and $companyAll == 'N' and is_numeric(strpos($pupilsightFinanceFeeCategoryIDList2, $fee['pupilsightFinanceFeeCategoryID'])))) {
                                        try {
                                            if ($fee['feeType'] == 'Standard') {
                                                $dataInvoiceFee = array('pupilsightFinanceInvoiceID' => $rowInvoice['pupilsightFinanceInvoiceID'], 'feeType' => $fee['feeType'], 'pupilsightFinanceFeeID' => $fee['pupilsightFinanceFeeID']);
                                                $sqlInvoiceFee = "INSERT INTO pupilsightFinanceInvoiceFee SET pupilsightFinanceInvoiceID=:pupilsightFinanceInvoiceID, feeType=:feeType, pupilsightFinanceFeeID=:pupilsightFinanceFeeID, separated='N', sequenceNumber=$count";
                                            } else {
                                                $dataInvoiceFee = array('pupilsightFinanceInvoiceID' => $rowInvoice['pupilsightFinanceInvoiceID'], 'feeType' => $fee['feeType'], 'name' => $fee['name'], 'description' => $fee['description'], 'pupilsightFinanceFeeCategoryID' => $fee['pupilsightFinanceFeeCategoryID'], 'fee' => $fee['fee']);
                                                $sqlInvoiceFee = "INSERT INTO pupilsightFinanceInvoiceFee SET pupilsightFinanceInvoiceID=:pupilsightFinanceInvoiceID, feeType=:feeType, name=:name, description=:description, pupilsightFinanceFeeCategoryID=:pupilsightFinanceFeeCategoryID, fee=:fee, sequenceNumber=$count";
                                            }
                                            $resultInvoiceFee = $connection2->prepare($sqlInvoiceFee);
                                            $resultInvoiceFee->execute($dataInvoiceFee);
                                        } catch (PDOException $e) {
                                            ++$invoiceFeeFailCount;
                                        }
                                    }
                                }

                                //Update invoice
                                try {
                                    if ($scheduling == 'Scheduled') {
                                        $dataInvoiceAdd = array('pupilsightPersonIDUpdate' => $_SESSION[$guid]['pupilsightPersonID'], 'notes' => $rowInvoice['notes'].' '.$notes, 'pupilsightFinanceInvoiceID' => $rowInvoice['pupilsightFinanceInvoiceID']);
                                        $sqlInvoiceAdd = "UPDATE pupilsightFinanceInvoice SET pupilsightPersonIDUpdate=:pupilsightPersonIDUpdate, notes=:notes, timeStampUpdate='".date('Y-m-d H:i:s')."' WHERE pupilsightFinanceInvoiceID=:pupilsightFinanceInvoiceID";
                                    } else {
                                        $dataInvoiceAdd = array('invoiceDueDate' => dateConvert($guid, $invoiceDueDate), 'pupilsightPersonIDUpdate' => $_SESSION[$guid]['pupilsightPersonID'], 'notes' => $rowInvoice['notes'].' '.$notes, 'pupilsightFinanceInvoiceID' => $rowInvoice['pupilsightFinanceInvoiceID']);
                                        $sqlInvoiceAdd = "UPDATE pupilsightFinanceInvoice SET invoiceDueDate=:invoiceDueDate, pupilsightPersonIDUpdate=:pupilsightPersonIDUpdate, notes=:notes, timeStampUpdate='".date('Y-m-d H:i:s')."' WHERE pupilsightFinanceInvoiceID=:pupilsightFinanceInvoiceID";
                                    }
                                    $resultInvoiceAdd = $connection2->prepare($sqlInvoiceAdd);
                                    $resultInvoiceAdd->execute($dataInvoiceAdd);
                                } catch (PDOException $e) {
                                    ++$invoiceFailCount;
                                    $thisInvoiceFailed = true;
                                }
                                $AI = $rowInvoice['pupilsightFinanceInvoiceID'];
                            } else {
                                if ($thisInvoiceFailed == false) {
                                    ++$invoiceFailCount;
                                    $thisInvoiceFailed = true;
                                }
                            }
                        }
                    }

                    $pupilsightFinanceInvoiceID = NULL;
                    if (isset($rowInvoice['pupilsightFinanceInvoiceID']))
                        $pupilsightFinanceInvoiceID = $rowInvoice['pupilsightFinanceInvoiceID'];
                    else if (isset($AI))
                        $pupilsightFinanceInvoiceID = $AI;

                    //SET pupilsightFinanceFeeCategoryIDList WITH ALL FEES (doing this now due to the complex nature of adding fees above)
                    try {
                        $dataTemp = array('pupilsightFinanceInvoiceID' => $pupilsightFinanceInvoiceID);
                        $sqlTemp = 'SELECT pupilsightFinanceFeeCategoryID FROM pupilsightFinanceInvoiceFee WHERE pupilsightFinanceInvoiceID=:pupilsightFinanceInvoiceID';
                        $resultTemp = $connection2->prepare($sqlTemp);
                        $resultTemp->execute($dataTemp);
                    } catch (PDOException $e) {}

                    $pupilsightFinanceFeeCategoryIDList = '';
                    while ($rowTemp = $resultTemp->fetch()) {
                        $pupilsightFinanceFeeCategoryIDList .= $rowTemp['pupilsightFinanceFeeCategoryID'].",";
                    }

                    $pupilsightFinanceFeeCategoryIDList = substr($pupilsightFinanceFeeCategoryIDList, 0, -1);
                    if ($pupilsightFinanceFeeCategoryIDList != '') {
                        try {
                            $dataTemp2 = array('pupilsightFinanceFeeCategoryIDList' => $pupilsightFinanceFeeCategoryIDList, 'pupilsightFinanceInvoiceID' => $pupilsightFinanceInvoiceID);
                            $sqlTemp2 = 'UPDATE pupilsightFinanceInvoice SET pupilsightFinanceFeeCategoryIDList=:pupilsightFinanceFeeCategoryIDList WHERE pupilsightFinanceInvoiceID=:pupilsightFinanceInvoiceID';
                            $resultTemp2 = $connection2->prepare($sqlTemp2);
                            $resultTemp2->execute($dataTemp2);
                        } catch (PDOException $e) {}
                    }
                }

                //Unlock module table
                try {
                    $sql = 'UNLOCK TABLES';
                    $result = $connection2->query($sql);
                } catch (PDOException $e) {
                }

                //Return results, include three types of fail and counts
                if ($studentFailCount != 0 or $invoiceFailCount != 0 or $invoiceFeeFailCount != 0) {
                    $URL .= "&return=error3&studentFailCount=$studentFailCount&invoiceFailCount=$invoiceFailCount&invoiceFeeFailCount=$invoiceFeeFailCount";
                    header("Location: {$URL}");
                } else {
                    $URL .= '&return=success0';
                    header("Location: {$URL}");
                }
            }
        }
    }
}
