<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Forms\Form;

if (isActionAccessible($guid, $connection2, '/modules/School Admin/dashboardSettings.php') == false) {
    //Acess denied
    echo "<div class='alert alert-danger'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    //Proceed!
    $page->breadcrumbs->add(__('Manage Dashboard Settings'));

    if (isset($_GET['return'])) {
        returnProcess($guid, $_GET['return'], null, null);
    }

    $form = Form::create('dashboardSettings', $_SESSION[$guid]['absoluteURL'].'/modules/'.$_SESSION[$guid]['module'].'/dashboardSettingsProcess.php' );

    $form->addHiddenValue('address', $_SESSION[$guid]['address']);

    $staffDashboardDefaultTabTypes = array(
        '' => '',
        'Planner' => __('Planner')
    );     
    $setting = getSettingByScope($connection2, 'School Admin', 'staffDashboardDefaultTab', true);
    $row = $form->addRow();
    	$row->addLabel($setting['name'], __($setting['nameDisplay']))->description(__($setting['description']));
        $row->addSelect($setting['name'])
            ->fromArray($staffDashboardDefaultTabTypes)
            ->fromQuery($pdo, "SELECT name, name AS value FROM pupilsightHook WHERE type='Staff Dashboard'")
            ->selected($setting['value']);

    $studentDashboardDefaultTabTypes = array(
        '' => '',
        'Planner' => __('Planner')
    );        
    $setting = getSettingByScope($connection2, 'School Admin', 'studentDashboardDefaultTab', true);
    $row = $form->addRow();
    	$row->addLabel($setting['name'], __($setting['nameDisplay']))->description(__($setting['description']));
        $row->addSelect($setting['name'])
            ->fromArray($studentDashboardDefaultTabTypes)
            ->fromQuery($pdo, "SELECT name, name AS value FROM pupilsightHook WHERE type='Student Dashboard'")
            ->selected($setting['value']);

    $parentDashboardDefaultTabTypes = array(
        '' => '',
        'Learning Overview' => __('Learning Overview'),
        'Timetable' => __('Timetable'),
        'Activities' => __('Activities')
    );         
    $setting = getSettingByScope($connection2, 'School Admin', 'parentDashboardDefaultTab', true);
    $row = $form->addRow();
    	$row->addLabel($setting['name'], __($setting['nameDisplay']))->description(__($setting['description']));
        $row->addSelect($setting['name'])
            ->fromArray($parentDashboardDefaultTabTypes)
            ->fromQuery($pdo, "SELECT name, name AS value FROM pupilsightHook WHERE type='Parental Dashboard'")
            ->selected($setting['value']);

    $row = $form->addRow();
		$row->addFooter();
		$row->addSubmit();

	echo $form->getOutput();
}
