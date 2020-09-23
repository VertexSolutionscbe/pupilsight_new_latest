<?php
/*
Pupilsight, Flexible & Open School System
*/

include '../../pupilsight.php';

$id = $_GET['id'];
$fn_fee_structure_id = $_POST['fn_fee_structure_id'];
$URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_POST['address']).'/fee_structure_assign_student_manage.php&id='.$fn_fee_structure_id;

if (isActionAccessible($guid, $connection2, '/modules/Finance/fee_structure_assign_student_manage_edit.php') == false) {
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
            $section = $_POST['pupilsightRollGroupID'];
            

            if ($fn_fee_structure_id == ''  or $program == '' or $class == '' or $section == '') {
                $URL .= '&return=error3';
                header("Location: {$URL}");
            } else {
                //Check unique inputs for uniquness
                try {
                    $data = array('fn_fee_structure_id'=>$fn_fee_structure_id,'pupilsightProgramID' => $program, 'pupilsightYearGroupID' => $class, 'pupilsightRollGroupID' => $section, 'id' => $id);
                    $sql = 'SELECT * FROM fn_fees_class_assign WHERE (fn_fee_structure_id=:fn_fee_structure_id AND pupilsightProgramID=:pupilsightProgramID AND pupilsightYearGroupID=:pupilsightYearGroupID AND pupilsightRollGroupID=:pupilsightRollGroupID) AND NOT id=:id';
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
                        $data = array('fn_fee_structure_id'=>$fn_fee_structure_id,'pupilsightProgramID' => $program, 'pupilsightYearGroupID' => $class, 'pupilsightRollGroupID' => $section, 'id' => $id);
                        $sql = 'UPDATE fn_fees_class_assign SET fn_fee_structure_id=:fn_fee_structure_id,pupilsightProgramID=:pupilsightProgramID, pupilsightYearGroupID=:pupilsightYearGroupID, pupilsightRollGroupID=:pupilsightRollGroupID WHERE id=:id';
                        $result = $connection2->prepare($sql);
                        $result->execute($data);
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
