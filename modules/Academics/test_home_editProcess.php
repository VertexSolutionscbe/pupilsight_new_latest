<?php
/*
Pupilsight, Flexible & Open School System
*/
include '../../pupilsight.php';
include './moduleFunctions.php';
$testid = $_POST['id'];
$URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_POST['address'])."/test_home_edit.php";

if (isActionAccessible($guid, $connection2, '/modules/Academics/test_home.php') == false) {
    $URL .= '&return=error0';
    header("Location: {$URL}");
} else {
    
            //Proceed!
          
            if ($testid == '') {
                $URL .= '&return=error1';
                header("Location: {$URL}");
            } else {
                try {
                  
                        $data = array('id' => $testid);
                        $sql = 'SELECT * FROM examinationTestMaster WHERE id=:id';
                   
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
                    //Proceed!
                    $name=$_POST['testname'] ;
                    $testcode=$_POST['testcode'];
                    $pupilsightSchoolYearID=$_POST['pupilsightSchoolYearID'] ;

                    if ($name == ''& $testcode == '') {
                        $URL .= '&return=error1';
                        header("Location: {$URL}");
                    } else {
                        //Write to database


                        try {
                           // `examinationTest` WHERE 1,,id,`pupilsightSchoolYearID`,`name`,`code`
                            $dataUpdate = array('id' => $testid,'name' => $name,'code' => $testcode, 'pupilsightSchoolYearID' => $pupilsightSchoolYearID);
                            $sql = 'UPDATE examinationTestMaster SET name=:name, code=:code, pupilsightSchoolYearID=:pupilsightSchoolYearID WHERE id=:id';
                            $result = $connection2->prepare($sql);
                            $result->execute($dataUpdate);
                        } catch (PDOException $e) {
                            $URL .= '&return=error2';
                            header("Location: {$URL}");
                            exit();
                        }

                        $URL .= '&return=success0';

                        header("Location: {$URL}");
                   
                    }
                }
            }
        
    
}
