<?php
/*
Pupilsight, Flexible & Open School System
*/


use Pupilsight\Forms\Form;
use Pupilsight\Forms\DatabaseFormFactory;

if (isActionAccessible($guid, $connection2, '/modules/School Admin/sms_template_manage_add.php') == false) {
    //Acess denied
    echo "<div class='alert alert-danger'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    //Proceed!
    
    $page->breadcrumbs
        ->add(__('Manage Sms Template'), 'manange_sms_template.php')
        ->add(__('Add Sms Template'));

    $editLink = '';
    if (isset($_GET['editID'])) {
        $editLink = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/School Admin/sms_template_manage_edit.php&id='.$_GET['editID'];
    }
    if (isset($_GET['return'])) {
        returnProcess($guid, $_GET['return'], $editLink, null);
    }

    $form = Form::create('SmsTemplate', $_SESSION[$guid]['absoluteURL'].'/modules/'.$_SESSION[$guid]['module'].'/manange_sms_template_addProcess.php')->addClass('newform');
    $form->setFactory(DatabaseFormFactory::create($pdo));

    $form->addHiddenValue('address', $_SESSION[$guid]['address']);
     

    $entities = array(
        'Student_name'     => __('Student Name'),
        'Student_info' => __('Student Info'),
        'Student_id' => __('Admission/Student ID No'),
        'Father_name' => __('Father Name'),
        'Mother_name' => __('Mother Name'),
        'Class' => __('Class'),
        'Section_name' => __('Section Name'),
        'Attendance_date'     => __('Attendance Date'),
        'Session_name'     => __('Session Name'),
        'Subject_name'  => __('Subject Name'),
        'Absent_reason' => __('Absent Reason'),
    );

    $status = array(
        '1'     => __('Active'),
    );
   
    echo '<h2>';
    echo __('Add Sms Template');
    echo '</h2>';
    $row = $form->addRow();
    
        $col = $row->addColumn()->setClass('newdes');
                $col->addLabel('name', __('Template Name'));
                $col->addTextField('name')->addClass('txtfield')->required();
            
        $col = $row->addColumn()->setClass('newdes');
                $col->addLabel('status', __('Template Status'))->setClass('addwdth');
                $col->addCheckBox('status')->fromArray($status);
                
    $row = $form->addRow();

        
        $col = $row->addColumn()->setClass('newdes');
                $col->addLabel('entities', __('Entities'));
                $col->addCheckBox('entities')->setId('entities')->fromArray($entities)->required()->addClass('entities_slt');      
    $row = $form->addRow();
              
        $col = $row->addColumn()->setClass('newdes');
            $col->addLabel('description', __('Description'));
            $col->addTextArea('description')->setId('smsDescEditor')->addClass('txtfield')->setRows(4)->required(); 

    
        $row = $form->addRow()->setID('lastseatdiv');
        $row->addFooter();
        $row->addSubmit();

    echo $form->getOutput();
  
}
?>
<style>
#entities{
    text-align: left !important;
}

#entities label{
    margin-left: 20px;
}

#entities input{
    margin-left: 5px;
}

.addwdth{
    width:300px;
}

.selType{
    cursor:pointer;
    border-bottom: 1px solid grey;
}    

.selDiv{
    display: inline-grid;
    background: #f0f1f3;
    width: 25%;
    text-align: center;
    border: 1px solid grey;
}

.selType:hover {
  background-color: blue;
  color:white;
}
</style>
<script>
//$("#status").html().find("<br>").remove();
$('#entities br').remove();

$(document).on('change','.entities_slt',function(){
    var val =$(this).val();
    
    if($(this). is(":checked")){
        val="@"+val;
    var $txt = $("#smsDescEditor");
    var caretPos = $txt[0].selectionStart;
    var textAreaTxt = $txt.val();
    var txtToAdd = val;
    $txt.val(textAreaTxt.substring(0, caretPos) + txtToAdd + textAreaTxt.substring(caretPos) );
}
});
</script>