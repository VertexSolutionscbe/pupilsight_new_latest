<?php
/*
Pupilsight, Flexible & Open School System
*/

include '../../pupilsight.php';

$id = $_POST['invoice_id'];
$pstid=$_POST['pstid'];
$URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_POST['address']).'/invoice_manage_delete.php&id='.$id;
$URLDelete = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_POST['address']).'/invoice_manage.php';

if (isActionAccessible($guid, $connection2, '/modules/Finance/invoice_manage_delete.php') == false) {
    //$URL .= '&return=error0';
    /////header("Location: {$URL}");
    echo "No permision ";
} else {
    //Proceed!
    //Check if school year specified
    if ($id == '') {
        //$URL .= '&return=error1';
        //header("Location: {$URL}");
        echo "Something went wrong";
    } else {
        try {
            // $data = array('id' => $id);
            // $sql = 'SELECT * FROM fn_fee_invoice WHERE id=:id';
            // $result = $connection2->prepare($sql);
            // $result->execute($data);
            $data = array('id' => $id);
            $sql = 'SELECT * FROM fn_fee_invoice_student_assign WHERE id=:id';
            $result = $connection2->prepare($sql);
            $result->execute($data);

           
        } catch (PDOException $e) {
            /*$URLDelete .= '&return=error2';
            header("Location: {$URLDelete}");
            exit();*/
            echo "Something went wrong";
        }

        // $dataa = array('fn_fee_invoice_id' => $id);
        // $sqla = 'SELECT * FROM fn_fees_class_assign WHERE fn_fee_invoice_id=:fn_fee_invoice_id';
        // $resulta = $connection2->prepare($sqla);
        // $resulta->execute($dataa);
       
        // if ($result->rowCount() != 1 || $resulta->rowCount() >= 1) {
        if ($result->rowCount() != 1) {    
            echo "You can't delete ";
           /*$URLDelete .= '&return=error3';
            header("Location: {$URLDelete}");*/
        } else {
            //Write to database
            try {
                $fn_fee_structure_id = $_POST['fn_fee_structure_id'];
                $pupilsightPersonID = $_POST['pupilsightPersonID'];
                $reason = $_POST['reason_for_cancel'];
                $fee_delete = $_POST['fee_delete'];
                $uid = $_SESSION[$guid]['pupilsightPersonID'];
                $cdt = date('Y-m-d H:i:s');
                $status = '2';

                $data = array('reason_for_cancel' => $reason, 'status' => $status, 'invoice_status' => 'Canceled', 'cancel_user_id' => $uid, 'id' => $id);
                $sql = 'UPDATE fn_fee_invoice_student_assign SET status=:status, reason_for_cancel=:reason_for_cancel, invoice_status=:invoice_status, cancel_user_id=:cancel_user_id WHERE id=:id';
                $result = $connection2->prepare($sql);
                $result->execute($data);

                if($fee_delete == '2'){

                    $data1 = array('fn_fee_structure_id' => $fn_fee_structure_id, 'pupilsightPersonID' => $pupilsightPersonID);
                    $sql1 = 'DELETE FROM fn_fees_student_assign WHERE fn_fee_structure_id=:fn_fee_structure_id AND pupilsightPersonID=:pupilsightPersonID';
                    $result1 = $connection2->prepare($sql1);
                    $result1->execute($data1);
                }
                // $data = array('reason_for_cancel' => $reason, 'fee_structure_delete_status' => $fee_delete, 'cancel_user_id' => $uid, 'cancel_date' => $cdt, 'status' => $status, 'id' => $id);
                // $sql = 'UPDATE fn_fee_invoice SET status=:status, reason_for_cancel=:reason_for_cancel, fee_structure_delete_status=:fee_structure_delete_status, cancel_user_id=:cancel_user_id, cancel_date=:cancel_date WHERE id=:id';
                // $result = $connection2->prepare($sql);
                // $result->execute($data);
                // $data1 = array('fn_fee_invoice_id' => $id);
                // $sql1 = 'DELETE FROM fn_fee_invoice_item WHERE fn_fee_invoice_id=:fn_fee_invoice_id';
                // $result1 = $connection2->prepare($sql1);
                // $result1->execute($data1);

                // $sql2 = 'DELETE FROM fn_fee_invoice_class_assign WHERE fn_fee_invoice_id=:fn_fee_invoice_id';
                // $result2 = $connection2->prepare($sql2);
                // $result2->execute($data1);

                
                // $data4 = array('fn_fee_invoice_id' => $id,'pupilsightPersonID'=>$pstid);
                // $sqls_a = 'DELETE FROM fn_fee_invoice_student_assign WHERE fn_fee_invoice_id=:fn_fee_invoice_id AND pupilsightPersonID=:pupilsightPersonID';
                // $results3 = $connection2->prepare($sqls_a);
                // $results3->execute($data4);
                
            } catch (PDOException $e) {
                //$URLDelete .= '&return=error2';
                //header("Location: {$URLDelete}");
                //exit();
                echo "Something went wrong";
            }
             echo "success";
            //$URLDelete = $URLDelete.'&return=success0';
           // header("Location: {$URLDelete}");
        }
    }
}
