<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Forms\Form;
use Pupilsight\Domain\DataSet;
use Pupilsight\Services\Format;
use Pupilsight\Forms\DatabaseFormFactory;
use Pupilsight\Tables\Prefab\ReportTable;
use Pupilsight\Domain\Students\StudentReportGateway;

//Module includes
require_once __DIR__ . '/moduleFunctions.php';

if (isActionAccessible($guid, $connection2, '/modules/Students/report_rollGroupSummary.php') == false) {
    //Acess denied
    echo "<div class='alert alert-danger'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    //Proceed!
    $viewMode = $_REQUEST['format'] ?? '';
    $pupilsightSchoolYearID = $pupilsight->session->get('pupilsightSchoolYearID');
    $today = time();
    $dateFrom = $_GET['dateFrom'] ?? '';
    $dateTo = $_GET['dateTo'] ?? '';

    if (empty($viewMode)) {
        $page->breadcrumbs->add(__('Roll Group Summary'));

        echo '<h2>';
        echo __('Choose Options');
        echo '</h2>';

        echo '<p>';
        echo __('By default this report counts all students who are enroled in the current academic year and whose status is currently set to full. However, if dates are set, only those students who have start and end dates outside of the specified dates, or have no start and end dates, will be shown (irrespective of their status).');
        echo '</p>';

        if (empty($dateFrom) && !empty($dateTo)) {
            $dateFrom = date($_SESSION[$guid]['i18n']['dateFormatPHP']);
        }
        if (empty($dateTo) && !empty($dateFrom)) {
            if (dateConvertToTimestamp(dateConvert($guid, $dateFrom))>$today) {
                $dateTo = $dateFrom;
            }
            else {
                $dateTo = date($_SESSION[$guid]['i18n']['dateFormatPHP']);
            }
        }

        $form = Form::create('filter', $_SESSION[$guid]['absoluteURL'].'/index.php', 'get');

        $form->setFactory(DatabaseFormFactory::create($pdo));
        $form->setClass('noIntBorder fullWidth');

        $form->addHiddenValue('q', "/modules/".$_SESSION[$guid]['module']."/report_rollGroupSummary.php");

        $row = $form->addRow();
            $row->addLabel('dateFrom', __('From Date'))->description(__('Start date must be before this date.'))->append('<br/>')->append(__('Format:').' ')->append($_SESSION[$guid]['i18n']['dateFormat']);
            $row->addDate('dateFrom')->setValue($dateFrom);

        $row = $form->addRow();
            $row->addLabel('dateTo', __('To Date'))->description(__('End date must be after this date.'))->append('<br/>')->append(__('Format:').' ')->append($_SESSION[$guid]['i18n']['dateFormat']);
            $row->addDate('dateTo')->setValue($dateTo);


        $row = $form->addRow();
            $row->addFooter();
            $row->addSearchSubmit($pupilsight->session);

        echo $form->getOutput();
    }

    $reportGateway = $container->get(StudentReportGateway::class);

    // CRITERIA
    $criteria = $reportGateway->newQueryCriteria()
        ->sortBy(['pupilsightYearGroup.sequenceNumber', 'pupilsightRollGroup.nameShort'])
        ->filterBy('from', Format::dateConvert($dateFrom))
        ->filterBy('to', Format::dateConvert($dateTo))
        ->pageSize(0)
        ->fromPOST();

    $rollGroups = $reportGateway->queryStudentCountByRollGroup($criteria, $pupilsightSchoolYearID);

    // DATA TABLE
    $table = ReportTable::createPaginated('rollGroupSummary', $criteria)->setViewMode($viewMode, $pupilsight->session);
    $table->setTitle(__('Roll Group Summary'));

    $table->modifyRows(function ($rollGroup, $row) {
        if ($rollGroup['rollGroup'] == __('All Roll Groups')) $row->addClass('dull');
        return $row;
    });

    $table->addColumn('rollGroup', __('Roll Group'));
    $table->addColumn('meanAge', __('Mean Age'));
    $table->addColumn('totalMale', __('Male'));
    $table->addColumn('totalFemale', __('Female'));
    $table->addColumn('total', __('Total'));

    $rollGroupsData = $rollGroups->toArray();
    $rollGroupsData[] = [
        'rollGroup'   => __('All Roll Groups'),
        'meanAge'     => number_format(array_sum(array_column($rollGroupsData, 'meanAge')) / count($rollGroupsData), 1),
        'totalMale'   => array_sum(array_column($rollGroupsData, 'totalMale')),
        'totalFemale' => array_sum(array_column($rollGroupsData, 'totalFemale')),
        'total'       => array_sum(array_column($rollGroupsData, 'total')),
    ];

    echo $table->render(new DataSet($rollGroupsData));
}
