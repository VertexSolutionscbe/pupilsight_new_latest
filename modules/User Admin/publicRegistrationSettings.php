<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Forms\Form;

if (isActionAccessible($guid, $connection2, '/modules/User Admin/publicRegistrationSettings.php') == false) {
    //Acess denied
    echo "<div class='alert alert-danger'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    //Proceed!
    $page->breadcrumbs->add(__('Public Registration Settings'));

    if (isset($_GET['return'])) {
        returnProcess($guid, $_GET['return'], null, null);
    }

    $form = Form::create('publicRegistrationSettings', $_SESSION[$guid]['absoluteURL'].'/modules/'.$_SESSION[$guid]['module'].'/publicRegistrationSettingsProcess.php');

    $form->addHiddenValue('address', $_SESSION[$guid]['address']);

    $row = $form->addRow()->addHeading(__('General Settings'));

    $setting = getSettingByScope($connection2, 'User Admin', 'enablePublicRegistration', true);
    $row = $form->addRow();
        $row->addLabel($setting['name'], __($setting['nameDisplay']))->description(__($setting['description']));
        $row->addYesNo($setting['name'])->selected($setting['value'])->required();

    $setting = getSettingByScope($connection2, 'User Admin', 'publicRegistrationMinimumAge', true);
    $row = $form->addRow();
        $row->addLabel($setting['name'], __($setting['nameDisplay']))->description(__($setting['description']));
        $row->addNumber($setting['name'])->setValue($setting['value'])->minimum(0)->maxLength(99);

    $setting = getSettingByScope($connection2, 'User Admin', 'publicRegistrationDefaultStatus', true);
    $row = $form->addRow();
        $row->addLabel($setting['name'], __($setting['nameDisplay']))->description(__($setting['description']));
        $row->addSelect($setting['name'])->fromString('Full, Pending Approval')->selected($setting['value'])->required();

    $sql = "SELECT pupilsightRoleID AS value, name FROM pupilsightRole ORDER BY name";
    $setting = getSettingByScope($connection2, 'User Admin', 'publicRegistrationDefaultRole', true);
    $row = $form->addRow();
        $row->addLabel($setting['name'], __($setting['nameDisplay']))->description(__($setting['description']));
        $row->addSelect($setting['name'])
            ->fromQuery($pdo, $sql)
            ->selected($setting['value'])
            ->required();

    $row = $form->addRow()->addHeading(__('Interface Options'));

    $setting = getSettingByScope($connection2, 'User Admin', 'publicRegistrationIntro', true);
    $row = $form->addRow();
        $row->addLabel($setting['name'], __($setting['nameDisplay']))->description(__($setting['description']));
        $row->addTextArea($setting['name'])->setValue($setting['value']);

    $setting = getSettingByScope($connection2, 'User Admin', 'publicRegistrationPostscript', true);
    $row = $form->addRow();
        $row->addLabel($setting['name'], __($setting['nameDisplay']))->description(__($setting['description']));
        $row->addTextArea($setting['name'])->setValue($setting['value']);

    $setting = getSettingByScope($connection2, 'User Admin', 'publicRegistrationPrivacyStatement', true);
    $row = $form->addRow();
        $row->addLabel($setting['name'], __($setting['nameDisplay']))->description(__($setting['description']));
        $row->addTextArea($setting['name'])->setValue($setting['value']);

    $setting = getSettingByScope($connection2, 'User Admin', 'publicRegistrationAgreement', true);
    $row = $form->addRow();
        $row->addLabel($setting['name'], __($setting['nameDisplay']))->description(__($setting['description']));
        $row->addTextArea($setting['name'])->setValue($setting['value']);

    $row = $form->addRow();
        $row->addFooter();
        $row->addSubmit();

    echo $form->getOutput();
}
?>
