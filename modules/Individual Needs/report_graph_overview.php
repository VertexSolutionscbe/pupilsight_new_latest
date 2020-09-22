<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Services\Format;
use Pupilsight\UI\Chart\Chart;
use Pupilsight\Tables\Prefab\ReportTable;
use Pupilsight\Domain\IndividualNeeds\INGateway;

if (isActionAccessible($guid, $connection2, '/modules/Individual Needs/report_graph_overview.php') == false) {
    // Access denied
    $page->addError(__('You do not have access to this action.'));
} else {
    // Proceed!
    $viewMode = isset($_REQUEST['format']) ? $_REQUEST['format'] : '';
    $pupilsightYearGroupID = $_GET['pupilsightYearGroupID'] ?? '';

    $onClickURL = $pupilsight->session->get('absoluteURL').'/index.php?q=/modules/Individual Needs/';
    $onClickURL .= !empty($pupilsightYearGroupID)? 'in_summary.php&pupilsightRollGroupID=' : 'report_graph_overview.php&pupilsightYearGroupID=';

    // DATA
    $inGateway = $container->get(INGateway::class);
    $criteria = $inGateway->newQueryCriteria()
        ->sortBy(['pupilsightYearGroup.sequenceNumber', 'pupilsightRollGroup.name'])
        ->pageSize(0)
        ->fromPOST();

    $inCounts = $inGateway->queryINCountsBySchoolYear($criteria, $_SESSION[$guid]['pupilsightSchoolYearID'], $pupilsightYearGroupID);
    $chartData = $inCounts->toArray();

    if (empty($viewMode)) {
        $page->breadcrumbs->add(__('Individual Needs Overview'));
        $page->scripts->add('chart');

        if (!empty($pupilsightYearGroupID)) {
            echo "<div class='linkTop'>";
            echo '<a href="'.$_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/Individual Needs/report_graph_overview.php">'.__('Clear Filters').'</a>';
            echo '</div>';
        }

        // SETUP CHART
        $chart = Chart::create('overview', 'bar');
        $chart->setLabels(array_column($chartData, 'labelName'));
        $chart->setMetaData(array_column($chartData, 'labelID'));
        $chart->setOptions([
            'tooltips' => [
                'mode' => 'label',
            ],
        ]);
        
        $chart->addDataset('total', __('Total Students'))
            ->setData(array_column($chartData, 'studentCount'));

        $chart->addDataset('in', __('Individual Needs'))
            ->setData(array_column($chartData, 'inCount'));

        $chart->onClick('function(event, elements) {
            var index = elements[0]._index;
            var labelID = elements[0]._chart.config.metadata[index];
            window.location = "'.$onClickURL.'" + labelID;
        }');

        // RENDER CHART
        echo $chart->render();
    }

    $table = ReportTable::createPaginated('inOverview', $criteria)->setViewMode($viewMode, $pupilsight->session);
    $table->setTitle(__('Individual Needs').': '.($pupilsightYearGroupID ? __('Roll Group') : __('Year Groups')));

    $table->addColumn('labelName', $pupilsightYearGroupID ? __('Roll Group') : __('Year Groups'))
        ->sortable(['pupilsightYearGroup.sequenceNumber', 'pupilsightRollGroup.name'])
        ->format(function ($inData) use ($onClickURL) {
            return Format::link($onClickURL.$inData['labelID'], $inData['labelName']);
        });

    $table->addColumn('studentCount', __('Total Students'));
    $table->addColumn('inCount', __('Individual Needs'));

    echo $table->render($inCounts);
}
