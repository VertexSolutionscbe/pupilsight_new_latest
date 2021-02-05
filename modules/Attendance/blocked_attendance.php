<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Forms\Form;
use Pupilsight\Forms\DatabaseFormFactory;
use Pupilsight\Tables\DataTable;
use Pupilsight\Module\Attendance\AttendanceView;
use Pupilsight\Domain\Attendance\AttendanceCodeGateway;
use Pupilsight\Services\Format;
use Pupilsight\Domain\Helper\HelperGateway;

use Pupilsight\Domain\Attendance\AttendanceLogPersonGateway;

// use Pupilsight\Domain\Staff\StaffGateway;

if (isActionAccessible($guid, $connection2, '/modules/Attendance/blocked_attendance.php') == false) {
    //Acess denied
    echo "<div class='alert alert-danger'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    $HelperGateway = $container->get(HelperGateway::class);
    //Get action with highest precendence
    $highestAction = getHighestGroupedAction($guid, $_GET['q'], $connection2);
    if ($highestAction == false) {
        echo "<div class='alert alert-danger'>";
        echo __('The highest grouped action cannot be determined.');
        echo '</div>';
    } else {
        $pupilsightSchoolYearID = '';
        $pupilsightProgramID  = $pupilsightYearGroupID =$pupilsightRollGroupID =$sdate=$edate='';

        if (isset($_GET['pupilsightSchoolYearID'])) {
            $pupilsightSchoolYearID = $_GET['pupilsightSchoolYearID'];
        }

        // echo '<pre>';
        // print_r($_POST);
        if ($_POST) {
            $pupilsightProgramID =  isset($_POST['pupilsightProgramID']) ? $_POST['pupilsightProgramID'] : '';
            $pupilsightYearGroupID = isset($_POST['pupilsightYearGroupID']) ? $_POST['pupilsightYearGroupID'] : '';
            $pupilsightRollGroupID = isset($_POST['pupilsightRollGroupID']) ? $_POST['pupilsightRollGroupID'] : '';

            $stuId = isset($_GET['studentId']) ? $_GET['studentId'] : '';

            $today = date('Y-m-d');
            $sdate = isset($_POST['sdate']) ? dateConvert($guid, $_POST['sdate']) : $today;
            $edate = isset($_POST['edate']) ? dateConvert($guid, $_POST['edate']) : $today;


            
            
            // Extra code to check date  
            
            $URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/Attendance'.getModuleName($_POST['address'])."/blocked_attendance.php";
            $dateStart = dateConvert($guid, $sdate);
            
            $dateEnd = $dateStart;
            $dateEnd = dateConvert($guid, $edate);
            
            $today = date('Y-m-d');

            //Check to see if date is in the future and is a school day.
            if ($dateStart == '' or ($dateEnd != '' and $dateEnd < $dateStart)  ) {
                $URL .= '&return=error8';
                header("Location: {$URL}");
            }
            
            //End Extra code to check date
            $classes =  $HelperGateway->getClassByProgram($connection2, $pupilsightProgramID);
            
            if($pupilsightYearGroupID!='' && $pupilsightProgramID!='')
            $sections =  $HelperGateway->getMultipleSectionByProgram($connection2, $pupilsightYearGroupID,  $pupilsightProgramID);

        } else {
            $classes = array('' => 'Select Class');
            $sections = array('' => 'Select Section');
            $pupilsightProgramID =  '';
            $pupilsightYearGroupID =  '';
            $pupilsightRollGroupID =  '';

            $stuId = '0';
        }

        $sql = 'SELECT a.*, b.name FROM pupilsightProgramClassSectionMapping AS a LEFT JOIN pupilsightYearGroup AS b ON a.pupilsightYearGroupID = b.pupilsightYearGroupID WHERE a.pupilsightProgramID = "' . $pupilsightProgramID . '" GROUP BY a.pupilsightYearGroupID';
        $result = $connection2->query($sql);
        $classesdata = $result->fetchAll();
        $classes = array();
        foreach ($classesdata as $ke => $cl) {
            $classes[$cl['pupilsightYearGroupID']] = $cl['name'];
        }

        $setclass = array();
        if (!empty($showName)) {
            $setclass = $showName;
        }


        $sqlp = 'SELECT pupilsightProgramID, name FROM pupilsightProgram ';
        $resultp = $connection2->query($sqlp);
        $rowdataprog = $resultp->fetchAll();

        $program = array();
        $program2 = array();
        $program1 = array('' => 'Select Program');
        foreach ($rowdataprog as $dt) {
            $program2[$dt['pupilsightProgramID']] = $dt['name'];
        }
        $program = $program1 + $program2;

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
        $page->breadcrumbs->add(__('Blocked Attendance'));
    }

    if (isset($_GET['return'])) {

        returnProcess(
            $guid,
            $_GET['return'],
            null,
            array('error8' => __('Your request failed because the start date should be less than or equal to end date'),)
        );
    }

    $searchform = Form::create('program', $_SESSION[$guid]['absoluteURL'] . '/index.php?q=/modules/' . $_SESSION[$guid]['module'] . '/blocked_attendance.php')->addClass('newform');
    $searchform->setFactory(DatabaseFormFactory::create($pdo));

    $searchform->setClass('noIntBorder fullWidth');

    $searchform->addHiddenValue('q', '/modules/' . $_SESSION[$guid]['module'] . '/blocked_attendance.php');
    $searchform->addHiddenValue('studentId', '0');
    $row = $searchform->addRow();

    $col = $row->addColumn()->setClass('newdes');
    $col->addLabel('pupilsightProgramID', __('Program'));
    $col->addSelect('pupilsightProgramID')->setId('getMultiClassByProg')->selected($pupilsightProgramID)->fromArray($program);

    $col = $row->addColumn()->setClass('newdes');
    $col->addLabel('pupilsightYearGroupID', __('Class'))->addClass('dte');
    $col->addSelect('pupilsightYearGroupID')->setId('showMultiClassByProg')->fromArray($classes)->selected($pupilsightYearGroupID)->selectMultiple();

    $col = $row->addColumn()->setClass('newdes');
    $col->addLabel('[pupilsightRollGroupID', __('Section'))->addClass('dte');
    $col->addSelect('pupilsightRollGroupID')->setId('showMultiSecByProgCls')->fromArray($sections)->selected($pupilsightRollGroupID)->selectMultiple();

    $col = $row->addColumn()->setClass('newdes');
    $col->addLabel('sdate', __(' From date'))->addClass('dte');
    $col->addDate('sdate')->setId('dueDate')->required()->setValue(dateConvertBack($guid, $sdate));

    $col = $row->addColumn()->setClass('newdes');
    $col->addLabel('edate', __('To date'))->addClass('dte');
    $col->addDate('edate')->required()->setValue(dateConvertBack($guid, $edate));

    $col = $row->addColumn()->setClass('newdes');

    $col->addLabel(' ', __(' '));
    $col->addContent('<button  class=" btn btn-primary">Search</button>');
    echo $searchform->getOutput();
    $AttendanceCodeGateway = $container->get(AttendanceCodeGateway::class);
    $criteria = $AttendanceCodeGateway->newQueryCriteria()
        //->sortBy(['id'])
        ->fromPOST();
    // $blocked = $AttendanceCodeGateway->queryAttendanceCodes($criteria);


    $attendanceLogGateway = $container->get(AttendanceLogPersonGateway::class);
    $criteria = $attendanceLogGateway->newQueryCriteria()
        ->sortBy('timestampTaken')
        ->fromPOST();
    $sdate_val = dateConvert($guid, $sdate);
    $edate_val = dateConvert($guid, $edate);

    if ($_POST) {
        $blocked = $attendanceLogGateway->selectBlockedAttendanceLogs($criteria, $pupilsightYearGroupID, $pupilsightRollGroupID, $sdate_val, $edate_val);
    } else {
        $blocked = $attendanceLogGateway->selectBlockedAttendanceLogsAll($criteria);
    }
    /* echo "<pre>";  
    print_r($blocked );  */
    // $blocked = $Gateway->getblockedAttendance($criteria);
    $table = DataTable::createPaginated('blockedattendanecmanage', $criteria);

    echo "<div style='height:20px; margin-top:20px'><div class='float-right mb-2' style=''><a href='index.php?q=/modules/Attendance/add_blocked_attendance.php' class='btn btn-primary'>Add</a>";
    echo "&nbsp;&nbsp;</div><div  class='float-none'></div></div>";

    // $table->addColumn('student_name', __('Name'));//yearGroup,rollGroup

    $table->addColumn('serial_number', __('Sl No'));
    $table->addColumn('name', __('Name'));
    $table->addColumn('yearGroup', __('Class'));
    $table->addColumn('rollGroup', __('Section'));
    $table->addColumn('type', __('Type'))
        ->format(function ($blocked) {
            if (($blocked['type']) == '1') {
                return "No Attendance";
            } else if (($blocked['type']) == '2') {
                return "Closure";
            } else {
                return "";
            }
        });
    $table->addColumn('start_date', __('From Date'))
        ->format(function ($blocked) {
            return Format::date($blocked['start_date']);
        });
    $table->addColumn('end_date', __('To date'))
        ->format(function ($blocked) {
            return Format::date($blocked['end_date']);
        });

    // $table->addColumn('start_date', __('From Date'));
    // $table->addColumn('end_date', __('To date'));
    $table->addActionColumn()
        ->addParam('pupilsightAttendanceBlockID')
        // ->addParam('pupilsightMappingID')
        ->format(function ($facilities, $actions) use ($guid) {
            $actions->addAction('edit', __('Edit'))
                ->setURL('/modules/Attendance/blocked_attendance_edit.php');

            $actions->addAction('delete', __('Delete'))
                ->setURL('/modules/Attendance/delete_blockled_attendance.php');
        });
    echo $table->render($blocked);
}
?>
<script>
    $(document).ready(function() {
        $('#showMultiClassByProg').selectize({
            plugins: ['remove_button'],
        });
    });

    $(document).ready(function() {
        $('#showMultiSecByProgCls').selectize({
            plugins: ['remove_button'],
        });
    });
</script>