<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Forms\Form;
use Pupilsight\Forms\DatabaseFormFactory;

if (isActionAccessible($guid, $connection2, '/modules/School Admin/sms_template_manage_edit.php') == false) {
    //Acess denied
    echo "<div class='alert alert-danger'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    //Proceed!
    $page->breadcrumbs
        ->add(__('Manage Sms Template'), 'sms_template_manage.php')
        ->add(__('Edit Sms Template'));

    if (isset($_GET['return'])) {
        returnProcess($guid, $_GET['return'], null, null);
    }

    //Check if school year specified
    $pupilsightTemplateID = $_GET['pupilsightTemplateID'];
    if ($pupilsightTemplateID == '') {
        echo "<div class='alert alert-danger'>";
        echo __('You have not specified one or more required parameters.');
        echo '</div>';
    } else {
        try {
            $data = array('pupilsightTemplateID' => $pupilsightTemplateID);
            $sql = 'SELECT * FROM pupilsightTemplate WHERE pupilsightTemplateID=:pupilsightTemplateID';
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
            $form = Form::create('Admission', $_SESSION[$guid]['absoluteURL'].'/modules/'.$_SESSION[$guid]['module'].'/sms_template_manage_editProcess.php?pupilsightTemplateID='.$pupilsightTemplateID)->addClass('newform');
            $form->setFactory(DatabaseFormFactory::create($pdo));

            $form->addHiddenValue('address', $_SESSION[$guid]['address']);

            $entities = array(
                'Admission'     => __('Admission'),
                'Student'     => __('Student'),
                'Staff'  => __('Staff'),
                'Parent' => __('Parent'),
                'Attendance' => __('Attendance'),
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
                    $col->addCheckBox('entities')->setId('entities')->fromArray($entities)->required()->checked($chkentities);   
        if(in_array('Student', $chkentities) || in_array('Admission', $chkentities) || in_array('Attendance', $chkentities)){
            $hidecls = '';
        } else {
            $hidecls = 'hidediv';
        }

        if(in_array('Parent', $chkentities)){
            $phidecls = '';
        } else {
            $phidecls = 'hidediv';
        }

        if(in_array('Staff', $chkentities)){
            $shidecls = '';
        } else {
            $shidecls = 'hidediv';
        }

        $row = $form->addRow()->setId('showAddField')->setClass('hidediv');
            $col = $row->addColumn()->setClass('newdes');
                    
                    $col->addContent('<div class="selDiv">
                    <h5 style="text-align:center;">Select Show Field</h5>
                    <span  class="selType studentShow '.$hidecls.'" data-val="student_name">Student Name</span>
                    <span  class="selType studentShow '.$hidecls.'" data-val="student_email">Student Email</span>
                    <span  class="selType parentShow '.$phidecls.'" data-val="parent_name">Parent Name</span>
                    <span  class="selType parentShow '.$phidecls.'" data-val="parent_email">Parent Email</span>
                    <span  class="selType staffShow '.$shidecls.'" data-val="staff_name">Staff Name</span>
                    <span  class="selType staffShow '.$shidecls.'" data-val="staff_email">Staff Email</span>
                    </div>');   


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


$(document).on('click', '#entities', function() {
    //var val = $(this).val();
    $.each($("input[name='entities[]']:checked"), function() {
        var val = $(this).val();
        
        if (val == "Admission" || val == "Student" || val == "Attendance") {
            $(".studentShow").removeClass('hidediv');
        }
        if (val == "Parent") {
           $(".parentShow").removeClass('hidediv');
        }
        if (val == "Staff") {
            $(".staffShow").removeClass('hidediv');
        }
    });
   
});

$(document).on('keyup', '#smsDescEditor', function() {
    var x = event.key;
    if (x == "@") {
        $("#showAddField").removeClass('hidediv');
    }
});

$(document).on('click', '.selType', function() {
    var val = $(this).attr('data-val');
    var $txt = $("#smsDescEditor");
    var caretPos = $txt[0].selectionStart;
    var textAreaTxt = $txt.val();
    var txtToAdd = val;
    $txt.val(textAreaTxt.substring(0, caretPos) + txtToAdd + textAreaTxt.substring(caretPos) );
    $("#showAddField").addClass('hidediv');
});
</script>