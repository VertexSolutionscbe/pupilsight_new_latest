<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Forms\Form;

if (isActionAccessible($guid, $connection2, '/modules/School Admin/markbookSettings.php') == false) {
    //Acess denied
    echo "<div class='alert alert-danger'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    //Proceed!
    $page->breadcrumbs->add(__('Markbook Settings'));

    if (isset($_GET['return'])) {
        returnProcess($guid, $_GET['return'], null, null);
    }

    $form = Form::create('markbookSettings', $_SESSION[$guid]['absoluteURL'].'/modules/'.$_SESSION[$guid]['module'].'/markbookSettingsProcess.php' );

    $form->addHiddenValue('address', $_SESSION[$guid]['address']);

    $row = $form->addRow()->addHeading(__('Features'));

    $setting = getSettingByScope($connection2, 'Markbook', 'enableEffort', true);
	$row = $form->addRow();
    	$row->addLabel($setting['name'], __($setting['nameDisplay']))->description(__($setting['description']));
		$row->addYesNo($setting['name'])->selected($setting['value'])->required();

    $setting = getSettingByScope($connection2, 'Markbook', 'enableRubrics', true);
    $row = $form->addRow();
    	$row->addLabel($setting['name'], __($setting['nameDisplay']))->description(__($setting['description']));
    	$row->addYesNo($setting['name'])->selected($setting['value'])->required();

    $setting = getSettingByScope($connection2, 'Markbook', 'enableColumnWeighting', true);
	$row = $form->addRow();
    	$row->addLabel($setting['name'], __($setting['nameDisplay']))->description(__($setting['description']));
		$row->addYesNo($setting['name'])->selected($setting['value'])->required();

    $form->toggleVisibilityByClass('columnWeighting')->onSelect('enableColumnWeighting')->when('Y');

    $defaultAssessmentScale = getSettingByScope($connection2, 'System', 'defaultAssessmentScale');
    if (intval($defaultAssessmentScale) != 4) {
        $row = $form->addRow()->addClass('columnWeighting');
            $row->addAlert(__('Calculation of cumulative marks and weightings is currently only available when using Percentage as the Default Assessment Scale. This value can be changed in System Settings.'));
    }
    
    $setting = getSettingByScope($connection2, 'Markbook', 'enableDisplayCumulativeMarks', true);
    $row = $form->addRow()->addClass('columnWeighting');
        $row->addLabel($setting['name'], __($setting['nameDisplay']))->description(__($setting['description']));
        $row->addYesNo($setting['name'])->selected($setting['value']);

    $setting = getSettingByScope($connection2, 'Markbook', 'enableRawAttainment', true);
	$row = $form->addRow();
    	$row->addLabel($setting['name'], __($setting['nameDisplay']))->description(__($setting['description']));
        $row->addYesNo($setting['name'])->selected($setting['value'])->required();
    
    $setting = getSettingByScope($connection2, 'Markbook', 'enableModifiedAssessment', true);
    $row = $form->addRow();
    	$row->addLabel($setting['name'], __($setting['nameDisplay']))->description(__($setting['description']));
    	$row->addYesNo($setting['name'])->selected($setting['value'])->required();

    $row = $form->addRow()->addHeading(__('Interface'));

    $setting = getSettingByScope($connection2, 'Markbook', 'markbookType', true);
    $row = $form->addRow();
        $row->addLabel($setting['name'], __($setting['nameDisplay']))->description(__($setting['description']));
        $row->addTextArea($setting['name'])->setValue($setting['value'])->required();

    $setting = getSettingByScope($connection2, 'Markbook', 'enableGroupByTerm', true);
	$row = $form->addRow();
    	$row->addLabel($setting['name'], __($setting['nameDisplay']))->description(__($setting['description']));
		$row->addYesNo($setting['name'])->selected($setting['value'])->required();

    $setting = getSettingByScope($connection2, 'Markbook', 'attainmentAlternativeName', true);
	$row = $form->addRow();
    	$row->addLabel($setting['name'], __($setting['nameDisplay']))->description(__($setting['description']));
		$row->addTextField($setting['name'])->setValue($setting['value']);

    $setting = getSettingByScope($connection2, 'Markbook', 'attainmentAlternativeNameAbrev', true);
    $row = $form->addRow();
        $row->addLabel($setting['name'], __($setting['nameDisplay']))->description(__($setting['description']));
        $row->addTextField($setting['name'])->setValue($setting['value']);

    $setting = getSettingByScope($connection2, 'Markbook', 'effortAlternativeName', true);
    $row = $form->addRow();
        $row->addLabel($setting['name'], __($setting['nameDisplay']))->description(__($setting['description']));
        $row->addTextField($setting['name'])->setValue($setting['value']);

    $setting = getSettingByScope($connection2, 'Markbook', 'effortAlternativeNameAbrev', true);
    $row = $form->addRow();
        $row->addLabel($setting['name'], __($setting['nameDisplay']))->description(__($setting['description']));
        $row->addTextField($setting['name'])->setValue($setting['value']);

    $row = $form->addRow()->addHeading(__('Warnings'));

    $setting = getSettingByScope($connection2, 'Markbook', 'showStudentAttainmentWarning', true);
    $row = $form->addRow();
    	$row->addLabel($setting['name'], __($setting['nameDisplay']))->description(__($setting['description']));
    	$row->addYesNo($setting['name'])->selected($setting['value'])->required();

    $setting = getSettingByScope($connection2, 'Markbook', 'showStudentEffortWarning', true);
    $row = $form->addRow();
    	$row->addLabel($setting['name'], __($setting['nameDisplay']))->description(__($setting['description']));
    	$row->addYesNo($setting['name'])->selected($setting['value'])->required();

    $setting = getSettingByScope($connection2, 'Markbook', 'showParentAttainmentWarning', true);
    $row = $form->addRow();
    	$row->addLabel($setting['name'], __($setting['nameDisplay']))->description(__($setting['description']));
    	$row->addYesNo($setting['name'])->selected($setting['value'])->required();

    $setting = getSettingByScope($connection2, 'Markbook', 'showParentEffortWarning', true);
    $row = $form->addRow();
    	$row->addLabel($setting['name'], __($setting['nameDisplay']))->description(__($setting['description']));
    	$row->addYesNo($setting['name'])->selected($setting['value'])->required();

    $setting = getSettingByScope($connection2, 'Markbook', 'personalisedWarnings', true);
    $row = $form->addRow();
    	$row->addLabel($setting['name'], __($setting['nameDisplay']))->description(__($setting['description']));
    	$row->addYesNo($setting['name'])->selected($setting['value'])->required();

	$row = $form->addRow();
		$row->addFooter();
		$row->addSubmit()->addClass('submit_align');

	echo $form->getOutput();
}
