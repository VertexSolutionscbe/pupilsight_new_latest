<?php
/*
Pupilsight, Flexible & Open School System
*/

include '../../pupilsight.php';

include './moduleFunctions.php';

$pupilsightFinanceFeeCategoryID = $_POST['pupilsightFinanceFeeCategoryID'];
$URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_POST['address'])."/feeCategories_manage_delete.php&pupilsightFinanceFeeCategoryID=$pupilsightFinanceFeeCategoryID";
$URLDelete = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_POST['address']).'/feeCategories_manage.php';

if (isActionAccessible($guid, $connection2, '/modules/Finance/feeCategories_manage_delete.php') == false) {
    $URL .= '&return=error0';
    header("Location: {$URL}");
} else {
    //Proceed!
    if ($pupilsightFinanceFeeCategoryID == '') {
        $URL .= '&return=error1';
        header("Location: {$URL}");
    } else {
        try {
            $data = array('pupilsightFinanceFeeCategoryID' => $pupilsightFinanceFeeCategoryID);
            $sql = 'SELECT * FROM pupilsightFinanceFeeCategory WHERE pupilsightFinanceFeeCategoryID=:pupilsightFinanceFeeCategoryID';
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
                $data = array('pupilsightFinanceFeeCategoryID' => $pupilsightFinanceFeeCategoryID);
                $sql = 'DELETE FROM pupilsightFinanceFeeCategory WHERE pupilsightFinanceFeeCategoryID=:pupilsightFinanceFeeCategoryID';
                $result = $connection2->prepare($sql);
                $result->execute($data);
            } catch (PDOException $e) {
                $URL .= '&return=error2';
                header("Location: {$URL}");
                exit();
            }

            //Update any fees using this category to "Other"
            try {
                $data = array('pupilsightFinanceFeeCategoryID' => $pupilsightFinanceFeeCategoryID);
                $sql = 'UPDATE pupilsightFinanceFee SET pupilsightFinanceFeeCategoryID=1 WHERE pupilsightFinanceFeeCategoryID=:pupilsightFinanceFeeCategoryID';
                $result = $connection2->prepare($sql);
                $result->execute($data);
            } catch (PDOException $e) {
            }

            try {
                $data = array('pupilsightFinanceFeeCategoryID' => $pupilsightFinanceFeeCategoryID);
                $sql = 'UPDATE pupilsightFinanceInvoiceFee SET pupilsightFinanceFeeCategoryID=1 WHERE pupilsightFinanceFeeCategoryID=:pupilsightFinanceFeeCategoryID';
                $result = $connection2->prepare($sql);
                $result->execute($data);
            } catch (PDOException $e) {
                echo 'Here';
            }

            $URLDelete = $URLDelete.'&return=success0';
            header("Location: {$URLDelete}");
        }
    }
}
