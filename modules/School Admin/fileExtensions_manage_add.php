<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Forms\Form;
use Pupilsight\FileUploader;

if (isActionAccessible($guid, $connection2, '/modules/School Admin/fileExtensions_manage_add.php') == false) {
    //Acess denied
    echo "<div class='alert alert-danger'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    //Proceed!
    $page->breadcrumbs
        ->add(__('Manage File Extensions'), 'fileExtensions_manage.php')
        ->add(__('Add File Extension'));

    $editLink = '';
    if (isset($_GET['editID'])) {
        $editLink = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/School Admin/fileExtensions_manage_edit.php&pupilsightFileExtensionID='.$_GET['editID'];
    }
    if (isset($_GET['return'])) {
        returnProcess($guid, $_GET['return'], $editLink, null);
    }

    $form = Form::create('fileExtensions', $_SESSION[$guid]['absoluteURL'].'/modules/'.$_SESSION[$guid]['module'].'/fileExtensions_manage_addProcess.php');

    $form->addHiddenValue('address', $_SESSION[$guid]['address']);

    $illegalTypes = FileUploader::getIllegalFileExtensions();

    $categories = array(
        'Document'        => __('Document'),
        'Spreadsheet'     => __('Spreadsheet'),
        'Presentation'    => __('Presentation'),
        'Graphics/Design' => __('Graphics/Design'),
        'Video'           => __('Video'),
        'Audio'           => __('Audio'),
        'Other'           => __('Other'),
    );

    $row = $form->addRow();
        $row->addLabel('extension', __('Extension'))->description(__('Must be unique.'));
        $ext = $row->addTextField('extension')->required()->maxLength(7);

        $within = implode(',', array_map(function ($str) { return sprintf("'%s'", $str); }, $illegalTypes));
        $ext->addValidation('Validate.Exclusion', 'within: ['.$within.'], failureMessage: "Illegal file type!", partialMatch: true, caseSensitive: false');

    $row = $form->addRow();
        $row->addLabel('name', __('Name'));
        $row->addTextField('name')->required()->maxLength(50);

    $row = $form->addRow();
        $row->addLabel('type', __('Type'));
        $row->addSelect('type')->fromArray($categories)->required()->placeholder();

    $row = $form->addRow();
        $row->addFooter();
        $row->addSubmit();

    echo $form->getOutput();
}
