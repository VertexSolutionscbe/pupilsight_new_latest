<?php
/*
Pupilsight, Flexible & Open School System
*/

include '../../pupilsight.php';

$URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_POST['address']).'/fee_payment_gateway_manage.php';

if (isActionAccessible($guid, $connection2, '/modules/Finance/fee_payment_gateway_manage_add.php') == false) {
    $URL .= '&return=error0';
    header("Location: {$URL}");
} else {
    //Proceed!
    //Validate Inputs
    $name = $_POST['name'];
    $gateway_name = $_POST['gateway_name'];
    $username = $_POST['username'];
    $password = $_POST['password'];
    $mid = $_POST['mid'];
    $key_id = $_POST['key_id'];
    $key_secret = $_POST['key_secret'];
    $terms_and_conditions = $_POST['terms_and_conditions'];
    // $sequenceNumber = $_POST['sequenceNumber'];
    
    if ($name == '' && $gateway_name == '') {
        $URL .= '&return=error1';
        header("Location: {$URL}");
    } else {
        //Check unique inputs for uniquness
        try {
            $data = array('gateway_name' => $gateway_name);
            $sql = 'SELECT * FROM fn_fee_payment_gateway WHERE gateway_name=:gateway_name';
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
                $data = array('name' => $name,'gateway_name' => $gateway_name,'username' => $username,'password' => $password,'mid' => $mid,'key_id' => $key_id,'key_secret' => $key_secret, 'terms_and_conditions' => $terms_and_conditions);
                $sql = 'INSERT INTO fn_fee_payment_gateway SET name=:name, gateway_name=:gateway_name, username=:username, password=:password, mid=:mid, key_id=:key_id, key_secret=:key_secret, terms_and_conditions=:terms_and_conditions';
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
