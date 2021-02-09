<?php
/*
Pupilsight, Flexible & Open School System
*/
include '../../pupilsight.php';

$URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_POST['address']).'/routes.php';

if (isActionAccessible($guid, $connection2, '/modules/Transport/transport_route_add.php') == false) {
    $URL .= '&return=error0';
    header("Location: {$URL}");
} else {
    // echo '<pre>';
    // print_r($_POST);
    // echo '</pre>';
    // die();
    $route_name = $_POST['route_name'];
    $pupilsightSchoolYearID = $_POST['pupilsightSchoolYearID'];
    $bus_id =  $_POST['bus_id'];
    $start_point = $_POST['start_point'];
    $start_time = $_POST['start_time'];
    $end_point = $_POST['end_point'];
    $end_time = $_POST['end_time'];
    $num_stops = 2;
    $type = $_POST['type'];
    $stop_name =$_POST['stop_name'] ;
    $pickup_time = $_POST['pickup_time'];
    $drop_time = $_POST['drop_time'];
    $stop_no = $_POST['stop_no'];
    $noofstops= count($stop_no);
    // $tax = $_POST['tax'];
    // $oneway_price = $_POST['oneway_price'];
    // $twoway_price = $_POST['twoway_price'];
    $lat = '';
    $lng = '';
//    print_r($stop_name); die();

    $cdt = date('Y-m-d H:i:s');
    
    if ($route_name == ''  or $start_point == '' or $pupilsightSchoolYearID == '' or $bus_id == '' or $type == '' or $start_time == '' or $end_time == ''  or $end_point == '' or $stop_name =='' or $stop_no =='' or $drop_time =='' or $pickup_time == '' ) {
        $URL .= '&return=error1';
        header("Location: {$URL}");
    } else {
        //Check unique inputs for uniquness
        try {
            $data = array('route_name' => $route_name , 'pupilsightSchoolYearID' => $pupilsightSchoolYearID);
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
              // print_r($data);die();
            $data2 = array('route_name' => $route_name, 'pupilsightSchoolYearID' => $pupilsightSchoolYearID);
            $sql2 = 'SELECT * FROM trans_routes WHERE route_name=:route_name AND pupilsightSchoolYearID=:pupilsightSchoolYearID';
            $result2 = $connection2->prepare($sql2);
            $result2->execute($data2);
                
            if ($result2->rowCount() > 0) {
                $URL .= '&return=error3';
                header("Location: {$URL}");
            } else {
                $data = array('route_name' => $route_name,'bus_id'=>$bus_id,'pupilsightSchoolYearID'=> $pupilsightSchoolYearID, 'start_point' => $start_point, 'start_time' => $start_time, 'end_point' => $end_point, 'end_time' => $end_time, 
                'num_stops' => $noofstops, 'type' => $type, 'cdt' => $cdt);

                
                $sql = 'INSERT INTO trans_routes SET route_name=:route_name, bus_id=:bus_id,pupilsightSchoolYearID=:pupilsightSchoolYearID,start_point=:start_point, start_time=:start_time, end_point=:end_point, end_time=:end_time,
                 num_stops=:num_stops, type=:type,  cdt=:cdt';
                $result = $connection2->prepare($sql);
                $result->execute($data);


                //stops 
                $strId = $connection2->lastInsertID();
                

                $k = 1;
                foreach($stop_no as  $d){
                    //  print_r( $pickup_time = $pickup_time[$k];);
                        $stopno =  $stop_no[$k];
                        $stopname =  $stop_name[$k];
                        $droptime = $drop_time[$k];
                        $pickuptime = $pickup_time[$k];
                        // $onewayprice = $oneway_price[$k];
                        // $twowayprice = $twoway_price[$k];
                        // $tax_ = $tax[$k];
                if(!empty($stopname) && !empty($stopno) && !empty($pickuptime) && !empty($droptime)){
                    
                   
                    $data1 = array('route_id' =>$strId,'bus_id'=>$bus_id,'stop_no' => $stopno,'stop_name' => $stopname,  'pickup_time' => $pickuptime, 'drop_time' => $droptime,'lat'=>$lat,'lng'=>$lng);

                    
                    $sql1 = 'INSERT INTO trans_route_stops SET  route_id=:route_id, bus_id=:bus_id,stop_no=:stop_no, stop_name=:stop_name, pickup_time=:pickup_time, drop_time=:drop_time,lat=:lat,lng=:lng';
                    $result1 = $connection2->prepare($sql1);
                    $result1->execute($data1);
                    }
                    $k++;
                  
                }
               
            }    
             
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
