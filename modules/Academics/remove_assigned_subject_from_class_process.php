<?php
/*
Pupilsight, Flexible & Open School System
*/

include '../../pupilsight.php';
 /*echo '<pre>';
 print_r($_SESSION);
 echo '</pre>';die();*/
//$id = $_POST['id'];
$ids = $_POST['id_sub'];
$pupilsightProgramID = $_POST['pupilsightProgramID'];
$pupilsightYearGroupID=$_POST['pupilsightYearGroupID'];
$URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_SESSION[$guid]['address']).'/remove_assigned_subject_from_class_process.php';
$URLDelete = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_SESSION[$guid]['address']).'/assign_subjects_class_add.php&search='.'&pupilsightSchoolYearID='.$_SESSION[$guid]['pupilsightSchoolYearID'];

if (isActionAccessible($guid, $connection2, '/modules/Academics/remove_assined_subj_fromclass.php') != false) {
    $URL .= '&return=error0';
    header("Location: {$URL}");
} else {
    //Proceed!
    $cnt =count($ids);
    if ($cnt == 0) {
        $URL .= '&return=error1';
        header("Location: {$URL}");
    } else {
       
        try {
            foreach($ids as $dep){
               $data = array('pupilsightProgramID'=> $pupilsightProgramID,'pupilsightYearGroupID'=> $pupilsightYearGroupID,'pupilsightDepartmentID' => $dep);
               $sql = 'DELETE FROM assign_core_subjects_toclass WHERE pupilsightProgramID=:pupilsightProgramID AND pupilsightYearGroupID=:pupilsightYearGroupID AND pupilsightDepartmentID=:pupilsightDepartmentID';
              
               $result = $connection2->prepare($sql);
               $result->execute($data);
                }
           
        } catch (PDOException $e) {
            $URLDelete .= '&return=error2';
            header("Location: {$URLDelete}");
            exit();
        }
    
        $URLDelete = $URLDelete.'&return=success0';
        header("Location: {$URLDelete}");

    }
}
