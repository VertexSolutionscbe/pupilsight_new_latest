<?php
/*
Pupilsight, Flexible & Open School System
*/

include '../../pupilsight.php';

$URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/Staff/assign_staff_toClassSection.php';

if (isActionAccessible($guid, $connection2, '/modules/Staff/assign_staff_toClassSection.php') == false) {
    $URL .= '&return=error0';
    header("Location: {$URL}");
} else {
    
    //Proceed!
    //Validate Inputs
    //$fn_fee_structure_id = $_POST['fn_fee_structure_id'];
    $pupilsightMappingID  = $_POST['stu_id'];

   // $pupilsightPersonID = explode(',', $_POST['staff']);
     $pupilsightPersonID  =  $_POST['staff'];
     $cnt= count($pupilsightPersonID);
     //echo $cnt;exit;
  //  print_r($pupilsightPersonID);die();
    
    if ($pupilsightMappingID == ''  or $cnt == 0) {
        $URL .= '&return=error1';
        header("Location: {$URL}");
    } else {
        //Check unique inputs for uniquness

        try {
                $data1 = array('pupilsightPersonID' => $pupilsightMappingID);
                $sql1 = 'SELECT * FROM assignstaff_toclasssection WHERE pupilsightPersonID=:pupilsightPersonID';
                $result1 = $connection2->prepare($sql1);
            //  print_r($data1);die();
                $result1->execute($data1);
            } catch (PDOException $e) {
                $URL .= '&return=error2';
                header("Location: {$URL}");
                exit();
            }
            
            if ($result1->rowCount()>0) {
                    
                $URL.= '&return=error3';
      
                header("Location: {$URL}");
                
            } else {   
                

                    foreach($pupilsightPersonID as $pid){
                
                 $data = array('pupilsightMappingID' => $pupilsightMappingID, 'pupilsightPersonID' => $pid);
                
                 $sql = 'INSERT INTO assignstaff_toclasssection SET pupilsightMappingID=:pupilsightMappingID, pupilsightPersonID=:pupilsightPersonID';

                 $result = $connection2->prepare($sql);               
                 $result->execute($data);
                

                    }
                    $URL .= "&return=success0";
                    header("Location: {$URL}");
             
            
           


            
 
            }
        
    }
}
