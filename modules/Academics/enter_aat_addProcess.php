<?php
/*
Pupilsight, Flexible & Open School System
*/

include '../../pupilsight.php';

$URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/Academics/manage_enter_aat.php';
if (isActionAccessible($guid, $connection2, '/modules/Academics/manage_enter_aat.php') == false) {
    $URL .= '&return=error0';
    header("Location: {$URL}");
} else {
  /*   echo '<pre>';
     print_r($_POST);
     echo '</pre>';
     die();*/
    //Proceed!
    $pupilsightYearGroupID =  $_POST['pupilsightYearGroupID'];
    $pupilsightRollGroupID =  $_POST['pupilsightRollGroupID'];
    $test_id = $_POST['test_id'];
    $skill_id = $_POST['skill_id'];
    $pupilsightDepartmentID = $_POST['pupilsightDepartmentID'];
    $stud_id = $_POST['student_id'];
    $mark_obtained = $_POST['marks_obtain'];//array for testwise
    $entry_based_on =  $_POST['entry_based_on'];

    
    //Validate Inputs
    if ($pupilsightDepartmentID == '' Or $mark_obtained == '') {
        $URL .= '&return=error1';
        header("Location: {$URL}");
    } else {
        try {
                foreach($skill_id as $skid){   //this can be either skill id or subject id
                    foreach($stud_id as $sd => $sid){
                    $student_id = $sid;                              
                    
                    if(isset( $_POST['marks_obtain'][$skid][$sid])){
                        $mark_obtn  = $_POST['marks_obtain'][$skid][$sid];
                    } else {
                        $mark_obtn = 0;
                    }  
                    $locksts =   $_POST['lock_status'][$skid][$sid];
                    $entrytype =  $_POST['entrytype'][$skid][$sid];
                    $prev_mark =  $_POST['prev_mark'][$skid][$sid];

                  //  $mark_estimated = ($prev_mark + $mark_obtn)/2;
                                          
                        $entry_type =($entrytype !="")? $entrytype: 1;
                        if($locksts!=1)
                        {
                            $skill_id = ($entry_based_on=='skill')? $skid : 0;
                           
                        $data1 = array('pupilsightPersonIDTaker' => $_SESSION[$guid]['pupilsightPersonID'],'test_id' => $test_id, 'pupilsightYearGroupID' => $pupilsightYearGroupID,'pupilsightRollGroupID' => $pupilsightRollGroupID,'pupilsightDepartmentID' => $pupilsightDepartmentID,'pupilsightPersonID' => $student_id,'skill_id' => $skill_id,'entrytype' => $entry_type);                    
                      /*  echo "<pre>";
                        print_r($data1);*/
                      
                          $sql1 = 'DELETE FROM examinationMarksEntrybySubject WHERE test_id=:test_id  AND pupilsightYearGroupID=:pupilsightYearGroupID AND pupilsightRollGroupID=:pupilsightRollGroupID AND pupilsightDepartmentID=:pupilsightDepartmentID AND pupilsightPersonID=:pupilsightPersonID AND  skill_id=:skill_id AND entrytype=:entrytype AND pupilsightPersonIDTaker=:pupilsightPersonIDTaker';
                        $result1 = $connection2->prepare($sql1);
                        $result1->execute($data1);
        
                        // `examinationMarksEntrybySubject` ,`test_id`,`pupilsightYearGroupID`,`pupilsightRollGroupID`,`pupilsightDepartmentID`,`pupilsightPersonID`,`skill_id`,`marks_obtained`,`gradeId`,`remarks`,                
                        $data = array('pupilsightPersonIDTaker' => $_SESSION[$guid]['pupilsightPersonID'],'test_id' => $test_id, 'pupilsightYearGroupID' => $pupilsightYearGroupID,'pupilsightRollGroupID' => $pupilsightRollGroupID,'pupilsightDepartmentID' => $pupilsightDepartmentID,'pupilsightPersonID' => $student_id,'skill_id' => $skill_id,'marks_obtained' => $mark_obtn, 'status'=>$locksts,'entrytype' => $entry_type);                    
                     /*   echo "<pre>";
                        print_r($data);*/
                        $sql = 'INSERT INTO examinationMarksEntrybySubject SET pupilsightPersonIDTaker=:pupilsightPersonIDTaker,test_id=:test_id, pupilsightYearGroupID=:pupilsightYearGroupID, pupilsightRollGroupID=:pupilsightRollGroupID, pupilsightDepartmentID=:pupilsightDepartmentID, pupilsightPersonID=:pupilsightPersonID, skill_id=:skill_id, marks_obtained=:marks_obtained,status=:status,entrytype=:entrytype';
                        $result = $connection2->prepare($sql);
                        $result->execute($data);
                        }
                    }
            }
    
        } catch (PDOException $e) {
           $URL .= '&return=error2';
           header("Location: {$URL}");
            exit();
        }
      //  die();
        //Last insert ID
        $AI = str_pad($connection2->lastInsertID(), 10, '0', STR_PAD_LEFT);

        $URL .= "&return=success0&editID=$AI";
        header("Location: {$URL}");
    }
}
