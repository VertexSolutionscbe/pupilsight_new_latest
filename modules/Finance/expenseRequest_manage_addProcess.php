<?php
/*
Pupilsight, Flexible & Open School System
*/

include '../../pupilsight.php';

//Module includes
include './moduleFunctions.php';

$pupilsightFinanceBudgetCycleID = $_POST['pupilsightFinanceBudgetCycleID'];
$pupilsightFinanceBudgetID = $_POST['pupilsightFinanceBudgetID'];
$status = $_POST['status'];
$pupilsightFinanceBudgetID2 = $_POST['pupilsightFinanceBudgetID2'];
$status2 = $_POST['status2'];

if ($pupilsightFinanceBudgetCycleID == '' or $pupilsightFinanceBudgetID == '' or $status == '' or $status != 'Requested') { echo 'Fatal error loading this page!';
} else {
    $URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_POST['address'])."/expenseRequest_manage_add.php&pupilsightFinanceBudgetCycleID=$pupilsightFinanceBudgetCycleID&pupilsightFinanceBudgetID2=$pupilsightFinanceBudgetID2&status2=$status2";

    if (isActionAccessible($guid, $connection2, '/modules/Finance/expenseRequest_manage_add.php') == false) {
        $URL .= '&return=error0';
        header("Location: {$URL}");
    } else {
        $title = $_POST['title'];
        $body = $_POST['body'];
        $cost = $_POST['cost'];
        $countAgainstBudget = $_POST['countAgainstBudget'];
        $purchaseBy = $_POST['purchaseBy'];
        $purchaseDetails = $_POST['purchaseDetails'];

        if ($title == '' or $cost == '' or $purchaseBy == '' or $countAgainstBudget == '') {
            $URL .= '&return=error1';
            header("Location: {$URL}");
        } else {
            //Prepare approval settings
            $budgetLevelExpenseApproval = getSettingByScope($connection2, 'Finance', 'budgetLevelExpenseApproval');
            if ($budgetLevelExpenseApproval == '') {
                $URL .= '&return=error2';
                header("Location: {$URL}");
                exit();
            } else {
                if ($budgetLevelExpenseApproval == 'N') { //Skip budget-level approval
                    $statusApprovalBudgetCleared = 'Y';
                } else {
                    $budgets = getBudgetsByPerson($connection2, $_SESSION[$guid]['pupilsightPersonID'], $pupilsightFinanceBudgetID);
                    if (@$budgets[0][2] == 'Full') { //I can self-approve budget-level, as have Full access
                        $statusApprovalBudgetCleared = 'Y';
                    } else { //I cannot self-approve budget-level
                        $statusApprovalBudgetCleared = 'N';
                    }
                }
            }

            //Write to database
            try {
                $data = array('pupilsightFinanceBudgetCycleID' => $pupilsightFinanceBudgetCycleID, 'pupilsightFinanceBudgetID' => $pupilsightFinanceBudgetID, 'title' => $title, 'body' => $body, 'status' => $status, 'statusApprovalBudgetCleared' => $statusApprovalBudgetCleared, 'cost' => $cost, 'countAgainstBudget' => $countAgainstBudget, 'purchaseBy' => $purchaseBy, 'purchaseDetails' => $purchaseDetails, 'pupilsightPersonIDCreator' => $_SESSION[$guid]['pupilsightPersonID']);
                $sql = "INSERT INTO pupilsightFinanceExpense SET pupilsightFinanceBudgetCycleID=:pupilsightFinanceBudgetCycleID, pupilsightFinanceBudgetID=:pupilsightFinanceBudgetID, title=:title, body=:body, status=:status, statusApprovalBudgetCleared=:statusApprovalBudgetCleared, cost=:cost, countAgainstBudget=:countAgainstBudget, purchaseBy=:purchaseBy, purchaseDetails=:purchaseDetails, pupilsightPersonIDCreator=:pupilsightPersonIDCreator, timestampCreator='".date('Y-m-d H:i:s')."'";
                $result = $connection2->prepare($sql);
                $result->execute($data);
            } catch (PDOException $e) {
                $URL .= '&return=error2';
                header("Location: {$URL}");
                exit();
            }

            $pupilsightFinanceExpenseID = str_pad($connection2->lastInsertID(), 14, '0', STR_PAD_LEFT);

            //Add log entry
            try {
                $data = array('pupilsightFinanceExpenseID' => $pupilsightFinanceExpenseID, 'pupilsightPersonID' => $_SESSION[$guid]['pupilsightPersonID']);
                $sql = "INSERT INTO pupilsightFinanceExpenseLog SET pupilsightFinanceExpenseID=:pupilsightFinanceExpenseID, pupilsightPersonID=:pupilsightPersonID, timestamp='".date('Y-m-d H:i:s')."', action='Request', comment=''";
                $result = $connection2->prepare($sql);
                $result->execute($data);
            } catch (PDOException $e) {
                echo $e->getMessage();
                $URL .= '&return=error2';
                header("Location: {$URL}");
                exit();
            }

            //Last insert ID
            $AI = str_pad($connection2->lastInsertID(), 14, '0', STR_PAD_LEFT);

            //Do notifications
            $partialFail = false;
            if (setExpenseNotification($guid, $pupilsightFinanceExpenseID, $pupilsightFinanceBudgetCycleID, $connection2) == false) {
                $partialFail = true;
            }

            if ($partialFail == true) {
                $URL .= "&return=success1&editID=$AI";
                header("Location: {$URL}");
            } else {
                $URL .= "&return=success0&editID=$AI";
                header("Location: {$URL}");
            }
        }
    }
}
