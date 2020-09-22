<?php
/*
Pupilsight, Flexible & Open School System
*/

include '../../pupilsight.php';


/*
 echo '<pre>';
print_r($_POST);
echo '</pre>';
exit;*/
$URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_POST['address']).'/student_view.php';

if (isActionAccessible($guid, $connection2, '/modules/Students/reg_dereg_student_view.php') != false) {
    $URL .= '&return=error0';
    header("Location: {$URL}");
} else {
    //Proceed!
  
    $pupilsightProgramID = $_POST['pupilsightProgramID'];
   
    $pupilsightYearGroupID = $_POST['pupilsightYearGroupID'];
    $dereg_status = $_POST['dereg_status'];
    $reg_degreg = $_POST['reg_degreg'];
    $cdt = date('Y-m-d H:i:s');
    $cuid = $_SESSION[$guid]['pupilsightPersonID'];          

     $stu_id =  $_POST['stu_id'];
  
   //$status  = '1';
    
    if ($stu_id == '') {
        $URL .= '&return=error1';
        header("Location: {$URL}");
    } else {
        //Check unique inputs for uniquness

        try {
            $data = array('pupilsightPersonID' => $stu_id);
            $sql = 'SELECT * FROM pupilsightStudentEnrolment WHERE pupilsightPersonID=:pupilsightPersonID ';
            $result = $connection2->prepare($sql);
            $result->execute($data);
            $prevdata=  $result->fetch();
             
                $active_status =  ($reg_degreg=="reg") ? 1 : 0;
              
                $data1 = array('pupilsightPersonID' => $stu_id, active=>$active_status);

                $sql1 = 'UPDATE pupilsightStudentEnrolment SET active=:active WHERE pupilsightPersonID=:pupilsightPersonID';
                $result1 = $connection2->prepare($sql1);
                $result1->execute($data1);

                $sql2 = 'UPDATE pupilsightPerson SET active=:active WHERE pupilsightPersonID=:pupilsightPersonID';
                $result2 = $connection2->prepare($sql2);
                $result2->execute($data1);



                if($reg_degreg=="dereg")
                {

                $data3 = array('pupilsightPersonID'=>$stu_id,'pupilsightStudentEnrolmentID' => $prevdata['pupilsightStudentEnrolmentID'], 'status' => $dereg_status, 'updated_by' => $cuid, 'cdt' => $cdt);
                $sql3 = 'INSERT INTO pupilsight_deregister_students SET pupilsightPersonID=:pupilsightPersonID, pupilsightStudentEnrolmentID=:pupilsightStudentEnrolmentID, status=:status, updated_by=:updated_by, cdt=:cdt';
                $result3 = $connection2->prepare($sql3);
                $result3->execute($data3);

                 }
                 else  if($reg_degreg=="reg")
                 {

                    $data_arr = array('pupilsightStudentEnrolmentID' => $prevdata['pupilsightStudentEnrolmentID']);
                    $sql_data = 'SELECT * FROM pupilsight_deregister_students WHERE pupilsightStudentEnrolmentID=:pupilsightStudentEnrolmentID ';
                    $result_data = $connection2->prepare($sql_data);
                    $result_data->execute($data_arr);
                        if( $result_data->rowCount() > 0)
                        {


                            $data = array('pupilsightStudentEnrolmentID' => $prevdata['pupilsightStudentEnrolmentID']);
                            $sql = 'DELETE FROM pupilsight_deregister_students WHERE pupilsightStudentEnrolmentID=:pupilsightStudentEnrolmentID';
                            $result = $connection2->prepare($sql);
                            $result->execute($data);
                        }
                 }
                $URL .= "&return=success0";
                header("Location: {$URL}");
        } catch (PDOException $e) {
            $URL .= '&return=error2';
            header("Location: {$URL}");
            exit;
        }


        
    }
}
