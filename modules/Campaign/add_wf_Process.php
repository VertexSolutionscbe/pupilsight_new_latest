<?php
/*
Pupilsight, Flexible & Open School System
*/

include '../../pupilsight.php';

$URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_POST['address']).'/wf_add.php';

if (isActionAccessible($guid, $connection2, '/modules/Campaign/wf_add.php') != false) {
    $URL .= '&return=error0';
    header("Location: {$URL}");
} else {
    //Proceed!
    //Validate Inputs

    /* echo "insert";
	 print_r($_REQUEST);exit;*/

	 //name,code,academic_year,description
    $name = $_POST['name'];
    $code = $_POST['code'];
    $description = $_POST['description'];
    $academic_year = $_POST['academic_year']; 
    $cuid = $_SESSION[$guid]['pupilsightPersonID'];
    $statename = $_POST['statename'];
    $statecode = $_POST['statecode'];
    $serialorder = $_POST['serialorder'];
    $notification = $_POST['notification']; 
    $tid = $_POST['pupilsightTemplateIDs'];
	
    if ($name == '' or $code == ''  or $academic_year == '') {
        $URL .= '&return=error1';
        header("Location: {$URL}");
    } else {
        //Check unique inputs for uniquness
        try {
            $data = array('name' => $name, 'code' => $code);
            $sql = 'SELECT * FROM workflow WHERE name=:name OR code=:code';
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
				//`name`,`description`,`code`,`academic_year`,`cuid`,`cdt`,`udt`
                try {
                    $data = array('name' => $name, 'description' => $description, 'code' => $code, 'academic_year' => $academic_year, 'cuid' => $cuid);
                    $sql = "INSERT INTO workflow SET name=:name, description=:description, code=:code, academic_year=:academic_year,cuid=:cuid";
                    $result = $connection2->prepare($sql);
                    $result->execute($data);
                } catch (PDOException $e) {
                    $URL .= '&return=error2';
                    header("Location: {$URL}");
                }

                //Last insert ID
                $AI = str_pad($connection2->lastInsertID(), 3, '0', STR_PAD_LEFT);
			$_SESSION['workflow_id']=$AI;
                // Update session vars so the user is warned if they're logged into a different year
                // if ($status == 'Current') {
                //     $_SESSION[$guid]['pupilsightSchoolYearIDCurrent'] = $AI;
                //     $_SESSION[$guid]['pupilsightSchoolYearNameCurrent'] = $name;
                //     $_SESSION[$guid]['pupilsightSchoolYearSequenceNumberCurrent'] = $sequenceNumber;
                // }
				//`workflow_id`,`campaign_id`,`cuid`,`cdt`,`udt`
                if(!empty($AI)){
                    $cid = $_REQUEST['cid'];
                    $data1 = array('workflow_id' => $AI, 'campaign_id' => $_REQUEST['cid'], 'cuid' => $cuid);
                            $sql1 = "INSERT INTO workflow_map SET workflow_id=:workflow_id, campaign_id=:campaign_id, cuid=:cuid";
                            $result = $connection2->prepare($sql1);
                            $result->execute($data1);

                    if(!empty($statename)){
                        foreach($statename as $k=>$s){
                            $sname = $s;
                            $scode = $statecode[$k];
                            $order = $serialorder[$k];
                            $notific = $notification[$k];
                            $pupilsightTemplateIDs = $tid[$k];
                            if(!empty($sname) && !empty($scode)){
                                $datas = array('code' => $scode, 'workflowid'=> $AI);
                                $sqls = 'SELECT * FROM workflow_state WHERE code=:code AND workflowid=:workflowid';
                                $results = $connection2->prepare($sqls);
                                $results->execute($datas);
                                $values = $results->fetch();

                                if(empty($values)){
                                    $datass = array('name' => $sname, 'code' => $scode, 'order_wise' => $order, 'notification' => $notific, 'pupilsightTemplateIDs' => $pupilsightTemplateIDs,'cuid' => $cuid,'workflowid'=>$AI);
                                    $sqlss = "INSERT INTO workflow_state SET name=:name, code=:code, order_wise=:order_wise,  notification=:notification,pupilsightTemplateIDs=:pupilsightTemplateIDs, cuid=:cuid,workflowid=:workflowid";
                                    $resultss = $connection2->prepare($sqlss);
                                    $resultss->execute($datass);

                                    if(!empty($pupilsightTemplateIDs)){
                                        $sq = "UPDATE pupilsightTemplate SET state = '1' where pupilsightTemplateID IN (".$pupilsightTemplateIDs.") ";
                                        $connection2->query($sq);
                                    }
                                }    
                            }
                        }
                    }



                }    
               // $URL .= "&return=success0&editID=$AI";
			   
				
                $URL1 = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/Campaign/add_wf_transitions.php&wid='.$AI.'&cid='.$cid;
				header("Location: {$URL1}");
           
        }
    }
}
