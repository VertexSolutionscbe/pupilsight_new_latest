<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Forms\Form;
use Pupilsight\Forms\DatabaseFormFactory;
use Pupilsight\Module\Attendance\AttendanceView;
use Pupilsight\Domain\Attendance\AttendanceLogPersonGateway;
use Pupilsight\Services\Format;

use Pupilsight\Domain\Helper\HelperGateway;

use Pupilsight\Tables\DataTable;

//Module includes
require_once __DIR__ . '/moduleFunctions.php';
require_once __DIR__ . '/src/AttendanceView.php';

// set page breadcrumb
$page->breadcrumbs->add(__('Take Attendance by Person'));

if (isActionAccessible($guid, $connection2, '/modules/Attendance/attendance_take_byPerson.php') == false) {
    //Acess denied
    echo "<div class='alert alert-danger'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    //Proceed!
    if (isset($_GET['return'])) {
        returnProcess($guid, $_GET['return'], null, array('error3' => __('Your request failed because the specified date is in the future, or is not a school day.')));
    }


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
        //  $sqlp = 'SELECT pupilsightProgramID, name FROM pupilsightProgram ';
        $sqlp = 'SELECT p.pupilsightProgramID, p.name FROM pupilsightProgram AS p RIGHT JOIN attn_settings AS a ON(p.pupilsightProgramID =a.pupilsightProgramID) ';
        $resultp = $connection2->query($sqlp);
        $rowdataprog = $resultp->fetchAll();

        foreach ($rowdataprog as $dt) {
            $program2[$dt['pupilsightProgramID']] = $dt['name'];
        }
        $program = $program1 + $program2;
    }

    $check_role = 'SELECT role.name FROM pupilsightPerson as p LEFT JOIN pupilsightRole as role ON p.pupilsightRoleIDAll = role.pupilsightRoleID 
    WHERE p.pupilsightPersonID ="' . $_SESSION[$guid]['pupilsightPersonID'] . '" AND role.name="Administrator"';
    $check_role = $connection2->query($check_role);
    $role = $check_role->fetch();
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
        $pupilsightPersonID =  $_GET['pupilsightPersonID'];
        $searchbyPost =  '';
        $search =  $_GET['search'];
        $stuId = $_GET['studentId'];
        $classes =  $HelperGateway->getClassByProgram_Attconfig($connection2, $pupilsightProgramID);
        $sections =  $HelperGateway->getSectionByProgram_attConfig($connection2, $pupilsightYearGroupID,  $pupilsightProgramID, $pupilsightSchoolYearID);
    } else {

        $pupilsightProgramID =  '';
        $pupilsightYearGroupID =  '';
        $pupilsightRollGroupID =  '';
        $pupilsightPersonID = '';
        $searchbyPost =  '';
        $search = '';
        $stuId = '0';
        $classes = array('');
        $sections = array('');
    }
    $sqls = 'SELECT a.*, b.officialName FROM  pupilsightStudentEnrolment AS a LEFT JOIN pupilsightPerson AS b ON a.pupilsightPersonID = b.pupilsightPersonID WHERE a.pupilsightSchoolYearID = "' . $pupilsightSchoolYearID . '" AND a.pupilsightProgramID = "' . $pupilsightProgramID . '" AND a.pupilsightYearGroupID = "' . $pupilsightYearGroupID . '" AND a.pupilsightRollGroupID = "' . $pupilsightRollGroupID . '" AND pupilsightRoleIDPrimary=003 GROUP BY b.pupilsightPersonID';
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
    //print_r($pupilsightPersonID);
    //$pupilsightPersonID = '0000003068';
    $sqlp = 'SELECT a.session_no, a.session_name FROM attn_session_settings AS a LEFT JOIN attn_settings AS b ON(a.attn_settings_id =b.id) WHERE b.pupilsightProgramID="' . $pupilsightProgramID . '" AND  FIND_IN_SET("' . $pupilsightYearGroupID . '",b.pupilsightYearGroupID) > 0';
    $resultp = $connection2->query($sqlp);
    $rowdatasession = $resultp->fetchAll();

    $session = array();
    $session2 = array();
    $session1 = array('' => 'Select Session');
    if (!empty($rowdatasession)) {
        $i = 1;
        $firstsession = '';
        foreach ($rowdatasession as $dt) {
            if ($i == 1) {
                $firstsession = $dt['session_no'];
            }
            $session2[$dt['session_no']] = $dt['session_name'];
            $i++;
        }
        $session = $session1 + $session2;
    }



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

    $form->addHiddenValue('q', '/modules/' . $_SESSION[$guid]['module'] . '/attendance_take_byPerson.php');

    $row = $form->addRow();
    $col = $row->addColumn()->setClass('newdes noEdit');
    $col->addLabel('pupilsightSchoolYearID', __('Academic Year'));
    $col->addSelect('pupilsightSchoolYearID')->fromArray($academic)->selected($pupilsightSchoolYearIDpost)->required();

    $col = $row->addColumn()->setClass('newdes');
    $col->addLabel('pupilsightProgramID', __('Program'));
    $col->addSelect('pupilsightProgramID')->fromArray($program)->selected($pupilsightProgramID)->required()->placeholder();



    $col = $row->addColumn()->setClass('newdes');
    $col->addLabel('pupilsightYearGroupID', __('Class'));
    $col->addSelect('pupilsightYearGroupID')->setId("pupilsightYearGroupIDA")->fromArray($classes)->selected($pupilsightYearGroupID)->required()->addClass("load_configSession");
    $col->addTextField('pupilsightPersonID')->setId('staff_id')->addClass('nodisply')->setValue($staff_person_id);

    $col = $row->addColumn()->setClass('newdes');
    $col->addLabel('pupilsightRollGroupID', __('Section'));
    $col->addSelect('pupilsightRollGroupID')->required()->fromArray($sections)->setId($section_id)->selected($pupilsightRollGroupID)->placeholder()->addClass('pupilsightRollGroupIDP');

    // $row->addSelectStudent('pupilsightPersonID', $_SESSION[$guid]['pupilsightSchoolYearID'])->required()->selected($pupilsightPersonID)->placeholder();

    $col = $row->addColumn()->setClass('newdes');
    $col->addLabel('pupilsightPersonID', __('Students'));
    $col->addSelect('pupilsightPersonID')->fromArray($student)->selected($pupilsightPersonID)->required();

    $col = $row->addColumn()->setClass('newdes');
    $col->addLabel('currentDate', __('Date'))->addClass('dte');
    $col->addDate('currentDate')->required()->setValue(dateConvertBack($guid, $currentDate));
    $col = $row->addColumn()->setClass('newdes');
    $col->addLabel('session', __('Session'));
    $col->addSelect('session')->fromArray($session)->required()->selected($session1);


    $row = $form->addRow();
    $row->addSearchSubmit($pupilsight->session);

    echo $form->getOutput();





    if ($pupilsightPersonID != '') {
        if ($currentDate > $today) {
            echo "<div class='alert alert-danger'>";
            echo __('The specified date is in the future: it must be today or earlier.');
            echo '</div>';
        } else {
            if (isSchoolOpen($guid, $currentDate, $connection2) == false) {
                echo "<div class='alert alert-danger'>";
                echo __('School is closed on the specified date, and so attendance information cannot be recorded.');
                echo '</div>';
            } else {

                //check auto lock
                $sqlt_autolock = "SELECT *FROM attn_settings WHERE pupilsightProgramID='" . $pupilsightProgramID . "' AND auto_lock_attendance='1' AND  FIND_IN_SET('" . $pupilsightYearGroupID . "',pupilsightYearGroupID) > 0";
                $autolockStatus = $connection2->query($sqlt_autolock);
                $auto_lock = $autolockStatus->fetch();
                if (!empty($auto_lock)) {
                    echo "<div class='alert alert-danger'>";
                    echo __('This Class Attendance Locked by admin. Please Contact admin');
                    echo '</div>';
                    return;
                }
                //ends auto lock
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

                $countClassAsSchool = getSettingByScope($connection2, 'Attendance', 'countClassAsSchool');

                //Get last 5 school days from currentDate within the last 100
                $timestamp = dateConvertToTimestamp($currentDate);

                // Get school-wide attendance logs
                $attendanceLogGateway = $container->get(AttendanceLogPersonGateway::class);
                $criteria = $attendanceLogGateway->newQueryCriteria()
                    ->sortBy('timestampTaken')
                    ->filterBy('notClass', $countClassAsSchool == 'N')
                    ->pageSize(0);

                $logs = $attendanceLogGateway->queryByPersonAndDateNew($criteria, $pupilsightPersonID, $currentDate);
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
                $table = DataTable::create('attendanceLogs');

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
                $table->addColumn('direction', __('Session'))
                    ->format(function ($log) use ($guid) {
                        return $log['session_name'];
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
                if (isActionAccessible($guid, $connection2, '/modules/Attendance/attendance_take_byPerson_edit.php')) {
                    $table->addActionColumn()
                        ->addParam('pupilsightAttendanceLogPersonID')
                        ->addParam('pupilsightPersonID', $pupilsightPersonID)
                        ->addParam('currentDate', $currentDate)
                        ->format(function ($log, $actions) {
                            if (empty($log['pupilsightAttendanceLogPersonID'])) return;

                            $actions->addAction('edit', __('Edit'))
                                ->setURL('/modules/Attendance/attendance_take_byPerson_edit.php');
                            if (!empty($role['name'])) return;
                            $actions->addAction('delete', __('Delete'))
                                ->setURL('/modules/Attendance/attendance_take_byPerson_delete.php');
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

                // FORM: Take Attendance by Person
                $form = Form::create('attendanceByPerson', $_SESSION[$guid]['absoluteURL'] . '/modules/' . $_SESSION[$guid]['module'] . '/attendance_take_byPersonProcess.php?pupilsightPersonID=' . $pupilsightPersonID);
                $form->setAutocomplete('off');

                if ($currentDate < $today) {
                    $form->addConfirmation(__('The selected date for attendance is in the past. Are you sure you want to continue?'));
                }
                $form->addHiddenValue('address', $_SESSION[$guid]['address']);
                $form->addHiddenValue('currentDate', $currentDate);
                $form->addHiddenValue('session1', $session1);
                /*$form->addHiddenValue('pupilsightProgramID', $pupilsightProgramID);*/


                $form->addRow()->addHeading(__('Take Attendance'));

                $row = $form->addRow();
                $row->addLabel('summary', __('Recent Attendance Summary'));
                $row->addContent($attendance->renderMiniHistory($pupilsightPersonID, 'Person', null, 'floatRight'));

                $row = $form->addRow();
                $row->addLabel('type', __('Type'));
                $row->addSelect('type')->fromArray(array_keys($attendance->getAttendanceTypes()))->selected($lastLog['type'] ?? '');

                $row = $form->addRow();
                $row->addLabel('reason', __('Reason'));
                $row->addSelect('reason')->fromArray($attendance->getAttendanceReasons())->selected($lastLog['reason'] ?? '');

                $row = $form->addRow();
                $row->addLabel('comment', __('Comment'))->description(__('255 character limit'));
                $row->addTextArea('comment')->setRows(3)->maxLength(255)->setValue($lastLog['comment'] ?? '');
                $sql1 = 'SELECT a.session_no, a.session_name FROM attn_session_settings AS a LEFT JOIN attn_settings AS b ON(a.attn_settings_id =b.id) WHERE b.pupilsightProgramID="' . $pupilsightProgramID . '" AND a.session_no!="' . $session1 . '" AND  FIND_IN_SET("' . $pupilsightYearGroupID . '",b.pupilsightYearGroupID) > 0';
                $sen = $connection2->query($sql1);
                $copy_this_too = $sen->fetchAll();
                if (!empty($copy_this_too)) {
                    $row = $form->addRow();
                    $row->addLabel('copy_this_too', __('Copy this too'));
                    $opt = "";
                    foreach ($copy_this_too as $val) {
                        $opt .= '<label><input type="checkbox" name="capy_to[]" value="' . $val['session_no'] . '"> ' . $val['session_name'] . ' </label> &nbsp;&nbsp;';
                    }
                    $row->addContent($opt);
                }

                $row = $form->addRow();
                $row->addFooter();
                $row->addSubmit();

                echo $form->getOutput();
            }
        }
    }
}
?>
<style>
    .noEdit {
        pointer-events: none;
    }
</style>

<script type="text/javascript">
    $(document).on('change', '#pupilsightProgramID', function() {
        var val = $(this).val();
        var type = "attendanceConfigCls";
        if (val != "") {
            $.ajax({
                url: 'ajax_data.php',
                type: 'post',
                data: {
                    val: val,
                    type: type
                },
                async: true,
                success: function(response) {
                    $("#pupilsightYearGroupIDA").html();
                    $("#pupilsightYearGroupIDA").html(response);

                }
            })
        }
    });

    $(document).on('change', '#pupilsightYearGroupIDA', function() {
        var id = $(this).val();
        var pid = $('#pupilsightProgramID').val();
        var type = 'getSection';
        $.ajax({
            url: 'ajax_data.php',
            type: 'post',
            data: {
                val: id,
                type: type,
                pid: pid
            },
            async: true,
            success: function(response) {
                $("#pupilsightRollGroupID").html();
                $("#pupilsightRollGroupID").html(response);
            }
        })
    });

    $(document).on('change', '.pupilsightRollGroupIDP', function() {
        var id = $("#pupilsightRollGroupID").val();
        var yid = $('#pupilsightSchoolYearID').val();
        var pid = $('#pupilsightProgramID').val();
        var cid = $('#pupilsightYearGroupIDA').val();
        var type = 'getStudent';
        $.ajax({
            url: 'ajax_data.php',
            type: 'post',
            data: {
                val: id,
                type: type,
                yid: yid,
                pid: pid,
                cid: cid
            },
            async: true,
            success: function(response) {
                $("#pupilsightPersonID").html();
                $("#pupilsightPersonID").html(response);
            }
        });
    });

    $(document).on('change', '.load_configSession', function() {
        var id = $('#pupilsightProgramID').val();
        var pupilsightYearGroupID = $('#pupilsightYearGroupIDA').val();
        var type = 'getsessionConfigured';
        $.ajax({
            url: 'ajax_data.php',
            type: 'post',
            data: {
                val: id,
                pupilsightYearGroupID: pupilsightYearGroupID,
                type: type
            },
            async: true,
            success: function(response) {
                $("#session").html();
                $("#session").html(response);
            }
        });
    });
</script>