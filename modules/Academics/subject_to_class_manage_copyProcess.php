<?php
/*
Pupilsight, Flexible & Open School System
*/

include '../../pupilsight.php';

$subjectToClassId = rtrim($_POST['subjectToClassId'], ',');
$getsql1 = 'SELECT * FROM subjectToClassCurriculum WHERE id IN ('.$subjectToClassId.') GROUP BY pupilsightYearGroupID';
$getresult1 = $connection2->query($getsql1);
$getdata1 = $getresult1->fetch();
$errorArray = array();

                
$URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_POST['address']).'/subject_to_class_manage.php&acaId='.$getdata1['pupilsightSchoolYearID'].'&proId='.$getdata1['pupilsightProgramID'].'&classId='.$getdata1['pupilsightYearGroupID'].'';

if (isActionAccessible($guid, $connection2, '/modules/Academics/subject_to_class_manage_copy.php') == false) {
    $URL .= '&return=error0';
    header("Location: {$URL}");
} else {
    
    //Proceed!
    //$subjectToClassId = $_POST['subjectToClassId'];
    $pupilsightSchoolYearID = $_POST['pupilsightSchoolYearID'];
    $pupilsightProgramID = $_POST['pupilsightProgramID'];
    $pupilsightYearGroupID1 = $_POST['pupilsightYearGroupID'];
    
    //Validate Inputs
    if ($pupilsightYearGroupID1 == '' or $subjectToClassId == '') {
        $URL .= '&return=error1';
        header("Location: {$URL}");
    } else {   
        try {

            $classId = explode(',', $subjectToClassId);
 foreach ($pupilsightYearGroupID1 as $pupilsightYearGroupID) {
                //check availabulity
            $check_sql = 'SELECT count(id) as total FROM assign_core_subjects_toclass WHERE pupilsightProgramID = "'.$pupilsightProgramID.'" AND pupilsightYearGroupID ="'.$pupilsightYearGroupID.'"';
            $chck_res = $connection2->query($check_sql);
            $reData = $chck_res->fetch();
        if(!empty($reData['total'])){
            foreach($classId as $sid){
                $getsql = 'SELECT * FROM subjectToClassCurriculum WHERE id = '.$sid.' ';
                $getresult = $connection2->query($getsql);
                $getdata = $getresult->fetch();
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                  
                $aid = $getdata['pupilsightSchoolYearID'];
                $pid = $getdata['pupilsightProgramID'];
                $cid = $getdata['pupilsightYearGroupID'];

                $pupilsightDepartmentID = $getdata['pupilsightDepartmentID'];
                $academicID = $getdata['pupilsightSchoolYearID'];
                $programID = $getdata['pupilsightProgramID'];
                $subject_code = $getdata['subject_code'];
                $subject_display_name = $getdata['subject_display_name'];
                $subject_type = $getdata['subject_type'];
                $di_mode = $getdata['di_mode'];
                $pos = $getdata['pos'];

                $getsqlskills = 'SELECT * FROM subjectSkillMapping WHERE pupilsightDepartmentID = "'.$pupilsightDepartmentID.'" AND pupilsightSchoolYearID = "'.$academicID.'" AND pupilsightProgramID = "'.$programID.'" AND pupilsightYearGroupID = "'.$cid.'" ';
                $getresultskills = $connection2->query($getsqlskills);
                $getskillsdata = $getresultskills->fetchAll();

                $data1 = array('pupilsightSchoolYearID' => $pupilsightSchoolYearID, 'pupilsightProgramID' => $pupilsightProgramID, 'pupilsightYearGroupID' => $pupilsightYearGroupID, 'pupilsightDepartmentID' => $pupilsightDepartmentID);
                $sql1 = 'DELETE FROM subjectToClassCurriculum WHERE pupilsightSchoolYearID=:pupilsightSchoolYearID AND pupilsightProgramID=:pupilsightProgramID AND pupilsightYearGroupID=:pupilsightYearGroupID AND pupilsightDepartmentID=:pupilsightDepartmentID';
                $result1 = $connection2->prepare($sql1);
                $result1->execute($data1);

                $data = array('pupilsightSchoolYearID' => $pupilsightSchoolYearID, 'pupilsightProgramID' => $pupilsightProgramID,'pupilsightYearGroupID' => $pupilsightYearGroupID, 'pupilsightDepartmentID' => $pupilsightDepartmentID, 'subject_code' => $subject_code, 'subject_display_name' => $subject_display_name, 'subject_type' => $subject_type, 'di_mode' => $di_mode, 'pos' => $pos);
                $sql = 'INSERT INTO subjectToClassCurriculum SET pupilsightSchoolYearID=:pupilsightSchoolYearID, pupilsightProgramID=:pupilsightProgramID, pupilsightYearGroupID=:pupilsightYearGroupID, pupilsightDepartmentID=:pupilsightDepartmentID, subject_code=:subject_code, subject_display_name=:subject_display_name, subject_type=:subject_type, di_mode=:di_mode, pos=:pos';
                $result = $connection2->prepare($sql);
                $result->execute($data);

                if(!empty($getskillsdata)){
                        $data3 = array('pupilsightSchoolYearID' => $pupilsightSchoolYearID, 'pupilsightProgramID' => $pupilsightProgramID, 'pupilsightYearGroupID' => $pupilsightYearGroupID, 'pupilsightDepartmentID' => $pupilsightDepartmentID);
                        $sql3 = 'DELETE FROM subjectSkillMapping WHERE pupilsightSchoolYearID=:pupilsightSchoolYearID AND pupilsightProgramID=:pupilsightProgramID AND pupilsightYearGroupID=:pupilsightYearGroupID AND pupilsightDepartmentID=:pupilsightDepartmentID';
                        $result3 = $connection2->prepare($sql3);
                        $result3->execute($data3);
                    foreach($getskillsdata as $sub){
                        
                        $sid = $sub['skill_id'];
                        $name = $sub['skill_display_name'];
                        $datask = array('pupilsightSchoolYearID' => $pupilsightSchoolYearID, 'pupilsightProgramID' => $pupilsightProgramID,'pupilsightYearGroupID' => $pupilsightYearGroupID, 'pupilsightDepartmentID' => $pupilsightDepartmentID, 'skill_id' => $sid, 'skill_display_name' => $name);
                        $sqlsk = "INSERT INTO subjectSkillMapping SET pupilsightSchoolYearID=:pupilsightSchoolYearID, pupilsightProgramID=:pupilsightProgramID, pupilsightYearGroupID=:pupilsightYearGroupID, pupilsightDepartmentID=:pupilsightDepartmentID, skill_id=:skill_id, skill_display_name=:skill_display_name";
                        $resultsk = $connection2->prepare($sqlsk);
                        $resultsk->execute($datask);
                        
                    }
                }
                
            }
            //else
        } else {
          $errorArray[]=$pupilsightYearGroupID;
        }
    }
    
        } catch (PDOException $e) {
            $URL .= '&return=error2';
            header("Location: {$URL}");
            exit();
        }

        //Last insert ID
        $AI = str_pad($connection2->lastInsertID(), 10, '0', STR_PAD_LEFT);
        $erorrs=implode(',',$errorArray);
          if(!empty($errorArray)){
           $URL.="&m_err=$erorrs";
          }
        $URL .= "&return=success0&editID=$AI";
        header("Location: {$URL}");
    }
}
