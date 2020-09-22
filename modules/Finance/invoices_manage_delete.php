<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Forms\Prefab\DeleteForm;

//Module includes
require_once __DIR__ . '/moduleFunctions.php';

if (isActionAccessible($guid, $connection2, '/modules/Finance/invoices_manage_delete.php') == false) {
    //Acess denied
    echo "<div class='error'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    //Check if school year specified
    $pupilsightSchoolYearID = $_GET['pupilsightSchoolYearID'];
    $pupilsightFinanceInvoiceID = $_GET['pupilsightFinanceInvoiceID'];
    $status = $_GET['status'];
    $pupilsightFinanceInvoiceeID = $_GET['pupilsightFinanceInvoiceeID'];
    $monthOfIssue = $_GET['monthOfIssue'];
    $pupilsightFinanceBillingScheduleID = $_GET['pupilsightFinanceBillingScheduleID'];
    $pupilsightFinanceFeeCategoryID = $_GET['pupilsightFinanceFeeCategoryID'];

    //Proceed!
    if (isset($_GET['return'])) {
        returnProcess($guid, $_GET['return'], null, null);
    }

    if ($pupilsightFinanceInvoiceID == '' or $pupilsightSchoolYearID == '') {
        echo "<div class='error'>";
        echo __('You have not specified one or more required parameters.');
        echo '</div>';
    } else {
        try {
            $data = array('pupilsightFinanceInvoiceID' => $pupilsightFinanceInvoiceID);
            $sql = "SELECT * FROM pupilsightFinanceInvoice WHERE pupilsightFinanceInvoiceID=:pupilsightFinanceInvoiceID AND status='Pending'";
            $result = $connection2->prepare($sql);
            $result->execute($data);
        } catch (PDOException $e) {
            echo "<div class='error'>".$e->getMessage().'</div>';
        }

        if ($result->rowCount() != 1) {
            echo "<div class='error'>";
            echo __('The selected record does not exist, or you do not have access to it.');
            echo '</div>';
        } else {
            $form = DeleteForm::createForm($_SESSION[$guid]['absoluteURL'].'/modules/'.$_SESSION[$guid]['module']."/invoices_manage_deleteProcess.php?pupilsightFinanceInvoiceID=$pupilsightFinanceInvoiceID&pupilsightSchoolYearID=$pupilsightSchoolYearID&status=$status&pupilsightFinanceInvoiceeID=$pupilsightFinanceInvoiceeID&monthOfIssue=$monthOfIssue&pupilsightFinanceBillingScheduleID=$pupilsightFinanceBillingScheduleID&pupilsightFinanceFeeCategoryID=$pupilsightFinanceFeeCategoryID");
            echo $form->getOutput();
        }
    }
}
?>
