<?php
/*
Pupilsight, Flexible & Open School System
*/

include '../../pupilsight.php';

include './moduleFunctions.php';

//Search & Filters


$remarkid = $_POST['id'];
$URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_POST['address'])."/ac_manage_remarks.php";

if (isActionAccessible($guid, $connection2, '/modules/Academics/ac_manage_remarks.php') == false) {
    $URL .= '&return=error0';
    header("Location: {$URL}");
} else {
    
            //Proceed!
          
            if ($remarkid == '') {
                $URL .= '&return=error1';
                header("Location: {$URL}");
            } else {
                try {
                  
                        $data = array('id' => $remarkid);
                        $sql = 'SELECT * FROM acRemarks WHERE id=:id';
                   
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
                    $remarkcode = $_POST['rcode'];
                    $description = $_POST['description'];
                    $pupilsightDepartmentID = $_POST['pupilsightDepartmentID'];
                    $skill = $_POST['skill'];

                    if ($remarkcode == ''& $description == '') {
                        $URL .= '&return=error1';
                        header("Location: {$URL}");
                    } else {
                        //Write to database


                        try {
                            $dataUpdate = array('id' => $remarkid,'remarkcode' => $remarkcode,'description' => $description, 'pupilsightDepartmentID' => $pupilsightDepartmentID, 'skill' => $skill);
                           
                            $sql = 'UPDATE acRemarks SET remarkcode=:remarkcode, description=:description, pupilsightDepartmentID=:pupilsightDepartmentID, skill=:skill WHERE id=:id';
                            $result = $connection2->prepare($sql);
                            $result->execute($dataUpdate);
                        } catch (PDOException $e) {
                            $URL .= '&return=error2';
                            header("Location: {$URL}");
                            exit();
                        }

                        $URL .= '&return=success0';

                       
                      //  echo $URL;
                        header("Location: {$URL}");
                       /*
                        if(isset($_SERVER['HTTP_REFERER'])) {
                            $previous = $_SERVER['HTTP_REFERER'].'&return=success0';
                        }
                            header("location:{$previous}");*/

                      
                    }
                }
            }
        
    
}
