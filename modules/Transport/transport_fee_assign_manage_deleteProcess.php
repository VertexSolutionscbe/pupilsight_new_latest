<?php
/*
Pupilsight, Flexible & Open School System
*/

include '../../pupilsight.php';

$id = $_GET['id'];
$URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_POST['address']).'/transport_fee_assign_manage_delete.php&id='.$id;


if (isActionAccessible($guid, $connection2, '/modules/Transport/transport_fee_assign_manage_delete.php') == false) {
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
            $sql = 'SELECT * FROM trans_schedule_assign_class WHERE id=:id';
            $result = $connection2->prepare($sql);
            $result->execute($data);
            $values = $result->fetch();
            $sid = $values['schedule_id'];
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
                $sql = 'DELETE FROM trans_schedule_assign_class WHERE id=:id';
                $result = $connection2->prepare($sql);
                $result->execute($data);

                $data1 = array('schedule_id' => $sid);
                $sql1 = 'DELETE FROM trans_schedule_assign_student WHERE schedule_id=:schedule_id';
                $result1 = $connection2->prepare($sql1);
                $result1->execute($data1);
            } catch (PDOException $e) {
                $URL .= '&return=error2';
                header("Location: {$URL}");
                exit();
            }

            $URLDelete = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_POST['address']).'/transport_fee_assign_manage.php&id='.$sid;
            $URLDelete = $URLDelete.'&return=success0';
            header("Location: {$URLDelete}");
        }
    }
}
