<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Forms\Form;
use Pupilsight\Tables\DataTable;
use Pupilsight\Domain\School\FacilityGateway;

//Module includes
require_once __DIR__ . '/moduleFunctions.php';

if (isActionAccessible($guid, $connection2, '/modules/Timetable/tt_space.php') == false) {
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
        $page->breadcrumbs->add(__('View Timetable by Facility'));

        $pupilsightPersonID = isset($_GET['pupilsightPersonID'])? $_GET['pupilsightPersonID'] : null;
        $search = isset($_GET['search'])? $_GET['search'] : '';

        $facilityGateway = $container->get(FacilityGateway::class);

        // CRITERIA
        $criteria = $facilityGateway->newQueryCriteria()
            ->searchBy($facilityGateway->getSearchableColumns(), $search)
            ->sortBy('name')
            ->fromPOST();

        echo '<h2>';
        echo __('Search');
        echo '</h2>';

        $form = Form::create('ttSpace', $_SESSION[$guid]['absoluteURL'].'/index.php', 'get');
        $form->setClass('noIntBorder fullWidth');

        $form->addHiddenValue('q', '/modules/'.$_SESSION[$guid]['module'].'/tt_space.php');

        $row = $form->addRow();
            $row->addLabel('search', __('Search For'));
            $row->addTextField('search')->setValue($criteria->getSearchText());

        $row = $form->addRow();
            $row->addSearchSubmit($pupilsight->session, __('Clear Search'));

        echo $form->getOutput();

        echo '<h2>';
        echo __('Choose A Facility');
        echo '</h2>';

        $facilities = $facilityGateway->queryFacilities($criteria);

        // DATA TABLE
        $table = DataTable::createPaginated('timetableByFacility', $criteria);

        $table->addColumn('name', __('Name'));
        $table->addColumn('type', __('Type'));

        $table->addActionColumn()
            ->addParam('pupilsightSpaceID')
            ->addParam('search', $criteria->getSearchText(true))
            ->format(function ($row, $actions) {
                $actions->addAction('view', __('View'))
                        ->setURL('/modules/Timetable/tt_space_view.php');
            });

        echo $table->render($facilities);
    }
}
