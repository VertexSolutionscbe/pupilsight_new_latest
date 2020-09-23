<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Tables\DataTable;
use Pupilsight\Services\Format;
use Pupilsight\Domain\Timetable\FacilityBookingGateway;

if (isActionAccessible($guid, $connection2, '/modules/Timetable/spaceBooking_manage.php') == false) {
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
        $page->breadcrumbs->add(__('Manage Facility Bookings'));

        if (isset($_GET['return'])) {
            returnProcess($guid, $_GET['return'], null, null);
        }

        if ($highestAction == 'Manage Facility Bookings_allBookings') {
            echo '<p>'.__('This page allows you to create facility and library bookings, whilst managing bookings created by all users. Only current and future bookings are shown: past bookings are hidden.').'</p>';
        } else {
            echo '<p>'.__('This page allows you to create and manage facility and library bookings. Only current and future changes are shown: past bookings are hidden.').'</p>';
        }

        $facilityBookingGateway = $container->get(FacilityBookingGateway::class);

        $criteria = $facilityBookingGateway->newQueryCriteria()
            ->sortBy(['date', 'name'])
            ->fromPOST();

        if ($highestAction == 'Manage Facility Bookings_allBookings') {
            $facilityBookings = $facilityBookingGateway->queryFacilityBookings($criteria);
        } else {
            $facilityBookings = $facilityBookingGateway->queryFacilityBookings($criteria, $_SESSION[$guid]['pupilsightPersonID']);
        }

        // DATA TABLE
        $table = DataTable::createPaginated('facilityBookings', $criteria);

        $table->addHeaderAction('add', __('Add'))
            ->setURL('/modules/Timetable/spaceBooking_manage_add.php')
            ->displayLabel();

        $table->addColumn('date', __('Date'))
            ->format(Format::using('date', 'date'));
        $table->addColumn('name', __('Facility'))
            ->format(function($row) use ($guid) {
                if ($row['foreignKey']=='pupilsightSpaceID') {
                    $output = Format::link($_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/Timetable/tt_space_view.php&pupilsightSpaceID='.str_pad($row['foreignKeyID'], 10, '0', STR_PAD_LEFT).'&ttDate='.dateConvertBack($guid, $row['date']), $row['name']);
                } else {
                    $output = $row['name'];
                }

                return $output.'<br/><small><i>'
                     .($row['foreignKey'] == 'pupilsightLibraryItemID'? __('Library') :'').'</i></small>';
            });
        $table->addColumn('time', __('Time'))
            ->sortable(['timeStart', 'timeEnd'])
            ->format(Format::using('timeRange', ['timeStart', 'timeEnd']));
        $table->addColumn('person', __('Person'))
            ->sortable(['preferredName', 'surname'])
            ->format(Format::using('name', ['', 'preferredName', 'surname', 'Staff', false, true]));

        $table->addActionColumn()
            ->addParam('pupilsightTTSpaceBookingID')
            ->format(function ($row, $actions) {
                $actions->addAction('delete', __('Delete'))
                        ->setURL('/modules/Timetable/spaceBooking_manage_delete.php');
            });

        echo $table->render($facilityBookings);
    }
}
