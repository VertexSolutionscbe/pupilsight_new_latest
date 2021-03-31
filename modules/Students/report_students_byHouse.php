<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Forms\Form;
use Pupilsight\Domain\DataSet;
use Pupilsight\Services\Format;
use Pupilsight\Tables\Prefab\ReportTable;
use Pupilsight\Domain\School\HouseGateway;

if (isActionAccessible($guid, $connection2, '/modules/Students/report_students_byHouse.php') == false) {
    //Acess denied
    echo "<div class='alert alert-danger'>";
    echo __('You do not have access to this action.');
    echo "</div>";
} else {
    //Proceed!
    $viewMode = $_REQUEST['format'] ?? '';
    $pupilsightSchoolYearID = $pupilsight->session->get('pupilsightSchoolYearID');
    $pupilsightYearGroupIDList = explode(',', $_GET['pupilsightYearGroupIDList'] ?? '');

    if (empty($viewMode)) {
        $page->breadcrumbs->add(__('Students by House'));
    }

    $houseGateway = $container->get(HouseGateway::class);
    $criteria = $houseGateway->newQueryCriteria()
        ->sortBy(['pupilsightYearGroup.sequenceNumber'])
        ->sortBy(['pupilsightHouse.name'])
        ->pageSize(0)
        ->fromPOST();

    $houseCounts = $houseGateway->queryStudentHouseCountByYearGroup($criteria, $pupilsightSchoolYearID);
    $houses = [];

    // Group each year group result by house, and total up houses as we go
    $yearGroupCounts = array_reduce($houseCounts->toArray(), function ($group, $item) use (&$houses) {
        $yearGroup = $item['pupilsightYearGroupID'];
        $house = $item['pupilsightHouseID'];

        $group[$yearGroup]['yearGroupName'] = $item['yearGroupName'];
        $group[$yearGroup][$house] = [
            'totalFemale' => $item['totalFemale'],
            'totalMale'   => $item['totalMale'],
            'total'       => $item['total'],
        ];
        $houses[$house] = [
            'houseName' => $item['house'],
            'totalFemale' => ($houses[$house]['totalFemale'] ?? 0) + $item['totalFemale'],
            'totalMale'   => ($houses[$house]['totalMale'] ?? 0) + $item['totalMale'],
            'total'       => ($houses[$house]['total'] ?? 0) + $item['total'],
        ];
        return $group;
    }, []);

    // Add the bottom row with a total count
    $yearGroupCounts[] = $houses + ['yearGroupName' => __('Total')];

    // DATA TABLE
    $table = ReportTable::createPaginated('studentsByHouse', $criteria)->setViewMode($viewMode, $pupilsight->session);
    $table->setTitle(__('Students by House'));
    $table->modifyRows(function ($house, $row) {
        if ($house['yearGroupName'] == __('Total')) $row->addClass('dull');
        return $row;
    });

    if (isset($_GET['count'])) {
        $table->setDescription(sprintf(__('%1$s students have been assigned to houses. These results include all student counts by house, updated year groups are highlighted in green. Hover over a number to see the balance by gender.'), $_GET['count']));
    }

    $table->addColumn('yearGroupName', __('Year Group'))
        ->sortable(['pupilsightYearGroup.sequenceNumber'])
        ->width('20%');

    foreach ($houses as $pupilsightHouseID => $house) {
        $table->addColumn($pupilsightHouseID, $house['houseName'])
            ->notSortable()
            ->format(function ($houses) use ($pupilsightHouseID) {
                $house = $houses[$pupilsightHouseID] ?? null;
                if (is_null($house)) return '0';

                $output = '<span title="' . $house['totalFemale'] . ' ' . __('Female') . '<br/>' . $house['totalMale'] . ' ' . __('Male') . '">';
                $output .= $house['total'];
                $output .= '</span>';
                return $output;
            });
    }

    echo $table->render(new DataSet($yearGroupCounts));
}
