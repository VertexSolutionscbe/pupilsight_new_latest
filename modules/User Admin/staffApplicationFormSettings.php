<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Forms\Form;

if (isActionAccessible($guid, $connection2, '/modules/User Admin/staffApplicationFormSettings.php') == false) {
    //Acess denied
    echo "<div class='alert alert-danger'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    //Proceed!
    $page->breadcrumbs->add(__('Staff Application Form Settings'));

    if (isset($_GET['return'])) {
        returnProcess($guid, $_GET['return'], null, null);
    }

    $form = Form::create('staffApplicationFormSettings', $_SESSION[$guid]['absoluteURL'].'/modules/'.$_SESSION[$guid]['module'].'/staffApplicationFormSettingsProcess.php');

    $form->addHiddenValue('address', $_SESSION[$guid]['address']);

    $row = $form->addRow()->addHeading(__('General Options'));

    $setting = getSettingByScope($connection2, 'Staff', 'staffApplicationFormIntroduction', true);
    $row = $form->addRow();
        $row->addLabel($setting['name'], __($setting['nameDisplay']))->description(__($setting['description']));
        $row->addTextArea($setting['name'])->setValue($setting['value']);

    $setting = getSettingByScope($connection2, 'Staff', 'staffApplicationFormQuestions', true);
    $row = $form->addRow();
        $row->addLabel($setting['name'], __($setting['nameDisplay']))->description(__($setting['description']));
        $row->addTextArea($setting['name'])->setValue($setting['value']);

    $setting = getSettingByScope($connection2, 'Staff', 'staffApplicationFormPostscript', true);
    $row = $form->addRow();
        $row->addLabel($setting['name'], __($setting['nameDisplay']))->description(__($setting['description']));
        $row->addTextArea($setting['name'])->setValue($setting['value']);

    $setting = getSettingByScope($connection2, 'Staff', 'staffApplicationFormAgreement', true);
    $row = $form->addRow();
        $row->addLabel($setting['name'], __($setting['nameDisplay']))->description(__($setting['description']));
        $row->addTextArea($setting['name'])->setValue($setting['value']);

    $setting = getSettingByScope($connection2, 'Staff Application Form', 'staffApplicationFormPublicApplications', true);
    $row = $form->addRow();
        $row->addLabel($setting['name'], __($setting['nameDisplay']))->description(__($setting['description']));
        $row->addYesNo($setting['name'])->selected($setting['value'])->required();

    $setting = getSettingByScope($connection2, 'Staff', 'staffApplicationFormMilestones', true);
    $row = $form->addRow();
        $row->addLabel($setting['name'], __($setting['nameDisplay']))->description(__($setting['description']));
        $row->addTextArea($setting['name'])->setValue($setting['value']);

    $setting = getSettingByScope($connection2, 'Staff', 'applicationFormRefereeLink', true);
    $row = $form->addRow()->addHeading(__($setting['nameDisplay']))->append(__($setting['description']));

    $applicationFormRefereeLink = unserialize($setting['value']);

    $types=array() ;
    $types[0] = 'Teaching';
    $types[1] = 'Support';
    $typeCount = 2 ;
    try {
        $dataSelect = array();
        $sqlSelect = "SELECT * FROM pupilsightRole WHERE category='Staff' ORDER BY name";
        $resultSelect = $connection2->prepare($sqlSelect);
        $resultSelect->execute($dataSelect);
    } catch (PDOException $e) {}
    while ($rowSelect = $resultSelect->fetch()) {
        $types[$typeCount] = $rowSelect['name'];
        $typeCount++;
    }
    $typeCount=0 ;
    foreach ($types AS $type) {
        $row = $form->addRow();
        if ($typeCount==0 || $typeCount==1)
            $row->addLabel($setting['name'], __("Staff Type").": ".__($type));
        else
            $row->addLabel($setting['name'], __("Staff Role").": ".__($type));
        $form->addHiddenValue("types[".$typeCount."]", $type);
        $row->addURL("refereeLinks[]")->setID('refereeLink'.$typeCount)->setValue(isset($applicationFormRefereeLink[$type]) ? $applicationFormRefereeLink[$type] : '');
        $typeCount++;
    }

    $row = $form->addRow()->addHeading(__('Required Documents Options'));

    $setting = getSettingByScope($connection2, 'Staff', 'staffApplicationFormRequiredDocuments', true);
    $row = $form->addRow();
        $row->addLabel($setting['name'], __($setting['nameDisplay']))->description(__($setting['description']));
        $row->addTextArea($setting['name'])->setValue($setting['value']);

    $setting = getSettingByScope($connection2, 'Staff', 'staffApplicationFormRequiredDocumentsText', true);
    $row = $form->addRow();
        $row->addLabel($setting['name'], __($setting['nameDisplay']))->description(__($setting['description']));
        $row->addTextArea($setting['name'])->setValue($setting['value']);

    $setting = getSettingByScope($connection2, 'Staff', 'staffApplicationFormRequiredDocumentsCompulsory', true);
    $row = $form->addRow();
        $row->addLabel($setting['name'], __($setting['nameDisplay']))->description(__($setting['description']));
        $row->addYesNo($setting['name'])->selected($setting['value'])->required();

    $row = $form->addRow()->addHeading(__('Acceptance Options'));

    $setting = getSettingByScope($connection2, 'Staff', 'staffApplicationFormUsernameFormat', true);
    $row = $form->addRow();
        $row->addLabel($setting['name'], __($setting['nameDisplay']))->description(__($setting['description']));
        $row->addTextField($setting['name'])->setValue($setting['value']);

    $setting = getSettingByScope($connection2, 'Staff', 'staffApplicationFormNotificationMessage', true);
    $row = $form->addRow();
        $row->addLabel($setting['name'], __($setting['nameDisplay']))->description(__($setting['description']));
        $row->addTextArea($setting['name'])->setValue($setting['value']);

    $setting = getSettingByScope($connection2, 'Staff', 'staffApplicationFormNotificationDefault', true);
    $row = $form->addRow();
        $row->addLabel($setting['name'], __($setting['nameDisplay']))->description(__($setting['description']));
        $row->addYesNo($setting['name'])->selected($setting['value'])->required();

    $setting = getSettingByScope($connection2, 'Staff', 'staffApplicationFormDefaultEmail', true);
    $row = $form->addRow();
        $row->addLabel($setting['name'], __($setting['nameDisplay']))->description(__($setting['description']));
        $row->addTextField($setting['name'])->setValue($setting['value']);

    $setting = getSettingByScope($connection2, 'Staff', 'staffApplicationFormDefaultWebsite', true);
    $row = $form->addRow();
        $row->addLabel($setting['name'], __($setting['nameDisplay']))->description(__($setting['description']));
        $row->addTextField($setting['name'])->setValue($setting['value']);

    $row = $form->addRow();
        $row->addFooter();
        $row->addSubmit();

    echo $form->getOutput();
}
?>
