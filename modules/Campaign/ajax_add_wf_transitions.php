<?php
/*
Pupilsight, Flexible & Open School System
*/
include '../../pupilsight.php';
use Pupilsight\Forms\Form;
use Pupilsight\Forms\DatabaseFormFactory;

if (isActionAccessible($guid, $connection2, '/modules/Campaign/ajax_add_wf_transitions.php') == false) {
    //Acess denied
    echo "<div class='error'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {  
    //Proceed!
    $aid = $_POST['ncid'];
    $wid = $_POST['wid'];

    $sqlf = 'SELECT a.form_id, a.id as cid FROM campaign AS a LEFT JOIN workflow_map AS b ON a.id = b.campaign_id WHERE b.workflow_id = '.$wid.' ';
    $resultf = $connection2->query($sqlf);
    $fdata = $resultf->fetch();
    $formId = $fdata['form_id'];
    $cid = $fdata['cid'];
    $sqlq = 'SELECT id,name FROM workflow_state WHERE workflowid = '.$wid.' ';
            $resultval = $connection2->query($sqlq);
				 $statuses = $resultval->fetchAll();
				//  $statuses=array();
				//  foreach ($rowdata as $dt) {
					
				// 	 $statuses[] = $dt['name'];
				//  }
               
               
    // $statuses = array(
    //     '1'     => __('Created'),
    //     '2'  => __('Submitted'),
    //     '3' => __('Stop'),
    // );
    
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
    $staff_list2[$dt['pupilsightStaffID']] = $dt['name'];
}
$staff_list= $staff_list2;  
    $data = ' <div id="seatdiv" data-id='.$aid.' class=" deltr' . $aid . '  row mb-1 "><div class=" col-sm newdes" ><div class=" ">
        <div class=" mb-1"></div><div class=" txtfield mb-1"><div class="flex-1 relative">
        <select id="from_state" name="from_state['.$aid.']" class="w-full txtfield">';
        foreach($statuses as $st){ 
            $data .= '<option value="'.$st['id'].'" >'.$st['name'].'</option>';
        }
        $data .= ' </select></div></div>
    </div></div>
      <div class=" col-sm newdes" >
    <div class=" ">
        <div class=" mb-1"></div><div class=" txtfield mb-1"><div class="flex-1 relative">
        <select id="to_state" name="to_state['.$aid.']" class="w-full txtfield">';
        foreach($statuses as $st){ 
            $data .= '<option value="'.$st['id'].'" >'.$st['name'].'</option>';
        }
        $data .= ' </select></div></div>
    </div></div>
     <div class=" col-sm newdes" >
    <div class=" ">
        <div class=" mb-1"></div><div class=" txtfield mb-1"><div class="flex-1 relative"><input type="text" id="transition_display_name" name="transition_display_name['.$aid.']" class="w-full txtfield"></div></div>
    </div></div>

    <div class=" col-sm newdes" >
    <div class=" ">
        <div class=" mb-1"></div><div class=" txtfield mb-1"><div class="flex-1 relative"><input type="checkbox" id="enable_remark" name="enable_remark['.$aid.']" class="w-full txtfield" value="1"></div></div>
    </div></div>
    
        <div class=" col-sm newdes" >
    <div class=" "><div class=" mb-1"></div><div class=" txtfield mb-1"><div class="flex-1 relative"><select id="tansition_action" name="tansition_action['.$aid.']" class="w-full txtfield">';
        foreach($tansition_action as $k=>$ts){ 
            $data .= '<option value="'.$k.'" >'.$ts.'</option>';
        }
        $data .= ' </select></div></div>
    </div>	</div>
    <div class=" col-sm newdes" >
    <div class=" ">
        <div class=" mb-1"></div><div class=" txtfield mb-1"><div class="flex-1 relative"><select id="screen/tab_def" name="screen_tab_def['.$aid.']" class="w-full txtfield">';
        foreach($defaul_open as $k=>$do){ 
            $data .= '<option value="'.$k.'" >'.$do.'</option>';
        }
        $data .= ' </select></div></div>
    </div>	 </div>

    <div class=" col-sm newdes" >
    <div class=" ">
        <div class=" mb-1"><div class="dropdown-mul-'.$aid.'"><select class="multipleStaff-'.$aid.'" id="user_permission" name="user_permission['.$aid.'][]" class="w-full txtfield " multiple>';
        foreach($staff_list as $s=>$staff){ 
            $data .= '<option value="'.$s.'" >'.$staff.'</option>';
        }
        $data .= ' </select></div></div>
    </div>	 </div>
    <div class=" col-sm newdes" >
    <div class=" ">
        <div class=" mb-1"></div><div class=" txtfield mb-1"><div class="flex-1 relative" ><select id="auto_gen_inv" name="auto_gen_inv['.$aid.']" data-sfid="'.$aid.'" class="w-full txtfield showFeeSettingButton">';
        foreach($auto_gen_inv as $k=>$au){ 
            $data .= '<option value="'.$k.'" >'.$au.'</option>';
        }
        $data .= ' </select></div></div>
    </div>	
    </div>
    <div class=" col-sm newdes  " style="display:inline-flex;">
        <div class=" feeSetting hiddencol">
            <div class=" mb-1"></div>
            <div class=" mb-1" style="padding: 4px 0 0 0px;"><a title="Fee Settings" href="'.$_SESSION[$guid]['absoluteURL'].'/fullscreen.php?q=/modules/Campaign/fee_setting.php&cid='.$cid.'&fid='.$formId.'&kid='.$aid.'&type=1&width=800" class="showFeeSetting thickbox" id="sfid'.$aid.'" style="display:none;"><i class="mdi mdi-plus-circle mdi-24px "></i></a><input type="hidden" name="fn_fee_admission_setting_ids['.$aid.']" id="feeSettingId-'.$aid.'" value="">
            </div>
        </div>
        
            <i title="Close" style="cursor:pointer;padding: 8px 10px" class="mdi mdi-close-circle mdi-24px delSeattr " data-id="' . $aid . '" ></i>
        
    </div>
    
</div>';
    

    echo $data;
  // update by bikash//
}
?>
<script type="text/javascript">var tb_pathToImage="lib/thickbox/loadingAnimation.gif";</script>
    	
        <script type="text/javascript" src="lib/thickbox/thickbox-compressed.js?v=18.0.01"></script>

        <script type="text/javascript" src="lib/tinymce/tinymce.min.js?v=18.0.01"></script>

        <script type="text/javascript">window.Pupilsight = {"config":{"datepicker":{"locale":"en-GB"},"thickbox":{"pathToImage":"http:\/\/localhost\/pupilsight\/lib\/thickbox\/loadingAnimation.gif"},"tinymce":{"valid_elements":"br[style],strong[style],em[style],span[style],p[style],address[style],pre[style],h1[style],h2[style],h3[style],h4[style],h5[style],h6[style],table[style],thead[style],tbody[style],tfoot[style],tr[style],td[style|colspan|rowspan],ol[style],ul[style],li[style],blockquote[style],a[style|target|href],img[style|class|src|width|height],video[style],source[style],hr[style],iframe[style|width|height|src|frameborder|allowfullscreen],embed[style],div[style],sup[style],sub[style]"},"sessionTimeout":{"sessionDuration":6400,"message":"Your session is about to expire: you will be logged out shortly."}}};</script>
<script src="assets/js/selectize.min.js"></script>
<script>
    $(document).ready(function () {
        var id = <?php echo $aid;?>;
      	$('.multipleStaff-'+id).selectize({
      		plugins: ['remove_button'],
      	});
    });
</script>        
