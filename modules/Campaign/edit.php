<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Forms\Form;
use Pupilsight\Forms\DatabaseFormFactory;

if (isActionAccessible($guid, $connection2, '/modules/Campaign/edit.php') == false) {
    //Acess denied
    echo "<div class='error'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    //Proceed!
    $page->breadcrumbs
        ->add(__('Manage Admission'), 'index.php')
        ->add(__('Edit Admission'));

    if (isset($_GET['return'])) {
        returnProcess($guid, $_GET['return'], null, null);
    }

    //Check if school year specified
    $id = $_GET['id'];
    if ($id == '') {
        echo "<div class='error'>";
        echo __('You have not specified one or more required parameters.');
        echo '</div>';
    } else {
        try {
            $data = array('id' => $id);
            $sql = 'SELECT * FROM campaign WHERE id=:id';
            $result = $connection2->prepare($sql);
            $result->execute($data);
        } catch (PDOException $e) {
            echo "<div class='error'>" . $e->getMessage() . '</div>';
        }

        if ($result->rowCount() != 1) {
            echo "<div class='error'>";
            echo __('The specified record cannot be found.');
            echo '</div>';
        } else {
            //Let's go!
            $kount = 0;
            $totalseats = 0;
            $values = $result->fetch();
            $campaignid = $id;
            $data1 = array('campaignid' => $campaignid);
            $sql1 = 'SELECT * FROM seatmatrix WHERE campaignid=:campaignid';
            $result1 = $connection2->prepare($sql1);
            $result1->execute($data1);
            $seatvalues = $result1->fetchall();
            $kount = count($seatvalues);
            if (!empty($seatvalues)) {
                foreach ($seatvalues as $ts) {
                    $totalseats += $ts['seats'];
                }
            }

            $sqlp = 'SELECT pupilsightProgramID, name FROM pupilsightProgram ';
            $resultp = $connection2->query($sqlp);
            $rowdataprog = $resultp->fetchAll();

            $program = array();
            $program2 = array();
            $program1 = array('' => 'Select Program');
            foreach ($rowdataprog as $key => $dt) {
                $program2[$dt['pupilsightProgramID']] = $dt['name'];
            }
            $program = $program1 + $program2;

            $seatProgs = array();
            if (!empty($values['pupilsightProgramID'])) {
                $seatProgs = explode(',', $values['pupilsightProgramID']);
            }

            $seatsPro = array('' => 'Select Program');
            if(!empty($program2)){
                foreach($program2 as $k => $cls){
                    if(in_array($k, $seatProgs)){
                        $seatsPro[$k] = $cls;
                    }
                }
            }

            $sqlrt = 'SELECT id, name FROM fn_fees_receipt_template_master ';
            $resultrt = $connection2->query($sqlrt);
            $templateData = $resultrt->fetchAll();

            $receiptTemplate = array();
            $receiptTemplate2 = array();
            $receiptTemplate1 = array('' => 'Select Receipt Template');
            foreach ($templateData as $key => $rt) {
                $receiptTemplate2[$rt['id']] = $rt['name'];
            }
            $receiptTemplate = $receiptTemplate1 + $receiptTemplate2;

            $sqle = 'SELECT pupilsightTemplateID, name, entities FROM pupilsightTemplate WHERE type = "Email" AND FIND_IN_SET("Admission",entities)';
            $resulte = $connection2->query($sqle);
            $eTempData = $resulte->fetchAll();

            $emailTemplate=array();  
            $emailTemplate2=array();  
            $emailTemplate1=array(''=>'Select Email Template');
            foreach ($eTempData as $key => $dt) {
                $emailTemplate2[$dt['pupilsightTemplateID']] = $dt['name'];
            }
            $emailTemplate= $emailTemplate1 + $emailTemplate2; 

            $sqls = 'SELECT pupilsightTemplateID, name, entities FROM pupilsightTemplate WHERE type = "Sms"  AND FIND_IN_SET("Admission",entities)';
            $results = $connection2->query($sqls);
            $sTempData = $results->fetchAll();

            $smsTemplate=array();  
            $smsTemplate2=array();  
            $smsTemplate1=array(''=>'Select Sms Template');
            foreach ($sTempData as $key => $dt) {
                $smsTemplate2[$dt['pupilsightTemplateID']] = $dt['name'];
            }
            $smsTemplate= $smsTemplate1 + $smsTemplate2; 

            $sqlcs = 'SELECT id, series_name, type FROM fn_fee_series WHERE type IN ("Application","Admission")';
            $resultcs = $connection2->query($sqlcs);
            $seriesData = $resultcs->fetchAll();

            $applicationSeries = array();
            $applicationSeries2 = array();
            $applicationSeries1 = array('' => 'Select Series');

            $admissionSeries = array();
            $admissionSeries2 = array();
            $admissionSeries1 = array('' => 'Select Series');

            foreach ($seriesData as $key => $cst) {
                if ($cst['type'] == 'Application') {
                    $applicationSeries2[$cst['id']] = $cst['series_name'];
                } else {
                    $admissionSeries2[$cst['id']] = $cst['series_name'];
                }
            }
            $applicationSeries = $applicationSeries1 + $applicationSeries2;
            $admissionSeries = $admissionSeries1 + $admissionSeries2;

            $pid = $values['pupilsightProgramID'];

            $sqlf = 'SELECT a.* FROM fn_fee_structure AS a LEFT JOIN fn_fees_class_assign AS b ON a.id = b.fn_fee_structure_id WHERE a.pupilsightSchoolYearID = "' . $values['academic_id'] . '" AND b.pupilsightProgramID IN (' . $pid . ') AND b.pupilsightYearGroupID IN (' . $values['classes'] . ') GROUP BY a.id';
            $resultf = $connection2->query($sqlf);
            $feeGroupData = $resultf->fetchAll();
            $feeGroups = array();
            $feeGroups2 = array();
            $feeGroups1 = array('' => 'Select Fee Structure');
            foreach ($feeGroupData as $fg) {
                $feeGroups2[$fg['id']] = $fg['name'];
            }

            $feeGroups = $feeGroups1 + $feeGroups2;



            $sql = 'SELECT a.*, b.name, c.name as progname, c.pupilsightProgramID FROM pupilsightProgramClassSectionMapping AS a LEFT JOIN pupilsightYearGroup AS b ON a.pupilsightYearGroupID = b.pupilsightYearGroupID LEFT JOIN pupilsightProgram AS c ON a.pupilsightProgramID = c.pupilsightProgramID WHERE a.pupilsightSchoolYearID = '.$values['academic_id'].' AND  a.pupilsightProgramID IN (' . $pid . ') GROUP BY a.pupilsightProgramID, a.pupilsightYearGroupID';
            $result = $connection2->query($sql);
            $classesdata = $result->fetchAll();
            $classes = array();
            foreach ($classesdata as $ke => $cl) {
                $classes[(int)$cl['pupilsightYearGroupID'].'-'.(int)$cl['pupilsightProgramID']] = $cl['name'].' - '.$cl['progname'];
                $classes2[$cl['pupilsightYearGroupID']] = $cl['name'];
            }

            $sqlpc = 'SELECT * FROM campaign_prog_class WHERE campaign_id = "' . $id . '" ';
            $resultpc = $connection2->query($sqlpc);
            $campProClsData = $resultpc->fetchAll();

            $setclass = array();
            if(!empty($campProClsData)){
                foreach($campProClsData as $cpc){
                    $setclass[] = $cpc['pupilsightYearGroupID'].'-'.$cpc['pupilsightProgramID'];
                }
            } else {
                if (!empty($values['classes'])) {
                    $setclassnew = explode(',', $values['classes']);
                    foreach($setclassnew as $sc){
                        $setclass[] = (int)$sc.'-'.(int)$values['pupilsightProgramID'];
                    }
                }
            }
           
            
            $setclass2 = array();
            if (!empty($values['classes'])) {
                $setclass2 = explode(',', $values['classes']);
            }

            // $seatsCls = array('' => 'Select Class');
            // if(!empty($classes2)){
            //     foreach($classes2 as $k => $cls){
            //         if(in_array($k, $setclass2)){
            //             $seatsCls[$k] = $cls;
            //         }
            //     }
            // }
            
            //print_r($seatsCls);
            $form = Form::create('Admission', $_SESSION[$guid]['absoluteURL'] . '/modules/' . $_SESSION[$guid]['module'] . '/editProcess.php?id=' . $id)->addClass('newform');
            $form->setFactory(DatabaseFormFactory::create($pdo));

            $form->addHiddenValue('address', $_SESSION[$guid]['address']);

            $sqlq = 'SELECT pupilsightSchoolYearID, name FROM pupilsightSchoolYear ';
            $resultval = $connection2->query($sqlq);
            $rowdata = $resultval->fetchAll();
            $academic = array();
            $ayear = '';
            if (!empty($rowdata)) {
                $ayear = $rowdata[0]['name'];
                foreach ($rowdata as $dt) {
                    $academic[$dt['pupilsightSchoolYearID']] = $dt['name'];
                }
            }
            $form->addHiddenValue('ayear', $values['academic_year']);
            $statuses = array(
                '1'     => __('Draft'),
                '2'  => __('Publish'),
                '3' => __('Stop'),
            );
            $reg_status = array(
                '1'     => __('No'),  //public// page_for-1(db)
                '2'  => __('Yes'),   // private// page_for-2(db)

            );

            $feechk = array(
                '' => __('Select Setting'),
                '1'     => __('Submit & Generate Application Fee'),
                '2'  => __('Submit & Pay Application Fee'),
            );

            echo '<h2>';
            echo __('Edit Campaign');
            echo '</h2>';

            $row = $form->addRow();


            $col = $row->addColumn()->setClass('newdes');
            $col->addLabel('name', __('Name'));
            $col->addTextField('name')->addClass('txtfield')->required()->setValue($values['name']);

            $col = $row->addColumn()->setClass('newdes');
            $col->addLabel('academic_year', __('Academic Year'));
            $col->addSelect('academic_id')->addClass('txtfield')->fromArray($academic)->required()->selected($values['academic_id']);

            $col = $row->addColumn()->setClass('newdes');
            $col->addLabel('pupilsightProgramID', __('Program'))->addClass('dte');
            $col->addSelect('pupilsightProgramID')->setId('getMultiClassByProgCamp')->addClass('txtfield')->fromArray($program)->required()->selected($seatProgs)->selectMultiple();

            $col = $row->addColumn()->setClass('newdes showClass');
            $col->addLabel('classes', __('Class'))->addClass('dte');
            $col->addSelect('classes')->setId('showMultiClassByProg')->addClass('txtfield')->placeholder('Select Class')->selectMultiple()->fromArray($classes)->selected($setclass)->required();

            $col = $row->addColumn()->setClass('newdes hiddencol');
            $col->addLabel('', __(''))->addClass('dte');
            $col->addSelect('')->setId('showMultiSecByProgCls')->addClass('txtfield')->placeholder('Select Class')->selectMultiple();
            // $col = $row->addColumn()->setClass('newdes');
            //         $col->addLabel('seats', __('Seats'))->addClass('dte');
            //         $col->addTextField('seats')->addClass('txtfield')->readonly()->setValue($values['seats']);
            $form->addHiddenValue('seats', $values['seats']);



            $row = $form->addRow();

            $col = $row->addColumn()->setClass('newdes');
            $col->addLabel('limit_max_users', __('Limit Max Users'))->addClass('dte');
            $col->addNumber('limit_apply_form')->setId('numAllow')->addClass('txtfield')->setValue($values['limit_apply_form']);

            $col = $row->addColumn()->setClass('newdes');
            $col->addLabel('status', __('Status'));
            $col->addSelect('status')->addClass('txtfield')->fromArray($statuses)->required()->selected($values['status']);



            $col = $row->addColumn()->setClass('newdes');
            $col->addLabel('start_date', __('Start Date'))->addClass('dte');
            $col->addDate('start_date')->addClass('txtfield')->readonly()->required()->setValue(dateConvertBack($guid, $values['start_date']));

            $col = $row->addColumn()->setClass('newdes');
            $col->addLabel('end_date', __('End Date'))->addClass('dte');
            $col->addDate('end_date')->addClass('txtfield')->readonly()->required()->setValue(dateConvertBack($guid, $values['end_date']));

            $row = $form->addRow();

            $col = $row->addColumn()->setClass('newdes');
            $col->addLabel('reg_req', __('Registration Required'));
            $col->addSelect('reg_req')->addClass('txtfield')->fromArray($reg_status)->required()->selected($values['page_for']);

            $col = $row->addColumn()->setClass('newdes');
            $col->addLabel('application_series_id', __('Application Series'));
            $col->addSelect('application_series_id')->addClass('txtfield')->fromArray($applicationSeries)->selected($values['application_series_id']);

            $col = $row->addColumn()->setClass('newdes');
            $col->addLabel('admission_series_id', __('Admission Series'));
            $col->addSelect('admission_series_id')->addClass('txtfield')->fromArray($admissionSeries)->selected($values['admission_series_id']);

            $col = $row->addColumn()->setClass('newdes');
            $col->addLabel('fn_fee_structure_id', __('Fee Group'));
            $col->addSelect('fn_fee_structure_id')->setId('getFeeStructureByProgClass')->addClass('txtfield')->fromArray($feeGroups)->selected($values['fn_fee_structure_id']);

            $row = $form->addRow();

            $col = $row->addColumn()->setClass('newdes');
            $col->addLabel('fn_fees_receipt_template_id', __('Receipt Template'));
            $col->addSelect('fn_fees_receipt_template_id')->addClass('txtfield')->fromArray($receiptTemplate)->selected($values['fn_fees_receipt_template_id']);

            $col = $row->addColumn()->setClass('newdes');
            $col->addLabel('email_template_id', __('Email Template'));
            $col->addSelect('email_template_id')->fromArray($emailTemplate)->selected($values['email_template_id'])->addClass('txtfield');
           
            $col = $row->addColumn()->setClass('newdes');
            $col->addLabel('sms_template_id', __('Sms Template'));
            $col->addSelect('sms_template_id')->fromArray($smsTemplate)->selected($values['sms_template_id'])->addClass('txtfield');

            $col = $row->addColumn()->setClass('newdes');
            $col->addLabel('is_fee_generate', __('Application Fee Settings'));
            $col->addSelect('is_fee_generate')->fromArray($feechk)->addClass('txtfield')->selected($values['is_fee_generate'])->addClass('txtfield');

        $row = $form->addRow();

            $col = $row->addColumn()->setClass('newdes');
            $col->addLabel('is_publish_parent', __('Publish For Parent'));
            $col->addCheckBox('is_publish_parent')->addClass('txtfield')->setValue('1')->checked($values['is_publish_parent']);

            $col = $row->addColumn()->setClass('newdes');
            $col->addLabel('allow_multiple_submission', __('Allow Multiple Submission'));
            $col->addCheckBox('allow_multiple_submission')->addClass('txtfield')->setValue('1')->checked($values['allow_multiple_submission']);

            $col = $row->addColumn()->setClass('newdes');
            $col->addLabel('', __(''))->addClass('dte');

            $col = $row->addColumn()->setClass('newdes');
            $col->addLabel('', __(''))->addClass('dte');

            

        $row = $form->addRow();
            $col = $row->addColumn()->setClass('newdes');
            $col->addLabel('description', __('Description'));
            $col->addTextArea('description')->addClass('txtfield')->setRows(4)->setValue($values['description']);

            $row = $form->addRow();
            $col = $row->addColumn()->setClass('newdes');
            $col->addLabel('', __('Application Forms'));
            if (!empty($values['form_id'])) {
                $col->addContent('<b>Online Form : </b><a class="thickbox " href="fullscreen.php?q=/modules/Campaign/view_selected_campaign_form.php&id=' . $id . '&width=1100&amp;height=550"><i title="View Online Form" class="mdi mdi-eye-outline mdi-24px  px-1"></i></a><a class="thickbox " href="fullscreen.php?q=/modules/Campaign/edit_selected_campaign_form.php&id=' . $id . '&width=1100&amp;height=550"><i title="Edit Online Form" class="mdi mdi-pencil-box-outline mdi-24px"></i></a><a class="thickbox " href="fullscreen.php?q=/modules/Campaign/change_online_campaign_form.php&id=' . $id . '&width=1100&amp;height=550"><i title="Edit Online Form" class="mdi mdi-content-copy mdi-24px"></i></a>');
            } else {
                $col->addContent('<b>Online Form : </b><a href="index.php?q=/modules/Campaign/fluent.php&id=' . $id . '"><i title="Add Online Form" class="mdi mdi-plus-outline mdi-24px"></i></a>');
            }

            //$row = $form->addRow();        
            $col = $row->addColumn()->setClass('newdes');
            $col->addLabel('', __(''));
            if (!empty($values['offline_form_id'])) {
                $col->addContent('<b>Offline Form : </b><a class="thickbox " href="fullscreen.php?q=/modules/Campaign/view_offline_campaign_form.php&id=' . $id . '&width=1100&amp;height=550"><i title="View Offline Form" class="mdi mdi-eye-outline mdi-24px  px-1"></i></a><a class="thickbox " href="fullscreen.php?q=/modules/Campaign/edit_offline_campaign_form.php&id=' . $id . '&width=1100&amp;height=550"><i title="Edit Offline Form" class="mdi mdi-pencil-box-outline mdi-24px"></i></a><a class="thickbox " href="fullscreen.php?q=/modules/Campaign/change_offline_campaign_form.php&id=' . $id . '&width=1100&amp;height=550"><i title="Edit Offline Form" class="mdi mdi-content-copy mdi-24px"></i></a>');
            } else {
                $col->addContent('<b>Offline Form : </b><a href="index.php?q=/modules/Campaign/offline_fluent.php&id=' . $id . '"><i title="Add Offline Form" class="mdi mdi-plus-outline mdi-24px"></i></a>');
            }

            $row = $form->addRow()->setID('seatdiv');
            $col = $row->addColumn()->setClass('newdes');
            //$col->addButton(__('Add More Seat Matrix'))->addData('cid', $kount)->setID('addSeats')->addClass('bttnsubmt');
            $col->addContent('<a class="btn btn-secondary text-white" id="addSeats" data-cid=' . $kount . '>Add More Seat Matrix</a>');

            $col = $row->addColumn()->setClass('newdes');
            $col->addLabel('Total Seats : ', __('Total Seats : ' . $totalseats . ''))->addClass('showSeats');
            // $row->addButton('Add Seats')->addData('class', 'addSeats')->addClass('submt');    

            if (!empty($seatvalues)) {
                $i = '1';
                foreach ($seatvalues as $k => $sv) {
                    if(!empty($sv['pupilsightProgramID'])){
                        $sqlpc = 'SELECT b.pupilsightYearGroupID, b.name FROM campaign_prog_class AS a LEFT JOIN pupilsightYearGroup AS b ON a.pupilsightYearGroupID = b.pupilsightYearGroupID WHERE a.campaign_id = "' . $id . '" AND a.pupilsightProgramID = '.$sv['pupilsightProgramID'].' ';
                        $resultpc = $connection2->query($sqlpc);
                        $campProClsSeatData = $resultpc->fetchAll();
                        $setcls = array();
                        foreach ($campProClsSeatData as $ke => $cl) {
                        $setcls[$cl['pupilsightYearGroupID']] = $cl['name'];
                        }

                        $row = $form->addRow()->setClass('deltr' . $k);

                        $col = $row->addColumn()->setClass('newdes');
                        if ($i == '1') {
                            $col->addLabel('progname', __('Program'));
                        }
                        $col->addSelect('progname[' . $sv['id'] . ']')->fromArray($seatsPro)->addClass('progSeat txtfield')->selected($sv['pupilsightProgramID'])->addData('id',$k);

                        $col = $row->addColumn()->setClass('newdes');
                        if ($i == '1') {
                            $col->addLabel('seatname', __('Class'));
                        }
                        $col->addSelect('seatname[' . $sv['id'] . ']')->setID('classSeat'.$k)->fromArray($setcls)->addClass(' txtfield')->selected($sv['pupilsightYearGroupID']);

                        $col = $row->addColumn()->setClass('newdes');
                        if ($i == '1') {
                            $col->addLabel('seat', __('Campaign Seat
                            '))->addClass('dte');
                        }
                        $col->addNumber('seatallocation[' . $sv['id'] . ']')->addClass('txtfield kountseat szewdt')->setValue($sv['seats']);
                        $col->addContent('<div class="dte mb-1"  style="font-size: 25px; padding:  6px 0 0px 4px"><i style="cursor:pointer" class="far fa-times-circle delSeattr " data-id="' . $k . '" data-sid="' . $sv['id'] . '"></i></div>');

                        $i++;
                    }
                }
            }
            $row = $form->addRow()->setID('lastseatdiv');
            $row->addFooter();
            $row->addSubmit();

            echo $form->getOutput();
        }
    }
}
?>

<style>
    .multiselect {
        width: 212px;
        height: 35px;
    }

    .multiselect-container {
        height: 300px;
        overflow: auto;
    }
</style>
<script>
    $("#start_date").datepicker({
        //minDate: 0,
        onClose: function(selectedDate) {
            $("#end_date").datepicker("option", "minDate", selectedDate);
        }
    });

    $(document).ready(function() {
        $('#getMultiClassByProgCamp').selectize({
            plugins: ['remove_button'],
        });
    });

    $(document).ready(function() {
        $('#showMultiClassByProg').selectize({
            plugins: ['remove_button'],
        });
    });

  
    $(document).on('change', '#getMultiClassByProgCamp', function () {
        var aid = $("#academic_id").val();
        var id = $(this).val();
        var type = 'getClassforCampaign';
        $('#showMultiClassByProg').selectize()[0].selectize.destroy();
        $("#getFeeStructureByProgClass").html('');
        $.ajax({
            url: 'ajax_data.php',
            type: 'post',
            data: { val: id, type: type, aid:aid },
            async: true,
            success: function (response) {
                $("#showMultiClassByProg").html('');
                $("#showMultiClassByProg").html(response);
                $("#showMultiClassByProg").parent().children('.LV_validation_message').remove();
                $('#showMultiClassByProg').selectize({
                    plugins: ['remove_button'],
                });
                
            }
        });
    });

    $(document).on('change', '#showMultiClassByProg', function () {
        var id = $(this).val();
        var aid = $('#academic_id').val();
        var pid = $('#getMultiClassByProgCamp').val();
        var type = 'getFeeStructure';
        $.ajax({
            url: 'ajax_data.php',
            type: 'post',
            data: { val: id, type: type, pid: pid, aid: aid },
            async: true,
            success: function (response) {
                $("#getFeeStructureByProgClass").html('');
                $("#getFeeStructureByProgClass").html(response);
            }
        });
        var type = 'getProgForSeats';
        $.ajax({
            url: 'ajax_data.php',
            type: 'post',
            data: { val: id, type: type, pid: pid, aid: aid },
            async: true,
            success: function (response) {
                $("#progSeat").html('');
                $("#progSeat").html(response);
            }
        });
    });

    $(document).on('change', '.progSeat', function () {
        var rid = $(this).attr('data-id');
        var id = $(this).val();
        var aid = $('#academic_id').val();
        var cid = $('#showMultiClassByProg').val();
        var type = 'getClassForSeats';
        if(id != ''){
            $.ajax({
                url: 'ajax_data.php',
                type: 'post',
                data: { val: id, type: type, cid: cid, aid: aid },
                async: true,
                success: function (response) {
                    $("#classSeat"+rid).html('');
                    $("#classSeat"+rid).html(response);
                }
            });
        }
    });

    $(document).on('change', '.seatProg', function () {
        var rid = $(this).attr('data-id');
        var id = $(this).val();
        var aid = $('#academic_id').val();
        var cid = $('#showMultiClassByProg').val();
        var type = 'getClassForSeats';
        $.ajax({
            url: 'ajax_data.php',
            type: 'post',
            data: { val: id, type: type, cid: cid, aid: aid },
            async: true,
            success: function (response) {
                $("#seatclass"+rid).html('');
                $("#seatclass"+rid).html(response);
            }
        });
    });
</script>