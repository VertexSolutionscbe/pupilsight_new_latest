<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Forms\Form;

if (isActionAccessible($guid, $connection2, '/modules/User Admin/studentsSettings_noteCategory_edit.php') == false) {
    //Acess denied
    echo "<div class='alert alert-danger'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    //Proceed!
    $page->breadcrumbs
        ->add(__('Manage Students Settings'), 'studentsSettings.php')
        ->add(__('Edit Note Category'));

    if (isset($_GET['return'])) {
        returnProcess($guid, $_GET['return'], null, null);
    }
    //Check if school year specified
    $pupilsightStudentNoteCategoryID = $_GET['pupilsightStudentNoteCategoryID'];
    if ($pupilsightStudentNoteCategoryID == '') {
        echo "<div class='alert alert-danger'>";
        echo __('You have not specified one or more required parameters.');
        echo '</div>';
    } else {
        try {
            $data = array('pupilsightStudentNoteCategoryID' => $pupilsightStudentNoteCategoryID);
            $sql = 'SELECT * FROM pupilsightStudentNoteCategory WHERE pupilsightStudentNoteCategoryID=:pupilsightStudentNoteCategoryID';
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
            
            $form = Form::create('noteCategory', $_SESSION[$guid]['absoluteURL'].'/modules/'.$_SESSION[$guid]['module'].'/studentsSettings_noteCategory_editProcess.php?pupilsightStudentNoteCategoryID='.$pupilsightStudentNoteCategoryID);
            
            $form->addHiddenValue('address', $_SESSION[$guid]['address']);
        
            $row = $form->addRow();
                $row->addLabel('name', __('Name'))->description(__('Must be unique.'));
                $row->addTextField('name')->required()->maxLength(30);
            
            $row = $form->addRow();
                $row->addLabel('active', __('Active'));
                $row->addYesNo('active')->required();
        
            $row = $form->addRow();
                $row->addLabel('template', __('Template'))->description(__('HTML code to be inserted into blank note.'));
                $row->addTextArea('template')->setRows(8);
        
            $row = $form->addRow();
                $row->addFooter();
                $row->addSubmit();

            $form->loadAllValuesFrom($values);
        
            echo $form->getOutput();
        }
    }
}
