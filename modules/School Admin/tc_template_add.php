<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Forms\Form;
use Pupilsight\Forms\DatabaseFormFactory;
$session = $container->get('session');
$name = $session->get('file_name_tmp');
$file = $session->get('file_doc_tmp');
if (isActionAccessible($guid, $connection2, '/modules/School Admin/tc_template_add.php') == false) {
    //Acess denied
    echo "<div class='error'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    //Proceed!
    // $page->breadcrumbs
    //     ->add(__('Manage Fee Receipts Template'), 'fee_receipts_manage.php')
    //     ->add(__('Add Fee Receipts Template'));

    if (isset($_GET['return'])) {
        returnProcess($guid, $_GET['return'], null, null);
    }
        $pupilsightSchoolYearID = $_SESSION[$guid]['pupilsightSchoolYearID'];

   
        echo '<h2>';
        echo __('Add Template');
        echo '</h2>';

        $sqlp = 'SELECT pupilsightProgramID, name FROM pupilsightProgram ';
        $resultp = $connection2->query($sqlp);
        $rowdataprog = $resultp->fetchAll();

        $program = array();
        $program2 = array();
        $program1 = array('' => 'Select Program');
        foreach ($rowdataprog as $dt) {
            $program2[$dt['pupilsightProgramID']] = $dt['name'];
        }
        $program = $program1 + $program2;

        $types = array('TC' => 'TC', 'Study Certificate' => 'Study Certificate', 'Bonafide Certificate' => 'Bonafide Certificate', 'Conduct Certificate' => 'Conduct Certificate', 'Fee Letter' => 'Fee Letter');


        $form = Form::create('reportTemplate', $_SESSION[$guid]['absoluteURL'].'/modules/'.$_SESSION[$guid]['module'].'/tc_template_addProcess.php?address='.$_SESSION[$guid]['address']);

        $form->setFactory(DatabaseFormFactory::create($pdo));
        $form->addHiddenValue('address', $_SESSION[$guid]['address']);
        $form->addHiddenValue('pupilsightSchoolYearID', $pupilsightSchoolYearID);

        $form->addHiddenValue('type', 'tc');

        // $row = $form->addRow();
        // $row->addLabel('pupilsightProgramID', __('Program'));
        // $row->addSelect('pupilsightProgramID')->setID('getMultiClassByProgCamp')->fromArray($program)->placeholder('Select Program')->required();

        // $row = $form->addRow();
        // $row->addLabel('classIds', __('Class'))->addClass('dte');
        // $row->addSelect('classIds')->setID('showMultiClassByProg')->placeholder('Select Class')->selectMultiple()->required();

        
        $row = $form->addRow();
        $row->addLabel('type', __('Type'));
        $row->addSelect('type')->fromArray($types)->placeholder('Select Type')->required();

        $row = $form->addRow();
        $row->addLabel('name', __('Template Name'));
        $row->addTextField('name')->required();

        $row = $form->addRow();
        $row->addLabel('file', __('Template'));
        $row->addFileUpload('file')->accepts('.docx')->setMaxUpload(false)->required();

        $row = $form->addRow();
        $row->addFooter();
        $row->addSubmit();
        echo $form->getOutput();
}
?>
<script type="text/javascript">

    $(document).ready(function () {
      	$('#showMultiClassByProg').selectize({
      		plugins: ['remove_button'],
      	});
    });

    $(document).on('change', '#getMultiClassByProgCamp', function () {
        var id = $(this).val();
        var type = 'getClass';
        $('#showMultiClassByProg').selectize()[0].selectize.destroy();
        $("#getFeeStructureByProgClass").html('');
        $.ajax({
            url: 'ajax_data.php',
            type: 'post',
            data: { val: id, type: type },
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

    // $('#edit_template_form').on('submit',(function(e) {
    // e.preventDefault();
    // var formData = new FormData(this);
    // $.ajax({
    //     url:"ajaxSwitch.php", 
    //     type: "POST",             
    //     data: formData, 
    //     contentType: false,      
    //     cache: false,             
    //     processData:false, 
    //     async: false,       
    //     success: function(data)  
    //     {
           
    //         alert(data);
    //         window.location.reload()
    //     }
    // });

    // }));
</script>