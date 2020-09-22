<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Forms\Form;
use Pupilsight\Forms\DatabaseFormFactory;
use Pupilsight\Services\Format;
use Pupilsight\Tables\Prefab\ReportTable;
use Pupilsight\Domain\Activities\ActivityReportGateway;
use Pupilsight\Domain\Students\StudentGateway;

//Module includes
require_once __DIR__ . '/moduleFunctions.php';

if (isActionAccessible($guid, $connection2, '/modules/Activities/report_activityType_rollGroup.php') == false) {
    //Acess denied
    echo "<div class='alert alert-danger'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    //Proceed!
    $pupilsightRollGroupID = isset($_GET['pupilsightRollGroupID'])? $_GET['pupilsightRollGroupID'] : null;
    $status = isset($_GET['status'])? $_GET['status'] : null;
    $dateType = getSettingByScope($connection2, 'Activities', 'dateType');

    $viewMode = isset($_REQUEST['format']) ? $_REQUEST['format'] : '';

    if (empty($viewMode)) {
        $page->breadcrumbs->add(__('Activity Type by Roll Group'));

        $form = Form::create('filter', $_SESSION[$guid]['absoluteURL'].'/index.php','get');

        $form->setTitle(__('Choose Roll Group'));
        $form->setFactory(DatabaseFormFactory::create($pdo));
        $form->setClass('noIntBorder fullWidth');

        $form->addHiddenValue('q', "/modules/".$_SESSION[$guid]['module']."/report_activityType_rollGroup.php");

        $row = $form->addRow();
            $row->addLabel('pupilsightRollGroupID', __('Roll Group'));
            $row->addSelectRollGroup('pupilsightRollGroupID', $_SESSION[$guid]['pupilsightSchoolYearID'])->selected($pupilsightRollGroupID)->required();

        $row = $form->addRow();
            $row->addLabel('status', __('Status'));
            $row->addSelect('status')->fromArray(array('Accepted' => __('Accepted'), 'Registered' => __('Registered')))->selected($status)->required();

        $row = $form->addRow();
            $row->addFooter();
            $row->addSearchSubmit($pupilsight->session);

        echo $form->getOutput();
    }

    if (empty($pupilsightRollGroupID)) return;

    $activityGateway = $container->get(ActivityReportGateway::class);
    $studentGateway = $container->get(StudentGateway::class);

    // CRITERIA
    $criteria = $activityGateway->newQueryCriteria()
        ->searchBy($activityGateway->getSearchableColumns(), isset($_GET['search'])? $_GET['search'] : '')
        ->sortBy(['surname', 'preferredName'])
        ->pageSize(!empty($viewMode) ? 0 : 50)
        ->fromPOST();

    $rollGroups = $studentGateway->queryStudentEnrolmentByRollGroup($criteria, $pupilsightRollGroupID);

    // Build a set of activity counts for each student
    $rollGroups->transform(function(&$student) use ($activityGateway,  $status) {
        $activities = $activityGateway->selectActivitiesByStudent($student['pupilsightSchoolYearID'], $student['pupilsightPersonID'], $status)->fetchAll();
        $student['total'] = count($activities);
        $student['activities'] = array();

        foreach ($activities as $activity) {
            $type = !empty($activity['type'])? $activity['type'] : 'noType';
            $student[$type] = isset($student[$type])? $student[$type] + 1 : 1;
            $student['activities'][] = $activity['name'];
        }
    });

    $activityTypeSetting = getSettingByScope($connection2, 'Activities', 'activityTypes');
    $activityTypes = array_map('trim', explode(',', $activityTypeSetting));

    // DATA TABLE
    $table = ReportTable::createPaginated('activityType_rollGroup', $criteria)->setViewMode($viewMode, $pupilsight->session);

    $table->setTitle(__('Activity Type by Roll Group'));

    $table->addColumn('rollGroup', __('Roll Group'))->width('10%');
    $table->addColumn('student', __('Student'))
        ->width('25%')
        ->sortable(['surname', 'preferredName'])
        ->format(function ($student) use ($guid) {
            $title = implode('<br>', $student['activities']);
            $name = Format::name('', $student['preferredName'], $student['surname'], 'Student', true);
            $url = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/Students/student_view_details.php&pupilsightPersonID='.$student['pupilsightPersonID'].'&subpage=Activities';

            return Format::link($url, $name, $title);
        });

    $table->addColumn('noType', __('No Type'))->notSortable()->width('10%');

    foreach ($activityTypes as $type) {
        $table->addColumn($type, __($type))->notSortable()->width('10%');
    }

    $table->addColumn('total', __('Total'))->notSortable()->width('10%');

    echo $table->render($rollGroups);
}
