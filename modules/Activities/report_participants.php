<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\View\View;
use Pupilsight\Forms\Form;
use Pupilsight\Services\Format;
use Pupilsight\Domain\User\FamilyGateway;
use Pupilsight\Forms\DatabaseFormFactory;
use Pupilsight\Tables\Prefab\ReportTable;
use Pupilsight\Domain\Activities\ActivityReportGateway;

//Module includes
require_once __DIR__ . '/moduleFunctions.php';

if (isActionAccessible($guid, $connection2, '/modules/Activities/report_participants.php') == false) {
    //Acess denied
    echo "<div class='alert alert-danger'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    //Proceed!
    $pupilsightActivityID = isset($_GET['pupilsightActivityID'])? $_GET['pupilsightActivityID'] : null;
    $viewMode = isset($_REQUEST['format']) ? $_REQUEST['format'] : '';

    if (empty($viewMode)) {
        $page->breadcrumbs->add(__('Participants by Activity'));

        $form = Form::create('filter', $_SESSION[$guid]['absoluteURL'].'/index.php','get');

        $form->setTitle(__('Choose Activity'));
        $form->setFactory(DatabaseFormFactory::create($pdo));
        $form->setClass('noIntBorder fullWidth');

        $form->addHiddenValue('q', "/modules/".$_SESSION[$guid]['module']."/report_participants.php");

        $data = array('pupilsightSchoolYearID' => $_SESSION[$guid]['pupilsightSchoolYearID']);
        $sql = "SELECT pupilsightActivityID AS value, name FROM pupilsightActivity WHERE pupilsightSchoolYearID=:pupilsightSchoolYearID AND active='Y' ORDER BY name, programStart";
        $row = $form->addRow();
            $row->addLabel('pupilsightActivityID', __('Activity'));
            $row->addSelect('pupilsightActivityID')->fromQuery($pdo, $sql, $data)->selected($pupilsightActivityID)->required()->placeholder();

        $row = $form->addRow();
            $row->addFooter();
            $row->addSearchSubmit($pupilsight->session);

        echo $form->getOutput();
    }

    if (empty($pupilsightActivityID)) return;

    $activityGateway = $container->get(ActivityReportGateway::class);
    $familyGateway = $container->get(FamilyGateway::class);

    // CRITERIA
    $criteria = $activityGateway->newQueryCriteria()
        ->searchBy($activityGateway->getSearchableColumns(), $_GET['search'] ?? '')
        ->sortBy(['surname', 'preferredName'])
        ->pageSize(!empty($viewMode) ? 0 : 50)
        ->fromPOST();

    $participants = $activityGateway->queryParticipantsByActivity($criteria, $pupilsightActivityID);

    // Join a set of family adults per student
    $people = $participants->getColumn('pupilsightPersonID');
    $familyAdults = $familyGateway->selectFamilyAdultsByStudent($people)->fetchGrouped();
    $participants->joinColumn('pupilsightPersonID', 'familyAdults', $familyAdults);

    // DATA TABLE
    $table = ReportTable::createPaginated('participants', $criteria)->setViewMode($viewMode, $pupilsight->session);

    $table->setTitle(__('Participants by Activity'));

    $table->addColumn('rollGroup', __('Roll Group'))->width('10%');
    $table->addColumn('student', __('Student'))
        ->sortable(['surname', 'preferredName'])
        ->format(function ($student) use ($guid) {
            $name = Format::name('', $student['preferredName'], $student['surname'], 'Student', true);
            return Format::link($_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/Students/student_view_details.php&pupilsightPersonID='.$student['pupilsightPersonID'].'&subpage=Activities', $name);
        });
    $table->addColumn('status', __('Status'))->translatable();

    $view = new View($container->get('twig'));

    $table->addColumn('contacts', __('Parental Contacts'))
        ->width('30%')
        ->notSortable()
        ->format(function ($student) use ($view) {
            return $view->fetchFromTemplate(
                'formats/familyContacts.twig.html',
                ['familyAdults' => $student['familyAdults']]
            );
        });

    echo $table->render($participants);
}
