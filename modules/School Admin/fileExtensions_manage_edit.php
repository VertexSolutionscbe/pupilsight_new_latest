<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Forms\Form;
use Pupilsight\FileUploader;

if (isActionAccessible($guid, $connection2, '/modules/School Admin/fileExtensions_manage_edit.php') == false) {
    //Acess denied
    echo "<div class='alert alert-danger'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    //Proceed!
    $page->breadcrumbs
        ->add(__('Manage File Extensions'), 'fileExtensions_manage.php')
        ->add(__('Edit File Extensions'));

    if (isset($_GET['return'])) {
        returnProcess($guid, $_GET['return'], null, null);
    }

    //Check if school year specified
    $pupilsightFileExtensionID = $_GET['pupilsightFileExtensionID'];
    if ($pupilsightFileExtensionID == '') {
        echo "<div class='alert alert-danger'>";
        echo __('You have not specified one or more required parameters.');
        echo '</div>';
    } else {
        try {
            $data = array('pupilsightFileExtensionID' => $pupilsightFileExtensionID);
            $sql = 'SELECT * FROM pupilsightFileExtension WHERE pupilsightFileExtensionID=:pupilsightFileExtensionID';
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
            $values = $result->fetch();

            $form = Form::create('fileExtensions', $_SESSION[$guid]['absoluteURL'].'/modules/'.$_SESSION[$guid]['module'].'/fileExtensions_manage_editProcess.php?pupilsightFileExtensionID='.$pupilsightFileExtensionID);

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
                $ext = $row->addTextField('extension')->required()->maxLength(7)->setValue($values['extension']);

                $within = implode(',', array_map(function ($str) { return sprintf("'%s'", $str); }, $illegalTypes));
                $ext->addValidation('Validate.Exclusion', 'within: ['.$within.'], failureMessage: "Illegal file type!", partialMatch: true, caseSensitive: false');

            $row = $form->addRow();
                $row->addLabel('name', __('Name'));
                $row->addTextField('name')->required()->maxLength(50)->setValue($values['name']);

            $row = $form->addRow();
                $row->addLabel('type', __('Type'));
                $row->addSelect('type')->fromArray($categories)->required()->placeholder()->selected($values['type']);

            $row = $form->addRow();
                $row->addFooter();
                $row->addSubmit();

            echo $form->getOutput();
        }
    }
}
