<?php
/*
Pupilsight, Flexible & Open School System
*/

include '../../pupilsight.php';

$id = $_GET['id'];
$URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_POST['address']).'/wf_state_edit.php&id='.$id;

if (isActionAccessible($guid, $connection2, '/modules/Campaign/wf_state_edit.php') != false) {
    $URL .= '&return=error0';
    header("Location: {$URL}");
} else {
    //Proceed!
    //Check if school year specified
    if ($id == '') {
        $URL .= '&return=error1';
        header("Location: {$URL}");
    } else {
        try {
            $data = array('id' => $id);
            $sql = 'SELECT * FROM workflow_state WHERE id=:id';
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
            //Validate Inputs
		    $name = $_POST['name'];
			$code = $_POST['code'];
			$display_name = $_POST['display_name'];
			$notification = $_POST['notification']; 
			$cuid = $_SESSION[$guid]['pupilsightPersonID'];
			$workflow_id=$_SESSION['workflow_id'];
            
            if ($name == '' or $code == '' or $display_name == '') {
                $URL .= '&return=error3';
                header("Location: {$URL}");
            } else {
                //Check unique inputs for uniquness
                try {
					
                    $data = array('name' => $name, 'code' => $code, 'id' => $id);
                    $sql = 'SELECT * FROM workflow_state WHERE (name=:name AND code=:code) AND NOT id=:id';
                    $result = $connection2->prepare($sql);
                    $result->execute($data);
                } catch (PDOException $e) {
                    $URL .= '&return=error2';
                    header("Location: {$URL}");
                    exit();
                }

                if ($result->rowCount() > 0) {
                    $URL .= '&return=error3';
                    header("Location: {$URL}");
                } else {
                    

                        //Write to database
                        try {
							
							$data = array('name' => $name, 'code' => $code, 'display_name' => $display_name, 'notification' => $notification,'cuid' => $cuid,'id' => $id);
		
                            $sql = "UPDATE workflow_state SET name=:name, code=:code,display_name=:display_name,notification=:notification,cuid=:cuid WHERE id=:id";
                            $result = $connection2->prepare($sql);
                            $result->execute($data);
                        } catch (PDOException $e) {
                            echo $e;
                            die();
                            $URL .= '&return=error2';
                            header("Location: {$URL}");
                            exit();
                        }

                        // Update session vars so the user is warned if they're logged into a different year
                        // if ($status == 'Current') {
                        //     $_SESSION[$guid]['idCurrent'] = $id;
                        //     $_SESSION[$guid]['pupilsightSchoolYearNameCurrent'] = $name;
                        //     $_SESSION[$guid]['pupilsightSchoolYearSequenceNumberCurrent'] = $sequenceNumber;
                        // }
                       
                        $URL .= '&return=success0';
                        header("Location: {$URL}");
                   
                }
            }
        }
    }
}
