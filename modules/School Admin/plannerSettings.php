<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Forms\Form;

if (isActionAccessible($guid, $connection2, '/modules/School Admin/plannerSettings.php') == false) {
    //Acess denied
    echo "<div class='alert alert-danger'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    //Proceed!
    $page->breadcrumbs->add(__('Manage Planner Settings'));

    if (isset($_GET['return'])) {
        returnProcess($guid, $_GET['return'], null, null);
    }

    $form = Form::create('plannerSettings', $_SESSION[$guid]['absoluteURL'].'/modules/'.$_SESSION[$guid]['module'].'/plannerSettingsProcess.php');

    $form->addHiddenValue('address', $_SESSION[$guid]['address']);

    $form->addRow()->addHeading(__('Planner Templates'));

    $setting = getSettingByScope($connection2, 'Planner', 'lessonDetailsTemplate', true);
    $row = $form->addRow();
        $row->addLabel($setting['name'], __($setting['nameDisplay']))->description(__($setting['description']));
        $row->addTextArea($setting['name'])->setRows(10)->setValue($setting['value']);

    $setting = getSettingByScope($connection2, 'Planner', 'teachersNotesTemplate', true);
    $row = $form->addRow();
        $row->addLabel($setting['name'], __($setting['nameDisplay']))->description(__($setting['description']));
        $row->addTextArea($setting['name'])->setRows(10)->setValue($setting['value']);

    $setting = getSettingByScope($connection2, 'Planner', 'unitOutlineTemplate', true);
    $row = $form->addRow();
        $row->addLabel($setting['name'], __($setting['nameDisplay']))->description(__($setting['description']));
        $row->addTextArea($setting['name'])->setRows(10)->setValue($setting['value']);

    $setting = getSettingByScope($connection2, 'Planner', 'smartBlockTemplate', true);
    $row = $form->addRow();
        $row->addLabel($setting['name'], __($setting['nameDisplay']))->description(__($setting['description']));
        $row->addTextArea($setting['name'])->setRows(10)->setValue($setting['value']);

    $form->addRow()->addHeading(__('Access Settings'));

    $setting = getSettingByScope($connection2, 'Planner', 'makeUnitsPublic', true);
    $row = $form->addRow();
        $row->addLabel($setting['name'], __($setting['nameDisplay']))->description(__($setting['description']));
        $row->addYesNo($setting['name'])->required()->selected($setting['value']);

    $setting = getSettingByScope($connection2, 'Planner', 'shareUnitOutline', true);
    $row = $form->addRow();
        $row->addLabel($setting['name'], __($setting['nameDisplay']))->description(__($setting['description']));
        $row->addYesNo($setting['name'])->required()->selected($setting['value']);

    $setting = getSettingByScope($connection2, 'Planner', 'allowOutcomeEditing', true);
    $row = $form->addRow();
        $row->addLabel($setting['name'], __($setting['nameDisplay']))->description(__($setting['description']));
        $row->addYesNo($setting['name'])->required()->selected($setting['value']);

    $setting = getSettingByScope($connection2, 'Planner', 'sharingDefaultParents', true);
    $row = $form->addRow();
        $row->addLabel($setting['name'], __($setting['nameDisplay']))->description(__($setting['description']));
        $row->addYesNo($setting['name'])->required()->selected($setting['value']);

    $setting = getSettingByScope($connection2, 'Planner', 'sharingDefaultStudents', true);
    $row = $form->addRow();
        $row->addLabel($setting['name'], __($setting['nameDisplay']))->description(__($setting['description']));
        $row->addYesNo($setting['name'])->required()->selected($setting['value']);

    $form->addRow()->addHeading(__('Miscellaneous'));

    $setting = getSettingByScope($connection2, 'Planner', 'parentWeeklyEmailSummaryIncludeBehaviour', true);
    $row = $form->addRow();
        $row->addLabel($setting['name'], __($setting['nameDisplay']))->description(__($setting['description']));
        $row->addYesNo($setting['name'])->required()->selected($setting['value']);

    $setting = getSettingByScope($connection2, 'Planner', 'parentWeeklyEmailSummaryIncludeMarkbook', true);
    $row = $form->addRow();
        $row->addLabel($setting['name'], __($setting['nameDisplay']))->description(__($setting['description']));
        $row->addYesNo($setting['name'])->required()->selected($setting['value']);

    $row = $form->addRow();
        $row->addFooter();
        $row->addSubmit();

    echo $form->getOutput();
}
