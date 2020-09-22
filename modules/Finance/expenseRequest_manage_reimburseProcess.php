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
$status = $_POST['status'];
$pupilsightFinanceBudgetID2 = $_POST['pupilsightFinanceBudgetID2'];
$status2 = $_POST['status2'];

if ($pupilsightFinanceBudgetCycleID == '' or $pupilsightFinanceBudgetID == '') { echo 'Fatal error loading this page!';
} else {
    $URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_POST['address'])."/expenseRequest_manage_reimburse.php&pupilsightFinanceExpenseID=$pupilsightFinanceExpenseID&pupilsightFinanceBudgetCycleID=$pupilsightFinanceBudgetCycleID&pupilsightFinanceBudgetID2=$pupilsightFinanceBudgetID2&status2=$status2";
    $URLSuccess = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_POST['address'])."/expenseRequest_manage.php&pupilsightFinanceExpenseID=$pupilsightFinanceExpenseID&pupilsightFinanceBudgetCycleID=$pupilsightFinanceBudgetCycleID&pupilsightFinanceBudgetID2=$pupilsightFinanceBudgetID2&status2=$status2";

    if (isActionAccessible($guid, $connection2, '/modules/Finance/expenseRequest_manage_reimburse.php') == false) {
        $URL .= '&return=error0';
        header("Location: {$URL}");
        exit();
    } else {
        if ($pupilsightFinanceExpenseID == '' or $status == '' or $status != 'Paid' or empty($_FILES['file']['tmp_name'])) {
            $URL .= '&return=error1';
            header("Location: {$URL}");
            exit();
        } else {
            //Get and check settings
            $expenseApprovalType = getSettingByScope($connection2, 'Finance', 'expenseApprovalType');
            $budgetLevelExpenseApproval = getSettingByScope($connection2, 'Finance', 'budgetLevelExpenseApproval');
            $expenseRequestTemplate = getSettingByScope($connection2, 'Finance', 'expenseRequestTemplate');
            if ($expenseApprovalType == '' or $budgetLevelExpenseApproval == '') {
                $URL .= '&return=error0';
                header("Location: {$URL}");
                exit();
            } else {
                //Check if there are approvers
                try {
                    $data = array();
                    $sql = "SELECT * FROM pupilsightFinanceExpenseApprover JOIN pupilsightPerson ON (pupilsightFinanceExpenseApprover.pupilsightPersonID=pupilsightPerson.pupilsightPersonID) WHERE status='Full'";
                    $result = $connection2->prepare($sql);
                    $result->execute($data);
                } catch (PDOException $e) {
                    $URL .= '&return=error2';
                    header("Location: {$URL}");
                    exit();
                }

                if ($result->rowCount() < 1) {
                    $URL .= '&return=error0';
                    header("Location: {$URL}");
                    exit();
                } else {
                    //Ready to go! Just check record exists and we have access, and load it ready to use...
                    try {
                        //Set Up filter wheres
                        $data = array('pupilsightFinanceBudgetCycleID' => $pupilsightFinanceBudgetCycleID, 'pupilsightFinanceExpenseID' => $pupilsightFinanceExpenseID);
                        $sql = "SELECT pupilsightFinanceExpense.*, pupilsightFinanceBudget.name AS budget, surname, preferredName, 'Full' AS access
							FROM pupilsightFinanceExpense
							JOIN pupilsightFinanceBudget ON (pupilsightFinanceExpense.pupilsightFinanceBudgetID=pupilsightFinanceBudget.pupilsightFinanceBudgetID)
							JOIN pupilsightPerson ON (pupilsightFinanceExpense.pupilsightPersonIDCreator=pupilsightPerson.pupilsightPersonID)
							WHERE pupilsightFinanceBudgetCycleID=:pupilsightFinanceBudgetCycleID AND pupilsightFinanceExpenseID=:pupilsightFinanceExpenseID AND pupilsightFinanceExpense.status='Approved'";
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
                        exit();
                    } else {
                        $row = $result->fetch();

                        //Get relevant
                        $paymentDate = dateConvert($guid, $_POST['paymentDate']);
                        $paymentAmount = $_POST['paymentAmount'];
                        $pupilsightPersonIDPayment = $_POST['pupilsightPersonIDPayment'];
                        $paymentMethod = $_POST['paymentMethod'];

                        $fileUploader = new Pupilsight\FileUploader($pdo, $pupilsight->session);

                        $file = (isset($_FILES['file']))? $_FILES['file'] : null;

                        // Upload the file, return the /uploads relative path
                        $attachment = $fileUploader->uploadFromPost($file, $row['title']);

                        if (empty($attachment)) {
                            $URL .= '&return=error5';
                            header("Location: {$URL}");
                            exit();
                        }

                        //Write back to pupilsightFinanceExpense
                        try {
                            $data = array('pupilsightFinanceExpenseID' => $pupilsightFinanceExpenseID, 'status' => 'Paid', 'paymentDate' => $paymentDate, 'paymentAmount' => $paymentAmount, 'pupilsightPersonIDPayment' => $pupilsightPersonIDPayment, 'paymentMethod' => $paymentMethod, 'paymentReimbursementReceipt' => $attachment, 'paymentReimbursementStatus' => 'Requested');
                            $sql = 'UPDATE pupilsightFinanceExpense SET status=:status, paymentDate=:paymentDate, paymentAmount=:paymentAmount, pupilsightPersonIDPayment=:pupilsightPersonIDPayment, paymentMethod=:paymentMethod, paymentReimbursementReceipt=:paymentReimbursementReceipt, paymentReimbursementStatus=:paymentReimbursementStatus WHERE pupilsightFinanceExpenseID=:pupilsightFinanceExpenseID';
                            $result = $connection2->prepare($sql);
                            $result->execute($data);
                        } catch (PDOException $e) {
                            $URL .= '&return=error2';
                            header("Location: {$URL}");
                            exit();
                        }

                        //Notify reimbursement officer that action is required
                        $reimbursementOfficer = getSettingByScope($connection2, 'Finance', 'reimbursementOfficer');
                        if ($reimbursementOfficer != false and $reimbursementOfficer != '') {
                            $notificationText = sprintf(__('Someone has requested reimbursement for "%1$s" in budget "%2$s".'), $row['title'], $row['budget']);
                            setNotification($connection2, $guid, $reimbursementOfficer, $notificationText, 'Finance', "/index.php?q=/modules/Finance/expenses_manage_edit.php&pupilsightFinanceExpenseID=$pupilsightFinanceExpenseID&pupilsightFinanceBudgetCycleID=$pupilsightFinanceBudgetCycleID&status=&pupilsightFinanceBudgetID2=".$row['pupilsightFinanceBudgetID']);
                        }

                        //Write paid change to log
                        try {
                            $data = array('pupilsightFinanceExpenseID' => $pupilsightFinanceExpenseID, 'pupilsightPersonID' => $_SESSION[$guid]['pupilsightPersonID'], 'action' => 'Payment');
                            $sql = "INSERT INTO pupilsightFinanceExpenseLog SET pupilsightFinanceExpenseID=:pupilsightFinanceExpenseID, pupilsightPersonID=:pupilsightPersonID, timestamp='".date('Y-m-d H:i:s')."', action=:action";
                            $result = $connection2->prepare($sql);
                            $result->execute($data);
                        } catch (PDOException $e) {
                            $URL .= '&return=error2';
                            header("Location: {$URL}");
                            exit();
                        }

                        //Write reimbursement request change to log
                        try {
                            $data = array('pupilsightFinanceExpenseID' => $pupilsightFinanceExpenseID, 'pupilsightPersonID' => $_SESSION[$guid]['pupilsightPersonID'], 'action' => 'Reimbursement Request');
                            $sql = "INSERT INTO pupilsightFinanceExpenseLog SET pupilsightFinanceExpenseID=:pupilsightFinanceExpenseID, pupilsightPersonID=:pupilsightPersonID, timestamp='".date('Y-m-d H:i:s')."', action=:action";
                            $result = $connection2->prepare($sql);
                            $result->execute($data);
                        } catch (PDOException $e) {
                            $URL .= '&return=error2';
                            header("Location: {$URL}");
                            exit();
                        }

                        $URLSuccess .= '&return=success0';
                        header("Location: {$URLSuccess}");
                    }
                }
            }
        }
    }
}
