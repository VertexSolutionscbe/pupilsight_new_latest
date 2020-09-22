<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Forms\Form;
use Pupilsight\Tables\DataTable;
use Pupilsight\Domain\Timetable\CourseSyncGateway;

//Module includes
require_once __DIR__ . '/moduleFunctions.php';

if (isActionAccessible($guid, $connection2, '/modules/Timetable Admin/courseEnrolment_sync.php') == false) {
    //Acess denied
    echo "<div class='alert alert-danger'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    $page->breadcrumbs->add(__('Sync Course Enrolment'));

    if (isset($_GET['return'])) {
        returnProcess($guid, $_GET['return'], null, null);
    }

    $pupilsightSchoolYearID = isset($_GET['pupilsightSchoolYearID'])? $_GET['pupilsightSchoolYearID'] : $_SESSION[$guid]['pupilsightSchoolYearID'];

    if ($pupilsightSchoolYearID == $_SESSION[$guid]['pupilsightSchoolYearID']) {
        $pupilsightSchoolYearName = $_SESSION[$guid]['pupilsightSchoolYearName'];
    } else {
        $data = array('pupilsightSchoolYearID' => $pupilsightSchoolYearID);
        $sql = "SELECT name FROM pupilsightSchoolYear WHERE pupilsightSchoolYearID=:pupilsightSchoolYearID";
        $pupilsightSchoolYearName = $pdo->selectOne($sql, $data);
    }

    echo '<h2>';
    echo $pupilsightSchoolYearName;
    echo '</h2>';

    echo "<div class='linkTop'>";
        //Print year picker
        $previousYear = getPreviousSchoolYearID($pupilsightSchoolYearID, $connection2);
        $nextYear = getNextSchoolYearID($pupilsightSchoolYearID, $connection2);
        if ($previousYear != false) {
            echo "<a href='".$_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.$_SESSION[$guid]['module'].'/courseEnrolment_sync.php&pupilsightSchoolYearID='.getPreviousSchoolYearID($pupilsightSchoolYearID, $connection2)."'>".__('Previous Year').'</a> ';
        } else {
            echo __('Previous Year').' ';
        }
        echo ' | ';
        if ($nextYear != false) {
            echo "<a href='".$_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.$_SESSION[$guid]['module'].'/courseEnrolment_sync.php&pupilsightSchoolYearID='.getNextSchoolYearID($pupilsightSchoolYearID, $connection2)."'>".__('Next Year').'</a> ';
        } else {
            echo __('Next Year').' ';
        }
    echo '</div>';

    $form = Form::create('settings', $_SESSION[$guid]['absoluteURL'].'/modules/Timetable Admin/courseEnrolment_sync_settingsProcess.php');
    $form->setTitle(__('Settings'));
    $form->addHiddenValue('address', $_SESSION[$guid]['address']);

    $setting = getSettingByScope($connection2, 'Timetable Admin', 'autoEnrolCourses', true);
    $row = $form->addRow();
        $row->addLabel($setting['name'], __($setting['nameDisplay']))->description(__($setting['description']));
        $row->addYesNo($setting['name'])->selected($setting['value'])->required();

    $row = $form->addRow();
        $row->addSubmit();

    echo $form->getOutput();

    $syncGateway = $container->get(CourseSyncGateway::class);

    // QUERY
    $criteria = $syncGateway->newQueryCriteria()
        ->sortBy(['pupilsightYearGroup.sequenceNumber'])
        ->fromArray($_POST);

    $classMaps = $syncGateway->queryCourseClassMaps($criteria, $pupilsightSchoolYearID);
    $classMapsAllYearGroups = implode(',', $classMaps->getColumn('pupilsightYearGroupID'));

    $table = DataTable::createPaginated('sync', $criteria);

    $table->setTitle(__('Map Classes'));
    $table->setDescription(__('Syncing enrolment lets you enrol students into courses by mapping them to a Roll Group and Year Group within the school. If auto-enrol is turned on, new students accepted through the application form and student enrolment process will be enroled in courses automatically.'));

    $table->addHeaderAction('add', __('Add'))
        ->setURL('/modules/Timetable Admin/courseEnrolment_sync_add.php')
        ->addParam('pupilsightSchoolYearID', $pupilsightSchoolYearID)
        ->displayLabel()
        ->append('&nbsp;|&nbsp;');

    $table->addHeaderAction('sync', __('Sync All'))
        ->setURL('/modules/Timetable Admin/courseEnrolment_sync_run.php')
        ->addParam('pupilsightSchoolYearID', $pupilsightSchoolYearID)
        ->addParam('pupilsightYearGroupIDList', $classMapsAllYearGroups)
        ->setIcon('refresh')
        ->displayLabel();

    $table->addColumn('yearGroupName', __('Year Group'))->sortable(['pupilsightYearGroup.sequenceNumber']);
    $table->addColumn('rollGroupList', __('Roll Groups'));
    $table->addColumn('classCount', __('Classes'));

    // ACTIONS
    $table->addActionColumn()
        ->addParam('pupilsightSchoolYearID', $pupilsightSchoolYearID)
        ->addParam('pupilsightYearGroupID')
        ->format(function ($row, $actions) {
            $actions->addAction('edit', __('Edit'))
                ->setURL('/modules/Timetable Admin/courseEnrolment_sync_edit.php');

            $actions->addAction('delete', __('Delete'))
                ->setURL('/modules/Timetable Admin/courseEnrolment_sync_delete.php');

            $actions->addAction('sync', __('Sync Now'))
                ->setIcon('refresh')
                ->addParam('pupilsightYearGroupIDList', $row['pupilsightYearGroupID'])
                ->setURL('/modules/Timetable Admin/courseEnrolment_sync_run.php');
        });

    echo $table->render($classMaps);
}
