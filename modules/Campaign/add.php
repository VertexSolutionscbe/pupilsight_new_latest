<?php
/*
Pupilsight, Flexible & Open School System
*/


use Pupilsight\Forms\Form;
use Pupilsight\Forms\DatabaseFormFactory;

if (isActionAccessible($guid, $connection2, '/modules/Campaign/add.php') == false) {
    //Acess denied
    echo "<div class='error'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    //Proceed!

    $page->breadcrumbs
        ->add(__('Manage Campaign'), 'index.php')
        ->add(__('Add Campaign'));

    $editLink = '';
    if (isset($_GET['editID'])) {
        $editLink = $_SESSION[$guid]['absoluteURL'] . '/index.php?q=/modules/Campaign/edit.php&id=' . $_GET['editID'];
    }
    if (isset($_GET['return'])) {
        returnProcess($guid, $_GET['return'], $editLink, null);
    }
    //if ($pupilsightSchoolYearID == '' or $pupilsightSchoolYearID == $_SESSION[$guid]['pupilsightSchoolYearID']) {
    $pupilsightSchoolYearID = $_SESSION[$guid]['pupilsightSchoolYearID'];
    $pupilsightSchoolYearName = $_SESSION[$guid]['pupilsightSchoolYearName'];
    //}

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

    $sqle = 'SELECT pupilsightTemplateID, name, entities FROM pupilsightTemplate WHERE type = "Email" AND FIND_IN_SET("Admission",entities)';
    $resulte = $connection2->query($sqle);
    $eTempData = $resulte->fetchAll();

    $emailTemplate = array();
    $emailTemplate2 = array();
    $emailTemplate1 = array('' => 'Select Email Template');
    foreach ($eTempData as $key => $dt) {
        $emailTemplate2[$dt['pupilsightTemplateID']] = $dt['name'];
    }
    $emailTemplate = $emailTemplate1 + $emailTemplate2;

    $sqls = 'SELECT pupilsightTemplateID, name, entities FROM pupilsightTemplate WHERE type = "Sms"  AND FIND_IN_SET("Admission",entities)';
    $results = $connection2->query($sqls);
    $sTempData = $results->fetchAll();

    $smsTemplate = array();
    $smsTemplate2 = array();
    $smsTemplate1 = array('' => 'Select Sms Template');
    foreach ($sTempData as $key => $dt) {
        $smsTemplate2[$dt['pupilsightTemplateID']] = $dt['name'];
    }
    $smsTemplate = $smsTemplate1 + $smsTemplate2;

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

    $form = Form::create('Campaign', $_SESSION[$guid]['absoluteURL'] . '/modules/' . $_SESSION[$guid]['module'] . '/addProcess.php')->addClass('newform');
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
    $form->addHiddenValue('ayear', $ayear);

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
    echo __('Add Campaign');
    echo '</h2>';
    $row = $form->addRow();

    $col = $row->addColumn()->setClass('newdes');
    $col->addLabel('name', __('Name'));
    $col->addTextField('name')->addClass('txtfield')->required();

    $col = $row->addColumn()->setClass('newdes');
    $col->addLabel('academic_year', __('Academic Year'));
    $col->addSelect('academic_id')->addClass('txtfield')->fromArray($academic)->selected($pupilsightSchoolYearID)->required();

    $col = $row->addColumn()->setClass('newdes');
    $col->addLabel('pupilsightProgramID', __('Program'))->addClass('dte');
    $col->addSelect('pupilsightProgramID')->setId('getMultiClassByProgCamp')->addClass('txtfield')->fromArray($program)->required()->selectMultiple();

    $col = $row->addColumn()->setClass('newdes showClass');
    $col->addLabel('classes', __('Class'))->addClass('dte');
    $col->addSelect('classes')->setId('showMultiClassByProg')->addClass('txtfield')->placeholder('Select Class')->selectMultiple()->required();

    $col = $row->addColumn()->setClass('newdes hiddencol');
    $col->addLabel('', __(''))->addClass('dte');
    $col->addSelect('')->setId('showMultiSecByProgCls')->addClass('txtfield')->placeholder('Select Class')->selectMultiple();
    // $col = $row->addColumn()->setClass('newdes');
    //         $col->addLabel('seats', __('Seats'))->addClass('dte');
    //         $col->addTextField('seats')->addClass('txtfield'); 
    $form->addHiddenValue('seats', '');




    $row = $form->addRow();

    $col = $row->addColumn()->setClass('newdes');
    $col->addLabel('limit_apply_form', __('Limit Max Users'))->addClass('dte');
    $col->addNumber('limit_apply_form')->setId('numAllow')->addClass('txtfield');

    $col = $row->addColumn()->setClass('newdes');
    $col->addLabel('status', __('Status'));
    $col->addSelect('status')->addClass('txtfield')->fromArray($statuses)->required();


    $col = $row->addColumn()->setClass('newdes');
    $col->addLabel('start_date', __('Start Date'))->addClass('dte');
    $col->addDate('start_date')->addClass('txtfield')->required();

    $col = $row->addColumn()->setClass('newdes');
    $col->addLabel('end_date', __('End Date'))->addClass('dte');
    $col->addDate('end_date')->addClass('txtfield')->required();

    $row = $form->addRow();

    $col = $row->addColumn()->setClass('newdes');
    $col->addLabel('reg_req', __('Registration Required'));
    $col->addSelect('reg_req')->addClass('txtfield')->fromArray($reg_status)->required();

    $col = $row->addColumn()->setClass('newdes');
    $col->addLabel('application_series_id', __('Application Series'));
    $col->addSelect('application_series_id')->addClass('txtfield')->fromArray($applicationSeries);

    $col = $row->addColumn()->setClass('newdes');
    $col->addLabel('admission_series_id', __('Admission Series'));
    $col->addSelect('admission_series_id')->addClass('txtfield')->fromArray($admissionSeries);


    $col = $row->addColumn()->setClass('newdes');
    $col->addLabel('fn_fee_structure_id', __('Fee Group'));
    $col->addSelect('fn_fee_structure_id')->setId('getFeeStructureByProgClass')->addClass('txtfield');

    $row = $form->addRow();

    $col = $row->addColumn()->setClass('newdes');
    $col->addLabel('fn_fees_receipt_template_id', __('Receipt Template'));
    $col->addSelect('fn_fees_receipt_template_id')->addClass('txtfield')->fromArray($receiptTemplate);



    $col = $row->addColumn()->setClass('newdes');
    $col->addLabel('email_template_id', __('Email Template'));
    $col->addSelect('email_template_id')->fromArray($emailTemplate)->addClass('txtfield');

    $col = $row->addColumn()->setClass('newdes');
    $col->addLabel('sms_template_id', __('Sms Template'));
    $col->addSelect('sms_template_id')->fromArray($smsTemplate)->addClass('txtfield');

    $col = $row->addColumn()->setClass('newdes');
    $col->addLabel('is_fee_generate', __('Application Fee Settings'));
    $col->addSelect('is_fee_generate')->fromArray($feechk)->addClass('txtfield');

    $row = $form->addRow();

    $col = $row->addColumn()->setClass('newdes');
    $col->addLabel('is_publish_parent', __('Publish For Parent'));
    $col->addCheckBox('is_publish_parent')->addClass('txtfield')->setValue('1');

    $col = $row->addColumn()->setClass('newdes');
    $col->addLabel('allow_multiple_submission', __('Allow Multiple Submission'));
    $col->addCheckBox('allow_multiple_submission')->addClass('txtfield')->setValue('1');

    $col = $row->addColumn()->setClass('newdes');
    $col->addLabel('', __(''))->addClass('dte');

    $col = $row->addColumn()->setClass('newdes');
    $col->addLabel('', __(''))->addClass('dte');

    // $form->toggleVisibilityByClass('statusChange')->onSelect('status')->when('Current');
    // $direction = __('Past');

    // Display an alert to warn users that changing this will have an impact on their system.
    // $row = $form->addRow()->setClass('statusChange');
    // $row->addAlert(sprintf(__('Setting the status of this school year to Current will change the current school year %1$s to %2$s. Adjustments to the Academic Year can affect the visibility of vital data in your system. It\'s recommended to use the Rollover tool in User Admin to advance school years rather than changing them here. PROCEED WITH CAUTION!'), $_SESSION[$guid]['pupilsightSchoolYearNameCurrent'], $direction) );



    $row = $form->addRow();


    $col = $row->addColumn()->setClass('newdes');
    $col->addLabel('description', __('Description'));
    $col->addTextArea('description')->addClass('txtfield')->setRows(4);

    $row = $form->addRow();
    $col = $row->addColumn()->setClass('newdes');
    //$col->addButton(__('Add More Seat Matrix'))->addData('cid', '1')->setID('addSeats')->addClass('btn btn-primary');
    $col->addContent('<a class="btn btn-primary" id="addSeats" data-cid="1">Add More Seat Matrix</a>');

    $col = $row->addColumn()->setClass('newdes');
    $col->addLabel('Total Seats : ', __('Total Seats : '))->addClass('showSeats');
    // $row->addButton('Add Seats')->addData('class', 'addSeats')->addClass('submt');

    $row = $form->addRow()->setID('seatdiv');
    $col = $row->addColumn()->setClass('newdes');
    $col->addLabel('progname', __('Program'));
    $col->addSelect('progname[1]')->setID('progSeat')->addClass('txtfield');
    $col = $row->addColumn()->setClass('newdes');
    $col->addLabel('seatname', __('Class'));
    $col->addSelect('seatname[1]')->setID('classSeat')->addClass('txtfield');

    $col = $row->addColumn()->setClass('newdes');
    $col->addLabel('seatallocation', __('Campaign Seat'))->addClass('dte');
    $col->addNumber('seatallocation[1]')->addClass('txtfield kountseat szewdt');
    // $col->addLabel('', __(''))->addClass('dte');





    $row = $form->addRow()->setID('lastseatdiv');
    $row->addFooter();
    $row->addSubmit();

    echo $form->getOutput();
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

    select[multiple] {
        min-height: 35px !important;
    }
</style>
<script>
    // $("#start_date").datepicker({
    //     //minDate: 0,
    //     onClose: function (selectedDate) {
    //         $("#end_date").datepicker("option", "minDate", selectedDate);
    //     }
    // });

    $(document).ready(function() {
        $('#getMultiClassByProgCamp').selectize({
            plugins: ['remove_button'],
        });
    });

    $(document).on('change', '#getMultiClassByProgCamp', function() {
        var aid = $("#academic_id").val();
        var id = $(this).val();
        var type = 'getClassforCampaign';
        $('#showMultiClassByProg').selectize()[0].selectize.destroy();
        $("#getFeeStructureByProgClass").html('');
        $.ajax({
            url: 'ajax_data.php',
            type: 'post',
            data: {
                val: id,
                type: type,
                aid: aid
            },
            async: true,
            success: function(response) {
                $("#showMultiClassByProg").html('');
                $("#showMultiClassByProg").html(response);
                $("#showMultiClassByProg").parent().children('.LV_validation_message').remove();
                $('#showMultiClassByProg').selectize({
                    plugins: ['remove_button'],
                });
            }
        });
    });


    $(document).on('change', '#showMultiClassByProg', function() {
        var id = $(this).val();
        var aid = $('#academic_id').val();
        var pid = $('#getMultiClassByProgCamp').val();
        var type = 'getFeeStructure';
        $.ajax({
            url: 'ajax_data.php',
            type: 'post',
            data: {
                val: id,
                type: type,
                pid: pid,
                aid: aid
            },
            async: true,
            success: function(response) {
                $("#getFeeStructureByProgClass").html('');
                $("#getFeeStructureByProgClass").html(response);
            }
        });
        var type = 'getProgForSeats';
        $.ajax({
            url: 'ajax_data.php',
            type: 'post',
            data: {
                val: id,
                type: type,
                pid: pid,
                aid: aid
            },
            async: true,
            success: function(response) {
                $("#progSeat").html('');
                $("#progSeat").html(response);
            }
        });
    });

    $(document).on('change', '#progSeat', function() {
        var id = $(this).val();
        var aid = $('#academic_id').val();
        var cid = $('#showMultiClassByProg').val();
        var type = 'getClassForSeats';
        $.ajax({
            url: 'ajax_data.php',
            type: 'post',
            data: {
                val: id,
                type: type,
                cid: cid,
                aid: aid
            },
            async: true,
            success: function(response) {
                $("#classSeat").html('');
                $("#classSeat").html(response);
            }
        });
    });

    $(document).on('change', '.seatProg', function() {
        var rid = $(this).attr('data-id');
        var id = $(this).val();
        var aid = $('#academic_id').val();
        var cid = $('#showMultiClassByProg').val();
        var type = 'getClassForSeats';
        $.ajax({
            url: 'ajax_data.php',
            type: 'post',
            data: {
                val: id,
                type: type,
                cid: cid,
                aid: aid
            },
            async: true,
            success: function(response) {
                $("#seatclass" + rid).html('');
                $("#seatclass" + rid).html(response);
            }
        });
    });
</script>