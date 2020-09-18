<?php
/*
Pupilsight, Flexible & Open School System
*/

include '../../pupilsight.php';

//Module includes
include './moduleFunctions.php';

$pupilsightFinanceBudgetCycleID = $_POST['pupilsightFinanceBudgetCycleID'];
$pupilsightFinanceBudgetID = $_POST['pupilsightFinanceBudgetID'];
$pupilsightFinanceExpenseID = $_POST['pupilsightFinanceExpenseID'];
$status2 = $_POST['status2'];
$pupilsightFinanceBudgetID2 = $_POST['pupilsightFinanceBudgetID2'];

if ($pupilsightFinanceBudgetCycleID == '' or $pupilsightFinanceBudgetID == '') { echo 'Fatal error loading this page!';
} else {
    $URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_POST['address'])."/expenses_manage_approve.php&pupilsightFinanceBudgetCycleID=$pupilsightFinanceBudgetCycleID&pupilsightFinanceBudgetID2=$pupilsightFinanceBudgetID2&status2=$status2";
    $URLApprove = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_POST['address'])."/expenses_manage.php&pupilsightFinanceBudgetCycleID=$pupilsightFinanceBudgetCycleID&pupilsightFinanceBudgetID2=$pupilsightFinanceBudgetID2&status2=$status2";

    if (isActionAccessible($guid, $connection2, '/modules/Finance/expenses_manage_approve.php') == false) {
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
                if ($highestAction == 'Manage Expenses_all') { //Access to everything
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
										WHERE pupilsightFinanceBudgetCycleID=:pupilsightFinanceBudgetCycleID AND pupilsightFinanceExpenseID=:pupilsightFinanceExpenseID AND pupilsightFinanceBudgetPerson.pupilsightPersonID=:pupilsightPersonID AND access='Full'";
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

                                $approval = $_POST['approval'];
                                if ($approval == 'Approval - Partial') {
                                    if ($row['statusApprovalBudgetCleared'] == 'N') {
                                        $approval = 'Approval - Partial - Budget';
                                    } else {
                                        //Check if school approver, if not, abort
                                        try {
                                            $data = array('pupilsightPersonID' => $_SESSION[$guid]['pupilsightPersonID']);
                                            $sql = "SELECT * FROM pupilsightFinanceExpenseApprover JOIN pupilsightPerson ON (pupilsightFinanceExpenseApprover.pupilsightPersonID=pupilsightPerson.pupilsightPersonID) WHERE status='Full' AND pupilsightFinanceExpenseApprover.pupilsightPersonID=:pupilsightPersonID";
                                            $result = $connection2->prepare($sql);
                                            $result->execute($data);
                                        } catch (PDOException $e) {
                                        }

                                        if ($result->rowCount() == 1) {
                                            $approval = 'Approval - Partial - School';
                                        } else {
                                            $URL .= '&return=error0';
                                            header("Location: {$URL}");
                                            exit();
                                        }
                                    }
                                }
                                $comment = $_POST['comment'];

                                if ($approval == '') {
                                    $URL .= '&return=error7';
                                    header("Location: {$URL}");
                                } else {
                                    //Write budget change
                                    try {
                                        $dataBudgetChange = array('pupilsightFinanceBudgetID' => $pupilsightFinanceBudgetID, 'pupilsightFinanceExpenseID' => $pupilsightFinanceExpenseID);
                                        $sqlBudgetChange = 'UPDATE pupilsightFinanceExpense SET pupilsightFinanceBudgetID=:pupilsightFinanceBudgetID WHERE pupilsightFinanceExpenseID=:pupilsightFinanceExpenseID';
                                        $resultBudgetChange = $connection2->prepare($sqlBudgetChange);
                                        $resultBudgetChange->execute($dataBudgetChange);
                                    } catch (PDOException $e) {
                                        $URL .= '&return=error2';
                                        header("Location: {$URL}");
                                        exit();
                                    }

                                    //Attempt to archive notification
                                    archiveNotification($connection2, $guid, $_SESSION[$guid]['pupilsightPersonID'], "/index.php?q=/modules/Finance/expenses_manage_approve.php&pupilsightFinanceExpenseID=$pupilsightFinanceExpenseID");

                                    if ($approval == 'Rejection') { //REJECT!
                                        //Write back to pupilsightFinanceExpense
                                        try {
                                            $data = array('pupilsightFinanceExpenseID' => $pupilsightFinanceExpenseID);
                                            $sql = "UPDATE pupilsightFinanceExpense SET status='Rejected' WHERE pupilsightFinanceExpenseID=:pupilsightFinanceExpenseID";
                                            $result = $connection2->prepare($sql);
                                            $result->execute($data);
                                        } catch (PDOException $e) {
                                            $URL .= '&return=error2';
                                            header("Location: {$URL}");
                                            exit();
                                        }

                                        //Write rejection to log
                                        try {
                                            $data = array('pupilsightFinanceExpenseID' => $pupilsightFinanceExpenseID, 'pupilsightPersonID' => $_SESSION[$guid]['pupilsightPersonID'], 'comment' => $comment);
                                            $sql = "INSERT INTO pupilsightFinanceExpenseLog SET pupilsightFinanceExpenseID=:pupilsightFinanceExpenseID, pupilsightPersonID=:pupilsightPersonID, timestamp='".date('Y-m-d H:i:s')."', action='Rejection', comment=:comment";
                                            $result = $connection2->prepare($sql);
                                            $result->execute($data);
                                        } catch (PDOException $e) {
                                            $URL .= '&return=error2';
                                            header("Location: {$URL}");
                                            exit();
                                        }

                                        //Notify original creator that it is rejected
                                        $notificationText = sprintf(__('Your expense request for "%1$s" in budget "%2$s" has been rejected.'), $row['title'], $row['budget']);
                                        setNotification($connection2, $guid, $row['pupilsightPersonIDCreator'], $notificationText, 'Finance', "/index.php?q=/modules/Finance/expenses_manage_view.php&pupilsightFinanceExpenseID=$pupilsightFinanceExpenseID&pupilsightFinanceBudgetCycleID=$pupilsightFinanceBudgetCycleID&status2=&pupilsightFinanceBudgetID2=".$row['pupilsightFinanceBudgetID']);

                                        $URLApprove .= '&return=success0';
                                        header("Location: {$URLApprove}");
                                    } elseif ($approval == 'Comment') { //COMMENT!
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

                                        //Notify original creator that it is commented upon
                                        $notificationText = sprintf(__('Someone has commented on your expense request for "%1$s" in budget "%2$s".'), $row['title'], $row['budget']);
                                        setNotification($connection2, $guid, $row['pupilsightPersonIDCreator'], $notificationText, 'Finance', "/index.php?q=/modules/Finance/expenses_manage_view.php&pupilsightFinanceExpenseID=$pupilsightFinanceExpenseID&pupilsightFinanceBudgetCycleID=$pupilsightFinanceBudgetCycleID&status2=&pupilsightFinanceBudgetID2=".$row['pupilsightFinanceBudgetID']);

                                        $URLApprove .= '&return=success0';
                                        header("Location: {$URLApprove}");
                                    } else { //APPROVE!
                                        if (approvalRequired($guid, $_SESSION[$guid]['pupilsightPersonID'], $row['pupilsightFinanceExpenseID'], $pupilsightFinanceBudgetCycleID, $connection2, true) == false) {
                                            $URL .= '&return=error0';
                                            header("Location: {$URL}");
                                        } else {
                                            //Add log entry
                                            try {
                                                $data = array('pupilsightFinanceExpenseID' => $pupilsightFinanceExpenseID, 'pupilsightPersonID' => $_SESSION[$guid]['pupilsightPersonID'], 'action' => $approval, 'comment' => $comment);
                                                $sql = "INSERT INTO pupilsightFinanceExpenseLog SET pupilsightFinanceExpenseID=:pupilsightFinanceExpenseID, pupilsightPersonID=:pupilsightPersonID, timestamp='".date('Y-m-d H:i:s')."', action=:action, comment=:comment";
                                                $result = $connection2->prepare($sql);
                                                $result->execute($data);
                                            } catch (PDOException $e) {
                                                $URL .= '&return=error2';
                                                header("Location: {$URL}");
                                                exit();
                                            }

                                            if ($approval = 'Approval - Partial - Budget') { //If budget-level approval, write that budget passed to expense record
                                                try {
                                                    $data = array('pupilsightFinanceExpenseID' => $pupilsightFinanceExpenseID);
                                                    $sql = "UPDATE pupilsightFinanceExpense SET statusApprovalBudgetCleared='Y' WHERE pupilsightFinanceExpenseID=:pupilsightFinanceExpenseID";
                                                    $result = $connection2->prepare($sql);
                                                    $result->execute($data);
                                                } catch (PDOException $e) {
                                                    $URL .= '&return=error2';
                                                    header("Location: {$URL}");
                                                    exit();
                                                }
                                            }

                                            //Check for completion status (returns FALSE, none, budget, school) based on log
                                            $partialFail = false;
                                            $completion = checkLogForApprovalComplete($guid, $pupilsightFinanceExpenseID, $connection2);
                                            if ($completion == false) { //If false
                                                $URL .= '&return=error2';
                                                header("Location: {$URL}");
                                                exit();
                                            } elseif ($completion == 'none') { //If none
                                                $URL .= '&return=error2';
                                                header("Location: {$URL}");
                                                exit();
                                            } elseif ($completion == 'budget') { //If budget completion met
                                                //Issue Notifications
                                                if (setExpenseNotification($guid, $pupilsightFinanceExpenseID, $pupilsightFinanceBudgetCycleID, $connection2) == false) {
                                                    $partialFail = true;
                                                }

                                                //Write back to pupilsightFinanceExpense
                                                try {
                                                    $data = array('pupilsightFinanceExpenseID' => $pupilsightFinanceExpenseID);
                                                    $sql = "UPDATE pupilsightFinanceExpense SET statusApprovalBudgetCleared='Y' WHERE pupilsightFinanceExpenseID=:pupilsightFinanceExpenseID";
                                                    $result = $connection2->prepare($sql);
                                                    $result->execute($data);
                                                } catch (PDOException $e) {
                                                    $URL .= '&return=error2';
                                                    header("Location: {$URL}");
                                                    exit();
                                                }

                                                if ($partialFail == true) {
                                                    $URLApprove .= '&return=success1';
                                                    header("Location: {$URLApprove}");
                                                } else {
                                                    $URLApprove .= '&return=success0';
                                                    header("Location: {$URLApprove}");
                                                }
                                            } elseif ($completion == 'school') { //If school completion met
                                                //Write completion to log
                                                try {
                                                    $data = array('pupilsightFinanceExpenseID' => $pupilsightFinanceExpenseID, 'pupilsightPersonID' => $_SESSION[$guid]['pupilsightPersonID']);
                                                    $sql = "INSERT INTO pupilsightFinanceExpenseLog SET pupilsightFinanceExpenseID=:pupilsightFinanceExpenseID, pupilsightPersonID=:pupilsightPersonID, timestamp='".date('Y-m-d H:i:s')."', action='Approval - Final'";
                                                    $result = $connection2->prepare($sql);
                                                    $result->execute($data);
                                                } catch (PDOException $e) {
                                                    $URL .= '&return=error2';
                                                    header("Location: {$URL}");
                                                    exit();
                                                }

                                                //Write back to pupilsightFinanceExpense
                                                try {
                                                    $data = array('pupilsightFinanceExpenseID' => $pupilsightFinanceExpenseID);
                                                    $sql = "UPDATE pupilsightFinanceExpense SET status='Approved' WHERE pupilsightFinanceExpenseID=:pupilsightFinanceExpenseID";
                                                    $result = $connection2->prepare($sql);
                                                    $result->execute($data);
                                                } catch (PDOException $e) {
                                                    $URL .= '&return=error2';
                                                    header("Location: {$URL}");
                                                    exit();
                                                }

                                                $notificationExtra = '';
                                                //Notify purchasing officer, if a school purchase, and officer set
                                                $purchasingOfficer = getSettingByScope($connection2, 'Finance', 'purchasingOfficer');
                                                if ($purchasingOfficer != false and $purchasingOfficer != '' and $row['purchaseBy'] == 'School') {
                                                    $notificationText = sprintf(__('A newly approved expense (%1$s) needs to be purchased from budget "%2$s".'), $row['title'], $row['budget']);
                                                    setNotification($connection2, $guid, $purchasingOfficer, $notificationText, 'Finance', "/index.php?q=/modules/Finance/expenses_manage_view.php&pupilsightFinanceExpenseID=$pupilsightFinanceExpenseID&pupilsightFinanceBudgetCycleID=$pupilsightFinanceBudgetCycleID&status2=&pupilsightFinanceBudgetID2=".$row['pupilsightFinanceBudgetID']);
                                                    $notificationExtra = '. '.__('The Purchasing Officer has been alerted, and will purchase the item on your behalf.');
                                                }

                                                //Notify original creator that it is approved
                                                $notificationText = sprintf(__('Your expense request for "%1$s" in budget "%2$s" has been fully approved.').$notificationExtra, $row['title'], $row['budget']);
                                                setNotification($connection2, $guid, $row['pupilsightPersonIDCreator'], $notificationText, 'Finance', "/index.php?q=/modules/Finance/expenses_manage_view.php&pupilsightFinanceExpenseID=$pupilsightFinanceExpenseID&pupilsightFinanceBudgetCycleID=$pupilsightFinanceBudgetCycleID&status2=&pupilsightFinanceBudgetID2=".$row['pupilsightFinanceBudgetID']);

                                                $URLApprove .= '&return=success0';
                                                header("Location: {$URLApprove}");
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
    }
}
