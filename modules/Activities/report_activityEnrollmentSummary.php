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

if (isActionAccessible($guid, $connection2, '/modules/Activities/report_activityEnrollmentSummary.php') == false) {
    //Acess denied
    echo "<div class='alert alert-danger'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    //Proceed!
    $viewMode = isset($_REQUEST['format']) ? $_REQUEST['format'] : '';

    if (empty($viewMode)) {
        $page->breadcrumbs->add(__('Activity Enrolment Summary'));
    }

    $activityGateway = $container->get(ActivityReportGateway::class);

    // CRITERIA
    $criteria = $activityGateway->newQueryCriteria()
        ->searchBy($activityGateway->getSearchableColumns(), isset($_GET['search'])? $_GET['search'] : '')
        ->sortBy('pupilsightActivity.name')
        ->pageSize(!empty($viewMode) ? 0 : 50)
        ->fromPOST();

    $activities = $activityGateway->queryActivityEnrollmentSummary($criteria, $_SESSION[$guid]['pupilsightSchoolYearID']);

    // DATA TABLE
    $table = ReportTable::createPaginated('activityEnrollmentSummary', $criteria)->setViewMode($viewMode, $pupilsight->session);

    $table->setTitle(__('Activity Enrolment Summary'));

    $table->modifyRows(function($activity, $row) {
        if ($activity['enrolment'] == $activity['maxParticipants'] && $activity['maxParticipants'] > 0) {
            $row->addClass('current');
        } else if ($activity['enrolment'] > $activity['maxParticipants']) {
            $row->addClass('error');
        } else if ($activity['maxParticipants'] == 0) {
            $row->addClass('warning');
        }
        return $row;
    });
    
    $table->addMetaData('filterOptions', [
        'active:Y'          => __('Active').': '.__('Yes'),
        'active:N'          => __('Active').': '.__('No'),
        'registration:Y'    => __('Registration').': '.__('Yes'),
        'registration:N'    => __('Registration').': '.__('No'),
        'enrolment:less'    => __('Enrolment').': &lt; '.__('Full'),
        'enrolment:full'    => __('Enrolment').': '.__('Full'),
        'enrolment:greater' => __('Enrolment').': &gt; '.__('Full'),
    ]);

    $table->addColumn('name', __('Activity'))
        ->format(function($activity) {
            return $activity['name'].'<br/><span class="small emphasis">'.$activity['type'].'</span>';
        });
    $table->addColumn('enrolment', __('Accepted'))->width('20%');
    $table->addColumn('registered', __('Registered'))->description(__('Excludes "Not Accepted"'))->width('20%');
    $table->addColumn('maxParticipants', __('Max Participants'))->width('20%');

    echo $table->render($activities);
}
