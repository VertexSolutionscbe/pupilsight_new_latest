<?php
/*
Pupilsight, Flexible & Open School System
*/

include '../../pupilsight.php';

include './moduleFunctions.php';

$pupilsightFinanceBudgetID = $_POST['pupilsightFinanceBudgetID'];
$URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_POST['address'])."/budgets_manage_delete.php&pupilsightFinanceBudgetID=$pupilsightFinanceBudgetID";
$URLDelete = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_POST['address']).'/budgets_manage.php';

if (isActionAccessible($guid, $connection2, '/modules/Finance/budgets_manage_delete.php') == false) {
    $URL .= '&return=error0';
    header("Location: {$URL}");
} else {
    //Proceed!
    if ($pupilsightFinanceBudgetID == '') {
        $URL .= '&return=error1';
        header("Location: {$URL}");
    } else {
        try {
            $data = array('pupilsightFinanceBudgetID' => $pupilsightFinanceBudgetID);
            $sql = 'SELECT * FROM pupilsightFinanceBudget WHERE pupilsightFinanceBudgetID=:pupilsightFinanceBudgetID';
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
                $data = array('pupilsightFinanceBudgetID' => $pupilsightFinanceBudgetID);
                $sql = 'DELETE FROM pupilsightFinanceBudget WHERE pupilsightFinanceBudgetID=:pupilsightFinanceBudgetID';
                $result = $connection2->prepare($sql);
                $result->execute($data);
            } catch (PDOException $e) {
                $URL .= '&return=error2';
                header("Location: {$URL}");
                exit();
            }

            try {
                $data = array('pupilsightFinanceBudgetID' => $pupilsightFinanceBudgetID);
                $sql = 'DELETE FROM pupilsightFinanceBudgetPerson WHERE pupilsightFinanceBudgetID=:pupilsightFinanceBudgetID';
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
