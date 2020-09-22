<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Forms\Form;
use Pupilsight\Forms\DatabaseFormFactory;
use Pupilsight\Tables\DataTable;
use Pupilsight\Domain\DataSet;
use Pupilsight\Domain\Staff\StaffAbsenceGateway;
use Pupilsight\Domain\Staff\StaffAbsenceDateGateway;
use Pupilsight\Domain\Staff\StaffAbsenceTypeGateway;
use Pupilsight\Domain\School\SchoolYearGateway;
use Pupilsight\Module\Staff\Tables\AbsenceFormats;
use Pupilsight\Module\Staff\Tables\AbsenceCalendar;

if (isActionAccessible($guid, $connection2, '/modules/Staff/absences_view_byPerson.php') == false) {
    // Access denied
    $page->addError(__('You do not have access to this action.'));
} else {
    $page->breadcrumbs->add(__('View Absences'));

    $highestAction = getHighestGroupedAction($guid, $_GET['q'], $connection2);
    if (empty($highestAction)) {
        $page->addError(__('You do not have access to this action.'));
        return;
    }

    $pupilsightSchoolYearID = $_SESSION[$guid]['pupilsightSchoolYearID'];

    $schoolYearGateway = $container->get(SchoolYearGateway::class);
    $staffAbsenceGateway = $container->get(StaffAbsenceGateway::class);
    $staffAbsenceDateGateway = $container->get(StaffAbsenceDateGateway::class);
    $staffAbsenceTypeGateway = $container->get(StaffAbsenceTypeGateway::class);

    if ($highestAction == 'View Absences_any') {
        $pupilsightPersonID = $_GET['pupilsightPersonID'] ?? $_SESSION[$guid]['pupilsightPersonID'];

        $form = Form::create('filter', $_SESSION[$guid]['absoluteURL'].'/index.php', 'get');
        $form->setFactory(DatabaseFormFactory::create($pdo));
        $form->setTitle(__('Filter'));
        $form->setClass('noIntBorder fullWidth');

        $form->addHiddenValue('address', $_SESSION[$guid]['address']);
        $form->addHiddenValue('q', '/modules/Staff/absences_view_byPerson.php');
        
        $row = $form->addRow();
            $row->addLabel('pupilsightPersonID', __('Person'));
            $row->addSelectStaff('pupilsightPersonID')->selected($pupilsightPersonID);

        $row = $form->addRow();
            $row->addFooter();
            $row->addSearchSubmit($pupilsight->session);

        echo $form->getOutput();
    } else {
        $pupilsightPersonID = $_SESSION[$guid]['pupilsightPersonID'];
    }

    
    $absences = $staffAbsenceDateGateway->selectApprovedAbsenceDatesByPerson($pupilsightPersonID)->fetchGrouped();
    $schoolYear = $schoolYearGateway->getSchoolYearByID($pupilsightSchoolYearID);

    // CALENDAR VIEW
    $table = AbsenceCalendar::create($absences, $schoolYear['firstDay'], $schoolYear['lastDay']);
    echo $table->getOutput().'<br/>';

    // COUNT TYPES
    $absenceTypes = $staffAbsenceTypeGateway->selectAllTypes()->fetchAll();
    $types = array_fill_keys(array_column($absenceTypes, 'name'), 0);

    foreach ($absences as $days) {
        foreach ($days as $absence) {
            $types[$absence['type']] += $absence['value'];
        }
    }

    $table = DataTable::create('staffAbsenceTypes');

    foreach ($types as $name => $count) {
        $table->addColumn($name, $name)->context('primary')->width((100 / count($types)).'%');
    }

    echo $table->render(new DataSet([$types]));

    // QUERY
    $criteria = $staffAbsenceGateway->newQueryCriteria()
        ->sortBy('date', 'DESC')
        ->fromPOST();

    $absences = $staffAbsenceGateway->queryAbsencesByPerson($criteria, $pupilsightPersonID);

    // Join a set of coverage data per absence
    $absenceIDs = $absences->getColumn('pupilsightStaffAbsenceID');
    $coverageData = $staffAbsenceDateGateway->selectDatesByAbsence($absenceIDs)->fetchGrouped();
    $absences->joinColumn('pupilsightStaffAbsenceID', 'coverageList', $coverageData);

    // DATA TABLE
    $table = DataTable::createPaginated('staffAbsences', $criteria);
    $table->setTitle(__('View'));

    $table->modifyRows(function ($absence, $row) {
        if ($absence['status'] == 'Pending Approval') $row->addClass('warning');
        if ($absence['status'] == 'Declined') $row->addClass('dull');
        return $row;
    });

    $table->addHeaderAction('add', __('New Absence'))
        ->setURL('/modules/Staff/absences_add.php')
        ->addParam('pupilsightPersonID', $pupilsightPersonID)
        ->displayLabel();

    // COLUMNS
    $table->addColumn('date', __('Date'))
        ->format([AbsenceFormats::class, 'dateDetails']);
    
    $table->addColumn('type', __('Type'))
        ->description(__('Reason'))
        ->format([AbsenceFormats::class, 'typeAndReason']);
    
    $table->addColumn('coverage', __('Coverage'))
        ->format([AbsenceFormats::class, 'coverageList']);

    $table->addColumn('timestampCreator', __('Created'))
        ->width('20%')
        ->format([AbsenceFormats::class, 'createdOn']);

    // ACTIONS
    $canManage = isActionAccessible($guid, $connection2, '/modules/Staff/absences_manage.php');
    $canRequest = isActionAccessible($guid, $connection2, '/modules/Staff/coverage_request.php');

    $table->addActionColumn()
        ->addParam('pupilsightStaffAbsenceID')
        ->addParam('search', $criteria->getSearchText(true))
        ->format(function ($absence, $actions) use ($canManage, $canRequest) {
            if ($canRequest && $absence['status'] == 'Approved' 
                && empty($absence['coverage']) && $absence['dateEnd'] >= date('Y-m-d')) {
                $actions->addAction('coverage', __('Request Coverage'))
                    ->setIcon('attendance')
                    ->setURL('/modules/Staff/coverage_request.php');
            }

            $actions->addAction('view', __('View Details'))
                ->isModal(800, 550)
                ->setURL('/modules/Staff/absences_view_details.php');

            if ($canManage) {
                $actions->addAction('edit', __('Edit'))
                    ->setURL('/modules/Staff/absences_manage_edit.php');

                $actions->addAction('delete', __('Delete'))
                    ->setURL('/modules/Staff/absences_manage_delete.php');
            }
        });

    echo $table->render($absences);
}
