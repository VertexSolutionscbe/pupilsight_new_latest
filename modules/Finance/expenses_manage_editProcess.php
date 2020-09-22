<?php
/*
Pupilsight, Flexible & Open School System
*/

include '../../pupilsight.php';

//Module includes
include './moduleFunctions.php';

$pupilsightFinanceBudgetCycleID = $_POST['pupilsightFinanceBudgetCycleID'];
$pupilsightFinanceBudgetID2 = $_POST['pupilsightFinanceBudgetID2'];
$pupilsightFinanceExpenseID = $_POST['pupilsightFinanceExpenseID'];
$status2 = $_POST['status2'];
$countAgainstBudget = $_POST['countAgainstBudget'];
$status = $_POST['status'];

if ($pupilsightFinanceBudgetCycleID == '') { echo 'Fatal error loading this page!';
} else {
    $URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_POST['address'])."/expenses_manage_edit.php&pupilsightFinanceExpenseID=$pupilsightFinanceExpenseID&pupilsightFinanceBudgetCycleID=$pupilsightFinanceBudgetCycleID&pupilsightFinanceBudgetID2=$pupilsightFinanceBudgetID2&status2=$status2";

    if (isActionAccessible($guid, $connection2, '/modules/Finance/expenses_manage_add.php', 'Manage Expenses_all') == false) {
        $URL .= '&return=error0';
        header("Location: {$URL}");
    } else {
        $highestAction = getHighestGroupedAction($guid, $_POST['address'], $connection2);
        if ($highestAction == false) {
            $URL .= '&return=error0';
            header("Location: {$URL}");
        } else {
            if ($pupilsightFinanceExpenseID == '' or $status == '' or $status == 'Please select...' or $countAgainstBudget == '') {
                $URL .= '&return=error1';
                header("Location: {$URL}");
            } else {
                //Get and check settings
                $expenseApprovalType = getSettingByScope($connection2, 'Finance', 'expenseApprovalType');
                $budgetLevelExpenseApproval = getSettingByScope($connection2, 'Finance', 'budgetLevelExpenseApproval');
                $expenseRequestTemplate = getSettingByScope($connection2, 'Finance', 'expenseRequestTemplate');
                if ($expenseApprovalType == '' or $budgetLevelExpenseApproval == '') {
                    $URL .= '&return=error1';
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
                        //Ready to go! Just check record exists and we have access, and load it ready to use...
                        try {
                            //Set Up filter wheres
                            $data = array('pupilsightFinanceBudgetCycleID' => $pupilsightFinanceBudgetCycleID, 'pupilsightFinanceExpenseID' => $pupilsightFinanceExpenseID);
                            $sql = "SELECT pupilsightFinanceExpense.*, pupilsightFinanceBudget.name AS budget, surname, preferredName, 'Full' AS access
								FROM pupilsightFinanceExpense
								JOIN pupilsightFinanceBudget ON (pupilsightFinanceExpense.pupilsightFinanceBudgetID=pupilsightFinanceBudget.pupilsightFinanceBudgetID)
								JOIN pupilsightPerson ON (pupilsightFinanceExpense.pupilsightPersonIDCreator=pupilsightPerson.pupilsightPersonID)
								WHERE pupilsightFinanceBudgetCycleID=:pupilsightFinanceBudgetCycleID AND pupilsightFinanceExpenseID=:pupilsightFinanceExpenseID";
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
                            $statusOld = $row['status'];

                            //Check if params are specified
                            if ($status == 'Paid' and ($row['status'] == 'Approved' or $row['status'] == 'Ordered')) {
                                $paymentDate = dateConvert($guid, $_POST['paymentDate']);
                                $paymentAmount = $_POST['paymentAmount'];
                                $pupilsightPersonIDPayment = $_POST['pupilsightPersonIDPayment'];
                                $paymentMethod = $_POST['paymentMethod'];
                                $paymentID = $_POST['paymentID'];
                            } else {
                                $paymentDate = $row['paymentDate'];
                                $paymentAmount = $row['paymentAmount'];
                                $pupilsightPersonIDPayment = $row['pupilsightPersonIDPayment'];
                                $paymentMethod = $row['paymentMethod'];
                                $paymentID = $row['paymentID'];
                            }

                            //Do Reimbursement work
                            $paymentReimbursementStatus = null;
                            $reimbursementComment = '';
                            if (isset($_POST['paymentReimbursementStatus'])) {
                                $paymentReimbursementStatus = $_POST['paymentReimbursementStatus'];
                                if ($paymentReimbursementStatus != 'Requested' and $paymentReimbursementStatus != 'Complete') {
                                    $paymentReimbursementStatus = null;
                                }
                                if ($row['status'] == 'Paid' and $row['purchaseBy'] == 'Self' and $row['paymentReimbursementStatus'] == 'Requested' and $paymentReimbursementStatus == 'Complete') {
                                    $paymentID = $_POST['paymentID'];
                                    $reimbursementComment = $_POST['reimbursementComment'];
                                    $notificationText = sprintf(__('Your reimbursement expense request for "%1$s" in budget "%2$s" has been completed.'), $row['title'], $row['budget']);
                                    setNotification($connection2, $guid, $row['pupilsightPersonIDCreator'], $notificationText, 'Finance', "/index.php?q=/modules/Finance/expenseRequest_manage_view.php&pupilsightFinanceExpenseID=$pupilsightFinanceExpenseID&pupilsightFinanceBudgetCycleID=$pupilsightFinanceBudgetCycleID&status=&pupilsightFinanceBudgetID=".$row['pupilsightFinanceBudgetID']);
                                    //Write change to log
                                    try {
                                        $data = array('pupilsightFinanceExpenseID' => $pupilsightFinanceExpenseID, 'pupilsightPersonID' => $_SESSION[$guid]['pupilsightPersonID'], 'action' => 'Reimbursement Completion', 'comment' => $reimbursementComment);
                                        $sql = "INSERT INTO pupilsightFinanceExpenseLog SET pupilsightFinanceExpenseID=:pupilsightFinanceExpenseID, pupilsightPersonID=:pupilsightPersonID, timestamp='".date('Y-m-d H:i:s')."', action=:action, comment=:comment";
                                        $result = $connection2->prepare($sql);
                                        $result->execute($data);
                                    } catch (PDOException $e) {
                                        $URL .= '&return=error2';
                                        header("Location: {$URL}");
                                        exit();
                                    }
                                }
                            }

                            //Write back to pupilsightFinanceExpense
                            try {
                                $data = array('pupilsightFinanceExpenseID' => $pupilsightFinanceExpenseID, 'status' => $status, 'countAgainstBudget' => $countAgainstBudget, 'paymentDate' => $paymentDate, 'paymentAmount' => $paymentAmount, 'pupilsightPersonIDPayment' => $pupilsightPersonIDPayment, 'paymentMethod' => $paymentMethod, 'paymentID' => $paymentID, 'paymentReimbursementStatus' => $paymentReimbursementStatus);
                                $sql = 'UPDATE pupilsightFinanceExpense SET status=:status, countAgainstBudget=:countAgainstBudget, paymentDate=:paymentDate, paymentAmount=:paymentAmount, pupilsightPersonIDPayment=:pupilsightPersonIDPayment, paymentMethod=:paymentMethod, paymentID=:paymentID, paymentReimbursementStatus=:paymentReimbursementStatus WHERE pupilsightFinanceExpenseID=:pupilsightFinanceExpenseID';
                                $result = $connection2->prepare($sql);
                                $result->execute($data);
                            } catch (PDOException $e) {
                                $URL .= '&return=error2';
                                header("Location: {$URL}");
                                exit();
                            }

                            if ($statusOld != $status) {
                                $action = '';
                                if ($status == 'Requested') {
                                    $action = 'Request';
                                } elseif ($status == 'Approved') {
                                    $action = 'Approval - Exempt';
                                    //Notify original creator that it is approved
                                    $notificationText = sprintf(__('Your expense request for "%1$s" in budget "%2$s" has been fully approved.'), $row['title'], $row['budget']);
                                    setNotification($connection2, $guid, $row['pupilsightPersonIDCreator'], $notificationText, 'Finance', "/index.php?q=/modules/Finance/expenses_manage_view.php&pupilsightFinanceExpenseID=$pupilsightFinanceExpenseID&pupilsightFinanceBudgetCycleID=$pupilsightFinanceBudgetCycleID&status=&pupilsightFinanceBudgetID=".$row['pupilsightFinanceBudgetID']);
                                } elseif ($status == 'Rejected') {
                                    $action = 'Rejection';
                                    //Notify original creator that it is rejected
                                    $notificationText = sprintf(__('Your expense request for "%1$s" in budget "%2$s" has been rejected.'), $row['title'], $row['budget']);
                                    setNotification($connection2, $guid, $row['pupilsightPersonIDCreator'], $notificationText, 'Finance', "/index.php?q=/modules/Finance/expenses_manage_view.php&pupilsightFinanceExpenseID=$pupilsightFinanceExpenseID&pupilsightFinanceBudgetCycleID=$pupilsightFinanceBudgetCycleID&status=&pupilsightFinanceBudgetID=".$row['pupilsightFinanceBudgetID']);
                                } elseif ($status == 'Ordered') {
                                    $action = 'Order';
                                } elseif ($status == 'Paid') {
                                    $action = 'Payment';
                                } elseif ($status == 'Cancelled') {
                                    $action = 'Cancellation';
                                }

                                //Write change to log
                                try {
                                    $data = array('pupilsightFinanceExpenseID' => $pupilsightFinanceExpenseID, 'pupilsightPersonID' => $_SESSION[$guid]['pupilsightPersonID'], 'action' => $action);
                                    $sql = "INSERT INTO pupilsightFinanceExpenseLog SET pupilsightFinanceExpenseID=:pupilsightFinanceExpenseID, pupilsightPersonID=:pupilsightPersonID, timestamp='".date('Y-m-d H:i:s')."', action=:action";
                                    $result = $connection2->prepare($sql);
                                    $result->execute($data);
                                } catch (PDOException $e) {
                                    $URL .= '&return=error2';
                                    header("Location: {$URL}");
                                    exit();
                                }
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
