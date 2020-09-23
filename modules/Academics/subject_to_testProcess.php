<?php
/*
Pupilsight, Flexible & Open School System
*/

include '../../pupilsight.php';
$test_id = $_POST['test_id'];

$URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/Academics/manage_edit_test.php';

if (isActionAccessible($guid, $connection2, '/modules/Academics/subject_to_test.php') != false) {
    $URL .= '&return=error0';
    header("Location: {$URL}");
} else {
    // echo '<pre>';
    // print_r($_POST);
    // echo '</pre>';
    // die();
    //Proceed!
    $test_id = $_POST['test_id'];
    $subID = $_POST['pupilsightDepartmentID'];
    
    //Validate Inputs
    if ($test_id == '' or $subID == '') {
        $URL .= '&return=error1';
        header("Location: {$URL}");
    } else {
        try {
            $data2 = array('test_id' => $test_id);
            $sql2 = 'DELETE FROM examinationSubjectToTest WHERE test_id=:test_id';
            $result2 = $connection2->prepare($sql2);
            $result2->execute($data2);

            $data3 = array('test_id' => $test_id);
            $sql3 = 'DELETE FROM examinationSubjectToTestSkillConfigure WHERE test_id=:test_id';
            $result3 = $connection2->prepare($sql3);
            $result3->execute($data3);

            foreach($subID as $key => $sid){
                $pupilsightDepartmentID = $sid;
                $skill_id = $_POST['skill_id'][$key];
                
                if($skill_id == 'm'){
                    $skill_id = '';
                } else {
                    $skill_id = $skill_id;
                }

                $skill_configure = $_POST['skill_configure'][$key];
                $skill_configure_form = $_POST['skill_configure_form'][$key];

                if(!empty($skill_configure_form)){
                    parse_str($skill_configure_form, $configureArray);
                    $skill_weightage_formula = $configureArray['weightage_formula'];
                } else {
                    $skill_weightage_formula = '';
                }
                

                if(!empty($_POST['is_tested'][$key])){
                    $is_tested = $_POST['is_tested'][$key];
                } else {
                    $is_tested = '';
                }
                
                $assesment_method = $_POST['assesment_method'][$key];
                $assesment_option = $_POST['assesment_option'][$key];
                $max_marks = $_POST['max_marks'][$key];
                $min_marks = $_POST['min_marks'][$key];
                if(!empty($_POST['enable_remarks'][$key])){
                    $enable_remarks = $_POST['enable_remarks'][$key];
                } else {
                    $enable_remarks = '';
                }
                $gradeSystemId = $_POST['gradeSystemId'][$key];
                $edate = str_replace('/', '-', $_POST['exam_date'][$key]);
                $exam_date = date('Y-m-d', strtotime($edate));
                $exam_start_time = $_POST['exam_start_time'][$key];
                $exam_end_time = $_POST['exam_end_time'][$key];
                $room_id = $_POST['room_id'][$key];
                $invigilator_id = $_POST['invigilator_id'][$key];
                if(!empty($_POST['aat'][$key])){
                    $aat = $_POST['aat'][$key];
                } else {
                    $aat = '';
                }
                $aat_calcutaion_by = $_POST['aat_calcutaion_by'][$key];

               
            
                $data = array('test_id' => $test_id, 'pupilsightDepartmentID' => $pupilsightDepartmentID,'skill_id' => $skill_id,'is_tested' => $is_tested, 'assesment_method' => $assesment_method, 'assesment_option' => $assesment_option, 'max_marks' => $max_marks, 'min_marks' => $min_marks, 'enable_remarks' => $enable_remarks, 'gradeSystemId' => $gradeSystemId,'exam_date' => $exam_date, 'exam_start_time' => $exam_start_time, 'exam_end_time' => $exam_end_time, 'room_id' => $room_id, 'invigilator_id' => $invigilator_id, 'aat' => $aat, 'aat_calcutaion_by' => $aat_calcutaion_by, 'skill_configure' => $skill_configure, 'skill_weightage_formula' => $skill_weightage_formula);

            
                $sql = 'INSERT INTO examinationSubjectToTest SET test_id=:test_id, pupilsightDepartmentID=:pupilsightDepartmentID, skill_id=:skill_id, is_tested=:is_tested, assesment_method=:assesment_method, assesment_option=:assesment_option, max_marks=:max_marks, min_marks=:min_marks, enable_remarks=:enable_remarks, gradeSystemId=:gradeSystemId, exam_date=:exam_date, exam_start_time=:exam_start_time, exam_end_time=:exam_end_time, room_id=:room_id, invigilator_id=:invigilator_id, aat=:aat, aat_calcutaion_by=:aat_calcutaion_by, skill_configure=:skill_configure, skill_weightage_formula=:skill_weightage_formula';
                $result = $connection2->prepare($sql);
                $result->execute($data);
                $examinationSubjectToTestID = $connection2->lastInsertID();
                if(!empty($configureArray)){
                    // echo '<pre>';
                    // print_r($configureArray);
                    // echo '</pre>';
                    foreach($configureArray as $sklconf){
                        foreach($sklconf as $sk => $ca){
                            $skill_id = $sk;
                            $skill_weightage = $ca;
                            
                            $datask = array('test_id' => $test_id, 'examinationSubjectToTestID' => $examinationSubjectToTestID, 'skill_id' => $skill_id,'skill_weightage' => $skill_weightage);

                            $sqlsk = 'INSERT INTO examinationSubjectToTestSkillConfigure SET test_id=:test_id, examinationSubjectToTestID=:examinationSubjectToTestID, skill_id=:skill_id, skill_weightage=:skill_weightage';
                            $resultsk = $connection2->prepare($sqlsk);
                            $resultsk->execute($datask);
                        }
                    }
                    unset($configureArray);
                }
            }
    
        } catch (PDOException $e) {
           $URL .= '&return=error2';
           header("Location: {$URL}");
            exit();
        }
        
        $URL .= "&return=success0";
        header("Location: {$URL}");
    }
}
