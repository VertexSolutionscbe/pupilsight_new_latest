<?php
/*
Pupilsight, Flexible & Open School System
*/

include '../../pupilsight.php';

include './moduleFunctions.php';

$pupilsightFinanceInvoiceeID = $_GET['pupilsightFinanceInvoiceeID'];
$URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_POST['address'])."/invoicees_manage_edit.php&pupilsightFinanceInvoiceeID=$pupilsightFinanceInvoiceeID&search=".$_GET['search'].'&allUsers='.$_GET['allUsers'];

if (isActionAccessible($guid, $connection2, '/modules/Finance/invoicees_manage_edit.php') == false) {
    $URL .= '&return=error0';
    header("Location: {$URL}");
} else {
    //Proceed!
    //Check if school year specified
    if ($pupilsightFinanceInvoiceeID == '') {
        $URL .= '&return=error1';
        header("Location: {$URL}");
    } else {
        try {
            $data = array('pupilsightFinanceInvoiceeID' => $pupilsightFinanceInvoiceeID);
            $sql = 'SELECT * FROM pupilsightFinanceInvoicee WHERE pupilsightFinanceInvoiceeID=:pupilsightFinanceInvoiceeID';
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
            //Proceed!
            $invoiceTo = $_POST['invoiceTo'];
            if ($invoiceTo == 'Company') {
                $companyName = $_POST['companyName'];
                $companyContact = $_POST['companyContact'];
                $companyAddress = $_POST['companyAddress'];
                $companyEmail = $_POST['companyEmail'];
                $companyCCFamily = $_POST['companyCCFamily'];
                $companyPhone = $_POST['companyPhone'];
                $companyAll = $_POST['companyAll'];
                $pupilsightFinanceFeeCategoryIDList = null;
                if ($companyAll == 'N') {
                    $pupilsightFinanceFeeCategoryIDList == '';
                    $pupilsightFinanceFeeCategoryIDArray = $_POST['pupilsightFinanceFeeCategoryIDList'];
                    if (count($pupilsightFinanceFeeCategoryIDArray) > 0) {
                        foreach ($pupilsightFinanceFeeCategoryIDArray as $pupilsightFinanceFeeCategoryID) {
                            $pupilsightFinanceFeeCategoryIDList .= $pupilsightFinanceFeeCategoryID.',';
                        }
                        $pupilsightFinanceFeeCategoryIDList = substr($pupilsightFinanceFeeCategoryIDList, 0, -1);
                    }
                }
            } else {
                $companyName = null;
                $companyContact = null;
                $companyAddress = null;
                $companyEmail = null;
                $companyCCFamily = null;
                $companyPhone = null;
                $companyAll = null;
                $pupilsightFinanceFeeCategoryIDList = null;
            }
            if ($invoiceTo == '') {
                $URL .= '&return=error1';
                header("Location: {$URL}");
            } else {
                //Write to database
                try {
                    $data = array('invoiceTo' => $invoiceTo, 'companyName' => $companyName, 'companyContact' => $companyContact, 'companyAddress' => $companyAddress, 'companyEmail' => $companyEmail, 'companyCCFamily' => $companyCCFamily, 'companyPhone' => $companyPhone, 'companyAll' => $companyAll, 'pupilsightFinanceFeeCategoryIDList' => $pupilsightFinanceFeeCategoryIDList, 'pupilsightFinanceInvoiceeID' => $pupilsightFinanceInvoiceeID);
                    $sql = 'UPDATE pupilsightFinanceInvoicee SET invoiceTo=:invoiceTo, companyName=:companyName, companyContact=:companyContact, companyAddress=:companyAddress, companyEmail=:companyEmail, companyCCFamily=:companyCCFamily, companyPhone=:companyPhone, companyAll=:companyAll, pupilsightFinanceFeeCategoryIDList=:pupilsightFinanceFeeCategoryIDList WHERE pupilsightFinanceInvoiceeID=:pupilsightFinanceInvoiceeID';
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
