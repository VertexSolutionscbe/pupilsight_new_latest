<?php
/*
Pupilsight, Flexible & Open School System
*/
include '../../pupilsight.php';
include './moduleFunctions.php';
$testid = $_POST['tid'];
$URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_POST['address'])."/manage_edit_test.php";

if (isActionAccessible($guid, $connection2, '/modules/Academics/copy_test_to_sections.php') == false) {
    $URL .= '&return=error0';
    header("Location: {$URL}");
} else {
    
            //Proceed!
            // echo '<pre>';
            // print_r($_POST);
            // echo '</pre>';
            // die();
          
            if ($testid == '') {
                $URL .= '&return=error1';
                header("Location: {$URL}");
            } else {
                   
                    $data = array('id' => $testid);
                    $sql = 'SELECT * FROM examinationTest WHERE id=:id';
                    $result = $connection2->prepare($sql);
                    $result->execute($data);
                
                    $values = $result->fetch();

                    $sqld = 'SELECT * FROM examinationTestAssignClass WHERE test_id = '.$testid.' ';
                    $resultd = $connection2->query($sqld);
                    $reData = $resultd->fetch();

                    $pid = $reData['pupilsightProgramID'];
                    $cid = $reData['pupilsightYearGroupID'];
                    $sid = $reData['pupilsightRollGroupID'];
                    $URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_POST['address'])."/manage_edit_test.php&pid=".$pid."&cid=".$cid."&sid=".$sid."";
                    //header("Location: {$URL}");
                    //die();

                    $testMasterId = $_POST['test_master_id'];
                    if(!empty($testMasterId)){
                        foreach($testMasterId as $tmId){

                            $datasub = array('test_master_id' => $testid);
                            $sqlsub = 'SELECT * FROM examinationTestSubjectCategory WHERE test_master_id=:test_master_id';
                            $resultsub = $connection2->prepare($sqlsub);
                            $resultsub->execute($datasub);
                            $subvalues = $resultsub->fetchAll();

                            //$testMasterId = $values['test_master_id'];

                            // $sqlterm = 'SELECT id FROM examinationTest WHERE test_master_id = '.$tmId.' AND id != '.$testid.' ';
                            // $resultterm = $connection2->query($sqlterm);
                            // $copytestid = $resultterm->fetchAll();

                            $pupilsightSchoolYearID = $_POST['pupilsightSchoolYearID'];
                            $pupilsightProgramID = $_POST['pupilsightProgramID'];
                            $classId = implode(',', $_POST['pupilsightYearGroupID']);

                            $sqlterm = 'SELECT test_id FROM examinationTestAssignClass WHERE test_master_id = '.$tmId.' AND pupilsightSchoolYearID = '.$pupilsightSchoolYearID.' AND pupilsightProgramID = '.$pupilsightProgramID.' AND pupilsightYearGroupID IN ('.$classId.') AND id != '.$testid.' ';
                            $resultterm = $connection2->query($sqlterm);
                            $copytestid = $resultterm->fetchAll();

                            $sqlsub = 'SELECT * FROM examinationSubjectToTest WHERE test_id = '.$testid.' ';
                            $resultsub = $connection2->query($sqlsub);
                            $subTestData = $resultsub->fetchAll();
                            // print_r($testdata);
                            // die();


                        // $copytestid = explode(',',$_POST['tid']);

                            // echo '<pre>';
                            // print_r($copytestid);
                            // echo '</pre>';
                            // die();
                            foreach($copytestid as $ctestss){
                                $ctestId = $ctestss['test_id'];
                                $subject_type = $values['subject_type'];
                                $assesment_method = $values['assesment_method'];
                                $assesment_option = $values['assesment_option'] ;
                                $max_marks = $values['max_marks'];
                                $min_marks = $values['min_marks'];
                                $gradeSystemId = $values['gradeSystemId'];
                                $enable_remarks = $values['enable_remarks'] ;
                                $enable_schedule = $values['enable_schedule'];
                                $sd = explode('/', $values['start_date']);
                                $start_date  = date('Y-m-d', strtotime(implode('-', array_reverse($sd))));
                                $start_time = $values['start_time'];
                                $ed = explode('/', $values['end_date']);
                                $end_date  = date('Y-m-d', strtotime(implode('-', array_reverse($ed))));
                                $end_time = $values['end_time'];

                                $report_template_id = $values['report_template_id'];
                                $pupilsightSchoolYearTermID = $values['pupilsightSchoolYearTermID'];
                                

                                $dataUpdate = array('subject_type' => $subject_type,'assesment_method' => $assesment_method,'assesment_option' => $assesment_option,'max_marks' => $max_marks, 'min_marks' => $min_marks,'gradeSystemId' => $gradeSystemId,'enable_remarks' => $enable_remarks,'enable_schedule' => $enable_schedule, 'start_date' => $start_date,'start_time' => $start_time,'end_date' => $end_date,'end_time' => $end_time,'report_template_id' => $report_template_id,'pupilsightSchoolYearTermID' => $pupilsightSchoolYearTermID, 'id' => $ctestId);
                                
                                $sql = 'UPDATE examinationTest SET subject_type=:subject_type, assesment_method=:assesment_method, assesment_option=:assesment_option, max_marks=:max_marks, min_marks=:min_marks, gradeSystemId=:gradeSystemId, enable_remarks=:enable_remarks, enable_schedule=:enable_schedule, start_date=:start_date, start_time=:start_time, end_date=:end_date, end_time=:end_time , report_template_id=:report_template_id, pupilsightSchoolYearTermID=:pupilsightSchoolYearTermID WHERE id=:id';
                                $result = $connection2->prepare($sql);
                                $result->execute($dataUpdate);

                                if(!empty($subTestData)){
                                    $datadel = array('test_id' => $ctestId);
                                    $sqldel = 'DELETE FROM examinationSubjectToTest WHERE test_id=:test_id';
                                    $resultdel = $connection2->prepare($sqldel);
                                    $resultdel->execute($datadel);

                                    foreach($subTestData as $subt){
                                        $test_id = $ctestId;
                                        $pupilsightDepartmentID = $subt['pupilsightDepartmentID'];
                                        $skill_id = $subt['skill_id'];
                                        $skill_configure = $subt['skill_configure'];
                                        $skill_weightage_formula = $subt['skill_weightage_formula'];
                                        $is_tested = $subt['is_tested'];
                                        $assesment_method = $subt['assesment_method'];
                                        $assesment_option = $subt['assesment_option'];
                                        $max_marks = $subt['max_marks'];
                                        $min_marks = $subt['min_marks'];
                                        $enable_remarks = $subt['enable_remarks'];
                                        $gradeSystemId = $subt['gradeSystemId'];
                                        $exam_date = $subt['exam_date'];
                                        $exam_start_time = $subt['exam_start_time'];
                                        $exam_end_time = $subt['exam_end_time'];
                                        $room_id = $subt['room_id'];
                                        $invigilator_id = $subt['invigilator_id'];
                                        $aat = $subt['aat'];
                                        $aat_calcutaion_by = $subt['aat_calcutaion_by'];

                                        $dataInsert = array('test_id' => $ctestId,'pupilsightDepartmentID' => $pupilsightDepartmentID,'skill_id' => $skill_id,'skill_configure' => $skill_configure,'skill_weightage_formula' => $skill_weightage_formula,'is_tested' => $is_tested, 'assesment_method' => $assesment_method,'assesment_option' => $assesment_option, 'max_marks' => $max_marks,'min_marks' => $min_marks,'enable_remarks' => $enable_remarks,'gradeSystemId' => $gradeSystemId,'exam_date' => $exam_date,'exam_start_time' => $exam_start_time, 'exam_end_time' => $exam_end_time,'room_id' => $room_id,'invigilator_id' => $invigilator_id, 'aat' => $aat,'aat_calcutaion_by' => $aat_calcutaion_by);

                                        $sqlInsert = 'INSERT INTO examinationSubjectToTest SET  test_id=:test_id, pupilsightDepartmentID=:pupilsightDepartmentID, skill_id=:skill_id,skill_configure=:skill_configure, skill_weightage_formula=:skill_weightage_formula, is_tested=:is_tested, assesment_method=:assesment_method, assesment_option=:assesment_option,max_marks=:max_marks, min_marks=:min_marks, enable_remarks=:enable_remarks,gradeSystemId=:gradeSystemId, exam_date=:exam_date, exam_start_time=:exam_start_time, exam_end_time=:exam_end_time, room_id=:room_id, invigilator_id=:invigilator_id, aat=:aat, aat_calcutaion_by=:aat_calcutaion_by';
                                        $resultInsert = $connection2->prepare($sqlInsert);
                                        $resultInsert->execute($dataInsert);
                                    }
                                }
                                

                                if(!empty($subvalues)){
                                    $datadel = array('test_id' => $ctestId);
                                    $sqldel = 'DELETE FROM examinationTestSubjectCategory WHERE test_id=:test_id';
                                    $resultdel = $connection2->prepare($sqldel);
                                    $resultdel->execute($datadel);

                                    foreach($subvalues as $sbu){
                                        $subject_type_id = $sbu['subject_type_id'];
                                        $stype = $sbu['subject_type'];
                                        $assesment_method = $sbu['assesment_method'];
                                        $assesment_option = $sbu['assesment_option'];
                                        $max_marks = $sbu['max_marks'];
                                        $min_marks = $sbu['min_marks'];
                                        $gradeSystemId = $sbu['gradeSystemId'];

                                        $dataInsert = array('test_id' => $ctestId,'subject_type' => $stype,'subject_type_id' => $subject_type_id,'assesment_method' => $assesment_method,'assesment_option' => $assesment_option,'max_marks' => $max_marks, 'min_marks' => $min_marks,'gradeSystemId' => $gradeSystemId);

                                        $sqlInsert = 'INSERT INTO examinationTestSubjectCategory SET  test_id=:test_id, subject_type=:subject_type, subject_type_id=:subject_type_id,assesment_method=:assesment_method, assesment_option=:assesment_option, max_marks=:max_marks, min_marks=:min_marks, gradeSystemId=:gradeSystemId';
                                        $resultInsert = $connection2->prepare($sqlInsert);
                                        $resultInsert->execute($dataInsert);
                                    }
                                }


                            }
                        }
                    }
                   // die();
                    $URL .= '&return=success0';
                    header("Location: {$URL}");
                   
            }
}
