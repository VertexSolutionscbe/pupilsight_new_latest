<?php
 

use Pupilsight\Services\Format;

use Pupilsight\Forms\Form;
use Pupilsight\Tables\DataTable;
use Pupilsight\Domain\Admission\AdmissionGateway;


// Module includes
require_once __DIR__ . '/moduleFunctions.php';


if (isActionAccessible($guid, $connection2, '/modules/Campaign/campaignFormList.php') == false) {
    // Access denied
    $page->addError(__('You do not have access to this action.'));
} else {
    
    $page->breadcrumbs->add(__('Applicant List'));

 
     if (isset($_GET['return'])) {
         returnProcess($guid, $_GET['return'], null, null);
     }

     $id="98";
     $formId = '';
     //if(isset($_REQUEST['id'])?$id=$_REQUEST['id']:$id="" );

    $sql = 'SELECT id, form_id, name FROM campaign ';
    $result = $connection2->query($sql);
    $rowdatacamp = $result->fetchAll();

    $campaign=array();  
    $campaign2=array();  
    $campaign1=array(''=>'Select Campaign');
    foreach ($rowdatacamp as $dt) {
        $campaign2[$dt['id']] = $dt['name'];
    }
    $campaign= $campaign1 + $campaign2;    

    if($_POST){
        $id = $_POST['campaign_id'];
        $sql1 = 'Select form_id FROM campaign WHERE id = '.$id.' ';
        $resultval1 = $connection2->query($sql1);
        $formid = $resultval1->fetch();
        
        $formId = $formid['form_id'];
    } else {
        $id = '0';
        $formId = '0';
    }

    
    
    $search = isset($_GET['search'])? $_GET['search'] : '';
    
    $admissionGateway = $container->get(AdmissionGateway::class);
     $criteria = $admissionGateway->newQueryCriteria()
        ->searchBy($admissionGateway->getSearchableColumns(), $search)
        ->sortBy(['id'])
        ->fromPOST();
    $form = Form::create('filter','');
    $form->setClass('noIntBorder fullWidth');
    $form->addHiddenValue('q', '/modules/'.$_SESSION[$guid]['module'].'/index.php'); 
    
    $row = $form->addRow();
    $col = $row->addColumn()->setClass('newdes');
        $col->addLabel('campaign_id', __('Campaign'));
        $col->addSelect('campaign_id')->fromArray($campaign)->selected($id)->required()->placeholder();

    $col = $row->addColumn()->setClass('newdes');   
    $col->addLabel('', __(''));
    $col->addSubmit()->addClass('left_align'); 
    
    $col = $row->addColumn()->setClass('newdes');   
    $col->addLabel('', __(''));
    
    $col = $row->addColumn()->setClass('newdes');   
    $col->addLabel('', __(''));
   

    echo $form->getOutput();

    
     // QUERY
     echo '<h2>';
     echo __('Applicant List');
     echo '</h2>';

    echo "<div style='height:25px; margin-top:5px;'><div class='float-right mb-2'>
    <a  data-href='".$_SESSION[$guid]['absoluteURL']."/index.php?q=/modules/Campaign/transitionImportProcess.php' class=' btn btn-primary' id='saveApplicant'>Admit</a></div></div> ";
    
    $dataSet = $admissionGateway->getApplicationFormList($criteria, $formId);
    $table = DataTable::createPaginated('userManage', $criteria)->setID('expore_tbls');;
    
    $sqlw = 'Select workflow_id  FROM workflow_map WHERE campaign_id = '.$id.' ';
    $resultvalw = $connection2->query($sqlw);
    $woid = $resultvalw->fetch();
    $wid = $woid['workflow_id'];
     
    // $sql = 'Select b.name as states, b.id as sid, b.notification FROM workflow_map as a LEFT JOIN workflow_state as b ON a.workflow_id = b.workflowid WHERE a.campaign_id = '.$id.' ';
    $sql = 'Select a.*, b.notification FROM workflow_transition as a LEFT JOIN workflow_state as b ON a.to_state = b.id WHERE a.campaign_id = '.$id.' ';
    $resultval = $connection2->query($sql);
    $stats = $resultval->fetchAll();
    
    echo '<input type="hidden" id="campId" value="'.$id.'"><input type="hidden" id="formId" value="'.$formId.'">';
    //  echo '<span id="statusButton"></span>';
     
    if(!empty($formId)){
        $sqlf = 'Select form_fields FROM wp_fluentform_forms WHERE id = '.$formId.' ';
        $resultvalf = $connection2->query($sqlf);
        $fluent = $resultvalf->fetch(); 
        $field = json_decode($fluent['form_fields']);
        $fields = array();
        // echo '<pre>';
        // print_r($field);
        // echo '</pre>';
        $showfields = '<div style="display:inline-flex;"><select class="filterCampaign" id="showfield1"><option>Select Field for Filter</option>';
        foreach($field as $fe){
            foreach($fe as $f){
                if(!empty($f->attributes)){
                    $fields[] = $f->attributes->name;
                    $showfields .= '<option value="'.$f->attributes->name.'" >'.ucwords($f->attributes->name).'</option>';
                }
            }
        }
        $showfields .= '</select>';
        echo $showfields;

        echo $showfields2 = '<select id="showfield2" class="filterCampaign" style="display:none;"><option>Select Search Criteria</option><option value="search">Search</option><option value="range">Range</option><option value="distance">Distance</option></select><input type="text" class="filterCampaign searchOpen searchby" name="searchby" id="" placeholder="Enter Your Search Data" style="display:none;"><input type="text" id="range1" name="rangestart" class="rangeOpen filterCampaign searchby" placeholder="Enter Your Start Range" style="display:none;"><input type="text" name="rangeend" class="rangeOpen filterCampaign searchby" id="range2" placeholder="Enter Your End Range" style="display:none;"><input type="hidden" id="campaignId" value='.$id.'><input type="hidden" id="formId" value='.$formId.'><button id="filterCampaign" class="btn btn-primary">Search</button></div>';
    }
   

    // $sqlw = 'Select * FROM wp_fluentform_entry_details WHERE form_id = '.$formId.' ';
    // $resultvalw = $connection2->query($sqlw);
    // $formdets = $resultvalw->fetchAll();
    
   echo "<input type='hidden' name='form_id' data-cid='".$id."' id='form_id' value = '".$formId."' ";
  
    $arrHeader = array();
    foreach ($field as $fe) {
        foreach ($fe as $f) {
            if (!empty($f->attributes)) {
                $arrHeader[] = $f->attributes->name;
            }
        }
    }


   if (!empty($formId)) {
        $table->addCheckboxColumn('submission_id', __(''))
            ->addClass('chkbox')
            ->context('Select')
            ->notSortable()
            ->width('10%');

        $table->addColumn('application_id', __('Application No'))
            ->width('10%');





        $len = count($dataSet->data);
        $i = 0;
        $flag = TRUE;
        while ($i < $len) {
            $sid = $dataSet->data[$i]["submission_id"];
            $sqlchk = 'Select ws.created_at, ws.pupilsightProgramID, ws.pupilsightYearGroupID FROM wp_fluentform_submissions AS ws WHERE ws.id = ' . $sid . ' AND  ws.form_id = ' . $formId . '';
            $resultchk = $connection2->query($sqlchk);
            $submited_formchk = $resultchk->fetch();

            if (!empty($submited_formchk['pupilsightProgramID']) && !empty($submited_formchk['pupilsightYearGroupID'])) {
                $sqlprog = 'Select name FROM pupilsightProgram WHERE pupilsightProgramID = ' . $submited_formchk['pupilsightProgramID'] . '  ';
                $resultprog = $connection2->query($sqlprog);
                $prog = $resultprog->fetch();
                $progname = $prog['name'];

                $sqlcls = 'Select name FROM pupilsightYearGroup WHERE pupilsightYearGroupID = ' . $submited_formchk['pupilsightYearGroupID'] . ' ';
                $resultcls = $connection2->query($sqlcls);
                $cls = $resultcls->fetch();
                $clsname = $cls['name'];

                $sqlname = 'Select GROUP_CONCAT(field_value) AS name FROM wp_fluentform_entry_details WHERE field_name = "names" AND submission_id = ' . $sid . ' ';
                $resultname = $connection2->query($sqlname);
                $aname = $resultname->fetch();
                $usrname = str_replace(',', ' ', $aname['name']);

                $pdfvalue = $progname . '-' . $clsname . '-' . $usrname;
            } else {
                $pdfvalue = $dataSet->data[$i]["submission_id"];
            }
            //$value = 
            echo '<input type="hidden" id="' . $sid . '-subId" value="' . $pdfvalue . '" >';

            $field = explode(",", $dataSet->data[$i]["field_name"]);
            $fieldval = explode(",", $dataSet->data[$i]["field_value"]);

            $jlen = count($field);
            $j = 0;
            if ($dataSet->data[$i]["workflowstate"] == '') {
                // $sqls = 'Select name FROM workflow_state WHERE workflowid = '.$wid.' AND order_wise = "1" ';
                // $resultvals = $connection2->query($sqls);
                // $states = $resultvals->fetch();
                // $statename = $states['name'];
                $dataSet->data[$i]["workflowstate"] = 'Submitted';
            }

            echo $dataSet->data[$i]["created_at"];
            if ($dataSet->data[$i]["created_at"] == '') {
                $sqls1 = 'Select ws.created_at FROM wp_fluentform_submissions AS ws WHERE ws.id = ' . $dataSet->data[$i]["submission_id"] . ' AND  ws.form_id = ' . $formId . '';
                $resultvals1 = $connection2->query($sqls1);
                $submited_form = $resultvals1->fetch();
                //$created_at = date('d-m-Y H:i:s', datetotime($submited_form['created_at']));
                $dt = new DateTime($submited_form['created_at']);
                $created_at = $dt->format('d-m-Y H:i:s');
                $dataSet->data[$i]["created_at"] = $created_at;
            }


            // echo  $dataSet->data[$i]["submission_id"];



            //$dt = array();

            $table->addColumn('workflowstate', __('Status'))
                ->width('10%')
                ->translatable();


            while ($j < $jlen) {
                $dataSet->data[$i][$field[$j]] = $fieldval[$j];
                if ($flag) {
                    foreach ($arrHeader as $ar) {
                        $headcol = ucwords(str_replace("_", " ", $ar));
                        if ($ar == 'file-upload') {
                            $table->addColumn('' . $ar . '', __('' . $headcol . ''))
                                ->format(function ($dataSet) {
                                    if ($dataSet['file-upload'] != '') {
                                        return '<a href="' . $dataSet['file-upload'] . '" download><i class="mdi mdi-download  mdi-24px download_icon"></i></a>';
                                    }
                                });
                        } elseif ($ar == 'image-upload') {
                            $table->addColumn('' . $ar . '', __('' . $headcol . ''))
                                ->format(function ($dataSet) {
                                    if ($dataSet['image-upload'] != '') {
                                        return '<a href="' . $dataSet['image-upload'] . '" download><i class="mdi mdi-download  mdi-24px download_icon"></i></a>';
                                    }
                                });
                        } else {
                            $table->addColumn('' . $ar . '', __('' . $headcol . ''))
                                ->width('10%')
                                ->notSortable()
                                ->translatable();
                        }
                    }
                }
                $j++;
            }
            $flag = FALSE;
            unset($dataSet->data[$i]["field_name"], $dataSet->data[$i]["field_value"]);
            $i++;
        }




        $table->addColumn('created_at', __('Submitted Date and time'))
            ->width('10%')
            ->translatable();

        
    }
   

    // echo '<pre>';
    // print_r($dataSet);
    // echo '</pre>';
    echo $table->render($dataSet);

   
}
?>
<script>
$(document).on('click', "#saveApplicant", function() {
    var favorite = [];
    $.each($("input[name='submission_id[]']:checked"), function() {
        favorite.push($(this).val());
    });
    var submit_id = favorite.join(", ");
    var url = $(this).attr('data-href');
    if (submit_id) {
        $.ajax({
            url: url,
            type: 'post',
            data: { subid: submit_id},
            async: true,
            success: function(response) {
                // alert('Your Applicant Admitted Successfully! Click Ok to Continue');
                // location.reload();
            }
        });
    } else {
        alert('You have to Select Applicant');
    }
});
</script>
