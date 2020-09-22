<?php
/*
Pupilsight, Flexible & Open School System
*/

include '../../pupilsight.php';

$id = $_GET['id'];

$URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_POST['address']).'/wf_delete.php&id='.$id.'&search='.$_GET['search'];
$URLDelete = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_POST['address']).'/wf_manage.php&search='.$_GET['search'];

if (isActionAccessible($guid, $connection2, '/modules/Campaign/wf_delete.php') != false) {
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
            $sql = 'SELECT * FROM workflow WHERE id=:id';
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
            //Write to database
            try {
				//to pass campaign details to return url
				$data1 = array('id' => $id);
				$sql1 = 'SELECT wm.campaign_id,cm.id,cm.name,cm.academic_year FROM workflow_map AS wm JOIN campaign AS cm ON wm.campaign_id=cm.id  WHERE wm.workflow_id=:id';
				$result1 = $connection2->prepare($sql1);
				$result1->execute($data1);
				$wfrow1 = $result1->fetch();
				$campaign_id=$wfrow1['campaign_id'];
				$academic_year=$wfrow1['academic_year'];
				$name=$wfrow1['name'];
				//to pass campaign details to return url
				
                $data = array('id' => $id);
                $sql = 'DELETE FROM workflow WHERE id=:id';
                $result = $connection2->prepare($sql);
                $result->execute($data);
					//delete from workflow_map
                $workflow_id = $id;
                $data2 = array('workflow_id' => $id);
                $sql2 = 'DELETE FROM workflow_map WHERE workflow_id=:workflow_id';
                $result2 = $connection2->prepare($sql2);
                $result2->execute($data2);
				
				//delete from workflow_state
				 $data3 = array('workflowid' => $id);
                $sql3 = 'DELETE FROM workflow_state WHERE workflowid=:workflowid';
                $result3 = $connection2->prepare($sql3);
                $result3->execute($data3);
				
				//delete from workflow_transition
				/*
				 $data4 = array('workflowid' => $id);
                $sql4 = 'DELETE FROM workflow_transition WHERE workflowid=:workflowid';
                $result4 = $connection2->prepare($sql4);
                $result4->execute($data4);
				*/
				
            } catch (PDOException $e) {
                $URL .= '&return=error2';
                header("Location: {$URL}");
                exit();
            }
			
            $URLDelete = $URLDelete.'&return=success0&id='.$campaign_id.'&academic_year='.$academic_year.'&name='.$name;
            header("Location: {$URLDelete}");
			
        }
    }
}
