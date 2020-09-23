<?php
/*
Pupilsight, Flexible & Open School System
*/

include '../../pupilsight.php';
include '../../db.php';

$fn_fee_structure_id = $_POST['fn_fee_structure_id'];
$URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_POST['address']).'/fee_structure_assign_manage.php&id='.$fn_fee_structure_id;

if (isActionAccessible($guid, $connection2, '/modules/Finance/fee_structure_assign_manage_add.php') == false) {
    $URL .= '&return=error0';
    header("Location: {$URL}");
} else {


    //Proceed!
    //Validate Inputs
    //$fn_fee_structure_id = $_POST['fn_fee_structure_id'];
    $program = $_POST['pupilsightProgramID'];
    $class = $_POST['class'];
    // echo '<pre>';
    // print_r($_POST);
    // echo '</pre>';
    // die(0);
    if ($fn_fee_structure_id == ''  or $program == '' or $class == '') {
        $URL .= '&return=error1';
        header("Location: {$URL}");
    } else {
        //Check unique inputs for uniquness
        try {
            $data = array('fn_fee_structure_id'=>$fn_fee_structure_id,'pupilsightProgramID' => $program, 'pupilsightYearGroupID' => $class);
            $sql = 'SELECT * FROM fn_fees_class_assign WHERE fn_fee_structure_id=:fn_fee_structure_id AND pupilsightProgramID=:pupilsightProgramID AND pupilsightYearGroupID=:pupilsightYearGroupID';
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
                foreach($class as $cl){
                    $datas = array( 'pupilsightYearGroupID' => $cl);
                    $sqls = 'SELECT GROUP_CONCAT(pupilsightPersonID) AS stuid FROM pupilsightStudentEnrolment WHERE pupilsightYearGroupID=:pupilsightYearGroupID ';
                    $results = $connection2->prepare($sqls);
                    $results->execute($datas);
                    $values = $results->fetch();
                    $studentIds = explode(',', $values['stuid']);
                    
                    if(!empty($studentIds)){
                        $data = array('fn_fee_structure_id'=>$fn_fee_structure_id,'pupilsightProgramID' => $program, 'pupilsightYearGroupID' => $cl);
                        $sql = 'INSERT INTO fn_fees_class_assign SET fn_fee_structure_id=:fn_fee_structure_id,pupilsightProgramID=:pupilsightProgramID, pupilsightYearGroupID=:pupilsightYearGroupID';
                        $result = $connection2->prepare($sql);
                        $result->execute($data);

                        $sql = "INSERT INTO fn_fees_student_assign (pupilsightPersonID,fn_fee_structure_id) VALUES "; 
                        foreach($studentIds as $key=>$value){
                            $sql .= '('.$value.','.$fn_fee_structure_id.'),';
                        }  
                        $sql = rtrim($sql, ", ");
                        $conn->query($sql);
                    }
                }
                
            } catch (PDOException $e) {
                $URL .= '&return=error2';
                header("Location: {$URL}");
                exit();
            }

            //Last insert ID
            $AI = str_pad($connection2->lastInsertID(), 3, '0', STR_PAD_LEFT);

            $URL .= "&return=success0&editID=$AI";
            header("Location: {$URL}");
        // }
    }
}
}