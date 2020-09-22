<?php
/*
Pupilsight, Flexible & Open School System
*/

include '../../pupilsight.php';

$id = $_GET['id'];
$URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_POST['address']).'/deposit_account_manage.php';

if (isActionAccessible($guid, $connection2, '/modules/Finance/deposit_account_manage_edit.php') == false) {
    $URL .= '&return=error0';
    header("Location: {$URL}");
} else {
    //Proceed!
    //Check if school year specified
    if ($id == '') {
        $URL .= '&return=error1';
        header("Location: {$URL}");
    } else {
        try {
            $data = array('id' => $id);
            $sql = 'SELECT * FROM fn_fees_deposit_account WHERE id=:id';
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
            

            if ($name == '' or $code == '' ) {
                $URL .= '&return=error3';
                header("Location: {$URL}");
            } else {
                //Check unique inputs for uniquness
                try {
                    $data = array('ac_name' => $name, 'ac_code' => $code, 'id' => $id);
                    $sql = 'SELECT * FROM fn_fees_deposit_account WHERE (ac_name=:ac_name AND ac_code=:ac_code) AND NOT id=:id';
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
                        $data = array('fn_fee_item_id' => $fn_fee_item_id, 'ac_name' => $name, 'ac_code' => $code, 'overpayment_account' => $overpayment_account, 'id' => $id);
                        $sql = 'UPDATE fn_fees_deposit_account SET fn_fee_item_id=:fn_fee_item_id,ac_name=:ac_name, ac_code=:ac_code, overpayment_account=:overpayment_account WHERE id=:id';
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
