<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Forms\Form;
use Pupilsight\Forms\DatabaseFormFactory;
$session = $container->get('session');
$name = $session->get('file_name_tmp');
$file = $session->get('file_doc_tmp');
if (isActionAccessible($guid, $connection2, '/modules/Finance/fee_receipts_manage.php') == false) {
    //Acess denied
    echo "<div class='error'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    //Proceed!
    $page->breadcrumbs
        ->add(__('Manage Fee Receipts Template'), 'fee_receipts_manage.php')
        ->add(__('Add Fee Receipts Template'));

    if (isset($_GET['return'])) {
        returnProcess($guid, $_GET['return'], null, null);
    }

   
        echo '<h2>';
        echo __('Add Fee Receipts Template');
        echo '</h2>';

        $form = Form::create('reportTemplate', $_SESSION[$guid]['absoluteURL'].'/modules/'.$_SESSION[$guid]['module'].'/fee_receipt_template_addProcess.php?address='.$_SESSION[$guid]['address']);

        $form->setFactory(DatabaseFormFactory::create($pdo));
        $form->addHiddenValue('address', $_SESSION[$guid]['address']);

        
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
<!-- <script type="text/javascript">
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
</script> -->