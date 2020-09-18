<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Forms\Form;
use Pupilsight\Forms\DatabaseFormFactory;
use Pupilsight\Tables\DataTable;
use Pupilsight\Services\Format;
use Pupilsight\Domain\User\UserGateway;
use Pupilsight\Domain\Staff\StaffCoverageGateway;
use Pupilsight\Domain\Staff\StaffCoverageDateGateway;
use Pupilsight\Module\Staff\View\StaffCard;

if (isActionAccessible($guid, $connection2, '/modules/Staff/coverage_manage_edit.php') == false) {
    // Access denied
    $page->addError(__('You do not have access to this action.'));
} else {
    // Proceed!
    $page->breadcrumbs
        ->add(__('Manage Staff Coverage'), 'coverage_manage.php')
        ->add(__('Edit Coverage'));

    if (isset($_GET['return'])) {
        returnProcess($guid, $_GET['return'], null, [
            'success1' => __('Your request was completed successfully.'),
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

    $form = Form::create('staffCoverage', $_SESSION[$guid]['absoluteURL'].'/modules/Staff/coverage_manage_editProcess.php');

    $form->setFactory(DatabaseFormFactory::create($pdo));
    $form->addHiddenValue('address', $_SESSION[$guid]['address']);
    $form->addHiddenValue('pupilsightStaffCoverageID', $pupilsightStaffCoverageID);

    $form->addRow()->addHeading(__('Coverage Request'));

    if (!empty($coverage['pupilsightPersonID'])) {
        $staffCard = $container->get(StaffCard::class);
        $staffCard->setPerson($coverage['pupilsightPersonID'])->compose($page);
    }

    if (!empty($coverage['pupilsightStaffAbsenceID'])) {
        $row = $form->addRow();
            $row->addLabel('typeLabel', __('Type'));
            $row->addTextField('type')->readonly()->setValue($coverage['reason'] ? "{$coverage['type']} ({$coverage['reason']})" : $coverage['type']);
    }
    
    $row = $form->addRow();
        $row->addLabel('timestamp', __('Requested'));
        $row->addTextField('timestampValue')
            ->readonly()
            ->setValue(Format::relativeTime($coverage['timestampStatus'], false))
            ->setTitle($coverage['timestampStatus']);

    
    $row = $form->addRow();
        $row->addLabel('notesStatusLabel', __('Notes'));
        $row->addTextArea('notesStatus')->setRows(3)->setValue($coverage['notesStatus']);
    
    
    $form->addRow()->addHeading(__('Substitute'));

    if ($coverage['requestType'] == 'Individual') {
        $row = $form->addRow();
            $row->addLabel('pupilsightPersonIDLabel', __('Person'));
            $row->addSelectUsers('pupilsightPersonIDCoverage')
                ->placeholder()
                ->isRequired()
                ->selected($coverage['pupilsightPersonIDCoverage'] ?? '')
                ->setReadonly(true);
    } else if ($coverage['requestType'] == 'Broadcast') {

        $row = $form->addRow();
            $row->addLabel('requestTypeLabel', __('Type'));
            $row->addTextField('requestType')->readonly()->setValue($coverage['requestType']);

        $notificationList = $coverage['notificationSent'] == 'Y' ? json_decode($coverage['notificationListAbsence'] ?? '') : [];

        if ($notificationList) {
            $notified = $container->get(UserGateway::class)->selectNotificationDetailsByPerson($notificationList)->fetchGroupedUnique();

            $row = $form->addRow();
                $row->addLabel('sentToLabel', __('Notified'));
                $row->addTextArea('sentTo')->readonly()->setValue(Format::nameList($notified, 'Staff', false, true, ', '));
        }
    }

    // Output the coverage status change timestamp, if it has been actioned
    if ($coverage['status'] != 'Requested' && !empty($coverage['timestampCoverage'])) {
        $row = $form->addRow();
        $row->addLabel('timestampCoverage', __($coverage['status']));
        $row->addTextField('timestampCoverageValue')
            ->readonly()
            ->setValue(Format::relativeTime($coverage['timestampCoverage'], false))
            ->setTitle($coverage['timestampCoverage']);
    }

    if (!empty($coverage['notesCoverage'])) {
        $row = $form->addRow();
            $row->addLabel('notesCoverageLabel', __('Comment'));
            $row->addTextArea('notesCoverage')->setRows(3)->readonly();
    }

    // DATA TABLE
    $coverageDates = $container->get(StaffCoverageDateGateway::class)->selectDatesByCoverage($pupilsightStaffCoverageID);
    
    $table = DataTable::create('staffCoverageDates');
    $table->setTitle(__('Dates'));

    $table->addColumn('date', __('Date'))
        ->format(Format::using('dateReadable', 'date'));

    $table->addColumn('timeStart', __('Time'))
        ->format(function ($coverage) {
            if ($coverage['allDay'] == 'N') {
                return Format::small(Format::timeRange($coverage['timeStart'], $coverage['timeEnd']));
            } else {
                return Format::small(__('All Day'));
            }
        });

    $table->addColumn('coverage', __('Coverage'))
        ->format(function ($coverage) {
            if (empty($coverage['coverage'])) {
                return Format::small(__('N/A'));
            }

            return $coverage['coverage'] == 'Accepted'
                    ? Format::name($coverage['titleCoverage'], $coverage['preferredNameCoverage'], $coverage['surnameCoverage'], 'Staff', false, true)
                    : '<span class="tag message">'.__('Pending').'</span>';
        });

    $row = $form->addRow()->addContent($table->render($coverageDates->toDataSet()));

    $row = $form->addRow();
        $row->addFooter();
        $row->addSubmit();
    
    $form->loadAllValuesFrom($coverage);

    echo $form->getOutput();
}
