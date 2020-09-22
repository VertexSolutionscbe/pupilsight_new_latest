<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Forms\Form;

if (isActionAccessible($guid, $connection2, '/modules/User Admin/userSettings.php') == false) {
    //Acess denied
    echo "<div class='alert alert-danger'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    //Proceed!
    $page->breadcrumbs
        ->add(__('Manage User Settings'),'userSettings.php')
        ->add(__('Add Username Format'));       

    $editLink = '';
    if (isset($_GET['editID'])) {
        $editLink = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/User Admin/userSettings_usernameFormat_edit.php&pupilsightUsernameFormatID='.$_GET['editID'];
    }
    if (isset($_GET['return'])) {
        returnProcess($guid, $_GET['return'], $editLink);
    }

    $form = Form::create('usernameFormat', $_SESSION[$guid]['absoluteURL'].'/modules/'.$_SESSION[$guid]['module'].'/userSettings_usernameFormat_addProcess.php');

    $form->addHiddenValue('address', $_SESSION[$guid]['address']);

    $sql = "SELECT pupilsightRole.pupilsightRoleID as value, pupilsightRole.name FROM pupilsightRole LEFT JOIN pupilsightUsernameFormat ON (FIND_IN_SET(pupilsightRole.pupilsightRoleID, pupilsightUsernameFormat.pupilsightRoleIDList)) WHERE pupilsightUsernameFormatID IS NULL ORDER BY pupilsightRole.name";
    $result = $pdo->executeQuery(array(), $sql);

    $row = $form->addRow();
        $row->addLabel('format', __('Username Format'))->description(__('How should usernames be formated? Choose from [preferredName], [firstName], [surname].').'<br>'.__('Use a colon to limit the number of letters, for example [preferredName:1] will use the first initial.'));
        $row->addTextField('format')->required()->setValue('[preferredName:1][surname]');

    $row = $form->addRow();
        $row->addLabel('pupilsightRoleIDList', __('Roles'));
        $row->addSelect('pupilsightRoleIDList')
            ->required()
            ->selectMultiple()
            ->setSize(4)
            ->fromResults($result);

    $row = $form->addRow();
        $row->addLabel('isDefault', __('Is Default?'));
        $row->addYesNo('isDefault')->selected('N');

    $row = $form->addRow();
        $row->addLabel('isNumeric', __('Numeric?'))->description(__('Enables the format [number] to insert a numeric value into your username.'));
        $row->addYesNo('isNumeric')->selected('N');

    $form->toggleVisibilityByClass('numericValueSettings')->onSelect('isNumeric')->when('Y');

    $row = $form->addRow()->addClass('numericValueSettings');
        $row->addLabel('numericValue', __('Starting Value'))->description(__('Each time a username is generated this value will increase by the increment defined below.'));
        $row->addTextField('numericValue')->required()->setValue('0')->maxLength(12);

    $row = $form->addRow()->addClass('numericValueSettings');
        $row->addLabel('numericSize', __('Number of Digits'));
        $row->addNumber('numericSize')->required()->setValue('4')->minimum(0)->maximum(12);

    $row = $form->addRow()->addClass('numericValueSettings');
        $row->addLabel('numericIncrement', __('Increment By'));
        $row->addNumber('numericIncrement')->required()->setValue('1')->minimum(0)->maximum(100);

    $row = $form->addRow();
        $row->addFooter();
        $row->addSubmit();

    echo $form->getOutput();
}
