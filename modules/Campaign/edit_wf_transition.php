<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Forms\Form;
use Pupilsight\Forms\DatabaseFormFactory;

if (isActionAccessible($guid, $connection2, '/modules/Campaign/edit_wf_transition.php') != false) {
    //Acess denied
    echo "<div class='error'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    //Proceed!
    $page->breadcrumbs
        ->add(__('Work Flow Transition'), 'wf_tansition.php')
        ->add(__('Edit Work Flow Transition'));

    if (isset($_GET['return'])) {
        returnProcess($guid, $_GET['return'], null, null);
    }

    //Check if school year specified
    $id = $_GET['id'];
    $wid = $_GET['wid'];
    if ($id == '') {
        echo "<div class='error'>";
        echo __('You have not specified one or more required parameters.');
        echo '</div>';
    } else {
        try {
            $data = array('id' => $id);
            $sql = 'SELECT * FROM workflow_transition  WHERE campaign_id=:id';
            $result = $connection2->prepare($sql);
            $result->execute($data);
          
        } catch (PDOException $e) {
            echo "<div class='error'>".$e->getMessage().'</div>';
        }

        // if ($result->rowCount() < 1) {
        //     echo "<div class='error'>";
        //     echo __('The specified record cannot be found.');
        //     echo '</div>';
        // } else {
            //Let's go!
     $values = $result->fetchAll();  
     $rowlast = end($values);
     $lastid = $rowlast['id'];
/*
echo "<pre>";   
print_r($values);  */
// `id``from_state``to_state``transition_display_name``tansition_action``cuid``auto_gen_INV``tansition_action``cuid`
        $sqlf = 'SELECT form_id FROM campaign WHERE id = '.$id.' ';
        $resultf = $connection2->query($sqlf);
        $fdata = $resultf->fetch();
        $formId = $fdata['form_id'];
	
            $form = Form::create('WorkFlow', $_SESSION[$guid]['absoluteURL'].'/modules/Campaign/edit_wf_transitionProcess.php?id='.$id.'&wid='.$wid)->addClass('newform');
            $form->setFactory(DatabaseFormFactory::create($pdo));

            $form->addHiddenValue('address', $_SESSION[$guid]['address']);
            $sqlq = 'SELECT * FROM workflow_state WHERE workflowid = '.$wid.' ';
            $resultval = $connection2->query($sqlq);
				 $rowdata = $resultval->fetchAll();
				 $statuses=array();
				 foreach ($rowdata as $dt) {
					$statuses[$dt['id']] = $dt['name'];
                 }
                
        $tansition_action = array(
            '' => _('Select Transition Action'),
            '1' => _('Generate Student ID / Admission No.'),
            '2' => _('Program Registration (Based on Organisation selected)'),
            '3' => _('Assign Curriculum (Subjects/Elective) ')
        );
        $auto_gen_inv = array(
            '2'  => __('No'),
            '1'     => __('Yes'),
        );
        $defaul_open = array(
            '' => _('Select'),
            '1'     => __('Payment'),
            '2'  => __('Form'), 
            '3'  => __('History'),
            
        );
        $sqlp = 'SELECT a.pupilsightStaffID,a.staff_status AS stat,b.*, b.pupilsightPersonID AS stu_id , a.type, b.firstName AS name FROM pupilsightStaff AS a INNER JOIN pupilsightPerson AS b ON a.pupilsightPersonID = b.pupilsightPersonID';
        $resultp = $connection2->query($sqlp);
        $getstaff= $resultp->fetchAll();
 
        $staff_list=array();  
    $staff_list2=array();  
    $staff_list1=array(''=>'Select User');
    foreach ($getstaff as $dt) {
        if(!empty($dt['officialName'])){
            $staff_list2[$dt['pupilsightStaffID']] = $dt['officialName'];
        }
    }
    $staff_list= $staff_list2;  

    
    echo '<h2>';
    echo __('Edit WorkFlow Transitions');
    echo '</h2>';

    $form->addHiddenValue('cid', $id); 
    $row = $form->addRow();
    $col = $row->addColumn()->setClass('newdes');
    //$col->addButton(__('Add More Transition'))->addData('cid', $lastid)->addData('wid', $wid)->setID('addTransition')->addClass('bttnsubmt');
    $col->addContent('<a class="btn btn-primary" data-cid="'.$lastid.'" data-wid="'.$wid.'" id="addTransition">Add More Transition</a>');

    $i = '1';
    foreach($values as $k=> $st){
    $row = $form->addRow()->setID('seatdiv')->setClass('deltr'.$st['id']);
        $col = $row->addColumn()->setClass('newdes');
        if($i == '1'){
            $col->addLabel('from_state', __('From State'))->addClass('labelfsize');
        }
        $col->addSelect('from_state['.$st['id'].']')->addClass('txtfield')->fromArray($statuses)->required()->selected($st['from_state']);
    
        $col = $row->addColumn()->setClass('newdes');
        if($i == '1'){
            $col->addLabel('to_state', __('To State'))->addClass('labelfsize');
        }
        $col->addSelect('to_state['.$st['id'].']')->addClass('txtfield')->fromArray($statuses)->required()->selected($st['to_state']);   

        $col = $row->addColumn()->setClass('newdes');
        if($i == '1'){
            $col->addLabel('transition_display_name', __('Display Name'))->addClass('labelfsize');
        }
        $col->addTextField('transition_display_name['.$st['id'].']')->addClass('txtfield')->required()->setValue($st['transition_display_name']);  

        
        $col = $row->addColumn()->setClass('newdes');
        if($i == '1'){
            $col->addLabel('tansition_action', __('Transition Action'))->addClass('labelfsize');
        }
        $col->addSelect('tansition_action['.$st['id'].']')->addClass('txtfield')->fromArray($tansition_action)->selected($st['tansition_action']);

        $col = $row->addColumn()->setClass('newdes');
        if($i == '1'){
            $col->addLabel('screen/tab_def', __('Screen Or Tab default'))->addClass('labelfsize');
        }
        $col->addSelect('screen_tab_def['.$st['id'].']')->addClass('txtfield')->fromArray($defaul_open)->selected($st['screen_tab_def']);

        $col = $row->addColumn()->setClass('newdes');
        if($i == '1'){
            $col->addLabel('user_permission', __('User Permission'))->addClass('labelfsize');
        }
        $seluser = explode(',',$st['user_permission']);
        $stdata = '';
        if (!empty($getstaff)) {
            $sel = '';
            foreach ($getstaff as $cl) {
                if(in_array($cl['pupilsightStaffID'], $seluser)){
                    $sel = 'selected';
                } else {
                    $sel = '';
                }
                $stdata .= '<option value="' . $cl['pupilsightStaffID'] . '" '.$sel.'>' . $cl['officialName'] . '</option>';
            }
        }
        
        //$col->addSelect('user_permission['.$st['id'].'][]')->setId('selmuluser'.$st['id'].'')->addClass('txtfield selboxusr')->fromArray($staff_list)->selectMultiple()->selected($seluser);
        $col->addContent('<div class="dropdown-mul-1" ><select style="display:none" name="user_permission['.$st['id'].'][]" multiple >
        '.$stdata.'</select></div>');

       // $col->addContent('<div class="clickOnStaffDIv getAllStaff" id="AllStaffDiv'.$st['id'].'" data-id="'.$st['id'].'"  style="outline:0px;"></div><input type="text" id="showTypeStaff'.$st['id'].'" class="getAllStaff" data-id="'.$st['id'].'" value="" style="display:none; margin-top: 4px;width: 100%;"><input type="hidden"  id="selmuluser'.$st['id'].'" class="getAllStaff" data-id="'.$st['id'].'" value=""><input type="hidden" name="user_permission['.$st['id'].'][]" id="selmuluser'.$st['id'].'" class="getAllStaff" data-id="'.$st['id'].'" value=""><div class="clsShowStaff showAllStaff-'.$st['id'].'"></div>');


        $col = $row->addColumn()->setClass('newdes');
        if($i == '1'){
            $col->addLabel('auto_gen_inv', __('Auto Generate Invoice'))->addClass('labelfsize');
        }
        $col->addSelect('auto_gen_inv['.$st['id'].']')->addClass('txtfield showFeeSettingButton')->fromArray($auto_gen_inv)->selected($st['auto_gen_inv'])->addData('sfid', $st['id']);

        if($st['auto_gen_inv'] == '1'){
            $hidecol = '';
        } else {
            $hidecol = 'style="display:none;"';
        }    
            $col = $row->addColumn()->setClass('newdes feeSetting  ');
            if($i == '1'){
                $col->addLabel('feeSetting', __(''));
            }
            $col->addContent('<a title="Fee Settings" href="'.$_SESSION[$guid]['absoluteURL'].'/fullscreen.php?q=/modules/Campaign/fee_setting.php&cid='.$id.'&fid='.$formId.'&kid='.$st['id'].'&type=2&width=800" class="showFeeSetting thickbox" id="sfid'.$st['id'].'" '.$hidecol.'><i class="fas fa-plus " ></i></a><input type="hidden" name="fn_fee_admission_setting_ids['.$st['id'].']" id="feeSettingId-'.$st['id'].'" value="'.$st['fn_fee_admission_setting_ids'].'">');
        

    
        $col = $row->addColumn()->setClass('newdes del_style');
        if($i == '1'){
            $col->addContent('<div class="dte mb-1"  style="font-size: 25px;  margin-top: 45px; "><i style="cursor:pointer" class="far fa-times-circle delSeattr_trans " data-cid="'.$id.'" data-wid="'.$wid.'" data-id="'.$st['id'].'" data-sid="'.$st['id'].'"></i></div>'); 
        } else {
            $col->addContent('<div class="dte mb-1"  style="font-size: 25px; "><i style="cursor:pointer" class="far fa-times-circle delSeattr_trans " data-cid="'.$id.'" data-wid="'.$wid.'" data-id="'.$st['id'].'" data-sid="'.$st['id'].'"></i></div>'); 
        }
        
        
        $i++;
    }    
    
            $row = $form->addRow()->setID('lastseatdiv');
                $row->addFooter();
                $row->addSubmit()->addClass('submt sumit_css');

            echo $form->getOutput();
        //}
    }
}

?>
<style>
    
    .del_style {
        width: 14%;
    }

    .mb-1 label{
        height:43px;
    }

    .multiselect {
        width: 120px;
        height: 35px;
    }
    .multiselect-container{
        height: 300px;
        overflow: auto;
    }

    .feeSetting{
        width: 15%;
    }

    
</style>

<script>
    $('.dropdown-mul-1').dropdown({
        limitCount: 40,
        multipleMode: 'label',
        choice: function () {
       
        }
    });

</script>