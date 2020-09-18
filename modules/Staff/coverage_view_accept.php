<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Forms\Form;
use Pupilsight\Services\Format;
use Pupilsight\Domain\Staff\StaffCoverageGateway;
use Pupilsight\Domain\Staff\SubstituteGateway;
use Pupilsight\Module\Staff\View\StaffCard;
use Pupilsight\Module\Staff\View\CoverageView;
use Pupilsight\Module\Staff\Tables\CoverageDates;

if (isActionAccessible($guid, $connection2, '/modules/Staff/coverage_view_accept.php') == false) {
    // Access denied
    $page->addError(__('You do not have access to this action.'));
} else {
    //Proceed!
    $page->breadcrumbs
        ->add(__('My Coverage'), 'coverage_my.php')
        ->add(__('Accept Coverage Request'));

    if (isset($_GET['return'])) {
        returnProcess($guid, $_GET['return'], null, [
            'warning3' => __('This coverage request has already been accepted.'),
        ]);
    }

    $pupilsightStaffCoverageID = $_GET['pupilsightStaffCoverageID'] ?? '';

    $staffCoverageGateway = $container->get(StaffCoverageGateway::class);

    if (empty($pupilsightStaffCoverageID)) {
        $page->addError(__('You have not specified one or more required parameters.'));
        return;
    }

    $coverage = $staffCoverageGateway->getCoverageDetailsByID($pupilsightStaffCoverageID);
    if (empty($coverage)) {
        $page->addError(__('The specified record cannot be found.'));
        return;
    }

    if ($coverage['status'] != 'Requested') {
        $page->addWarning(__('This coverage request has already been accepted.'));
        return;
    }

    // Staff Card
    $staffCard = $container->get(StaffCard::class);
    $staffCard->setPerson($coverage['pupilsightPersonID'])->compose($page);

    // Coverage View Composer
    $coverageView = $container->get(CoverageView::class);
    $coverageView->setCoverage($pupilsightStaffCoverageID)->compose($page);

    // Coverage Dates
    $table = $container->get(CoverageDates::class)->create($pupilsightStaffCoverageID);
    $table->getRenderer()->addData('class', 'bulkActionForm');

    // Checkbox options
    $pupilsightPersonID = !empty($coverage['pupilsightPersonIDCoverage']) ? $coverage['pupilsightPersonIDCoverage'] : $_SESSION[$guid]['pupilsightPersonID'];
    $unavailable = $container->get(SubstituteGateway::class)->selectUnavailableDatesBySub($pupilsightPersonID, $pupilsightStaffCoverageID)->fetchGrouped();

    $datesAvailableToRequest = 0;
    $table->addCheckboxColumn('coverageDates', 'date')
        ->width('15%')
        ->checked(true)
        ->format(function ($coverage) use (&$datesAvailableToRequest, &$unavailable) {
            // Has this date already been requested?
            if (empty($coverage['pupilsightStaffCoverageID'])) return __('N/A');

            // Is this date unavailable: absent, already booked, or has an availability exception
            if (isset($unavailable[$coverage['date']])) {
                $times = $unavailable[$coverage['date']];

                foreach ($times as $time) {
                    // Handle full day and partial day unavailability
                    if ($time['allDay'] == 'Y' 
                    || ($time['allDay'] == 'N' && $coverage['allDay'] == 'Y')
                    || ($time['allDay'] == 'N' && $coverage['allDay'] == 'N'
                        && $time['timeStart'] <= $coverage['timeEnd']
                        && $time['timeEnd'] >= $coverage['timeStart'])) {
                        return Format::small(__($time['status'] ?? 'Not Available'));
                    }
                }
            }

            $datesAvailableToRequest++;
        })
        ->modifyCells(function ($coverage, $cell) {
            return $cell->addClass('h-10');
        });

    // FORM
    $form = Form::create('staffCoverage', $_SESSION[$guid]['absoluteURL'].'/modules/Staff/coverage_view_acceptProcess.php');
    $form->addHiddenValue('address', $_SESSION[$guid]['address']);
    $form->addHiddenValue('pupilsightStaffCoverageID', $pupilsightStaffCoverageID);

    $form->addRow()->addHeading(__('Accept Coverage Request'));

    $row = $form->addRow()->addContent($table->getOutput());

    if ($datesAvailableToRequest > 0) {
        $row = $form->addRow();
            $row->addLabel('notesCoverage', __('Reply'));
            $row->addTextArea('notesCoverage')->setRows(3)->setClass('w-full sm:max-w-xs');

        $row = $form->addRow();
            $row->addContent();
            $row->addSubmit();
    } else {
        $row = $form->addRow()->addAlert(__('Not Available'), 'warning');
    }

    echo $form->getOutput();
}
