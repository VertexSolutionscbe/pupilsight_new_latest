<?php
/*
Pupilsight, Flexible & Open School System
*/

include '../../pupilsight.php';

$pupilsightSchoolYearID = $_GET['pupilsightSchoolYearID'];
$pupilsightFinanceBillingScheduleID = $_POST['pupilsightFinanceBillingScheduleID'];
$search = $_GET['search'];

if ($pupilsightFinanceBillingScheduleID == '' or $pupilsightSchoolYearID == '') { echo 'Fatal error loading this page!';
} else {
    $URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_POST['address'])."/billingSchedule_manage_edit.php&pupilsightFinanceBillingScheduleID=$pupilsightFinanceBillingScheduleID&pupilsightSchoolYearID=$pupilsightSchoolYearID&search=$search";

    if (isActionAccessible($guid, $connection2, '/modules/Finance/billingSchedule_manage_edit.php') == false) {
        $URL .= '&return=error0';
        header("Location: {$URL}");
    } else {
        //Proceed!
        //Check if person specified
        if ($pupilsightFinanceBillingScheduleID == '') {
            $URL .= '&return=error1';
            header("Location: {$URL}");
        } else {
            try {
                $data = array('pupilsightSchoolYearID' => $pupilsightSchoolYearID, 'pupilsightFinanceBillingScheduleID' => $pupilsightFinanceBillingScheduleID);
                $sql = 'SELECT * FROM pupilsightFinanceBillingSchedule WHERE pupilsightSchoolYearID=:pupilsightSchoolYearID AND pupilsightFinanceBillingScheduleID=:pupilsightFinanceBillingScheduleID';
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
                $name = $_POST['name'];
                $active = $_POST['active'];
                $description = $_POST['description'];
                $invoiceIssueDate = $_POST['invoiceIssueDate'];
                $invoiceDueDate = $_POST['invoiceDueDate'];

                if ($name == '' or $active == '' or $invoiceIssueDate == '' or $invoiceDueDate == '') {
                    $URL .= '&return=error1';
                    header("Location: {$URL}");
                } else {
                    //Write to database
                    try {
                        $data = array('pupilsightSchoolYearID' => $pupilsightSchoolYearID, 'name' => $name, 'active' => $active, 'description' => $description, 'invoiceIssueDate' => dateConvert($guid, $invoiceIssueDate), 'invoiceDueDate' => dateConvert($guid, $invoiceDueDate), 'pupilsightPersonIDUpdate' => $_SESSION[$guid]['pupilsightPersonID'], 'pupilsightFinanceBillingScheduleID' => $pupilsightFinanceBillingScheduleID);
                        $sql = "UPDATE pupilsightFinanceBillingSchedule SET pupilsightSchoolYearID=:pupilsightSchoolYearID, name=:name, active=:active, description=:description, invoiceIssueDate=:invoiceIssueDate, invoiceDueDate=:invoiceDueDate, pupilsightPersonIDUpdate=:pupilsightPersonIDUpdate, timestampUpdate='".date('Y-m-d H:i:s')."' WHERE pupilsightFinanceBillingScheduleID=:pupilsightFinanceBillingScheduleID";
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
