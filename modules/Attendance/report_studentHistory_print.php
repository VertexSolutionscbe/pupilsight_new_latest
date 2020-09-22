<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Tables\DataTable;
use Pupilsight\Domain\User\UserGateway;
use Pupilsight\Module\Attendance\StudentHistoryData;
use Pupilsight\Module\Attendance\StudentHistoryView;

//Module includes
require_once __DIR__ . '/moduleFunctions.php';

if (isActionAccessible($guid, $connection2, '/modules/Attendance/report_studentHistory_print.php') == false) {
    // Access denied
    $page->addError(__('You do not have access to this action.'));
} else {
    $highestAction = getHighestGroupedAction($guid, $_GET['q'], $connection2);
    $pupilsightPersonID = $_GET['pupilsightPersonID'] ?? '';

    if ($highestAction != 'Student History_all' || empty($pupilsightPersonID)) {
        $page->addError(__('The selected record does not exist, or you do not have access to it.'));
        return;
    }

    $student = $container->get(UserGateway::class)->getByID($pupilsightPersonID);

    if (!empty($student)) {
        // ATTENDANCE DATA
        $attendanceData = $container
            ->get(StudentHistoryData::class)
            ->getAttendanceData($_SESSION[$guid]['pupilsightSchoolYearID'], $student['pupilsightPersonID'], $student['dateStart'], $student['dateEnd']);

        // DATA TABLE
        $renderer = $container->get(StudentHistoryView::class);
        $renderer->addData('printView', true);
        
        $table = DataTable::create('studentHistory', $renderer);
        $table->setTitle(__('Attendance History for').' '.formatName('', $student['preferredName'], $student['surname'], 'Student'));
        $table->addHeaderAction('print', __('Print'))
            ->setExternalURL('javascript:window.print()')
            ->setIcon('print')
            ->displayLabel();

        echo $table->render($attendanceData);
    }
}
