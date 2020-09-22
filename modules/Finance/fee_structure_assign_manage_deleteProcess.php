<?php
/*
Pupilsight, Flexible & Open School System
*/

include '../../pupilsight.php';

$id = $_GET['id'];
$URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_POST['address']).'/fee_structure_assign_manage_delete.php&id='.$id;


if (isActionAccessible($guid, $connection2, '/modules/Finance/fee_structure_assign_manage_delete.php') == false) {
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
            $values = $result->fetch();
            $sid = $values['fn_fee_structure_id'];
        } catch (PDOException $e) {
            $URL .= '&return=error2';
            header("Location: {$URL}");
            exit();
        }

        if ($result->rowCount() != 1) {
            $URL .= '&return=error2';
            header("Location: {$URL}");
        } else {
            //Write to database
            try {
                $data = array('id' => $id);
                $sql = 'DELETE FROM fn_fees_class_assign WHERE id=:id';
                $result = $connection2->prepare($sql);
                $result->execute($data);

                $data1 = array('fn_fee_structure_id' => $sid);
                $sql1 = 'DELETE FROM fn_fees_student_assign WHERE fn_fee_structure_id=:fn_fee_structure_id';
                $result1 = $connection2->prepare($sql1);
                $result1->execute($data1);
            } catch (PDOException $e) {
                $URL .= '&return=error2';
                header("Location: {$URL}");
                exit();
            }

            $URLDelete = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_POST['address']).'/fee_structure_assign_manage.php&id='.$sid;
            $URLDelete = $URLDelete.'&return=success0';
            header("Location: {$URLDelete}");
        }
    }
}
