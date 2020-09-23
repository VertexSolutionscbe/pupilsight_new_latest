<?php
/*
Pupilsight, Flexible & Open School System
*/

include '../../pupilsight.php';

$id = $_GET['id'];
$URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_POST['address']).'/transport_route_delete.php&id='.$id;
$URLDelete = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_POST['address']).'/routes.php';

if (isActionAccessible($guid, $connection2, '/modules/Transport/transport_route_delete.php') == false) {
    $URL .= '&return=error0';
    header("Location: {$URL}");
} else {
    //Proceed!
    //Check if school year specified
    // print_r($id);die();
    if ($id == '') {
        $URL .= '&return=error1';
        header("Location: {$URL}");
    } else {
        try {
            $data = array('id' => $id);
            $sql = 'SELECT * FROM trans_routes WHERE id=:id';
            $result = $connection2->prepare($sql);
            $result->execute($data);

           
        } catch (PDOException $e) {
            $URLDelete .= '&return=error2';
            header("Location: {$URLDelete}");
            exit();
        }

        // $dataa = array('fn_fee_structure_id' => $id);
        // $sqla = 'SELECT * FROM fn_fees_class_assign WHERE fn_fee_structure_id=:fn_fee_structure_id';
        // $resulta = $connection2->prepare($sqla);
        // $resulta->execute($dataa);
       
        if ($result->rowCount() != 1) {
           $URLDelete .= '&return=error3';
            header("Location: {$URLDelete}");
        } else {
            //Write to database
            try {
                $data = array('id' => $id);
                $sql = 'DELETE FROM trans_routes WHERE id=:id';
                $result = $connection2->prepare($sql);
                $result->execute($data);

                $data1 = array('route_id' => $id);
                $sql1 = 'DELETE FROM trans_route_stops WHERE route_id=:route_id';
                $result1 = $connection2->prepare($sql1);
                $result1->execute($data1);
            } catch (PDOException $e) {
                $URLDelete .= '&return=error2';
                header("Location: {$URLDelete}");
                exit();
            }

            $URLDelete = $URLDelete.'&return=success0';
            header("Location: {$URLDelete}");
        }
    }
}
