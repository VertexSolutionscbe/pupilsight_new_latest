<?php
/*
Pupilsight, Flexible & Open School System
*/

include '../../pupilsight.php';

$URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_POST['address']).'/fee_quick_cash_payment.php';

if (isActionAccessible($guid, $connection2, '/modules/Finance/fee_quick_cash_payment_add.php') == false) {
    $URL .= '&return=error0';
    header("Location: {$URL}");
} else {
    //Proceed!
    //Validate Inputs
    // echo '<pre>';
    // print_r($_POST);
    // echo '</pre>';
    // die();
    $amount = $_POST['amount'];
    $fine = $_POST['fine'];
    $grand_total = $_POST['grand_total'];
    $stu_id = explode(',', $_POST['stu_id']);
    
    if ($amount == ''  or  $stu_id == '' or $grand_total == '' ) {
        $URL .= '&return=error1';
        header("Location: {$URL}");
    } else {
       
        try {
           
                foreach($stu_id as $stu){
                  //  $data = array('fn_fee_structure_id'=>$festrId,'pupilsightPersonID' => $stu,'amount' => $amount,'fine' => $fine,'grand_total' => $grand_total);
                  //  $sql = 'SELECT * FROM pupilsight_quickcash_payment WHERE  pupilsightPersonID=:pupilsightPersonID';
                  //  $result = $connection2->prepare($sql);
                  //  $result->execute($data);
                  //  if($result->rowCount() == 0){
                        $data = array('pupilsightPersonID' => $stu,'amount'=>$amount,'fine'=>$fine,'grand_total'=>$grand_total);
                        $sql = 'INSERT INTO pupilsight_quickcash_payment SET pupilsightPersonID=:pupilsightPersonID ,amount=:amount,fine=:fine,grand_total=:grand_total';
                        $result = $connection2->prepare($sql);
                        $result->execute($data);
                   // }
                }
            
            
            $URL .= "&return=success0";
            header("Location: {$URL}");
        } catch (PDOException $e) {
            $URL .= '&return=error2';
            header("Location: {$URL}");
            exit();
        }

        
    }
}
