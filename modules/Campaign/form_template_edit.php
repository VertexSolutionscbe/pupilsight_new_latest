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
            ->add(__('Manage Application Form Template'), 'form_template_manage.php&id='.$id.'')
            ->add(__('Add Application Form Template'));

        if (isset($_GET['return'])) {
            returnProcess($guid, $_GET['return'], null, null);
        }

        $id = $_GET['id'];
        $typ = $_GET['type'];

        
        $chksub = 'SELECT * FROM campaign WHERE id = ' . $id . '  ';
        $resultsub = $connection2->query($chksub);
        $chkCamp = $resultsub->fetch();

        if($typ == 1){
            $ttype = 'Online';
            $name = $chkCamp['template_name'];
            $file = $chkCamp['template_filename'];
            $path = $chkCamp['template_path'];
        } else if($typ == 2){
            $ttype = 'Offline';
            $name = $chkCamp['offline_template_name'];
            $file = $chkCamp['offline_template_filename'];
            $path = $chkCamp['offline_template_path'];
        }

        $type = array(''=>'Select Type', $ttype => $ttype);


        echo '<h2>';
        echo __('Edit Application Form Template');
        echo '</h2>';

        $form = Form::create('reportTemplate', $_SESSION[$guid]['absoluteURL'].'/modules/'.$_SESSION[$guid]['module'].'/form_template_editProcess.php?address='.$_SESSION[$guid]['address']);

        $form->setFactory(DatabaseFormFactory::create($pdo));
        $form->addHiddenValue('address', $_SESSION[$guid]['address']);
        $form->addHiddenValue('id', $id);

        

        $row = $form->addRow();
        $row->addLabel('type', __('Template Type'));
        $row->addSelect('type')->fromArray($type)->required()->selected($ttype);
        
        $row = $form->addRow();
        $row->addLabel('name', __('Template Name'));
        $row->addTextField('name')->required()->setValue($name);

        $row = $form->addRow();
        $row->addLabel('file', __('Template'));
        $row->addFileUpload('file')->accepts('.pdf')->setMaxUpload(false)->required();

        $row = $form->addRow();
        $row->addLabel('', __(''));
        $row->addContent('<a href="public/application_template/'.$file.'" download>'.$file.'</a>');

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