<?php
/*
Pupilsight, Flexible & Open School System
*/
include '../../pupilsight.php';
include './moduleFunctions.php';
$testid = $_POST['test_id'];
$errorArray = array();
$URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_POST['address'])."/test_home.php";

if (isActionAccessible($guid, $connection2, '/modules/Academics/test_create.php') == false) {
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
                try {
                  
                    $data = array('id' => $testid);
                    $sql = 'SELECT a.name as tmname,a.code,a.pupilsightSchoolYearID,a.id,b.* FROM examinationTestMaster AS a LEFT JOIN  examinationTest AS b ON a.id = b.test_master_id WHERE a.id=:id GROUP BY b.test_master_id';
                    $result = $connection2->prepare($sql);
                    $result->execute($data);
                } catch (PDOException $e) {
                    $URL .= '&return=error2';
                    header("Location: {$URL}");
                    exit();
                }

                if ($result->rowCount() != 1) {
                    $URL .= '&return=error2';
                    header("Location: {$URL}");
                } else {
                    $values = $result->fetch();
                    //Proceed!
                    $pupilsightSchoolYearID = $_POST['pupilsightSchoolYearID'];
                    $pupilsightProgramID = $_POST['pupilsightProgramID'];
                    $classIds = $_POST['pupilsightYearGroupID'];
                    
                    $datasub = array('test_master_id' => $testid);
                    $sqlsub = 'SELECT * FROM examinationTestSubjectCategory WHERE test_master_id=:test_master_id';
                    $resultsub = $connection2->prepare($sqlsub);
                    $resultsub->execute($datasub);
                    $subvalues = $resultsub->fetchAll();
                    

                    $name = $values['tmname'];
                    $code = $values['code'];
                    $pupilsightSchoolYearTermID = $values['pupilsightSchoolYearTermID'];
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

                   
                    if ($name == '') {
                        $URL .= '&return=error1';
                        header("Location: {$URL}");
                    } else {
                        //Write to database

                        try {
                            $datachk = array('name' => $name,'pupilsightSchoolYearID' => $pupilsightSchoolYearID);

                            $sqlchk = "SELECT id FROM examinationTest WHERE name=:name AND pupilsightSchoolYearID=:pupilsightSchoolYearID";
                            $resultchk = $connection2->prepare($sqlchk);
                            $resultchk->execute($datachk);

                            if ($resultchk->rowCount() < 1) {

                                $dataInsertMaster = array('name' => $name,'code' => $code,'pupilsightSchoolYearID' => $pupilsightSchoolYearID);
                                
                                $sqlMaster = 'INSERT INTO examinationTestMaster SET name=:name, code=:code, pupilsightSchoolYearID=:pupilsightSchoolYearID';
                                $resultMaster = $connection2->prepare($sqlMaster);
                                $resultMaster->execute($dataInsertMaster);
                                $test_master_id = $connection2->lastInsertID();

                                if(!empty($classIds)){
                                    foreach($classIds as $cls){
                                        // check maps
                                        $check_sql = 'SELECT COUNT(pupilsightMappingID) as total  FROM `pupilsightProgramClassSectionMapping` WHERE `pupilsightSchoolYearID` = '.$pupilsightSchoolYearID.' AND `pupilsightProgramID` = "'.$pupilsightProgramID.'" AND `pupilsightYearGroupID` = "'.$cls.'"';
                                        $chck_res = $connection2->query($check_sql);
                                        $reData = $chck_res->fetch();
                                         if(!empty($reData['total'])){
                                        //ends herer 
                                        $sqlsec = 'SELECT a.*,b.name FROM pupilsightProgramClassSectionMapping AS a LEFT JOIN pupilsightRollGroup AS b ON a.pupilsightRollGroupID = b.pupilsightRollGroupID WHERE a.pupilsightProgramID = '.$pupilsightProgramID.' AND  a.pupilsightYearGroupID = '.$cls.' ';
                                        $resultsec = $connection2->query($sqlsec);
                                        $secdata = $resultsec->fetchAll();
                                        foreach($secdata as $sec){
                                            $pupilsightRollGroupID = $sec['pupilsightRollGroupID'];
                                            $newname = $values['tmname'].' - '.$sec['name'];
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
                                                $dataInsert2 = array('test_master_id' => $test_master_id,'test_id' => $testId,'pupilsightSchoolYearID' => $pupilsightSchoolYearID, 'pupilsightProgramID' => $pupilsightProgramID, 'pupilsightYearGroupID' => $cls, 'pupilsightRollGroupID' => $pupilsightRollGroupID);

                                                $sqlUpdate = 'INSERT INTO examinationTestAssignClass SET  test_master_id=:test_master_id, test_id=:test_id, pupilsightSchoolYearID=:pupilsightSchoolYearID, pupilsightProgramID=:pupilsightProgramID, pupilsightYearGroupID=:pupilsightYearGroupID, pupilsightRollGroupID=:pupilsightRollGroupID';
                                                $resultUpdate = $connection2->prepare($sqlUpdate);
                                                $resultUpdate->execute($dataInsert2);
                                            }

                                        }
                                        
                                        } else {
                                          $errorArray[]=$cls;
                                        }
                                    }//ok
                                }

                                //if($subject_type == '2'){
                                    if(!empty($subvalues)){
                                        foreach($subvalues as $sbu){
                                            $subject_type_id = $sbu['subject_type_id'];
                                            $stype = $sbu['subject_type'];
                                            $assesment_method = $sbu['assesment_method'];
                                            $assesment_option = $sbu['assesment_option'];
                                            $max_marks = $sbu['max_marks'];
                                            $min_marks = $sbu['min_marks'];
                                            $gradeSystemId = $sbu['gradeSystemId'];

                                            $dataInsert = array('test_id' => $test_id,'subject_type' => $stype,'subject_type_id' => $subject_type_id,'assesment_method' => $assesment_method,'assesment_option' => $assesment_option,'max_marks' => $max_marks, 'min_marks' => $min_marks,'gradeSystemId' => $gradeSystemId);

                                            $sqlInsert = 'INSERT INTO examinationTestSubjectCategory SET  test_id=:test_id, subject_type=:subject_type, subject_type_id=:subject_type_id,assesment_method=:assesment_method, assesment_option=:assesment_option, max_marks=:max_marks, min_marks=:min_marks, gradeSystemId=:gradeSystemId';
                                            $resultInsert = $connection2->prepare($sqlInsert);
                                            $resultInsert->execute($dataInsert);
                                        }
                                    }
                                //}
                            }
                        } catch (PDOException $e) {
                            $URL .= '&return=error2';
                           header("Location: {$URL}");
                           exit();
                        }
                        $erorrs=implode(',',$errorArray);
                        if(!empty($errorArray)){
                        $URL.="&m_err=$erorrs";
                        }
                        $URL .= '&return=success0';
                        header("Location: {$URL}");
                   
                    }
                }
            }
        
    
}
