<?php
/*
Pupilsight, Flexible & Open School System
*/

include '../../pupilsight.php';
include '../../db.php';

$id = $_GET['id'];
$schedule_id = $_POST['schedule_id'];
$URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_POST['address']).'/transport_fee_assign_manage.php&id='.$schedule_id;

if (isActionAccessible($guid, $connection2, '/modules/Transport/transport_fee_assign_manage_edit.php') == false) {
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
            $sql = 'SELECT * FROM trans_schedule_assign_class WHERE id=:id';
            $result = $connection2->prepare($sql);
            $result->execute($data);
        } catch (PDOException $e) {
            $URL .= '&return=error2';
            header("Location: {$URL}");
            exit();
        }

        if ($result->rowCount() != 1) {
            $URL .= '&return=error4';
            header("Location: {$URL}");
        } else {
            //Validate Inputs
            $program = $_POST['pupilsightProgramID'];
            $class = $_POST['pupilsightYearGroupID'];
            //$section = $_POST['pupilsightRollGroupID'];
            

            if ($schedule_id == ''  or $program == '' or $class == '' ) {
                $URL .= '&return=error3';
                header("Location: {$URL}");
            } else {
                //Check unique inputs for uniquness
                try {
                    $data = array('schedule_id'=>$schedule_id,'pupilsightProgramID' => $program, 'pupilsightYearGroupID' => $class, 'id' => $id);
                    $sql = 'SELECT * FROM trans_schedule_assign_class WHERE (schedule_id=:schedule_id AND pupilsightProgramID=:pupilsightProgramID AND pupilsightYearGroupID=:pupilsightYearGroupID) AND NOT id=:id';
                    $result = $connection2->prepare($sql);
                    $result->execute($data);
                } catch (PDOException $e) {
                    $URL .= '&return=error5';
                    header("Location: {$URL}");
                    exit();
                }

                if ($result->rowCount() > 0) {
                    $URL .= '&return=error3';
                    header("Location: {$URL}");
                } else {
                    //Write to database
                    try {
                        $datas = array( 'pupilsightYearGroupID' => $class);
                        $sqls = 'SELECT GROUP_CONCAT(pupilsightPersonID) AS stuid FROM pupilsightStudentEnrolment WHERE pupilsightYearGroupID=:pupilsightYearGroupID ';
                        $results = $connection2->prepare($sqls);
                        $results->execute($datas);
                        $values = $results->fetch();
                        $studentIds = explode(',', $values['stuid']);

                        $data = array('schedule_id'=>$schedule_id,'pupilsightProgramID' => $program, 'pupilsightYearGroupID' => $class, 'id' => $id);
                        $sql = 'UPDATE trans_schedule_assign_class SET schedule_id=:schedule_id,pupilsightProgramID=:pupilsightProgramID, pupilsightYearGroupID=:pupilsightYearGroupID WHERE id=:id';
                        $result = $connection2->prepare($sql);
                        $result->execute($data);

                        $data1 = array('schedule_id' => $schedule_id);
                        $sql1 = 'DELETE FROM trans_schedule_assign_student WHERE schedule_id=:schedule_id';
                        $result1 = $connection2->prepare($sql1);
                        $result1->execute($data1);

                        if(!empty($studentIds)){
                            
                            $sqls = "INSERT INTO trans_schedule_assign_student (pupilsightPersonID,schedule_id) VALUES "; 
                            foreach($studentIds as $key=>$value){
                                $sqls .= '('.$value.','.$schedule_id.'),';
                            }  
                            $sqls = rtrim($sqls, ", ");
                            $conn->query($sqls);
                        }

                    } catch (PDOException $e) {
                        $URL .= '&return=error6';
                        header("Location: {$URL}");
                        exit();
                    }

                    $URL .= '&return=success0';
                    header("Location: {$URL}");
                }
            }
        }
    }
}
