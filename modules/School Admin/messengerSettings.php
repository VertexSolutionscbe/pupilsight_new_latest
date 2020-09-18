<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Forms\Form;

if (isActionAccessible($guid, $connection2, '/modules/School Admin/messengerSettings.php') == false) {
    //Acess denied
    echo "<div class='alert alert-danger'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    //Proceed!
    $page->breadcrumbs->add(__('Manage Messenger Settings'));

    if (isset($_GET['return'])) {
        returnProcess($guid, $_GET['return'], null, null);
    }


    $form = Form::create('messengerSettings', $_SESSION[$guid]['absoluteURL'].'/modules/'.$_SESSION[$guid]['module'].'/messengerSettingsProcess.php' );

    $form->addHiddenValue('address', $_SESSION[$guid]['address']);

    $row = $form->addRow()->addHeading(__('SMS Settings'));

    $row = $form->addRow()->addAlert(__('Pupilsight can use a number of different gateways to send out SMS messages. These are paid services, not affiliated with Pupilsight, and you must create your own account with them before being able to send out SMSs using the Messenger module.').' '.sprintf(__('%1$sClick here%2$s to configure SMS settings.'), "<a href='".$_SESSION[$guid]['absoluteURL']."/index.php?q=/modules/System Admin/thirdPartySettings.php'>", "</a>"));

	$row = $form->addRow()->addHeading(__('Message Wall Settings'));

	$setting = getSettingByScope($connection2, 'Messenger', 'messageBubbleWidthType', true);
	$row = $form->addRow();
    	$row->addLabel($setting['name'], __($setting['nameDisplay']))->description(__($setting['description']));
    	$row->addSelect($setting['name'])->fromString('Regular, Wide')->selected($setting['value'])->required();

    $setting = getSettingByScope($connection2, 'Messenger', 'messageBubbleBGColor', true);
	$row = $form->addRow();
    	$row->addLabel($setting['name'], __($setting['nameDisplay']))->description(__($setting['description']));
		$row->addTextField($setting['name'])->setValue($setting['value']);

	$setting = getSettingByScope($connection2, 'Messenger', 'messageBubbleAutoHide', true);
	$row = $form->addRow();
    	$row->addLabel($setting['name'], __($setting['nameDisplay']))->description(__($setting['description']));
		$row->addYesNo($setting['name'])->selected($setting['value'])->required();

	$setting = getSettingByScope($connection2, 'Messenger', 'enableHomeScreenWidget', true);
	$row = $form->addRow();
    	$row->addLabel($setting['name'], __($setting['nameDisplay']))->description(__($setting['description']));
        $row->addYesNo($setting['name'])->selected($setting['value'])->required();

    $row = $form->addRow()->addHeading(__('Miscellaneous'));

	$setting = getSettingByScope($connection2, 'Messenger', 'messageBcc', true);
	$row = $form->addRow();
    	$row->addLabel($setting['name'], __($setting['nameDisplay']))->description(__($setting['description']));
		$row->addTextArea($setting['name'])->setValue($setting['value'])->setRows(2);
    $setting = getSettingByScope($connection2, 'Messenger', 'smsCopy', true);
    $row = $form->addRow();
        $row->addLabel($setting['name'], __($setting['nameDisplay']))->description(__($setting['description']));
        $row->addTextArea($setting['name'])->setValue($setting['value'])->setRows(2);

	$row = $form->addRow();
		$row->addFooter();
		$row->addSubmit();

	echo $form->getOutput();

}
