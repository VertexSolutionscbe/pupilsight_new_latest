<?php
/*
Pupilsight, Flexible & Open School System

*/

//Increase max execution time, as this stuff gets big
ini_set('max_execution_time', 600);

//System includes
include '../../pupilsight.php';
include '../../version.php';

//Module includes
include './moduleFunctions.php';

if (isActionAccessible($guid, $connection2, '/modules/Activities/report_attendanceExport.php') == false) {
    //Acess denied
    echo "<div class='alert alert-danger'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    // Create new PHPExcel object
    $excel = new PHPExcel();

    //Create border styles
    $style_head_fill = array('fill' => array('type' => PHPExcel_Style_Fill::FILL_SOLID, 'color' => array('rgb' => 'eeeeee')),'borders' => array('top' => array('style' => PHPExcel_Style_Border::BORDER_THIN, 'color' => array('argb' => '444444')), 'bottom' => array('style' => PHPExcel_Style_Border::BORDER_THIN, 'color' => array('argb' => '444444'))),);

    // Set document properties
    $excel->getProperties()->setCreator(formatName('', $_SESSION[$guid]['preferredName'], $_SESSION[$guid]['surname'], 'Staff'))
         ->setLastModifiedBy(formatName('', $_SESSION[$guid]['preferredName'], $_SESSION[$guid]['surname'], 'Staff'))
         ->setTitle(__('Activity Attendance'))
         ->setDescription(__('This information is confidential. Generated by Pupilsight (http://pupilsight.in).'));

    $pupilsightActivityID = $_GET['pupilsightActivityID'];
    $filename = __('Activity').__('Attendance').'-'.$pupilsightActivityID;

    if (empty($pupilsightActivityID)) { //Seems like things are not configured, so show error
        $excel->setActiveSheetIndex(0)->setCellValue('A1', __('An error has occurred.'));
    } else {

        // Get the activity info
        try {
            $data = array('pupilsightActivityID' => $pupilsightActivityID);
            $sql = 'SELECT pupilsightActivity.name, description, programStart, programEnd, pupilsightSchoolYearTermIDList, pupilsightYearGroupIDList, pupilsightSchoolYear.pupilsightSchoolYearID, pupilsightSchoolYear.name as schoolYearName FROM pupilsightActivity, pupilsightSchoolYear WHERE pupilsightSchoolYear.pupilsightSchoolYearID=pupilsightActivity.pupilsightSchoolYearID AND pupilsightActivityID=:pupilsightActivityID';
            $activityResult = $connection2->prepare($sql);
            $activityResult->execute($data);
        } catch (PDOException $e) {
            echo $e->getMessage();
        }
        $activity = $activityResult->fetch();

        // Get the students
        try {
            $data = array('pupilsightSchoolYearID' => $_SESSION[$guid]['pupilsightSchoolYearID'], 'pupilsightActivityID' => $pupilsightActivityID);
            $sql = "SELECT pupilsightPerson.pupilsightPersonID as pupilsightPersonID, surname, preferredName FROM pupilsightPerson JOIN pupilsightStudentEnrolment ON (pupilsightPerson.pupilsightPersonID=pupilsightStudentEnrolment.pupilsightPersonID) JOIN pupilsightActivityStudent ON (pupilsightActivityStudent.pupilsightPersonID=pupilsightPerson.pupilsightPersonID) WHERE pupilsightPerson.status='Full' AND (dateStart IS NULL OR dateStart<='".date('Y-m-d')."') AND (dateEnd IS NULL  OR dateEnd>='".date('Y-m-d')."') AND pupilsightSchoolYearID=:pupilsightSchoolYearID AND pupilsightActivityStudent.status='Accepted' AND pupilsightActivityID=:pupilsightActivityID ORDER BY pupilsightActivityStudent.status, surname, preferredName";
            $studentResult = $connection2->prepare($sql);
            $studentResult->execute($data);
        } catch (PDOException $e) {
            echo $e->getMessage();
        }
        $students = $studentResult->fetchAll();

        // Get the recorded attendance
        try {
            $data = array('pupilsightActivityID' => $pupilsightActivityID);
            $sql = 'SELECT UNIX_TIMESTAMP(pupilsightActivityAttendance.date) as date, pupilsightActivityAttendance.timestampTaken, pupilsightActivityAttendance.attendance, pupilsightPerson.preferredName, pupilsightPerson.surname FROM pupilsightActivityAttendance, pupilsightPerson WHERE pupilsightActivityAttendance.pupilsightPersonIDTaker=pupilsightPerson.pupilsightPersonID AND pupilsightActivityAttendance.pupilsightActivityID=:pupilsightActivityID ORDER BY DATE ASC';
            $resultAttendance = $connection2->prepare($sql);
            $resultAttendance->execute($data);
        } catch (PDOException $e) {
            echo $e->getMessage();
        }
        $sessions = $resultAttendance->fetchAll();

        // Get the time slots
        try {
            $data = array('pupilsightActivityID' => $pupilsightActivityID);
            $sql = 'SELECT nameShort, timeStart, timeEnd FROM pupilsightActivitySlot JOIN pupilsightDaysOfWeek ON (pupilsightActivitySlot.pupilsightDaysOfWeekID=pupilsightDaysOfWeek.pupilsightDaysOfWeekID) WHERE pupilsightActivityID=:pupilsightActivityID ORDER BY pupilsightDaysOfWeek.pupilsightDaysOfWeekID';
            $resultSlots = $connection2->prepare($sql);
            $resultSlots->execute($data);
        } catch (PDOException $e) {
            echo $e->getMessage();
        }

        // Get the activity staff members
        try {
            $dataStaff = array('pupilsightActivityID' => $pupilsightActivityID);
            $sqlStaff = "SELECT title, preferredName, surname, role FROM pupilsightActivityStaff JOIN pupilsightPerson ON (pupilsightActivityStaff.pupilsightPersonID=pupilsightPerson.pupilsightPersonID) WHERE pupilsightActivityID=:pupilsightActivityID AND pupilsightPerson.status='Full' AND (dateStart IS NULL OR dateStart<='".date('Y-m-d')."') AND (dateEnd IS NULL  OR dateEnd>='".date('Y-m-d')."') ORDER BY surname, preferredName";
            $resultStaff = $connection2->prepare($sqlStaff);
            $resultStaff->execute($dataStaff);
        } catch (PDOException $e) {
            $e->getMessage();
        }

        $columnStart = 1;
        $columnEnd = count($students) + 1;

        $excel->setActiveSheetIndex(0);

        // Sheet defaults
        $excel->getActiveSheet()->getColumnDimension('A')->setAutoSize(true);
        $excel->getActiveSheet()->getDefaultRowDimension()->setRowHeight(20);
        $excel->getActiveSheet()->getDefaultColumnDimension()->setWidth(12);

        // Output the activity name
        $excel->getActiveSheet()->setCellValue('A1', __('Activity'))
                                ->setCellValue('B1', $activity['name']);

        $excel->getActiveSheet()->mergeCells('B1:I1');
        $excel->getActiveSheet()->getRowDimension('1')->setRowHeight(30);
        $excel->getActiveSheet()->getStyle('A1:I1')->getFont()->setSize(18);
        $excel->getActiveSheet()->getStyle('A2:J2')->applyFromArray($style_head_fill);

        // Output some activity details (useful if we're printing this)
        $infoRowLines = 1;

        $slots = array();
        if ($resultSlots->rowCount() > 0) {
            while ($rowSlots = $resultSlots->fetch()) {
                $slots[] = __($rowSlots['nameShort']).': '.substr($rowSlots['timeStart'], 0, 5).' - '.substr($rowSlots['timeEnd'], 0, 5);
            }
        }
        $infoRowLines = max($infoRowLines, count($slots));

        // TIME SLOTS
        $excel->getActiveSheet()->setCellValue('A2', __('Time Slots'))
                                ->setCellValue('A3', implode(",\r\n", $slots));

        $excel->getActiveSheet()->getStyle('A3')->getAlignment()->setWrapText(true);

        // DATE / TERMS
        $dateType = getSettingByScope($connection2, 'Activities', 'dateType');
        if ($dateType != 'Date') {
            $terms = getTerms($connection2, $activity['pupilsightSchoolYearID']);
            $termList = array();
            for ($i = 0; $i < count($terms); $i = $i + 2) {
                if (is_numeric(strpos($activity['pupilsightSchoolYearTermIDList'], $terms[$i]))) {
                    $termList[] = $terms[($i + 1)];
                }
            }

            $excel->getActiveSheet()->setCellValue('B2', __('Terms'));
            $excel->getActiveSheet()->setCellValue('B3', implode(",\r\n", $termList));
            $excel->getActiveSheet()->mergeCells('B3:C3');
            $infoRowLines = max($infoRowLines, count($termList));
        } else {
            $excel->getActiveSheet()->setCellValue('B2', __('Start Date'))
                ->setCellValue('B3', dateConvertBack($guid, $activity['programStart']));

            $excel->getActiveSheet()->setCellValue('C2', __('End Date'))
                ->setCellValue('C3', dateConvertBack($guid, $activity['programEnd']));
        }

        // SCHOOL YEAR
        $excel->getActiveSheet()->setCellValue('D2', __('School Year'))
            ->setCellValue('D3', $activity['schoolYearName']);

        $excel->getActiveSheet()->setCellValue('E2', __('Participants'))
            ->setCellValue('E3', count($students));

        // STAFF
        $staff = array();
        if ($resultStaff->rowCount() > 0) {
            while ($rowStaff = $resultStaff->fetch()) {
                $staff[] = formatName($rowStaff['title'], $rowStaff['preferredName'], $rowStaff['surname'], 'Staff');
            }
        }
        $infoRowLines = max($infoRowLines, count($staff));

        $excel->getActiveSheet()->setCellValue('F2', __('Staff'))
            ->setCellValue('F3', implode(",\r\n", $staff));

        $excel->getActiveSheet()->mergeCells('F3:G3');
        $excel->getActiveSheet()->getStyle('F3:G3')->getAlignment()->setWrapText(true);

        // YEAR GROUPS
        $excel->getActiveSheet()->setCellValue('H2', __('Year Groups'))
            ->setCellValue('H3', strip_tags(getYearGroupsFromIDList($guid, $connection2, $activity['pupilsightYearGroupIDList'])));

        $excel->getActiveSheet()->mergeCells('H3:I3');
        $excel->getActiveSheet()->getStyle('H3:I3')->getAlignment()->setWrapText(true);

        // TOTAL SESSIONS
        $excel->getActiveSheet()->setCellValue('J2', __('Total Sessions'))
            ->setCellValue('J3', count($sessions));

        $excel->getActiveSheet()->getRowDimension('3')->setRowHeight($infoRowLines * 16);

        // Iterate over the sessions and output the column headings, plus setup the attendance data array
        $attendance = array();
        $columnStart += 4;
        for ($i = 0; $i < count($sessions); ++$i) {
            $excel->getActiveSheet()->setCellValue(num2alpha($i + 1).($columnStart),
                date('D', $sessions[$i]['date']));

            $excel->getActiveSheet()->setCellValue(num2alpha($i + 1).($columnStart + 1),
                date($_SESSION[$guid]['i18n']['dateFormatPHP'], $sessions[$i]['date']));

            $excel->getActiveSheet()->getStyle(num2alpha($i + 1).($columnStart + 1))->applyFromArray($style_head_fill);

            // Store the unserialized attendance data in an associative array so student rows can access them based on pupilsightpPersonID
            $sessionAttendance = (!empty($sessions[$i]['attendance'])) ? unserialize($sessions[$i]['attendance']) : array();
            foreach ($sessionAttendance as $studentID => $value) {
                $attendance[$i][$studentID] = $value;
            }
        }

        // Build an empty array of attendance count data for each session
        $attendanceCount = array_fill(0, count($sessions), 0);

        // Setup the column heading for students
        $excel->getActiveSheet()->setCellValue('A'.($columnStart), __('Days'))
            ->setCellValue('A'.($columnStart + 1), __('Student'));
        $excel->getActiveSheet()->getStyle('A'.($columnStart))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
        $excel->getActiveSheet()->getStyle('A'.($columnStart + 1))->applyFromArray($style_head_fill);

        $excel->getActiveSheet()->setCellValue(num2alpha(count($sessions) + 1).($columnStart + 1), __('Attended:'));
        $excel->getActiveSheet()->getStyle(num2alpha(count($sessions) + 1).($columnStart + 1))
            ->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);

        // Iterate over the students and output each row
        $columnStart += 2;
        for ($i = 0; $i < count($students); ++$i) {
            $excel->getActiveSheet()->setCellValue('A'.($i + $columnStart),
                formatName('', $students[$i]['preferredName'], $students[$i]['surname'], 'Student', true));

            $studentID = $students[$i]['pupilsightPersonID'];

            $daysAttended = 0;
            for ($n = 0; $n < count($sessions); ++$n) {
                if (isset($attendance[$n][$studentID]) && !empty($attendance[$n][$studentID])) {
                    $excel->getActiveSheet()->setCellValue(num2alpha($n + 1).($i + $columnStart), '✓');
                    ++$daysAttended;

                    $attendanceCount[$n]++;
                }
            }

            // Add the totals for each student to the last column
            $excel->getActiveSheet()->setCellValue(num2alpha(count($sessions) + 1).($i + $columnStart), $daysAttended);
        }

        // Add the totals and timestamp data to the bottom of each column
        $excel->getActiveSheet()->setCellValue('A'.($columnStart + $columnEnd), __('Total students:'))
            ->setCellValue('A'.($columnStart + $columnEnd + 1), __('Recorded'))
            ->setCellValue('A'.($columnStart + $columnEnd + 2), __('By'));

        $excel->getActiveSheet()->getRowDimension($columnStart + $columnEnd + 1)->setRowHeight(2 * 18);

        for ($i = 0; $i < count($sessions); ++$i) {
            $excel->getActiveSheet()->setCellValue(num2alpha($i + 1).($columnStart + $columnEnd), $attendanceCount[$i]);

            $excel->getActiveSheet()->getStyle(num2alpha($i + 1).($columnStart + 1 + $columnEnd))->getAlignment()->setWrapText(true);

            $excel->getActiveSheet()->setCellValue(num2alpha($i + 1).($columnStart + 1 + $columnEnd),
                substr($sessions[$i]['timestampTaken'], 11)."\r".dateConvertBack($guid, substr($sessions[$i]['timestampTaken'], 0, 10)));

            $excel->getActiveSheet()->setCellValue(num2alpha($i + 1).($columnStart + 2 + $columnEnd),
                formatName('', $sessions[$i]['preferredName'], $sessions[$i]['surname'], 'Staff', false, true));
        }
    }

    //FINALISE THE DOCUMENT SO IT IS READY FOR DOWNLOAD
    // Set active sheet index to the first sheet, so Excel opens this as the first sheet
    $excel->setActiveSheetIndex(0);

    // Redirect output to a client’s web browser (Excel2007)
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment;filename="'.$filename.'.xlsx"');
    header('Cache-Control: max-age=0');
    // If you're serving to IE 9, then the following may be needed
    header('Cache-Control: max-age=1');

    // If you're serving to IE over SSL, then the following may be needed
    header('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
    header('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT'); // always modified
    header('Cache-Control: cache, must-revalidate'); // HTTP/1.1
    header('Pragma: public'); // HTTP/1.0

    $objWriter = PHPExcel_IOFactory::createWriter($excel, 'Excel2007');
    $objWriter->save('php://output');
    exit;
}
