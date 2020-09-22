<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Tables\DataTable;
use Pupilsight\Services\Format;
use Pupilsight\Domain\User\UserGateway;
use Pupilsight\Domain\School\SchoolYearGateway;
use Pupilsight\Domain\Staff\StaffCoverageGateway;
use Pupilsight\Domain\Staff\SubstituteGateway;
use Pupilsight\Module\Staff\View\CoverageTodayView;
use Pupilsight\Module\Staff\View\StaffCard;
use Pupilsight\Module\Staff\Tables\AbsenceFormats;
use Pupilsight\Module\Staff\Tables\CoverageCalendar;

if (isActionAccessible($guid, $connection2, '/modules/Staff/coverage_my.php') == false) {
    // Access denied
    $page->addError(__('You do not have access to this action.'));
} else {
    // Proceed!
    $page->breadcrumbs->add(__('My Coverage'));

    if (isset($_GET['return'])) {
        returnProcess($guid, $_GET['return'], null, null);
    }
    
    $pupilsightPersonID = $_SESSION[$guid]['pupilsightPersonID'];
    
    $schoolYearGateway = $container->get(SchoolYearGateway::class);
    $staffCoverageGateway = $container->get(StaffCoverageGateway::class);
    $substituteGateway = $container->get(SubstituteGateway::class);
    $userGateway = $container->get(UserGateway::class);
    $urgencyThreshold = getSettingByScope($connection2, 'Staff', 'urgencyThreshold');

    // TODAY'S COVERAGE
    $criteria = $staffCoverageGateway->newQueryCriteria()
        ->sortBy('timeStart')
        ->filterBy('status:Accepted')
        ->filterBy('dateStart:'.date('Y-m-d'))
        ->filterBy('dateEnd:'.date('Y-m-d'))
        ->fromPOST('staffCoverageToday');

    $todaysCoverage = $staffCoverageGateway->queryCoverageByPersonCovering($criteria, $pupilsightPersonID);

    if (count($todaysCoverage) > 0) {
        $page->write('<h2>'.__("Today's Coverage").'</h2>');

        foreach ($todaysCoverage as $coverage) {
            $status = Format::dateRangeReadable($coverage['dateStart'], $coverage['dateEnd']).' - ';
            $status .= $coverage['allDay'] == 'Y'
                ? __('All Day')
                : Format::timeRange($coverage['timeStart'], $coverage['timeEnd']);

            // Staff Card
            $container->get(StaffCard::class)
                ->setPerson($coverage['pupilsightPersonID'])
                ->setStatus($status)
                ->compose($page);

            // Today's Coverage View Composer
            $container->get(CoverageTodayView::class)
                ->setCoverage($coverage['pupilsightStaffCoverageID'], $coverage['pupilsightPersonID'])
                ->compose($page);
        }
    }


    // TEACHER COVERAGE
    $criteria = $staffCoverageGateway->newQueryCriteria()
        ->sortBy('date')
        ->filterBy('date:upcoming')
        ->fromPOST('staffCoverageSelf');

    $coverage = $staffCoverageGateway->queryCoverageByPersonAbsent($criteria, $pupilsightPersonID);
    if (isActionAccessible($guid, $connection2, '/modules/Staff/coverage_request.php') || $coverage->getResultCount() > 0) {
        $table = DataTable::createPaginated('staffCoverageSelf', $criteria);
        $table->setTitle(__('My Coverage'));

        $table->modifyRows(function ($coverage, $row) {
            if ($coverage['status'] == 'Accepted') $row->addClass('current');
            if ($coverage['status'] == 'Declined') $row->addClass('error');
            if ($coverage['status'] == 'Cancelled') $row->addClass('dull');
            return $row;
        });

        $table->addMetaData('filterOptions', [
            'date:upcoming'    => __('Upcoming'),
            'date:past'        => __('Past'),
            'status:requested' => __('Status').': '.__('Requested'),
            'status:accepted'  => __('Status').': '.__('Accepted'),
            'status:declined'  => __('Status').': '.__('Declined'),
            'status:cancelled' => __('Status').': '.__('Cancelled'),
        ]);

        $table->addColumn('status', __('Status'))
            ->width('15%')
            ->format(function ($coverage) use ($urgencyThreshold) {
                return AbsenceFormats::coverageStatus($coverage, $urgencyThreshold);
            });

        $table->addColumn('date', __('Date'))
            ->context('primary')
            ->format([AbsenceFormats::class, 'dateDetails']);

        $table->addColumn('requested', __('Substitute'))
            ->context('primary')
            ->width('30%')
            ->sortable(['surnameCoverage', 'preferredNameCoverage'])
            ->format([AbsenceFormats::class, 'substituteDetails']);

        $table->addColumn('notesCoverage', __('Comment'))
            ->format(function ($coverage) {
                return $coverage['status'] == 'Requested'
                    ? Format::small(__('Pending'))
                    : Format::truncate($coverage['notesCoverage'], 60);
            });

        $table->addActionColumn()
            ->addParam('pupilsightStaffCoverageID')
            ->format(function ($coverage, $actions) {
                $actions->addAction('view', __('View Details'))
                    ->isModal(800, 550)
                    ->setURL('/modules/Staff/coverage_view_details.php');

                if ($coverage['status'] == 'Requested' || $coverage['status'] == 'Accepted') {
                    $actions->addAction('edit', __('Edit'))
                        ->setURL('/modules/Staff/coverage_view_edit.php');
                }
                    
                if ($coverage['status'] == 'Requested' || ($coverage['status'] == 'Accepted' && $coverage['dateEnd'] < date('Y-m-d'))) {
                    $actions->addAction('cancel', __('Cancel'))
                        ->setIcon('iconCross')
                        ->setURL('/modules/Staff/coverage_view_cancel.php');
                }
            });

        echo $table->render($coverage);
    }

    // SUBSTITUTE COVERAGE
    $substitute = $substituteGateway->getSubstituteByPerson($pupilsightPersonID);
    if (!empty($substitute)) {
        $criteria = $staffCoverageGateway->newQueryCriteria()->pageSize(0);

        $coverage = $staffCoverageGateway->queryCoverageByPersonCovering($criteria, $pupilsightPersonID, false);
        $exceptions = $substituteGateway->queryUnavailableDatesBySub($criteria, $pupilsightPersonID);
        $schoolYear = $schoolYearGateway->getSchoolYearByID($_SESSION[$guid]['pupilsightSchoolYearID']);

        // CALENDAR VIEW
        $table = CoverageCalendar::create($coverage->toArray(), $exceptions->toArray(), $schoolYear['firstDay'], $schoolYear['lastDay']);

        $table->addHeaderAction('availability', __('Edit Availability'))
            ->setURL('/modules/Staff/coverage_availability.php')
            ->setIcon('planner')
            ->displayLabel();

        echo $table->getOutput().'<br/>';

        // QUERY
        $criteria = $staffCoverageGateway->newQueryCriteria()
            ->sortBy('date')
            ->filterBy('date:upcoming')
            ->fromPOST('staffCoverageOther');

        $coverage = $staffCoverageGateway->queryCoverageByPersonCovering($criteria, $pupilsightPersonID);

        // DATA TABLE
        $table = DataTable::createPaginated('staffCoverageOther', $criteria);
        $table->setTitle(__('Coverage Requests'));

        $table->modifyRows(function ($coverage, $row) {
            if ($coverage['status'] == 'Accepted') $row->addClass('current');
            if ($coverage['status'] == 'Declined') $row->addClass('error');
            if ($coverage['status'] == 'Cancelled') $row->addClass('dull');
            return $row;
        });

        $table->addMetaData('filterOptions', [
            'date:upcoming'    => __('Upcoming'),
            'date:past'        => __('Past'),
            'status:requested' => __('Status').': '.__('Requested'),
            'status:accepted'  => __('Status').': '.__('Accepted'),
            'status:declined'  => __('Status').': '.__('Declined'),
            'status:cancelled' => __('Status').': '.__('Cancelled'),
        ]);

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
            ->width('30%')
            ->sortable(['surname', 'preferredName'])
            ->format([AbsenceFormats::class, 'personDetails']);
            
        $table->addColumn('notesStatus', __('Comment'))
            ->format(function ($coverage) {
                return Format::truncate($coverage['notesStatus'], 60);
            });

        $table->addActionColumn()
            ->addParam('pupilsightStaffCoverageID')
            ->format(function ($coverage, $actions) {

                if ($coverage['status'] == 'Requested') {
                    $actions->addAction('accept', __('Accept'))
                        ->setIcon('iconTick')
                        ->setURL('/modules/Staff/coverage_view_accept.php');

                    $actions->addAction('decline', __('Decline'))
                        ->setIcon('iconCross')
                        ->setURL('/modules/Staff/coverage_view_decline.php');
                } else {
                    $actions->addAction('view', __('View Details'))
                        ->isModal(800, 550)
                        ->setURL('/modules/Staff/coverage_view_details.php');
                }
            });

        echo $table->render($coverage);
    }
}
