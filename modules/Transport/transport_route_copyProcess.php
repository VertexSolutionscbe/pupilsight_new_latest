<?php
/*
Pupilsight, Flexible & Open School System
*/

include '../../pupilsight.php';

$URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_POST['address']).'/routes.php';

if (isActionAccessible($guid, $connection2, '/modules/Transport/transport_route_copy.php') != false) {
    $URL .= '&return=error0';
    header("Location: {$URL}");
} else {
    //Proceed!
    //Validate Inputs
    // echo '<pre>';
    // print_r($_POST);
    // echo '</pre>';
    // die();
    $id = $_POST['id'];
    $route_name = $_POST['route_name'];
    $pupilsightSchoolYearID = $_POST['pupilsightSchoolYearID'];
    // $fd = explode('/', $_POST['due_date']);
   // $due_date  = date('Y-m-d', strtotime(implode('-', array_reverse($fd))));
    
    $cdt = date('Y-m-d H:i:s');
    
    if ($route_name == ''  or $pupilsightSchoolYearID == '' ) {
        $URL .= '&return=error1';
        header("Location: {$URL}");
    } else {
        //Check unique inputs for uniquness
        try {
            $data = array('route_name' => $name, 'pupilsightSchoolYearID' => $pupilsightSchoolYearID);
            $sql = 'SELECT * FROM trans_routes WHERE route_name=:route_name AND pupilsightSchoolYearID=:pupilsightSchoolYearID';
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
                $datas = array('id' => $id);
                $sqls = 'SELECT * FROM trans_routes WHERE id=:id';
                $results = $connection2->prepare($sqls);
                $results->execute($datas);
                $values = $results->fetch();

                // $datac = array('fn_fee_structure_id' => $id);
                // $sqlc = 'SELECT * FROM fn_fee_structure_item WHERE fn_fee_structure_id=:fn_fee_structure_id';
                // $resultc = $connection2->prepare($sqlc);
                // $resultc->execute($datac);
                // $childvalues = $resultc->fetchAll();

                $data = array('route_name' => $route_name, 'pupilsightSchoolYearID' => $pupilsightSchoolYearID, 'start_time' => $values['start_time'],
                 'start_point' => $values['start_point'], 'end_point' => $values['end_point'], 
                 'end_time' => $values['end_time'], 'num_stops' => $values['num_stops'], 'type' => $values['type'], 'cdt' => $cdt);
                
                $sql = 'INSERT INTO trans_routes SET route_name=:route_name, pupilsightSchoolYearID=:pupilsightSchoolYearID, start_point=:start_point, 
                start_time=:start_time, end_point=:end_point, end_time=:end_time, 
                num_stops=:num_stops, type=:type, cdt=:cdt';
                $result = $connection2->prepare($sql);
                $result->execute($data);
                
               
      

                // echo '<pre>';
                // print_r($data);
                // echo '</pre>';
            } catch (PDOException $e) {
                $URL .= '&return=error9';
                header("Location: {$URL}");
                exit();
            }

            //Last insert ID
            $AI = str_pad($connection2->lastInsertID(), 3, '0', STR_PAD_LEFT);

            $URL .= "&return=success0";
            header("Location: {$URL}");
        }
    }
}
