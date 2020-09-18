<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Forms\Form;
use Pupilsight\Forms\DatabaseFormFactory;
use Pupilsight\Domain\Staff\StaffCoverageGateway;
use Pupilsight\Module\Staff\View\StaffCard;
use Pupilsight\Module\Staff\View\CoverageView;
use Pupilsight\Module\Staff\Tables\CoverageDates;

if (isActionAccessible($guid, $connection2, '/modules/Staff/coverage_view_decline.php') == false) {
    // Access denied
    $page->addError(__('You do not have access to this action.'));
} else {
    //Proceed!
    $page->breadcrumbs
        ->add(__('My Coverage'), 'coverage_my.php')
        ->add(__('Decline Coverage Request'));

    if (isset($_GET['return'])) {
        returnProcess($guid, $_GET['return'], null);
    }

    $pupilsightStaffCoverageID = $_GET['pupilsightStaffCoverageID'] ?? '';

    $staffCoverageGateway = $container->get(StaffCoverageGateway::class);

    if (empty($pupilsightStaffCoverageID)) {
        $page->addError(__('You have not specified one or more required parameters.'));
        return;
    }

    $coverage = $staffCoverageGateway->getCoverageDetailsByID($pupilsightStaffCoverageID);

    if (empty($coverage) || $coverage['status'] != 'Requested') {
        $page->addError(__('The specified record cannot be found.'));
        return;
    }

    $form = Form::create('staffCoverage', $_SESSION[$guid]['absoluteURL'].'/modules/Staff/coverage_view_declineProcess.php');

    $form->setFactory(DatabaseFormFactory::create($pdo));
    $form->addHiddenValue('address', $_SESSION[$guid]['address']);
    $form->addHiddenValue('pupilsightStaffCoverageID', $pupilsightStaffCoverageID);

    $form->addRow()->addHeading(__('Decline Coverage Request'));
    
    // Staff Card
    $staffCard = $container->get(StaffCard::class);
    $staffCard->setPerson($coverage['pupilsightPersonID'])->compose($page);

    // Coverage Dates
    $table = $container->get(CoverageDates::class)->create($pupilsightStaffCoverageID);
    $page->write($table->getOutput());
    
    // Coverage View Composer
    $coverageView = $container->get(CoverageView::class);
    $coverageView->setCoverage($pupilsightStaffCoverageID)->compose($page);

    $row = $form->addRow();
        $row->addLabel('markAsUnavailable', __('Not Available'))->description(__('Checking this will mark you as unavailable for any further requests on these dates.'));
        $row->addCheckbox('markAsUnavailable')->checked(true);

    $row = $form->addRow();
        $row->addLabel('notesCoverage', __('Reply'));
        $row->addTextArea('notesCoverage')->setRows(3);

    $row = $form->addRow();
        $row->addFooter();
        $row->addSubmit();
    

    echo $form->getOutput();
}
