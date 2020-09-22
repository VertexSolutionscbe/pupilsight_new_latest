<?php
/*
Pupilsight, Flexible & Open School System
*/

include '../../pupilsight.php';

$id = $_GET['id'];
$wid = $_GET['wid'];
$URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/Campaign/edit_wf_transition.php&id='.$id.'&wid='.$wid;

if (isActionAccessible($guid, $connection2, '/modules/Campaign/edit_wf_transaitionProcess.php') != false) {
    $URL .= '&return=error0';
    header("Location: {$URL}");
} else {
    //Proceed!
    //Check if school year specified
    if ($id == '') {
        $URL .= '&return=error1';
        header("Location: {$URL}");
    } else {
        // echo '<pre>';
        // print_r($_POST);
        // echo '</pre>';
        // die(0);

  //Validate Inputs    
        $from_state = $_POST['from_state'];
        $to_state = $_POST['to_state'];
        $transition_display_name = $_POST['transition_display_name'];
        $auto_gen_inv = $_POST['auto_gen_inv'];
        $tansition_action = $_POST['tansition_action']; 
        $screen_tab_def = dateConvert($guid, $_POST['screen_tab_def']);
        $campaignid = $_POST['cid'];
        $user_permission = $_POST['user_permission'];
        
        $cuid = $_SESSION[$guid]['pupilsightPersonID'];


        if ($from_state == '' or $to_state == ''  or $transition_display_name == '' ) {
            $URL .= '&return=error1';
            header("Location: {$URL}");
        }  else {
            if(!empty($from_state)){
                foreach($from_state as $k=> $d){
                    $fstate = $d;
                    $tstate = $to_state[$k];
                    $transitionname = $transition_display_name[$k];
                    $autoinv = $auto_gen_inv[$k];
                    $screendef = $screen_tab_def[$k];
                    $userpermission = implode(',',$user_permission[$k]);
                    $tansitionaction = $tansition_action[$k];
                    $fn_fee_admission_setting_ids = $_POST['fn_fee_admission_setting_ids'][$k];
                  
                    $tid = $k;
                    if(!empty($from_state) && !empty($to_state)){
                        $newdata = array('campaign_id'=> $campaignid, 'id'=>$tid);
                        $sqln = 'SELECT * FROM workflow_transition WHERE  campaign_id=:campaign_id AND id=:id';
                        $resultn = $connection2->prepare($sqln);
                        $resultn->execute($newdata);
                        $valuesn = $resultn->fetch();
                       
                        if(empty($valuesn)){
                            $data = array('campaign_id' => $campaignid,'from_state' => $fstate, 'to_state' => $tstate, 'transition_display_name' => $transitionname,  'auto_gen_inv' => $autoinv,  'screen_tab_def' => $screendef, 'tansition_action' => $tansitionaction,'user_permission'=>$userpermission, 'fn_fee_admission_setting_ids' => $fn_fee_admission_setting_ids, 'cuid' => $cuid);
                            
                            $data1 = array('campaign_id' => $campaignid,'from_state' => $fstate, 'to_state' => $tstate);
                            $sql1 = 'SELECT * FROM workflow_transition WHERE campaign_id=:campaign_id AND (from_state=:from_state AND to_state=:to_state) OR (from_state=:to_state AND to_state=:from_state)';
                            $result1 = $connection2->prepare($sql1);
                            $result1->execute($data1);
                            $values = $result1->fetch();

                            if(empty($values)){

                                
                                $sql = "INSERT INTO workflow_transition SET campaign_id=:campaign_id,from_state=:from_state, to_state=:to_state,transition_display_name=:transition_display_name,auto_gen_inv=:auto_gen_inv, screen_tab_def=:screen_tab_def, 
                                tansition_action=:tansition_action,
                                user_permission=:user_permission, fn_fee_admission_setting_ids=:fn_fee_admission_setting_ids, cuid=:cuid";
                                $result = $connection2->prepare($sql);
                                $result->execute($data);
                            }
                        } else {
                            $data = array('campaign_id' => $campaignid,'from_state' => $fstate, 'to_state' => $tstate, 'transition_display_name' => $transitionname,  'auto_gen_inv' => $autoinv,  'screen_tab_def' => $screendef, 'tansition_action' => $tansitionaction,'user_permission'=>$userpermission, 'fn_fee_admission_setting_ids' => $fn_fee_admission_setting_ids, 'cuid' => $cuid, 'id'=>$tid);
                            
                        
                            $data1 = array('campaign_id' => $campaignid,'from_state' => $fstate, 'to_state' => $tstate, 'id'=>$tid);
                            $sql1 = 'SELECT * FROM workflow_transition WHERE campaign_id=:campaign_id AND id!=:id AND (from_state=:from_state AND to_state=:to_state) OR (from_state=:to_state AND to_state=:from_state)';
                            $result1 = $connection2->prepare($sql1);
                            $result1->execute($data1);
                            $values = $result1->fetch();

                            if(empty($values)){
                                print_r(user_permission);
                                $sql = "UPDATE workflow_transition SET campaign_id=:campaign_id,from_state=:from_state, to_state=:to_state,transition_display_name=:transition_display_name,auto_gen_inv=:auto_gen_inv, screen_tab_def=:screen_tab_def, tansition_action=:tansition_action,user_permission=:user_permission, fn_fee_admission_setting_ids=:fn_fee_admission_setting_ids, cuid=:cuid WHERE id=:id";
                                $result = $connection2->prepare($sql);
                                $result->execute($data);
                            }
                        }    
                        
                    }
                }
            } 
            $URL .= '&return=success0';
            header("Location: {$URL}");
        }
    }
}
