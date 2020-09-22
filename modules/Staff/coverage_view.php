<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Services\Format;
use Pupilsight\Tables\DataTable;
use Pupilsight\Domain\School\SchoolYearGateway;
use Pupilsight\Domain\Staff\SubstituteGateway;
use Pupilsight\Domain\Staff\StaffCoverageGateway;
use Pupilsight\Module\Staff\Tables\AbsenceFormats;

if (isActionAccessible($guid, $connection2, '/modules/Staff/coverage_view.php') == false) {
    // Access denied
    $page->addError(__('You do not have access to this action.'));
} else {
    $page->breadcrumbs->add(__('Open Requests'));

    if (isset($_GET['return'])) {
        returnProcess($guid, $_GET['return'], null, null);
    }

    $urgencyThreshold = getSettingByScope($connection2, 'Staff', 'urgencyThreshold');

    $pupilsightPersonID = $_SESSION[$guid]['pupilsightPersonID'];
    $pupilsightSchoolYearID = $_SESSION[$guid]['pupilsightSchoolYearID'];
    
    $schoolYearGateway = $container->get(SchoolYearGateway::class);
    $staffCoverageGateway = $container->get(StaffCoverageGateway::class);

    // QUERY
    $criteria = $staffCoverageGateway->newQueryCriteria()
        ->sortBy('date', 'ASC')
        ->filterBy('requested', 'Y')
        ->filterBy('date:upcoming')
        ->pageSize(0)
        ->fromPOST('myCoverage');

    $myCoverage = $staffCoverageGateway->queryCoverageByPersonCovering($criteria, $pupilsightPersonID, true);

    $criteria = $staffCoverageGateway->newQueryCriteria()
        ->sortBy('date', 'ASC')
        ->filterBy('requested', 'Y')
        ->filterBy('date:upcoming')
        ->pageSize(0)
        ->fromPOST('allCoverage');

    $allCoverage = $staffCoverageGateway->queryCoverageWithNoPersonAssigned($criteria, true);

    if ($myCoverage->getResultCount() == 0 && $allCoverage->getResultCount() == 0) {
        echo Format::alert(__('All coverage requests have been filled!'), 'success');
        return;
    }

    // DATA TABLE
    $table = DataTable::createPaginated('staffCoverageAvailable', $criteria);

    $table->addMetaData('hidePagination', true);
    
    $table->modifyRows(function ($coverage, $row) {
        if ($coverage['status'] == 'Accepted') $row->addClass('current');
        if ($coverage['status'] == 'Declined') $row->addClass('error');
        if ($coverage['status'] == 'Cancelled') $row->addClass('dull');
        return $row;
    });

    $table->addColumn('status', __('Status'))
        ->width('15%')
        ->format(function ($coverage) use ($urgencyThreshold) {
            return AbsenceFormats::coverageStatus($coverage, $urgencyThreshold);
        });

    $table->addColumn('date', __('Date'))
        ->context('primary')
        ->format([AbsenceFormats::class, 'dateDetails']);

    $table->addColumn('requested', __('Person'))
        ->context('primary')
        ->sortable(['surname', 'preferredName'])
        ->format([AbsenceFormats::class, 'personDetails']);

    // Only display the Accept / Decline options for people who are substitutes
    $substitute = $container->get(SubstituteGateway::class)->getSubstituteByPerson($pupilsightPersonID);
    if (!empty($substitute)) {
        $table->addActionColumn()
            ->addParam('pupilsightStaffCoverageID')
            ->format(function ($coverage, $actions) use ($pupilsightPersonID) {
                $actions->addAction('accept', __('Accept'))
                    ->setIcon('iconTick')
                    ->setURL('/modules/Staff/coverage_view_accept.php');

                if ($pupilsightPersonID == $coverage['pupilsightPersonIDCoverage']) {
                    $actions->addAction('decline', __('Decline'))
                        ->setIcon('iconCross')
                        ->setURL('/modules/Staff/coverage_view_decline.php');
                }
            });
    }

    if ($myCoverage->getResultCount() > 0) {
        $myRequestsTable = clone $table;
        $myRequestsTable->setID('myCoverage');
        $myRequestsTable->setTitle(__('Personal Coverage Requests'));
        $myRequestsTable->setDescription(Format::alert(__('These requests have been submitted to you personally. If you are unable to accept the request, please decline it so that the requesting staff member is notified and can find a different substitute.'), 'message'));

        echo $myRequestsTable->render($myCoverage);
    }

    if ($allCoverage->getResultCount() > 0) {
        $allRequestsTable = clone $table;
        $allRequestsTable->setID('allCoverage');
        $allRequestsTable->setTitle(__('All Coverage Requests'));
        $allRequestsTable->setDescription(__('These requests are open for any available substitute to accept.'));

        echo $allRequestsTable->render($allCoverage);
    }
}
