<?php
/*
Pupilsight, Flexible & Open School System
*/

include '../../pupilsight.php';
include '../../db.php';

$schedule_id = $_POST['schedule_id'];
$URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_POST['address']).'/transport_fee_assign_manage.php&id='.$schedule_id;

if (isActionAccessible($guid, $connection2, '/modules/Transport/transport_fee_assign_manage_add.php') == false) {
    $URL .= '&return=error0';
    header("Location: {$URL}");
} else {


    //Proceed!
    //Validate Inputs
    //$schedule_id = $_POST['schedule_id'];
    $program = $_POST['pupilsightProgramID'];
    $class = $_POST['pupilsightYearGroupID'];
    // echo '<pre>';
    // print_r($_POST);
    // echo '</pre>';
    // die(0);
    if ($schedule_id == ''  or $program == '' or $class == '') {
        $URL .= '&return=error1';
        header("Location: {$URL}");
    } else {
        //Check unique inputs for uniquness
        // try {
        //     $data = array('schedule_id'=>$schedule_id,'pupilsightProgramID' => $program, 'pupilsightYearGroupID' => $class);
        //     $sql = 'SELECT * FROM trans_schedule_assign_class WHERE schedule_id=:schedule_id AND pupilsightProgramID=:pupilsightProgramID AND pupilsightYearGroupID=:pupilsightYearGroupID AND pupilsightRollGroupID=:pupilsightRollGroupID';
        //     $result = $connection2->prepare($sql);
        //     $result->execute($data);
        // } catch (PDOException $e) {
        //     $URL .= '&return=error2';
        //     header("Location: {$URL}");
        //     exit();
        // } 

        // if ($result->rowCount() > 0) {
        //     $URL .= '&return=error3';
        //     header("Location: {$URL}");
        // } else {
            //Write to database
            try {
                foreach($class as $cl){
                    $datas = array( 'pupilsightYearGroupID' => $cl);
                    $sqls = 'SELECT GROUP_CONCAT(pupilsightPersonID) AS stuid FROM pupilsightStudentEnrolment WHERE pupilsightYearGroupID=:pupilsightYearGroupID ';
                    $results = $connection2->prepare($sqls);
                    $results->execute($datas);
                    $values = $results->fetch();
                    $studentIds = explode(',', $values['stuid']);
                    
                    if(!empty($studentIds)){
                        $data = array('schedule_id'=>$schedule_id,'pupilsightProgramID' => $program, 'pupilsightYearGroupID' => $cl);
                        $sql = 'INSERT INTO trans_schedule_assign_class SET schedule_id=:schedule_id,pupilsightProgramID=:pupilsightProgramID, pupilsightYearGroupID=:pupilsightYearGroupID';
                        $result = $connection2->prepare($sql);
                        $result->execute($data);

                        $sql = "INSERT INTO trans_schedule_assign_student (pupilsightPersonID,schedule_id) VALUES "; 
                        foreach($studentIds as $key=>$value){
                            $sql .= '('.$value.','.$schedule_id.'),';
                        }  
                        $sql = rtrim($sql, ", ");
                        $conn->query($sql);
                    }
                }
                
            } catch (PDOException $e) {
                $URL .= '&return=error2';
                header("Location: {$URL}");
                exit();
            }

            //Last insert ID
            $AI = str_pad($connection2->lastInsertID(), 3, '0', STR_PAD_LEFT);

            $URL .= "&return=success0&editID=$AI";
            header("Location: {$URL}");
        // }
    }
}
