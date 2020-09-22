<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Forms\Form;

if (isActionAccessible($guid, $connection2, '/modules/System Admin/stringReplacement_manage_edit.php') == false) {
    //Acess denied
    echo "<div class='alert alert-danger'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    //Proceed!
    $page->breadcrumbs
        ->add(__('Manage String Replacements'), 'stringReplacement_manage.php')
        ->add(__('Edit String'));

    $search = $_GET['search'] ?? '';

    if (isset($_GET['return'])) {
        returnProcess($guid, $_GET['return'], null, null);
    }

    //Check if StringID specified
    $pupilsightStringID = $_GET['pupilsightStringID'] ?? '';
    
    if ($pupilsightStringID == '') {
        echo "<div class='alert alert-danger'>";
        echo __('You have not specified one or more required parameters.');
        echo '</div>';
    } else {
        try {
            $data = array('pupilsightStringID' => $pupilsightStringID);
            $sql = 'SELECT * FROM pupilsightString WHERE pupilsightStringID=:pupilsightStringID';
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

            if ($search != '') {
                echo "<div class='linkTop'>";
                echo "<a href='".$_SESSION[$guid]['absoluteURL']."/index.php?q=/modules/System Admin/stringReplacement_manage.php&search=$search'>".__('Back to Search Results').'</a>';
                echo '</div>';
            }

            $form = Form::create('editString', $_SESSION[$guid]['absoluteURL'].'/modules/'.$_SESSION[$guid]['module'].'/stringReplacement_manage_editProcess.php?pupilsightStringID='.$values['pupilsightStringID'].'&search='.$search);

            $form->addHiddenValue('address', $_SESSION[$guid]['address']);

            $row = $form->addRow();
                $row->addLabel('original', __('Original String'));
                $row->addTextField('original')->required()->maxLength(100)->setValue($values['original']);

            $row = $form->addRow();
                $row->addLabel('replacement', __('Replacement String'));
                $row->addTextField('replacement')->required()->maxLength(100)->setValue($values['replacement']);

            $row = $form->addRow();
                $row->addLabel('mode', __('Mode'));
                $row->addSelect('mode')
                    ->fromArray(array('Whole' => __('Whole'), 'Partial' => __('Partial')))
                    ->selected($values['mode']);

            $row = $form->addRow();
                $row->addLabel('caseSensitive', __('Case Sensitive'));
                $row->addYesNo('caseSensitive')->selected('N')->required()->selected($values['caseSensitive']);

            $row = $form->addRow();
                $row->addLabel('priority', __('Priority'))->description(__('Higher priorities are substituted first.'));
                $row->addNumber('priority')->required()->maxLength(2)->setValue($values['priority']);

            $row = $form->addRow();
                $row->addFooter();
                $row->addSubmit();

            echo $form->getOutput();
        }
    }
}
