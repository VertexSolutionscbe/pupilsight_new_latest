<?php
/*
Pupilsight, Flexible & Open School System
*/

include '../../pupilsight.php';
$schedule_id = $_POST['schedule_id'];

$URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/Transport/transport_amount_manage.php&id='.$schedule_id.' ';

if (isActionAccessible($guid, $connection2, '/modules/Transport/transport_amount_config.php') == false) {
    $URL .= '&return=error0';
    header("Location: {$URL}");
} else {
    // echo '<pre>';
    // print_r($_POST);
    // echo '</pre>';
    // die();
    $transtype = $_POST['trans_type'];
    
    $type = $_POST['type'];
    
    

    if($transtype == 'Route'){
        $route_id =  $_POST['route_id'];
        $oneway_price = $_POST['oneway_price'];
        $twoway_price = $_POST['twoway_price'];
        $tax = $_POST['tax'];
        $k = 1;
        foreach($route_id as  $d){
            $route_id1 =  $d;
            $schedule_id =  $schedule_id;
            $type = $type;
            $oneway_price1 = $oneway_price[$k];
            $twoway_price1 = $twoway_price[$k];
            $tax1 = $tax[$k];
            if(!empty($route_id1) && !empty($schedule_id) && !empty($type) ){
                $data = array('route_id' => $route_id1, 'schedule_id' => $schedule_id, 'type' => $type);
                $sql = 'SELECT * FROM trans_route_price WHERE route_id=:route_id AND schedule_id=:schedule_id AND type=:type';
                $result = $connection2->prepare($sql);
                $result->execute($data);
                
                if ($result->rowCount() == '0') {

                    $data1 = array('route_id' =>$route_id1,'schedule_id'=>$schedule_id,'type' => $type,'oneway_price' => $oneway_price1,  'twoway_price' => $twoway_price1, 'tax' => $tax1);
                    $sql1 = 'INSERT INTO trans_route_price SET  route_id=:route_id, schedule_id=:schedule_id,type=:type, oneway_price=:oneway_price, twoway_price=:twoway_price, tax=:tax';
                    $result1 = $connection2->prepare($sql1);
                    $result1->execute($data1);
                }
            }
            $k++;
        }
    } else {
        $stop_route_id =  $_POST['stop_route_id'];
        $stop_id =  $_POST['stop_id'];
        $stop_oneway_price = $_POST['stop_oneway_price'];
        $stop_twoway_price = $_POST['stop_twoway_price'];
        $stop_tax = $_POST['stop_tax'];
        $k = 1;
        foreach($stop_id as  $d){
            $stop_id1 =  $d;
            $route_id =  $stop_route_id;
            $schedule_id =  $schedule_id;
            $type = $type;
            $oneway_price1 = $stop_oneway_price[$k];
            $twoway_price1 = $stop_twoway_price[$k];
            $tax1 = $stop_tax[$k];
            if(!empty($route_id) && !empty($schedule_id) && !empty($type) ){
                $data = array('route_id' => $route_id , 'stop_id' => $stop_id1 , 'schedule_id' => $schedule_id, 'type' => $type);
                $sql = 'SELECT * FROM trans_route_price WHERE route_id=:route_id AND stop_id=:stop_id AND schedule_id=:schedule_id AND type=:type';
                $result = $connection2->prepare($sql);
                $result->execute($data);
                
                if ($result->rowCount() == '0') {

                    $data1 = array('route_id' =>$route_id, 'stop_id' => $stop_id1,'schedule_id'=>$schedule_id,'type' => $type,'oneway_price' => $oneway_price1,  'twoway_price' => $twoway_price1, 'tax' => $tax1);
                    $sql1 = 'INSERT INTO trans_route_price SET  route_id=:route_id, stop_id=:stop_id,  schedule_id=:schedule_id,type=:type, oneway_price=:oneway_price, twoway_price=:twoway_price, tax=:tax';
                    $result1 = $connection2->prepare($sql1);
                    $result1->execute($data1);
                }
            }
            $k++;
        }
    }
    
    
    $AI = str_pad($connection2->lastInsertID(), 3, '0', STR_PAD_LEFT);

    $URL .= "&return=success0";
    header("Location: {$URL}");
}
