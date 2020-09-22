<?php
/*
Pupilsight, Flexible & Open School System
*/

include '../../pupilsight.php';

$pupilsightFinanceBudgetID = $_GET['pupilsightFinanceBudgetID'];
$pupilsightFinanceBudgetPersonID = $_GET['pupilsightFinanceBudgetPersonID'];
$URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_GET['address'])."/budgets_manage_edit.php&pupilsightFinanceBudgetID=$pupilsightFinanceBudgetID";

if (isActionAccessible($guid, $connection2, '/modules/School Admin/department_manage_edit.php') == false) {
    $URL .= '&return=error0';
    header("Location: {$URL}");
} else {
    //Proceed!

    //Check if school year specified
    if ($pupilsightFinanceBudgetID == '' or $pupilsightFinanceBudgetPersonID == '') {
        $URL .= '&return=error1';
        header("Location: {$URL}");
    } else {
        try {
            $data = array('pupilsightFinanceBudgetPersonID' => $pupilsightFinanceBudgetPersonID, 'pupilsightFinanceBudgetID' => $pupilsightFinanceBudgetID);
            $sql = 'SELECT * FROM pupilsightFinanceBudgetPerson WHERE pupilsightFinanceBudgetPersonID=:pupilsightFinanceBudgetPersonID AND pupilsightFinanceBudgetID=:pupilsightFinanceBudgetID';
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
                $data = array('pupilsightFinanceBudgetPersonID' => $pupilsightFinanceBudgetPersonID);
                $sql = 'DELETE FROM pupilsightFinanceBudgetPerson WHERE pupilsightFinanceBudgetPersonID=:pupilsightFinanceBudgetPersonID';
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
