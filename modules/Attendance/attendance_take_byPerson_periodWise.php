<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Forms\Form;
use Pupilsight\Forms\DatabaseFormFactory;
use Pupilsight\Module\Attendance\AttendanceView;
use Pupilsight\Domain\Attendance\AttendanceLogPersonGateway;
use Pupilsight\Services\Format;
use Pupilsight\Domain\Attendance\AttendanceCodeGateway;
use Pupilsight\Tables\DataTable;
use Pupilsight\Domain\Timetable\TimetableGateway;
use Pupilsight\Domain\Timetable\TimetableDayGateway;
use Pupilsight\Domain\Helper\HelperGateway;
/*$session = $container->get('session');
$periodId = $session->get('period_ids');*/
//Module includes
require_once __DIR__ . '/moduleFunctions.php';
require_once __DIR__ . '/src/AttendanceView.php';

// set page breadcrumb
$page->breadcrumbs->add(__('Take Attendance by Person'));

if (isActionAccessible($guid, $connection2, '/modules/Attendance/attendance_take_byPerson_periodWise.php') == false) {
    //Acess denied
    echo "<div class='alert alert-danger'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    //Proceed!
    if (isset($_GET['return'])) {
        returnProcess($guid, $_GET['return'], null, array('error3' => __('Your request failed because the specified date is in the future, or is not a school day.')));
    }

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
            // print_r($pupilsightTTID);

        }
    }





    $timetableGateway = $container->get(TimetableGateway::class);
    $timetableDayGateway = $container->get(TimetableDayGateway::class);

    $values = $timetableGateway->getTTByID($pupilsightTTID);
    $ttDays = $timetableDayGateway->selectTTDaysByID($pupilsightTTID)->fetchAll();
    //print_r($ttDays);die();

    $pupilsightSchoolYearID = '';
    if (isset($_GET['pupilsightSchoolYearID'])) {
        $pupilsightSchoolYearID = $_GET['pupilsightSchoolYearID'];
    }
    if ($pupilsightSchoolYearID == '' or $pupilsightSchoolYearID == $_SESSION[$guid]['pupilsightSchoolYearID']) {
        $pupilsightSchoolYearID = $_SESSION[$guid]['pupilsightSchoolYearID'];
        $pupilsightSchoolYearName = $_SESSION[$guid]['pupilsightSchoolYearName'];
    }

    $HelperGateway = $container->get(HelperGateway::class);

    $pupilsightPersonID_logged =   $_SESSION[$guid]['pupilsightPersonID'];
    $pupilsightRoleIDPrimary = $_SESSION[$guid]['pupilsightRoleIDPrimary'];
    $program = array();
    $program2 = array();
    $program1 = array('' => 'Select Program');
    if ($pupilsightRoleIDPrimary != '001') //for staff login
    {
        $staff_person_id = $pupilsightPersonID_logged;
        $sql1 = "SELECT p.pupilsightProgramID,p.name AS program,a.pupilsightYearGroupID FROM pupilsightProgramClassSectionMapping AS a LEFT JOIN assignstaff_toclasssection b ON(a.pupilsightMappingID =b.pupilsightMappingID) LEFT JOIN pupilsightProgram AS p
    ON(p.pupilsightProgramID =a.pupilsightProgramID) WHERE b.pupilsightPersonID=" . $staff_person_id . "  GROUP By a.pupilsightYearGroupID "; //except Admin //0000002962
        $result1 = $connection2->query($sql1);
        $row1 = $result1->fetchAll();
        /* echo "<pre>";
    print_r($row1);*/

        $progrm_id = "Staff_program";
        $class_id = "Staff_class";
        $section_id = "Staff_section";
        foreach ($row1 as $dt) {
            $program2[$dt['pupilsightProgramID']] = $dt['program'];
        }
        $program = $program1 + $program2;
        $disable_cls = 'dsble_attr';
    } else {
        $staff_person_id = Null;
        $disable_cls = '';
        $progrm_id = "pupilsightProgramID";
        $class_id = "pupilsightYearGroupID";
        $section_id = "pupilsightRollGroupID";
        $sqlp = 'SELECT pupilsightProgramID, name FROM pupilsightProgram ';
        $resultp = $connection2->query($sqlp);
        $rowdataprog = $resultp->fetchAll();

        foreach ($rowdataprog as $dt) {
            $program2[$dt['pupilsightProgramID']] = $dt['name'];
        }
        $program = $program1 + $program2;
    }


    $sqlq = 'SELECT pupilsightSchoolYearID, name FROM pupilsightSchoolYear ';
    $resultval = $connection2->query($sqlq);
    $rowdata = $resultval->fetchAll();
    $academic = array();
    $ayear = '';
    if (!empty($rowdata)) {
        $ayear = $rowdata[0]['name'];
        foreach ($rowdata as $dt) {
            $academic[$dt['pupilsightSchoolYearID']] = $dt['name'];
        }
    }

    $searchby = array('' => 'Search By', 'stu_name' => 'Student Name', 'stu_id' => 'Student Id', 'adm_id' => 'Admission Id', 'father_name' => 'Father Name', 'father_email' => 'Father Email', 'mother_name' => 'Mother Name', 'mother_email' => 'Mother Email');
    // echo '<pre>';
    // print_r($_POST);
    // echo '</pre>';
    //die();

    if (isset($_GET['pupilsightProgramID'])) {
        $pupilsightProgramID =  $_GET['pupilsightProgramID'];
        $pupilsightSchoolYearIDpost = $_GET['pupilsightSchoolYearID'];
        $pupilsightYearGroupID =  $_GET['pupilsightYearGroupID'];
        $pupilsightRollGroupID =  $_GET['pupilsightRollGroupID'];
        $searchbyPost =  '';
        $search =  $_GET['search'];
        $stuId = $_GET['studentId'];
        $classes =  $HelperGateway->getClassByProgram_staff($connection2, $pupilsightProgramID, $staff_person_id);
        $sections =  $HelperGateway->getSectionByProgram_staff($connection2, $pupilsightYearGroupID,  $pupilsightProgramID, $staff_person_id);
    } else {
        $pupilsightProgramID =  '';
        $pupilsightYearGroupID =  '';
        $pupilsightRollGroupID =  '';
        $searchbyPost =  '';
        $search = '';
        $stuId = '0';
        $classes = array('');
        $sections = array('');
    }
    $pupilsightSchoolYearIDpost = $pupilsightSchoolYearID;
    if (isset($_GET['pupilsightProgramID'])) {
        $pupilsightProgramID = $_GET['pupilsightProgramID'];
    }
    if (isset($_GET['pupilsightYearGroupID'])) {
        $pupilsightYearGroupID = $_GET['pupilsightYearGroupID'];
    }
    if (isset($_GET['pupilsightRollGroupID'])) {
        $pupilsightRollGroupID = $_GET['pupilsightRollGroupID'];
    }
    if (isset($_GET['stuId'])) {
        $stuId = $_GET['stuId'];
    }
    if (isset($_GET['pupilsightPersonID'])) {
        $pupilsightPersonID = $_GET['pupilsightPersonID'];
    }
    $sessions = array();
    $sessions = array(
        '' => 'Select Session',
        '1' => 'Morning Session',
        '2' => 'After Noon',
        '3' => 'Both'
    );

    $sqls = 'SELECT pupilsightPersonID, officialName FROM pupilsightPerson  WHERE pupilsightRoleIDPrimary=003';
    $results = $connection2->query($sqls);
    $rowdatastd = $results->fetchAll();
    $student = array();
    $student1 = array('' => 'Select Student');
    $student2 = array();

    if (!empty($rowdatastd)) {

        foreach ($rowdatastd as $st) {
            $student2[$st['pupilsightPersonID']] = $st['officialName'];
        }
    }
    $student = $student1 + $student2;


    $attendance = new AttendanceView($pupilsight, $pdo);

    $today = date('Y-m-d');

    $currentDate = isset($_GET['currentDate']) ? dateConvert($guid, $_GET['currentDate']) :

        $today;
    $session1 = isset($_GET['session']) ? $_GET['session'] : null;
    $pupilsightPersonID = isset($_GET['pupilsightPersonID']) ? $_GET['pupilsightPersonID'] : null;

    $form = Form::create('filter', $_SESSION[$guid]['absoluteURL'] . '/index.php', 'get');
    $form->setFactory(DatabaseFormFactory::create($pdo));
    $form->setClass('noIntBorder fullWidth');
    $form->setTitle(__('Choose Student'));

    $form->addHiddenValue('q', '/modules/' . $_SESSION[$guid]['module'] . '/attendance_take_byPerson_periodWise.php');

    $row = $form->addRow();
    $col = $row->addColumn()->setClass('newdes');
    $col->addLabel('pupilsightProgramID', __('Program'));
    $col->addSelect('pupilsightProgramID')->fromArray($program)->setId($progrm_id)->selected($pupilsightProgramID)->required()->placeholder();

    $col = $row->addColumn()->setClass('newdes');
    $col->addLabel('pupilsightSchoolYearID', __('Academic Year'));
    $col->addSelect('pupilsightSchoolYearID')->required()->fromArray($academic)->selected($pupilsightSchoolYearIDpost);

    $col = $row->addColumn()->setClass('newdes');
    $col->addLabel('pupilsightYearGroupID', __('Class'));
    $col->addSelect('pupilsightYearGroupID')->setId($class_id)->fromArray($classes)->selected($pupilsightYearGroupID)->required();
    $col->addTextField('pupilsightPersonID')->setId('staff_id')->addClass('nodisply')->setValue($staff_person_id);


    $col = $row->addColumn()->setClass('newdes');
    $col->addLabel('pupilsightRollGroupID', __('Section'));
    $col->addSelect('pupilsightRollGroupID')->required()->fromArray($sections)->setId($section_id)->selected($pupilsightRollGroupID)->placeholder();


    // $row->addSelectStudent('pupilsightPersonID', $_SESSION[$guid]['pupilsightSchoolYearID'])->required()->selected($pupilsightPersonID)->placeholder();
    $pupilsightPersonID = isset($_GET['pupilsightPersonID']) ? $_GET['pupilsightPersonID'] : null;
    $col = $row->addColumn()->setClass('newdes');
    $col->addLabel('students', __('Students *'));
    $col->addSelect('pupilsightPersonID', $pupilsightPersonID)->fromArray($student)->selected($pupilsightPersonID)->required();


    $col = $row->addColumn()->setClass('newdes');
    $col->addLabel('date', __('Date *'));
    $col->addDate('currentDate')->setValue(dateConvertBack($guid, $currentDate))->required();

    $row = $form->addRow();
    $row->addSearchSubmit($pupilsight->session);

    echo $form->getOutput();

    if ($pupilsightPersonID != '') {
        if ($currentDate > $today) {
            echo "<div class='alert alert-danger'>";
            echo __('The specified date is in the future: it must be today or earlier.');
            echo '</div>';
        } else {
            // check special day
            $SpecialDays = array('date' => $currentDate);
            $sqlSpecialDays = "SELECT * FROM pupilsightSchoolYearSpecialDay WHERE date=:date";
            $resultSpecialDays = $connection2->prepare($sqlSpecialDays);
            $resultSpecialDays->execute($SpecialDays);
            $specialDaysCounts = $resultSpecialDays->fetch();
            //check special day ends 
            if (isSchoolOpen($guid, $currentDate, $connection2) == false and empty($specialDaysCounts)) {
                echo "<div class='alert alert-danger'>";
                echo __('School is closed on the specified date, and so attendance information cannot be recorded.');
                echo '</div>';
            } else {
                $countClassAsSchool = getSettingByScope($connection2, 'Attendance', 'countClassAsSchool');
                //check marking lock
                $m_sql = "SELECT *FROM attn_settings WHERE pupilsightProgramID='" . $pupilsightProgramID . "' AND lock_attendance_marking='1' AND FIND_IN_SET('" . $pupilsightYearGroupID . "',pupilsightYearGroupID) > 0";
                $marking_Status = $connection2->query($m_sql);
                $marking_lock = $marking_Status->fetch();
                if (!empty($marking_lock)) {
                    if ($marking_lock['fromDate'] <= $currentDate and $marking_lock['toDate'] >= $currentDate) {
                        echo "<div class='alert alert-danger'>";
                        echo __('This Class Attendance Locked by admin (between ' . date('d/m/Y', strtotime($marking_lock['fromDate'])) . ' To ' . date('d/m/Y', strtotime($marking_lock['toDate'])) . ' dates). Please Contact admin');
                        echo '</div>';
                        return;
                    }
                }
                //ends marking lock
                //Get last 5 school days from currentDate within the last 100
                $timestamp = dateConvertToTimestamp($currentDate);

                // Get school-wide attendance logs
                $attendanceLogGateway = $container->get(AttendanceLogPersonGateway::class);
                $criteria = $attendanceLogGateway->newQueryCriteria()
                    ->sortBy('timestampTaken')
                    ->filterBy('notClass', $countClassAsSchool == 'N')
                    ->pageSize(0);

                $logs = $attendanceLogGateway->queryByPersonAndDate($criteria, $pupilsightPersonID, $currentDate);
                $lastLog = $logs->getRow(count($logs) - 1);

                // Get class attendance logs
                $classLogCount = 0;
                if ($countClassAsSchool == 'N') {
                    $criteria = $attendanceLogGateway->newQueryCriteria()
                        ->sortBy(['timeStart', 'timeEnd', 'timestampTaken'])
                        ->pageSize(0);

                    $classLogs = $attendanceLogGateway->queryClassAttendanceByPersonAndDate($criteria, $pupilsight->session->get('pupilsightSchoolYearID'), $pupilsightPersonID, $currentDate);
                    $classLogs->transform(function (&$log) use (&$classLogCount) {
                        if (!empty($log['pupilsightAttendanceLogPersonID'])) $classLogCount++;
                    });
                }

                // DATA TABLE: Show attendance log for the current day
                $table = DataTable::createPaginated('attendanceLogs', $criteria);

                $table->modifyRows(function ($log, $row) {
                    if ($log['scope'] == 'Onsite - Late' || $log['scope'] == 'Offsite - Left') $row->addClass('warning');
                    elseif ($log['direction'] == 'Out') $row->addClass('error');
                    elseif (!empty($log['direction'])) $row->addClass('current');
                    return $row;
                });

                $table->addColumn('period', __('Period'))
                    ->format(function ($log) {
                        if (empty($log['period'])) return Format::small(__('N/A'));
                        return $log['period'] . '<br/>' . Format::small(Format::timeRange($log['timeStart'], $log['timeEnd']));
                    });

                $table->addColumn('time', __('Time'))
                    ->format(function ($log) use ($currentDate) {
                        if (empty($log['timestampTaken'])) return Format::small(__('N/A'));

                        return $currentDate != substr($log['timestampTaken'], 0, 10)
                            ? Format::dateTimeReadable($log['timestampTaken'], '%H:%M, %b %d')
                            : Format::dateTimeReadable($log['timestampTaken'], '%H:%M');
                    });

                $table->addColumn('direction', __('Attendance'))
                    ->format(function ($log) use ($guid) {
                        if (empty($log['direction'])) return Format::small(__('Not Taken'));

                        $output = '<b>' . __($log['direction']) . '</b> (' . __($log['type']) . (!empty($log['reason']) ? ', ' . __($log['reason']) : '') . ')';
                        if (!empty($log['comment'])) {
                            $output .= '&nbsp;<img title="' . $log['comment'] . '" src="./themes/' . $_SESSION[$guid]['pupilsightThemeName'] . '/img/messageWall.png" width=16 height=16/>';
                        }

                        return $output;
                    });

                $table->addColumn('where', __('Where'))
                    ->width('25%')
                    ->format(function ($log) {
                        return ($log['context'] == 'Class' && !empty($log['pupilsightCourseClassID']))
                            ? __($log['context']) . ' (' . Format::courseClassName($log['courseName'], $log['className']) . ')'
                            : __($log['context']);
                    });

                $table->addColumn('timestampTaken', __('Recorded By'))
                    ->width('22%')
                    ->format(Format::using('name', ['title', 'preferredName', 'surname', 'Staff', false, true]));

                // ACTIONS
                if (isActionAccessible($guid, $connection2, '/modules/Attendance/attendance_take_byPerson_periodWise_edit.php')) {
                    $table->addActionColumn()
                        ->addParam('pupilsightAttendanceLogPersonID')
                        ->addParam('pupilsightPersonID', $pupilsightPersonID)
                        ->addParam('currentDate', $currentDate)
                        ->format(function ($log, $actions) {
                            if (empty($log['pupilsightAttendanceLogPersonID'])) return;

                            $actions->addAction('edit', __('Edit'))
                                ->setURL('/modules/Attendance/attendance_take_byPerson_periodWise_edit.php');

                            $actions->addAction('delete', __('Delete'))
                                ->setURL('/modules/Attendance/attendance_take_byPerson_periodWise_delete.php');
                        });
                }

                // School-wide attendance: Roll Group, Person, Future and Self Registration
                $schoolTable = clone $table;
                $schoolTable->setTitle(__('Attendance Log'));
                $schoolTable->setDescription(count($logs) > 0 ? __('The following attendance log has been recorded for the selected student today:') : '');
                $schoolTable->removeColumn('period');

                if (count($logs) + $classLogCount == 0) {
                    $schoolTable->addMetaData('blankSlate', __('There is currently no attendance data today for the selected student.'));
                }

                echo $schoolTable->render($logs);

                // Class Attendance
                if ($countClassAsSchool == 'N') {
                    if ($classLogCount > 0) {
                        $classTable = clone $table;
                        $classTable->setTitle(__('Class Attendance'));

                        echo $classTable->render($classLogs);
                    }
                }
                echo '<br/>';

                $date = $currentDate;

                $nameOfDay = date('l', strtotime($date));
                $setempty = '';
                /*  $form = Form::create('attendanceByPerson', $_SESSION[$guid]['absoluteURL'].'/modules/'.$_SESSION[$guid]['module']. '/attendance_take_byPerson_periodWiseProcess.php?pupilsightPersonID='.$pupilsightPersonID)->setClass('persontimtable');
                $form->setAutocomplete('off');

                if ($currentDate < $today) {
                    $form->addConfirmation(__('The selected date for attendance is in the past. Are you sure you want to continue?'));
                }
                $form->addHiddenValue('address', $_SESSION[$guid]['address']);
                $form->addHiddenValue('currentDate', $currentDate);
                $form->addHiddenValue('session1', $session1);
                $form->addHiddenValue('periodId', $periodId);*/
                $url = $_SESSION[$guid]['absoluteURL'] . '/modules/' . $_SESSION[$guid]['module'] . '/attendance_take_byPerson_periodWiseProcess.php?pupilsightPersonID=' . $pupilsightPersonID;
?>
                <form action="<?php echo  $url; ?>" method="post">
                    <input type="hidden" name="currentDate" value="<?php echo $currentDate; ?>">
                    <input type="hidden" name="address" value="<?php echo $_SESSION[$guid]['address']; ?>">
                    <input type="hidden" name="session1" value="<?php echo $session1; ?>">
                    <?php
                    $types = $attendance->getAttendanceTypes();
                    $reasons = $attendance->getAttendanceReasons();
                    foreach ($ttDays as $ttDay) {
                        // print_r(ucwords(trim(strtolower($ttDay['name']), ' ')));
                        if (ucwords(trim(strtolower($ttDay['name']), ' ')) == $nameOfDay) {
                            $setempty = $nameOfDay;
                            echo "<table class='timetable ' style='width:100% !important' border='1'>  <tr>               
                <td>Time</td>            
                <td>" . ucfirst(strtolower($ttDay['name'])) . "</td>
                <td>Subject</td>
                <td>Attendance</td>              
                <td>Reason</td>              
                <td>Remark</td>              
                </tr>";
                            $ttDayRows = $timetableDayGateway->selectTTDayRowsByIDNew($ttDay['pupilsightTTDayID'])->fetchAll();
                            foreach ($ttDayRows as $ttDayRow) {

                                $sqls1 = 'SELECT *  FROM `pupilsightAttendanceLogPerson` WHERE `pupilsightPersonID` = "' . $pupilsightPersonID . '" AND periodID="' . $ttDayRow['pupilsightTTColumnRowID'] . '" AND   date ="' . $date . '"';
                                $results1 = $connection2->query($sqls1);
                                $rowCheck = $results1->fetch();

                                //get subject 
                                $sql_sub = 'SELECT  d.name
                    FROM pupilsightTTDayRowClass as tb
                    LEFT JOIN pupilsightDepartment as d
                    ON tb.pupilsightDepartmentID = d.pupilsightDepartmentID WHERE tb.pupilsightTTColumnRowID="' . $ttDayRow['pupilsightTTColumnRowID'] . '"';
                                $sub_res = $connection2->query($sql_sub);
                                $subjectCheck = $sub_res->fetch();
                                $subject = "";
                                if ($subjectCheck['name']) {
                                    $subject = $subjectCheck['name'];
                                }
                                //ends subject
                    ?>
                                <input type="hidden" name="pd_id[]" value="<?php echo $ttDayRow['pupilsightTTColumnRowID']; ?>">
                                <?php
                                echo "<tr>";
                                echo "<td>";
                                echo '<span style=\'font-weight: normal\'> (' . Format::timeRange($ttDayRow['timeStart'], $ttDayRow['timeEnd']) . ')</span>';
                                echo "</td>";
                                echo "<td>";
                                echo __($ttDayRow['name']);
                                echo "</td>";
                                echo "<td>" . $subject . "</td>";
                                echo "<td>
                <select name='type[]' class='w-full'>";
                                foreach ($types as $val) {
                                ?>
                                    <option value="<?php echo $val['name']; ?>" <?php if (!empty($rowCheck)) {
                                                                                    if ($val['name'] == $rowCheck['type']) {
                                                                                        echo "selected";
                                                                                    }
                                                                                } ?>><?php echo $val['name']; ?></option>
                                <?php
                                }
                                echo "</select></td>";
                                echo "<td>
                <select name='reasons[]' class='w-full'>";
                                foreach ($reasons as $val) {
                                ?>
                                    <option value="<?php echo $val; ?>" <?php if (!empty($rowCheck)) {
                                                                            if ($val == $rowCheck['reason']) {
                                                                                echo "selected";
                                                                            }
                                                                        } ?>><?php echo $val; ?></option>
    <?php
                                }
                                echo "</select></td>";
                                echo '<td><input type="text"  name="comment[]" value="' . $rowCheck['comment'] . '" class="w-full" maxlength="255"></td>';
                                echo '</tr>';
                            }
                            echo "<tr>
                <td colspan='5'>
                <input type='submit' style='float: right;' id='btnStn' name='submit' value='Submit' class='btn btn-primary '>
                </td>
                </td>";
                            echo "</table>";
                        }
                    }
                    echo "</form>";
                }
            }
        }
    }
    ?>