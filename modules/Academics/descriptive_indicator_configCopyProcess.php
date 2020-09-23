<?php
/*
Pupilsight, Flexible & Open School System
*/
include '../../pupilsight.php';
include './moduleFunctions.php';

$URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_POST['address'])."/descriptive_indicator_config.php";

if (isActionAccessible($guid, $connection2, '/modules/Academics/descriptive_indicator_config.php') == false) {
    $URL .= '&return=error0';
    header("Location: {$URL}");
} else {
    $pupilsightProgramID = $_POST['pupilsightProgramID'];
    $class = $_POST['classes'];
    $classId =$class;
    $pupilsightSchoolYearID =$_POST['pupilsightSchoolYearID'];
    $sub_id =  $_POST['sub_id'];
    
           // Proceed!
            //   echo '<pre>';
            // print_r($_POST);
            // echo '</pre>';
            // die();
          
            if ($sub_id == '' || $class=='' ) {
                $URL .= '&return=error1';
                header("Location: {$URL}");
                exit();


            } else {

                try {
                    
                    foreach ($classId as $cls_Id) {
                    $data = array('pupilsightDepartmentID' => $sub_id,'pupilsightSchoolYearID'=>$pupilsightSchoolYearID,'pupilsightProgramID'=>$pupilsightProgramID,'pupilsightYearGroupID'=>$cls_Id);
                    $sql = 'SELECT * FROM subjectToClassCurriculum WHERE pupilsightSchoolYearID=:pupilsightSchoolYearID AND pupilsightProgramID=:pupilsightProgramID AND pupilsightYearGroupID=:pupilsightYearGroupID AND pupilsightDepartmentID=:pupilsightDepartmentID';
                    $result = $connection2->prepare($sql);
                    $result->execute($data);
                    }
                   
                } catch (PDOException $e) {
                    $URL .= '&return=error1';
                    header("Location: {$URL}");
                    exit();
                }
        //print_r($result->rowCount());die();
               
                if ($result->rowCount() == 0 ) {
                    $URL .= '&return=error1';
                    header("Location: {$URL}");
                    exit();
                 } else {
                        $copy_cls = $_POST['copyCls'];
                        $copy_prg =  $_POST['copyPrg'];
                        $datac = array('pupilsightDepartmentID' => $sub_id,'pupilsightYearGroupID'=>$copy_cls ,'pupilsightProgramID'=>$copy_prg);
                        $sqlc='SELECT * FROM  subject_skill_descriptive_indicator_config WHERE pupilsightDepartmentID =:pupilsightDepartmentID AND pupilsightYearGroupID=:pupilsightYearGroupID AND  pupilsightProgramID=:pupilsightProgramID'; 

                        $resultc = $connection2->prepare($sqlc);
                        $resultc->execute($datac);   
                        $values = $resultc->fetchAll();  
                 
                        
                   
                    if (empty($values)) {
                        $URL .= '&return=error1';
                        header("Location: {$URL}");
                    } else {
                        //Write to database

                        try {
                            foreach($classId as $clsID){        
                               
                            
        foreach($values as $val){         
                

        $datar = array('remark_id' =>  $val['remark_id'],'pupilsightDepartmentID'=> $val['pupilsightDepartmentID'],'pupilsightProgramID'=>$pupilsightProgramID,'pupilsightYearGroupID'=>$clsID);                               
        $sqlr = 'DELETE FROM subject_skill_descriptive_indicator_config WHERE remark_id=:remark_id AND pupilsightDepartmentID=:pupilsightDepartmentID AND pupilsightProgramID =:pupilsightProgramID AND pupilsightYearGroupID=:pupilsightYearGroupID';
        $resultr = $connection2->prepare($sqlr);
        $resultr->execute($datar);
                        
        $datacr = array('remark_id' => $val['remark_id'],'remark_description'=>$val['remark_description'],'pupilsightDepartmentID'=>$val['pupilsightDepartmentID'],'pupilsightYearGroupID'=>$clsID,'di_mode'=>$val['di_mode'],'grade'=>$val['grade'],'grade_id'=>$val['grade_id'],'pupilsightProgramID'=>$pupilsightProgramID);
                                // print_r($datacr);die();
                $sqlcr = "INSERT INTO subject_skill_descriptive_indicator_config SET remark_id	=:remark_id	,remark_description=:remark_description,pupilsightYearGroupID=:pupilsightYearGroupID,pupilsightDepartmentID=:pupilsightDepartmentID,di_mode=:di_mode,grade=:grade,grade_id=:grade_id,pupilsightProgramID=:pupilsightProgramID";
                $resultcr = $connection2->prepare($sqlcr);
                
                $resultcr->execute($datacr);
               // print_r($result);die();

                }
            } 
                        }catch (PDOException $e) {
                            $URL .= '&return=error2';
                            header("Location: {$URL}");
                        }
           
                        // $URL .= "&return=success0&editID=$AI";
                      
                        $URL .= '&return=success0';
                        header("Location: {$URL}");
                   
                    }
                }
            }
        
    
}
