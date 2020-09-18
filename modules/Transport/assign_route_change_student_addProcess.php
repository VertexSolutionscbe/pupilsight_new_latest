<?php
/*
Pupilsight, Flexible & Open School System
*/

include '../../pupilsight.php';
// echo '<pre>';
// print_r($_POST);
// echo '</pre>';

$URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_POST['address']).'/assign_route.php';

if (isActionAccessible($guid, $connection2, '/modules/Transport/assign_route_change_student_add.php') == false) {
    $URL .= '&return=error0';
    header("Location: {$URL}");
} else {
    //Proceed!
    //Validate Inputs
    //$fn_fee_structure_id = $_POST['fn_fee_structure_id'];

    $transport_type = $_POST['transport_for'];
    $academic_year = $_POST['pupilsightSchoolYearID'];
   
    
    if(!empty($_POST['select_route'])){
        $type = $_POST['select_route'];
    } else {
        $type = 'both';
    }

    if(!empty($_POST['onwardroute'])){
        $routeId = $_POST['onwardroute'];
    }

    if(!empty($_POST['onwardsp'])){
        $stopId = $_POST['onwardsp'];
    }

    if(!empty($_POST['return_rt'])){
        $routeId = $_POST['return_rt'];
    }

    if(!empty($_POST['return_sp'])){
        $stopId = $_POST['return_sp'];
    }

  if(!empty($_POST['return_rt']) && !empty($_POST['return_sp'])){
    $return_rt = $_POST['return_rt'];
    $return_stop = $_POST['return_sp'];
  }else{
    $return_rt = 0;
    $return_stop = 0;

  }

    $stu_id = explode(',', $_POST['stu_id']);
  
   $status  = '1';
    
    if ( $stu_id == '') {
        $URL .= '&return=error1';
        header("Location: {$URL}");
    } else {
        //Check unique inputs for uniquness
        try {
            if(!empty($stu_id)){
                foreach($stu_id as $st){
                    $datadel = array('pupilsightPersonID' => $st);
                    $sqldel = 'DELETE FROM trans_route_assign WHERE pupilsightPersonID=:pupilsightPersonID';
                    $resultdel = $connection2->prepare($sqldel);
                    $resultdel->execute($datadel);
                }
            }

            if($type == 'both'){
                foreach($stu_id as $stu){
                    $data = array('pupilsightPersonID' => $stu, 'type' => 'onward', 'pupilsightSchoolYearID' => $academic_year);

                    $sql = 'SELECT * FROM trans_route_assign WHERE pupilsightPersonID=:pupilsightPersonID AND pupilsightSchoolYearID=:pupilsightSchoolYearID AND type=:type';
                    $result = $connection2->prepare($sql);
                    $result->execute($data);
                
                    if($result->rowCount() == 0 && $_POST['onward_rt_tway'] != '' && $_POST['onward_sp_tway'] != ''){
                        $data1 = array('status' =>$status, 'pupilsightPersonID' => $stu,'pupilsightSchoolYearID' =>$academic_year,'route_id'=>$_POST['onward_rt_tway'],'route_stop_id' =>$_POST['onward_sp_tway'],'type' => 'onward', 'transport_type' => $transport_type);
                    
                        $sql1 = 'INSERT INTO trans_route_assign SET status=:status,pupilsightPersonID=:pupilsightPersonID , pupilsightSchoolYearID=:pupilsightSchoolYearID,route_id=:route_id,route_stop_id=:route_stop_id,type=:type, transport_type=:transport_type';
                        $result1 = $connection2->prepare($sql1);
                    
                        $result1->execute($data1);
                    } 

                    $data2 = array('pupilsightPersonID' => $stu, 'type' => 'return', 'pupilsightSchoolYearID' => $academic_year);

                    $sql2 = 'SELECT * FROM trans_route_assign WHERE pupilsightPersonID=:pupilsightPersonID AND pupilsightSchoolYearID=:pupilsightSchoolYearID AND type=:type';
                    $result2 = $connection2->prepare($sql2);
                    $result2->execute($data2);
                
                    if($result2->rowCount() == 0 && $_POST['return_rt_tway'] != '' && $_POST['return_sp_tway'] != ''){
                        $data3 = array('status' =>$status, 'pupilsightPersonID' => $stu,'pupilsightSchoolYearID' =>$academic_year,'route_id'=>$_POST['return_rt_tway'],'route_stop_id' =>$_POST['return_sp_tway'],'type' => 'return', 'transport_type' => $transport_type);
                    
                        $sql3 = 'INSERT INTO trans_route_assign SET status=:status,pupilsightPersonID=:pupilsightPersonID , pupilsightSchoolYearID=:pupilsightSchoolYearID,route_id=:route_id,route_stop_id=:route_stop_id,type=:type, transport_type=:transport_type';
                        $result3 = $connection2->prepare($sql3);
                    
                        $result3->execute($data3);
                    } 
                }
            } else {
                foreach($stu_id as $stu){
                    $data = array('pupilsightPersonID' => $stu, 'type' => $type, 'pupilsightSchoolYearID' => $academic_year);

                    $sql = 'SELECT * FROM trans_route_assign WHERE pupilsightPersonID=:pupilsightPersonID AND pupilsightSchoolYearID=:pupilsightSchoolYearID AND type=:type';
                    $result = $connection2->prepare($sql);
                    $result->execute($data);
                
                    if($result->rowCount() == 0){
                        $data1 = array('status' =>$status, 'pupilsightPersonID' => $stu,'pupilsightSchoolYearID' =>$academic_year,'route_id'=>$routeId,'route_stop_id' =>$stopId,'type' => $type , 'transport_type' => $transport_type);
                    
                        $sql1 = 'INSERT INTO trans_route_assign SET status=:status,pupilsightPersonID=:pupilsightPersonID , pupilsightSchoolYearID=:pupilsightSchoolYearID,route_id=:route_id,route_stop_id=:route_stop_id,type=:type, transport_type=:transport_type';
                        $result1 = $connection2->prepare($sql1);
                        $result1->execute($data1);
                    
                    } else {
                        $URL .= '&return=error2';
                        header("Location: {$URL}");
                        exit();
                    }
                }
            }    
           
            $URL .= "&return=success0";
            header("Location: {$URL}");
        } catch (PDOException $e) {
            $URL .= '&return=error2';
            header("Location: {$URL}");
            exit();
        }

        
    }
}
