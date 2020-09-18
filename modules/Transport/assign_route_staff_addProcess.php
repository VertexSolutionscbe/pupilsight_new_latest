<?php
/*
Pupilsight, Flexible & Open School System
*/

include '../../pupilsight.php';
// echo '<pre>';
// print_r($_POST);
// echo '</pre>';
// die();
$URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_POST['address']).'/assign_staff_to_route.php';

if (isActionAccessible($guid, $connection2, '/modules/Finance/assign_route_staff_add.php') != false) {
    $URL .= '&return=error0';
    header("Location: {$URL}");
} else {
    //Proceed!
    //Validate Inputs
    $academic_year = $_POST['pupilsightSchoolYearID'];
  if(!empty($_POST['return_rt']) && !empty($_POST['return_sp'])){
    $return_rt = $_POST['return_rt'];
    $return_stop = $_POST['return_sp'];
    $onwardroute = 0;
    $onwardsp = 0;
  }else{
    $return_rt = 0;
    $return_stop = 0;
    $onwardroute = $_POST['onwardroute'];
    $onwardsp = $_POST['onwardsp'];

  }

 
    $stu_id = explode(',', $_POST['stu_id']);
  
   $status  = 'active';
    
    if ( $stu_id == '') {
        $URL .= '&return=error1';
        header("Location: {$URL}");
    } else {
        //Check unique inputs for uniquness
        try {
        
           
                foreach($stu_id as $stu){
                    $data = array('pupilsightPersonID' => $stu);
                    $sql = 'SELECT * FROM trans_route_assign WHERE  pupilsightPersonID=:pupilsightPersonID';
                    $result = $connection2->prepare($sql);
                    $result->execute($data);
                   
              
                    if($result->rowCount() == 0){
                        $data1 = array('status' =>$status, 'pupilsightPersonID' => $stu,'route_id'=>$onwardroute,'return_route_id' =>$return_rt,'route_stop_id' =>$onwardsp,	'return_stop_id'=>$return_stop);
                      
                        $sql1 = 'INSERT INTO trans_route_assign SET status=:status,pupilsightPersonID=:pupilsightPersonID , route_id=:route_id,route_stop_id=:route_stop_id,return_route_id=:return_route_id,return_stop_id=:return_stop_id';
                        $result1 = $connection2->prepare($sql1);
                        $result1->execute($data1);
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
