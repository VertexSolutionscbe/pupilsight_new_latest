<?php
/*
Pupilsight, Flexible & Open School System
*/

include '../../pupilsight.php';

//Module includes
include './moduleFunctions.php';

$pupilsightFinanceBudgetCycleID = $_POST['pupilsightFinanceBudgetCycleID'];
$pupilsightFinanceBudgetID2 = $_POST['pupilsightFinanceBudgetID2'];
$status2 = $_POST['status2'];

if ($pupilsightFinanceBudgetCycleID == '') { echo 'Fatal error loading this page!';
} else {
    $URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_POST['address'])."/expenses_manage_add.php&pupilsightFinanceBudgetCycleID=$pupilsightFinanceBudgetCycleID&pupilsightFinanceBudgetID2=$pupilsightFinanceBudgetID2&status2=$status2";

    if (isActionAccessible($guid, $connection2, '/modules/Finance/expenses_manage_add.php', 'Manage Expenses_all') == false) {
        $URL .= '&return=error0';
        header("Location: {$URL}");
    } else {
        $allowExpenseAdd = getSettingByScope($connection2, 'Finance', 'allowExpenseAdd');
        if ($allowExpenseAdd != 'Y') {
            $URL .= '&return=error0';
            header("Location: {$URL}");
        } else {
            $pupilsightFinanceBudgetID = $_POST['pupilsightFinanceBudgetID'];
            $status = $_POST['status'];
            $title = $_POST['title'];
            $body = $_POST['body'];
            $cost = $_POST['cost'];
            $countAgainstBudget = $_POST['countAgainstBudget'];
            $purchaseBy = $_POST['purchaseBy'];
            $purchaseDetails = $_POST['purchaseDetails'];
            if ($status == 'Paid') {
                $paymentDate = dateConvert($guid, $_POST['paymentDate']);
                $paymentAmount = $_POST['paymentAmount'];
                $pupilsightPersonIDPayment = $_POST['pupilsightPersonIDPayment'];
                $paymentMethod = $_POST['paymentMethod'];
                $paymentID = $_POST['paymentID'];
            } else {
                $paymentDate = null;
                $paymentAmount = null;
                $pupilsightPersonIDPayment = null;
                $paymentMethod = null;
                $paymentID = null;
            }

            if ($status == '' or $title == '' or $cost == '' or $countAgainstBudget == '' or $purchaseBy == '' or ($status == 'Paid' and ($paymentDate == '' or $paymentAmount == '' or $pupilsightPersonIDPayment == '' or $paymentMethod == ''))) {
                $URL .= '&return=error1';
                header("Location: {$URL}");
            } else {
                //Write to database
                try {
                    $data = array('pupilsightFinanceBudgetCycleID' => $pupilsightFinanceBudgetCycleID, 'pupilsightFinanceBudgetID' => $pupilsightFinanceBudgetID, 'title' => $title, 'body' => $body, 'status' => $status, 'statusApprovalBudgetCleared' => 'Y', 'cost' => $cost, 'countAgainstBudget' => $countAgainstBudget, 'purchaseBy' => $purchaseBy, 'purchaseDetails' => $purchaseDetails, 'pupilsightPersonIDCreator' => $_SESSION[$guid]['pupilsightPersonID'], 'paymentDate' => $paymentDate, 'paymentAmount' => $paymentAmount, 'pupilsightPersonIDPayment' => $pupilsightPersonIDPayment, 'paymentMethod' => $paymentMethod, 'paymentID' => $paymentID);
                    $sql = "INSERT INTO pupilsightFinanceExpense SET pupilsightFinanceBudgetCycleID=:pupilsightFinanceBudgetCycleID, pupilsightFinanceBudgetID=:pupilsightFinanceBudgetID, title=:title, body=:body, status=:status, statusApprovalBudgetCleared=:statusApprovalBudgetCleared, cost=:cost, countAgainstBudget=:countAgainstBudget, purchaseBy=:purchaseBy, purchaseDetails=:purchaseDetails, pupilsightPersonIDCreator=:pupilsightPersonIDCreator, timestampCreator='".date('Y-m-d H:i:s')."', paymentDate=:paymentDate, paymentAmount=:paymentAmount, pupilsightPersonIDPayment=:pupilsightPersonIDPayment, paymentMethod=:paymentMethod, paymentID=:paymentID";
                    $result = $connection2->prepare($sql);
                    $result->execute($data);
                } catch (PDOException $e) {
                    $URL .= '&return=error2';
                    header("Location: {$URL}");
                    exit();
                }

                $pupilsightFinanceExpenseID = $connection2->lastInsertID();

                //Add log entry
                try {
                    $data = array('pupilsightFinanceExpenseID' => $pupilsightFinanceExpenseID, 'pupilsightPersonID' => $_SESSION[$guid]['pupilsightPersonID']);
                    $sql = "INSERT INTO pupilsightFinanceExpenseLog SET pupilsightFinanceExpenseID=:pupilsightFinanceExpenseID, pupilsightPersonID=:pupilsightPersonID, timestamp='".date('Y-m-d H:i:s')."', action='Approval - Exempt', comment=''";
                    $result = $connection2->prepare($sql);
                    $result->execute($data);
                } catch (PDOException $e) {
                    $URL .= '&return=error2';
                    header("Location: {$URL}");
                    exit();
                }

                //Last insert ID
                $AI = str_pad($connection2->lastInsertID(), 14, '0', STR_PAD_LEFT);

                //Add Payment log entry if needed
                if ($status == 'Paid') {
                    try {
                        $data = array('pupilsightFinanceExpenseID' => $pupilsightFinanceExpenseID, 'pupilsightPersonID' => $_SESSION[$guid]['pupilsightPersonID']);
                        $sql = "INSERT INTO pupilsightFinanceExpenseLog SET pupilsightFinanceExpenseID=:pupilsightFinanceExpenseID, pupilsightPersonID=:pupilsightPersonID, timestamp='".date('Y-m-d H:i:s')."', action='Payment', comment=''";
                        $result = $connection2->prepare($sql);
                        $result->execute($data);
                    } catch (PDOException $e) {
                        $URL .= '&return=error2';
                        header("Location: {$URL}");
                        exit();
                    }
                }

                $URL .= "&return=success0&editID=$pupilsightFinanceExpenseID";
                header("Location: {$URL}");
            }
        }
    }
}
