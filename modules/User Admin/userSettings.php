<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Forms\Form;
use Pupilsight\Services\Format;
use Pupilsight\Tables\DataTable;
use Pupilsight\Domain\User\UsernameFormatGateway;

if (isActionAccessible($guid, $connection2, '/modules/User Admin/userSettings.php') == false) {
    //Acess denied
    echo "<div class='alert alert-danger'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    //Proceed!
    $page->breadcrumbs->add(__('Manage User Settings'));

    if (isset($_GET['return'])) {
        returnProcess($guid, $_GET['return'], null, null);
    }

    echo '<h3>';
    echo __('Username Formats');
    echo '</h3>';

    echo "<div class='linkTop'>";
    echo "<a href='" . $_SESSION[$guid]['absoluteURL'] . '/index.php?q=/modules/' . $_SESSION[$guid]['module'] . "/userSettings_usernameFormat_add.php'>" . __('Add') . "<img style='margin-left: 5px' title='" . __('Add') . "' src='./themes/" . $_SESSION[$guid]['pupilsightThemeName'] . "/img/page_new.png'/></a>";
    echo '</div>';

    $gateway = $container->get(UsernameFormatGateway::class);
    $usernameFormats = $gateway->selectUsernameFormats();

    $table = DataTable::create('usernameFormats');

    $table->addColumn('roles', __('Roles'));
    $table->addColumn('format', __('Format'));
    $table->addColumn('isDefault', __('Is Default?'))->format(Format::using('yesNo', 'isDefault'));

    $table->addActionColumn()
        ->addParam('pupilsightUsernameFormatID')
        ->format(function ($row, $actions) {
            $actions->addAction('edit', __('Edit'))
                ->setURL('/modules/User Admin/userSettings_usernameFormat_edit.php');
            if ($row['isDefault'] == 'N') {
                $actions->addAction('delete', __('Delete'))
                    ->setURL('/modules/User Admin/userSettings_usernameFormat_delete.php');
            }
        });

    echo $table->render($usernameFormats->toDataSet());

    echo '<br/>';

    echo '<h3>';
    echo __('Settings');
    echo '</h3>';

    $form = Form::create('userSettings', $_SESSION[$guid]['absoluteURL'] . '/modules/' . $_SESSION[$guid]['module'] . '/userSettingsProcess.php');

    $form->addHiddenValue('address', $_SESSION[$guid]['address']);

    $row = $form->addRow()->addHeading(__('Field Values'));

    $setting = getSettingByScope($connection2, 'User Admin', 'nationality', true);
    $row = $form->addRow();
    $row->addLabel($setting['name'], __($setting['nameDisplay']))->description($setting['description']);
    $row->addTextArea($setting['name'])->setValue($setting['value']);

    $setting = getSettingByScope($connection2, 'User Admin', 'ethnicity', true);
    $row = $form->addRow();
    $row->addLabel($setting['name'], __($setting['nameDisplay']))->description($setting['description']);
    $row->addTextArea($setting['name'])->setValue($setting['value']);

    $setting = getSettingByScope($connection2, 'User Admin', 'religions', true);
    $row = $form->addRow();
    $row->addLabel($setting['name'], __($setting['nameDisplay']))->description($setting['description']);
    $row->addTextArea($setting['name'])->setValue($setting['value']);

    $setting = getSettingByScope($connection2, 'User Admin', 'residencyStatus', true);
    $row = $form->addRow();
    $row->addLabel($setting['name'], __($setting['nameDisplay']))->description($setting['description']);
    $row->addTextArea($setting['name'])->setValue($setting['value']);

    $setting = getSettingByScope($connection2, 'User Admin', 'departureReasons', true);
    $row = $form->addRow();
    $row->addLabel($setting['name'], __($setting['nameDisplay']))->description($setting['description']);
    $row->addTextArea($setting['name'])->setValue($setting['value']);

    $row = $form->addRow()->addHeading(__('Privacy Options'));

    $setting = getSettingByScope($connection2, 'User Admin', 'privacy', true);
    $row = $form->addRow();
    $row->addLabel($setting['name'], __($setting['nameDisplay']))->description($setting['description']);
    $row->addYesNo($setting['name'])->selected($setting['value'])->required();

    $form->toggleVisibilityByClass('privacy')->onSelect($setting['name'])->when('Y');

    $setting = getSettingByScope($connection2, 'User Admin', 'privacyBlurb', true);
    $row = $form->addRow()->addClass('privacy');
    $row->addLabel($setting['name'], __($setting['nameDisplay']))->description($setting['description']);
    $row->addTextArea($setting['name'])->setValue($setting['value']);

    $setting = getSettingByScope($connection2, 'User Admin', 'privacyOptions', true);
    $row = $form->addRow()->addClass('privacy');
    $row->addLabel($setting['name'], __($setting['nameDisplay']))->description($setting['description']);
    $row->addTextArea($setting['name'])->setValue($setting['value']);

    $row = $form->addRow()->addHeading(__('User Data Options'));

    $setting = getSettingByScope($connection2, 'User Admin', 'uniqueEmailAddress', true);
    $row = $form->addRow();
    $row->addLabel($setting['name'], __($setting['nameDisplay']))->description(__($setting['description']));
    $row->addYesNo($setting['name'])->selected($setting['value']);

    $row = $form->addRow()->addHeading(__('User Interface Options'));

    $setting = getSettingByScope($connection2, 'User Admin', 'personalBackground', true);
    $row = $form->addRow();
    $row->addLabel($setting['name'], __($setting['nameDisplay']))->description($setting['description']);
    $row->addYesNo($setting['name'])->selected($setting['value'])->required();

    $row = $form->addRow()->addHeading(__('Day-Type Options'));

    $setting = getSettingByScope($connection2, 'User Admin', 'dayTypeOptions', true);
    $row = $form->addRow();
    $row->addLabel($setting['name'], __($setting['nameDisplay']))->description($setting['description']);
    $row->addTextArea($setting['name'])->setValue($setting['value']);

    $setting = getSettingByScope($connection2, 'User Admin', 'dayTypeText', true);
    $row = $form->addRow();
    $row->addLabel($setting['name'], __($setting['nameDisplay']))->description($setting['description']);
    $row->addTextArea($setting['name'])->setValue($setting['value']);

    $row = $form->addRow();
    $row->addFooter();
    $row->addSubmit();

    echo $form->getOutput();
}
