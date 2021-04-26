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
        ->add(__('Manage Sketch Template'), 'examination_report_template_manage.php')
        ->add(__('Add Sketch Template'));

    if (isset($_GET['return'])) {
        returnProcess($guid, $_GET['return'], null, null);
    }

        $campaign_id = $_GET['cid'];
        $field_name = $_GET['id'];
        $template_type = $_GET['type'];

        $sql = 'SELECT * FROM campaign_configure_image_template WHERE campaign_id = "' . $campaign_id . '" AND field_name = "'.$field_name.'" AND template_type = "'.$template_type.'" ';
        $result = $connection2->query($sql);
        $imgData = $result->fetch();

        echo '<h2>';
        echo __('Set Parameter');
        echo '</h2>';

        $form = Form::create('imgForm', $_SESSION[$guid]['absoluteURL'].'/modules/'.$_SESSION[$guid]['module'].'/campaign_template_configure_imageProcess.php?address='.$_SESSION[$guid]['address']);

        $form->setFactory(DatabaseFormFactory::create($pdo));
        $form->addHiddenValue('address', $_SESSION[$guid]['address']);
        $form->addHiddenValue('campaign_id', $campaign_id);
        $form->addHiddenValue('field_name', $field_name);
        $form->addHiddenValue('template_type', $template_type);
        
        $row = $form->addRow();
        $row->addLabel('x', __('X'));
        $row->addTextField('x')->setValue($imgData['x']);

        $row = $form->addRow();
        $row->addLabel('y', __('Y'));
        $row->addTextField('y')->setValue($imgData['y']);

        $row = $form->addRow();
        $row->addLabel('page_no', __('Page No'));
        $row->addTextField('page_no')->setValue($imgData['page_no']);

        $row = $form->addRow();
        $row->addLabel('width', __('Width'));
        $row->addTextField('width')->setValue($imgData['width']);

        $row = $form->addRow();
        $row->addLabel('height', __('Height'));
        $row->addTextField('height')->setValue($imgData['height']);

        

        $row = $form->addRow();
        $row->addFooter();
        //$row->addSubmit();
        $row->addContent('<a id="saveCampImgData" class="btn btn-primary">Save</a>&nbsp;&nbsp;<a id="delCamImgData" class="btn btn-danger text-white" data-cid="'.$campaign_id.'" data-fname="'.$field_name.'" data-type="'.$template_type.'">Delete</a>');
        echo $form->getOutput();
}
?>
