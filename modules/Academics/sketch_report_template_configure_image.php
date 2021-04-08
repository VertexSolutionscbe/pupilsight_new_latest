<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Forms\Form;
use Pupilsight\Forms\DatabaseFormFactory;
$session = $container->get('session');
$name = $session->get('file_name_tmp');
$file = $session->get('file_doc_tmp');
if (isActionAccessible($guid, $connection2, '/modules/Academics/sketch_manage_attribute.php') == false) {
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

        $sketch_id = $_GET['skid'];
        $attr_id = $_GET['id'];

        $sql = 'SELECT * FROM examinationReportSketchConfigureImage WHERE sketch_id = "' . $sketch_id . '" AND attr_id = '.$attr_id.' ';
        $result = $connection2->query($sql);
        $imgData = $result->fetch();

        echo '<h2>';
        echo __('Set Parameter');
        echo '</h2>';

        $form = Form::create('imgForm', $_SESSION[$guid]['absoluteURL'].'/modules/'.$_SESSION[$guid]['module'].'/sketch_report_template_configure_imageProcess.php?address='.$_SESSION[$guid]['address']);

        $form->setFactory(DatabaseFormFactory::create($pdo));
        $form->addHiddenValue('address', $_SESSION[$guid]['address']);
        $form->addHiddenValue('sketch_id', $sketch_id);
        $form->addHiddenValue('attr_id', $attr_id);
        
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
        $row->addContent('<a id="saveImgData" class="btn btn-primary">Save</a>&nbsp;&nbsp;<a id="delImgData" class="btn btn-danger text-white" data-sk="'.$sketch_id.'" data-atr="'.$attr_id.'">Delete</a>');
        echo $form->getOutput();
}
?>
