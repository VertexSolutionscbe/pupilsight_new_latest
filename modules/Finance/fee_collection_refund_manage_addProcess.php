<?php
/*
Pupilsight, Flexible & Open School System
*/

include '../../pupilsight.php';

$URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_POST['address']).'/fee_collection_manage.php';

if (isActionAccessible($guid, $connection2, '/modules/Finance/fee_collection_refund_manage_add.php') == false) {
    $URL .= '&return=error0';
    header("Location: {$URL}");
} else {
    //Proceed!
    //Validate Inputs
    // echo '<pre>';
    // print_r($_POST);
    // echo '</pre>';
    // die();
    $pupilsightPersonID = $_POST['pupilsightPersonID'];
    $payment_mode_id = $_POST['payment_mode_id'];
    $transcation_id = $_POST['transcation_id'];
    $bank_id = $_POST['bank_id'];
    $dd_cheque_no = $_POST['dd_cheque_no'];
    if(!empty($_POST['dd_cheque_date'])){
        $fd = explode('/', $_POST['dd_cheque_date']);
        $dd_cheque_date  = date('Y-m-d', strtotime(implode('-', array_reverse($fd))));
    } else {
        $dd_cheque_date  = '';
    }
    $dd_cheque_amount = $_POST['dd_cheque_amount'];
    $fn_fees_series_id = $_POST['fn_fees_series_id'];
    if(!empty($_POST['refund_date'])){
        $pd = explode('/', $_POST['refund_date']);
        $refund_date  = date('Y-m-d', strtotime(implode('-', array_reverse($pd))));
    } else {
        $refund_date  = '';
    }
    $refund_amount = $_POST['refund_amount'];
    $remarks = $_POST['remarks'];
    $cdt = date('Y-m-d H:i:s');

    
    if ($transcation_id == ''  or $pupilsightPersonID == '' or $refund_amount == '') {
        $URL .= '&return=error1';
        header("Location: {$URL}");
    } else {
        //Check unique inputs for uniquness
        try {
            $data = array('transcation_id' => $transcation_id, 'pupilsightPersonID' => $pupilsightPersonID);
            $sql = 'SELECT * FROM fn_refund WHERE transcation_id=:transcation_id AND pupilsightPersonID=:pupilsightPersonID';
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
                // if(!empty($fn_fees_head_id)){
                //     $datah = array('id' => $fn_fees_head_id);
                //     $sqlh = 'SELECT inv_fee_series_id, recp_fee_series_id FROM fn_fees_head WHERE id=:id';
                //     $resulth = $connection2->prepare($sqlh);
                //     $resulth->execute($datah);
                //     $valueh = $resulth->fetch();
                //     $inv_fee_series_id = $valueh['inv_fee_series_id'];
                //     $recp_fee_series_id = $valueh['recp_fee_series_id'];
                // } else {
                //     $inv_fee_series_id = '';
                //     $recp_fee_series_id = '';
                // }
                
                $data = array('pupilsightPersonID' => $pupilsightPersonID, 'payment_mode_id' => $payment_mode_id, 'transcation_id' => $transcation_id, 'bank_id' => $bank_id, 'dd_cheque_no' => $dd_cheque_no, 'dd_cheque_date' => $dd_cheque_date, 'dd_cheque_amount' => $dd_cheque_amount, 'fn_fees_series_id' => $fn_fees_series_id,'refund_date' => $refund_date, 'refund_amount' => $refund_amount, 'remarks' => $remarks, 'cdt' => $cdt);
                
                $sql = 'INSERT INTO fn_refund SET  pupilsightPersonID=:pupilsightPersonID, payment_mode_id=:payment_mode_id, transcation_id=:transcation_id, bank_id=:bank_id, dd_cheque_no=:dd_cheque_no, dd_cheque_date=:dd_cheque_date, dd_cheque_amount=:dd_cheque_amount, fn_fees_series_id=:fn_fees_series_id, refund_date=:refund_date, refund_amount=:refund_amount, remarks=:remarks, cdt=:cdt';
                $result = $connection2->prepare($sql);
                $result->execute($data);
                
                $strId = $connection2->lastInsertID();

                
                // echo '<pre>';
                // print_r($data);
                // echo '</pre>';
            } catch (PDOException $e) {
                $URL .= '&return=error9';
                header("Location: {$URL}");
                exit();
            }

            //Last insert ID
            $AI = str_pad($connection2->lastInsertID(), 3, '0', STR_PAD_LEFT);

            $URL .= "&return=success0";
            header("Location: {$URL}");
        }
    }
}
