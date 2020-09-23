<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Forms\Form;
use Pupilsight\Forms\DatabaseFormFactory;

if (isActionAccessible($guid, $connection2, '/modules/School Admin/program_manage_edit.php') == false) {
    //Acess denied
    echo "<div class='alert alert-danger'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    //Proceed!
    $page->breadcrumbs
        ->add(__('Manage Program'), 'program_manage.php')
        ->add(__('Edit Program'));

    if (isset($_GET['return'])) {
        returnProcess($guid, $_GET['return'], null, null);
    }

    //Check if school year specified
    $pupilsightProgramID = $_GET['pupilsightProgramID'];
    if ($pupilsightProgramID == '') {
        echo "<div class='alert alert-danger'>";
        echo __('You have not specified one or more required parameters.');
        echo '</div>';
    } else {
        try {
            $data = array('pupilsightProgramID' => $pupilsightProgramID);
            $sql = 'SELECT * FROM pupilsightProgram WHERE pupilsightProgramID=:pupilsightProgramID';
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

            $form = Form::create('program', $_SESSION[$guid]['absoluteURL'].'/modules/'.$_SESSION[$guid]['module'].'/program_manage_editProcess.php?pupilsightProgramID='.$pupilsightProgramID);
            $form->setFactory(DatabaseFormFactory::create($pdo));

            $form->addHiddenValue('address', $_SESSION[$guid]['address']);

            $row = $form->addRow();
                $row->addLabel('name', __('Name'))->description(__('Must be unique.'));
                $row->addTextField('name')->required()->maxLength(15)->setValue($values['name']);

            // $row = $form->addRow();
            //     $row->addLabel('nameShort', __('Short Name'))->description(__('Must be unique.'));
            //     $row->addTextField('nameShort')->required()->maxLength(4)->setValue($values['nameShort']);

            // $row = $form->addRow();
            //     $row->addLabel('sequenceNumber', __('Sequence Number'))->description(__('Must be unique. Controls chronological ordering.'));
            //     $row->addSequenceNumber('sequenceNumber', 'pupilsightProgram', $values['sequenceNumber'])
            //         ->required()
            //         ->maxLength(3)
            //         ->setValue($values['sequenceNumber']);
            
           
            $row = $form->addRow();
                $row->addFooter();
                $row->addSubmit();

            echo $form->getOutput();
        }
    }
}
