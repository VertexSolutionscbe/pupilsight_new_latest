<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Tables\DataTable;
use Pupilsight\Services\Format;
use Pupilsight\Domain\Staff\StaffAbsenceGateway;
use Pupilsight\Module\Staff\Tables\AbsenceFormats;

if (isActionAccessible($guid, $connection2, '/modules/Staff/absences_approval.php') == false) {
    // Access denied
    $page->addError(__('You do not have access to this action.'));
} else {
    // Proceed!
    $page->breadcrumbs->add(__('Approve Staff Absences'));

    if (isset($_GET['return'])) {
        returnProcess($guid, $_GET['return'], null, null);
    }

    $pupilsightSchoolYearID = $_SESSION[$guid]['pupilsightSchoolYearID'];
    $pupilsightStaffAbsenceTypeID = $_GET['pupilsightStaffAbsenceTypeID'] ?? '';
    $search = $_GET['search'] ?? '';
    $dateStart = $_GET['dateStart'] ?? '';
    $dateEnd = $_GET['dateEnd'] ?? '';

    $staffAbsenceGateway = $container->get(StaffAbsenceGateway::class);

    // QUERY
    $criteria = $staffAbsenceGateway->newQueryCriteria()
        ->searchBy($staffAbsenceGateway->getSearchableColumns(), $search)
        ->sortBy('status', 'ASC')
        ->fromPOST();

    $absences = $staffAbsenceGateway->queryAbsencesByApprover($criteria, $_SESSION[$guid]['pupilsightPersonID']);

    // DATA TABLE
    $table = DataTable::createPaginated('staffAbsences', $criteria);
    $table->setTitle(__('View'));

    $table->modifyRows(function ($absence, $row) {
        if ($absence['status'] == 'Approved') $row->addClass('current');
        if ($absence['status'] == 'Declined') $row->addClass('error');
        return $row;
    });
    
    $table->addMetaData('filterOptions', [
        'date:upcoming'           => __('Upcoming'),
        'date:today'              => __('Today'),
        'date:past'               => __('Past'),
        'status:pending approval' => __('Status').': '.__('Pending Approval'),
        'status:approved'         => __('Status').': '.__('Approved'),
        'status:declined'         => __('Status').': '.__('Declined'),
    ]);

    // COLUMNS
    $table->addColumn('fullName', __('Name'))
        ->sortable(['surname', 'preferredName'])
        ->format(function ($absence) use ($guid) {
            $text = Format::name($absence['title'], $absence['preferredName'], $absence['surname'], 'Staff', false, true);
            $url = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/Staff/absences_view_byPerson.php&pupilsightPersonID='.$absence['pupilsightPersonID'];

            return Format::link($url, $text);
        });

    $table->addColumn('date', __('Date'))
        ->width('18%')
        ->format([AbsenceFormats::class, 'dateDetails']);

    $table->addColumn('type', __('Type'))
        ->description(__('Reason'))
        ->format([AbsenceFormats::class, 'typeAndReason']);

    $table->addColumn('timestampCreator', __('Created'))
        ->format([AbsenceFormats::class, 'createdOn']);

    // ACTIONS
    $table->addActionColumn()
        ->addParam('pupilsightStaffAbsenceID')
        ->format(function ($absence, $actions) {
            $actions->addAction('view', __('View Details'))
                ->isModal(800, 550)
                ->setURL('/modules/Staff/absences_view_details.php');

            if ($absence['status'] == 'Pending Approval') {
                $actions->addAction('approve', __('Approve'))
                    ->setIcon('iconTick')
                    ->addParam('status', 'Approved')
                    ->setURL('/modules/Staff/absences_approval_action.php');

                $actions->addAction('decline', __('Decline'))
                    ->setIcon('iconCross')
                    ->addParam('status', 'Declined')
                    ->setURL('/modules/Staff/absences_approval_action.php');
            }
        });

    echo $table->render($absences);
}
