<?php
/*
Pupilsight, Flexible & Open School System
*/

include '../../pupilsight.php';

$URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_POST['address']).'/expenseApprovers_manage_add.php';

if (isActionAccessible($guid, $connection2, '/modules/Finance/expenseApprovers_manage_add.php') == false) {
    $URL .= '&return=error0';
    header("Location: {$URL}");
} else {
    //Proceed!
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
                $data = array('pupilsightPersonID' => $pupilsightPersonID, 'sequenceNumber' => $sequenceNumber);
                $sql = 'SELECT * FROM pupilsightFinanceExpenseApprover WHERE pupilsightPersonID=:pupilsightPersonID OR sequenceNumber=:sequenceNumber';
            } else {
                $data = array('pupilsightPersonID' => $pupilsightPersonID);
                $sql = 'SELECT * FROM pupilsightFinanceExpenseApprover WHERE pupilsightPersonID=:pupilsightPersonID';
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
                $data = array('pupilsightPersonID' => $pupilsightPersonID, 'sequenceNumber' => $sequenceNumber, 'pupilsightPersonIDCreator' => $_SESSION[$guid]['pupilsightPersonID'], 'timestampCreator' => date('Y-m-d H:i:s', time()));
                $sql = 'INSERT INTO pupilsightFinanceExpenseApprover SET pupilsightPersonID=:pupilsightPersonID, sequenceNumber=:sequenceNumber, pupilsightPersonIDCreator=:pupilsightPersonIDCreator, timestampCreator=:timestampCreator';
                $result = $connection2->prepare($sql);
                $result->execute($data);
            } catch (PDOException $e) {
                $URL .= '&return=error2';
                header("Location: {$URL}");
                exit();
            }

            //Last insert ID
            $AI = str_pad($connection2->lastInsertID(), 4, '0', STR_PAD_LEFT);

            $URL .= "&return=success0&editID=$AI";
            header("Location: {$URL}");
        }
    }
}
