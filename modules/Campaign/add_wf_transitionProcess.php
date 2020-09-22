<?php
/*
Pupilsight, Flexible & Open School System
*/

include '../../pupilsight.php';

$URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_POST['address']).'/add_wf_transitions.php';

if (isActionAccessible($guid, $connection2, '/modules/Campaign/add_wf_transitions.php') == false) {
    $URL .= '&return=error0';
    header("Location: {$URL}");
} else {
    //Proceed!
    //Validate Inputs
   // `id``from_state``to_state``transition_display_name``tansition_action``cuid``auto_gen_inv``tansition_action``cuid`

//   echo "<pre>";
//   print_r($_REQUEST);exit;
    $from_state = $_POST['from_state'];
    $to_state = $_POST['to_state'];
    $transition_display_name = $_POST['transition_display_name'];
    $auto_gen_inv = $_POST['auto_gen_inv'];
    $tansition_action = $_POST['tansition_action']; 
    $screen_tab_def = dateConvert($guid, $_POST['screen_tab_def']);
    $user_permission = $_POST['user_permission'];
    $campaignid = $_POST['cid'];
   
    $cuid = $_SESSION[$guid]['pupilsightPersonID'];

  
    if ($from_state == '' or $to_state == ''  or $transition_display_name == '') {
        $URL .= '&return=error1';
        header("Location: {$URL}");
    }  else {
        //Check unique inputs for uniquness
        // try {
        //     $data = array('from_state' => $to_state, 'to_state' => $to_state);
        //     $sql = 'SELECT * FROM workflow_transition WHERE to_state=:to_state OR to_state=:to_state';
        //     $result = $connection2->prepare($sql);
        //     $result->execute($data);
        // } catch (PDOException $e) {
        //     $URL .= '&return=error2';
        //     header("Location: {$URL}");
        //     exit();
        // }

            //Check for other currents
                //Write to database
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
                        if(!empty($from_state) && !empty($to_state)){
                            $data = array('campaign_id' => $campaignid,'from_state' => $fstate, 'to_state' => $tstate, 'transition_display_name' => $transitionname,  'auto_gen_inv' => $autoinv,  'screen_tab_def' => $screendef, 'user_permission'=>$userpermission,'tansition_action' => $tansitionaction, 'fn_fee_admission_setting_ids' => $fn_fee_admission_setting_ids, 'cuid' => $cuid);
                            
                            $data1 = array('campaign_id' => $campaignid,'from_state' => $fstate, 'to_state' => $tstate);
                            $sql1 = 'SELECT * FROM workflow_transition WHERE campaign_id=:campaign_id AND (from_state=:from_state AND to_state=:to_state) OR (from_state=:to_state AND to_state=:from_state)';
                            $result1 = $connection2->prepare($sql1);
                            $result1->execute($data1);
                            $values = $result1->fetch();

                            if(empty($values)){
                                $sql = "INSERT INTO workflow_transition SET campaign_id=:campaign_id,from_state=:from_state, to_state=:to_state,transition_display_name=:transition_display_name,auto_gen_inv=:auto_gen_inv, screen_tab_def=:screen_tab_def, tansition_action=:tansition_action,user_permission=:user_permission, fn_fee_admission_setting_ids=:fn_fee_admission_setting_ids, cuid=:cuid";
                                $result = $connection2->prepare($sql);
                                $result->execute($data);
                            }
                        }
                    }
                } 

                //Last insert ID
                $AI = str_pad($connection2->lastInsertID(), 3, '0', STR_PAD_LEFT);

                // Update session vars so the user is warned if they're logged into a different year
                // if ($status == 'Current') {
                //     $_SESSION[$guid]['pupilsightSchoolYearIDCurrent'] = $AI;
                //     $_SESSION[$guid]['pupilsightSchoolYearNameCurrent'] = $name;
                //     $_SESSION[$guid]['pupilsightSchoolYearSequenceNumberCurrent'] = $sequenceNumber;
                // }
                if(!empty($alloseatname)){
              
                }    
                //$URL .= "&return=success0&editID=$AI";
                $URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/Campaign/index.php';
                header("Location: {$URL}");
                
       
    }
}
