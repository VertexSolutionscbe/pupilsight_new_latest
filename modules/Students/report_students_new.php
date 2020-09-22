<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\View\View;
use Pupilsight\Services\Format;
use Pupilsight\Forms\Form;
use Pupilsight\Forms\DatabaseFormFactory;
use Pupilsight\Tables\Prefab\ReportTable;
use Pupilsight\Domain\User\FamilyGateway;
use Pupilsight\Domain\Students\StudentReportGateway;

//Module includes
require_once __DIR__ . '/moduleFunctions.php';

if (isActionAccessible($guid, $connection2, '/modules/Students/report_students_new') == false) {
    //Acess denied
    echo "<div class='alert alert-danger'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    //Proceed!
    $viewMode = $_REQUEST['format'] ?? '';
    $type = $_GET['type'] ?? '';
    $ignoreEnrolment = $_GET['ignoreEnrolment'] ?? false;
    $startDateFrom = $_GET['startDateFrom'] ?? '';
    $startDateTo = $_GET['startDateTo'] ?? '';
    $pupilsightSchoolYearID = $pupilsight->session->get('pupilsightSchoolYearID');

    if (empty($viewMode)) {
        $page->breadcrumbs->add(__('New Students'));

        $form = Form::create('action', $_SESSION[$guid]['absoluteURL'].'/index.php','get');
        $form->setTitle(__('Choose Options'));
        $form->setFactory(DatabaseFormFactory::create($pdo));
        $form->setClass('noIntBorder fullWidth');

        $form->addHiddenValue('q', "/modules/".$_SESSION[$guid]['module']."/report_students_new.php");

        $row = $form->addRow();
            $row->addLabel('type', __('Type'));
            $row->addSelect('type')->fromArray(array('Current School Year' => __('Current School Year'), 'Date Range' => __('Date Range')))->selected($type)->required();

        $form->toggleVisibilityByClass('dateRange')->onSelect('type')->when('Date Range');

        $row = $form->addRow()->addClass('dateRange');
            $row->addLabel('startDateFrom', __('From Date'))->description('Earliest student start date to include.')->append('<br/>')->append(__('Format:').' ')->append($_SESSION[$guid]['i18n']['dateFormat']);
            $row->addDate('startDateFrom')->setValue($startDateFrom)->required();

        $row = $form->addRow()->addClass('dateRange');
            $row->addLabel('startDateTo', __('To Date'))->description('Latest student start date to include.')->append('<br/>')->append(__('Format:').' ')->append($_SESSION[$guid]['i18n']['dateFormat']);
            $row->addDate('startDateTo')->setValue($startDateTo)->required();

        $row = $form->addRow()->addClass('dateRange');
            $row->addLabel('ignoreEnrolment', __('Ignore Enrolment'))->description(__('This is useful for picking up students who are set to Full, have a start date but are not yet enroled.'));
            $row->addCheckbox('ignoreEnrolment')->checked($ignoreEnrolment);

        $row = $form->addRow();
            $row->addFooter();
            $row->addSearchSubmit($pupilsight->session);

        echo $form->getOutput();
    }

    if (empty($type)) {
        return;
    }

    $reportGateway = $container->get(StudentReportGateway::class);
    $familyGateway = $container->get(FamilyGateway::class);

    // CRITERIA
    $criteria = $reportGateway->newQueryCriteria()
        ->sortBy(['rollGroup', 'pupilsightPerson.surname', 'pupilsightPerson.preferredName'])
        ->pageSize(!empty($viewMode) ? 0 : 50)
        ->fromPOST();

    $students = $reportGateway->queryStudentStatusBySchoolYear(
        $criteria,
        $pupilsightSchoolYearID,
        'Full',
        Format::dateConvert($startDateFrom),
        Format::dateConvert($startDateTo),
        $ignoreEnrolment
    );

    // Join a set of family adults per student
    $people = $students->getColumn('pupilsightPersonID');
    $familyAdults = $familyGateway->selectFamilyAdultsByStudent($people, true)->fetchGrouped();
    $students->joinColumn('pupilsightPersonID', 'familyAdults', $familyAdults);

    // DATA TABLE
    $table = ReportTable::createPaginated('studentsNew', $criteria)->setViewMode($viewMode, $pupilsight->session);
    $table->setTitle(__('New Students'));

    $table->modifyRows($reportGateway->getSharedUserRowHighlighter());

    if ($type == 'Date Range') {
        $table->setDescription(__('This report shows all students whose Start Date is on or between the indicated dates.'));
    } else {
        $table->setDescription(__('This report shows all students who are newly arrived in the school during the current academic year (e.g. they were not enroled in the previous academic year).'));
    }

    $table->addRowCountColumn($students->getPageFrom());
    $table->addColumn('student', __('Student'))
        ->context('primary')
        ->sortable(['pupilsightPerson.surname', 'pupilsightPerson.preferredName'])
        ->format(function ($student) {
            return Format::name('', $student['preferredName'], $student['surname'], 'Student', true, true) 
                 . '<br/><small><i>'.Format::userStatusInfo($student).'</i></small>';
        });
    $table->addColumn('rollGroup', __('Roll Group'))
        ->context('primary');
    $table->addColumn('username', __('Username'));
    $table->addColumn('dateStart', __('Start Date'))
        ->context('secondary')
        ->format(Format::using('date', 'dateStart'));
    $table->addColumn('lastSchool', __('Last School'));

    $view = new View($container->get('twig'));
    $table->addColumn('contacts', __('Parents'))
        ->width('30%')
        ->notSortable()
        ->format(function ($student) use ($view) {
            return $view->fetchFromTemplate(
                'formats/familyContacts.twig.html',
                ['familyAdults' => $student['familyAdults'], 'includeCitizenship' => true]
            );
        });

    echo $table->render($students);
}
