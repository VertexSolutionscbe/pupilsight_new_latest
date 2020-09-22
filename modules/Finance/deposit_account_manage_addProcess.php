<?php
/*
Pupilsight, Flexible & Open School System
*/

include '../../pupilsight.php';

$URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_POST['address']).'/deposit_account_manage.php';

if (isActionAccessible($guid, $connection2, '/modules/Finance/deposit_account_manage_add.php') == false) {
    $URL .= '&return=error0';
    header("Location: {$URL}");
} else {
    //Proceed!
    //Validate Inputs
    $name = $_POST['ac_name'];
    $code = $_POST['ac_code'];
    $fn_fee_item_id = $_POST['fn_fee_item_id'];
    if(!empty($_POST['overpayment_account'])){
        $overpayment_account = $_POST['overpayment_account'];
        $data1 = array('overpayment_account' => '0');
        $sql1 = 'UPDATE fn_fees_deposit_account SET overpayment_account=:overpayment_account';
        $result1 = $connection2->prepare($sql1);
        $result1->execute($data1);
    } else {
        $overpayment_account = '';
    }
    
    if ($name == ''  or $code == '') {
        $URL .= '&return=error1';
        header("Location: {$URL}");
    } else {
        //Check unique inputs for uniquness
        try {
            $data = array('ac_name' => $name, 'ac_code' => $code);
            $sql = 'SELECT * FROM fn_fees_deposit_account WHERE ac_name=:ac_name AND ac_code=:ac_code';
            $result = $connection2->prepare($sql);
            $result->execute($data);
        } catch (PDOException $e) {
            $URL .= '&return=error2';
            header("Location: {$URL}");
            exit();
        }

        if ($result->rowCount() > 0) {
            $URL .= '&return=error3';
            header("Location: {$URL}");
        } else {
            //Write to database
            try {
                $data = array('fn_fee_item_id'=>$fn_fee_item_id, 'ac_name' => $name, 'ac_code' => $code, 'overpayment_account' => $overpayment_account);
                $sql = 'INSERT INTO fn_fees_deposit_account SET fn_fee_item_id=:fn_fee_item_id,ac_name=:ac_name, ac_code=:ac_code, overpayment_account=:overpayment_account';
                $result = $connection2->prepare($sql);
                $result->execute($data);
            } catch (PDOException $e) {
                $URL .= '&return=error2';
                header("Location: {$URL}");
                exit();
            }

            //Last insert ID
            $AI = str_pad($connection2->lastInsertID(), 3, '0', STR_PAD_LEFT);

            $URL .= "&return=success0&editID=$AI";
            header("Location: {$URL}");
        }
    }
}
