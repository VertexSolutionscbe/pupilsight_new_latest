<?php
/*
Pupilsight, Flexible & Open School System
*/
include '../../pupilsight.php';
include './moduleFunctions.php';
$test_master_id = $_POST['test_id'];
$URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_POST['address'])."/manage_edit_test.php";

if (isActionAccessible($guid, $connection2, '/modules/Academics/test_create.php') == false) {
    $URL .= '&return=error0';
    header("Location: {$URL}");
} else {
    
            //Proceed!
          
            if ($test_master_id == '') {
                $URL .= '&return=error1';
                header("Location: {$URL}");
            } else {
                try {
                  
                    $data = array('id' => $test_master_id);
                    $sql = 'SELECT * FROM examinationTestMaster WHERE id=:id';
                   
                    $result = $connection2->prepare($sql);
                    $result->execute($data);
                    
                } catch (PDOException $e) {
                    $URL .= '&return=error2';
                    header("Location: {$URL}");
                    exit();
                }

                if ($result->rowCount() != 1) {
                    $URL .= '&return=error3';
                    header("Location: {$URL}");
                } else {
                    $values = $result->fetch();
                    //Proceed!
                    $name = $_POST['name'];
                    $code = $values['code'];
                    $pupilsightSchoolYearID = $values['pupilsightSchoolYearID'];
                    $subject_type = $_POST['subject_type'];
                    $pupilsightSchoolYearTermID = $_POST['pupilsightSchoolYearTermID'];
                    $assesment_method = $_POST['assesment_method'];
                    $assesment_option = $_POST['assesment_option'] ;
                    $max_marks = $_POST['max_marks'];
                    $min_marks = $_POST['min_marks'];
                    $gradeSystemId = $_POST['gradeSystemId'];
                    if(!empty($_POST['enable_remarks'])){
                        $enable_remarks = $_POST['enable_remarks'];
                    } else {
                        $enable_remarks = '';
                    }
                    if(!empty($_POST['enable_schedule'])){
                        $enable_schedule = $_POST['enable_schedule'];
                    } else {
                        $enable_schedule = '';
                    }
                    $sd = explode('/', $_POST['start_date']);
                    $start_date  = date('Y-m-d', strtotime(implode('-', array_reverse($sd))));
                    $start_time = $_POST['start_time'];
                    $ed = explode('/', $_POST['end_date']);
                    $end_date  = date('Y-m-d', strtotime(implode('-', array_reverse($ed))));
                    $end_time = $_POST['end_time'];

                    $pupilsightMappingID = $_POST['pupilsightMappingID'];
                    
                    if ($name == '') {
                        $URL .= '&return=error1';
                        header("Location: {$URL}");
                    } else {
                        //Write to database

                        try {

                            if(!empty($pupilsightMappingID)){
                                $sqlcls = 'SELECT * FROM pupilsightProgramClassSectionMapping WHERE pupilsightMappingID IN ('.$pupilsightMappingID.') ';
                                $resultcls = $connection2->query($sqlcls);
                                $clsdata = $resultcls->fetchAll();
                                foreach($clsdata as $cls){
                                    $sqlsec = 'SELECT a.*,b.name FROM pupilsightProgramClassSectionMapping AS a LEFT JOIN pupilsightRollGroup AS b ON a.pupilsightRollGroupID = b.pupilsightRollGroupID WHERE a.pupilsightSchoolYearID = '.$pupilsightSchoolYearID.' AND a.pupilsightProgramID = '.$cls['pupilsightProgramID'].' AND  a.pupilsightYearGroupID = '.$cls['pupilsightYearGroupID'].' ';
                                    $resultsec = $connection2->query($sqlsec);
                                    $secdata = $resultsec->fetchAll();
                                    foreach($secdata as $sec){
                                        $pupilsightRollGroupID = $sec['pupilsightRollGroupID'];
                                        $newname = $name.' - '.$sec['name'];
                                        $dataUpdate = array('test_master_id' => $test_master_id,'name' => $newname,'code'=>$code,'pupilsightSchoolYearID'=>$pupilsightSchoolYearID,'pupilsightSchoolYearTermID' => $pupilsightSchoolYearTermID, 'subject_type' => $subject_type,'assesment_method' => $assesment_method,'assesment_option' => $assesment_option,'max_marks' => $max_marks, 'min_marks' => $min_marks,'gradeSystemId' => $gradeSystemId,'enable_remarks' => $enable_remarks,'enable_schedule' => $enable_schedule, 'start_date' => $start_date,'start_time' => $start_time,'end_date' => $end_date,'end_time' => $end_time);
                                        // echo '<pre>';
                                        // print_r($dataUpdate);
                                        // echo '</pre>'; 
                                        $sql = 'INSERT INTO examinationTest SET test_master_id=:test_master_id,name=:name, code=:code, pupilsightSchoolYearID=:pupilsightSchoolYearID,pupilsightSchoolYearTermID=:pupilsightSchoolYearTermID, subject_type=:subject_type, assesment_method=:assesment_method, assesment_option=:assesment_option, max_marks=:max_marks, min_marks=:min_marks, gradeSystemId=:gradeSystemId, enable_remarks=:enable_remarks, enable_schedule=:enable_schedule, start_date=:start_date, start_time=:start_time, end_date=:end_date, end_time=:end_time';
                                        $result = $connection2->prepare($sql);
                                        $result->execute($dataUpdate);
                                        $testId = $connection2->lastInsertID();

                                        $dataUpdate2 = array('test_id' => $testId,'pupilsightSchoolYearID' => $pupilsightSchoolYearID, 'pupilsightProgramID' => $cls['pupilsightProgramID'], 'pupilsightYearGroupID' => $cls['pupilsightYearGroupID'], 'pupilsightRollGroupID' => $pupilsightRollGroupID);

                                        $sqlchk = "SELECT id FROM examinationTestAssignClass WHERE test_id=:test_id AND pupilsightSchoolYearID=:pupilsightSchoolYearID AND pupilsightProgramID=:pupilsightProgramID AND pupilsightYearGroupID=:pupilsightYearGroupID AND pupilsightRollGroupID=:pupilsightRollGroupID";
                                        $resultchk = $connection2->prepare($sqlchk);
                                        $resultchk->execute($dataUpdate2);

                                        if ($resultchk->rowCount() < 1) {
                                            $dataInsert2 = array('test_master_id' => $test_master_id,'test_id' => $testId,'pupilsightSchoolYearID' => $pupilsightSchoolYearID, 'pupilsightProgramID' => $cls['pupilsightProgramID'], 'pupilsightYearGroupID' => $cls['pupilsightYearGroupID'], 'pupilsightRollGroupID' => $pupilsightRollGroupID);

                                            $sqlUpdate = 'INSERT INTO examinationTestAssignClass SET  test_master_id=:test_master_id, test_id=:test_id, pupilsightSchoolYearID=:pupilsightSchoolYearID, pupilsightProgramID=:pupilsightProgramID, pupilsightYearGroupID=:pupilsightYearGroupID, pupilsightRollGroupID=:pupilsightRollGroupID';
                                            $resultUpdate = $connection2->prepare($sqlUpdate);
                                            $resultUpdate->execute($dataInsert2);
                                        }

                                    }
                                }
                            }
                        } catch (PDOException $e) {
                            $URL .= '&return=error5';
                          header("Location: {$URL}");
                          exit();
                        }
                        
                        $URL .= '&return=success0';

                        header("Location: {$URL}");
                   
                    }
                }
            }
        
    
}
