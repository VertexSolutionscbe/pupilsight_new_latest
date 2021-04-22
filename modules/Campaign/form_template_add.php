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
    $id = $_GET['id'];
    $page->breadcrumbs
        ->add(__('Manage Application Form Template'), 'form_template_manage.php&id='.$id.'')
        ->add(__('Add Application Form Template'));

    if (isset($_GET['return'])) {
        returnProcess($guid, $_GET['return'], null, null);
    }

    

    

    $chksub = 'SELECT * FROM campaign WHERE id = ' . $id . ' ';
    $resultsub = $connection2->query($chksub);
    $chkCamp = $resultsub->fetch();
    if(!empty($chkCamp['template_name']) && empty($chkCamp['offline_template_name'])){
        $type = array(''=>'Select Type','Offline' => 'Offline');
    }

    if(!empty($chkCamp['offline_template_name']) && empty($chkCamp['template_name'])){
        $type = array(''=>'Select Type','Online' => 'Online');
    }

    if(!empty($chkCamp['offline_template_name']) && !empty($chkCamp['template_name'])){
        $type = array(''=>'Select Type');
    }

    if(empty($chkCamp['offline_template_name']) && empty($chkCamp['template_name'])){
        $type = array(''=>'Select Type','Online' => 'Online', 'Offline' => 'Offline');
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

    <div style="margin-top:100px;">
        <h3>Configure Image Fields</h3>
        <table class="table">
            <thead>
                <tr>
                    <th>Sl No</th>
                    <th>Attribute Name</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <th>1</th>
                    <th>student_photo</th>
                    <th><a class="btn btn-white  setParam" data-hrf="fullscreen.php?q=/modules/Campaign/campaign_template_configure_image.php&id=student_photo&cid=<?= $id; ?>" >Set Parameter</a></th>
                </tr>
                <tr>
                    <th>2</th>
                    <th>father_photo</th>
                    <th><a class="btn btn-white  setParam" data-hrf="fullscreen.php?q=/modules/Campaign/campaign_template_configure_image.php&id=father_photo&cid=<?= $id; ?>" >Set Parameter</a></th>
                </tr>
                <tr>
                    <th>3</th>
                    <th>mother_photo</th>
                    <th><a class="btn btn-white  setParam" data-hrf="fullscreen.php?q=/modules/Campaign/campaign_template_configure_image.php&id=mother_photo&cid=<?= $id; ?>" >Set Parameter</a></th>
                </tr>
                <tr>
                    <th>4</th>
                    <th>guardian_photo</th>
                    <th><a class="btn btn-white  setParam" data-hrf="fullscreen.php?q=/modules/Campaign/campaign_template_configure_image.php&id=guardian_photo&cid=<?= $id; ?>" >Set Parameter</a></th>
                </tr>
            </tbody>
        </table>
        <a id="clickImgConfg" style="display:none" class="thickbox" href="">click</a>
    </div>

<script type="text/javascript">
    $(document).on('click', '.setParam', function (e) {
        e.preventDefault();
        var hrf = $(this).attr('data-hrf');
        var type = $("#type").val();
        var newhrf = hrf+'&type='+type;
        if(type){
            $("#clickImgConfg").attr('href', newhrf);
            $("#clickImgConfg")[0].click();
        } else {
            alert('Please Select Template Type');
        }
    });
</script> 