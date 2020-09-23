<?php
/*
Pupilsight, Flexible & Open School System
*/
include '../../pupilsight.php';
include './moduleFunctions.php';

$id = $_POST['id'];
$URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_POST['address'])."/modify_test_class_section_wise.php&id=".$id."";

$URLSuccess = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_POST['address'])."/subject_to_test.php";

if (isActionAccessible($guid, $connection2, '/modules/Academics/modify_test_class_section_wise.php') == false) {
    $URL .= '&return=error0';
    header("Location: {$URL}");
} else {
    // echo '<pre>';
    // print_r($_POST);
    // echo '</pre>';
    // die();
            //Proceed!
            
            $name = $_POST['name'];
            $pupilsightSchoolYearID = $_POST['pupilsightSchoolYearID'] ;
            $cid = $_POST['cid'];
          
            if ($name == '') {
                $URL .= '&return=error1';
                header("Location: {$URL}");
            } else {
                // try {
                  
                //     $data = array('id' => $id,'name' => $name, 'pupilsightSchoolYearID' => $pupilsightSchoolYearID);
                //     $sql = 'SELECT * FROM examinationTest WHERE (name=:name AND pupilsightSchoolYearID=:pupilsightSchoolYearID) AND NOT id=:id';
                   
                //     $result = $connection2->prepare($sql);
                //     $result->execute($data);
                // } catch (PDOException $e) {
                //     $URL .= '&return=error2';
                //     header("Location: {$URL}");
                //     exit();
                // }

                // if ($result->rowCount() > 0) {
                //     $URL .= '&return=error3';
                //     header("Location: {$URL}");
                // } else {
                    //Proceed!
                    
                    $pupilsightProgramID = $_POST['pupilsightProgramID'];
                    $pupilsightSchoolYearTermID = $_POST['pupilsightSchoolYearTermID'];
                    $gradeSystemId = $_POST['gradeSystemId'];
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
                    $report_template_id = $_POST['report_template'];
                    $gradeSystemId = $_POST['gradeSystemId'];

                    if ($name == '') {
                        $URL .= '&return=error1';
                        header("Location: {$URL}");
                    } else {
                        //Write to database

                        try {
                           // `examinationTest` WHERE 1,,id,`pupilsightSchoolYearID`,`name`,`code`
                            $dataUpdate = array('name' => $name,'pupilsightSchoolYearID' => $pupilsightSchoolYearID,'pupilsightSchoolYearTermID' => $pupilsightSchoolYearTermID, 'gradeSystemId' => $gradeSystemId, 'enable_schedule' => $enable_schedule, 'report_template_id' => $report_template_id, 'start_date' => $start_date,'start_time' => $start_time,'end_date' => $end_date,'end_time' => $end_time, 'id' => $id);
                            $sql = 'UPDATE examinationTest SET name=:name, pupilsightSchoolYearID=:pupilsightSchoolYearID, pupilsightSchoolYearTermID=:pupilsightSchoolYearTermID, gradeSystemId=:gradeSystemId, enable_schedule=:enable_schedule, report_template_id=:report_template_id, start_date=:start_date, start_time=:start_time, end_date=:end_date, end_time=:end_time WHERE id=:id';
                            $result = $connection2->prepare($sql);
                            $result->execute($dataUpdate);
                            $testid = $id;

                            
                        } catch (PDOException $e) {
                            $URL .= '&return=error5';
                           header("Location: {$URL}");
                           exit();
                        }

                        $URLSuccess .= '&tid='.$testid.'&aid='.$pupilsightSchoolYearID.'&pid='.$pupilsightProgramID.'&cid='.$cid.'&return=success0';

                        header("Location: {$URLSuccess}");
                   
                    }
                //}
            }
        
    
}
