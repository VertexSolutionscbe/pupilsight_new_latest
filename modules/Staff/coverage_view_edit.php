<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Forms\Form;
use Pupilsight\Domain\Staff\StaffCoverageGateway;
use Pupilsight\Module\Staff\View\StaffCard;
use Pupilsight\Module\Staff\View\CoverageView;
use Pupilsight\Module\Staff\Tables\CoverageDates;

if (isActionAccessible($guid, $connection2, '/modules/Staff/coverage_view_edit.php') == false) {
    // Access denied
    $page->addError(__('You do not have access to this action.'));
} else {
    //Proceed!
    $page->breadcrumbs
        ->add(__('My Coverage'), 'coverage_my.php')
        ->add(__('Edit Coverage'));

    if (isset($_GET['return'])) {
        returnProcess($guid, $_GET['return'], null, [
            'error3' => __('Failed to write file to disk.'),
        ]);
    }

    $pupilsightStaffCoverageID = $_GET['pupilsightStaffCoverageID'] ?? '';
    $staffCoverageGateway = $container->get(StaffCoverageGateway::class);

    if (empty($pupilsightStaffCoverageID)) {
        $page->addError(__('You have not specified one or more required parameters.'));
        return;
    }

    $coverage = $staffCoverageGateway->getByID($pupilsightStaffCoverageID);

    if (empty($coverage)) {
        $page->addError(__('The specified record cannot be found.'));
        return;
    }

    if ($coverage['pupilsightPersonID'] != $_SESSION[$guid]['pupilsightPersonID'] && $coverage['pupilsightPersonIDStatus'] != $_SESSION[$guid]['pupilsightPersonID']) {
        $page->addError(__('You do not have access to this action.'));
        return;
    }

    // Staff Card
    $staffCard = $container->get(StaffCard::class);
    $staffCard->setPerson($coverage['pupilsightPersonID'])->compose($page);

    // Coverage Dates
    $table = $container->get(CoverageDates::class)->create($pupilsightStaffCoverageID);
    $page->write($table->getOutput());

    // Coverage View Composer
    $coverageView = $container->get(CoverageView::class);
    $coverageView->setCoverage($pupilsightStaffCoverageID)->compose($page);
    
    // FORM
    $form = Form::create('staffCoverageFile', $_SESSION[$guid]['absoluteURL'].'/modules/Staff/coverage_view_editProcess.php');

    $form->addHiddenValue('address', $_SESSION[$guid]['address']);
    $form->addHiddenValue('pupilsightStaffCoverageID', $pupilsightStaffCoverageID);

    $form->addRow()->addHeading(__('Attachment'));
    
    $types = array('File' => __('File'),  'Link' => __('Link'), 'Text' => __('Text'));
    $row = $form->addRow();
        $row->addLabel('attachmentType', __('Type'));
        $row->addSelect('attachmentType')->fromArray($types)->placeholder()->selected($coverage['attachmentType'] ?? '');

    // File
    $form->toggleVisibilityByClass('attachmentFile')->onSelect('attachmentType')->when('File');
    $row = $form->addRow()->addClass('attachmentFile');
        $row->addLabel('file', __('File'));
        $row->addFileUpload('file')
            ->required()
            ->setAttachment('attachment', $_SESSION[$guid]['absoluteURL'], $coverage['attachmentContent'] ?? '');

    // Text
    $form->toggleVisibilityByClass('attachmentText')->onSelect('attachmentType')->when('Text');
    $row = $form->addRow()->addClass('attachmentText');
        $column = $row->addColumn()->setClass('');
        $column->addLabel('text', __('Text'));
        $column->addEditor('text', $guid)
            ->required()
            ->setValue($coverage['attachmentContent'] ?? '');

    // Link
    $form->toggleVisibilityByClass('attachmentLink')->onSelect('attachmentType')->when('Link');
    $row = $form->addRow()->addClass('attachmentLink');
        $row->addLabel('link', __('Link'));
        $row->addURL('link')
            ->maxLength(255)
            ->required()
            ->setValue($coverage['attachmentContent'] ?? '');

    $form->addRow()->addHeading(__('Details'));

    $row = $form->addRow();
        $row->addLabel('notesStatus', __('Comment'))->description(__('This message is shared with substitutes, and is also visible to users who manage staff coverage.'));
        $row->addTextArea('notesStatus')->setRows(3)->setValue($coverage['notesStatus']);

    $row = $form->addRow();
        $row->addFooter();
        $row->addSubmit();
    
    echo $form->getOutput();
}
