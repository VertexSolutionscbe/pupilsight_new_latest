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

if (isActionAccessible($guid, $connection2, '/modules/Students/report_students_left.php') == false) {
    //Acess denied
    echo "<div class='alert alert-danger'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    //Proceed!
    $viewMode = $_REQUEST['format'] ?? '';
    $type = $_GET['type'] ?? '';
    $ignoreStatus = $_GET['ignoreStatus'] ?? false;
    $endDateFrom = $_GET['endDateFrom'] ?? '';
    $endDateTo = $_GET['endDateTo'] ?? '';
    $pupilsightSchoolYearID = $pupilsight->session->get('pupilsightSchoolYearID');

    if (empty($viewMode)) {
        $page->breadcrumbs->add(__('Left Students'));

        $form = Form::create('action', $_SESSION[$guid]['absoluteURL'].'/index.php','get');
        $form->setTitle(__('Choose Options'));
        $form->setFactory(DatabaseFormFactory::create($pdo));
        $form->setClass('noIntBorder fullWidth');

        $form->addHiddenValue('q', "/modules/".$_SESSION[$guid]['module']."/report_students_left.php");

        $row = $form->addRow();
            $row->addLabel('type', __('Type'));
            $row->addSelect('type')->fromArray(array('Current School Year' => __('Current School Year'), 'Date Range' => __('Date Range')))->selected($type)->required();

        $form->toggleVisibilityByClass('dateRange')->onSelect('type')->when('Date Range');

        $row = $form->addRow()->addClass('dateRange');
            $row->addLabel('endDateFrom', __('From Date'))->description('Earliest student end date to include.')->append('<br/>')->append(__('Format:').' ')->append($_SESSION[$guid]['i18n']['dateFormat']);
            $row->addDate('endDateFrom')->setValue($endDateFrom)->required();

        $row = $form->addRow()->addClass('dateRange');
            $row->addLabel('endDateTo', __('To Date'))->description('Latest student end date to include.')->append('<br/>')->append(__('Format:').' ')->append($_SESSION[$guid]['i18n']['dateFormat']);
            $row->addDate('endDateTo')->setValue($endDateTo)->required();

        $row = $form->addRow()->addClass('dateRange');
            $row->addLabel('ignoreStatus', __('Ignore Status'))->description(__('This is useful for picking up students who have not yet left, but have an End Date set.'));
            $row->addCheckbox('ignoreStatus')->checked($ignoreStatus);

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
        'Left',
        Format::dateConvert($endDateFrom),
        Format::dateConvert($endDateTo),
        $ignoreStatus
    );

    // Join a set of family adults per student
    $people = $students->getColumn('pupilsightPersonID');
    $familyAdults = $familyGateway->selectFamilyAdultsByStudent($people, true)->fetchGrouped();
    $students->joinColumn('pupilsightPersonID', 'familyAdults', $familyAdults);

    // DATA TABLE
    $table = ReportTable::createPaginated('studentsLeft', $criteria)->setViewMode($viewMode, $pupilsight->session);
    $table->setTitle(__('Left Students'));

    if ($type == 'Date Range') {
        $table->setDescription(__('This report shows all students whose End Date is on or between the indicated dates.'));
    } else {
        $table->setDescription(__('This report shows all students who left the school during the current academic year.'));
    }

    $table->addRowCountColumn($students->getPageFrom());
    $table->addColumn('student', __('Student'))
        ->sortable(['pupilsightPerson.surname', 'pupilsightPerson.preferredName'])
        ->format(function ($student) {
            return Format::name('', $student['preferredName'], $student['surname'], 'Student', true, true) 
                 . '<br/><small><i>'.Format::userStatusInfo($student).'</i></small>';
        });
    $table->addColumn('rollGroup', __('Roll Group'));
    $table->addColumn('username', __('Username'));
    $table->addColumn('dateEnd', __('End Date'))->format(function ($student) {
        return Format::date($student['dateEnd']).'<br/>'.Format::small($student['departureReason']);
    });
    $table->addColumn('nextSchool', __('Next School'));

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
