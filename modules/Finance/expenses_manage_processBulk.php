<?php
/*
Pupilsight, Flexible & Open School System
*/

include '../../pupilsight.php';

//Module includes
include './moduleFunctions.php';

$action = $_POST['action'];
$pupilsightFinanceBudgetCycleID = $_GET['pupilsightFinanceBudgetCycleID'];

if ($pupilsightFinanceBudgetCycleID == '' or $action == '') { echo 'Fatal error loading this page!';
} else {
    $URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_POST['address'])."/expenses_manage.php&pupilsightFinanceBudgetCycleID=$pupilsightFinanceBudgetCycleID";

    if (isActionAccessible($guid, $connection2, '/modules/Finance/expenses_manage.php') == false) {
        $URL .= '&return=error0';
        header("Location: {$URL}");
    } else {
        $pupilsightFinanceExpenseIDs = $_POST['pupilsightFinanceExpenseIDs'];
        if (count($pupilsightFinanceExpenseIDs) < 1) {
            $URL .= '&return=error1';
            header("Location: {$URL}");
        } else {
            $partialFail = false;
            //Export
            if ($action == 'export') {
                $_SESSION[$guid]['financeExpenseExportIDs'] = $pupilsightFinanceExpenseIDs;

				include './expenses_manage_processBulkExportContents.php';

                // THIS CODE HAS BEEN COMMENTED OUT, AS THE EXPORT RETURNS WITHOUT IT...NOT SURE WHY!
                    //$URL.="&bulkReturn=success0" ;
                //header("Location: {$URL}");
            } else {
                $URL .= '&return=error1';
                header("Location: {$URL}");
            }
        }
    }
}
