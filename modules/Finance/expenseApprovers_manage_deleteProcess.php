<?php
/*
Pupilsight, Flexible & Open School System
*/

include '../../pupilsight.php';

$pupilsightFinanceExpenseApproverID = $_GET['pupilsightFinanceExpenseApproverID'];
$URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_POST['address']).'/expenseApprovers_manage_delete.php&pupilsightFinanceExpenseApproverID='.$pupilsightFinanceExpenseApproverID;
$URLDelete = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_POST['address']).'/expenseApprovers_manage.php';

if (isActionAccessible($guid, $connection2, '/modules/Finance/expenseApprovers_manage_delete.php') == false) {
    $URL .= '&return=error0';
    header("Location: {$URL}");
} else {
    //Proceed!
    //Check if school year specified
    if ($pupilsightFinanceExpenseApproverID == '') {
        $URL .= '&return=error1';
        header("Location: {$URL}");
    } else {
        try {
            $data = array('pupilsightFinanceExpenseApproverID' => $pupilsightFinanceExpenseApproverID);
            $sql = 'SELECT * FROM pupilsightFinanceExpenseApprover WHERE pupilsightFinanceExpenseApproverID=:pupilsightFinanceExpenseApproverID';
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
            //Write to database
            try {
                $data = array('pupilsightFinanceExpenseApproverID' => $pupilsightFinanceExpenseApproverID);
                $sql = 'DELETE FROM pupilsightFinanceExpenseApprover WHERE pupilsightFinanceExpenseApproverID=:pupilsightFinanceExpenseApproverID';
                $result = $connection2->prepare($sql);
                $result->execute($data);
            } catch (PDOException $e) {
                $URL .= '&return=error2';
                header("Location: {$URL}");
                exit();
            }

            $URLDelete = $URLDelete.'&return=success0';
            header("Location: {$URLDelete}");
        }
    }
}
