<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Forms\Form;

if (isActionAccessible($guid, $connection2, '/modules/System Admin/stringReplacement_manage_add.php') == false) {
    //Acess denied
    echo "<div class='alert alert-danger'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    $search = '';
    if (isset($_GET['search'])) {
        $search = $_GET['search'];
    }

    //Proceed!
    $page->breadcrumbs
        ->add(__('Manage String Replacements'), 'stringReplacement_manage.php')
        ->add(__('Add String'));

    $editLink = '';
    if (isset($_GET['editID'])) {
        $editLink = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/System Admin/stringReplacement_manage_edit.php&pupilsightStringID='.$_GET['editID'];
    }
    if (isset($_GET['return'])) {
        returnProcess($guid, $_GET['return'], $editLink, null);
    }

    if ($search != '') {
        echo "<div class='linkTop'>";
        echo "<a href='".$_SESSION[$guid]['absoluteURL']."/index.php?q=/modules/System Admin/stringReplacement_manage.php&search=$search'>".__('Back to Search Results').'</a>';
        echo '</div>';
    }

    $form = Form::create('addString', $_SESSION[$guid]['absoluteURL'].'/modules/'.$_SESSION[$guid]['module'].'/stringReplacement_manage_addProcess.php?search='.$search);

    $form->addHiddenValue('address', $_SESSION[$guid]['address']);

    $row = $form->addRow();
        $row->addLabel('original', __('Original String'));
        $row->addTextField('original')->required()->maxLength(100);

    $row = $form->addRow();
        $row->addLabel('replacement', __('Replacement String'));
        $row->addTextField('replacement')->required()->maxLength(100);

    $row = $form->addRow();
        $row->addLabel('mode', __('Mode'));
        $row->addSelect('mode')->fromArray(array('Whole' => __('Whole'), 'Partial' => __('Partial')));

    $row = $form->addRow();
        $row->addLabel('caseSensitive', __('Case Sensitive'));
        $row->addYesNo('caseSensitive')->selected('N')->required();

    $row = $form->addRow();
        $row->addLabel('priority', __('Priority'))->description(__('Higher priorities are substituted first.'));
        $row->addNumber('priority')->required()->maxLength(2)->setValue('0');

    $row = $form->addRow();
        $row->addFooter();
        $row->addSubmit();

    echo $form->getOutput();
}
