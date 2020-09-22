<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Tables\DataTable;
use Pupilsight\Services\Format;
use Pupilsight\Domain\School\SchoolYearGateway;
use Pupilsight\Domain\RollGroups\RollGroupGateway;

if (isActionAccessible($guid, $connection2, '/modules/School Admin/rollGroup_manage.php') == false) {
    //Acess denied
    echo "<div class='alert alert-danger'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    //Proceed!
    $pupilsightSchoolYearID = $_GET['pupilsightSchoolYearID'] ?? '';
    $pupilsightRollGroupID = $_GET['pupilsightRollGroupID'] ?? '';

    $page->breadcrumbs->add(__('Manage Roll Groups'));

    if (isset($_GET['return'])) {
        returnProcess($guid, $_GET['return'], null, null);
    }

    $pupilsightSchoolYearID = isset($_REQUEST['pupilsightSchoolYearID'])? $_REQUEST['pupilsightSchoolYearID'] : $_SESSION[$guid]['pupilsightSchoolYearID'];

    // School Year Picker
    if (!empty($pupilsightSchoolYearID)) {
        $schoolYearGateway = $container->get(SchoolYearGateway::class);
        $targetSchoolYear = $schoolYearGateway->getSchoolYearByID($pupilsightSchoolYearID);

        echo '<h2>';
        echo $targetSchoolYear['name'];
        echo '</h2>';

        echo "<div class='linkTop'>";
            if ($prevSchoolYear = $schoolYearGateway->getPreviousSchoolYearByID($pupilsightSchoolYearID)) {
                echo "<a href='".$_SESSION[$guid]['absoluteURL'].'/index.php?q='.$_GET['q'].'&pupilsightSchoolYearID='.$prevSchoolYear['pupilsightSchoolYearID']."'>".__('Previous Year').'</a> ';
            } else {
                echo __('Previous Year').' ';
            }
			echo ' | ';
			if ($nextSchoolYear = $schoolYearGateway->getNextSchoolYearByID($pupilsightSchoolYearID)) {
				echo "<a href='".$_SESSION[$guid]['absoluteURL'].'/index.php?q='.$_GET['q'].'&pupilsightSchoolYearID='.$nextSchoolYear['pupilsightSchoolYearID']."'>".__('Next Year').'</a> ';
			} else {
				echo __('Next Year').' ';
			}
        echo '</div>';
    }
        
    $rollGroupGateway = $container->get(RollGroupGateway::class);

    // QUERY
    $criteria = $rollGroupGateway->newQueryCriteria()
        ->sortBy(['sequenceNumber', 'pupilsightRollGroup.name'])
        ->fromPOST();

    $rollGroups = $rollGroupGateway->queryRollGroups($criteria, $pupilsightSchoolYearID);

    $formatTutorsList = function($row) use ($rollGroupGateway) {
        $tutors = $rollGroupGateway->selectTutorsByRollGroup($row['pupilsightRollGroupID'])->fetchAll();
        if (count($tutors) > 1) $tutors[0]['surname'] .= ' ('.__('Main Tutor').')';

        return Format::nameList($tutors, 'Staff', false, true);
    };

    // DATA TABLE
    $table = DataTable::createPaginated('rollGroupManage', $criteria);

    if (!empty($nextSchoolYear)) {
        $table->addHeaderAction('copy', __('Copy All To Next Year'))
            ->setURL('/modules/School Admin/rollGroup_manage_copyProcess.php')
            ->addParam('pupilsightSchoolYearID', $pupilsightSchoolYearID)
            ->addParam('pupilsightSchoolYearIDNext', $nextSchoolYear['pupilsightSchoolYearID'])
            ->setIcon('copy')
            ->onClick('return confirm("'.__('Are you sure you want to continue?').' '.__('This operation cannot be undone.').'");')
            ->displayLabel()
            ->directLink()
            ->append('&nbsp;|&nbsp;');
    }

    $table->addHeaderAction('add', __('Add'))
        ->setURL('/modules/School Admin/rollGroup_manage_add.php')
        ->addParam('pupilsightSchoolYearID', $pupilsightSchoolYearID)
        ->displayLabel();

    $table->addColumn('name', __('Name'))
          ->description(__('Short Name'))
          ->format(function ($rollGroup) {
            return '<strong>' . $rollGroup['name'] . '</strong><br/><small><i>' . $rollGroup['nameShort'] . '</i></small>';
          });
    $table->addColumn('tutors', __('Form Tutors'))->sortable(false)->format($formatTutorsList);
    $table->addColumn('space', __('Location'));
    $table->addColumn('website', __('Website'))
            ->format(Format::using('link', ['website']));
        
    // ACTIONS
    $table->addActionColumn()
        ->addParam('pupilsightRollGroupID')
        ->addParam('pupilsightSchoolYearID', $pupilsightSchoolYearID)
        ->format(function ($rollGroup, $actions) {
            $actions->addAction('edit', __('Edit'))
                    ->setURL('/modules/School Admin/rollGroup_manage_edit.php');

            $actions->addAction('delete', __('Delete'))
                    ->setURL('/modules/School Admin/rollGroup_manage_delete.php');
        });

    echo $table->render($rollGroups);
}
