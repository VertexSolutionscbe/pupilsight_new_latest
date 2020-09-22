<?php
/*
Pupilsight, Flexible & Open School System
*/
include '../../pupilsight.php';
include './moduleFunctions.php';
$testid = $_POST['test_id'];
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

                    $datasub = array('test_master_id' => $testid);
                    $sqlsub = 'SELECT * FROM examinationTestSubjectCategory WHERE test_master_id=:test_master_id';
                    $resultsub = $connection2->prepare($sqlsub);
                    $resultsub->execute($datasub);
                    $subvalues = $resultsub->fetchAll();

                    $copytestid = explode(',',$_POST['tid']);
                    // echo '<pre>';
                    // print_r($copytestid);
                    // echo '</pre>';
                    // die();
                    foreach($copytestid as $ctestId){
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

                        $dataUpdate = array('subject_type' => $subject_type,'assesment_method' => $assesment_method,'assesment_option' => $assesment_option,'max_marks' => $max_marks, 'min_marks' => $min_marks,'gradeSystemId' => $gradeSystemId,'enable_remarks' => $enable_remarks,'enable_schedule' => $enable_schedule, 'start_date' => $start_date,'start_time' => $start_time,'end_date' => $end_date,'end_time' => $end_time, 'id' => $ctestId);
                        
                        $sql = 'UPDATE examinationTest SET subject_type=:subject_type, assesment_method=:assesment_method, assesment_option=:assesment_option, max_marks=:max_marks, min_marks=:min_marks, gradeSystemId=:gradeSystemId, enable_remarks=:enable_remarks, enable_schedule=:enable_schedule, start_date=:start_date, start_time=:start_time, end_date=:end_date, end_time=:end_time WHERE id=:id';
                        $result = $connection2->prepare($sql);
                        $result->execute($dataUpdate);
                        

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
                    
                    $URL .= '&return=success0';
                    header("Location: {$URL}");
                   
            }
}
