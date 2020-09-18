<?php
/*
Pupilsight, Flexible & Open School System
*/

include '../../pupilsight.php';

$pupilsightFinanceExpenseApproverID = $_GET['pupilsightFinanceExpenseApproverID'];
$URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_POST['address']).'/expenseApprovers_manage_edit.php&pupilsightFinanceExpenseApproverID='.$pupilsightFinanceExpenseApproverID;

if (isActionAccessible($guid, $connection2, '/modules/Finance/expenseApprovers_manage_edit.php') == false) {
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
            //Validate Inputs
            $pupilsightPersonID = $_POST['pupilsightPersonID'];
            $expenseApprovalType = getSettingByScope($connection2, 'Finance', 'expenseApprovalType');
            $sequenceNumber = null;
            if ($expenseApprovalType == 'Chain Of All') {
                $sequenceNumber = abs($_POST['sequenceNumber']);
            }

            if ($pupilsightPersonID == '' or ($expenseApprovalType == 'Y' and $sequenceNumber == '')) {
                $URL .= '&return=error1';
                header("Location: {$URL}");
            } else {
                //Check unique inputs for uniquness
                try {
                    if ($expenseApprovalType == 'Chain Of All') {
                        $data = array('pupilsightPersonID' => $pupilsightPersonID, 'sequenceNumber' => $sequenceNumber, 'pupilsightFinanceExpenseApproverID' => $pupilsightFinanceExpenseApproverID);
                        $sql = 'SELECT * FROM pupilsightFinanceExpenseApprover WHERE (pupilsightPersonID=:pupilsightPersonID OR sequenceNumber=:sequenceNumber) AND NOT pupilsightFinanceExpenseApproverID=:pupilsightFinanceExpenseApproverID';
                    } else {
                        $data = array('pupilsightPersonID' => $pupilsightPersonID, 'pupilsightFinanceExpenseApproverID' => $pupilsightFinanceExpenseApproverID);
                        $sql = 'SELECT * FROM pupilsightFinanceExpenseApprover WHERE pupilsightPersonID=:pupilsightPersonID AND NOT pupilsightFinanceExpenseApproverID=:pupilsightFinanceExpenseApproverID';
                    }
                    $result = $connection2->prepare($sql);
                    $result->execute($data);
                } catch (PDOException $e) {
                    $URL .= '&return=error2';
                    header("Location: {$URL}");
                    exit();
                }

                if ($result->rowCount() > 0) {
                    $URL .= '&return=error7';
                    header("Location: {$URL}");
                } else {
                    //Write to database
                    try {
                        $data = array('pupilsightPersonID' => $pupilsightPersonID, 'sequenceNumber' => $sequenceNumber, 'pupilsightPersonIDUpdate' => $_SESSION[$guid]['pupilsightPersonID'], 'timestampUpdate' => date('Y-m-d H:i:s', time()), 'pupilsightFinanceExpenseApproverID' => $pupilsightFinanceExpenseApproverID);
                        $sql = 'UPDATE pupilsightFinanceExpenseApprover SET pupilsightPersonID=:pupilsightPersonID, sequenceNumber=:sequenceNumber, pupilsightPersonIDUpdate=:pupilsightPersonIDUpdate, timestampUpdate=:timestampUpdate WHERE pupilsightFinanceExpenseApproverID=:pupilsightFinanceExpenseApproverID';
                        $result = $connection2->prepare($sql);
                        $result->execute($data);
                    } catch (PDOException $e) {
                        $URL .= '&return=error2';
                        header("Location: {$URL}");
                        exit();
                    }

                    $URL .= '&return=success0';
                    header("Location: {$URL}");
                }
            }
        }
    }
}
