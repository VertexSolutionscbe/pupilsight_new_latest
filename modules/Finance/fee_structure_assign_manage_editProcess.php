<?php
/*
Pupilsight, Flexible & Open School System
*/

include '../../pupilsight.php';
include '../../db.php';

$id = $_GET['id'];
$fn_fee_structure_id = $_POST['fn_fee_structure_id'];
$URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_POST['address']).'/fee_structure_assign_manage.php&id='.$fn_fee_structure_id;

if (isActionAccessible($guid, $connection2, '/modules/Finance/fee_structure_assign_manage_edit.php') == false) {
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
            $sql = 'SELECT * FROM fn_fees_class_assign WHERE id=:id';
            $result = $connection2->prepare($sql);
            $result->execute($data);
        } catch (PDOException $e) {
            $URL .= '&return=error2';
            header("Location: {$URL}");
            exit();
        }

        if ($result->rowCount() != 1) {
            $URL .= '&return=error4';
            header("Location: {$URL}");
        } else {
            //Validate Inputs
            $program = $_POST['pupilsightProgramID'];
            $class = $_POST['pupilsightYearGroupID'];
            //$section = $_POST['pupilsightRollGroupID'];
            

            if ($fn_fee_structure_id == ''  or $program == '' or $class == '' ) {
                $URL .= '&return=error3';
                header("Location: {$URL}");
            } else {
                //Check unique inputs for uniquness
                try {
                    $data = array('fn_fee_structure_id'=>$fn_fee_structure_id,'pupilsightProgramID' => $program, 'pupilsightYearGroupID' => $class, 'id' => $id);
                    $sql = 'SELECT * FROM fn_fees_class_assign WHERE (fn_fee_structure_id=:fn_fee_structure_id AND pupilsightProgramID=:pupilsightProgramID AND pupilsightYearGroupID=:pupilsightYearGroupID) AND NOT id=:id';
                    $result = $connection2->prepare($sql);
                    $result->execute($data);
                } catch (PDOException $e) {
                    $URL .= '&return=error5';
                    header("Location: {$URL}");
                    exit();
                }

                if ($result->rowCount() > 0) {
                    $URL .= '&return=error3';
                    header("Location: {$URL}");
                } else {
                    //Write to database
                    try {
                        $datas = array( 'pupilsightYearGroupID' => $class);
                        $sqls = 'SELECT GROUP_CONCAT(pupilsightPersonID) AS stuid FROM pupilsightStudentEnrolment WHERE pupilsightYearGroupID=:pupilsightYearGroupID ';
                        $results = $connection2->prepare($sqls);
                        $results->execute($datas);
                        $values = $results->fetch();
                        $studentIds = explode(',', $values['stuid']);


                        $data = array('fn_fee_structure_id'=>$fn_fee_structure_id,'pupilsightProgramID' => $program, 'pupilsightYearGroupID' => $class, 'id' => $id);
                        $sql = 'UPDATE fn_fees_class_assign SET fn_fee_structure_id=:fn_fee_structure_id,pupilsightProgramID=:pupilsightProgramID, pupilsightYearGroupID=:pupilsightYearGroupID WHERE id=:id';
                        $result = $connection2->prepare($sql);
                        $result->execute($data);

                        $data1 = array('fn_fee_structure_id' => $fn_fee_structure_id);
                        $sql1 = 'DELETE FROM fn_fees_student_assign WHERE fn_fee_structure_id=:fn_fee_structure_id';
                        $result1 = $connection2->prepare($sql1);
                        $result1->execute($data1);

                        if(!empty($studentIds)){
                            
                            $sqls = "INSERT INTO fn_fees_student_assign (pupilsightPersonID,fn_fee_structure_id) VALUES "; 
                            foreach($studentIds as $key=>$value){
                                $sqls .= '('.$value.','.$fn_fee_structure_id.'),';
                            }  
                            $sqls = rtrim($sqls, ", ");
                            $conn->query($sqls);
                        }
                    } catch (PDOException $e) {
                        $URL .= '&return=error6';
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
