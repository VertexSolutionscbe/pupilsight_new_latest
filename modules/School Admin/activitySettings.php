<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Forms\Form;

if (isActionAccessible($guid, $connection2, '/modules/School Admin/activitySettings.php') == false) {
    //Acess denied
    echo "<div class='alert alert-danger'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    //Proceed!
    $page->breadcrumbs->add(__('Manage Activity Settings'));

    if (isset($_GET['return'])) {
        returnProcess($guid, $_GET['return'], null, null);
    }

    $form = Form::create('activitySettings', $_SESSION[$guid]['absoluteURL'].'/modules/'.$_SESSION[$guid]['module'].'/activitySettingsProcess.php');

    $form->addHiddenValue('address', $_SESSION[$guid]['address']);

    $dateTypes = array(
        'Date' => __('Date'),
        'Term' =>  __('Term')
    );  
    $setting = getSettingByScope($connection2, 'Activities', 'dateType', true);
    $row = $form->addRow();
        $row->addLabel($setting['name'], __($setting['nameDisplay']))->description($setting['description']);
        $row->addSelect($setting['name'])->fromArray($dateTypes)->selected($setting['value'])->required();    

    $form->toggleVisibilityByClass('perTerm')->onSelect($setting['name'])->when('Term');

    $setting = getSettingByScope($connection2, 'Activities', 'maxPerTerm', true);
    $row = $form->addRow()->addClass('perTerm');
        $row->addLabel($setting['name'], __($setting['nameDisplay']))->description(__($setting['description']));
        $row->addSelect($setting['name'])->fromString('0,1,2,3,4,5')->selected($setting['value'])->required();

    $accessTypes = array(
        'None' => __('None'),
        'View' => __('View'),
        'Register' =>  __('Register')
    ); 
    $setting = getSettingByScope($connection2, 'Activities', 'access', true);
    $row = $form->addRow();
        $row->addLabel($setting['name'], __($setting['nameDisplay']))->description(__($setting['description']));
        $row->addSelect($setting['name'])->fromArray($accessTypes)->selected($setting['value'])->required();

    $paymentTypes = array(
        'None' => __('None'),
        'Single' => __('Single'),
        'Per Activity' =>  __('Per Activity'),
        'Single + Per Activity' =>  __('Single + Per Activity')
    );    
    $setting = getSettingByScope($connection2, 'Activities', 'payment', true);
    $row = $form->addRow();
        $row->addLabel($setting['name'], __($setting['nameDisplay']))->description(__($setting['description']));
        $row->addSelect($setting['name'])->fromArray($paymentTypes)->selected($setting['value'])->required();

    $enrolmentTypes = array(
        'Competitive' => __('Competitive'),
        'Selection' => __('Selection')
    ); 
    $setting = getSettingByScope($connection2, 'Activities', 'enrolmentType', true);
    $row = $form->addRow();
        $row->addLabel($setting['name'], __($setting['nameDisplay']))->description(__($setting['description']));
        $row->addSelect($setting['name'])->fromArray($enrolmentTypes)->selected($setting['value'])->required();

    $setting = getSettingByScope($connection2, 'Activities', 'backupChoice', true);
    $row = $form->addRow();
        $row->addLabel($setting['name'], __($setting['nameDisplay']))->description(__($setting['description']));
        $row->addYesNo($setting['name'])->selected($setting['value'])->required();


    $setting = getSettingByScope($connection2, 'Activities', 'activityTypes', true);
    $row = $form->addRow();
        $row->addLabel($setting['name'], __($setting['nameDisplay']))->description(__($setting['description']));
        $row->addTextarea($setting['name'])->setValue($setting['value']);

    $setting = getSettingByScope($connection2, 'Activities', 'disableExternalProviderSignup', true);
    $row = $form->addRow();
        $row->addLabel($setting['name'], __($setting['nameDisplay']))->description(__($setting['description']));
        $row->addYesNo($setting['name'])->selected($setting['value'])->required();

    $setting = getSettingByScope($connection2, 'Activities', 'hideExternalProviderCost', true);
    $row = $form->addRow();
        $row->addLabel($setting['name'], __($setting['nameDisplay']))->description(__($setting['description']));
        $row->addYesNo($setting['name'])->selected($setting['value'])->required();

    $row = $form->addRow();
        $row->addFooter();
        $row->addSubmit();

    echo $form->getOutput();
}
