<?php
/*
Pupilsight, Flexible & Open School System
*/
include '../../pupilsight.php';
include './moduleFunctions.php';
$testid = $_POST['test_master_id'];
$stype_id = $_POST['subject_type_id'];
 $URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/Academics/test_create.php&tid='.$testid;

if (isActionAccessible($guid, $connection2, '/modules/Academics/test_create.php') == false) {
    $URL .= '&return=error0';
    header("Location: {$URL}");
} else {
    // echo '<pre>';
    // print_r($_POST);
    // echo '</pre>';
    // die();
    
            //Proceed!
          
            if ($testid == '' && $stype_id == '') {
                $URL .= '&return=error1';
                header("Location: {$URL}");
            } else {
                    foreach($stype_id as $k=>$sid){
                        $subject_type_id = $k;
                        $subject_type = $_POST['subject_type'][$k];
                        $assesment_method = $_POST['assesment_method'][$k];
                        $assesment_option = $_POST['assesment_option'][$k];
                        if($assesment_method == 'Grade'){
                            $max_marks = '';
                            $min_marks = '';
                        } else {
                            $max_marks = $_POST['max_marks'][$k];
                            $min_marks = $_POST['min_marks'][$k];
                        }
                        $gradeSystemId = $_POST['gradeSystemId'][$k];

                        $dataChk = array('test_master_id' => $testid,'subject_type' => $subject_type,'subject_type_id' => $subject_type_id);

                        $sqlchk = "SELECT id FROM examinationTestSubjectCategory WHERE test_master_id=:test_master_id AND subject_type=:subject_type AND subject_type_id=:subject_type_id";
                        $resultchk = $connection2->prepare($sqlchk);
                        $resultchk->execute($dataChk);

                        if ($resultchk->rowCount() < 1) {
                            $dataInsert = array('test_master_id' => $testid,'subject_type' => $subject_type,'subject_type_id' => $subject_type_id,'assesment_method' => $assesment_method,'assesment_option' => $assesment_option,'max_marks' => $max_marks, 'min_marks' => $min_marks,'gradeSystemId' => $gradeSystemId);

                            $sqlInsert = 'INSERT INTO examinationTestSubjectCategory SET  test_master_id=:test_master_id, subject_type=:subject_type, subject_type_id=:subject_type_id,assesment_method=:assesment_method, assesment_option=:assesment_option, max_marks=:max_marks, min_marks=:min_marks, gradeSystemId=:gradeSystemId';
                            $resultInsert = $connection2->prepare($sqlInsert);
                            $resultInsert->execute($dataInsert);
                        } else {
                            $values = $resultchk->fetch();
                            
                            $dataUpdate = array('test_master_id' => $testid,'subject_type' => $subject_type,'subject_type_id' => $subject_type_id,'assesment_method' => $assesment_method,'assesment_option' => $assesment_option,'max_marks' => $max_marks, 'min_marks' => $min_marks,'gradeSystemId' => $gradeSystemId, 'id' => $values['id']);

                            $sql = 'UPDATE examinationTestSubjectCategory SET test_master_id=:test_master_id, subject_type=:subject_type, subject_type_id=:subject_type_id,assesment_method=:assesment_method, assesment_option=:assesment_option, max_marks=:max_marks, min_marks=:min_marks, gradeSystemId=:gradeSystemId WHERE id=:id';
                            $result = $connection2->prepare($sql);
                            $result->execute($dataUpdate);
                        }
                    }


                    $dataUpdate2 = array('id' => $testid,'subject_type' => '2');
                    $sql2 = 'UPDATE examinationTest SET subject_type=:subject_type WHERE id=:id';
                    $result2 = $connection2->prepare($sql2);
                    $result2->execute($dataUpdate2);

                    $URL .= '&return=success0';
                    //header("Location: {$URL}");   
                   
            }
        
    
}
