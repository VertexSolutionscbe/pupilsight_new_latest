<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Tables\DataTable;
use Pupilsight\Services\Format;
use Pupilsight\Domain\Timetable\FacilityChangeGateway;

if (isActionAccessible($guid, $connection2, '/modules/Timetable/spaceChange_manage.php') == false) {
    //Acess denied
    echo "<div class='alert alert-danger'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    //Get action with highest precendence
    $highestAction = getHighestGroupedAction($guid, $_GET['q'], $connection2);
    if ($highestAction == false) {
        echo "<div class='alert alert-danger'>";
        echo __('The highest grouped action cannot be determined.');
        echo '</div>';
    } else {
        //Proceed!
        $page->breadcrumbs->add(__('Manage Facility Changes'));

        if (isset($_GET['return'])) {
            returnProcess($guid, $_GET['return'], null, null);
        }

        if ($highestAction == 'Manage Facility Changes_allClasses') {
            echo '<p>'.__('This page allows you to create and manage one-off location changes within any class in the timetable. Only current and future changes are shown: past changes are hidden.').'</p>';
        } else if ($highestAction == 'Manage Facility Changes_myDepartment') {
            echo '<p>'.__('This page allows you to create and manage one-off location changes within any of the classes departments for which have have the role Coordinator. Only current and future changes are shown: past changes are hidden.').'</p>';
        } else {
            echo '<p>'.__('This page allows you to create and manage one-off location changes within any of your classes in the timetable. Only current and future changes are shown: past changes are hidden.').'</p>';
        }

        $facilityChangeGateway = $container->get(FacilityChangeGateway::class);

        $criteria = $facilityChangeGateway->newQueryCriteria()
            ->sortBy(['date', 'courseName', 'className'])
            ->fromPOST();

        if ($highestAction == 'Manage Facility Changes_allClasses') {
            $facilityChanges = $facilityChangeGateway->queryFacilityChanges($criteria);
        } else if ($highestAction == 'Manage Facility Changes_myDepartment') {
            $facilityChanges = $facilityChangeGateway->queryFacilityChangesByDepartment($criteria, $_SESSION[$guid]['pupilsightPersonID']);
        } else {
            $facilityChanges = $facilityChangeGateway->queryFacilityChanges($criteria, $_SESSION[$guid]['pupilsightPersonID']);
        }

        // DATA TABLE
        $table = DataTable::createPaginated('facilityChanges', $criteria);

        $table->addHeaderAction('add', __('Add'))
            ->setURL('/modules/Timetable/spaceChange_manage_add.php')
            ->displayLabel();

        $table->addColumn('date', __('Date'))
            ->format(Format::using('date', 'date'));
        $table->addColumn('courseClass', __('Class'))
            ->sortable(['courseName', 'className'])
            ->format(Format::using('courseClassName', ['courseName', 'className']));
        $table->addColumn('spaceOld', __('Original Facility'));
        $table->addColumn('spaceNew', __('New Facility'));
        $table->addColumn('person', __('Person'))
            ->sortable(['preferredName', 'surname'])
            ->format(Format::using('name', ['', 'preferredName', 'surname', 'Staff', false, true]));
        
        $table->addActionColumn()
            ->addParam('pupilsightTTSpaceChangeID')
            ->addParam('pupilsightCourseClassID')
            ->format(function ($row, $actions) {
                $actions->addAction('delete', __('Delete'))
                        ->setURL('/modules/Timetable/spaceChange_manage_delete.php');
            });

        echo $table->render($facilityChanges);
    }
}
