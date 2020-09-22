<?php
/*
Pupilsight, Flexible & Open School System
*/
include '../../pupilsight.php';
include './moduleFunctions.php';

$URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_POST['address'])."/test_create_with_section.php";

$URLSuccess = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_POST['address'])."/manage_edit_test.php";

if (isActionAccessible($guid, $connection2, '/modules/Academics/test_create.php') == false) {
    $URL .= '&return=error0';
    header("Location: {$URL}");
} else {
    // echo '<pre>';
    // print_r($_POST);
    // echo '</pre>';
    // die();
            //Proceed!
            $name = $_POST['name'] ;
            $pupilsightSchoolYearID = $_POST['pupilsightSchoolYearID'] ;
          
            if ($name == '') {
                $URL .= '&return=error1';
                header("Location: {$URL}");
            } else {
                try {
                  
                    $data = array('name' => $name, 'pupilsightSchoolYearID' => $pupilsightSchoolYearID);
                    $sql = 'SELECT * FROM examinationTest WHERE name=:name AND pupilsightSchoolYearID=:pupilsightSchoolYearID';
                   
                    $result = $connection2->prepare($sql);
                    $result->execute($data);
                } catch (PDOException $e) {
                    $URL .= '&return=error2';
                    header("Location: {$URL}");
                    exit();
                }

                if ($result->rowCount() >= 1) {
                    $URL .= '&return=error3';
                    header("Location: {$URL}");
                } else {
                    //Proceed!
                    $test_master_id = $_POST['test_master_id'];
                    $subject_type = $_POST['subject_type'];
                    $pupilsightSchoolYearTermID = $_POST['pupilsightSchoolYearTermID'];
                    $assesment_method = $_POST['assesment_method'];
                    $assesment_option = $_POST['assesment_option'] ;
                    $max_marks = $_POST['max_marks'];
                    $min_marks = $_POST['min_marks'];
                    $gradeSystemId = $_POST['gradeSystemId'];
                    $enable_remarks = $_POST['enable_remarks'] ;
                    $enable_schedule = $_POST['enable_schedule'];
                    if(!empty($_POST['start_date'])){
                        $sd = explode('/', $_POST['start_date']);
                        $start_date  = date('Y-m-d', strtotime(implode('-', array_reverse($sd))));
                    } else {
                        $start_date  = '';
                    }
                    $start_time = $_POST['start_time'];
                    if(!empty($_POST['end_date'])){
                        $ed = explode('/', $_POST['end_date']);
                        $end_date  = date('Y-m-d', strtotime(implode('-', array_reverse($ed))));
                    } else {
                        $end_date  = '';
                    }
                    $end_time = $_POST['end_time'];

                    $pupilsightProgramID = $_POST['pupilsightProgramID'];
                    $pupilsightYearGroupID = $_POST['pupilsightYearGroupID'];
                    $pupilsightRollGroupID = $_POST['pupilsightRollGroupID'];
                    
                    if ($name == '') {
                        $URL .= '&return=error1';
                        header("Location: {$URL}");
                    } else {
                        //Write to database

                        try {
                           // `examinationTest` WHERE 1,,id,`pupilsightSchoolYearID`,`name`,`code`
                            $dataUpdate = array('test_master_id'=>$test_master_id,'name' => $name,'code' => $name,'pupilsightSchoolYearID' => $pupilsightSchoolYearID,'pupilsightSchoolYearTermID' => $pupilsightSchoolYearTermID, 'subject_type' => $subject_type,'assesment_method' => $assesment_method,'assesment_option' => $assesment_option,'max_marks' => $max_marks, 'min_marks' => $min_marks,'gradeSystemId' => $gradeSystemId,'enable_remarks' => $enable_remarks,'enable_schedule' => $enable_schedule, 'start_date' => $start_date,'start_time' => $start_time,'end_date' => $end_date,'end_time' => $end_time);
                            $sql = 'INSERT INTO examinationTest SET test_master_id=:test_master_id, name=:name, code=:code, pupilsightSchoolYearID=:pupilsightSchoolYearID, pupilsightSchoolYearTermID=:pupilsightSchoolYearTermID, subject_type=:subject_type, assesment_method=:assesment_method, assesment_option=:assesment_option, max_marks=:max_marks, min_marks=:min_marks, gradeSystemId=:gradeSystemId, enable_remarks=:enable_remarks, enable_schedule=:enable_schedule, start_date=:start_date, start_time=:start_time, end_date=:end_date, end_time=:end_time';
                            $result = $connection2->prepare($sql);
                            $result->execute($dataUpdate);
                            $testid = $connection2->lastInsertID();

                            if(!empty($pupilsightYearGroupID)){
                               if(!empty($pupilsightRollGroupID)){
                                   $sectionIds = implode(',',$pupilsightRollGroupID);
                                    //foreach($pupilsightRollGroupID as $sec){
                                        $dataUpdatechk = array('test_id' => $testid,'pupilsightSchoolYearID' => $pupilsightSchoolYearID, 'pupilsightProgramID' => $pupilsightProgramID, 'pupilsightYearGroupID' => $pupilsightYearGroupID);

                                        $sqlchk = "SELECT id FROM examinationTestAssignClass WHERE test_id=:test_id AND pupilsightSchoolYearID=:pupilsightSchoolYearID AND pupilsightProgramID=:pupilsightProgramID AND pupilsightYearGroupID=:pupilsightYearGroupID";
                                        $resultchk = $connection2->prepare($sqlchk);
                                        $resultchk->execute($dataUpdatechk);

                                        if ($resultchk->rowCount() < 1) {

                                            $dataUpdate2 = array('test_id' => $testid,'pupilsightSchoolYearID' => $pupilsightSchoolYearID, 'pupilsightProgramID' => $pupilsightProgramID, 'pupilsightYearGroupID' => $pupilsightYearGroupID, 'pupilsightRollGroupID' => $sectionIds);

                                            $sqlUpdate = 'INSERT INTO examinationTestAssignClass SET  test_id=:test_id, pupilsightSchoolYearID=:pupilsightSchoolYearID, pupilsightProgramID=:pupilsightProgramID, pupilsightYearGroupID=:pupilsightYearGroupID,pupilsightRollGroupID=:pupilsightRollGroupID';
                                            $resultUpdate = $connection2->prepare($sqlUpdate);
                                            $resultUpdate->execute($dataUpdate2);
                                        }
                                    //}
                                } else {
                                    $dataUpdate2 = array('test_id' => $testid,'pupilsightSchoolYearID' => $pupilsightSchoolYearID, 'pupilsightProgramID' => $pupilsightProgramID, 'pupilsightYearGroupID' => $pupilsightYearGroupID);

                                    $sqlchk = "SELECT id FROM examinationTestAssignClass WHERE test_id=:test_id AND pupilsightSchoolYearID=:pupilsightSchoolYearID AND pupilsightProgramID=:pupilsightProgramID AND pupilsightYearGroupID=:pupilsightYearGroupID";
                                    $resultchk = $connection2->prepare($sqlchk);
                                    $resultchk->execute($dataUpdate2);

                                    if ($resultchk->rowCount() < 1) {
                                        $sqlUpdate = 'INSERT INTO examinationTestAssignClass SET  test_id=:test_id, pupilsightSchoolYearID=:pupilsightSchoolYearID, pupilsightProgramID=:pupilsightProgramID, pupilsightYearGroupID=:pupilsightYearGroupID';
                                        $resultUpdate = $connection2->prepare($sqlUpdate);
                                        $resultUpdate->execute($dataUpdate2);
                                    }
                                }
                            }
                        } catch (PDOException $e) {
                            $URL .= '&return=error5';
                           header("Location: {$URL}");
                           exit();
                        }

                        $URLSuccess .= '&return=success0';

                        header("Location: {$URLSuccess}");
                   
                    }
                }
            }
        
    
}
