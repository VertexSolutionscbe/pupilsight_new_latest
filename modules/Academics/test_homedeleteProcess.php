<?php
/*
Pupilsight, Flexible & Open School System
*/

include '../../pupilsight.php';
include './moduleFunctions.php';
$testid = $_GET['id'];
$URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_POST['address'])."/test_home_delete.php";
$URLDelete = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_POST['address'])."/test_home.php";

if (isActionAccessible($guid, $connection2, '/modules/Academics/test_home_delete.php') == false) {
    $URL .= '&return=error0';
    header("Location: {$URL}");
} else {
    //Get action with highest precendence
   
            //Proceed!
            if ($testid == '') {
                $URL .= '&return=error1';
                header("Location: {$URL}");
            } else {
                             
                    //Write to database
                    try {

                        $data = array('id' => $testid);
                        $sql = 'DELETE FROM examinationTestMaster WHERE id=:id';
                        $result = $connection2->prepare($sql);
                        $result->execute($data);

                        $data1 = array('test_master_id' => $testid);
                        $sql1 = 'DELETE FROM examinationTest WHERE test_master_id=:test_master_id';
                        $result1 = $connection2->prepare($sql1);
                        $result1->execute($data1);

                        $data2 = array('test_master_id' => $testid);
                        $sql2 = 'DELETE FROM examinationTestAssignClass WHERE test_master_id=:test_master_id';
                        $result2 = $connection2->prepare($sql2);
                        $result2->execute($data2);

                        $data3 = array('test_master_id' => $testid);
                        $sql3 = 'DELETE FROM examinationTestSubjectCategory WHERE test_master_id=:test_master_id';
                        $result3 = $connection2->prepare($sql3);
                        $result3->execute($data3);
                    } catch (PDOException $e) {
                        $URL .= '&return=error2';
                        header("Location: {$URL}");
                        exit();
                    }

                    $URLDelete = $URLDelete.'&return=success0';
                  //  header("Location: {$URLDelete}");
                  if(isset($_SERVER['HTTP_REFERER'])) {
                    $previous = $_SERVER['HTTP_REFERER'];
                }
                    header("location:{$previous}");
               
            }
        
    
}
