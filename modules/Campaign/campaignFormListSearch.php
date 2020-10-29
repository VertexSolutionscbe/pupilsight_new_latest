<?php
/*
Pupilsight, Flexible & Open School System
*/
include '../../pupilsight.php';
use Pupilsight\Forms\Form;
use Pupilsight\Tables\DataTable;
use Pupilsight\Domain\Admission\AdmissionGateway;

require_once __DIR__ . '/moduleFunctions.php';

if (isActionAccessible($guid, $connection2, '/modules/Campaign/campaignFromListSearch.php') == false) {
    //Acess denied
    echo "<div class='error'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {  
       // echo '<pre>';
    // print_r($dataSet);
    // echo '</pre>';
    //Proceed!
    $fieldname = $_POST['field'];
    $searchby = $_POST['searchby'];
    $search = $_POST['search'];
    $range1 = $_POST['range1'];
    $range2 = $_POST['range2'];
    $cid = $_POST['cid'];
    $fid = $_POST['fid'];
    $application_id = $_POST['aid'];
    $applicationStatus = $_POST['stid'];
    $applicantName = $_POST['aname'];

    $admissionGateway = $container->get(AdmissionGateway::class);
    $criteria = $admissionGateway->newQueryCriteria()
    //->searchBy($admissionGateway->getSearchableColumns(), $search)
    ->sortBy(['id'])
    ->fromPOST();

    $sqlf = 'Select form_fields FROM wp_fluentform_forms WHERE id = '.$fid.' ';
    $resultvalf = $connection2->query($sqlf);
    $fluent = $resultvalf->fetch(); 
    $field = json_decode($fluent['form_fields']);
    $fields = array();
   

    $table = DataTable::createPaginated('userManage', $criteria);

    $sqlw = 'Select workflow_id  FROM workflow_map WHERE campaign_id = '.$cid.' ';
    $resultvalw = $connection2->query($sqlw);
    $woid = $resultvalw->fetch();
    $wid = $woid['workflow_id'];
    
    $sql = "SELECT `fd`.`submission_id`, GROUP_CONCAT(case when `fd`.`sub_field_name` IS NULL OR `fd`.`sub_field_name` = '' then `fd`.`field_name` else `fd`.`sub_field_name` end) AS `field_name`, GROUP_CONCAT(field_value) AS `field_value`, (select state from campaign_form_status where submission_id=`fd`.`submission_id` and status=1 order by id desc limit 1) AS `state` FROM `wp_fluentform_entry_details` AS `fd` WHERE fd.form_id = ".$fid." ";
    if($searchby == 'search'){
        $sql .= "AND (fd.field_name = '".$fieldname."' OR fd.sub_field_name = '".$fieldname."')  AND fd.field_value like '%".$search."%' ";
    } 
    if($searchby == 'range'){
        $sql .= "AND fd.field_name = '".$fieldname."' AND fd.field_value >= '".$range1."' AND fd.field_value <= '".$range2."' ";
    } 
    if(!empty($applicantName)){
        $sql .= "AND fd.field_name = 'student_name'  AND fd.field_value like '%".$applicantName."%' ";
    } 
    $sql .= " GROUP BY fd.submission_id";
    //echo $sql;
     $resultval = $connection2->query($sql);
    $rowdata = $resultval->fetchAll();

    $key = 0;
    //$dataSet = array();
    $subId = array();
    foreach($rowdata as $Key => $rd){
        $subId[] = $rd['submission_id'];
    }

    if($applicationStatus == 'Submitted'){
        $chkcs = 'SELECT GROUP_CONCAT(submission_id) AS sids FROM campaign_form_status WHERE campaign_id = '.$cid.' AND form_id = '.$fid.' ';
        $resultchkcs = $connection2->query($chkcs);
        $chkStaData = $resultchkcs->fetch();
        $sdata = explode(',', $chkStaData['sids']);
        $resultd=array_diff($subId,$sdata);
        $submissionIds = implode(',',$resultd);
        //print_r($result);
    } else {
        $submissionIds = implode(',',$subId);
    }
    
   
    $dataSet = $admissionGateway->getSearchCampaignFormList($criteria, $submissionIds, $application_id, $applicationStatus);
    // echo '<pre>';
    // print_r($dataSet);
    // echo '</pre>';

    $arrHeader = array();
    foreach($field as $fe){
        foreach($fe as $f){
            if (!empty($f->attributes)) {
                if ($f->attributes->name == 'student_name' || $f->attributes->class == 'show-in-grid') {
                    $arrHeader[] = $f->attributes->name;
                }
            }
        }
    }
    // echo '<pre>';
    // print_r($arrHeader);
    // echo '</pre>';
    echo '<input type="hidden" id="kountApplicantSearch" value='.count($dataSet).'>';

    $table->addCheckboxColumn('submission_id','')
    ->setClass('chkbox')
    ->context('Select')
    ->notSortable()
    ->width('10%');

    $table->addColumn('application_id',__('Application No'))
         ->width('10%');

    $len = count($dataSet->data);
        $i = 0;
        $flag = TRUE;
        while($i<$len){
            $sid = $dataSet->data[$i]["submission_id"];
            $sqlchk = 'Select ws.created_at, ws.pupilsightProgramID, ws.pupilsightYearGroupID FROM wp_fluentform_submissions AS ws WHERE ws.id = '.$sid.' AND  ws.form_id = '. $fid.'';
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
                $dataSet->data[$i]["workflowstate"] = 'Submitted';
            }

            //if($dataSet->data[$i]["created_at"] == ''){
                $sqls1 = 'Select ws.created_at FROM wp_fluentform_submissions AS ws WHERE ws.id = '.$dataSet->data[$i]["submission_id"].' AND  ws.form_id = '. $fid.'';
                $resultvals1 = $connection2->query($sqls1);
                $submited_form = $resultvals1->fetch();
                //$created_at = date('d-m-Y H:i:s', datetotime($submited_form['created_at']));
                $dt = new DateTime($submited_form['created_at']);
                $created_at= $dt->format('d-m-Y H:i:s');
                $dataSet->data[$i]["created_at"] = $created_at;
            //}

            $table->addColumn('workflowstate', __('Status'))
            ->width('10%')
            ->translatable();


            //$dt = array();
            while($j<$jlen){
                $dataSet->data[$i][$field[$j]]=$fieldval[$j];
                if($flag){
                    foreach($arrHeader as $ar){
                        $headcol = ucwords(str_replace("_", " ", $ar));
                        if($ar == 'file-upload'){
                            $table->addColumn(''.$ar.'', __(''.$headcol.''))
                                ->format(function ($dataSet) {
                                    if(!empty($dataSet['file-upload'])){
                                        return '<a href="'.$dataSet['file-upload'].'" download><i class="mdi mdi-download  mdi-24px download_icon"></i></a>';
                                    }
                                    
                                });
                        } elseif($ar == 'image-upload'){
                            $table->addColumn(''.$ar.'', __(''.$headcol.''))
                                ->format(function ($dataSet) {
                                    if(!empty($dataSet['image-upload'])){
                                        return '<a href="'.$dataSet['image-upload'].'" download><i class="mdi mdi-download  mdi-24px download_icon"></i></a>';
                                    }
                                
                                });
                        } else {
                            $table->addColumn(''.$ar.'', __(''.$headcol.''))
                            ->width('10%')
                            ->notSortable()
                            ->translatable();
                        }
                    }
                }
                $j++;
            }
            $flag = FALSE;
            unset($dataSet->data[$i]["field_name"],$dataSet->data[$i]["field_value"]);
            $i++;
        }

        // $len = count($newarr);
        // $i = 0;
        // $flag = TRUE;
        // while($i<$len){
        //     $field = explode(",",$newarr[$i]["field_name"]);
        //     $fieldval = explode(",",$newarr[$i]["field_value"]);
        //     $jlen = count($field);
        //     $j = 0;
        //     if($newarr[$i]["state"] == ''){
        //         $sqls = 'Select name FROM workflow_state WHERE workflowid = '.$wid.' AND order_wise = "1" ';
        //         $resultvals = $connection2->query($sqls);
        //         $states = $resultvals->fetch();
        //         $statename = $states['name'];
        //         $newarr[$i]["state"] = $statename;
        //     }



        //     //$dt = array();
        //     while($j<$jlen){
        //         $newarr[$i][$field[$j]]=$fieldval[$j];
        //         if($flag){
        //             $headcol = ucwords(str_replace("_", " ", $field[$j]));
        //             $table->addColumn(''.$field[$j].'', __(''.$headcol.''))
        //             ->width('10%')
        //             ->translatable();

        //         }
        //         $j++;
        //     }
        //     $flag = FALSE;
        //     unset($newarr[$i]["field_name"],$newarr[$i]["field_value"]);
        //     $i++;
        // }
     

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

// echo '<pre>';
// print_r($dataSet);
// echo '</pre>';
echo $table->render($dataSet);
    
//         foreach($newarr as $na){
//     $searchdata .= '<tr class="odd" role="row">
                  
//     <td class="p-2 sm:p-3 bulkCheckbox textCenter sorting_1">
//           <div class="inline flex-1 relative"><label class="leading-normal" for="submission_id1"></label> <input type="checkbox" name="submission_id[]" id="submission_id'.$na['submission_id'].'" value='.$na['submission_id'].'><br></div>  
//     </td>
     
//     <td class="p-2 sm:p-3">
//     '.$na['first_name'].'  
//     </td>
     
//     <td class="p-2 sm:p-3 hidden-1 sm:table-cell">
//     '.$na['last_name'].'  
//     </td>
     
//     <td class="p-2 sm:p-3 hidden-1 md:table-cell">
//     '.$na['email'].'  
//     </td>
     
//     <td class="p-2 sm:p-3 hidden-1 md:table-cell">
//     '.$na['submission_id'].'  
//     </td>
     
//     <td class="p-2 sm:p-3 hidden-1 md:table-cell">
//           test  
//     </td>
     
//     <td class="p-2 sm:p-3 hidden-1 md:table-cell">
//           c  
//     </td>
     
// </tr>';    
}
?>

<script>
$(document).ready(function() {

    var kount = $("#kountApplicantSearch").val();
    $("#kountApplicant").html('');
    $("#kountApplicant").html(kount);

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

    // $(document).on('click', '#pdf_export', function() {
    //     //var type = 'getApplicationInvoice';
    //     //var hrf = $("#clickAdmissionFeePayment").attr('href');
    //     var checked = $("input[name='submission_id[]']:checked").length;
    //     if (checked >= 1) {
    //         var favorite = [];
    //         $.each($("input[name='submission_id[]']:checked"), function() {
    //             var id = $(this).val();
    //             var nme = $("#"+id+"-subId").val();
    //             var link = document.createElement('a');
    //             link.href = "public/applicationpdf/"+id+"-application.pdf";
    //             link.download = nme+".pdf";
    //             link.click();
    //         });
            
    //     } else {
    //         alert('You Have to Select Applicant');
    //     }
        
    // });
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
</script>
<?php

