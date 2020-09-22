<?php
/*
Pupilsight, Flexible & Open School System
*/

include '../../pupilsight.php';

$URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_POST['address']).'/wf_state_add.php';

if (isActionAccessible($guid, $connection2, '/modules/Campaign/wf_state_add.php') != false) {
    $URL .= '&return=error0';
    header("Location: {$URL}");
} else {
    //Proceed!
    //Validate Inputs
/* $_SESSION['workflow_id'];
    echo "insert";
	 print_r($_REQUEST);exit;*/

	 //name,code,academic_year,description
    $name = $_POST['name'];
    $code = $_POST['code'];
    $display_name = $_POST['display_name'];
    $notification = $_POST['notification']; 
    $cuid = $_SESSION[$guid]['pupilsightPersonID'];
	$workflow_id=$_SESSION['workflow_id'];
	
    if ($name == '' or $code == ''  or $display_name == '') {
        $URL .= '&return=error1';
        header("Location: {$URL}");
    } else {
        //Check unique inputs for uniquness
		//`workflowid`,`name`,`code`,`display_name`,`notification`,`cuid`,
        try {
            $data = array('code' => $code);
            $sql = 'SELECT * FROM workflow_state WHERE code=:code';
            $result = $connection2->prepare($sql);
            $result->execute($data);
        } catch (PDOException $e) {
            $URL .= '&return=error2';
            header("Location: {$URL}");
            exit();
        }

        if ($result->rowCount() > 0) {
           $URL_BACK= $_SERVER['HTTP_REFERER'];
            $URL_BACK .= '&return=error7';		
            header("Location: {$URL_BACK}");
        } else {
            //Check for other currents
            
                //Write to database
				//`workflowid`,`name`,`code`,`display_name`,`notification`,`cuid`
                try {
                    $data = array('name' => $name, 'code' => $code, 'display_name' => $display_name, 'notification' => $notification,'cuid' => $cuid,'workflowid'=>$workflow_id);
                    $sql = "INSERT INTO workflow_state SET name=:name, code=:code, display_name=:display_name, notification=:notification,cuid=:cuid,workflowid=:workflowid";
                    $result = $connection2->prepare($sql);
                    $result->execute($data);
                } catch (PDOException $e) {
                    $URL .= '&return=error2';
                    header("Location: {$URL}");
                }

                //Last insert ID
                $AI = str_pad($connection2->lastInsertID(), 3, '0', STR_PAD_LEFT);

               
				//`workflow_id`,`campaign_id`,`cuid`,`cdt`,`udt`
              
				 $URL .= "&return=success0&editID=$AI";
                header("Location: {$URL}");
				
           
        }
    }
}
