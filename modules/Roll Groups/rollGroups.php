<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Tables\DataTable;
use Pupilsight\Services\Format;
use Pupilsight\Domain\RollGroups\RollGroupGateway;

if (isActionAccessible($guid, $connection2, '/modules/Roll Groups/rollGroups.php') == false) {
    //Acess denied
    echo "<div class='alert alert-danger'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    //Proceed!
    $page->breadcrumbs->add(__('View Roll Groups'));

    echo '<p>';
    echo __('This page shows all roll groups in the current school year.');
    echo '</p>';

    $gateway = $container->get(RollGroupGateway::class);
    $rollGroups = $gateway->selectRollGroupsBySchoolYear($_SESSION[$guid]['pupilsightSchoolYearID']);

    $formatTutorsList = function($row) use ($gateway) {
        $tutors = $gateway->selectTutorsByRollGroup($row['pupilsightRollGroupID'])->fetchAll();
        if (count($tutors) > 1) $tutors[0]['surname'] .= ' ('.__('Main Tutor').')';

        return Format::nameList($tutors, 'Staff', false, true);
    };

    $table = DataTable::create('rollGroups');

    $table->addColumn('name', __('Name'));
    $table->addColumn('tutors', __('Form Tutors'))->format($formatTutorsList);
    $table->addColumn('space', __('Room'));
    if (getRoleCategory($_SESSION[$guid]['pupilsightRoleIDCurrent'], $connection2) == "Staff") {
        $table->addColumn('students', __('Students'));
    }
    $table->addColumn('website', __('Website'))->format(Format::using('link', 'website'));

    $actions = $table->addActionColumn()->addParam('pupilsightRollGroupID');
    $actions->addAction('view', __('View'))
            ->setURL('/modules/Roll Groups/rollGroups_details.php');

    echo $table->render($rollGroups->toDataSet());
}
