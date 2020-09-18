<?php
/*
Pupilsight, Flexible & Open School System
*/

include '../../pupilsight.php';

$id = $_POST['id'];

$URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_POST['address']).'/staff_view.php';

if (isActionAccessible($guid, $connection2, '/modules/Staff/change_staff_status.php') == false) {
    $URL .= '&return=error0';
    header("Location: {$URL}");
} else {
    //        echo '<pre>';
    // print_r($_POST);
    // echo '</pre>';
    // die();
    try {
        $data = array('pupilsightPersonID' => $id);
        $sql = 'SELECT * FROM pupilsightStaff WHERE pupilsightPersonID=:pupilsightPersonID';
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
        $status = $_POST['staffstatus'];

        if(!empty($_POST['reasoninactive'])){
            $inactive_reason = $_POST['reasoninactive'];
        }else{
            $inactive_reason = '';
        }
        
        if ($status == '' ) {
            $URL .= '&return=error1';
            header("Location: {$URL}");
        } else {
            try{
            $data = array('staff_status' => $status, 'inactive_reason' => $inactive_reason,'pupilsightPersonID'=>$id  );
         //       print_r($data);die();
                        $sql = 'UPDATE pupilsightStaff SET staff_status=:staff_status, inactive_reason=:inactive_reason, pupilsightPersonID=:pupilsightPersonID WHERE pupilsightPersonID=:pupilsightPersonID';
                        $result = $connection2->prepare($sql);
                        $result->execute($data);
            }
         catch (PDOException $e) {
            $URL .= '&return=error2';
            header("Location: {$URL}");
            exit();
        }

        $URL.= '&return=success0';
        header("Location: {$URL}");

        }
    
    
    }

}
