<?php
/*
Pupilsight, Flexible & Open School System
*/

include '../../pupilsight.php';
$session = $container->get('session');
$collectionId = $session->get('collection_id');

$URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_POST['address']).'/fee_transaction_manage.php';

if (isActionAccessible($guid, $connection2, '/modules/Finance/fee_transaction_manage.php') == false) {
    $URL .= '&return=error0';
    header("Location: {$URL}");
} else {
    //Proceed!
    //Validate Inputs
    // echo "<pre>";
    // print_r($_POST);die();
    $payment_status = $_POST['payment_status'];
    $fd = explode('/', $_POST['payment_status_up_date']);
    $payment_status_up_date  = date('Y-m-d', strtotime(implode('-', array_reverse($fd))));
    
    if ($payment_status == '' or $collectionId == ''  or $payment_status_up_date == '') {
        $URL .= '&return=error1';
        header("Location: {$URL}");
    } else {
 
    $tid = explode(',', $collectionId);
    foreach($tid as $t){
        $data1 = array('payment_status' => $payment_status, 'payment_status_up_date' => $payment_status_up_date, 'id' => $t);
        $sql1 = 'UPDATE fn_fees_collection SET payment_status=:payment_status, payment_status_up_date=:payment_status_up_date WHERE id=:id';
        $result1 = $connection2->prepare($sql1);
        $result1->execute($data1);
    }
    // $URL .= "&return=success0";
    // header("Location: {$URL}");
}
}