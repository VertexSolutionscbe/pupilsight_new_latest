<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Forms\Form;
use Pupilsight\Forms\DatabaseFormFactory;
$session = $container->get('session');
$name = $session->get('file_name_tmp');
$file = $session->get('file_doc_tmp');
if (isActionAccessible($guid, $connection2, '/modules/Finance/edit_template.php') != false) {
    //Acess denied
    echo "<div class='error'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    //Proceed!
    $page->breadcrumbs
        ->add(__('Manage Program'), 'program_manage.php')
        ->add(__('Edit Program'));

    if (isset($_GET['return'])) {
        returnProcess($guid, $_GET['return'], null, null);
    }

   
        echo '<h2>';
        echo __('Upload '.$name);
        echo '</h2>';

        $form = Form::create('edit_template_form','');
        $form->setFactory(DatabaseFormFactory::create($pdo));

        $form->addHiddenValue('old_file',$file);
        $form->addHiddenValue('type','update_template_for_receipt');
        $row = $form->addRow();
        $row->addLabel('file_upload', __('Template'));
        $row->addFileUpload('file_upload')->accepts('.docx')
            ->setMaxUpload(false);
        $row = $form->addRow();
        $row->addFooter();
        $row->addSubmit();
        echo $form->getOutput();
}
?>
<script type="text/javascript">
    $('#edit_template_form').on('submit',(function(e) {
    e.preventDefault();
    var formData = new FormData(this);
    $.ajax({
        url:"ajaxSwitch.php", 
        type: "POST",             
        data: formData, 
        contentType: false,      
        cache: false,             
        processData:false, 
        async: false,       
        success: function(data)  
        {
           
            alert(data);
            window.location.reload()
        }
    });

    }));
</script>