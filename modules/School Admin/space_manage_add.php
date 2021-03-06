<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Forms\Form;

if (isActionAccessible($guid, $connection2, '/modules/School Admin/space_manage_add.php') == false) {
    //Acess denied
    echo "<div class='alert alert-danger'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    //Proceed!
    $page->breadcrumbs
        ->add(__('Manage Facilities'), 'space_manage.php')
        ->add(__('Add Facility'));

    $editLink = '';
    if (isset($_GET['editID'])) {
        $editLink = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/School Admin/space_manage_edit.php&pupilsightSpaceID='.$_GET['editID'];
    }
    if (isset($_GET['return'])) {
        returnProcess($guid, $_GET['return'], $editLink, null);
    }

    $form = Form::create('spaceAdd', $_SESSION[$guid]['absoluteURL'].'/modules/'.$_SESSION[$guid]['module'].'/space_manage_addProcess.php');

    $form->addHiddenValue('address', $_SESSION[$guid]['address']);

    $row = $form->addRow();
        $row->addLabel('name', __('Name'))->description(__('Must be unique.'));
        $row->addTextField('name')->required()->maxLength(30);

    $types = getSettingByScope($connection2, 'School Admin', 'facilityTypes');

    $row = $form->addRow();
        $row->addLabel('type', __('Type'));
        $row->addSelect('type')->fromString($types)->required()->placeholder();

    $row = $form->addRow();
        $row->addLabel('capacity', __('Capacity'));
        $row->addNumber('capacity')->maxLength(5)->setValue('0');

    $row = $form->addRow();
        $row->addLabel('computer', __('Teacher\'s Computer'));
        $row->addYesNo('computer')->selected('N');

    $row = $form->addRow();
        $row->addLabel('computerStudent', __('Student Computers'))->description(__('How many are there'));
        $row->addNumber('computerStudent')->maxLength(5)->setValue('0');

    $row = $form->addRow();
        $row->addLabel('projector', __('Projector'));
        $row->addYesNo('projector')->selected('N');

    $row = $form->addRow();
        $row->addLabel('tv', __('TV'));
        $row->addYesNo('tv')->selected('N');

    $row = $form->addRow();
        $row->addLabel('dvd', __('DVD Player'));
        $row->addYesNo('dvd')->selected('N');

    $row = $form->addRow();
        $row->addLabel('hifi', __('Hifi'));
        $row->addYesNo('hifi')->selected('N');

    $row = $form->addRow();
        $row->addLabel('speakers', __('Speakers'));
        $row->addYesNo('speakers')->selected('N');

    $row = $form->addRow();
        $row->addLabel('iwb', __('Interactive White Board'));
        $row->addYesNo('iwb')->selected('N');

    $row = $form->addRow();
        $row->addLabel('phoneInternal', __('Extension'))->description(__('Room\'s internal phone number.'));
        $row->addTextField('phoneInternal')->maxLength(5);

    $row = $form->addRow();
        $row->addLabel('phoneExternal', __('Phone Number'))->description(__('Room\'s external phone number.'));
        $row->addTextField('phoneExternal')->maxLength(20);

    $row = $form->addRow();
        $row->addLabel('comment', __('Comment'));
        $row->addTextArea('comment')->setRows(8);

    $row = $form->addRow();
        $row->addFooter();
        $row->addSubmit();

    echo $form->getOutput();
}
