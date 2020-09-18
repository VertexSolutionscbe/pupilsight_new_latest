<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Forms\Form;
use Pupilsight\Forms\DatabaseFormFactory;

if (isActionAccessible($guid, $connection2, '/modules/Attendance/manange_sms_template_edit.php') == false) {
    //Acess denied
    echo "<div class='alert alert-danger'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    //Proceed!
    $page->breadcrumbs
        ->add(__('Manage Sms Template'), 'manange_sms_template.php')
        ->add(__('Edit Sms Template'));

    if (isset($_GET['return'])) {
        returnProcess($guid, $_GET['return'], null, null);
    }

    //Check if school year specified
    $pupilsightTemplateID = $_GET['id'];
    if ($pupilsightTemplateID == '') {
        echo "<div class='alert alert-danger'>";
        echo __('You have not specified one or more required parameters.');
        echo '</div>';
    } else {
        try {
            $data = array('id' => $pupilsightTemplateID);
            $sql = 'SELECT * FROM pupilsightTemplateForAttendance WHERE id=:id';
            $result = $connection2->prepare($sql);
            $result->execute($data);
        } catch (PDOException $e) {
            echo "<div class='alert alert-danger'>".$e->getMessage().'</div>';
        }

        if ($result->rowCount() != 1) {
            echo "<div class='alert alert-danger'>";
            echo __('The specified record cannot be found.');
            echo '</div>';
        } else {
            //Let's go!
            $kount = 0;
            $totalseats = 0;
            $values = $result->fetch();
            
            
            // echo '<pre>';
            // print_r($seatvalues);
            // echo '</pre>';
            // die();
            $form = Form::create('Admission', $_SESSION[$guid]['absoluteURL'].'/modules/'.$_SESSION[$guid]['module'].'/manange_sms_template_editProcess.php?pupilsightTemplateID='.$pupilsightTemplateID)->addClass('newform');
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
            echo __('Edit Sms Template');
            echo '</h2>';

            $row = $form->addRow();

                    
            $col = $row->addColumn()->setClass('newdes');
                    $col->addLabel('name', __('Template Name'));
                    $col->addTextField('name')->addClass('txtfield')->required()->setValue($values['name']);
                
                    $col = $row->addColumn()->setClass('newdes');
                    $col->addLabel('status', __('Template Status'))->setClass('addwdth');
                    $col->addCheckBox('status')->fromArray($status)->checked($values['status']);
                    
        $row = $form->addRow();
    
            $chkentities = explode(', ',$values['entities']);
            $col = $row->addColumn()->setClass('newdes');
                    $col->addLabel('entities', __('Entities'));
                    $col->addCheckBox('entities')->setId('entities')->fromArray($entities)->required()->checked($chkentities)->addClass('entities_slt'); 

            $row = $form->addRow();
                $col = $row->addColumn()->setClass('newdes');
                    $col->addLabel('description', __('Description'));
                    $col->addTextArea('description')->setId('smsDescEditor')->addClass('txtfield')->setRows(4)->setValue($values['description'])->required();   

            
            $row = $form->addRow()->setID('lastseatdiv');
                $row->addFooter();
                $row->addSubmit();

            echo $form->getOutput();
        }
    }
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