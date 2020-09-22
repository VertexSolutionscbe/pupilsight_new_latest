<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Forms\Form;
use Pupilsight\Forms\DatabaseFormFactory;
use Pupilsight\Services\Format;
//Module includes
require_once __DIR__ . '/moduleFunctions.php';

if (isActionAccessible($guid, $connection2, '/modules/Attendance/attendance_configSettings_manage_add.php') == false) {
    //Acess denied
    echo "<div class='alert alert-danger'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    //Proceed!
    
    $page->breadcrumbs
        ->add(__('Attendance Configuration'), 'attendance_configSettings.php')
        ->add(__('Add Attendance Configuration'));

    $editLink = '';
    if (isset($_GET['editID'])) {
        $editLink = $_SESSION[$guid]['absoluteURL'] . '/index.php?q=/modules/Attendance/attendanceSettings_manage_add.php&pupilsightDepartmentID=' . $_GET['editID'];
    }
    if (isset($_GET['return'])) {
        returnProcess($guid, $_GET['return'], $editLink, null);
    }
    
    $pupilsightSchoolYearID = '';
    if (isset($_GET['pupilsightSchoolYearID'])) {
        $pupilsightSchoolYearID = $_GET['pupilsightSchoolYearID'];
    }
    if ($pupilsightSchoolYearID == '' or $pupilsightSchoolYearID == $_SESSION[$guid]['pupilsightSchoolYearID']) {
        $pupilsightSchoolYearID = $_SESSION[$guid]['pupilsightSchoolYearID'];
        $pupilsightSchoolYearName = $_SESSION[$guid]['pupilsightSchoolYearName'];
    }

    $data = array('pupilsightSchoolYearID' => $pupilsightSchoolYearID);
    $sql = 'SELECT name FROM pupilsightSchoolYear WHERE pupilsightSchoolYearID=:pupilsightSchoolYearID';
    $result = $pdo->executeQuery($data, $sql);
    
    $sqlp = 'SELECT pupilsightProgramID, name FROM pupilsightProgram ';
    $resultp = $connection2->query($sqlp);
    $rowdataprog = $resultp->fetchAll();

    $program = array();
    $program2 = array();
    $program1 = array('' => 'Select Organisation');
    foreach ($rowdataprog as $dt) {
        $program2[$dt['pupilsightProgramID']] = $dt['name'];
    }
    $program = $program1 + $program2;
    
    $sms_tmp_sql = 'SELECT id,name FROM pupilsightTemplateForAttendance WHERE status="1" AND type="Sms"';
    $sms_tmp = $connection2->query($sms_tmp_sql);
    $sms_tdata = $sms_tmp->fetchAll();
    
    $smsTemplate = array();
    $smsTemplate2 = array();
    $smsTemplate1 = array('' => 'Select SMS Template');
    foreach ($sms_tdata as $dt) {
        $smsTemplate2[$dt['id']] = $dt['name'];
    }
    
    $smsTemplate = $smsTemplate1 + $smsTemplate2;
    if (isset(($_GET))) {
        $pupilsightProgramID =  isset($_GET['pupilsightProgramID']) ? $_GET['pupilsightProgramID'] : '';
        $pupilsightYearGroupID = isset($_GET['classes']) ? $_GET['classes'] : '';
        $setclasss = isset($_GET['classes']) ? $_GET['classes'] : '';
        $slectSection = isset($_GET['slectSection']) ? $_GET['slectSection'] : '';
        $pupilsightRollGroupID = isset($_GET['pupilsightRollGroupID']) ? $_GET['pupilsightRollGroupID'] : '';
    } else {
        $pupilsightProgramID =  '';
        $pupilsightYearGroupID =  '';
        $pupilsightRollGroupID =  '';
        $slectSection = '';
        $setclasss = '';
    }
    $sql = 'SELECT a.*, b.name FROM pupilsightProgramClassSectionMapping AS a LEFT JOIN pupilsightYearGroup AS b ON a.pupilsightYearGroupID = b.pupilsightYearGroupID WHERE a.pupilsightProgramID = "' . $pupilsightProgramID . '" GROUP BY a.pupilsightYearGroupID';
    $result = $connection2->query($sql);
    $classesdata = $result->fetchAll();
    $classes = array();
    foreach ($classesdata as $ke => $cl) {
        $classes[$cl['pupilsightYearGroupID']] = $cl['name'];
    }
    
    $setclass = array();
    if (!empty($setclasss)) {
        $setclass = $setclasss;
    }

    $att_type = array();
    $att_type =  array(
        '' => 'Select  Attendance Type',
        '1' => 'Session',
        '2' => 'Subjects',
        '3' => 'Both Session'
    );
    //student_id,preferredName,pupilsightPersonID,Class-yearGroup,rollGroup->Section,dob

    $display_fields = array();
    $recipients= array(
            'Student_mobile'     => __('Student Mobile'),
            'Father' => __('Father Mobile'),
            'Mother' => __('Mother Mobile'),
            'Other' => __('Others'),
            );
     $display_fields =  array(''=>'Select Display Field',
            'Student ID' =>'Student ID',
            'Admission No' =>'Admission No',
            'gender' =>'Gender',
            'Father Name'=>'Father Name',
            'Mother Name'=>'Mother Name',
            'Date OF Birth'=>'Date OF Birth',
            'Class'=>'Class',
            'Section'=>'Section',   
            );
    $default_field='Student Name';
    $form = Form::create('attendanceConfigSetting', $_SESSION[$guid]['absoluteURL'].'/modules/'.$_SESSION[$guid]['module'].'/attendance_configSettings_manage_addProcess.php');
    $form->setFactory(DatabaseFormFactory::create($pdo));

    $form->addHiddenValue('address', $_SESSION[$guid]['address']);
    $form->addHiddenValue('default_field', 'preferredName');
    $form->addHiddenValue('pupilsightSchoolYearID', $pupilsightSchoolYearID);

    $row = $form->addRow();
    $row->addLabel('pupilsightProgramID', __('Organisation'));
    $row->addSelect('pupilsightProgramID')->setId('getMultiClassByProg')->addClass('txtfield')->fromArray($program)->selected($pupilsightProgramID);
    
    $row = $form->addRow()->addClass('showClass');
    $row->addLabel('classes', __('Class'));
    $row->addSelect('classes')->setId('showMultiClassByProg')->addClass('txtfield')->placeholder('Select Class')->selectMultiple()->selected($setclass);
    
    $row = $form->addRow();
    $row->addLabel('attn_type', __('Attendance Type'));
    $row->addSelect('attn_type')->setId('att_type_id')->fromArray($att_type)->required()->placeholder();
    $row = $form->addRow();
    $row->addLabel('lock_attendance_marking', __('Select Lock Attendance Marking'));
    $row->addCheckbox('lock_attendance_marking')->description(__('Yes'))->setValue('1');
    $row = $form->addRow();
    $row->addLabel('fromDate', __('From date'))->description($_SESSION[$guid]['i18n']['dateFormat'])->prepend(__('Format:'));
    $row->addDate('fromDate')->setValue(Format::date($currentDate));
    $row = $form->addRow();
    $row->addLabel('toDate', __('To date'))->description($_SESSION[$guid]['i18n']['dateFormat'])->prepend(__('Format:'));
    $row->addDate('toDate')->setValue(Format::date($currentDate));
    $row = $form->addRow();
    $row->addLabel('auto_lock_attendance', __('Auto Lock Attendance'))->addClass('txtfield');
    $row->addCheckbox('auto_lock_attendance')->description(__('Yes'))->setValue('1')->checked($values['auto_lock_attendance']);
    $row = $form->addRow();
    $row->addLabel('enable_sms_absent', __('Enable SMS for Absence ?'));
    $row->addCheckbox('enable_sms_absent')->description(__('Yes'))->setValue('1');
    $row = $form->addRow();
    $row->addLabel('sms_template_id', __('Select SMS Template'))->addClass('hidearrow');
    $row->addSelect('sms_template_id')->fromArray($smsTemplate)->placeholder()->addClass('hidearrow');

    $row = $form->addRow();
    $row->addLabel('sms_recipients', __('Select the SMS recipients'))->addClass('hidearrow');
    $row->addSelect('sms_recipients')->fromArray($recipients)->placeholder()->addClass('hidearrow')->selectMultiple();

    $row = $form->addRow();
    $row->addLabel('select_sub_mandatory', __('Select Subject Mandatory'));
    $row->addCheckbox('select_sub_mandatory')->description(__('Yes'))->setValue('1');
    $row = $form->addRow();
    $row->addLabel('display_field_1', __('Display Field1'));
    $row->addSelect('display_field_1')->setId('att_type_id')->fromArray($display_fields)->placeholder();

    $row = $form->addRow();
    $row->addLabel('enable_sort_display_field_1', __('Sort by this field?'));
    $row->addCheckbox('enable_sort_display_field_1')->description(__('Sort by this field'))->setValue('1');

    $row = $form->addRow();
    $row->addLabel('display_field_2', __('Display Field2'));
    $row->addSelect('display_field_2')->setId('att_type_id')->fromArray($display_fields)->placeholder();


    $row = $form->addRow();
    $row->addLabel('enable_sort_display_field_2', __('Sort by this field?'))->addClass('txtfield');
    $row->addCheckbox('enable_sort_display_field_2')->description(__('Sort by this field'))->setValue('1');

    $row = $form->addRow();
    $row->addLabel('default_display', __('Default Display Field'));
    $row->addTextField('default_display')->addClass('txtfield')->setValue($default_field)->readOnly();


    $row = $form->addRow()->addClass('');

    $row->addLabel('no_of_session', __(' No of Sessions'))->addClass('wd100 nodsply showsessionclick');
    $row->addTextField('no_of_session')->addClass('numfield nodsply showsessionclick');
    //    $row->addContent('<a id="session_add" class="nodsply showsessionclick text-light bg-dodger-blue" style=" ;cursor: pointer; height: 28px; width: 32px; text-align: center;  margin-left: 10p; line-height: 28px; margin-top: 5px; ">Go</a>')->addClass('wd7');
    $row = $form->addRow()->addClass(' ');
    $col = $row->addColumn()->setClass('newdes ');
    // 
    // $col->addTextField('');   

    $row->addContent('<div id="session_table"></div>');



    $row = $form->addRow();
    $row->addFooter();
    $row->addContent('<button type="button" id="attnSettingsSubmit" class=" btn btn-primary" style="position:absolute; right:0; margin-top: -18px;">Submit</button>');


    echo $form->getOutput();
}
?>
<style>
    .hidearrow {
        display: none;
    }

    .nodsply {
        display: none;
    }

    .sml_wdth {
        width: 20%;
    }

    .wd100 {
        width: 123%;
    }

    .wd28 {

        width: 35%;
    }

    .wd7 {
        width: 7%;
    }

    #session_table {
        margin-left: -9px;
    }
    

    .text-xxs {
        display: none
    }
</style>
<script type='text/javascript'>
   $(document).ready(function () {
      	$('#showMultiClassByProg').selectize({
      		maxItems: 15,
      		plugins: ['remove_button'],
      	});
    });

    $(document).on('change', '#lock_attendance_marking', function() {
        if ($(this).prop("checked") == true) {
            $("#fromDate").prop("disabled", false);
            $("#toDate").prop("disabled", false);
        } else if ($(this).prop("disabled") == false) {
            $("#fromDate").val('');
            $("#toDate").val('');
            $("#fromDate").prop("disabled", true);
            $("#toDate").prop("disabled", true);
        }
    });

    $(document).on('change', '#enable_sms_absent', function() {
        if ($(this).prop("checked") == true) {
            $(".hidearrow").show();
        } else if ($(this).prop("disabled") == false) {
            $(".hidearrow").hide();
        }
    });

    function check() {
        $("#fromDate").prop("disabled", true);
        $("#toDate").prop("disabled", true);
    }
    check();
</script>