<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\View\View;
use Pupilsight\Services\Format;
use Pupilsight\Domain\User\FamilyGateway;
use Pupilsight\Tables\Prefab\ReportTable;
use Pupilsight\Domain\Students\StudentReportGateway;


//Module includes
require_once __DIR__ . '/moduleFunctions.php';

if (isActionAccessible($guid, $connection2, '/modules/Students/report_transport_student.php') == false) {
    //Acess denied
    echo "<div class='alert alert-danger'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    //Proceed!
    $viewMode = $_REQUEST['format'] ?? '';
    $pupilsightSchoolYearID = $pupilsight->session->get('pupilsightSchoolYearID');

    if (empty($viewMode)) {
        $page->breadcrumbs->add(__('Student Transport'));
    }

    $reportGateway = $container->get(StudentReportGateway::class);
    $familyGateway = $container->get(FamilyGateway::class);

    // CRITERIA
    $criteria = $reportGateway->newQueryCriteria()
        ->sortBy(['pupilsightPerson.transport', 'pupilsightPerson.surname', 'pupilsightPerson.preferredName'])
        ->pageSize(!empty($viewMode) ? 0 : 50)
        ->fromPOST();

    $transport = $reportGateway->queryStudentTransport($criteria, $pupilsightSchoolYearID);

    // Join a set of family data per student
    $people = $transport->getColumn('pupilsightPersonID');
    $familyData = $familyGateway->selectFamiliesByStudent($people)->fetchGrouped();
    $transport->joinColumn('pupilsightPersonID', 'families', $familyData);

    // Join a set of family adults per student
    $familyAdults = $familyGateway->selectFamilyAdultsByStudent($people)->fetchGrouped();
    $transport->joinColumn('pupilsightPersonID', 'familyAdults', $familyAdults);

    // DATA TABLE
    $table = ReportTable::createPaginated('studentTransport', $criteria)->setViewMode($viewMode, $pupilsight->session);
    $table->setTitle(__('Student Transport'));

    $table->addColumn('transport', __('Transport'))
        ->context('primary');
    $table->addColumn('rollGroup', __('Roll Group'))
        ->context('secondary')
        ->width('10%');
    $table->addColumn('student', __('Student'))
        ->context('primary')
        ->sortable(['pupilsightPerson.surname', 'pupilsightPerson.preferredName'])
        ->format(Format::using('name', ['', 'preferredName', 'surname', 'Student', true]));
    
    $view = new View($container->get('twig'));

    $table->addColumn('address1', __('Address'))
        ->width('30%')
        ->notSortable()
        ->format(function ($student) use ($view) {
            return $view->fetchFromTemplate(
                'formats/familyAddresses.twig.html',
                ['families' => $student['families'], 'person' => $student]
            );
        });

    $table->addColumn('contacts', __('Parental Contacts'))
        ->context('secondary')
        ->width('30%')
        ->notSortable()
        ->format(function ($student) use ($view) {
            return $view->fetchFromTemplate(
                'formats/familyContacts.twig.html',
                ['familyAdults' => $student['familyAdults']]
            );
        });

    echo $table->render($transport);
}
