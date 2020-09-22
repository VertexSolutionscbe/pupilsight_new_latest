<?php
/*
Pupilsight, Flexible & Open School System
*/

include '../../pupilsight.php';

include './moduleFunctions.php';



$remarkid = $_GET['id'];
$URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_POST['address'])."/delete_blockled_attendance.php";
$URLDelete = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_POST['address'])."/ac_manage_remarks.php";

if (isActionAccessible($guid, $connection2, '/modules/Academics/ac_manage_remarks_delete.php') == false) {
    $URL .= '&return=error0';
    header("Location: {$URL}");
} else {
    //Get action with highest precendence
   
            //Proceed!
            if ($remarkid == '') {
                $URL .= '&return=error1';
                header("Location: {$URL}");
            } else {
               
               
                    //Write to database
                    try {
                        $data = array('id' => $remarkid);
                        $sql = 'DELETE FROM acRemarks WHERE id=:id';
                        $result = $connection2->prepare($sql);
                        $result->execute($data);
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
