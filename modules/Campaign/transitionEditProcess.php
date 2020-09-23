<?php
/*
Pupilsight, Flexible & Open School System
*/

include '../../pupilsight.php';

$URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_POST['address']).'/transitions.php';

if (isActionAccessible($guid, $connection2, '/modules/Campaign/transitionEditProcess.php') == false) {
    $URL .= '&return=error0';
    header("Location: {$URL}");
} else {
    //Proceed!
    //Validate Inputs
   // `id``from_state``to_state``transition_display_name``tansition_action``cuid``auto_gen_inv``tansition_action``cuid`

//   echo "<pre>";
//   print_r($_REQUEST);exit;
    $table_name = $_POST['table_name'];
    $column_name = $_POST['column'];
    $campaignId = $_POST['campaign'];
    $fluent_form = $_POST['fluent_form'];
    $tansition_id = '1'; 
    
    $cuid = $_SESSION[$guid]['pupilsightPersonID'];

  
    if ($table_name == '' or $column_name == ''  or $campaignId == '' or $fluent_form == '') {
        $URL .= '&return=error1';
        header("Location: {$URL}");
    }  else {
          //Write to database
                if(!empty($table_name)){
                    foreach($table_name as $k=> $d){
                        $table_name = $d;
                        $column = $column_name[$k];
                        $campaign = $campaignId[$k];
                        $fluent = $fluent_form[$k];
                       if(!empty($table_name) && !empty($column_name)){
                            $data = array('campaign_id' => $campaign,'table_name' => $table_name, 'column_name' => $column, 'fluent_form_column_name' => $fluent,  'transition_id' => $tansition_id, 'cuid' => $cuid, 'id'=>$k);
                            
                            // $data1 = array('campaign_id' => $campaignid,'from_state' => $from_state, 'to_state' => $to_state);
                            // $sql1 = 'SELECT * FROM workflow_transition WHERE campaign_id=:campaign_id AND (from_state=:from_state AND to_state=:to_state) OR (from_state=:to_state AND to_state=:from_state)';
                            // $result1 = $connection2->prepare($sql1);
                            // $result1->execute($data1);
                            // $values = $result1->fetch();

                            // if(empty($values)){
                                $sql = "UPDATE campaign_transitions_form_map SET campaign_id=:campaign_id,table_name=:table_name, column_name=:column_name,fluent_form_column_name=:fluent_form_column_name,transition_id=:transition_id, cuid=:cuid WHERE id=:id";
                                $result = $connection2->prepare($sql);
                                $result->execute($data);
                            // }
                        }
                    }
                } 
                //die();
                $URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_POST['address']).'/transitionsList.php';
                header("Location: {$URL}");
                
       
    }
}
