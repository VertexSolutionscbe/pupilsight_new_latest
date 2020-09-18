<?php
/*
Pupilsight, Flexible & Open School System
*/

include '../../pupilsight.php';

$pupilsightFinanceInvoiceeUpdateID = $_GET['pupilsightFinanceInvoiceeUpdateID'];
$pupilsightFinanceInvoiceeID = $_POST['pupilsightFinanceInvoiceeID'];
$URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_POST['address'])."/data_finance_manage_edit.php&pupilsightFinanceInvoiceeUpdateID=$pupilsightFinanceInvoiceeUpdateID";

if (isActionAccessible($guid, $connection2, '/modules/Data Updater/data_finance_manage_edit.php') == false) {
    $URL .= '&return=error0';
    header("Location: {$URL}");
} else {
    //Proceed!
    //Check if school year specified
    if ($pupilsightFinanceInvoiceeUpdateID == '' or $pupilsightFinanceInvoiceeID == '') {
        $URL .= '&return=error1';
        header("Location: {$URL}");
    } else {
        try {
            $data = array('pupilsightFinanceInvoiceeUpdateID' => $pupilsightFinanceInvoiceeUpdateID);
            $sql = 'SELECT * FROM pupilsightFinanceInvoiceeUpdate WHERE pupilsightFinanceInvoiceeUpdateID=:pupilsightFinanceInvoiceeUpdateID';
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
            //Set values
            $data = array();
            $set = '';
            if (isset($_POST['newinvoiceToOn'])) {
                if ($_POST['newinvoiceToOn'] == 'on') {
                    $data['invoiceTo'] = $_POST['newinvoiceTo'];
                    $set .= 'pupilsightFinanceInvoicee.invoiceTo=:invoiceTo, ';
                }
            }
            if (isset($_POST['newcompanyNameOn'])) {
                if ($_POST['newcompanyNameOn'] == 'on') {
                    $data['companyName'] = $_POST['newcompanyName'];
                    $set .= 'pupilsightFinanceInvoicee.companyName=:companyName, ';
                }
            }
            if (isset($_POST['newcompanyContactOn'])) {
                if ($_POST['newcompanyContactOn'] == 'on') {
                    $data['companyContact'] = $_POST['newcompanyContact'];
                    $set .= 'pupilsightFinanceInvoicee.companyContact=:companyContact, ';
                }
            }
            if (isset($_POST['newcompanyAddressOn'])) {
                if ($_POST['newcompanyAddressOn'] == 'on') {
                    $data['companyAddress'] = $_POST['newcompanyAddress'];
                    $set .= 'pupilsightFinanceInvoicee.companyAddress=:companyAddress, ';
                }
            }
            if (isset($_POST['newcompanyEmailOn'])) {
                if ($_POST['newcompanyEmailOn'] == 'on') {
                    $data['companyEmail'] = $_POST['newcompanyEmail'];
                    $set .= 'pupilsightFinanceInvoicee.companyEmail=:companyEmail, ';
                }
            }
            if (isset($_POST['newcompanyCCFamilyOn'])) {
                if ($_POST['newcompanyCCFamilyOn'] == 'on') {
                    $data['companyCCFamily'] = $_POST['newcompanyCCFamily'];
                    $set .= 'pupilsightFinanceInvoicee.companyCCFamily=:companyCCFamily, ';
                }
            }
            if (isset($_POST['newcompanyPhoneOn'])) {
                if ($_POST['newcompanyPhoneOn'] == 'on') {
                    $data['companyPhone'] = $_POST['newcompanyPhone'];
                    $set .= 'pupilsightFinanceInvoicee.companyPhone=:companyPhone, ';
                }
            }
            if (isset($_POST['newcompanyAllOn'])) {
                if ($_POST['newcompanyAllOn'] == 'on') {
                    $data['companyAll'] = $_POST['newcompanyAll'];
                    $set .= 'pupilsightFinanceInvoicee.companyAll=:companyAll, ';
                }
            }
            if (isset($_POST['newpupilsightFinanceFeeCategoryIDListOn'])) {
                if ($_POST['newpupilsightFinanceFeeCategoryIDListOn'] == 'on') {
                    $data['pupilsightFinanceFeeCategoryIDList'] = $_POST['newpupilsightFinanceFeeCategoryIDList'];
                    $set .= 'pupilsightFinanceInvoicee.pupilsightFinanceFeeCategoryIDList=:pupilsightFinanceFeeCategoryIDList, ';
                }
            }

            if (strlen($set) > 1) {
                //Write to database
                try {
                    $data['pupilsightFinanceInvoiceeID'] = $pupilsightFinanceInvoiceeID;
                    $sql = 'UPDATE pupilsightFinanceInvoicee SET '.substr($set, 0, (strlen($set) - 2)).' WHERE pupilsightFinanceInvoiceeID=:pupilsightFinanceInvoiceeID';
                    $result = $connection2->prepare($sql);
                    $result->execute($data);
                } catch (PDOException $e) {
                    $URL .= '&return=error2';
                    header("Location: {$URL}");
                    exit();
                }

                //Write to database
                try {
                    $data = array('pupilsightFinanceInvoiceeUpdateID' => $pupilsightFinanceInvoiceeUpdateID);
                    $sql = "UPDATE pupilsightFinanceInvoiceeUpdate SET status='Complete' WHERE pupilsightFinanceInvoiceeUpdateID=:pupilsightFinanceInvoiceeUpdateID";
                    $result = $connection2->prepare($sql);
                    $result->execute($data);
                } catch (PDOException $e) {
                    $URL .= '&return=warning1';
                    header("Location: {$URL}");
                    exit();
                }

                $URL .= '&return=success0';
                header("Location: {$URL}");
            } else {
                //Write to database
                try {
                    $data = array('pupilsightFinanceInvoiceeUpdateID' => $pupilsightFinanceInvoiceeUpdateID);
                    $sql = "UPDATE pupilsightFinanceInvoiceeUpdate SET status='Complete' WHERE pupilsightFinanceInvoiceeUpdateID=:pupilsightFinanceInvoiceeUpdateID";
                    $result = $connection2->prepare($sql);
                    $result->execute($data);
                } catch (PDOException $e) {
                    $URL .= '&updateReturn=success1';
                    header("Location: {$URL}");
                    exit();
                }

                $URL .= '&return=success0';
                header("Location: {$URL}");
            }
        }
    }
}
