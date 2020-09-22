<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Forms\Form;
use Pupilsight\Forms\DatabaseFormFactory;

if (isActionAccessible($guid, $connection2, '/modules/School Admin/inSettings_edit.php') == false) {
    //Acess denied
    echo "<div class='alert alert-danger'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    //Proceed!
    $page->breadcrumbs
        ->add(__('Manage Individual Needs Settings'), 'inSettings.php')
        ->add(__('Edit Descriptor'));

    if (isset($_GET['return'])) {
        returnProcess($guid, $_GET['return'], null, null);
    }

    //Check if school year specified
    $pupilsightINDescriptorID = $_GET['pupilsightINDescriptorID'];
    if ($pupilsightINDescriptorID == '') {
        echo "<div class='alert alert-danger'>";
        echo __('You have not specified one or more required parameters.');
        echo '</div>';
    } else {
        try {
            $data = array('pupilsightINDescriptorID' => $pupilsightINDescriptorID);
            $sql = 'SELECT * FROM pupilsightINDescriptor WHERE pupilsightINDescriptorID=:pupilsightINDescriptorID';
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
            
            $form = Form::create('inDescriptor', $_SESSION[$guid]['absoluteURL'].'/modules/'.$_SESSION[$guid]['module'].'/inSettings_editProcess.php?pupilsightINDescriptorID='.$pupilsightINDescriptorID);
            $form->setFactory(DatabaseFormFactory::create($pdo));
        
            $form->addHiddenValue('address', $_SESSION[$guid]['address']);
        
            $row = $form->addRow();
                $row->addLabel('name', __('Name'))->description(__('Must be unique.'));
                $row->addTextField('name')->required()->maxLength(50);
            
            $row = $form->addRow();
                $row->addLabel('nameShort', __('Short Name'))->description(__('Must be unique.'));
                $row->addTextField('nameShort')->required()->maxLength(5);
        
            $row = $form->addRow();
                $row->addLabel('sequenceNumber', __('Sequence Number'));
                $row->addSequenceNumber('sequenceNumber', 'pupilsightINDescriptor', $values['sequenceNumber'])->required()->maxLength(5);
        
            $row = $form->addRow();
                $row->addLabel('description', __('Description'));
                $row->addTextArea('description')->setRows(8);
        
            $row = $form->addRow();
                $row->addFooter();
                $row->addSubmit();

            $form->loadAllValuesFrom($values);
        
            echo $form->getOutput();
        }
    }
}

