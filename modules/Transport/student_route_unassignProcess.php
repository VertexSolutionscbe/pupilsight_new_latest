<?php
/*
Pupilsight, Flexible & Open School System
*/

include '../../pupilsight.php';
// $id = $_GET['id'];
$session = $container->get('session');
$studentids = $session->get('student_ids');

$URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_POST['address']).'/unassign_route_student.php&id=';
$URLDelete = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_POST['address']).'/assign_route.php';

if (isActionAccessible($guid, $connection2, '/modules/Transport/unassign_route_student.php') != false) {
    $URL .= '&return=error0';
    header("Location: {$URL}");
} else {
    //Proceed!
    //Check if school year specified
    $stu_id = explode(',', $studentids);
    if ($stu_id == '') {
        $URL .= '&return=error1';
        header("Location: {$URL}");
    } else {
        foreach($stu_id as $stu){
        try {
            // $data = array('id' => $id);
            // $sql = 'SELECT * FROM trans_routes WHERE id=:id';
            // $result = $connection2->prepare($sql);
            // $result->execute($data);
            $data = array('pupilsightPersonID' => $stu);
            $sql = 'SELECT * FROM trans_route_assign WHERE  pupilsightPersonID=:pupilsightPersonID';
            $result = $connection2->prepare($sql);
            $result->execute($data);
           
        } catch (PDOException $e) {
            $URLDelete .= '&return=error2';
            header("Location: {$URLDelete}");
            exit();
        }
      
        
        }
        // $dataa = array('fn_fee_structure_id' => $id);
        // $sqla = 'SELECT * FROM fn_fees_class_assign WHERE fn_fee_structure_id=:fn_fee_structure_id';
        // $resulta = $connection2->prepare($sqla);
        // $resulta->execute($dataa);
       
        if ($result->rowCount() != 1 >= 1) {
           $URLDelete .= '&return=error3';
            header("Location: {$URLDelete}");
        } else {
            //Write to database
            foreach($stu_id as $stu){
            try {
             
                $data = array('pupilsightPersonID' => $stu);
                $sql = 'DELETE FROM trans_route_assign WHERE pupilsightPersonID=:pupilsightPersonID';
                $result = $connection2->prepare($sql);
                $result->execute($data);
                
               
                // $data1 = array('fn_fee_structure_id' => $id);
                // $sql1 = 'DELETE FROM fn_fee_structure_item WHERE fn_fee_structure_id=:fn_fee_structure_id';
                // $result1 = $connection2->prepare($sql1);
                // $result1->execute($data1);
            } catch (PDOException $e) {
                $URLDelete .= '&return=error2';
                header("Location: {$URLDelete}");
                exit();
            }
        }

            $URLDelete = $URLDelete.'&return=success0';
            header("Location: {$URLDelete}");
        }
    }
}
