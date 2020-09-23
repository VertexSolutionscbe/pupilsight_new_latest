<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Forms\Form;
use Pupilsight\Forms\DatabaseFormFactory;

if (isActionAccessible($guid, $connection2, '/modules/School Admin/financeSettings.php') == false) {
    //Acess denied
    echo "<div class='alert alert-danger'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    //Proceed!
    $page->breadcrumbs->add(__('Manage Finance Settings'));

    if (isset($_GET['return'])) {
        returnProcess($guid, $_GET['return'], null, null);
    }

    $form = Form::create('financeSettings', $_SESSION[$guid]['absoluteURL'].'/modules/'.$_SESSION[$guid]['module'].'/financeSettingsProcess.php');

    $form->setFactory(DatabaseFormFactory::create($pdo));
    $form->addHiddenValue('address', $_SESSION[$guid]['address']);

    $row = $form->addRow()->addHeading(__('General Settings'));

    $setting = getSettingByScope($connection2, 'Finance', 'email', true);
    $row = $form->addRow();
        $row->addLabel($setting['name'], __($setting['nameDisplay']))->description(__($setting['description']));
        $row->addEmail($setting['name'])->setValue($setting['value'])->required();

    $setting = getSettingByScope($connection2, 'Finance', 'financeOnlinePaymentEnabled', true);
    $row = $form->addRow();
        $row->addLabel($setting['name'], __($setting['nameDisplay']))->description(__($setting['description']));
        $row->addYesNo($setting['name'])->selected($setting['value'])->required();

    $form->toggleVisibilityByClass('onlinePayment')->onSelect($setting['name'])->when('Y');

    $setting = getSettingByScope($connection2, 'Finance', 'financeOnlinePaymentThreshold', true);
    $row = $form->addRow()->addClass('onlinePayment');
        $row->addLabel($setting['name'], __($setting['nameDisplay']))
            ->description(__($setting['description']))
            ->append(sprintf(__('In %1$s.'), $_SESSION[$guid]['currency']));
        $row->addNumber($setting['name'])
            ->setValue($setting['value'])
            ->decimalPlaces(2);

    $row = $form->addRow()->addHeading(__('Invoices'));

    $setting = getSettingByScope($connection2, 'Finance', 'invoiceText', true);
    $row = $form->addRow();
        $row->addLabel($setting['name'], __($setting['nameDisplay']))->description(__($setting['description']));
        $row->addTextArea($setting['name'])->setValue($setting['value']);

    $setting = getSettingByScope($connection2, 'Finance', 'invoiceNotes', true);
    $row = $form->addRow();
        $row->addLabel($setting['name'], __($setting['nameDisplay']))->description(__($setting['description']));
        $row->addTextArea($setting['name'])->setValue($setting['value']);

    $invoiceeNameStyle = array(
        'Surname, Preferred Name' => __('Surname') . ', ' . __('Preferred Name'),
        'Official Name' => __('Official Name')
    );
    $setting = getSettingByScope($connection2, 'Finance', 'invoiceeNameStyle', true);
    $row = $form->addRow();
        $row->addLabel($setting['name'], __($setting['nameDisplay']))->description(__($setting['description']));
        $row->addSelect($setting['name'])->fromArray($invoiceeNameStyle)->selected($setting['value'])->required();

    $invoiceNumber = array(
        'Invoice ID' => __('Invoice ID'), 
        'Person ID + Invoice ID' => __('Person ID')  . ' + ' . __('Invoice ID'), 
        'Student ID + Invoice ID' => __('Student ID') . ' + ' . __('Invoice ID')
    );
    $setting = getSettingByScope($connection2, 'Finance', 'invoiceNumber', true);
    $row = $form->addRow();
        $row->addLabel($setting['name'], __($setting['nameDisplay']))->description(__($setting['description']));
        $row->addSelect($setting['name'])->fromArray($invoiceNumber)->selected($setting['value'])->required();

    $row = $form->addRow()->addHeading(__('Receipts'));

    $setting = getSettingByScope($connection2, 'Finance', 'receiptText', true);
    $row = $form->addRow();
        $row->addLabel($setting['name'], __($setting['nameDisplay']))->description(__($setting['description']));
        $row->addTextArea($setting['name'])->setValue($setting['value']);

    $setting = getSettingByScope($connection2, 'Finance', 'receiptNotes', true);
    $row = $form->addRow();
        $row->addLabel($setting['name'], __($setting['nameDisplay']))->description(__($setting['description']));
        $row->addTextArea($setting['name'])->setValue($setting['value']);

    $setting = getSettingByScope($connection2, 'Finance', 'hideItemisation', true);
    $row = $form->addRow();
        $row->addLabel($setting['name'], __($setting['nameDisplay']))->description(__($setting['description']));
        $row->addYesNo($setting['name'])->selected($setting['value'])->required();

    $row = $form->addRow()->addHeading(__('Reminders'));

    $setting = getSettingByScope($connection2, 'Finance', 'reminder1Text', true);
    $row = $form->addRow();
        $row->addLabel($setting['name'], __($setting['nameDisplay']))->description(__($setting['description']));
        $row->addTextArea($setting['name'])->setValue($setting['value']);

    $setting = getSettingByScope($connection2, 'Finance', 'reminder2Text', true);
    $row = $form->addRow();
        $row->addLabel($setting['name'], __($setting['nameDisplay']))->description(__($setting['description']));
        $row->addTextArea($setting['name'])->setValue($setting['value']);

    $setting = getSettingByScope($connection2, 'Finance', 'reminder3Text', true);
    $row = $form->addRow();
        $row->addLabel($setting['name'], __($setting['nameDisplay']))->description(__($setting['description']));
        $row->addTextArea($setting['name'])->setValue($setting['value']);

    $row = $form->addRow()->addHeading(__('Expenses'));

    $setting = getSettingByScope($connection2, 'Finance', 'budgetCategories', true);
    $row = $form->addRow();
        $row->addLabel($setting['name'], __($setting['nameDisplay']))->description(__($setting['description']));
        $row->addTextArea($setting['name'])->setValue($setting['value'])->required();

    $setting = getSettingByScope($connection2, 'Finance', 'expenseApprovalType', true);
    $row = $form->addRow();
        $row->addLabel($setting['name'], __($setting['nameDisplay']))->description(__($setting['description']));
        $row->addSelect($setting['name'])->fromString('One Of, Two Of, Chain Of All')->selected($setting['value'])->required();

    $setting = getSettingByScope($connection2, 'Finance', 'budgetLevelExpenseApproval', true);
    $row = $form->addRow();
        $row->addLabel($setting['name'], __($setting['nameDisplay']))->description(__($setting['description']));
        $row->addYesNo($setting['name'])->selected($setting['value'])->required();

    $setting = getSettingByScope($connection2, 'Finance', 'expenseRequestTemplate', true);
    $row = $form->addRow();
        $row->addLabel($setting['name'], __($setting['nameDisplay']))->description(__($setting['description']));
        $row->addTextArea($setting['name'])->setValue($setting['value']);

    $setting = getSettingByScope($connection2, 'Finance', 'allowExpenseAdd', true);
    $row = $form->addRow();
        $row->addLabel($setting['name'], __($setting['nameDisplay']))->description(__($setting['description']));
        $row->addYesNo($setting['name'])->selected($setting['value'])->required();

    $setting = getSettingByScope($connection2, 'Finance', 'purchasingOfficer', true);
    $row = $form->addRow();
        $row->addLabel($setting['name'], __($setting['nameDisplay']))->description(__($setting['description']));
        $row->addSelectStaff($setting['name'])
            ->selected($setting['value'])
            ->placeholder('');

    $setting = getSettingByScope($connection2, 'Finance', 'reimbursementOfficer', true);
    $row = $form->addRow();
        $row->addLabel($setting['name'], __($setting['nameDisplay']))->description(__($setting['description']));
        $row->addSelectStaff($setting['name'])
            ->selected($setting['value'])
            ->placeholder('');

    $row = $form->addRow();
        $row->addFooter();
        $row->addSubmit();

    echo $form->getOutput();
}
