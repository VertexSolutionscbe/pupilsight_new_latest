<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Forms\Form;
use Pupilsight\Forms\DatabaseFormFactory;

if (isActionAccessible($guid, $connection2, '/modules/Campaign/add_wf_transitions.php') == false) {
    //Acess denied
    echo "<div class='error'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    //Proceed!
    
    $page->breadcrumbs
        ->add(__('Work flow Transition'), 'index.php')
        ->add(__('Add Work Flow Transition'));

    $editLink = '';
    if (isset($_GET['editID'])) {
        $editLink = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/Campaign/edit_wf_transition.php&id='.$_GET['editID'];
    }
    if (isset($_GET['return'])) {
        returnProcess($guid, $_GET['return'], $editLink, null);
    }

    
    $form = Form::create('WorkflowTransition', $_SESSION[$guid]['absoluteURL'].'/modules/Campaign/add_wf_transitionProcess.php')->addClass('newform');
    $form->setFactory(DatabaseFormFactory::create($pdo));

    $form->addHiddenValue('address', $_SESSION[$guid]['address']);
    $wid="";
	$cid="";
    if(isset($_REQUEST['wid'])?$wid=$_REQUEST['wid']:$wid="" );
    if(isset($_REQUEST['cid'])?$cid=$_REQUEST['cid']:$cid="" );
    $form->addHiddenValue('wid', $wid);
    $form->addHiddenValue('cid', $cid);
    
    $sqlf = 'SELECT form_id FROM campaign WHERE id = '.$cid.' ';
    $resultf = $connection2->query($sqlf);
    $fdata = $resultf->fetch();
    $formId = $fdata['form_id'];

    $sqlq = 'SELECT id, name FROM workflow_state WHERE workflowid = '.$wid.' ';
            $resultval = $connection2->query($sqlq);
				 $rowdata = $resultval->fetchAll();
				 $statuses=array();
				 foreach ($rowdata as $dt) {
					$statuses[$dt['id']] = $dt['name'];
				 }
               
                
    // $statuses = array(
    //     '1'     => __('Created'),
    //     '2'  => __('Submitted'),
    //     '3' => __('Stop'),
    // );
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
    echo '<h2>';
    echo __('Add WorkFlow Transitions');
    echo '</h2>';
    $row = $form->addRow();
    $col = $row->addColumn()->setClass('newdes');
    //$col->addButton(__('Add More Transition'))->addData('cid', '1')->addData('wid', $wid)->setID('addTransition')->addClass('btn btn-primary');
    $col->addContent('<a class="btn btn-primary" data-cid="1" data-wid="'.$wid.'" id="addTransition">Add More Transition</a>');

    $row = $form->addRow()->setID('seatdiv');
        $col = $row->addColumn()->setClass('newdes');
                $col->addLabel('from_state', __('From State'));
                $col->addSelect('from_state[1]')->addClass('txtfield')->fromArray($statuses)->required();
            
                $col = $row->addColumn()->setClass('newdes');
                $col->addLabel('to_state', __('To State'));
                $col->addSelect('to_state[1]')->addClass('txtfield')->fromArray($statuses)->required();   
        
                $col = $row->addColumn()->setClass('newdes');
                $col->addLabel('transition_display_name', __('Display Name'));
                $col->addTextField('transition_display_name[1]')->addClass('txtfield')->required();  

                $col = $row->addColumn()->setClass('newdes');
                $col->addLabel('tansition_action', __('Transition Action'));
                $col->addSelect('tansition_action[1]')->addClass('txtfield')->fromArray($tansition_action);

                $col = $row->addColumn()->setClass('newdes');
                $col->addLabel('screen/tab_def', __('Screen Or Tab default'));
                $col->addSelect('screen_tab_def[1]')->addClass('txtfield')->fromArray($defaul_open);

                $col = $row->addColumn()->setClass('newdes');
                $col->addLabel('user_permission', __('User Permission'));
                $col->addSelect('user_permission[1][]')->setId('selmuluser')->addClass('txtfield')->selectMultiple()->fromArray($staff_list);

                // $stdata = '';
                // if (!empty($getstaff)) {
                //     $sel = '';
                //     foreach ($getstaff as $cl) {
                //         $stdata .= '<option value="' . $cl['pupilsightStaffID'] . '" >' . $cl['officialName'] . '</option>';
                //     }
                // }

                $col->addContent('<div class="dropdown-mul-1" ><select style="display:none" name="user_permission[1][]" multiple >'.$stdata.'</select></div>');

                $col = $row->addColumn()->setClass('newdes');
                $col->addLabel('auto_gen_inv', __('Auto Generate Invoice'));
                $col->addSelect('auto_gen_inv[1]')->addClass('txtfield showFeeSettingButton')->fromArray($auto_gen_inv)->addData('sfid', '1');

                $col = $row->addColumn()->setClass('newdes feeSetting ');
                $col->addLabel('feeSetting', __('Fee Setting'));
                $col->addContent('<a title="Fee Settings" href="'.$_SESSION[$guid]['absoluteURL'].'/fullscreen.php?q=/modules/Campaign/fee_setting.php&cid='.$cid.'&fid='.$formId.'&kid=1&type=1&width=800" class="showFeeSetting thickbox" id="sfid1" style="display:none;"><i class="mdi mdi-plus-circle mdi-24px " ></i></a><input type="hidden" name="fn_fee_admission_setting_ids[1]" id="feeSettingId-1" value="">');

    // $row = $form->addRow();
    //         $col = $row->addColumn()->setClass('newdes');
    //         $col->addLabel('auto_gen_inv', __('Auto Generate Invoice'));
    //         $col->addSelect('auto_gen_inv')->addClass('txtfield')->fromArray($auto_gen_inv)->required();

    //         $col = $row->addColumn()->setClass('newdes');
    //         $col->addLabel('tansition_action', __('Transition Action'));
    //         $col->addSelect('tansition_action')->addClass('txtfield')->fromArray($tansition_action)->required();
            

    //         $col = $row->addColumn()->setClass('newdes');
    //         $col->addLabel('screen/tab_def', __('Screen Or Tab default'));
    //         $col->addSelect('screen/tab_def')->addClass('txtfield')->fromArray($defaul_open)->required();

   
    // $form->toggleVisibilityByClass('statusChange')->onSelect('status')->when('Current');
    // $direction = __('Past');

    // Display an alert to warn users that changing this will have an impact on their system.
    // $row = $form->addRow()->setClass('statusChange');
    // $row->addAlert(sprintf(__('Setting the status of this school year to Current will change the current school year %1$s to %2$s. Adjustments to the Academic Year can affect the visibility of vital data in your system. It\'s recommended to use the Rollover tool in User Admin to advance school years rather than changing them here. PROCEED WITH CAUTION!'), $_SESSION[$guid]['pupilsightSchoolYearNameCurrent'], $direction) );

    
               
           
           
   
    
        $row = $form->addRow()->setID('lastseatdiv');
        $row->addFooter();
        $row->addSubmit()->addClass('submit_align submt text-right');

    echo $form->getOutput();
  
}
?>
<style>
    .mb-1 label{
        height:43px;
    }

    .feeSetting{
        width: 15%;
    }
    
</style>
<script>
   $(document).ready(function () {
      	$('#selmuluser').selectize({
      		plugins: ['remove_button'],
      	});
    });
</script>