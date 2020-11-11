<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Forms\Form;
use Pupilsight\Forms\DatabaseFormFactory;
$session = $container->get('session');
$name = $session->get('file_name_tmp');
$file = $session->get('file_doc_tmp');
if (isActionAccessible($guid, $connection2, '/modules/Campaign/index.php') == false) {
    //Acess denied
    echo "<div class='error'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    //Proceed!
    $page->breadcrumbs
        ->add(__('Manage Application Form Template'), 'form_template_manage.php')
        ->add(__('Add Application Form Template'));

    if (isset($_GET['return'])) {
        returnProcess($guid, $_GET['return'], null, null);
    }

    $id = $_GET['id'];

    

    $chksub = 'SELECT * FROM campaign WHERE id = ' . $id . ' ';
    $resultsub = $connection2->query($chksub);
    $chkCamp = $resultsub->fetch();
    if(!empty($chkCamp['template_name']) && empty($chkCamp['offline_template_name'])){
        $type = array(''=>'Select Type', 'Offline' => 'Offline');
    }

    if(!empty($chkCamp['offline_template_name']) && empty($chkCamp['template_name'])){
        $type = array(''=>'Select Type', 'Online' => 'Online');
    }

    if(!empty($chkCamp['offline_template_name']) && !empty($chkCamp['template_name'])){
        $type = array(''=>'Select Type');
    }

    if(empty($chkCamp['offline_template_name']) && empty($chkCamp['template_name'])){
        $type = array(''=>'Select Type', 'Online' => 'Online', 'Offline' => 'Offline');
    }


   
        echo '<h2>';
        echo __('Add Application Form Template');
        echo '</h2>';

        $form = Form::create('reportTemplate', $_SESSION[$guid]['absoluteURL'].'/modules/'.$_SESSION[$guid]['module'].'/form_template_addProcess.php?address='.$_SESSION[$guid]['address']);

        $form->setFactory(DatabaseFormFactory::create($pdo));
        $form->addHiddenValue('address', $_SESSION[$guid]['address']);
        $form->addHiddenValue('id', $id);

        

        $row = $form->addRow();
        $row->addLabel('type', __('Template Type'));
        $row->addSelect('type')->fromArray($type)->required();
        
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