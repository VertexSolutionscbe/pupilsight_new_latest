<?php
/*
Pupilsight, Flexible & Open School System
*/

include '../../pupilsight.php';

$fn_fee_invoice_id = $_GET['invoice_id'];
$pupilsightProgramID = $_GET['pupilsightProgramID'];
$URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_POST['address']).'/invoice_assign_manage.php&id='.$fn_fee_invoice_id;


if (isActionAccessible($guid, $connection2, '/modules/Finance/invoice_assign_manage_delete.php') == false) {
    $URL .= '&return=error0';
    header("Location: {$URL}");
} else {
    //Proceed!
    //Check if school year specified
    if ($fn_fee_invoice_id == '') {
        $URL .= '&return=error1';
        header("Location: {$URL}");
    } else {
        try {
            $data = array('fn_fee_invoice_id' => $fn_fee_invoice_id, 'pupilsightProgramID' => $pupilsightProgramID);
            $sql = 'SELECT * FROM fn_fee_invoice_class_assign WHERE fn_fee_invoice_id=:fn_fee_invoice_id AND pupilsightProgramID=:pupilsightProgramID';
            $result = $connection2->prepare($sql);
            $result->execute($data);
        } catch (PDOException $e) {
            $URL .= '&return=error2';
            header("Location: {$URL}");
            exit();
        }

        if ($result->rowCount() <= 1) {
            $URL .= '&return=error2';
            header("Location: {$URL}");
        } else {
            //Write to database
            try {
                $datad = array('fn_fee_invoice_id' => $fn_fee_invoice_id, 'pupilsightProgramID' => $pupilsightProgramID);
                $sqld = 'DELETE FROM fn_fee_invoice_class_assign WHERE fn_fee_invoice_id=:fn_fee_invoice_id AND pupilsightProgramID=:pupilsightProgramID';
                $resultd = $connection2->prepare($sqld);
                $resultd->execute($datad);
            } catch (PDOException $e) {
                $URL .= '&return=error2';
                header("Location: {$URL}");
                exit();
            }

            $URLDelete = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_POST['address']).'/invoice_assign_manage.php&id='.$fn_fee_invoice_id;
            $URLDelete = $URLDelete.'&return=success0';
            header("Location: {$URLDelete}");
        }
    }
}
