<?php
/*
Pupilsight, Flexible & Open School System
*/

include '../../pupilsight.php';
 //echo '<pre>';
 //print_r($_POST);
 //echo '</pre>';die();
$ids = $_POST['id_sub'];
$pid= $_POST['pid'];

$URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_POST['address']).'/remove_assigned_elect_subject_student_process.php&id=';
$URLDelete = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_POST['address']).'/student_view.php';

if (isActionAccessible($guid, $connection2, '/modules/Students/remove_assigned_elect_subject_from_student.php') != false) {
    $URL .= '&return=error0';
    header("Location: {$URL}");
} else {
    //Proceed!
    //Check if school year specified
    $cnt =count($ids);
    if ($cnt == 0) {
        $URL .= '&return=error1';
        header("Location: {$URL}");
    } else {
   
            //Write to database
            try {
                foreach($ids as $dep){
                    $data = array('pupilsightdepartmentID' => $dep,'pupilsightPersonID'=>$pid);
                   

                   $sql = 'DELETE FROM assign_elective_subjects_tostudents WHERE pupilsightdepartmentID=:pupilsightdepartmentID AND pupilsightPersonID=:pupilsightPersonID';
                  
                   $result = $connection2->prepare($sql);
                   $result->execute($data);
                    }


/*
                $data = array('id' => $id);
                $sql = 'DELETE FROM assign_elective_subjects_tostudents WHERE id=:id';
                $result = $connection2->prepare($sql);
                $result->execute($data);
*/
               
            } catch (PDOException $e) {
                $URLDelete .= '&return=error2';
                header("Location: {$URLDelete}");
                exit();
            }
        
            $URLDelete = $URLDelete.'&return=success0';
            header("Location: {$URLDelete}");
        
    }
}
