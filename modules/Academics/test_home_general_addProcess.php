<?php
/*
Pupilsight, Flexible & Open School System
*/

//Pupilsight system-wide includes
include __DIR__ . '/../../pupilsight.php';

//Module includes
include __DIR__ . '/moduleFunctions.php';

/*echo "<pre>";
print_r($_POST);
exit;
*/
$name=$_POST['testname'] ;
$testcode=$_POST['testcode'];
$pupilsightSchoolYearID=$_POST['pupilsightSchoolYearID'] ;

$URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_POST['address'])."/test_create.php";

if (isActionAccessible($guid, $connection2, '/modules/Academics/test_home_general_add.php') == false) {
    $URL .= '&return=error0';
    header("Location: {$URL}");
} else {
    //Proceed!
  
    if ($name == ''& $testcode == '' ) {
        $URL .= '&return=error1';
        header("Location: {$URL}");
    } else {
      
            $today = date('Y-m-d');

                // `examinationTest` WHERE 1,`pupilsightSchoolYearID`,`name`,`code`

                        try {
                           
                                $data = array('name' => $name,'pupilsightSchoolYearID'=>$pupilsightSchoolYearID);
                                $sql = 'SELECT * FROM examinationTestMaster WHERE name=:name AND  pupilsightSchoolYearID=:pupilsightSchoolYearID';
                                $result = $connection2->prepare($sql);
                                $result->execute($data);
                               // echo $result->rowCount();die();
                                if (!empty($result->rowCount())) {
                                    $URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_POST['address'])."/test_home_general_add.php";
                                    $_SESSION['error']="Test name is duplicated";
                                    header("Location: {$URL}");
                                    exit();
                                } else {  
                            $dataUpdate = array('name' => $name,'code' => $testcode, 'pupilsightSchoolYearID' => $pupilsightSchoolYearID);
                            $sqlUpdate = 'INSERT INTO examinationTestMaster SET  name=:name, code=:code, pupilsightSchoolYearID=:pupilsightSchoolYearID';
                            $resultUpdate = $connection2->prepare($sqlUpdate);
                            $resultUpdate->execute($dataUpdate);
                            $testId = $connection2->lastInsertID();
                                }
                        } catch (PDOException $e) {
                            $URL .= '&return=error2';
                            header("Location: {$URL}");
                            exit();
                        }

                        $URL .= '&tid='.$testId.'&return=success0';
                       // echo $URL;
                       header("Location: {$URL}");
       
             }
        }
