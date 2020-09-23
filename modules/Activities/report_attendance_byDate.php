<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Forms\Form;
use Pupilsight\Services\Format;
use Pupilsight\Tables\Prefab\ReportTable;
use Pupilsight\Domain\Activities\ActivityReportGateway;

//Module includes
require_once __DIR__ . '/moduleFunctions.php';

if (isActionAccessible($guid, $connection2, '/modules/Activities/report_attendance_byDate.php') == false) {
    //Acess denied
    echo "<div class='alert alert-danger'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    //Proceed!
    $today = date('Y-m-d');
    $date = (isset($_GET['date']))? dateConvert($guid, $_GET['date']) : date('Y-m-d');
    $sort = (isset($_GET['sort']))? $_GET['sort'] : 'surname';
    $viewMode = isset($_REQUEST['format']) ? $_REQUEST['format'] : '';

    if (empty($viewMode)) {
        $page->breadcrumbs->add(__('Activity Attendance by Date'));

        if (isset($_GET['return'])) {
            returnProcess($guid, $_GET['return'], null, null);
        }

        // Options & Filters
        $form = Form::create('filter', $_SESSION[$guid]['absoluteURL'].'/index.php', 'get');

        $form->setTitle(__('Choose Date'));
        $form->setClass('noIntBorder fullWidth');

        $form->addHiddenValue('q', '/modules/'.$_SESSION[$guid]['module'].'/report_attendance_byDate.php');
        $form->addHiddenValue('address', $_SESSION[$guid]['address']);

        $row = $form->addRow();
            $row->addLabel('date', __('Date'))->description($_SESSION[$guid]['i18n']['dateFormat'])->prepend(__('Format:'));
            $row->addDate('date')->setValue(dateConvertBack($guid, $date))->required();

        $sortOptions = array('absent' => __('Absent'), 'surname' => __('Surname'), 'preferredName' => __('Given Name'), 'rollGroup' => __('Roll Group'));
        $row = $form->addRow();
            $row->addLabel('sort', __('Sort By'));
            $row->addSelect('sort')->fromArray($sortOptions)->selected($sort);

        $row = $form->addRow();
            $row->addFooter();
            $row->addSearchSubmit($pupilsight->session);

        echo $form->getOutput();
    }

    // Cancel out early if we have no date
    if (empty($date)) return;

    if ($date > $today) {
        echo "<div class='alert alert-danger'>" ;
        echo __('The specified date is in the future: it must be today or earlier.');
        echo "</div>" ;
        return;
    } else if (isSchoolOpen($guid, $date, $connection2)==FALSE) {
        echo "<div class='alert alert-danger'>" ;
        echo __('School is closed on the specified date, and so attendance information cannot be recorded.') ;
        echo "</div>" ;
        return;
    }

    //Turn $date into UNIX timestamp and extract day of week
    $dayOfWeek = date('l', dateConvertToTimestamp($date));
    $dateType = getSettingByScope($connection2, 'Activities', 'dateType');

    $activityGateway = $container->get(ActivityReportGateway::class);

    switch ($sort) {
        case 'surname':         $defaultSort = ['pupilsightPerson.surname', 'pupilsightPerson.preferredName']; break;
        case 'preferredName':   $defaultSort = ['pupilsightPerson.preferredName', 'pupilsightPerson.surname']; break;
        case 'rollGroup':       $defaultSort = ['rollGroup', 'pupilsightPerson.surname', 'pupilsightPerson.preferredName']; break;
        case 'absent':
        default:                $defaultSort = ['attendance', 'pupilsightPerson.surname', 'pupilsightPerson.preferredName']; break;
    }

    // CRITERIA
    $criteria = $activityGateway->newQueryCriteria()
        ->searchBy($activityGateway->getSearchableColumns(), isset($_GET['search'])? $_GET['search'] : '')
        ->sortBy($defaultSort)
        ->pageSize(!empty($viewMode) ? 0 : 50)
        ->fromPOST();

    $activityAttendance = $activityGateway->queryActivityAttendanceByDate($criteria, $_SESSION[$guid]['pupilsightSchoolYearID'], $dateType, $date);

    // DATA TABLE
    $table = ReportTable::createPaginated('attendance_byDate', $criteria)->setViewMode($viewMode, $pupilsight->session);

    $table->setTitle(__('Activity Attendance by Date'));

    $table->modifyRows(function($student, $row) {
        if ($student['attendance'] == 'Absent') $row->addClass('error');
        return $row;
    });

    $table->addMetaData('post', ['date' => $date]);

    $table->addColumn('rollGroup', __('Roll Group'))->width('10%');
    $table->addColumn('student', __('Student'))
        ->sortable(['pupilsightPerson.surname', 'pupilsightPerson.preferredName'])
        ->format(Format::using('name', ['', 'preferredName', 'surname', 'Student', true]));
    $table->addColumn('attendance', __('Attendance'));
    $table->addColumn('activity', __('Activity'));
    $table->addColumn('provider', __('Provider'))
        ->format(function($activity) use ($guid){
            return ($activity['provider'] == 'School')? $_SESSION[$guid]['organisationNameShort'] : __('External');
        });

    echo $table->render($activityAttendance);
}
