<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Forms\Form;
use Pupilsight\Forms\DatabaseFormFactory;
use Pupilsight\Forms\Prefab\BulkActionForm;
use Pupilsight\Services\Format;
use Pupilsight\Domain\Staff\SubstituteGateway;
use Pupilsight\Domain\School\SchoolYearGateway;
use Pupilsight\Domain\Staff\StaffCoverageGateway;
use Pupilsight\Module\Staff\Tables\CoverageCalendar;

if (isActionAccessible($guid, $connection2, '/modules/Staff/coverage_availability.php') == false) {
    // Access denied
    $page->addError(__('You do not have access to this action.'));
} else {
    // Proceed!
    if (isActionAccessible($guid, $connection2, '/modules/Staff/substitutes_manage.php')) {
        $page->breadcrumbs
            ->add(__('Manage Substitutes'), 'substitutes_manage.php')
            ->add(__('Edit Availability'));

        $pupilsightPersonID = $_GET['pupilsightPersonID'] ?? $_SESSION[$guid]['pupilsightPersonID'];

        // Display the details for who's availability we're editing
        $form = Form::create('userInfo', '#');
        $form->setFactory(DatabaseFormFactory::create($pdo));

        $row = $form->addRow();
            $row->addLabel('personLabel', __('Person'));
            $row->addSelectUsers('person')->readonly()->selected($pupilsightPersonID);

        echo $form->getOutput();
    } else {
        $page->breadcrumbs
            ->add(__('My Coverage'), 'coverage_my.php')
            ->add(__('Edit Availability'));

        $pupilsightPersonID = $_SESSION[$guid]['pupilsightPersonID'];
    }

    if (empty($pupilsightPersonID)) {
        $page->addError(__('You have not specified one or more required parameters.'));
        return;
    }

    $substituteGateway = $container->get(SubstituteGateway::class);
    $schoolYearGateway = $container->get(SchoolYearGateway::class);
    $staffCoverageGateway = $container->get(StaffCoverageGateway::class);

    if (isset($_GET['return'])) {
        returnProcess($guid, $_GET['return'], null, [
            'success1' => __('Your request was completed successfully.').' '.__('You may now continue by submitting a coverage request for this absence.')
        ]);
    }

    $criteria = $staffCoverageGateway->newQueryCriteria()->pageSize(0);

    $coverage = $staffCoverageGateway->queryCoverageByPersonCovering($criteria, $pupilsightPersonID, false);
    $exceptions = $substituteGateway->queryUnavailableDatesBySub($criteria, $pupilsightPersonID);
    $schoolYear = $schoolYearGateway->getSchoolYearByID($_SESSION[$guid]['pupilsightSchoolYearID']);

    // CALENDAR VIEW
    $calendar = CoverageCalendar::create($coverage->toArray(), $exceptions->toArray(), $schoolYear['firstDay'], $schoolYear['lastDay']);
    echo $calendar->getOutput().'<br/>';

    // BULK ACTIONS
    $form = BulkActionForm::create('bulkAction', $_SESSION[$guid]['absoluteURL'].'/modules/Staff/coverage_availability_deleteProcess.php');
    $form->setTitle(__('Dates'));
    $form->addHiddenValue('pupilsightPersonID', $pupilsightPersonID);

    // DATA TABLE
    $criteria = $substituteGateway->newQueryCriteria()
        ->sortBy('date')
        ->fromPOST();

    $dates = $substituteGateway->queryUnavailableDatesBySub($criteria, $pupilsightPersonID);
    
    $bulkActions = array(
        'Delete' => __('Delete'),
    );

    $col = $form->createBulkActionColumn($bulkActions);
    $col->addSubmit(__('Go'));

    $table = $form->addRow()->addDataTable('staffAvailabilityExceptions', $criteria)->withData($dates);

    $table->addMetaData('bulkActions', $col);

    $table->addColumn('date', __('Date'))
        ->format(Format::using('dateReadable', 'date'));

    $table->addColumn('timeStart', __('Time'))->format(function ($date) {
        if ($date['allDay'] == 'N') {
            return Format::timeRange($date['timeStart'], $date['timeEnd']);
        } else {
            return __('All Day');
        }
    });

    $table->addColumn('reason', __('Reason'))
        ->format(function ($date) {
            return !empty($date['reason'])
                ? __($date['reason'])
                : Format::small(__('Not Available'));
        });

    $table->addActionColumn()
        ->addParam('pupilsightPersonID', $pupilsightPersonID)
        ->addParam('pupilsightStaffCoverageDateID')
        ->format(function ($date, $actions) {
            $actions->addAction('deleteInstant', __('Delete'))
                    ->setIcon('garbage')
                    ->isDirect()
                    ->setURL('/modules/Staff/coverage_availability_deleteProcess.php')
                    ->addConfirmation(__('Are you sure you wish to delete this record?'));
        });

    $table->addCheckboxColumn('pupilsightStaffCoverageDateID');

    echo $form->getOutput();

    
    $form = Form::create('staffAvailability', $_SESSION[$guid]['absoluteURL'].'/modules/Staff/coverage_availability_addProcess.php');

    $form->setFactory(DatabaseFormFactory::create($pdo));
    $form->addHiddenValue('address', $_SESSION[$guid]['address']);
    $form->addHiddenValue('pupilsightPersonID', $pupilsightPersonID);

    $form->addRow()->addHeading(__('Add'));

    $row = $form->addRow();
    $row->addLabel('allDay', __('All Day'));
    $row->addYesNoRadio('allDay')->checked('Y');

    $form->toggleVisibilityByClass('timeOptions')->onRadio('allDay')->when('N');

    $date = $_GET['date'] ?? '';
    $row = $form->addRow();
        $row->addLabel('dateStart', __('Start Date'));
        $row->addDate('dateStart')->chainedTo('dateEnd')->isRequired()->setValue($date);

    $row = $form->addRow();
        $row->addLabel('dateEnd', __('End Date'));
        $row->addDate('dateEnd')->chainedFrom('dateStart')->setValue($date);

    $row = $form->addRow()->addClass('timeOptions');
        $row->addLabel('timeStart', __('Start Time'));
        $row->addTime('timeStart')->isRequired();

    $row = $form->addRow()->addClass('timeOptions');
        $row->addLabel('timeEnd', __('End Time'));
        $row->addTime('timeEnd')->chainedTo('timeStart')->isRequired();

    $row = $form->addRow();
        $row->addLabel('reason', __('Reason'))->description(__('Optional'));
        $row->addTextField('reason')->maxLength(255);

    $row = $form->addRow();
        $row->addFooter();
        $row->addSubmit();

    echo $form->getOutput();
}
