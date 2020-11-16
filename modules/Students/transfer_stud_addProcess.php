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

if (isActionAccessible($guid, $connection2, '/modules/Students/transfer_student_view.php') != false) {
    $URL .= '&return=error0';
    header("Location: {$URL}");
} else {
    //Proceed!
    //Validate Inputs
    //$fn_fee_structure_id = $_POST['fn_fee_structure_id'];

    $old_academic_year = $_POST['old_pupilsightSchoolYearID'];
    $academic_year = $_POST['pupilsightSchoolYearID'];
    $pupilsightProgramID = $_POST['pupilsightProgramID'];
    $remarks = $_POST['remarks'];
    $pupilsightYearGroupID = $_POST['pupilsightYearGroupID'];
    $pupilsightRollGroupID = $_POST['pupilsightRollGroupID'];
    $cdt = date('Y-m-d H:i:s');
    $cuid = $_SESSION[$guid]['pupilsightPersonID'];          

     $stu_id =  $_POST['stu_id'];
  
   $status  = '1';
    
    if ($stu_id == '') {
        $URL .= '&return=error1';
        header("Location: {$URL}");
    } else {
        //Check unique inputs for uniquness

        try {
            $data = array('pupilsightPersonID' => $stu_id, 'pupilsightSchoolYearID' => $old_academic_year);
            $sql = 'SELECT * FROM pupilsightStudentEnrolment WHERE pupilsightPersonID=:pupilsightPersonID AND pupilsightSchoolYearID=:pupilsightSchoolYearID';
            $result = $connection2->prepare($sql);
            $result->execute($data);
            
            $prevdata=  $result->fetch();
            
            $data1 = array('pupilsightPersonID' => $stu_id, 'pupilsightSchoolYearID' => $academic_year, 'pupilsightProgramID' => $pupilsightProgramID,'pupilsightYearGroupID' => $pupilsightYearGroupID, 'pupilsightRollGroupID' => $pupilsightRollGroupID);
            
            $sql1 = 'UPDATE pupilsightStudentEnrolment SET pupilsightSchoolYearID=:pupilsightSchoolYearID, pupilsightProgramID=:pupilsightProgramID, pupilsightYearGroupID=:pupilsightYearGroupID, pupilsightRollGroupID=:pupilsightRollGroupID WHERE pupilsightPersonID=:pupilsightPersonID';
            $result1 = $connection2->prepare($sql1);
            $result1->execute($data1);


            $data2 = array('pupilsightPersonID'=>$stu_id,'pupilsightStudentEnrolmentID' => $prevdata['pupilsightStudentEnrolmentID'], 'remarks' => $remarks, 'updated_by' => $cuid, 'cdt' => $cdt);
            print_r($data2);
            $sql2 = 'INSERT INTO pupilsightstudent_transfer SET pupilsightPersonID=:pupilsightPersonID, pupilsightStudentEnrolmentID=:pupilsightStudentEnrolmentID, remarks=:remarks, updated_by=:updated_by, cdt=:cdt';
            $result2 = $connection2->prepare($sql2);
            $result2->execute($data2);
            $URL .= "&return=success0";
            header("Location: {$URL}");
            //die();
            
        } catch (PDOException $e) {
            echo $e;
            $URL .= '&return=error2';
            header("Location: {$URL}");
            exit;
        }


        
    }
}
