<?php
 
 
/*
Gibbon, Flexible & Open School System
Copyright (C) 2010, Ross Parker

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program. If not, see <http://www.gnu.org/licenses/>.
*/
//echo '<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/1.3.4/jspdf.min.js"></script>';

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
    
    $page->breadcrumbs->add(__('Campaign Submitted Form List'));

 
     if (isset($_GET['return'])) {
         returnProcess($guid, $_GET['return'], null, null);
     }

     $id="";
     $formId = '';
     if(isset($_REQUEST['id'])?$id=$_REQUEST['id']:$id="" );

    $sql1 = 'Select offline_form_id FROM campaign WHERE id = '.$id.' ';
    $resultval1 = $connection2->query($sql1);
    $formid = $resultval1->fetch();
    //  echo $formid['form_id'];
    //  die();
    $formId = $formid['offline_form_id'];
    
    $search = isset($_GET['search'])? $_GET['search'] : '';
    
    $admissionGateway = $container->get(AdmissionGateway::class);
     $criteria = $admissionGateway->newQueryCriteria()
        ->searchBy($admissionGateway->getSearchableColumns(), $search)
        ->sortBy(['id'])
        ->fromPOST();
    $form = Form::create('filter', $_SESSION[$guid]['absoluteURL'].'/index.php', 'get');
    $form->setClass('noIntBorder fullWidth');
    $form->addHiddenValue('q', '/modules/'.$_SESSION[$guid]['module'].'/index.php'); 

    echo $form->getOutput();
 
     // QUERY
     echo '<h2>';
     echo __('Offline Campaign Submitted Form List');
     echo '</h2>';

    // echo $butt = '<i id="btnExport" title="Export PDF" class="far fa-file-pdf download_icon"></i> ';
    //echo $butt = '<i id="expore_csv" title="Export CSV" class="fas fa-file-csv download_icon"></i> ';
    echo "<div style='height:25px; margin-top:5px;'><div class='float-right mb-2'>
    <a style='display:none; ' href='".$_SESSION[$guid]['absoluteURL']."/fullscreen.php?q=/modules/Campaign/fee_make_payment.php&cid=".$id."' class='thickbox btn btn-primary' id='clickAdmissionFeePayment'>Fee Payment</a>
    <a style='display:none; margin-bottom:10px;'  class='btn btn-primary' id='admissionFeePayment'>Fee Payment</a>
    &nbsp;&nbsp;<a style=' margin-bottom:10px;' href='?q=/modules/Campaign/campaignFormList.php&id=".$id."'   class=' btn btn-primary' >Online Submitted List</a>
    &nbsp;&nbsp;<a style=' margin-bottom:10px;' href='?q=/modules/Campaign/formopen.php&id=".$id."'   class=' btn btn-primary' id='sendSMS'>Add</a>  &nbsp;&nbsp;<a style=' margin-bottom:10px;' href=''  data-toggle='modal' data-target='#large-modal-campaign_list' data-noti='2'  class='sendButton_campaign_list btn btn-primary' id='sendSMS'>Send SMS</a>";  
    echo "&nbsp;&nbsp;<a style=' margin-bottom:10px;' href='' data-toggle='modal' data-noti='1' data-target='#large-modal-campaign_list' class='sendButton_campaign_list btn btn-primary' id='sendEmail'>Send Email</a>";
    echo $butt = '<i id="expore_xl_campaign" title="Export Excel" class="mdi mdi-file-excel mdi-24px download_icon"></i><i id="pdf_export" title="Export PDF" class="mdi mdi-file-pdf mdi-24px download_icon"></i></div></div> <br>';

    //  print_r($criteria);
    //  die();
    // echo $butt = '<i id="btnExport" title="Export PDF" class="far fa-file-pdf download_icon"></i> ';
    // echo $butt = '<i id="expore_csv" title="Export CSV" class="fas fa-file-csv download_icon"></i> ';
    // echo $butt = '<i id="expore_xl" title="Export Excel" class="far fa-file-excel download_icon"></i> <br>';

    $dataSet = $admissionGateway->getCampaignFormList($criteria, $formId);
    $table = DataTable::createPaginated('userManage', $criteria)->setID('expore_tbls');;
    
    $sqlw = 'Select workflow_id  FROM workflow_map WHERE campaign_id = '.$id.' ';
    $resultvalw = $connection2->query($sqlw);
    $woid = $resultvalw->fetch();
    $wid = $woid['workflow_id'];
     
    // $sql = 'Select b.name as states, b.id as sid, b.notification FROM workflow_map as a LEFT JOIN workflow_state as b ON a.workflow_id = b.workflowid WHERE a.campaign_id = '.$id.' ';
    $sql = 'Select a.*, b.notification FROM workflow_transition as a LEFT JOIN workflow_state as b ON a.to_state = b.id WHERE a.campaign_id = '.$id.' ';
    $resultval = $connection2->query($sql);
    $stats = $resultval->fetchAll();
    // echo '<pre>';
    //  print_r($dataSet);
    // echo '</pre>';
    //  foreach($stats as $s){
    //     echo $butt = '<button class="btn btn-primary statesButton" data-toggle="modal" data-target="#large-modal" data-formid = '.$formId.' data-name="'.$s['transition_display_name'].'" data-sid='.$s['id'].' data-cid='.$id.' data-noti='.$s['notification'].' style="margin:5px" >'.ucwords($s['transition_display_name']).'</button>';
        
    //  }
    //  foreach($stats as $s){
    //     echo $butt = '<button class="btn btn-primary statesButton"  data-formid = '.$formId.' data-name="'.$s['transition_display_name'].'" data-sid='.$s['id'].' data-cid='.$id.' data-noti='.$s['notification'].' style="margin:5px" >'.ucwords($s['transition_display_name']).'</button>';
    //  }
    echo '<input type="hidden" id="campId" value="'.$id.'"><input type="hidden" id="formId" value="'.$formId.'">';
     echo '<span id="statusButton"></span>';
     
     if(!empty($formId)){
        $sqlf = 'Select form_fields FROM wp_fluentform_forms WHERE id = '.$formId.' ';
        $resultvalf = $connection2->query($sqlf);
        $fluent = $resultvalf->fetch(); 
        $field = json_decode($fluent['form_fields']);
        $fields = array();
        
        $showfields = '<div style="display:inline-flex;width:50%"><select class="filterCampaign" id="showfield1" style="width:50%"><option>Select Field for Filter</option>';
        foreach($field as $fe){
            foreach($fe as $f){
                if(!empty($f->fields)){
                    
                    foreach($f->fields as $fs){
                        if(!empty($fs->attributes)){
                            $lbl = $fs->attributes->placeholder;
                            if(empty($lbl)){
                                $lbl = $fs->attributes->name;
                            }
                            $lbl = str_replace("_"," ",$lbl);
                            if($lbl){
                                $fields[] = $fs->attributes->name;
                                $showfields .= '<option value="'.$fs->attributes->name.'" >'.ucwords($lbl).'</option>';
                            }
                        }
                    }

                } else {
                    if(!empty($f->attributes)){
                        $fields[] = $f->attributes->name;

                        $lbl = $f->attributes->placeholder;
                        if(empty($lbl)){
                            $lbl = $f->attributes->name;
                        }
                        $lbl = str_replace("_"," ",$lbl);
                        $showfields .= '<option value="'.$f->attributes->name.'" >'.ucwords($lbl).'</option>';
                    }
                }
                
            }
        }
        $showfields .= '</select>';
        echo $showfields;

        echo $showfields2 = '<select id="showfield2" class="filterCampaign" style="display:none;"><option>Select Search Criteria</option><option value="search">Search</option><option value="range">Range</option><option value="distance">Distance</option></select><input type="text" class="filterCampaign searchOpen searchby" name="searchby" id="" placeholder="Enter Your Search Data" style="display:none;"><input type="text" id="range1" name="rangestart" class="rangeOpen filterCampaign searchby" placeholder="Enter Your Start Range" style="display:none;"><input type="text" name="rangeend" class="rangeOpen filterCampaign searchby" id="range2" placeholder="Enter Your End Range" style="display:none;"><input type="hidden" id="campaignId" value='.$id.'><input type="hidden" id="formId" value='.$formId.'><button id="filterCampaign" class="btn btn-primary">Search</button>
        <br/>
          <span id="totalCount">&nbsp;Total Count : '.count($dataSet).'</span>
        </div>';
}

    // $sqlw = 'Select * FROM wp_fluentform_entry_details WHERE form_id = '.$formId.' ';
    // $resultvalw = $connection2->query($sqlw);
    // $formdets = $resultvalw->fetchAll();
    
   echo "<input type='hidden' name='form_id' data-cid='".$id."' id='form_id' value = '".$formId."'> ";
  


    if(!empty($formId)){
        $table->addCheckboxColumn('submission_id',__(''))
        ->addClass('chkbox')
        ->context('Select')
        ->notSortable()
        ->width('10%');


        $len = count($dataSet->data);
        $i = 0;
        $flag = TRUE;
        while($i<$len){
            $sid = $dataSet->data[$i]["submission_id"];
            $sqlchk = 'Select ws.created_at, ws.pupilsightProgramID, ws.pupilsightYearGroupID FROM wp_fluentform_submissions AS ws WHERE ws.id = '.$sid.' AND  ws.form_id = '. $formId.'';
                $resultchk = $connection2->query($sqlchk);
                $submited_formchk = $resultchk->fetch();

            if(!empty($submited_formchk['pupilsightProgramID']) && !empty($submited_formchk['pupilsightYearGroupID'])){
                $sqlprog = 'Select name FROM pupilsightProgram WHERE pupilsightProgramID = '.$submited_formchk['pupilsightProgramID'].'  ';
                $resultprog = $connection2->query($sqlprog);
                $prog = $resultprog->fetch();
                $progname = $prog['name'];

                $sqlcls = 'Select name FROM pupilsightYearGroup WHERE pupilsightYearGroupID = '.$submited_formchk['pupilsightYearGroupID'].' ';
                $resultcls = $connection2->query($sqlcls);
                $cls = $resultcls->fetch();
                $clsname = $cls['name'];

                $sqlname = 'Select GROUP_CONCAT(field_value) AS name FROM wp_fluentform_entry_details WHERE field_name = "names" AND submission_id = '.$sid.' ';
                $resultname = $connection2->query($sqlname);
                $aname = $resultname->fetch();
                $usrname = str_replace( ',', ' ', $aname['name']);

                $pdfvalue = $progname.'-'.$clsname.'-'.$usrname;
            } else {
                $pdfvalue = $dataSet->data[$i]["submission_id"];
            }
            //$value = 
            echo '<input type="hidden" id="'.$sid.'-subId" value="'.$pdfvalue.'" >';
            
            $field = explode(",",$dataSet->data[$i]["field_name"]);
            $fieldval = explode(",",$dataSet->data[$i]["field_value"]);
        
            $jlen = count($field);
            $j = 0;
            if($dataSet->data[$i]["workflowstate"] == ''){
                // $sqls = 'Select name FROM workflow_state WHERE workflowid = '.$wid.' AND order_wise = "1" ';
                // $resultvals = $connection2->query($sqls);
                // $states = $resultvals->fetch();
                // $statename = $states['name'];
                // $dataSet->data[$i]["state"] = $statename;
                $dataSet->data[$i]["workflowstate"] = 'Submitted';
            }

            echo $dataSet->data[$i]["created_at"];
            if($dataSet->data[$i]["created_at"] == ''){
                $sqls1 = 'Select ws.created_at FROM wp_fluentform_submissions AS ws WHERE ws.id = '.$dataSet->data[$i]["submission_id"].' AND  ws.form_id = '. $formId.'';
                $resultvals1 = $connection2->query($sqls1);
                $submited_form = $resultvals1->fetch();
                //$created_at = date('d-m-Y H:i:s', datetotime($submited_form['created_at']));
                $dt = new DateTime($submited_form['created_at']);
                $created_at= $dt->format('d-m-Y H:i:s');
                $dataSet->data[$i]["created_at"] = $created_at;
                
            }

            
          // echo  $dataSet->data[$i]["submission_id"];



            //$dt = array();
            while($j<$jlen){
                $dataSet->data[$i][$field[$j]]=$fieldval[$j];
                if($flag){
                    $headcol = ucwords(str_replace("_", " ", $field[$j]));
                    $table->addColumn(''.$field[$j].'', __(''.$headcol.''))
                    ->width('10%')
                    ->notSortable()
                    ->translatable();

                }
                $j++;
            }
            $flag = FALSE;
            unset($dataSet->data[$i]["field_name"],$dataSet->data[$i]["field_value"]);
            $i++;
        }


        $table->addColumn('workflowstate', __('Status'))
        ->width('10%')
        ->translatable();
        $table->addColumn('created_at', __('Submitted Date and time'))
        ->width('10%')
        ->translatable();

        $table->addActionColumn()
     
        ->addParam('submission_id')
        ->format(function ($person, $actions) use ($guid) { 
               $actions->addAction('View', __('Show History'))
               ->setTitle('form')
               ->setIcon('eye')
               ->setURL('modules/Campaign/history.php')
               ->modalWindow(1100, 550);

               $actions->addAction('form', __('Form'))
                ->setURL('/modules/Campaign/wplogin_form.php')
                ->modalWindow(1100, 550);
          
        });
        
    }
   

    // echo '<pre>';
    // print_r($dataSet);
    // echo '</pre>';
    echo $table->render($dataSet);

   
}
?>

<script>
$(document).ready(function() {
    $('#expore_tbl').find("input[name='submission_id[]']").each(function() {
        $(this).addClass('include_cell');
        $(this).closest('tr').addClass('rm_cell');
        var val = $(this).val();
        // alert(val);
        var type = 'chkInvoice';
        var thiscls = $(this);
        $.ajax({
            url: 'ajax_data.php',
            type: 'post',
            data: {val:val, type:type},
            async: true,
            success: function(response) {
                if(response == 'yes'){
                    thiscls.addClass('invoicemade');
                }
            }
        });
    });


    $(document).on('change', '.include_cell', function() {
        if($(this).is(":checked")) {                       
            $(this).closest('tr').removeClass('rm_cell');
        } else {              
            $(this).closest('tr').addClass('rm_cell');
        }
    });

    $(document).on('change', '.invoicemade', function() {
        if($(this).is(":checked")) {                       
            $("#admissionFeePayment").show();
        } else {              
            $("#admissionFeePayment").hide();
        }
    });

    $(document).on('click', '#admissionFeePayment', function() {
        var checked = $("input[name='submission_id[]']:checked").length;
        if (checked > 1) {
            alert('You Have to Select only One Applicant.');
        } else {
            var type = 'getApplicationInvoice';
            var hrf = $("#clickAdmissionFeePayment").attr('href');
            var favorite = [];
            $.each($("input[name='submission_id[]']:checked"), function() {
                favorite.push($(this).val());
            });
            var id = favorite.join(",");
            var newhrf = hrf+'&sid='+id+'&width=1100';
            $("#clickAdmissionFeePayment").attr('href',newhrf);
            $("#clickAdmissionFeePayment").click();
        }    
    });
   
 });

//jQuery.noConflict()(function($){
    $(document).on('click', "#checkall", function() {
        var id = 'all';
        var cid = $("#campId").val();
        var fid = $("#formId").val();
        var type = 'getCampaignStatusButton';
        $.ajax({
            url: 'ajax_data.php',
            type: 'post',
            data: { val: id, type: type, cid:cid, fid:fid },
            async: true,
            success: function(response) {
                $("#statusButton").html();
                $("#statusButton").html(response);
            }
        });
    });
    /*
    $(document).ready(function() {
        $('#expore_tbl').DataTable( {
            dom: 'Bfrtip',
            buttons: [
                'copy',
                'csv',
                'excel',
                'pdf',
                {
                    extend: 'print',
                    text: 'Print all (not just selected)',
                    exportOptions: {
                        modifier: {
                            selected: null
                        }
                    }
                }
            ],
            select: true
        } );
    } );
    */
//});    

    $(document).on('click', '#pdf_export', function() {
        //var type = 'getApplicationInvoice';
        //var hrf = $("#clickAdmissionFeePayment").attr('href');
        var checked = $("input[name='submission_id[]']:checked").length;
        if (checked >= 1) {
            var favorite = [];
            $.each($("input[name='submission_id[]']:checked"), function() {
                var id = $(this).val();
                var nme = $("#"+id+"-subId").val();
                var link = document.createElement('a');
                link.href = "public/applicationpdf/"+id+"-application.pdf";
                link.download = nme+".pdf";
                link.click();
            });
            
        } else {
            alert('You Have to Select Applicant');
        }
        
    });

    $(document).on('click', '#filterCampaign', function () {
        var field = $("#showfield1").val();
        var searchby = $("#showfield2").val();
        var search = $(".searchOpen").val();
        var range1 = $("#range1").val();
        var range2 = $("#range2").val();
        var cid = $("#campaignId").val();
        var fid = $("#formId").val();
        if (field != '' && searchby != '') {
            $.ajax({
                url: 'modules/Campaign/offline_campaignFormListSearch.php',
                type: 'post',
                data: { field: field, searchby: searchby, search: search, range1: range1, range2: range2, cid: cid, fid: fid },
                async: true,
                success: function (response) {
                    $("#expore_tbl").parent().html(response);
                }
            });
        }
    });
</script>
<?php
