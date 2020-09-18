<?php
/*
Pupilsight, Flexible & Open School System
*/

include '../../pupilsight.php';

include './moduleFunctions.php';

$pupilsightSchoolYearID = $_GET['pupilsightSchoolYearID'];
$pupilsightFinanceInvoiceID = $_POST['pupilsightFinanceInvoiceID'];
$status = null;
if (isset($_GET['status'])) {
    $status = $_GET['status'];
}

$pupilsightFinanceInvoiceeID = null;
if (isset($_GET['pupilsightFinanceInvoiceeID'])) {
    $pupilsightFinanceInvoiceeID = $_GET['pupilsightFinanceInvoiceeID'];
}
$monthOfIssue = null;
if (isset($_GET['monthOfIssue'])) {
    $monthOfIssue = $_GET['monthOfIssue'];
}

$pupilsightFinanceBillingScheduleID = null;
if (isset($_GET['pupilsightFinanceBillingScheduleID'])) {
    $pupilsightFinanceBillingScheduleID = $_GET['pupilsightFinanceBillingScheduleID'];
}

$pupilsightFinanceFeeCategoryID = null;
if (isset($_GET['pupilsightFinanceFeeCategoryID'])) {
    $pupilsightFinanceFeeCategoryID = $_GET['pupilsightFinanceFeeCategoryID'];
}

if ($pupilsightFinanceInvoiceID == '' or $pupilsightSchoolYearID == '') { echo 'Fatal error loading this page!';
} else {
    $URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_POST['address'])."/invoices_manage_delete.php&pupilsightFinanceInvoiceID=$pupilsightFinanceInvoiceID&pupilsightSchoolYearID=$pupilsightSchoolYearID&status=$status&pupilsightFinanceInvoiceeID=$pupilsightFinanceInvoiceeID&monthOfIssue=$monthOfIssue&pupilsightFinanceBillingScheduleID=$pupilsightFinanceBillingScheduleID&pupilsightFinanceFeeCategoryID=$pupilsightFinanceFeeCategoryID";
    $URLDelete = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_POST['address'])."/invoices_manage.php&pupilsightSchoolYearID=$pupilsightSchoolYearID&status=$status&pupilsightFinanceInvoiceeID=$pupilsightFinanceInvoiceeID&monthOfIssue=$monthOfIssue&pupilsightFinanceBillingScheduleID=$pupilsightFinanceBillingScheduleID&pupilsightFinanceFeeCategoryID=$pupilsightFinanceFeeCategoryID";

    if (isActionAccessible($guid, $connection2, '/modules/Finance/invoices_manage_delete.php') == false) {
        $URL .= '&return=error0';
        header("Location: {$URL}");
    } else {
        //Proceed!
        if ($pupilsightFinanceInvoiceID == '') {
            $URL .= '&return=error1';
            header("Location: {$URL}");
        } else {
            try {
                $data = array('pupilsightFinanceInvoiceID' => $pupilsightFinanceInvoiceID);
                $sql = "SELECT * FROM pupilsightFinanceInvoice WHERE pupilsightFinanceInvoiceID=:pupilsightFinanceInvoiceID AND status='Pending'";
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
                    $data = array('pupilsightFinanceInvoiceID' => $pupilsightFinanceInvoiceID);
                    $sql = 'DELETE FROM pupilsightFinanceInvoice WHERE pupilsightFinanceInvoiceID=:pupilsightFinanceInvoiceID';
                    $result = $connection2->prepare($sql);
                    $result->execute($data);
                } catch (PDOException $e) {
                    $URL .= '&return=error2';
                    header("Location: {$URL}");
                    exit();
                }

                try {
                    $data = array('pupilsightFinanceInvoiceID' => $pupilsightFinanceInvoiceID);
                    $sql = 'DELETE FROM pupilsightFinanceInvoiceFee WHERE pupilsightFinanceInvoiceID=:pupilsightFinanceInvoiceID';
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
}
