<?php
/*
Pupilsight, Flexible & Open School System
*/

include '../../pupilsight.php';

$id = $_GET['id'];
$URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_POST['address']).'/routes.php';

if (isActionAccessible($guid, $connection2, '/modules/Transport/transport_route_edit.php') == false) {
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
            $sql = 'SELECT * FROM trans_routes WHERE id=:id';
            $result = $connection2->prepare($sql);
            $result->execute($data);
           
        } catch (PDOException $e) {
            $URL .= '&return=error2';
            header("Location: {$URL}");
            exit();
        }

        if ($result->rowCount() != 1) {
            $URL .= '&return=error2';
            header("Location: {$URL}");
        } else {
            // Validate Inputs
     
            $route_name = $_POST['route_name'];
            $pupilsightSchoolYearID = $_POST['pupilsightSchoolYearID'];
            $bus_id =  $_POST['bus_id'];
            $start_point = $_POST['start_point'];
            $start_time = $_POST['start_time'];
            $end_point = $_POST['end_point'];
            $end_time = $_POST['end_time'];
           // $num_stops = $_POST['num_stops'];
            $type =$_POST['type'] ;
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
        
            $udt = date('Y-m-d H:i:s');
            

            if ($route_name == ''  or $start_point == '' or $type == '' or $bus_id == '' or $start_time == '' or $end_time == ''  or $end_point == '' or $stop_name =='' or $stop_no =='' or $drop_time =='' or $pickup_time == '' ) {
                $URL .= '&return=error3';
                header("Location: {$URL}");
            } else {
                //Check unique inputs for uniquness
                try {

                    $data = array('route_name' => $route_name,  'id' => $id);
                    $sql = 'SELECT * FROM trans_routes WHERE route_name=:route_name  AND NOT id=:id';
                    $result = $connection2->prepare($sql);

                    $result->execute($data);
                    // print_r($data);die();
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
                        // if(!empty($fn_fees_head_id)){
                        //     $datah = array('id' => $fn_fees_head_id);
                        //     $sqlh = 'SELECT inv_fee_series_id, recp_fee_series_id FROM fn_fees_head WHERE id=:id';
                        //     $resulth = $connection2->prepare($sqlh);
                        //     $resulth->execute($datah);
                        //     $valueh = $resulth->fetch();
                        //     $inv_fee_series_id = $valueh['inv_fee_series_id'];
                        //     $recp_fee_series_id = $valueh['recp_fee_series_id'];
                        // } else {
                        //     $inv_fee_series_id = '';
                        //     $recp_fee_series_id = '';
                        // }
                        
                        
                        $data = array('route_name' => $route_name, 'start_point' => $start_point,'pupilsightSchoolYearID'=>$pupilsightSchoolYearID ,'start_time' => $start_time, 'end_point' => $end_point,'bus_id'=> $bus_id ,'end_time' => $end_time, 
                        'num_stops' => $noofstops, 'type' => $type, 'udt' => $udt, 'id' => $id);
                       
                    // $data = array('name' => $name, 'pupilsightSchoolYearID' => $pupilsightSchoolYearID, 'invoice_title' => $invoice_title, 'pupilsightSchoolFinanceYearID' => $pupilsightSchoolFinanceYearID, 'fn_fees_head_id' => $fn_fees_head_id, 'inv_fee_series_id' => $inv_fee_series_id, 'recp_fee_series_id' => $recp_fee_series_id, 'fn_fees_fine_rule_id' => $fn_fees_fine_rule_id, 'fn_fees_discount_id' => $fn_fees_discount_id, 'due_date' => $due_date, 'udt' => $udt, 'id' => $id);
                        $sql = 'UPDATE trans_routes SET route_name=:route_name, pupilsightSchoolYearID=:pupilsightSchoolYearID, start_point=:start_point, start_time=:start_time,bus_id =:bus_id,end_point=:end_point, end_time=:end_time,
                         num_stops=:num_stops, type=:type, udt=:udt WHERE id=:id';
                        $result = $connection2->prepare($sql);
                        $result->execute($data);
                        
                       

                        $datad = array('route_id' => $id);
                        $sqld = 'DELETE FROM trans_route_stops WHERE route_id=:route_id';
                        $resultd = $connection2->prepare($sqld);
                        $resultd->execute($datad);

                        $strId = $id;

                     
                    
                       foreach($stop_no as $k=>$d){

                        $stopno =  $stop_no[$k];
                        $stopname =  $stop_name[$k];
                        $droptime = $drop_time[$k];
                        $pickuptime = $pickup_time[$k];
                        // $onewayprice = $oneway_price[$k];
                        // $twowayprice = $twoway_price[$k];
                        // $tax_ = $tax[$k];
                        if(!empty($stopname) && !empty($stopno) && !empty($pickuptime) && !empty($droptime)){
                            $data1 = array('route_id' => $strId, 'bus_id'=>$bus_id,'stop_no' => $stopno,'stop_name' => $stopname,  'pickup_time' => $pickuptime, 'drop_time' => $droptime,'lat'=>$lat,'lng'=>$lng);
                         
                            $sql1 = 'INSERT INTO trans_route_stops SET  route_id=:route_id, bus_id=:bus_id,stop_no=:stop_no, stop_name=:stop_name, pickup_time=:pickup_time, drop_time=:drop_time,lat=:lat,lng=:lng';
                            $result1 = $connection2->prepare($sql1);
                            $result1->execute($data1);

                        }
                       
                    
                    }

                       
                    } catch (PDOException $e) {
                        $URL .= '&return=error2';
                        header("Location: {$URL}");
                        exit();
                    }

                    $URL.= '&return=success0';
                    header("Location: {$URL}");
                   
                }
            }
        }
    }
}
