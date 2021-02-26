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


if (isActionAccessible($guid, $connection2, '/modules/Campaign/history_show.php') != false) {
    // Access denied
    $page->addError(__('You do not have access to this action.'));
} else {
    
    $page->breadcrumbs->add(__('Campaign Transition History'));

 
     if (isset($_GET['return'])) {
         returnProcess($guid, $_GET['return'], null, null);
     }

     $id=$_GET['id'];
     $formId = '';
    
// print_r($id);die();
    $sql1 = 'Select form_id FROM campaign WHERE id = '.$id.' ';
    $resultval1 = $connection2->query($sql1);
    $formid = $resultval1->fetch();
    //  echo $formid['form_id'];
    //  die();
    $formId = $formid['form_id'];
    
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
     echo __('Campaign Transition History List');
     echo '</h2>';

    
    
    $dataSet = $admissionGateway->getCampaignFormList($criteria, $formId);
    $table = DataTable::createPaginated('userManage', $criteria)->setID('expore_tbls');;
    
    $sqlw = 'Select workflow_id  FROM workflow_map WHERE campaign_id = '.$id.' ';
    $resultvalw = $connection2->query($sqlw);
    $woid = $resultvalw->fetch();
    $wid = $woid['workflow_id'];    
    $sql = 'Select a.*, b.notification FROM workflow_transition as a LEFT JOIN workflow_state as b ON a.to_state = b.id WHERE a.campaign_id = '.$id.' ';
    $resultval = $connection2->query($sql);
    $stats = $resultval->fetchAll();
    // echo '<pre>';
    //  print_r($dataSet);
    // echo '</pre>';
    
    echo '<input type="hidden" id="campId" value="'.$id.'"><input type="hidden" id="formId" value="'.$formId.'">';
     echo '<span id="statusButton"></span>';
     
     if(!empty($formId)){
        $sqlf = 'Select form_fields FROM wp_fluentform_forms WHERE id = '.$formId.' ';
        $resultvalf = $connection2->query($sqlf);
        $fluent = $resultvalf->fetch(); 
        $field = json_decode($fluent['form_fields']);
        $fields = array();
        }
   
   echo "<input type='hidden' name='form_id' data-cid='".$id."' id='form_id' value = '".$formId."' ";
  
    if(!empty($formId)){    
        $len = count($dataSet->data);
        $i = 0;
        $flag = TRUE;
        while($i<$len){
            $field = explode(",",$dataSet->data[$i]["field_name"]);
            $fieldval = explode(",",$dataSet->data[$i]["field_value"]);
        
            $jlen = count($field);
            $j = 0;
            if($dataSet->data[$i]["state"] == ''){
                $sqls = 'Select name FROM workflow_state WHERE workflowid = '.$wid.' AND order_wise = "1" ';
                $resultvals = $connection2->query($sqls);
                $states = $resultvals->fetch();
                $statename = $states['name'];
                $dataSet->data[$i]["state"] = $statename;
            }
            if($dataSet->data[$i]["created_at"] == ''){
                $sqls1 = 'Select ws.created_at FROM wp_fluentform_submissions AS ws WHERE ws.id = '.$dataSet->data[$i]["submission_id"].' AND  ws.form_id = '. $formId.'';
                $resultvals1 = $connection2->query($sqls1);
                $submited_form = $resultvals1->fetch();
                $created_at = $submited_form['created_at'];
                $dataSet->data[$i]["created_at"] = $created_at;
            }
             // echo  $dataSet->data[$i]["submission_id"];
            while($j<$jlen){
                $dataSet->data[$i][$field[$j]]=$fieldval[$j];
                if($flag){
                    $headcol = ucwords(str_replace("_", " ", $field[$j]));
                    if(($field[$j]=='first_name') || ($field[$j]=='email') ){ $table->addColumn(''.$field[$j].'', __(''.$headcol.''))
                        ->width('10%')
                        ->notSortable()
                        ->translatable();}
                   

                }
                $j++;
            }
            $flag = FALSE;
            unset($dataSet->data[$i]["field_name"],$dataSet->data[$i]["field_value"]);
            $i++;
        }
    

        $table->addColumn('state', __('Status'))
        ->width('10%')
        ->translatable();
        $table->addColumn('created_at', __('Submitted Date and time'))
        ->width('10%')
        ->translatable();

        
    }
   
    echo $table->render($dataSet);
}
?>

<script>
$(document).ready(function() {
    $('#expore_tbl').find("input[name='submission_id[]']").each(function() {
                $(this).addClass('include_cell');
                $(this).closest('tr').addClass('rm_cell');
    });
    $(document).on('change', '.include_cell', function() {
            if($(this).is(":checked")) {                       
                $(this).closest('tr').removeClass('rm_cell');
        } else {              
                $(this).closest('tr').addClass('rm_cell');
        }
    });
   
 });

</script>



<script>
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

    
//});    
</script>
<?php
