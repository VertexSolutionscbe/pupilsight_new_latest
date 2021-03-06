<?php
/*
Pupilsight, Flexible & Open School System
*/

//Returns amount paid on an particular table/ID combo
function getAmountPaid($connection2, $guid, $foreignTable, $foreignTableID)
{
    $return = true;

    try {
        $data = array('foreignTable' => $foreignTable, 'foreignTableID' => $foreignTableID);
        $sql = 'SELECT pupilsightPayment.* FROM pupilsightPayment WHERE foreignTable=:foreignTable AND foreignTableID=:foreignTableID ORDER BY timestamp';
        $result = $connection2->prepare($sql);
        $result->execute($data);
    } catch (PDOException $e) {
        $return = false;
    }
    if ($result) {
        $return = 0;
        while ($row = $result->fetch()) {
            $return += $row['amount'];
        }
    }

    return $return;
}

//Returns log associated with a particular expense
//If $pupilsightPaymentID is not NULL, then only that ID's entry is included
function getPaymentLog($connection2, $guid, $foreignTable, $foreignTableID, $pupilsightPaymentID = null)
{
    $return = '';
    try {
        $data = array('foreignTable' => $foreignTable, 'foreignTableID' => $foreignTableID);
        $sql = 'SELECT pupilsightPayment.*, surname, preferredName FROM pupilsightPayment JOIN pupilsightPerson ON (pupilsightPayment.pupilsightPersonID=pupilsightPerson.pupilsightPersonID) WHERE foreignTable=:foreignTable AND foreignTableID=:foreignTableID ORDER BY timestamp, pupilsightPaymentID';
        $result = $connection2->prepare($sql);
        $result->execute($data);
    } catch (PDOException $e) {
        $return .= "<div class='error'>".$e->getMessage().'</div>';
    }

    if ($result->rowCount() < 1) {
        $return .= "<div class='error'>";
        $return .= __('There are no records to display.');
        $return .= '</div>';
    } else {
        $return .= "<table cellspacing='0' style='width: 100%'>";
        $return .= "<tr class='head'>";
        $return .= '<th>';
        $return .= __('Date');
        $return .= '</th>';
        $return .= '<th>';
        $return .= __('Payment Event');
        $return .= '</th>';
        $return .= '<th>';
        $return .= __('Amount').'<br/>';
        if ($_SESSION[$guid]['currency'] != '') {
            $return .= "<span style='font-style: italic; font-size: 85%'>".$_SESSION[$guid]['currency'].'</span>';
        }
        $return .= '</th>';
        $return .= '<th>';
        $return .= __('Type');
        $return .= '</th>';
        $return .= '<th>';
        $return .= __('Paid/Recorded By');
        $return .= '</th>';
        $return .= "<th style='width: 150px'>";
        $return .= __('Transaction ID');
        $return .= '</th>';
        $return .= '</tr>';

        $rowNum = 'odd';
        $count = 0;
        $paymentTotal = 0;

        $beforeCurrentDate = true;
        while ($row = $result->fetch()) {
            if ($row['pupilsightPaymentID'] == $pupilsightPaymentID or $pupilsightPaymentID == null) {
                $beforeCurrentDate = false;
                if ($count % 2 == 0) {
                    $rowNum = 'even';
                } else {
                    $rowNum = 'odd';
                }
                ++$count;

                //COLOR ROW BY STATUS!
                $return .= "<tr class=$rowNum>";
                $return .= '<td>';
                $return .= dateConvertBack($guid, substr($row['timestamp'], 0, 10));
                $return .= '</td>';
                $return .= '<td>';
                $return .= $row['status'];
                $return .= '</td>';
                $return .= '<td>';
                $paymentTotal += $row['amount'];
                if (substr($_SESSION[$guid]['currency'], 4) != '') {
                    $return .= substr($_SESSION[$guid]['currency'], 4).' ';
                }
                $return .= number_format($row['amount'], 2, '.', ',');
                $return .= '</td>';
                $return .= '<td>';
                $return .= $row['type'];
                $return .= '</td>';
                $return .= '<td>';
                $return .= formatName('', $row['preferredName'], $row['surname'], 'Staff', false, true);
                $return .= '</td>';
                $return .= '<td>';
                $return .= $row['paymentTransactionID'];
                $return .= '</td>';
                $return .= '</tr>';
            } else {
                if ($beforeCurrentDate) {
                    $paymentTotal += $row['amount'];
                }
            }
        }
        $return .= "<tr style='height: 35px' class='current'>";
        $return .= "<td colspan=5 style='text-align: right'>";
        $return .= '<b>'.__('Total Payment On This Invoice:').'</b>';
        $return .= '</td>';
        $return .= '<td>';
        if (substr($_SESSION[$guid]['currency'], 4) != '') {
            $return .= substr($_SESSION[$guid]['currency'], 4).' ';
        }
        $return .= '<b>'.number_format($paymentTotal, 2, '.', ',').'</b>';
        $return .= '</td>';
        $return .= '</tr>';
        $return .= '</table>';
    }

    return $return;
}

//Create an entry in the payment log table, recording the details of a particular payment
function setPaymentLog($connection2, $guid, $foreignTable, $foreignTableID, $type, $status, $amount, $gateway = null, $onlineTransactionStatus = null, $paymentToken = null, $paymentPayerID = null, $paymentTransactionID = null, $paymentReceiptID = null, $timestamp = null)
{
    $return = true;

    if ($timestamp == null) {
        $timestamp = date('Y-m-d H:i:s');
    }
    $pupilsightPersonID = null;
    if (isset($_SESSION[$guid]['pupilsightPersonID'])) {
        $pupilsightPersonID = $_SESSION[$guid]['pupilsightPersonID'];
    }
    try {
        $data = array('foreignTable' => $foreignTable, 'foreignTableID' => $foreignTableID, 'pupilsightPersonID' => $pupilsightPersonID, 'type' => $type, 'status' => $status, 'amount' => $amount, 'gateway' => $gateway, 'onlineTransactionStatus' => $onlineTransactionStatus, 'paymentToken' => $paymentToken, 'paymentPayerID' => $paymentPayerID, 'paymentTransactionID' => $paymentTransactionID, 'paymentReceiptID' => $paymentReceiptID, 'timestamp' => $timestamp);
        $sql = 'INSERT INTO pupilsightPayment SET foreignTable=:foreignTable, foreignTableID=:foreignTableID, pupilsightPersonID=:pupilsightPersonID, type=:type, status=:status, amount=:amount, gateway=:gateway, onlineTransactionStatus=:onlineTransactionStatus, paymentToken=:paymentToken, paymentPayerID=:paymentPayerID, paymentTransactionID=:paymentTransactionID, paymentReceiptID=:paymentReceiptID, timestamp=:timestamp';
        $result = $connection2->prepare($sql);
        $result->execute($data);
    } catch (PDOException $e) {
        $return = false;
    }

    $return = $connection2->lastInsertID();

    return $return;
}

//Checks log to see if approval is complete. Returns false (on error), none (if no completion), budget (if budget completion done or not required), school (if all complete)
function checkLogForApprovalComplete($guid, $pupilsightFinanceExpenseID, $connection2)
{
    //Lock tables
    $lock = true;
    try {
        $sqlLock = 'LOCK TABLE pupilsightFinanceExpense WRITE, pupilsightFinanceExpenseApprover WRITE, pupilsightFinanceExpenseLog WRITE, pupilsightFinanceBudget WRITE, pupilsightFinanceBudgetPerson WRITE, pupilsightSetting WRITE, pupilsightNotification WRITE, pupilsightPerson READ, pupilsightModule READ';
        $resultLock = $connection2->query($sqlLock);
    } catch (PDOException $e) {
        $lock = false;
        return false;
    }

    if ($lock) {
        try {
            $data = array('pupilsightFinanceExpenseID' => $pupilsightFinanceExpenseID);
            $sql = 'SELECT pupilsightFinanceExpense.*, pupilsightFinanceBudget.name AS budget FROM pupilsightFinanceExpense JOIN pupilsightFinanceBudget ON (pupilsightFinanceExpense.pupilsightFinanceBudgetID=pupilsightFinanceBudget.pupilsightFinanceBudgetID) WHERE pupilsightFinanceExpense.pupilsightFinanceExpenseID=:pupilsightFinanceExpenseID';
            $result = $connection2->prepare($sql);
            $result->execute($data);
        } catch (PDOException $e) {
            return false;
        }

        if ($result->rowCount() != 1) {
            return false;
        } else {
            $row = $result->fetch();

            //Get settings for budget-level and school-level approval
            $expenseApprovalType = getSettingByScope($connection2, 'Finance', 'expenseApprovalType');
            $budgetLevelExpenseApproval = getSettingByScope($connection2, 'Finance', 'budgetLevelExpenseApproval');

            if ($expenseApprovalType == '' or $budgetLevelExpenseApproval == '') {
                return false;
            } else {
                if ($row['status'] != 'Requested') { //Finished? Return
                    return false;
                } else { //Not finished
                    if ($row['statusApprovalBudgetCleared'] == 'N') { //Notify budget holders (e.g. access Full)
                        return 'none';
                    } else { //School-level approval, what type is it?
                        if ($expenseApprovalType == 'One Of' or $expenseApprovalType == 'Two Of') { //One Of or Two Of, so alert all approvers
                            if ($expenseApprovalType == 'One Of') {
                                $expected = 1;
                            } else {
                                $expected = 2;
                            }
                            //Do we have correct number of approvals
                            try {
                                $dataTest = array('pupilsightFinanceExpenseID' => $pupilsightFinanceExpenseID);
                                $sqlTest = "SELECT DISTINCT * FROM pupilsightFinanceExpenseLog WHERE pupilsightFinanceExpenseID=:pupilsightFinanceExpenseID AND action='Approval - Partial - School'";
                                $resultTest = $connection2->prepare($sqlTest);
                                $resultTest->execute($dataTest);
                            } catch (PDOException $e) {
                                return false;
                            }
                            if ($resultTest->rowCount() >= $expected) { //Yes - return "school"
                                return 'school';
                            } else { //No - return "budget"
                                return 'budget';
                            }
                        } elseif ($expenseApprovalType == 'Chain Of All') { //Chain of all
                            try {
                                $dataApprovers = array('pupilsightFinanceExpenseID' => $pupilsightFinanceExpenseID);
                                $sqlApprovers = "SELECT pupilsightPerson.pupilsightPersonID AS g1, pupilsightFinanceExpenseLog.pupilsightPersonID AS g2 FROM pupilsightFinanceExpenseApprover JOIN pupilsightPerson ON (pupilsightFinanceExpenseApprover.pupilsightPersonID=pupilsightPerson.pupilsightPersonID) LEFT JOIN pupilsightFinanceExpenseLog ON (pupilsightFinanceExpenseLog.pupilsightPersonID=pupilsightFinanceExpenseApprover.pupilsightPersonID AND pupilsightFinanceExpenseLog.action='Approval - Partial - School' AND pupilsightFinanceExpenseLog.pupilsightFinanceExpenseID=:pupilsightFinanceExpenseID) WHERE pupilsightPerson.status='Full' ORDER BY sequenceNumber, surname, preferredName";
                                $resultApprovers = $connection2->prepare($sqlApprovers);
                                $resultApprovers->execute($dataApprovers);
                            } catch (PDOException $e) {
                                return false;
                            }
                            $approvers = $resultApprovers->fetchAll();
                            $countTotal = $resultApprovers->rowCount();
                            $count = 0;
                            foreach ($approvers as $approver) {
                                if ($approver['g1'] == $approver['g2']) {
                                    ++$count;
                                }
                            }

                            if ($count >= $countTotal) { //Yes - return "school"
                                return 'school';
                            } else { //No - return "budget"
                                return 'budget';
                            }
                        } else {
                            return false;
                        }
                    }
                }
            }
        }

        //Unlock tables
        try {
            $sql = 'UNLOCK TABLES';
            $result = $connection2->query($sql);
        } catch (PDOException $e) {}
    }
}

//Checks a certain expense request, and returns FALSE on error, TRUE if specified person can approve it.
function approvalRequired($guid, $pupilsightPersonID, $pupilsightFinanceExpenseID, $pupilsightFinanceBudgetCycleID, $connection2, $locking = true)
{
    //Lock tables
    $lock = true;
    if ($locking) {
        try {
            $sqlLock = 'LOCK TABLE pupilsightFinanceExpense WRITE, pupilsightFinanceExpenseApprover WRITE, pupilsightFinanceExpenseLog WRITE, pupilsightFinanceBudget WRITE, pupilsightFinanceBudgetPerson WRITE, pupilsightSetting WRITE, pupilsightNotification WRITE, pupilsightPerson READ, pupilsightModule READ, pupilsightAction READ, pupilsightPermission READ';
            $resultLock = $connection2->query($sqlLock);
        } catch (PDOException $e) {
            $lock = false;
            return false;
        }
    }

    if ($lock) {
        try {
            $data = array('pupilsightFinanceExpenseID' => $pupilsightFinanceExpenseID);
            $sql = 'SELECT pupilsightFinanceExpense.*, pupilsightFinanceBudget.name AS budget FROM pupilsightFinanceExpense JOIN pupilsightFinanceBudget ON (pupilsightFinanceExpense.pupilsightFinanceBudgetID=pupilsightFinanceBudget.pupilsightFinanceBudgetID) WHERE pupilsightFinanceExpense.pupilsightFinanceExpenseID=:pupilsightFinanceExpenseID';
            $result = $connection2->prepare($sql);
            $result->execute($data);
        } catch (PDOException $e) {
            return false;
        }

        if ($result->rowCount() != 1) {
            echo $result->rowCount();
            exit();

            return false;
        } else {
            $row = $result->fetch();

            //Get settings for budget-level and school-level approval
            $expenseApprovalType = getSettingByScope($connection2, 'Finance', 'expenseApprovalType');
            $budgetLevelExpenseApproval = getSettingByScope($connection2, 'Finance', 'budgetLevelExpenseApproval');

            if ($expenseApprovalType == '' or $budgetLevelExpenseApproval == '') {
                return false;
            } else {
                if ($row['status'] != 'Requested') { //Finished? Return
                    return false;
                } else { //Not finished
                    if ($row['statusApprovalBudgetCleared'] == 'N') {
                        //Get Full budget people
                        try {
                            $dataBudget = array('pupilsightFinanceBudgetID' => $row['pupilsightFinanceBudgetID'], 'pupilsightPersonID' => $pupilsightPersonID);
                            $sqlBudget = "SELECT pupilsightPersonID FROM pupilsightFinanceBudget JOIN pupilsightFinanceBudgetPerson ON (pupilsightFinanceBudgetPerson.pupilsightFinanceBudgetID=pupilsightFinanceBudget.pupilsightFinanceBudgetID) WHERE access='Full' AND pupilsightFinanceBudget.pupilsightFinanceBudgetID=:pupilsightFinanceBudgetID AND pupilsightFinanceBudgetPerson.pupilsightPersonID=:pupilsightPersonID";
                            $resultBudget = $connection2->prepare($sqlBudget);
                            $resultBudget->execute($dataBudget);
                        } catch (PDOException $e) {
                            return false;
                        }

                        if ($resultBudget->rowCount() != 1) {
                            return false;
                        } else {
                            return true;
                        }
                    } else { //School-level approval, what type is it?
                        if ($expenseApprovalType == 'One Of' or $expenseApprovalType == 'Two Of') { //One Of or Two Of, so alert all approvers
                            try {
                                $dataApprovers = array('pupilsightPersonID' => $pupilsightPersonID);
                                $sqlApprovers = "SELECT pupilsightPerson.pupilsightPersonID FROM pupilsightFinanceExpenseApprover JOIN pupilsightPerson ON (pupilsightFinanceExpenseApprover.pupilsightPersonID=pupilsightPerson.pupilsightPersonID) WHERE pupilsightPerson.status='Full' AND pupilsightFinanceExpenseApprover.pupilsightPersonID=:pupilsightPersonID ORDER BY surname, preferredName";
                                $resultApprovers = $connection2->prepare($sqlApprovers);
                                $resultApprovers->execute($dataApprovers);
                            } catch (PDOException $e) {
                                return false;
                            }

                            if ($resultApprovers->rowCount() != 1) {
                                return false;
                            } else {
                                //Check of already approved at school-level
                                try {
                                    $dataApproval = array('pupilsightFinanceExpenseID' => $pupilsightFinanceExpenseID, 'pupilsightPersonID' => $pupilsightPersonID);
                                    $sqlApproval = "SELECT * FROM pupilsightFinanceExpenseLog WHERE pupilsightFinanceExpenseID=:pupilsightFinanceExpenseID AND pupilsightPersonID=:pupilsightPersonID AND action='Approval - Partial - School'";
                                    $resultApproval = $connection2->prepare($sqlApproval);
                                    $resultApproval->execute($dataApproval);
                                } catch (PDOException $e) {
                                    return false;
                                }
                                if ($resultApproval->rowCount() > 0) {
                                    return false;
                                } else {
                                    return true;
                                }
                            }
                        } elseif ($expenseApprovalType == 'Chain Of All') { //Chain of all
                            //Get notifiers in sequence
                            try {
                                $dataApprovers = array('pupilsightFinanceExpenseID' => $pupilsightFinanceExpenseID);
                                $sqlApprovers = "SELECT pupilsightPerson.pupilsightPersonID AS g1, pupilsightFinanceExpenseLog.pupilsightPersonID AS g2 FROM pupilsightFinanceExpenseApprover JOIN pupilsightPerson ON (pupilsightFinanceExpenseApprover.pupilsightPersonID=pupilsightPerson.pupilsightPersonID) LEFT JOIN pupilsightFinanceExpenseLog ON (pupilsightFinanceExpenseLog.pupilsightPersonID=pupilsightFinanceExpenseApprover.pupilsightPersonID AND pupilsightFinanceExpenseLog.action='Approval - Partial - School' AND pupilsightFinanceExpenseLog.pupilsightFinanceExpenseID=:pupilsightFinanceExpenseID) WHERE pupilsightPerson.status='Full' ORDER BY sequenceNumber, surname, preferredName";
                                $resultApprovers = $connection2->prepare($sqlApprovers);
                                $resultApprovers->execute($dataApprovers);
                            } catch (PDOException $e) {
                                return false;
                            }
                            if ($resultApprovers->rowCount() < 1) {
                                return false;
                            } else {
                                $approvers = $resultApprovers->fetchAll();
                                $pupilsightPersonIDNext = null;
                                foreach ($approvers as $approver) {
                                    if ($approver['g1'] != $approver['g2']) {
                                        if (is_null($pupilsightPersonIDNext)) {
                                            $pupilsightPersonIDNext = $approver['g1'];
                                        }
                                    }
                                }

                                if (is_null($pupilsightPersonIDNext)) {
                                    return false;
                                } else {
                                    if ($pupilsightPersonIDNext != $pupilsightPersonID) {
                                        return false;
                                    } else {
                                        return true;
                                    }
                                }
                            }
                        } else {
                            return false;
                        }
                    }
                }
            }
        }

        //Unlock tables
        try {
            $sql = 'UNLOCK TABLES';
            $result = $connection2->query($sql);
        } catch (PDOException $e) {}
    }
}

//Issues correct notificaitons for give expense, depending on circumstances. Returns FALSE on error, TRUE if it did its job.
//Tries to avoid issue duplicate notifications
function setExpenseNotification($guid, $pupilsightFinanceExpenseID, $pupilsightFinanceBudgetCycleID, $connection2)
{
    //Lock tables
    $lock = true;
    try {
        $sqlLock = 'LOCK TABLE pupilsightFinanceExpense WRITE, pupilsightFinanceExpenseApprover WRITE, pupilsightFinanceExpenseLog WRITE, pupilsightFinanceBudget WRITE, pupilsightFinanceBudgetPerson WRITE, pupilsightSetting WRITE, pupilsightNotification WRITE, pupilsightPerson READ, pupilsightModule READ';
        $resultLock = $connection2->query($sqlLock);
    } catch (PDOException $e) {
        $lock = false;

        return false;
    }

    if ($lock) {
        try {
            $data = array('pupilsightFinanceExpenseID' => $pupilsightFinanceExpenseID);
            $sql = 'SELECT pupilsightFinanceExpense.*, pupilsightFinanceBudget.name AS budget FROM pupilsightFinanceExpense JOIN pupilsightFinanceBudget ON (pupilsightFinanceExpense.pupilsightFinanceBudgetID=pupilsightFinanceBudget.pupilsightFinanceBudgetID) WHERE pupilsightFinanceExpense.pupilsightFinanceExpenseID=:pupilsightFinanceExpenseID';
            $result = $connection2->prepare($sql);
            $result->execute($data);
        } catch (PDOException $e) {
            return false;
        }

        if ($result->rowCount() != 1) {
            return false;
        } else {
            $row = $result->fetch();

            //Get settings for budget-level and school-level approval
            $expenseApprovalType = getSettingByScope($connection2, 'Finance', 'expenseApprovalType');
            $budgetLevelExpenseApproval = getSettingByScope($connection2, 'Finance', 'budgetLevelExpenseApproval');

            if ($expenseApprovalType == '' or $budgetLevelExpenseApproval == '') {
                return false;
            } else {
                if ($row['status'] != 'Requested') { //Finished? Return
                    return true;
                } else { //Not finished
                    $notificationText = sprintf(__('Someone has requested expense approval for "%1$s" in budget "%2$s".'), $row['title'], $row['budget']);

                    if ($row['statusApprovalBudgetCleared'] == 'N') { //Notify budget holders (e.g. access Full)
                        //Get Full budget people, and notify them
                        try {
                            $dataBudget = array('pupilsightFinanceBudgetID' => $row['pupilsightFinanceBudgetID']);
                            $sqlBudget = "SELECT pupilsightPersonID FROM pupilsightFinanceBudget JOIN pupilsightFinanceBudgetPerson ON (pupilsightFinanceBudgetPerson.pupilsightFinanceBudgetID=pupilsightFinanceBudget.pupilsightFinanceBudgetID) WHERE access='Full' AND pupilsightFinanceBudget.pupilsightFinanceBudgetID=:pupilsightFinanceBudgetID";
                            $resultBudget = $connection2->prepare($sqlBudget);
                            $resultBudget->execute($dataBudget);
                        } catch (PDOException $e) {
                            return false;
                        }
                        if ($resultBudget->rowCount() < 1) {
                            return false;
                        } else {
                            while ($rowBudget = $resultBudget->fetch()) {
                                setNotification($connection2, $guid, $rowBudget['pupilsightPersonID'], $notificationText, 'Finance', "/index.php?q=/modules/Finance/expenses_manage_approve.php&pupilsightFinanceExpenseID=$pupilsightFinanceExpenseID&pupilsightFinanceBudgetCycleID=$pupilsightFinanceBudgetCycleID&status2=&pupilsightFinanceBudgetID2=".$row['pupilsightFinanceBudgetID']);

                                return true;
                            }
                        }
                    } else { //School-level approval, what type is it?
                        if ($expenseApprovalType == 'One Of' or $expenseApprovalType == 'Two Of') { //One Of or Two Of, so alert all approvers
                            try {
                                $dataApprovers = array('pupilsightFinanceExpenseID' => $pupilsightFinanceExpenseID);
                                $sqlApprovers = "SELECT pupilsightPerson.pupilsightPersonID, pupilsightFinanceExpenseLog.pupilsightFinanceExpenseLogID FROM pupilsightFinanceExpenseApprover JOIN pupilsightPerson ON (pupilsightFinanceExpenseApprover.pupilsightPersonID=pupilsightPerson.pupilsightPersonID) LEFT JOIN pupilsightFinanceExpenseLog ON (pupilsightFinanceExpenseLog.pupilsightPersonID=pupilsightPerson.pupilsightPersonID AND pupilsightFinanceExpenseLog.pupilsightFinanceExpenseID=:pupilsightFinanceExpenseID) WHERE pupilsightPerson.status='Full' ORDER BY surname, preferredName";
                                $resultApprovers = $connection2->prepare($sqlApprovers);
                                $resultApprovers->execute($dataApprovers);
                            } catch (PDOException $e) {
                                return false;
                            }
                            if ($resultApprovers->rowCount() < 1) {
                                return false;
                            } else {
                                while ($rowApprovers = $resultApprovers->fetch()) {
                                    if ($rowApprovers['pupilsightFinanceExpenseLogID'] == '') {
                                        setNotification($connection2, $guid, $rowApprovers['pupilsightPersonID'], $notificationText, 'Finance', "/index.php?q=/modules/Finance/expenses_manage_approve.php&pupilsightFinanceExpenseID=$pupilsightFinanceExpenseID&pupilsightFinanceBudgetCycleID=$pupilsightFinanceBudgetCycleID&status2=&pupilsightFinanceBudgetID2=".$row['pupilsightFinanceBudgetID']);
                                    }
                                }

                                return true;
                            }
                        } elseif ($expenseApprovalType == 'Chain Of All') { //Chain of all
                            //Get notifiers in sequence
                            try {
                                $dataApprovers = array('pupilsightFinanceExpenseID' => $pupilsightFinanceExpenseID);
                                $sqlApprovers = "SELECT pupilsightPerson.pupilsightPersonID AS g1, pupilsightFinanceExpenseLog.pupilsightPersonID AS g2 FROM pupilsightFinanceExpenseApprover JOIN pupilsightPerson ON (pupilsightFinanceExpenseApprover.pupilsightPersonID=pupilsightPerson.pupilsightPersonID) LEFT JOIN pupilsightFinanceExpenseLog ON (pupilsightFinanceExpenseLog.pupilsightPersonID=pupilsightFinanceExpenseApprover.pupilsightPersonID AND pupilsightFinanceExpenseLog.action='Approval - Partial - School' AND pupilsightFinanceExpenseLog.pupilsightFinanceExpenseID=:pupilsightFinanceExpenseID) WHERE pupilsightPerson.status='Full' ORDER BY sequenceNumber, surname, preferredName";
                                $resultApprovers = $connection2->prepare($sqlApprovers);
                                $resultApprovers->execute($dataApprovers);
                            } catch (PDOException $e) {
                                return false;
                            }
                            if ($resultApprovers->rowCount() < 1) {
                                return false;
                            } else {
                                $approvers = $resultApprovers->fetchAll();
                                $pupilsightPersonIDNext = null;
                                foreach ($approvers as $approver) {
                                    if ($approver['g1'] != $approver['g2']) {
                                        if (is_null($pupilsightPersonIDNext)) {
                                            $pupilsightPersonIDNext = $approver['g1'];
                                        }
                                    }
                                }

                                if (is_null($pupilsightPersonIDNext)) {
                                    return false;
                                } else {
                                    setNotification($connection2, $guid, $pupilsightPersonIDNext, $notificationText, 'Finance', "/index.php?q=/modules/Finance/expenses_manage_approve.php&pupilsightFinanceExpenseID=$pupilsightFinanceExpenseID&pupilsightFinanceBudgetCycleID=$pupilsightFinanceBudgetCycleID&status2=&pupilsightFinanceBudgetID2=".$row['pupilsightFinanceBudgetID']);

                                    return true;
                                }
                            }
                        } else {
                            return false;
                        }
                    }
                }
            }
        }

        //Unlock tables
        try {
            $sql = 'UNLOCK TABLES';
            $result = $connection2->query($sql);
        } catch (PDOException $e) {}
    }
}

//Returns log associated with a particular expense
function getExpenseLog($guid, $pupilsightFinanceExpenseID, $connection2, $commentsOpen = false)
{
    $output = '';

    try {
        $data = array('pupilsightFinanceExpenseID' => $pupilsightFinanceExpenseID);
        $sql = 'SELECT pupilsightFinanceExpenseLog.*, surname, preferredName FROM pupilsightFinanceExpense JOIN pupilsightFinanceExpenseLog ON (pupilsightFinanceExpenseLog.pupilsightFinanceExpenseID=pupilsightFinanceExpense.pupilsightFinanceExpenseID) JOIN pupilsightPerson ON (pupilsightFinanceExpenseLog.pupilsightPersonID=pupilsightPerson.pupilsightPersonID) WHERE pupilsightFinanceExpenseLog.pupilsightFinanceExpenseID=:pupilsightFinanceExpenseID ORDER BY timestamp';
        $result = $connection2->prepare($sql);
        $result->execute($data);
    } catch (PDOException $e) {
        $output .= "<div class='error'>".$e->getMessage().'</div>';
    }

    if ($result->rowCount() < 1) {
        $output .= "<div class='error'>";
        $output .= __('There are no records to display.');
        $output .= '</div>';
    } else {
        $output .= "<table cellspacing='0' style='width: 100%'>";
        $output .= "<tr class='head'>";
        $output .= '<th>';
        $output .= __('Person');
        $output .= '</th>';
        $output .= '<th>';
        $output .= __('Date');
        $output .= '</th>';
        $output .= '<th>';
        $output .= __('Event');
        $output .= '</th>';
        if ($commentsOpen == false) {
            $output .= '<th>';
            $output .= __('Actions');
            $output .= '</th>';
        }
        $output .= '</tr>';

        $rowNum = 'odd';
        $count = 0;
        while ($row = $result->fetch()) {
            if ($count % 2 == 0) {
                $rowNum = 'even';
            } else {
                $rowNum = 'odd';
            }
            ++$count;

            //COLOR ROW BY STATUS!
            $output .= "<tr class=$rowNum>";
            $output .= '<td>';
            $output .= formatName('', $row['preferredName'], $row['surname'], 'Staff', false, true);
            $output .= '</td>';
            $output .= '<td>';
            $output .= dateConvertBack($guid, substr($row['timestamp'], 0, 10));
            $output .= '</td>';
            $output .= '<td>';
            $output .= $row['action'];
            $output .= '</td>';
            if ($commentsOpen == false) {
                $output .= '<td>';
                $output .= "<script type='text/javascript'>";
                $output .= '$(document).ready(function(){';
                $output .= "\$(\".comment-$count\").hide();";
                $output .= "\$(\".show_hide-$count\").fadeIn(1000);";
                $output .= "\$(\".show_hide-$count\").click(function(){";
                $output .= "\$(\".comment-$count\").fadeToggle(1000);";
                $output .= '});';
                $output .= '});';
                $output .= '</script>';
                if ($row['comment'] != '') {
                    $output .= "<a title='".__('View Description')."' class='show_hide-$count' onclick='false' href='#'><img style='padding-right: 5px' src='".$_SESSION[$guid]['absoluteURL'].'/themes/'.$_SESSION[$guid]['pupilsightThemeName']."/img/page_down.png' alt='".__('Show Comment')."' onclick='return false;' /></a>";
                }
                $output .= '</td>';
            }
            $output .= '</tr>';
            if ($row['comment'] != '') {
                $output .= "<tr class='comment-$count' id='comment-$count'>";
                $output .= '<td colspan=4>';
                if ($row['comment'] != '') {
                    $output .= nl2brr($row['comment']).'<br/><br/>';
                }
                $output .= '</td>';
                $output .= '</tr>';
            }
        }
        $output .= '</table>';
    }

    return $output;
}

//Returns all budgets a person is linked to, as well as their access rights to that budget
function getBudgetsByPerson($connection2, $pupilsightPersonID, $pupilsightFinanceBudgetID = '')
{
    $return = false;

    try {
        $data = array('pupilsightPersonID' => $pupilsightPersonID);
        if ($pupilsightFinanceBudgetID == '') {
            $sql = "SELECT * FROM pupilsightFinanceBudget JOIN pupilsightFinanceBudgetPerson ON (pupilsightFinanceBudgetPerson.pupilsightFinanceBudgetID=pupilsightFinanceBudget.pupilsightFinanceBudgetID) WHERE pupilsightPersonID=:pupilsightPersonID AND active='Y' ORDER BY name";
        } else {
            $data['pupilsightFinanceBudgetID'] = $pupilsightFinanceBudgetID;
            $sql = "SELECT * FROM pupilsightFinanceBudget JOIN pupilsightFinanceBudgetPerson ON (pupilsightFinanceBudgetPerson.pupilsightFinanceBudgetID=pupilsightFinanceBudget.pupilsightFinanceBudgetID) WHERE pupilsightPersonID=:pupilsightPersonID AND pupilsightFinanceBudget.pupilsightFinanceBudgetID=:pupilsightFinanceBudgetID AND active='Y' ORDER BY name";
        }
        $result = $connection2->prepare($sql);
        $result->execute($data);
    } catch (PDOException $e) {
        echo $e->getMessage();
    }

    $count = 0;
    if ($result->rowCount() > 0) {
        $return = array();
        while ($row = $result->fetch()) {
            $return[$count][0] = $row['pupilsightFinanceBudgetID'];
            $return[$count][1] = $row['name'];
            $return[$count][2] = $row['access'];
            ++$count;
        }
    }

    return $return;
}

//Returns all active budgets
function getBudgets($connection2)
{
    $return = false;

    try {
        $data = array();
        $sql = "SELECT * FROM pupilsightFinanceBudget WHERE active='Y' ORDER BY name";
        $result = $connection2->prepare($sql);
        $result->execute($data);
    } catch (PDOException $e) {
        echo $e->getMessage();
    }

    $count = 0;
    if ($result->rowCount() > 0) {
        $return = array();
        while ($row = $result->fetch()) {
            $return[$count][0] = $row['pupilsightFinanceBudgetID'];
            $return[$count][1] = $row['name'];
            ++$count;
        }
    }

    return $return;
}

//Take a budget cycle, and return the previous one, or false if none
function getBudgetCycleName($pupilsightFinanceBudgetCycleID, $connection2)
{
    $output = false;

    try {
        $dataCycle = array('pupilsightFinanceBudgetCycleID' => $pupilsightFinanceBudgetCycleID);
        $sqlCycle = 'SELECT * FROM pupilsightFinanceBudgetCycle WHERE pupilsightFinanceBudgetCycleID=:pupilsightFinanceBudgetCycleID';
        $resultCycle = $connection2->prepare($sqlCycle);
        $resultCycle->execute($dataCycle);
    } catch (PDOException $e) { }
    if ($resultCycle->rowCount() == 1) {
        $rowCycle = $resultCycle->fetch();
        $output = $rowCycle['name'];
    }

    return $output;
}

//Take a budget cycle, and return the previous one, or false if none
function getPreviousBudgetCycleID($pupilsightFinanceBudgetCycleID, $connection2)
{
    $output = false;

    try {
        $data = array('pupilsightFinanceBudgetCycleID' => $pupilsightFinanceBudgetCycleID);
        $sql = 'SELECT * FROM pupilsightFinanceBudgetCycle WHERE pupilsightFinanceBudgetCycleID=:pupilsightFinanceBudgetCycleID';
        $result = $connection2->prepare($sql);
        $result->execute($data);
    } catch (PDOException $e) {
    }
    if ($result->rowcount() == 1) {
        $row = $result->fetch();
        try {
            $dataPrevious = array('sequenceNumber' => $row['sequenceNumber']);
            $sqlPrevious = 'SELECT * FROM pupilsightFinanceBudgetCycle WHERE sequenceNumber<:sequenceNumber ORDER BY sequenceNumber DESC';
            $resultPrevious = $connection2->prepare($sqlPrevious);
            $resultPrevious->execute($dataPrevious);
        } catch (PDOException $e) {
        }
        if ($resultPrevious->rowCount() >= 1) {
            $rowPrevious = $resultPrevious->fetch();
            $output = $rowPrevious['pupilsightFinanceBudgetCycleID'];
        }
    }

    return $output;
}

//Take a budget cycle, and return the previous one, or false if none
function getNextBudgetCycleID($pupilsightFinanceBudgetCycleID, $connection2)
{
    $output = false;

    try {
        $data = array('pupilsightFinanceBudgetCycleID' => $pupilsightFinanceBudgetCycleID);
        $sql = 'SELECT * FROM pupilsightFinanceBudgetCycle WHERE pupilsightFinanceBudgetCycleID=:pupilsightFinanceBudgetCycleID';
        $result = $connection2->prepare($sql);
        $result->execute($data);
    } catch (PDOException $e) {
    }
    if ($result->rowcount() == 1) {
        $row = $result->fetch();
        try {
            $dataPrevious = array('sequenceNumber' => $row['sequenceNumber']);
            $sqlPrevious = 'SELECT * FROM pupilsightFinanceBudgetCycle WHERE sequenceNumber>:sequenceNumber ORDER BY sequenceNumber ASC';
            $resultPrevious = $connection2->prepare($sqlPrevious);
            $resultPrevious->execute($dataPrevious);
        } catch (PDOException $e) {
        }
        if ($resultPrevious->rowCount() >= 1) {
            $rowPrevious = $resultPrevious->fetch();
            $output = $rowPrevious['pupilsightFinanceBudgetCycleID'];
        }
    }

    return $output;
}

//Make the display for a block, according to the input provided, where $i is a unique number appended to the block's field ids.
//Mode can be add, edit
function makeFeeBlock($guid, $connection2, $i, $mode = 'add', $feeType, $pupilsightFinanceFeeID, $name = '', $description = '', $pupilsightFinanceFeeCategoryID = '', $fee = '', $category = '', $outerBlock = true)
{
    if ($outerBlock) {
        echo "<div id='blockOuter$i' class='blockOuter'>";
    }
    ?>
		<script type="text/javascript">
			$(document).ready(function(){
				$("#blockInner<?php echo $i ?>").css("display","none");
				$("#block<?php echo $i ?>").css("height","72px")

				//Block contents control
				$('#show<?php echo $i ?>').unbind('click').click(function() {
					if ($("#blockInner<?php echo $i ?>").is(":visible")) {
						$("#blockInner<?php echo $i ?>").css("display","none");
						$("#block<?php echo $i ?>").css("height","72px")
						$('#show<?php echo $i ?>').css("background-image", "<?php echo "url(\'".$_SESSION[$guid]['absoluteURL'].'/themes/'.$_SESSION[$guid]['pupilsightThemeName']."/img/plus.png\'"?>)");
					} else {
						$("#blockInner<?php echo $i ?>").slideDown("fast", $("#blockInner<?php echo $i ?>").css("display","table-row"));
						$("#block<?php echo $i ?>").css("height","auto")
						$('#show<?php echo $i ?>').css("background-image", "<?php echo "url(\'".$_SESSION[$guid]['absoluteURL'].'/themes/'.$_SESSION[$guid]['pupilsightThemeName']."/img/minus.png\'"?>)");
					}
				});

				$('#delete<?php echo $i ?>').unbind('click').click(function() {
					if (confirm("Are you sure you want to delete this record?")) {
						$('#blockOuter<?php echo $i ?>').fadeOut(600, function(){ $('#block<?php echo $i ?>').remove(); });
						fee<?php echo $i ?>.destroy() ;
					}
				});
			});
		</script>
		<div class='hiddenReveal' style='border: 1px solid #d8dcdf; margin: 0 0 5px' id="block<?php echo $i ?>" style='padding: 0px'>
			<table class='blank' cellspacing='0' style='width: 100%'>
				<tr>
					<td style='width: 70%'>
						<input name='order[]' type='hidden' value='<?php echo $i ?>'>
						<input <?php if ($feeType == 'Standard') { echo 'readonly'; } ?> maxlength=100 id='name<?php echo $i ?>' name='name<?php echo $i ?>' type='text' style='float: none; border: 1px dotted #aaa; background: none; margin-left: 3px; margin-top: 0px; font-size: 140%; font-weight: bold; width: 350px' value='<?php if (!($mode == 'add' and $feeType == 'Ad Hoc')) { echo htmlPrep($name); }?>' placeholder='<?php echo __('Fee Name'); ?>'><br/>
                        <?php
                        if ($feeType != 'Standard') {
                            ?>
                            <select name="pupilsightFinanceFeeCategoryID<?php echo $i ?>" id="pupilsightFinanceFeeCategoryID<?php echo $i ?>" style='float: none; border: 1px dotted #aaa; background: none; margin-left: 3px; margin-top: 2px; font-size: 110%; font-style: italic; width: 250px'>
                                <?php
                                try {
                                    $dataSelect = array();
                                    $sqlSelect = "SELECT * FROM pupilsightFinanceFeeCategory WHERE active='Y' AND NOT pupilsightFinanceFeeCategoryID=1 ORDER BY name";
                                    $resultSelect = $connection2->prepare($sqlSelect);
                                    $resultSelect->execute($dataSelect);
                                } catch (PDOException $e) {
                                }
                                echo "<option value='0001'>".__('Other').'</option>';
                                while ($rowSelect = $resultSelect->fetch()) {
                                    $selected = '' ;
                                    if ($mode == 'edit') {
                                        if ($rowSelect['pupilsightFinanceFeeCategoryID'] == $pupilsightFinanceFeeCategoryID) {
                                            $selected = 'selected';
                                        }
                                    }
                                    echo "<option $selected value='".$rowSelect['pupilsightFinanceFeeCategoryID']."'>".$rowSelect['name'].'</option>';
                                }
                                ?>
                            </select>
                            <?php
                        }
                        else {
                            ?>
                            <input readonly maxlength=100 id='category<?php echo $i ?>' name='category<?php echo $i ?>' type='text' style='float: none; border: 1px dotted #aaa; background: none; margin-left: 3px; margin-top: 2px; font-size: 110%; font-style: italic; width: 250px' value='<?php echo htmlPrep($category) ?>'>
                            <input type='hidden' id='pupilsightFinanceFeeCategoryID<?php echo $i ?>' name='pupilsightFinanceFeeCategoryID<?php echo $i ?>' value='<?php echo htmlPrep($pupilsightFinanceFeeCategoryID) ?>'>
                            <?php
                        }
                        ?>
						<input <?php if ($feeType == 'Standard') { echo 'readonly'; } ?> maxlength=13 id='fee<?php echo $i ?>' name='fee<?php echo $i ?>' type='text' style='float: none; border: 1px dotted #aaa; background: none; margin-left: 3px;  margin-top: 2px; font-size: 110%; font-style: italic; width: 95px' value='<?php if (!($mode == 'add' and $feeType == 'Ad Hoc')) { echo htmlPrep($fee); } ?>' placeholder='<?php echo __('Value'); if ($_SESSION[$guid]['currency'] != '') { echo ' ('.$_SESSION[$guid]['currency'].')'; } ?>'>
						<script type="text/javascript">
							var fee<?php echo $i ?>=new LiveValidation('fee<?php echo $i ?>');
							fee<?php echo $i ?>.add(Validate.Presence);
							fee<?php echo $i ?>.add( Validate.Format, { pattern: /^-?[0-9]{1,13}(?:\.[0-9]{1,2})?$/, failureMessage: "Invalid number format!" } );
						</script>
					</td>
					<td style='text-align: right; width: 30%'>
						<div style='margin-bottom: 5px'>
							<?php
                            echo "<img id='delete$i' title='".__('Delete')."' src='./themes/".$_SESSION[$guid]['pupilsightThemeName']."/img/garbage.png'/> ";
							echo "<div id='show$i'  title='".__('Show/Hide')."' style='margin-top: -1px; margin-left: 3px; padding-right: 1px; float: right; width: 25px; height: 25px; background-image: url(\"".$_SESSION[$guid]['absoluteURL'].'/themes/'.$_SESSION[$guid]['pupilsightThemeName']."/img/plus.png\"); background-repeat: no-repeat'></div></br>";
							?>
						</div>
						<?php
                        if ($mode == 'plannerEdit') {
                            echo '</br>';
                        }
   				 		?>
						<input type='hidden' name='feeType<?php echo $i ?>' value='<?php echo $feeType ?>'>
						<input type='hidden' name='pupilsightFinanceFeeID<?php echo $i ?>' value='<?php echo $pupilsightFinanceFeeID ?>'>
					</td>
				</tr>
				<tr id="blockInner<?php echo $i ?>">
					<td colspan=2 style='vertical-align: top'>
						<?php
                        echo "<div style='text-align: left; font-weight: bold; margin-top: 15px'>Description</div>";
						if ($pupilsightFinanceFeeID == null) {
							echo "<textarea style='width: 100%;' name='description".$i."'>".htmlPrep($description).'</textarea>';
						} else {
							echo "<div style='width: 100%;'>".htmlPrep($description).'</div>';
							echo "<input type='hidden' name='description".$i."' value='".htmlPrep($description)."'>";
						}
						?>
					</td>
				</tr>
			</table>
		</div>
	<?php
    if ($outerBlock) {
        echo '</div>';
    }
}

function invoiceContents($guid, $connection2, $pupilsightFinanceInvoiceID, $pupilsightSchoolYearID, $currency = '', $email = false, $preview = false)
{
    $return = '';

    //Get currency
    $currency = getSettingByScope($connection2, 'System', 'currency');
    $invoiceeNameStyle = getSettingByScope($connection2, 'Finance', 'invoiceeNameStyle');

    try {
        $data = array('pupilsightSchoolYearID' => $pupilsightSchoolYearID, 'pupilsightSchoolYearID2' => $pupilsightSchoolYearID, 'pupilsightFinanceInvoiceID' => $pupilsightFinanceInvoiceID);
        $sql = 'SELECT pupilsightPerson.pupilsightPersonID, studentID, officialName, surname, preferredName, pupilsightFinanceInvoice.*, companyContact, companyEmail, companyName, companyAddress, pupilsightRollGroup.name AS rollgroup FROM pupilsightFinanceInvoice JOIN pupilsightFinanceInvoicee ON (pupilsightFinanceInvoice.pupilsightFinanceInvoiceeID=pupilsightFinanceInvoicee.pupilsightFinanceInvoiceeID) JOIN pupilsightPerson ON (pupilsightFinanceInvoicee.pupilsightPersonID=pupilsightPerson.pupilsightPersonID) JOIN pupilsightStudentEnrolment ON (pupilsightStudentEnrolment.pupilsightPersonID=pupilsightPerson.pupilsightPersonID) JOIN pupilsightRollGroup ON (pupilsightStudentEnrolment.pupilsightRollGroupID=pupilsightRollGroup.pupilsightRollGroupID) WHERE pupilsightStudentEnrolment.pupilsightSchoolYearID=:pupilsightSchoolYearID2 AND pupilsightFinanceInvoice.pupilsightSchoolYearID=:pupilsightSchoolYearID AND pupilsightFinanceInvoiceID=:pupilsightFinanceInvoiceID';
        $result = $connection2->prepare($sql);
        $result->execute($data);
    } catch (PDOException $e) {
        $return = false;
    }

    if ($result->rowCount() == 1) {
        //Let's go!
        $row = $result->fetch();

        if ($email == true) {
            $return .= "<div style='width: 100%; text-align: right'>";
            $return .= "<a target='_blank' href='".$_SESSION[$guid]['absoluteURL']."'><img height='100px' width='400px' class='School Logo' alt='Logo' src='".$_SESSION[$guid]['absoluteURL'].'/'.$_SESSION[$guid]['organisationLogo']."'/></a>";
            $return .= '</div>';
        }

        //Invoice Text
        $invoiceText = getSettingByScope($connection2, 'Finance', 'invoiceText');
        if ($invoiceText != '') {
            $return .= '<p>';
            $return .= $invoiceText;
            $return .= '</p>';
        }

        $style = '';
        $style2 = '';
        $style3 = '';
        $style4 = '';
        if ($email == true) {
            $style = 'border-top: 1px solid #333; ';
            $style2 = 'border-bottom: 1px solid #333; ';
            $style3 = 'background-color: #f0f0f0; ';
            $style4 = 'background-color: #f6f6f6; ';
        }
        //Invoice Details
        $return .= "<table cellspacing='0' style='width: 100%'>";
        $return .= '<tr>';
        $return .= "<td style='padding-top: 15px; padding-left: 10px; vertical-align: top; $style $style3' colspan=3>";
        $return .= "<span style='font-size: 115%; font-weight: bold'>".__('Invoice To').' ('.$row['invoiceTo'].')</span><br/>';
        if ($row['invoiceTo'] == 'Company') {
            $invoiceTo = '';
            if ($row['companyContact'] != '') {
                $invoiceTo .= '<b>'.$row['companyContact'].'</b>, ';
            }
            if ($row['companyEmail'] != '') {
                $invoiceTo .= $row['companyEmail'].', ';
            }
            if ($row['companyName'] != '') {
                $invoiceTo .= $row['companyName'].', ';
            }
            if ($row['companyAddress'] != '') {
                $invoiceTo .= $row['companyAddress'].', ';
            }
            $return .= substr($invoiceTo, 0, -2);
        } else {
            try {
                $dataParents = array('pupilsightFinanceInvoiceeID' => $row['pupilsightFinanceInvoiceeID']);
                $sqlParents = "SELECT parent.title, parent.surname, parent.preferredName, parent.email, parent.address1, parent.address1District, parent.address1Country, homeAddress, homeAddressDistrict, homeAddressCountry FROM pupilsightFinanceInvoicee JOIN pupilsightPerson AS student ON (pupilsightFinanceInvoicee.pupilsightPersonID=student.pupilsightPersonID) JOIN pupilsightFamilyChild ON (pupilsightFamilyChild.pupilsightPersonID=student.pupilsightPersonID) JOIN pupilsightFamily ON (pupilsightFamilyChild.pupilsightFamilyID=pupilsightFamily.pupilsightFamilyID) JOIN pupilsightFamilyAdult ON (pupilsightFamily.pupilsightFamilyID=pupilsightFamilyAdult.pupilsightFamilyID) JOIN pupilsightPerson AS parent ON (pupilsightFamilyAdult.pupilsightPersonID=parent.pupilsightPersonID) WHERE pupilsightFinanceInvoiceeID=:pupilsightFinanceInvoiceeID AND (contactPriority=1 OR (contactPriority=2 AND contactEmail='Y')) AND parent.status='Full' ORDER BY contactPriority, surname, preferredName";
                $resultParents = $connection2->prepare($sqlParents);
                $resultParents->execute($dataParents);
            } catch (PDOException $e) {
                $return .= "<div class='error'>".$e->getMessage().'</div>';
            }
            if ($resultParents->rowCount() < 1) {
                $return .= "<div class='warning'>".__('There are no family members available to send this receipt to.').'</div>';
            } else {
                $return .= "<ul style='margin-top: 3px; margin-bottom: 3px'>";
                while ($rowParents = $resultParents->fetch()) {
                    $return .= '<li>';
                    $invoiceTo = '';
                    $invoiceTo .= '<b>'.formatName(htmlPrep($rowParents['title']), htmlPrep($rowParents['preferredName']), htmlPrep($rowParents['surname']), 'Parent', false).'</b>, ';
                    if ($rowParents['email'] != '') {
                        $invoiceTo .= $rowParents['email'].', ';
                    }
                    if ($rowParents['address1'] != '') {
                        $invoiceTo .= $rowParents['address1'].', ';
                        if ($rowParents['address1District'] != '') {
                            $invoiceTo .= $rowParents['address1District'].', ';
                        }
                        if ($rowParents['address1Country'] != '') {
                            $invoiceTo .= $rowParents['address1Country'].', ';
                        }
                    } else {
                        $invoiceTo .= $rowParents['homeAddress'].', ';
                        if ($rowParents['homeAddressDistrict'] != '') {
                            $invoiceTo .= $rowParents['homeAddressDistrict'].', ';
                        }
                        if ($rowParents['homeAddressCountry'] != '') {
                            $invoiceTo .= $rowParents['homeAddressCountry'].', ';
                        }
                    }
                    $return .= substr($invoiceTo, 0, -2);
                    $return .= '</li>';
                }
                $return .= '</ul>';
            }
        }
        $return .= '</td>';
        $return .= '</tr>';
        $return .= '<tr>';
        $return .= "<td style='width: 33%; padding-top: 15px; padding-left: 10px; vertical-align: top; $style $style4'>";
        $return .= "<span style='font-size: 115%; font-weight: bold'>".__('Fees For').'</span><br/>';
        if ($invoiceeNameStyle =='Official Name') {
            $return .= htmlPrep($row['officialName'])."<br/><span style='font-style: italic; font-size: 85%'>".__('Roll Group').' '.$row['rollgroup'].'</span><br/>';
        }
        else {
            $return .= formatName('', htmlPrep($row['preferredName']), htmlPrep($row['surname']), 'Student', true)."<br/><span style='font-style: italic; font-size: 85%'>".__('Roll Group').' '.$row['rollgroup'].'</span><br/>';
        }
        $return .= '</td>';
        $return .= "<td style='width: 33%; padding-top: 15px; vertical-align: top; $style $style4'>";
        $return .= "<span style='font-size: 115%; font-weight: bold'>".__('Status').'</span><br/>';
        $return .= $row['status'];
        $return .= '</td>';
        $return .= "<td style='width: 33%; padding-top: 15px; vertical-align: top; $style $style4'>";
        $return .= "<span style='font-size: 115%; font-weight: bold'>".__('Schedule').'</span><br/>';
        if ($row['billingScheduleType'] == 'Ad Hoc') {
            $return .= __('Ad Hoc');
        } else {
            try {
                $dataSched = array('pupilsightFinanceBillingScheduleID' => $row['pupilsightFinanceBillingScheduleID']);
                $sqlSched = 'SELECT * FROM pupilsightFinanceBillingSchedule WHERE pupilsightFinanceBillingScheduleID=:pupilsightFinanceBillingScheduleID';
                $resultSched = $connection2->prepare($sqlSched);
                $resultSched->execute($dataSched);
            } catch (PDOException $e) {
                $return .= "<div class='error'>".$e->getMessage().'</div>';
            }
            if ($resultSched->rowCount() == 1) {
                $rowSched = $resultSched->fetch();
                $return .= $rowSched['name'];
            }
        }
        $return .= '</td>';
        $return .= '</tr>';
        $return .= '<tr>';
        $return .= "<td style='width: 33%; padding-top: 15px; padding-left: 10px; vertical-align: top; $style $style2 $style3'>";
        $return .= "<span style='font-size: 115%; font-weight: bold'>".__('Invoice Issue Date').'</span><br/>';
        $return .= dateConvertBack($guid, $row['invoiceIssueDate']);
        $return .= '</td>';
        $return .= "<td style='width: 33%; padding-top: 15px; vertical-align: top; $style $style2 $style3'>";
        $return .= "<span style='font-size: 115%; font-weight: bold'>".__('Due Date').'</span><br/>';
        $return .= dateConvertBack($guid, $row['invoiceDueDate']);
        $return .= '</td>';
        $return .= "<td style='width: 33%; padding-top: 15px; vertical-align: top; $style $style2 $style3'>";
        $return .= "<span style='font-size: 115%; font-weight: bold'>".__('Invoice Number').'</span><br/>';
        $invoiceNumber = getSettingByScope($connection2, 'Finance', 'invoiceNumber');
        if ($invoiceNumber == 'Person ID + Invoice ID') {
            $return .= ltrim($row['pupilsightPersonID'], '0').'-'.ltrim($pupilsightFinanceInvoiceID, '0');
        } elseif ($invoiceNumber == 'Student ID + Invoice ID') {
            $return .= ltrim($row['studentID'], '0').'-'.ltrim($pupilsightFinanceInvoiceID, '0');
        } else {
            $return .= ltrim($pupilsightFinanceInvoiceID, '0');
        }
        $return .= '</td>';
        $return .= '</tr>';
        if($row['notes']) {
            $return .= '<tr>';
            $return .= "<td colspan=3 style='width: 33%; padding-top: 15px; padding-left: 10px; vertical-align: top; $style $style2 $style3'>";
            $return .= "<span style='font-size: 115%; font-weight: bold'>".__('Notes').'</span><br/>';
            $return .= $row['notes'];
            $return .= '</td>';
            $return .= '</tr>';
        }
        $return .= '</table>';

        try {
            $dataFees['pupilsightFinanceInvoiceID1'] = $row['pupilsightFinanceInvoiceID'];

            if ($preview) { //Get fees from pupilsightFinanceFee
                //Standard
                $sqlFees = "(SELECT pupilsightFinanceInvoiceFee.pupilsightFinanceInvoiceFeeID, pupilsightFinanceInvoiceFee.feeType, pupilsightFinanceFeeCategory.name AS category, pupilsightFinanceFee.name AS name, pupilsightFinanceFee.fee AS fee, pupilsightFinanceFee.description AS description, pupilsightFinanceInvoiceFee.pupilsightFinanceFeeID AS pupilsightFinanceFeeID, pupilsightFinanceInvoiceFee.pupilsightFinanceFeeCategoryID AS pupilsightFinanceFeeCategoryID, sequenceNumber FROM pupilsightFinanceInvoiceFee JOIN pupilsightFinanceFee ON (pupilsightFinanceInvoiceFee.pupilsightFinanceFeeID=pupilsightFinanceFee.pupilsightFinanceFeeID) JOIN pupilsightFinanceFeeCategory ON (pupilsightFinanceFee.pupilsightFinanceFeeCategoryID=pupilsightFinanceFeeCategory.pupilsightFinanceFeeCategoryID) WHERE pupilsightFinanceInvoiceID=:pupilsightFinanceInvoiceID1 AND feeType='Standard')";
            } else { //Get fees from pupilsightFinanceInvoiceFee
                //Standard
                $sqlFees = "(SELECT pupilsightFinanceInvoiceFee.pupilsightFinanceInvoiceFeeID, pupilsightFinanceInvoiceFee.feeType, pupilsightFinanceFeeCategory.name AS category, pupilsightFinanceFee.name AS name, pupilsightFinanceInvoiceFee.fee AS fee, pupilsightFinanceFee.description AS description, pupilsightFinanceInvoiceFee.pupilsightFinanceFeeID AS pupilsightFinanceFeeID, pupilsightFinanceInvoiceFee.pupilsightFinanceFeeCategoryID AS pupilsightFinanceFeeCategoryID, sequenceNumber FROM pupilsightFinanceInvoiceFee JOIN pupilsightFinanceFee ON (pupilsightFinanceInvoiceFee.pupilsightFinanceFeeID=pupilsightFinanceFee.pupilsightFinanceFeeID) JOIN pupilsightFinanceFeeCategory ON (pupilsightFinanceFee.pupilsightFinanceFeeCategoryID=pupilsightFinanceFeeCategory.pupilsightFinanceFeeCategoryID) WHERE pupilsightFinanceInvoiceID=:pupilsightFinanceInvoiceID1 AND feeType='Standard')";
            }
            //Ad Hoc
            $sqlFees .= ' UNION ';
            $dataFees['pupilsightFinanceInvoiceID2'] = $row['pupilsightFinanceInvoiceID'];
            $sqlFees .= "(SELECT pupilsightFinanceInvoiceFee.pupilsightFinanceInvoiceFeeID, pupilsightFinanceInvoiceFee.feeType, pupilsightFinanceFeeCategory.name AS category, pupilsightFinanceInvoiceFee.name AS name, pupilsightFinanceInvoiceFee.fee, pupilsightFinanceInvoiceFee.description AS description, NULL AS pupilsightFinanceFeeID, pupilsightFinanceInvoiceFee.pupilsightFinanceFeeCategoryID AS pupilsightFinanceFeeCategoryID, sequenceNumber FROM pupilsightFinanceInvoiceFee JOIN pupilsightFinanceFeeCategory ON (pupilsightFinanceInvoiceFee.pupilsightFinanceFeeCategoryID=pupilsightFinanceFeeCategory.pupilsightFinanceFeeCategoryID) WHERE pupilsightFinanceInvoiceID=:pupilsightFinanceInvoiceID2 AND feeType='Ad Hoc')";
            $sqlFees .= ' ORDER BY sequenceNumber';
            $resultFees = $connection2->prepare($sqlFees);
            $resultFees->execute($dataFees);
        } catch (PDOException $e) {
            $return .= "<div class='error'>".$e->getMessage().'</div>';
        }
        if ($resultFees->rowCount() < 1) {
            $return .= "<div class='error'>";
            $return .= __('There are no records to display');
            $return .= '</div>';
        } else {
            $feeTotal = 0;

            //Fee table
            $return .= "<h3 style='padding-top: 40px; padding-left: 10px; margin: 0px; $style4'>";
            $return .= __('Fee Table');
            $return .= '</h3>';

            $return .= "<table cellspacing='0' style='width: 100%; $style4'>";
            $return .= "<tr class='head'>";
            $return .= "<th style='text-align: left; padding-left: 10px'>";
            $return .= __('Name');
            $return .= '</th>';
            $return .= "<th style='text-align: left'>";
            $return .= __('Category');
            $return .= '</th>';
            $return .= "<th style='text-align: left'>";
            $return .= __('Description');
            $return .= '</th>';
            $return .= "<th style='text-align: left'>";
            $return .= __('Fee').'<br/>';
            if ($currency != '') {
                $return .= "<span style='font-style: italic; font-size: 85%'>".$currency.'</span>';
            }
            $return .= '</th>';
            $return .= '</tr>';

            $count = 0;
            $rowNum = 'odd';
            while ($rowFees = $resultFees->fetch()) {
                if ($count % 2 == 0) {
                    $rowNum = 'even';
                } else {
                    $rowNum = 'odd';
                }
                ++$count;

                $return .= "<tr style='height: 25px' class=$rowNum>";
                $return .= "<td style='padding-left: 10px'>";
                $return .= $rowFees['name'];
                $return .= '</td>';
                $return .= '<td>';
                $return .= $rowFees['category'];
                $return .= '</td>';
                $return .= '<td>';
                $return .= $rowFees['description'];
                $return .= '</td>';
                $return .= '<td>';
                if (substr($currency, 4) != '') {
                    $return .= substr($currency, 4).' ';
                }
                $return .= number_format($rowFees['fee'], 2, '.', ',');
                $feeTotal += $rowFees['fee'];
                $return .= '</td>';
                $return .= '</tr>';
            }
            $return .= "<tr style='height: 35px' class='current'>";
            $return .= "<td colspan=3 style='text-align: right; $style2'>";
            $return .= '<b>'.__('Invoice Total:').'</b>';
            $return .= '</td>';
            $return .= "<td style='$style2'>";
            if (substr($currency, 4) != '') {
                $return .= substr($currency, 4).' ';
            }
            $return .= '<b>'.number_format($feeTotal, 2, '.', ',').'</b>';
            $return .= '</td>';
            $return .= '</tr>';
            if ($row['status'] == 'Paid - Partial') {
                $return .= "<tr style='height: 35px' class='warning'>";
                $return .= "<td colspan=3 style='text-align: right; $style2'>";
                $return .= '<b>'.__('Amount Outstanding:').'</b>';
                $return .= '</td>';
                $return .= "<td style='$style2'>";
                if (substr($currency, 4) != '') {
                    $return .= substr($currency, 4).' ';
                }
                $return .= '<b>'.number_format(($feeTotal-$row['paidAmount']), 2, '.', ',').'</b>';
                $return .= '</td>';
                $return .= '</tr>';
            }
            $return .= '</table>';
        }

        //Online payment
        $enablePayments = getSettingByScope($connection2, 'System', 'enablePayments');
        $paypalAPIUsername = getSettingByScope($connection2, 'System', 'paypalAPIUsername');
        $paypalAPIPassword = getSettingByScope($connection2, 'System', 'paypalAPIPassword');
        $paypalAPISignature = getSettingByScope($connection2, 'System', 'paypalAPISignature');

        if (!$preview && $enablePayments == 'Y' and $paypalAPIUsername != '' and $paypalAPIPassword != '' and $paypalAPISignature != '' and $row['status'] != 'Paid' and $row['status'] != 'Cancelled' and $row['status'] != 'Refunded') {
            $financeOnlinePaymentEnabled = getSettingByScope($connection2, 'Finance', 'financeOnlinePaymentEnabled');
            $financeOnlinePaymentThreshold = getSettingByScope($connection2, 'Finance', 'financeOnlinePaymentThreshold');
            if ($financeOnlinePaymentEnabled == 'Y') {
                $return .= "<h3 style='margin-top: 40px'>";
                $return .= __('Online Payment');
                $return .= '</h3>';
                $return .= '<p>';
                if ($financeOnlinePaymentThreshold == '' or $financeOnlinePaymentThreshold >= $feeTotal) {
                    $return .= sprintf(__('Payment can be made by credit card, using our secure PayPal payment gateway. When you press Pay Now below, you will be directed to a %1$s page from where you can use PayPal in order to make payment. You can continue with payment through %1$s whether you are logged in or not. During this process we do not see or store your credit card details.'), $_SESSION[$guid]['systemName']).' ';
                    $return .= "<a style='font-weight: bold' href='".$_SESSION[$guid]['absoluteURL']."/index.php?q=/modules/Finance/invoices_payOnline.php&pupilsightFinanceInvoiceID=$pupilsightFinanceInvoiceID&key=".$row['key']."'>".__('Pay Now').'.</a>';
                } else {
                    $return .= "<div class='warning'>".__('Payment is not permitted for this invoice, as the total amount is greater than the permitted online payment threshold.').'</div>';
                }
                $return .= '</p>';
            }
        }

        //Invoice Notes
        $invoiceNotes = getSettingByScope($connection2, 'Finance', 'invoiceNotes');
        if ($invoiceNotes != '') {
            $return .= "<h3 style='margin-top: 40px'>";
            $return .= __('Notes');
            $return .= '</h3>';
            $return .= '<p>';
            $return .= $invoiceNotes;
            $return .= '</p>';
        }

        return $return;
    }
}

//$receiptNumber is the numerical position (counting from 0) of the payment within a series of payments. NULL $receipt Number means it is an old receipt, prior to multiple payments (e.g. before v11)
function receiptContents($guid, $connection2, $pupilsightFinanceInvoiceID, $pupilsightSchoolYearID, $currency = '', $email = false, $receiptNumber)
{
    $return = '';

    //Get currency
    $currency = getSettingByScope($connection2, 'System', 'currency');
    $invoiceeNameStyle = getSettingByScope($connection2, 'Finance', 'invoiceeNameStyle');

    try {
        $data = array('pupilsightSchoolYearID' => $pupilsightSchoolYearID, 'pupilsightSchoolYearID2' => $pupilsightSchoolYearID, 'pupilsightFinanceInvoiceID' => $pupilsightFinanceInvoiceID);
        $sql = 'SELECT pupilsightPerson.pupilsightPersonID, studentID, officialName, surname, preferredName, pupilsightFinanceInvoice.*, companyContact, companyEmail, companyName, companyAddress, pupilsightRollGroup.name AS rollgroup FROM pupilsightFinanceInvoice JOIN pupilsightFinanceInvoicee ON (pupilsightFinanceInvoice.pupilsightFinanceInvoiceeID=pupilsightFinanceInvoicee.pupilsightFinanceInvoiceeID) JOIN pupilsightPerson ON (pupilsightFinanceInvoicee.pupilsightPersonID=pupilsightPerson.pupilsightPersonID) JOIN pupilsightStudentEnrolment ON (pupilsightStudentEnrolment.pupilsightPersonID=pupilsightPerson.pupilsightPersonID) JOIN pupilsightRollGroup ON (pupilsightStudentEnrolment.pupilsightRollGroupID=pupilsightRollGroup.pupilsightRollGroupID) WHERE pupilsightStudentEnrolment.pupilsightSchoolYearID=:pupilsightSchoolYearID2 AND pupilsightFinanceInvoice.pupilsightSchoolYearID=:pupilsightSchoolYearID AND pupilsightFinanceInvoiceID=:pupilsightFinanceInvoiceID';
        $result = $connection2->prepare($sql);
        $result->execute($data);
    } catch (PDOException $e) {
        $return = false;
    }

    if ($result->rowCount() == 1) {
        //Let's go!
        $row = $result->fetch();

        if ($email == true) {
            $return .= "<div style='width: 100%; text-align: right'>";
            $return .= "<a target='_blank' href='".$_SESSION[$guid]['absoluteURL']."'><img height='100px' width='400px' class='School Logo' alt='Logo' src='".$_SESSION[$guid]['absoluteURL'].'/'.$_SESSION[$guid]['organisationLogo']."'/></a>";
            $return .= '</div>';
        }

        //Receipt Text
        $receiptText = getSettingByScope($connection2, 'Finance', 'receiptText');
        if ($receiptText != '') {
            $return .= '<p>';
            $return .= $receiptText;
            $return .= '</p>';
        }

        $style = '';
        $style2 = '';
        $style3 = '';
        $style4 = '';
        if ($email == true) {
            $style = 'border-top: 1px solid #333; ';
            $style2 = 'border-bottom: 1px solid #333; ';
            $style3 = 'background-color: #f0f0f0; ';
            $style4 = 'background-color: #f6f6f6; ';
        }
        //Receipt Details
        $return .= "<table cellspacing='0' style='width: 100%'>";
        $return .= '<tr>';
        $return .= "<td style='padding-top: 15px; padding-left: 10px; vertical-align: top; $style $style3' colspan=3>";
        $return .= "<span style='font-size: 115%; font-weight: bold'>".__('Receipt To').' ('.$row['invoiceTo'].')</span><br/>';
        if ($row['invoiceTo'] == 'Company') {
            $invoiceTo = '';
            if ($row['companyContact'] != '') {
                $invoiceTo .= '<b>'.$row['companyContact'].'</b>, ';
            }
            if ($row['companyEmail'] != '') {
                $invoiceTo .= $row['companyEmail'].', ';
            }
            if ($row['companyName'] != '') {
                $invoiceTo .= $row['companyName'].', ';
            }
            if ($row['companyAddress'] != '') {
                $invoiceTo .= $row['companyAddress'].', ';
            }
            $return .= substr($invoiceTo, 0, -2);
        } else {
            try {
                $dataParents = array('pupilsightFinanceInvoiceeID' => $row['pupilsightFinanceInvoiceeID']);
                $sqlParents = "SELECT parent.title, parent.surname, parent.preferredName, parent.email, parent.address1, parent.address1District, parent.address1Country, homeAddress, homeAddressDistrict, homeAddressCountry FROM pupilsightFinanceInvoicee JOIN pupilsightPerson AS student ON (pupilsightFinanceInvoicee.pupilsightPersonID=student.pupilsightPersonID) JOIN pupilsightFamilyChild ON (pupilsightFamilyChild.pupilsightPersonID=student.pupilsightPersonID) JOIN pupilsightFamily ON (pupilsightFamilyChild.pupilsightFamilyID=pupilsightFamily.pupilsightFamilyID) JOIN pupilsightFamilyAdult ON (pupilsightFamily.pupilsightFamilyID=pupilsightFamilyAdult.pupilsightFamilyID) JOIN pupilsightPerson AS parent ON (pupilsightFamilyAdult.pupilsightPersonID=parent.pupilsightPersonID) WHERE pupilsightFinanceInvoiceeID=:pupilsightFinanceInvoiceeID AND (contactPriority=1 OR (contactPriority=2 AND contactEmail='Y')) AND parent.status='Full' ORDER BY contactPriority, surname, preferredName";
                $resultParents = $connection2->prepare($sqlParents);
                $resultParents->execute($dataParents);
            } catch (PDOException $e) {
                $return .= "<div class='error'>".$e->getMessage().'</div>';
            }
            if ($resultParents->rowCount() < 1) {
                $return .= "<div class='warning'>".__('There are no family members available to send this receipt to.').'</div>';
            } else {
                $return .= "<ul style='margin-top: 3px; margin-bottom: 3px'>";
                while ($rowParents = $resultParents->fetch()) {
                    $return .= '<li>';
                    $invoiceTo = '';
                    $invoiceTo .= '<b>'.formatName(htmlPrep($rowParents['title']), htmlPrep($rowParents['preferredName']), htmlPrep($rowParents['surname']), 'Parent', false).'</b>, ';
                    if ($rowParents['email'] != '') {
                        $invoiceTo .= $rowParents['email'].', ';
                    }
                    if ($rowParents['address1'] != '') {
                        $invoiceTo .= $rowParents['address1'].', ';
                        if ($rowParents['address1District'] != '') {
                            $invoiceTo .= $rowParents['address1District'].', ';
                        }
                        if ($rowParents['address1Country'] != '') {
                            $invoiceTo .= $rowParents['address1Country'].', ';
                        }
                    } else {
                        $invoiceTo .= $rowParents['homeAddress'].', ';
                        if ($rowParents['homeAddressDistrict'] != '') {
                            $invoiceTo .= $rowParents['homeAddressDistrict'].', ';
                        }
                        if ($rowParents['homeAddressCountry'] != '') {
                            $invoiceTo .= $rowParents['homeAddressCountry'].', ';
                        }
                    }
                    $return .= substr($invoiceTo, 0, -2);
                    $return .= '</li>';
                }
                $return .= '</ul>';
            }
        }
        $return .= '</td>';
        $return .= '</tr>';
        $return .= '<tr>';
        $return .= "<td style='width: 33%; padding-top: 15px; padding-left: 10px; vertical-align: top; $style $style4'>";
        $return .= "<span style='font-size: 115%; font-weight: bold'>".__('Fees For').'</span><br/>';
        if ($invoiceeNameStyle =='Official Name') {
            $return .= htmlPrep($row['officialName'])."<br/><span style='font-style: italic; font-size: 85%'>".__('Roll Group').' '.$row['rollgroup'].'</span><br/>';
        }
        else {
            $return .= formatName('', htmlPrep($row['preferredName']), htmlPrep($row['surname']), 'Student', true)."<br/><span style='font-style: italic; font-size: 85%'>".__('Roll Group').' '.$row['rollgroup'].'</span><br/>';
        }
        $return .= '</td>';
        $return .= "<td style='width: 33%; padding-top: 15px; vertical-align: top; $style $style4'>";
        $return .= "<span style='font-size: 115%; font-weight: bold'>".__('Status').'</span><br/>';
        if ($receiptNumber == null) { //Old style receipt, before multiple payments
            $return .= $row['status'];
        } else {
            $paymentFail = false;
            if (is_numeric($receiptNumber) == false) {
                $paymentFail = true;
            } else {
                try {
                    $dataPayment = array('foreignTable' => 'pupilsightFinanceInvoice', 'foreignTableID' => $pupilsightFinanceInvoiceID);
                    $sqlPayment = "SELECT pupilsightPayment.*, surname, preferredName FROM pupilsightPayment JOIN pupilsightPerson ON (pupilsightPayment.pupilsightPersonID=pupilsightPerson.pupilsightPersonID) WHERE foreignTable=:foreignTable AND foreignTableID=:foreignTableID LIMIT $receiptNumber, 1";
                    $resultPayment = $connection2->prepare($sqlPayment);
                    $resultPayment->execute($dataPayment);
                } catch (PDOException $e) {
                    $paymentFail = true;
                    $return .= "<div class='error'>".$e->getMessage().'</div>';
                }

                if ($resultPayment->rowCount() != 1) {
                    $paymentFail = true;
                } else {
                    $rowPayment = $resultPayment->fetch();
                    $return .= $row['status'];
                    if ($row['status'] == 'Paid') {
                        $return .= ' ('.$rowPayment['status'].')';
                    }
                }
            }
        }
        $return .= '</td>';
        $return .= "<td style='width: 33%; padding-top: 15px; vertical-align: top; $style $style4'>";
        $return .= "<span style='font-size: 115%; font-weight: bold'>".__('Schedule').'</span><br/>';
        if ($row['billingScheduleType'] == 'Ad Hoc') {
            $return .= __('Ad Hoc');
        } else {
            try {
                $dataSched = array('pupilsightFinanceBillingScheduleID' => $row['pupilsightFinanceBillingScheduleID']);
                $sqlSched = 'SELECT * FROM pupilsightFinanceBillingSchedule WHERE pupilsightFinanceBillingScheduleID=:pupilsightFinanceBillingScheduleID';
                $resultSched = $connection2->prepare($sqlSched);
                $resultSched->execute($dataSched);
            } catch (PDOException $e) {
                $return .= "<div class='error'>".$e->getMessage().'</div>';
            }
            if ($resultSched->rowCount() == 1) {
                $rowSched = $resultSched->fetch();
                $return .= $rowSched['name'];
            }
        }
        $return .= '</td>';
        $return .= '</tr>';
        $return .= '<tr>';
        $return .= "<td style='width: 33%; padding-top: 15px; padding-left: 10px; vertical-align: top; $style $style2 $style3'>";
        $return .= "<span style='font-size: 115%; font-weight: bold'>".__('Due Date').'</span><br/>';
        $return .= dateConvertBack($guid, $row['invoiceDueDate']);
        $return .= '</td>';
        $return .= "<td style='width: 33%; padding-top: 15px; vertical-align: top; $style $style2 $style3'>";
        $return .= "<span style='font-size: 115%; font-weight: bold'>".__('Date Paid').'</span><br/>';
        $return .= dateConvertBack($guid, $row['paidDate']);
        $return .= '</td>';
        $return .= "<td style='width: 33%; padding-top: 15px; vertical-align: top; $style $style2 $style3'>";
        $return .= "<span style='font-size: 115%; font-weight: bold'>".__('Invoice Number').'</span><br/>';
        $invoiceNumber = getSettingByScope($connection2, 'Finance', 'invoiceNumber');
        if ($invoiceNumber == 'Person ID + Invoice ID') {
            $return .= ltrim($row['pupilsightPersonID'], '0').'-'.ltrim($pupilsightFinanceInvoiceID, '0');
        } elseif ($invoiceNumber == 'Student ID + Invoice ID') {
            $return .= ltrim($row['studentID'], '0').'-'.ltrim($pupilsightFinanceInvoiceID, '0');
        } else {
            $return .= ltrim($pupilsightFinanceInvoiceID, '0');
        }
        if ($receiptNumber != null) {
            $return .= '<br/>';
            $return .= "<span style='font-size: 115%; font-weight: bold'>".__('Receipt Number (on this invoice)').'</span><br/>';
            $return .= ($receiptNumber + 1);
        }
        $return .= '</td>';
        if($row['notes']) {
            $return .= '<tr>';
            $return .= "<td colspan=3 style='width: 33%; padding-top: 15px; padding-left: 10px; vertical-align: top; $style $style2 $style3'>";
            $return .= "<span style='font-size: 115%; font-weight: bold'>".__('Notes').'</span><br/>';
            $return .= $row['notes'];
            $return .= '</td>';
            $return .= '</tr>';
        }
        $return .= '</tr>';
        $return .= '</table>';

        //Check itemisation status
        $hideItemisation = getSettingByScope($connection2, 'Finance', 'hideItemisation');

        try {
            $dataFees['pupilsightFinanceInvoiceID'] = $row['pupilsightFinanceInvoiceID'];
            $sqlFees = 'SELECT pupilsightFinanceInvoiceFee.pupilsightFinanceInvoiceFeeID, pupilsightFinanceInvoiceFee.feeType, pupilsightFinanceFeeCategory.name AS category, pupilsightFinanceInvoiceFee.name AS name, pupilsightFinanceInvoiceFee.fee, pupilsightFinanceInvoiceFee.description AS description, NULL AS pupilsightFinanceFeeID, pupilsightFinanceInvoiceFee.pupilsightFinanceFeeCategoryID AS pupilsightFinanceFeeCategoryID, sequenceNumber FROM pupilsightFinanceInvoiceFee JOIN pupilsightFinanceFeeCategory ON (pupilsightFinanceInvoiceFee.pupilsightFinanceFeeCategoryID=pupilsightFinanceFeeCategory.pupilsightFinanceFeeCategoryID) WHERE pupilsightFinanceInvoiceID=:pupilsightFinanceInvoiceID ORDER BY sequenceNumber';
            $resultFees = $connection2->prepare($sqlFees);
            $resultFees->execute($dataFees);
        } catch (PDOException $e) {
            $return .= "<div class='error'>".$e->getMessage().'</div>';
        }
        if ($resultFees->rowCount() < 1) {
            $return .= "<div class='error'>";
            $return .= __('There are no records to display');
            $return .= '</div>';
        } else {
            $feeTotal = 0;

            if ($hideItemisation != 'Y') { //Do not hide itemisation
                //Fee table
                $return .= "<h3 style='padding-top: 40px; padding-left: 10px; margin: 0px; $style4'>";
                $return .= __('Fee Table');
                $return .= '</h3>';

                $return .= "<table cellspacing='0' style='width: 100%; $style4'>";
                $return .= "<tr class='head'>";
                $return .= "<th style='text-align: left; padding-left: 10px'>";
                $return .= __('Name');
                $return .= '</th>';
                $return .= "<th style='text-align: left'>";
                $return .= __('Category');
                $return .= '</th>';
                $return .= "<th style='text-align: left'>";
                $return .= __('Description');
                $return .= '</th>';
                $return .= "<th style='text-align: left; width: 150px'>";
                $return .= __('Fee').'<br/>';
                if ($currency != '') {
                    $return .= "<span style='font-style: italic; font-size: 85%'>".$currency.'</span>';
                }
                $return .= '</th>';
                $return .= '</tr>';

                $count = 0;
                $rowNum = 'odd';
                while ($rowFees = $resultFees->fetch()) {
                    if ($count % 2 == 0) {
                        $rowNum = 'even';
                    } else {
                        $rowNum = 'odd';
                    }
                    ++$count;

                    $return .= "<tr style='height: 25px' class=$rowNum>";
                    $return .= "<td style='padding-left: 10px'>";
                    $return .= $rowFees['name'];
                    $return .= '</td>';
                    $return .= '<td>';
                    $return .= $rowFees['category'];
                    $return .= '</td>';
                    $return .= '<td>';
                    $return .= $rowFees['description'];
                    $return .= '</td>';
                    $return .= '<td>';
                    if (substr($currency, 4) != '') {
                        $return .= substr($currency, 4).' ';
                    }
                    $return .= number_format($rowFees['fee'], 2, '.', ',');
                    $feeTotal += $rowFees['fee'];
                    $return .= '</td>';
                    $return .= '</tr>';
                }
                $return .= "<tr style='height: 35px' class='current'>";
                $return .= "<td colspan=3 style='text-align: right; $style2'>";
                $return .= '<b>'.__('Invoice Total:').'</b>';
                $return .= '</td>';
                $return .= "<td style='$style2'>";
                if (substr($currency, 4) != '') {
                    $return .= substr($currency, 4).' ';
                }
                $return .= '<b>'.number_format($feeTotal, 2, '.', ',').'</b>';
                $return .= '</td>';
                $return .= '</tr>';
                $return .= '</table>';
            } else {
                $return .= "<h3 style='padding-top: 40px; padding-left: 10px; margin: 0px; $style4'>";
                $return .= __('Amount Due');
                $return .= '</h3>';
                while ($rowFees = $resultFees->fetch()) {
                    $feeTotal += $rowFees['fee'];
                }
                $return .= "<p style='margin-top: 10px; text-align: right'>";
                $return .= __('Invoice Total').': ';
                if (substr($currency, 4) != '') {
                    $return .= substr($currency, 4).' ';
                }
                $return .= '<b>'.number_format($feeTotal, 2, '.', ',').'</b>';
                $return .= '</p>';
            }
        }

        //Payment details
        if ($receiptNumber == null) { //Old style receipt, before multiple payments
            $return .= "<h3 style='padding-top: 40px; padding-left: 10px; margin: 0px; $style4'>";
            $return .= __('Payment Details');
            $return .= '</h3>';
            $return .= "<p style='margin-top: 10px; text-align: right'>";
            $return .= __('Payment Total').': ';
            if (substr($currency, 4) != '') {
                $return .= substr($currency, 4).' ';
            }
            $return .= '<b>'.number_format($row['paidAmount'], 2, '.', ',').'</b>';
            $return .= '</p>';
        } else { //New style receipt, post multiple payments
            if ($hideItemisation != 'Y') { //Do not hide itemisation
                $return .= "<h3 style='padding-top: 40px; padding-left: 10px; margin: 0px; $style4'>";
                $return .= __('Payment Details');
                $return .= '</h3>';
                if ($paymentFail) {
                    $return .= "<div class='error'>";
                    $return .= __('There are no records to display.');
                    $return .= '</div>';
                } else {
                    $return .= getPaymentLog($connection2, $guid, 'pupilsightFinanceInvoice', $pupilsightFinanceInvoiceID, $rowPayment['pupilsightPaymentID']);
                }
            } else {
                $return .= "<h3 style='padding-top: 40px; padding-left: 10px; margin: 0px; $style4'>";
                $return .= __('Amount Paid');
                $return .= '</h3>';
                if ($paymentFail) {
                    $return .= "<div class='error'>";
                    $return .= __('There are no records to display.');
                    $return .= '</div>';
                } else {
                    $return .= "<p style='margin-top: 10px; text-align: right'>";
                    $return .= __('Payment Total').': ';
                    if (substr($currency, 4) != '') {
                        $return .= substr($currency, 4).' ';
                    }
                    $return .= '<b>'.number_format($rowPayment['amount'], 2, '.', ',').'</b>';
                    $return .= '</p>';
                }
            }
        }

        //Display balance
        if ($row['status'] == 'Paid' or $row['status'] == 'Paid - Partial' or $row['status'] == 'Refunded') {
            if (@$rowPayment['status'] == 'Partial') {
                if ($receiptNumber != null) { //New style receipt, with multiple payments
                    $balanceFail = false;
                    $amountPaid = 0;
                    //Get amount paid until this point
                    try {
                        $dataPayment2 = array('foreignTable' => 'pupilsightFinanceInvoice', 'foreignTableID' => $pupilsightFinanceInvoiceID);
                        $sqlPayment2 = 'SELECT pupilsightPayment.*, surname, preferredName FROM pupilsightPayment JOIN pupilsightPerson ON (pupilsightPayment.pupilsightPersonID=pupilsightPerson.pupilsightPersonID) WHERE foreignTable=:foreignTable AND foreignTableID=:foreignTableID ORDER BY timestamp LIMIT 0, '.($receiptNumber + 1);
                        $resultPayment2 = $connection2->prepare($sqlPayment2);
                        $resultPayment2->execute($dataPayment2);
                    } catch (PDOException $e) {
                        $balanceFail = true;
                    }

                    if ($resultPayment2->rowCount() < 1) {
                        $paymentFail = true;
                    } else {
                        while ($rowPayment2 = $resultPayment2->fetch()) {
                            $amountPaid += $rowPayment2['amount'];
                        }
                    }

                    if ($row['status'] == 'Refunded') {
                        $return .= "<h3 style='padding-top: 40px; padding-left: 10px; margin: 0px; $style4'>";
                        $return .= __('Refund Issued');
                        $return .= '</h3>';
                        $return .= '<table cellspacing="0" style="width: 100%; $style4">';
                        $return .= "<tr style='height: 35px' class='current error'>";
                        $return .= "<td style='text-align: right; $style2'>";
                        $return .= '<b>'.__('Refund Total:').'</b>';
                        $return .= '</td>';
                        $return .= "<td style='width: 135px; $style2'>";
                        if (substr($currency, 4) != '') {
                            $return .= substr($currency, 4).' ';
                        }
                        $return .= '<b>'.number_format($amountPaid, 2, '.', ',').'</b>';
                        $return .= '</td>';
                        $return .= '</tr>';
                        $return .= '</table>';
                    } else if ($balanceFail == false) {
                        $return .= "<h3 style='padding-top: 40px; padding-left: 10px; margin: 0px; $style4'>";
                        $return .= __('Outstanding Balance');
                        $return .= '</h3>';
                        if ($hideItemisation != 'Y') { //Do not hide itemisation
                            $return .= "<table cellspacing='0' style='width: 100%; $style4'>";
                            $return .= "<tr style='height: 35px' class='current'>";
                            $return .= "<td style='text-align: right; $style2'>";
                            $return .= '<b>'.__('Outstanding Balance:').'</b>';
                            $return .= '</td>';
                            $return .= "<td style='width: 135px; $style2'>";
                            if (substr($currency, 4) != '') {
                                $return .= substr($currency, 4).' ';
                            }
                            $return .= '<b>'.number_format(($feeTotal - $amountPaid), 2, '.', ',').'</b>';
                            $return .= '</td>';
                            $return .= '</tr>';
                            $return .= '</table>';
                        } else { //Hide itemisation
                            $return .= "<p style='margin-top: 10px; text-align: right'>";
                            $return .= __('Payment Total').': ';
                            if (substr($currency, 4) != '') {
                                $return .= substr($currency, 4).' ';
                            }
                            $return .= '<b>'.number_format(($feeTotal - $amountPaid), 2, '.', ',').'</b>';
                            $return .= '</p>';
                        }
                    }
                }
            } else if (@$rowPayment['status'] == 'Complete') {
                if ($row['status'] == 'Refunded') {
                    $return .= "<h3 style='padding-top: 40px; padding-left: 10px; margin: 0px; $style4'>";
                    $return .= __('Refund Issued');
                    $return .= '</h3>';
                    $return .= '<table cellspacing="0" style="width: 100%; $style4">';
                    $return .= "<tr style='height: 35px' class='current error'>";
                    $return .= "<td style='text-align: right; $style2'>";
                    $return .= '<b>'.__('Refund Total:').'</b>';
                    $return .= '</td>';
                    $return .= "<td style='width: 135px; $style2'>";
                    if (substr($currency, 4) != '') {
                        $return .= substr($currency, 4).' ';
                    }
                    $return .= '<b>'.number_format($rowPayment['amount'], 2, '.', ',').'</b>';
                    $return .= '</td>';
                    $return .= '</tr>';
                    $return .= '</table>';
                }
            }
        }

        //Receipts Notes
        $receiptNotes = getSettingByScope($connection2, 'Finance', 'receiptNotes');
        if ($receiptNotes != '') {
            $return .= "<h3 style='margin-top: 40px'>";
            $return .= __('Notes');
            $return .= '</h3>';
            $return .= '<p>';
            $return .= $receiptNotes;
            $return .= '</p>';
        }

        return $return;
    }
}

function getBudgetAllocation($pdo, $pupilsightFinanceBudgetCycleID, $pupilsightFinanceBudgetID)
{
    $data = array('pupilsightFinanceBudgetCycleID' => $pupilsightFinanceBudgetCycleID, 'pupilsightFinanceBudgetID' => $pupilsightFinanceBudgetID);
    $sql = "SELECT value FROM pupilsightFinanceBudgetCycleAllocation WHERE pupilsightFinanceBudgetCycleID=:pupilsightFinanceBudgetCycleID AND pupilsightFinanceBudgetID=:pupilsightFinanceBudgetID";
    $result = $pdo->executeQuery($data, $sql);

    return ($result->rowCount() == 1)? $result->fetchColumn(0) : __('N/A');
}

function getBudgetAllocated($pdo, $pupilsightFinanceBudgetCycleID, $pupilsightFinanceBudgetID)
{
    $data = array('pupilsightFinanceBudgetCycleID' => $pupilsightFinanceBudgetCycleID, 'pupilsightFinanceBudgetID' => $pupilsightFinanceBudgetID);
    $sql = "(SELECT cost FROM pupilsightFinanceExpense WHERE countAgainstBudget='Y' AND pupilsightFinanceBudgetCycleID=:pupilsightFinanceBudgetCycleID AND pupilsightFinanceBudgetID=:pupilsightFinanceBudgetID AND FIELD(status, 'Approved', 'Order'))
        UNION
        (SELECT paymentAmount AS cost FROM pupilsightFinanceExpense WHERE countAgainstBudget='Y' AND pupilsightFinanceBudgetCycleID=:pupilsightFinanceBudgetCycleID AND pupilsightFinanceBudgetID=:pupilsightFinanceBudgetID AND FIELD(status, 'Paid'))";
    $result = $pdo->executeQuery($data, $sql);

    $budgetAllocated = 0;
    if ($result->rowCount() > 0) {
        $budgetAllocated = array_reduce($result->fetchAll(), function($sum, $item) {
            $sum += $item['cost'];
            return $sum;
        }, 0);
    }
    return $budgetAllocated;
}

function getInvoiceTotalFee($pdo, $pupilsightFinanceInvoiceID, $status)
{
    try {
        $dataTotal = array('pupilsightFinanceInvoiceID' => $pupilsightFinanceInvoiceID);
        if ($status == 'Pending') {
            $sqlTotal = 'SELECT pupilsightFinanceInvoiceFee.fee AS fee, pupilsightFinanceFee.fee AS fee2 FROM pupilsightFinanceInvoiceFee LEFT JOIN pupilsightFinanceFee ON (pupilsightFinanceInvoiceFee.pupilsightFinanceFeeID=pupilsightFinanceFee.pupilsightFinanceFeeID) WHERE pupilsightFinanceInvoiceID=:pupilsightFinanceInvoiceID';
        } else {
            $sqlTotal = 'SELECT pupilsightFinanceInvoiceFee.fee AS fee, NULL AS fee2 FROM pupilsightFinanceInvoiceFee WHERE pupilsightFinanceInvoiceID=:pupilsightFinanceInvoiceID';
        }
        $resultTotal = $pdo->executeQuery($dataTotal, $sqlTotal);
    } catch (PDOException $e) {
        return null;
    }

    $totalFee = 0;

    while ($rowTotal = $resultTotal->fetch()) {
        $totalFee += is_numeric($rowTotal['fee2'])? $rowTotal['fee2'] : $rowTotal['fee'];
    }

    return $totalFee;
}
