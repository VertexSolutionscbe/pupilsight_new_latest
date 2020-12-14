<?php
/*
Pupilsight, Flexible & Open School System
*/

include '../../pupilsight.php';
// echo '<pre>';
// print_r($_POST);
// echo '</pre>';
// die();
$URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_POST['address']).'/fee_structure_assign_student_manage.php';

if (isActionAccessible($guid, $connection2, '/modules/Finance/fee_structure_assign_student_manage_add.php') == false) {
    $URL .= '&return=error0';
    header("Location: {$URL}");
} else {
    //Proceed!
    //Validate Inputs
    //$fn_fee_structure_id = $_POST['fn_fee_structure_id'];
    $fee_id = $_POST['fee_id'];
    $stu_id = explode(',', $_POST['stu_id']);
    
    if ($fee_id == ''  or $stu_id == '') {
        $URL .= '&return=error1';
        header("Location: {$URL}");
    } else {
        //Check unique inputs for uniquness
        try {
            foreach($fee_id as $fe){
                $festrId = $fe;
                foreach($stu_id as $stu){
                    $data = array('fn_fee_structure_id'=>$festrId,'pupilsightPersonID' => $stu);
                    $sql = 'SELECT * FROM fn_fees_student_assign WHERE fn_fee_structure_id=:fn_fee_structure_id AND pupilsightPersonID=:pupilsightPersonID';
                    $result = $connection2->prepare($sql);
                    $result->execute($data);
                    if($result->rowCount() == 0){
                        $data1 = array('fn_fee_structure_id'=>$festrId,'pupilsightPersonID' => $stu);
                        $sql1 = 'INSERT INTO fn_fees_student_assign SET fn_fee_structure_id=:fn_fee_structure_id,pupilsightPersonID=:pupilsightPersonID';
                        $result1 = $connection2->prepare($sql1);
                        $result1->execute($data1);
                    }
                }
            }
            
            // $URL .= "&return=success0";
            // header("Location: {$URL}");

        } catch (PDOException $e) {
            // $URL .= '&return=error2';
            // header("Location: {$URL}");
            // exit();
        }

        
    }
}
