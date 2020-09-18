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

if (isActionAccessible($guid, $connection2, '/modules/Activities/report_activitySpread_rollGroup.php') == false) {
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
        $page->breadcrumbs->add(__('Activity Spread by Roll Group'));

        echo '<h2>';
        echo __('Choose Roll Group');
        echo '</h2>';

        $form = Form::create('action', $_SESSION[$guid]['absoluteURL'].'/index.php','get');

        $form->setFactory(DatabaseFormFactory::create($pdo));
        $form->setClass('noIntBorder fullWidth');

        $form->addHiddenValue('q', "/modules/".$_SESSION[$guid]['module']."/report_activitySpread_rollGroup.php");

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

    // Join a set of activity counts per student
    $rollGroups->transform(function(&$student) use ($activityGateway, $dateType, $status) {
        $activityCounts = $activityGateway->selectActivitySpreadByStudent($student['pupilsightSchoolYearID'], $student['pupilsightPersonID'], $dateType, $status);
        $student['activities'] = $activityCounts->fetchGroupedUnique();
    });

    // DATA TABLE
    $table = ReportTable::createPaginated('activitySpread_rollGroup', $criteria)->setViewMode($viewMode, $pupilsight->session);

    $table->setTitle(__('Activity Spread by Roll Group'));

    $table->addColumn('rollGroup', __('Roll Group'))->width('10%');
    $table->addColumn('student', __('Student'))
        ->sortable(['surname', 'preferredName'])
        ->format(function ($student) use ($guid) {
            $name = Format::name('', $student['preferredName'], $student['surname'], 'Student', true);
            return Format::link($_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/Students/student_view_details.php&pupilsightPersonID='.$student['pupilsightPersonID'].'&subpage=Activities', $name);
        });

    // Build a reusable formatter for activity counts
    $displayActivityCount = function($student, $key) {
        $count = isset($student['activities'][$key])? $student['activities'][$key]['count'] : 0;
        $title = ($count > 0) ? $student['activities'][$key]['activityNames'] : __('There are no records to display.');
        $extra = ($count > 0 && $student['activities'][$key]['notAccepted'] > 0) ? "<span style='color: #cc0000' title='".__('Some activities not accepted.')."'> *</span>" : '';

        return '<span title="'.$title.'">'.$count.$extra.'</span>';
    };

    if ($dateType == 'Term') {
        // Group the activity spread by term & weekday
        $terms = $activityGateway->selectActivityWeekdaysPerTerm($_SESSION[$guid]['pupilsightSchoolYearID'])->fetchGrouped();
        foreach ($terms as $termName => $days) {
            $termColumn = $table->addColumn($termName, $termName);
            foreach ($days as $day) {
                $termColumn->addColumn($day['nameShort'], $day['nameShort'])
                    ->notSortable()
                    ->format(function($student) use ($displayActivityCount, $day) {
                        $key = $day['pupilsightSchoolYearTermID'].'-'.$day['pupilsightDaysOfWeekID'];
                        return $displayActivityCount($student, $key);
                    });
            }
        }
    } else {
        // Group the activity spread by weekday only
        $days = $activityGateway->selectActivityWeekdays($_SESSION[$guid]['pupilsightSchoolYearID'])->fetchAll();
        foreach ($days as $day) {
            $table->addColumn($day['nameShort'], $day['nameShort'])
                ->notSortable()
                ->format(function($student) use ($displayActivityCount, $day) {
                    $key = $day['pupilsightDaysOfWeekID'];
                    return $displayActivityCount($student, $key);
                });
        }
    }

    echo $table->render($rollGroups);
}
