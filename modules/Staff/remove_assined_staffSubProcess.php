<?php
/*
Pupilsight, Flexible & Open School System
*/

include '../../pupilsight.php';
    //      echo '<pre>';
    // print_r($_POST);
    // echo '</pre>';
    // die();

    $id = $_POST['id'];
$URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_POST['address']).'/assign_Staff_toSubject.php&id='.$id;
$URLDelete = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_POST['address']).'/assign_staff_toSubject.php';

if (isActionAccessible($guid, $connection2, '/modules/Staff/remove_assigned_staffSub.php') == false) {
    $URL .= '&return=error0';
    header("Location: {$URL}");
} else {
    //Proceed!
    //Check if school year specified
   // $pupilsightdepartmentID = $_POST['dep'];
    $assignstaff_tosubject_id = $_POST['dep'];

    if ($assignstaff_tosubject_id == '') {
        $URL .= '&return=error1';
        header("Location: {$URL}");
    } else {
        
              //Write to database
              try {
                  //
                foreach($assignstaff_tosubject_id as $dep){
                
                $data = array('pupilsightStaffID' => $id,'id'=>$dep);
                $sql = 'DELETE FROM assignstaff_tosubject WHERE id=:id AND pupilsightStaffID=:pupilsightStaffID';
                $result = $connection2->prepare($sql);
                $result->execute($data);
                }

               
            } catch (PDOException $e) {
                $URLDelete .= '&return=error2';
                header("Location: {$URLDelete}");
                exit();
            }
        
            $URLDelete = $URLDelete.'&return=success0';
            header("Location: {$URLDelete}");
        }
    }

