<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Tables\DataTable;
use Pupilsight\Services\Format;
use Pupilsight\Domain\School\SchoolYearGateway;
use Pupilsight\Domain\DataUpdater\PersonUpdateGateway;

if (isActionAccessible($guid, $connection2, '/modules/Data Updater/data_personal_manage.php') == false) {
    //Acess denied
    echo "<div class='alert alert-danger'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    //Proceed!
    $page->breadcrumbs->add(__('Personal Data Updates'));

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

    $gateway = $container->get(PersonUpdateGateway::class);

    // QUERY
    $criteria = $gateway->newQueryCriteria()
        ->sortBy('status')
        ->sortBy('timestamp', 'DESC')
        ->fromPOST();

    $dataUpdates = $gateway->queryDataUpdates($criteria, $pupilsightSchoolYearID);

    // DATA TABLE
    $table = DataTable::createPaginated('personUpdateManage', $criteria);

    $table->modifyRows(function ($update, $row) {
        if ($update['status'] != 'Pending') $row->addClass('current');
        return $row;
    });

    // COLUMNS
    $table->addColumn('target', __('Target User'))
        ->sortable(['target.surname', 'target.preferredName'])
        ->format(Format::using('name', ['', 'preferredName', 'surname', 'Student']));
    $table->addColumn('updater', __('Requesting User'))
        ->sortable(['updater.surname', 'updater.preferredName'])
        ->format(Format::using('name', ['updaterTitle', 'updaterPreferredName', 'updaterSurname', 'Parent']));
    $table->addColumn('timestamp', __('Date & Time'))->format(Format::using('dateTime', 'timestamp'));
    $table->addColumn('status', __('Status'))->width('12%');

    // ACTIONS
    $table->addActionColumn()
        ->addParam('pupilsightSchoolYearID', $pupilsightSchoolYearID)
        ->addParam('pupilsightPersonUpdateID')
        ->format(function ($update, $actions) {
            if ($update['status'] == 'Pending') {
                $actions->addAction('edit', __('Edit'))
                        ->setURL('/modules/Data Updater/data_personal_manage_edit.php');

                $actions->addAction('delete', __('Delete'))
                        ->setURL('/modules/Data Updater/data_personal_manage_delete.php');
            }
        });

    echo $table->render($dataUpdates);
}
