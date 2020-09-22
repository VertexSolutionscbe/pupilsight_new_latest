<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Forms\Form;
use Pupilsight\Services\Format;
use Pupilsight\Tables\DataTable;
use Pupilsight\Domain\Timetable\TimetableGateway;
use Pupilsight\Domain\Timetable\TimetableDayGateway;

//Module includes
require_once __DIR__ . '/moduleFunctions.php';

if (isActionAccessible($guid, $connection2, '/modules/Timetable/tt_master.php') == false) {
    //Acess denied
    echo "<div class='alert alert-danger'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    $page->breadcrumbs->add(__('View Master Timetable'));

    echo '<h2>';
    echo __('Choose Timetable');
    echo '</h2>';

    $pupilsightTTID = null;
    if (isset($_GET['pupilsightTTID'])) {
        $pupilsightTTID = $_GET['pupilsightTTID'];
    }
    if ($pupilsightTTID == null) { //If TT not set, get the first timetable in the current year, and display that
        try {
            $dataSelect = array();
            $sqlSelect = "SELECT pupilsightTTID FROM pupilsightTT JOIN pupilsightSchoolYear ON (pupilsightTT.pupilsightSchoolYearID=pupilsightSchoolYear.pupilsightSchoolYearID) WHERE pupilsightSchoolYear.status='Current' ORDER BY pupilsightTT.name LIMIT 0, 1";
            $resultSelect = $connection2->prepare($sqlSelect);
            $resultSelect->execute($dataSelect);
        } catch (PDOException $e) {
        }
        if ($resultSelect->rowCount() == 1) {
            $rowSelect = $resultSelect->fetch();
            $pupilsightTTID = $rowSelect['pupilsightTTID'];
            
        }
    }
   

    $form = Form::create('ttMaster', $_SESSION[$guid]['absoluteURL'].'/index.php', 'get');

    $form->setClass('noIntBorder fullWidth');

    $form->addHiddenValue('q', '/modules/'.$_SESSION[$guid]['module'].'/tt_master.php');

    $sql = "SELECT pupilsightSchoolYear.name as groupedBy, pupilsightTTID as value, pupilsightTT.name AS name FROM pupilsightTT JOIN pupilsightSchoolYear ON (pupilsightTT.pupilsightSchoolYearID=pupilsightSchoolYear.pupilsightSchoolYearID) ORDER BY pupilsightSchoolYear.sequenceNumber, pupilsightTT.name";
    $result = $pdo->executeQuery(array(), $sql);

    // Transform into an option list grouped by Year
    $ttList = ($result && $result->rowCount() > 0)? $result->fetchAll() : array();
    $ttList = array_reduce($ttList, function($list, $item) {
        $list[$item['groupedBy']][$item['value']] = $item['name'];
        return $list;
    }, array());

    $row = $form->addRow();
        $row->addLabel('pupilsightTTID', __('Timetable'));
        $row->addSelect('pupilsightTTID')->fromArray($ttList)->required()->selected($pupilsightTTID);

    $row = $form->addRow();
        $row->addSearchSubmit($pupilsight->session);


    echo $form->getOutput();

    if ($pupilsightTTID != '') {

        $timetableGateway = $container->get(TimetableGateway::class);
        $timetableDayGateway = $container->get(TimetableDayGateway::class);
        
        $values = $timetableGateway->getTTByID($pupilsightTTID);
        $ttDays = $timetableDayGateway->selectTTDaysByID($pupilsightTTID)->fetchAll();

        if (empty($values) || empty($ttDays)) {
            echo "<div class='alert alert-danger'>";
            echo __('There are no records to display.');
            echo '</div>';
        } else {
            foreach ($ttDays as $ttDay) {
                echo '<h2 style="margin-top: 40px">';
                echo __($ttDay['name']);
                echo '</h2>';

                $ttDayRows = $timetableDayGateway->selectTTDayRowsByID($ttDay['pupilsightTTDayID'])->fetchAll();
                
                foreach ($ttDayRows as $ttDayRow) {
                    echo '<h5 style="margin-top: 25px">';
                    echo __($ttDayRow['name']).'<span style=\'font-weight: normal\'> ('.Format::timeRange($ttDayRow['timeStart'], $ttDayRow['timeEnd']).')</span>';
                    echo '</h5>';

                    $ttDayRowClasses = $timetableDayGateway->selectTTDayRowClassesByID($ttDay['pupilsightTTDayID'], $ttDayRow['pupilsightTTColumnRowID']);

                    if ($ttDayRowClasses->isEmpty()) {
                        echo '<div class="alert alert-warning">';
                        echo __('There are no classes associated with this period on this day.');
                        echo '</div>';
                    } else {
                        $table = DataTable::create('timetableDayRowClasses');
                        $table->modifyRows(function ($data, $row) {
                            return $row->addClass('compactRow');
                        });
                        $table->addColumn('class', __('Class'))->format(Format::using('courseClassName', ['courseName', 'className']));
                        $table->addColumn('location', __('Location'));
                        $table->addColumn('teachers', __('Teachers'))->format(function($class) use ($timetableDayGateway) {
                            $teachers = $timetableDayGateway->selectTTDayRowClassTeachersByID($class['pupilsightTTDayRowClassID'])->fetchAll();
                            return Format::nameList($teachers, 'Staff', false, true);
                        });
                        echo $table->render($ttDayRowClasses->toDataSet());
                    }
                }
            }
        }
    }
}

