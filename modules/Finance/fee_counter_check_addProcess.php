<?php
/*
Pupilsight, Flexible & Open School System
*/

include '../../pupilsight.php';

$URL = $_SESSION[$guid]['absoluteURL'].'/index.php';

$sucessurl = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_POST['address']).'/fee_collection_manage.php';
$session = $container->get('session');
$session->forget(['counterid']);

if (isActionAccessible($guid, $connection2, '/modules/Finance/fee_counter_check_add.php') == false) {
    $URL .= '&return=error0';
    header("Location: {$URL}");
} else {
    //Proceed!
    //Validate Inputs
    $fn_fees_counter_id = $_POST['fn_fees_counter_id'];
    $cuid = $_SESSION[$guid]['pupilsightPersonID'];
    $cdate = date('Y-m-d');
    $ctime = date('H:i:s');
    
    if ($fn_fees_counter_id == '' or $cuid == '') {
        $URL .= '&return=error1';
        header("Location: {$URL}");
    } else {
        //Check unique inputs for uniquness
         //Write to database
            try {
                $data = array('fn_fees_counter_id' => $fn_fees_counter_id, 'pupilsightPersonID' => $cuid, 'active_date' => $cdate, 'start_time' => $ctime);
                $sql = 'INSERT INTO fn_fees_counter_map SET fn_fees_counter_id=:fn_fees_counter_id, pupilsightPersonID=:pupilsightPersonID, active_date=:active_date, start_time=:start_time';
                $result = $connection2->prepare($sql);
                $result->execute($data);

                $data1 = array('status' => '1', 'id' => $fn_fees_counter_id);
                $sql1 = 'UPDATE fn_fees_counter SET status=:status WHERE id=:id';
                $result1 = $connection2->prepare($sql1);
                $result1->execute($data1);


            } catch (PDOException $e) {
                $URL .= '&return=error2';
                header("Location: {$URL}");
                exit();
            }

            //Last insert ID
            $AI = str_pad($connection2->lastInsertID(), 3, '0', STR_PAD_LEFT);

            //$sucessurl .= "&return=success0&editID=$AI";
            $session->set('counterid', $fn_fees_counter_id);
            header("Location: {$sucessurl}");
     
    }
}
