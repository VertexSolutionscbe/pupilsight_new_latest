<?php
/*
Pupilsight, Flexible & Open School System
*/

include '../../pupilsight.php';

//Module includes
include './moduleFunctions.php';

$pupilsightFinanceBudgetCycleID = $_POST['pupilsightFinanceBudgetCycleID'];
$pupilsightFinanceExpenseID = $_POST['pupilsightFinanceExpenseID'];
$status2 = $_POST['status2'];
$pupilsightFinanceBudgetID2 = $_POST['pupilsightFinanceBudgetID2'];

if ($pupilsightFinanceBudgetCycleID == '') { echo 'Fatal error loading this page!';
} else {
    $URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_POST['address'])."/expenseRequest_manage_view.php&pupilsightFinanceExpenseID=$pupilsightFinanceExpenseID&pupilsightFinanceBudgetCycleID=$pupilsightFinanceBudgetCycleID&pupilsightFinanceBudgetID2=$pupilsightFinanceBudgetID2&status2=$status2";

    if (isActionAccessible($guid, $connection2, '/modules/Finance/expenseRequest_manage_view.php') == false) {
        $URL .= '&return=error0';
        header("Location: {$URL}");
    } else {
        $highestAction = getHighestGroupedAction($guid, $_POST['address'], $connection2);
        if ($highestAction == false) {
            $URL .= '&return=error0';
            header("Location: {$URL}");
        } else {
            //Check if params are specified
            if ($pupilsightFinanceExpenseID == '' or $pupilsightFinanceBudgetCycleID == '') {
                $URL .= '&return=error0';
                header("Location: {$URL}");
            } else {
                $budgetsAccess = false;
                if ($highestAction == 'Manage Expenses_all') { //Access to everything {
                    $budgetsAccess = true;
                } else {
                    //Check if have Full or Write in any budgets
                    $budgets = getBudgetsByPerson($connection2, $_SESSION[$guid]['pupilsightPersonID']);
                    if (is_array($budgets) && count($budgets)>0) {
                        foreach ($budgets as $budget) {
                            if ($budget[2] == 'Full' or $budget[2] == 'Write') {
                                $budgetsAccess = true;
                            }
                        }
                    }
                }

                if ($budgetsAccess == false) {
                    $URL .= '&return=error0';
                    header("Location: {$URL}");
                } else {
                    //Get and check settings
                    $expenseApprovalType = getSettingByScope($connection2, 'Finance', 'expenseApprovalType');
                    $budgetLevelExpenseApproval = getSettingByScope($connection2, 'Finance', 'budgetLevelExpenseApproval');
                    $expenseRequestTemplate = getSettingByScope($connection2, 'Finance', 'expenseRequestTemplate');
                    if ($expenseApprovalType == '' or $budgetLevelExpenseApproval == '') {
                        $URL .= '&return=error0';
                        header("Location: {$URL}");
                    } else {
                        //Check if there are approvers
                        try {
                            $data = array();
                            $sql = "SELECT * FROM pupilsightFinanceExpenseApprover JOIN pupilsightPerson ON (pupilsightFinanceExpenseApprover.pupilsightPersonID=pupilsightPerson.pupilsightPersonID) WHERE status='Full'";
                            $result = $connection2->prepare($sql);
                            $result->execute($data);
                        } catch (PDOException $e) {
                            echo $e->getMessage();
                        }

                        if ($result->rowCount() < 1) {
                            $URL .= '&return=error0';
                            header("Location: {$URL}");
                        } else {
                            $approvers = $result->fetchAll();

                            //Ready to go! Just check record exists and we have access, and load it ready to use...
                            try {
                                //Set Up filter wheres
                                $data = array('pupilsightFinanceBudgetCycleID' => $pupilsightFinanceBudgetCycleID, 'pupilsightFinanceExpenseID' => $pupilsightFinanceExpenseID);
                                //GET THE DATA ACCORDING TO FILTERS
                                if ($highestAction == 'Manage Expenses_all') { //Access to everything
                                    $sql = "SELECT pupilsightFinanceExpense.*, pupilsightFinanceBudget.name AS budget, surname, preferredName, 'Full' AS access
										FROM pupilsightFinanceExpense
										JOIN pupilsightFinanceBudget ON (pupilsightFinanceExpense.pupilsightFinanceBudgetID=pupilsightFinanceBudget.pupilsightFinanceBudgetID)
										JOIN pupilsightPerson ON (pupilsightFinanceExpense.pupilsightPersonIDCreator=pupilsightPerson.pupilsightPersonID)
										WHERE pupilsightFinanceBudgetCycleID=:pupilsightFinanceBudgetCycleID AND pupilsightFinanceExpenseID=:pupilsightFinanceExpenseID";
                                } else { //Access only to own budgets
                                    $data['pupilsightPersonID'] = $_SESSION[$guid]['pupilsightPersonID'];
                                    $sql = "SELECT pupilsightFinanceExpense.*, pupilsightFinanceBudget.name AS budget, surname, preferredName, access
										FROM pupilsightFinanceExpense
										JOIN pupilsightFinanceBudget ON (pupilsightFinanceExpense.pupilsightFinanceBudgetID=pupilsightFinanceBudget.pupilsightFinanceBudgetID)
										JOIN pupilsightFinanceBudgetPerson ON (pupilsightFinanceBudgetPerson.pupilsightFinanceBudgetID=pupilsightFinanceBudget.pupilsightFinanceBudgetID)
										JOIN pupilsightPerson ON (pupilsightFinanceExpense.pupilsightPersonIDCreator=pupilsightPerson.pupilsightPersonID)
										WHERE pupilsightFinanceBudgetCycleID=:pupilsightFinanceBudgetCycleID AND pupilsightFinanceExpenseID=:pupilsightFinanceExpenseID AND pupilsightFinanceBudgetPerson.pupilsightPersonID=:pupilsightPersonID AND (access='Full' OR access='Write')";
                                }
                                $result = $connection2->prepare($sql);
                                $result->execute($data);
                            } catch (PDOException $e) {
                                $URL .= '&return=error2';
                                header("Location: {$URL}");
                                exit();
                            }

                            if ($result->rowCount() != 1) {
                                $URL .= '&return=error0';
                                header("Location: {$URL}");
                            } else {
                                $row = $result->fetch();

                                $pupilsightFinanceBudgetID = $row['pupilsightFinanceBudgetID'];
                                $comment = $_POST['comment'];

                                //Write comment to log
                                try {
                                    $data = array('pupilsightFinanceExpenseID' => $pupilsightFinanceExpenseID, 'pupilsightPersonID' => $_SESSION[$guid]['pupilsightPersonID'], 'comment' => $comment);
                                    $sql = "INSERT INTO pupilsightFinanceExpenseLog SET pupilsightFinanceExpenseID=:pupilsightFinanceExpenseID, pupilsightPersonID=:pupilsightPersonID, timestamp='".date('Y-m-d H:i:s')."', action='Comment', comment=:comment";
                                    $result = $connection2->prepare($sql);
                                    $result->execute($data);
                                } catch (PDOException $e) {
                                    $URL .= '&return=error2';
                                    header("Location: {$URL}");
                                    exit();
                                }

                                //Notify budget holders
                                if ($budgetLevelExpenseApproval == 'Y') {
                                    try {
                                        $dataHolder = array('pupilsightFinanceBudgetID' => $pupilsightFinanceBudgetID);
                                        $sqlHolder = "SELECT * FROM pupilsightFinanceBudgetPerson WHERE access='Full' AND pupilsightFinanceBudgetID=:pupilsightFinanceBudgetID";
                                        $resultHolder = $connection2->prepare($sqlHolder);
                                        $resultHolder->execute($dataHolder);
                                    } catch (PDOException $e) {
                                    }
                                    while ($rowHolder = $resultHolder->fetch()) {
                                        $notificationText = sprintf(__('Someone has commented on the expense request for "%1$s" in budget "%2$s".'), $row['title'], $row['budget']);
                                        setNotification($connection2, $guid, $rowHolder['pupilsightPersonID'], $notificationText, 'Finance', "/index.php?q=/modules/Finance/expenses_manage_view.php&pupilsightFinanceExpenseID=$pupilsightFinanceExpenseID&pupilsightFinanceBudgetCycleID=$pupilsightFinanceBudgetCycleID&status2=&pupilsightFinanceBudgetID2=".$row['pupilsightFinanceBudgetID']);
                                    }
                                }

                                //Notify approvers that it is commented upon
                                foreach ($approvers as $approver) {
                                    $notificationText = sprintf(__('Someone has commented on the expense request for "%1$s" in budget "%2$s".'), $row['title'], $row['budget']);
                                    setNotification($connection2, $guid, $approver['pupilsightPersonID'], $notificationText, 'Finance', "/index.php?q=/modules/Finance/expenses_manage_view.php&pupilsightFinanceExpenseID=$pupilsightFinanceExpenseID&pupilsightFinanceBudgetCycleID=$pupilsightFinanceBudgetCycleID&status2=&pupilsightFinanceBudgetID2=".$row['pupilsightFinanceBudgetID']);
                                }

                                $URL .= '&return=success0';
                                header("Location: {$URL}");
                            }
                        }
                    }
                }
            }
        }
    }
}
