<?php
/*
Pupilsight, Flexible & Open School System
*/

include '../../pupilsight.php';

$id = $_GET['id'];
$URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_POST['address']).'/wf_edit.php&id='.$id;

if (isActionAccessible($guid, $connection2, '/modules/Campaign/wf_edit.php') != false) {
    $URL .= '&return=error0';
    header("Location: {$URL}");
} else {
    // echo '<pre>';
    // print_r($_POST);
    // echo '</pre>';
    // die(0);
    $name = $_POST['name'];
    $code = $_POST['code'];
    $description = $_POST['description'];
    $statename = $_POST['statename'];
    $statecode = $_POST['statecode'];
    $serialorder = $_POST['serialorder'];
    $notification = $_POST['notification']; 
    $tid = $_POST['pupilsightTemplateIDs'];
    $cuid = $_SESSION[$guid]['pupilsightPersonID'];

    if(!empty($statename)){
        $data = array('name' => $name, 'description' => $description, 'code' => $code, 'id' => $id);
        $sql = "UPDATE workflow SET name=:name, description=:description, code=:code WHERE id=:id ";
        $result = $connection2->prepare($sql);
        $result->execute($data);

        foreach($statename as $k=>$s){
            $sname = $s;
            $scode = $statecode[$k];
            $order = $serialorder[$k];
            $notific = $notification[$k];
            $pupilsightTemplateIDs = $tid[$k];
            $sid = $k;
            if(!empty($sname) && !empty($scode)){
                $newdata = array('workflowid'=> $id, 'id'=>$sid);
                $sqln = 'SELECT * FROM workflow_state WHERE  workflowid=:workflowid AND id=:id';
                $resultn = $connection2->prepare($sqln);
                $resultn->execute($newdata);
                $valuesn = $resultn->fetch();
                if(empty($valuesn)){
                    $datas = array('code' => $scode, 'workflowid'=> $AI);
                    $sqls = 'SELECT * FROM workflow_state WHERE code=:code AND workflowid=:workflowid';
                    $results = $connection2->prepare($sqls);
                    $results->execute($datas);
                    $values = $results->fetch();

                    if(empty($values)){
                        $datass = array('name' => $sname, 'code' => $scode, 'order_wise' => $order, 'notification' => $notific, 'pupilsightTemplateIDs' => $pupilsightTemplateIDs, 'cuid' => $cuid,'workflowid'=>$id);
                        $sqlss = "INSERT INTO workflow_state SET name=:name, code=:code, order_wise=:order_wise,  notification=:notification, pupilsightTemplateIDs=:pupilsightTemplateIDs, cuid=:cuid,workflowid=:workflowid";
                        $resultss = $connection2->prepare($sqlss);
                        $resultss->execute($datass);

                        if(!empty($pupilsightTemplateIDs)){
                            $sq = "UPDATE pupilsightTemplate SET state = '1' where pupilsightTemplateID IN (".$pupilsightTemplateIDs.") ";
                            $connection2->query($sq);
                        }
                    }   
                } else {
                    $datas = array('code' => $scode, 'workflowid'=> $id, 'id'=>$sid);
                    $sqls = 'SELECT * FROM workflow_state WHERE code=:code AND workflowid=:workflowid AND id!=:id';
                    $results = $connection2->prepare($sqls);
                    $results->execute($datas);
                    $values = $results->fetch();

                    if(empty($values)){
                        $datass = array('name' => $sname, 'code' => $scode, 'order_wise' => $order, 'notification' => $notific, 'pupilsightTemplateIDs' => $pupilsightTemplateIDs,'cuid' => $cuid,'workflowid'=>$id, 'id'=>$sid);
                        $sqlss = "UPDATE workflow_state SET name=:name, code=:code, order_wise=:order_wise,  notification=:notification,pupilsightTemplateIDs=:pupilsightTemplateIDs, cuid=:cuid,workflowid=:workflowid WHERE id=:id";
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
    $URL .= '&return=success0';
    header("Location: {$URL}");
    
        
}

