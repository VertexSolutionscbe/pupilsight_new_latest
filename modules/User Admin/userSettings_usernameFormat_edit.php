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
        ->add(__('Manage User Settings'), 'userSettings.php')
        ->add(__('Edit Username Format'));

    if (isset($_GET['return'])) {
        returnProcess($guid, $_GET['return']);
    }

    $pupilsightUsernameFormatID = isset($_GET['pupilsightUsernameFormatID'])? $_GET['pupilsightUsernameFormatID'] : '';

    if (empty($pupilsightUsernameFormatID)) {
        echo "<div class='alert alert-danger'>";
        echo __('You have not specified one or more required parameters.');
        echo '</div>';
        return;
    }

    $data = array('pupilsightUsernameFormatID' => $pupilsightUsernameFormatID);
    $sql = "SELECT * FROM pupilsightUsernameFormat WHERE pupilsightUsernameFormatID=:pupilsightUsernameFormatID";
    $result = $pdo->executeQuery($data, $sql);

    if ($result->rowCount() == 0) {
        echo "<div class='alert alert-danger'>";
        echo __('The specified record cannot be found.');
        echo '</div>';
        return;
    }

    $values = $result->fetch();
    $values['pupilsightRoleIDList'] = explode(',', $values['pupilsightRoleIDList']);
    $values['numericValue'] = str_pad($values['numericValue'], $values['numericSize'], '0', STR_PAD_LEFT);

    $form = Form::create('usernameFormat', $_SESSION[$guid]['absoluteURL'].'/modules/'.$_SESSION[$guid]['module'].'/userSettings_usernameFormat_editProcess.php');

    $form->addHiddenValue('address', $_SESSION[$guid]['address']);
    $form->addHiddenValue('pupilsightUsernameFormatID', $pupilsightUsernameFormatID);

    $data = array('pupilsightUsernameFormatID' => $pupilsightUsernameFormatID);
    $sql = "SELECT pupilsightRole.pupilsightRoleID as value, pupilsightRole.name FROM pupilsightRole LEFT JOIN pupilsightUsernameFormat ON (FIND_IN_SET(pupilsightRole.pupilsightRoleID, pupilsightUsernameFormat.pupilsightRoleIDList) AND pupilsightUsernameFormatID<>:pupilsightUsernameFormatID) WHERE pupilsightUsernameFormatID IS NULL ORDER BY pupilsightRole.name";
    $result = $pdo->executeQuery($data, $sql);

    $row = $form->addRow();
        $row->addLabel('format', __('Username Format'))->description(__('How should usernames be formated? Choose from [preferredName], [firstName], [surname].').'<br>'.__('Use a colon to limit the number of letters, for example [preferredName:1] will use the first initial.'));
        $row->addTextField('format')->required();

    $row = $form->addRow();
        $row->addLabel('pupilsightRoleIDList', __('Roles'));
        $row->addSelect('pupilsightRoleIDList')
            ->required()
            ->selectMultiple()
            ->setSize(4)
            ->fromResults($result);

    $row = $form->addRow();
        $row->addLabel('isDefault', __('Is Default?'));
        $row->addYesNo('isDefault');

    $row = $form->addRow();
        $row->addLabel('isNumeric', __('Numeric?'))->description(__('Enables the format [number] to insert a numeric value into your username.'));
        $row->addYesNo('isNumeric');

    $form->toggleVisibilityByClass('numericValueSettings')->onSelect('isNumeric')->when('Y');

    $row = $form->addRow()->addClass('numericValueSettings');
        $row->addLabel('numericValue', __('Starting Value'))->description(__('Each time a username is generated this value will increase by the increment defined below.'));
        $row->addTextField('numericValue')->required()->maxLength(12);

    $row = $form->addRow()->addClass('numericValueSettings');
        $row->addLabel('numericSize', __('Number of Digits'));
        $row->addNumber('numericSize')->required()->minimum(0)->maximum(12);

    $row = $form->addRow()->addClass('numericValueSettings');
        $row->addLabel('numericIncrement', __('Increment By'));
        $row->addNumber('numericIncrement')->required()->minimum(0)->maximum(100);

    $row = $form->addRow();
        $row->addFooter();
        $row->addSubmit();

    $form->loadAllValuesFrom($values);

    echo $form->getOutput();
}
