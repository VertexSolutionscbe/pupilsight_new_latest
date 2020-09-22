<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Forms\Form;
use Pupilsight\FileUploader;

if (isActionAccessible($guid, $connection2, '/modules/School Admin/house_manage_edit.php') == false) {
    //Acess denied
    echo "<div class='alert alert-danger'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    //Proceed!
    $page->breadcrumbs
        ->add(__('Manage Houses'), 'house_manage.php')
        ->add(__('Edit House'));

    if (isset($_GET['return'])) {
        returnProcess($guid, $_GET['return'], null, null);
    }

    //Check if school year specified
    $pupilsightHouseID = $_GET['pupilsightHouseID'];
    if ($pupilsightHouseID == '') {
        echo "<div class='alert alert-danger'>";
        echo __('You have not specified one or more required parameters.');
        echo '</div>';
    } else {
        try {
            $data = array('pupilsightHouseID' => $pupilsightHouseID);
            $sql = 'SELECT * FROM pupilsightHouse WHERE pupilsightHouseID=:pupilsightHouseID';
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

            $form = Form::create('houses', $_SESSION[$guid]['absoluteURL'].'/modules/'.$_SESSION[$guid]['module'].'/house_manage_editProcess.php?pupilsightHouseID='.$pupilsightHouseID);

            $form->addHiddenValue('address', $_SESSION[$guid]['address']);
            $form->addHiddenValue('attachment1', $values['logo']);

            $row = $form->addRow();
                $row->addLabel('name', __('Name'))->description(__('Must be unique.'));
                $row->addTextField('name')->required()->maxLength(30)->setValue($values['name']);

            $row = $form->addRow();
                $row->addLabel('nameShort', __('Short Name'))->description(__('Must be unique.'));
                $row->addTextField('nameShort')->required()->maxLength(10)->setValue($values['nameShort']);

            $fileUploader = new FileUploader($pdo, $pupilsight->session);

            $row = $form->addRow();
                $row->addLabel('file1', __('Logo'));
                $file = $row->addFileUpload('file1')
                    ->accepts($fileUploader->getFileExtensions('Graphics/Design'))
                    ->setAttachment('logo', $_SESSION[$guid]['absoluteURL'], $values['logo'])
                    ->setDeleteAction('/modules/School Admin/house_manage_edit_photoDeleteProcess.php?pupilsightHouseID='.$pupilsightHouseID);

            $row = $form->addRow();
                $row->addFooter();
                $row->addSubmit();

            echo $form->getOutput();
        }
    }
}
