<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Services\Format;
use Pupilsight\Domain\Staff\StaffCoverageGateway;
use Pupilsight\Module\Staff\View\StaffCard;
use Pupilsight\Module\Staff\Tables\CoverageDates;
use Pupilsight\Module\Staff\View\CoverageView;

if (isActionAccessible($guid, $connection2, '/modules/Staff/coverage_view_details.php') == false) {
    // Access denied
    $page->addError(__('You do not have access to this action.'));
} else {
    $page->breadcrumbs
        ->add(__('My Coverage'), 'coverage_my.php')
        ->add(__('View Details'));

    $pupilsightStaffCoverageID = $_GET['pupilsightStaffCoverageID'] ?? '';

    $staffCoverageGateway = $container->get(StaffCoverageGateway::class);
    $coverage = $container->get(StaffCoverageGateway::class)->getByID($pupilsightStaffCoverageID);
    
    // Staff Card
    $staffCard = $container->get(StaffCard::class);
    $staffCard->setPerson($coverage['pupilsightPersonID'])->compose($page);

    // Coverage Dates
    $table = $container->get(CoverageDates::class)->create($pupilsightStaffCoverageID);
    $page->write($table->getOutput());

    // Coverage View Composer
    $coverageView = $container->get(CoverageView::class);
    $coverageView->setCoverage($pupilsightStaffCoverageID)->compose($page);

    // Attachment
    if (!empty($coverage['attachmentType'])) {
        $page->writeFromTemplate('statusComment.twig.html', [
            'name'       => __('Attachment'),
            'icon'       => 'internalAssessment',
            'tag'        => 'dull',
            'status'     => __($coverage['attachmentType']),
            'attachment' => $coverage['attachmentType'] != 'Text' ? Format::link($coverage['attachmentContent']) : '',
            'html'       => $coverage['attachmentType'] == 'Text' ? $coverage['attachmentContent'] : '',
        ]);
    }
}
