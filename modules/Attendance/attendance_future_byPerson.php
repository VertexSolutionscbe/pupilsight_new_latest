<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Forms\Form;
use Pupilsight\Forms\DatabaseFormFactory;
use Pupilsight\Module\Attendance\AttendanceView;

use Pupilsight\Tables\DataTable;

use Pupilsight\Services\Format;
use Pupilsight\Domain\Helper\HelperGateway;

//Module includes
require_once __DIR__ . '/moduleFunctions.php';
require_once __DIR__ . '/src/AttendanceView.php';

// set page breadcrumb
$page->breadcrumbs->add(__('Set Future Absence'));

if (isActionAccessible($guid, $connection2, '/modules/Attendance/attendance_future_byPerson.php') == false) {
    //Acess denied
    echo "<div class='alert alert-danger'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    $HelperGateway = $container->get(HelperGateway::class);
    //Proceed!
    if (isset($_GET['return'])) {
        returnProcess($guid, $_GET['return'], null,
        	array( 'warning2' => __('Your request was successful, but some data was not properly saved.') .' '. __('The specified date is not in the future, or is not a school day.'),
        		   'error7' => __('Your request failed because the student has already been marked absent for the full day.'),
        		   'error8' => __('Your request failed because the selected date is not in the future.'), )
        );
    }

    $attendance = new AttendanceView($pupilsight, $pdo);

    $scope = (isset($_POST['scope']))? $_POST['scope'] : 'single';
    $pupilsightPersonID = (isset($_POST['pupilsightPersonID']))? $_POST['pupilsightPersonID'] : [];
    if (!empty($pupilsightPersonID)) {
        $pupilsightPersonID = is_array($pupilsightPersonID)
            ? array_unique($pupilsightPersonID)
            : explode(",", $pupilsightPersonID);
    }

    if($_POST){         
        $pupilsightProgramID =  isset($_POST['pupilsightProgramID'])? $_POST['pupilsightProgramID'] : '';
        $pupilsightYearGroupID = isset($_POST['pupilsightYearGroupID'])? $_POST['pupilsightYearGroupID'] : '';
        $pupilsightRollGroupID = isset($_POST['pupilsightRollGroupID'])? $_POST['pupilsightRollGroupID'] : '';
        $classes =  $HelperGateway->getClassByProgram($connection2, $pupilsightProgramID);
        $sections =  $HelperGateway->getMultipleSectionByProgram($connection2, $pupilsightYearGroupID,  $pupilsightProgramID);
    } else {
        $pupilsightProgramID =  '';
        $pupilsightYearGroupID =  '';
        $pupilsightRollGroupID =  '';
        $classes = array('' => 'Select Class');
        $sections = array('' => 'Select Section');
     
    }
           $sql = 'SELECT a.*, b.name FROM pupilsightProgramClassSectionMapping AS a LEFT JOIN pupilsightYearGroup AS b ON a.pupilsightYearGroupID = b.pupilsightYearGroupID WHERE a.pupilsightProgramID = "' . $pupilsightProgramID . '" GROUP BY a.pupilsightYearGroupID';
        $result = $connection2->query($sql);
        $classesdata = $result->fetchAll();
        $classes=array();  
        foreach ($classesdata as $ke => $cl) {
            $classes[$cl['pupilsightYearGroupID']] = $cl['name'];
        }

        $setclass = array();
        if(!empty($setclasss)){
            $setclass = $setclasss;
        }
    $absenceType = (isset($_GET['absenceType']))? $_GET['absenceType'] : 'full';
    $date = (isset($_GET['date']))? date($_GET['date']) : '';

    echo '<h2>'.__('Choose Student')."</h2>";
    $sqlp = 'SELECT pupilsightProgramID, name FROM pupilsightProgram ';
    $resultp = $connection2->query($sqlp);
    $rowdataprog = $resultp->fetchAll();
    $program=array();  
    $program2=array();  
    $program1=array(''=>'Select Program');
    foreach ($rowdataprog as $dt) {
        $program2[$dt['pupilsightProgramID']] = $dt['name'];
    }
    $program= $program1 + $program2;  
    $sqls = "SELECT pupilsightPersonID, officialName FROM pupilsightPerson  WHERE pupilsightRoleIDPrimary=003 ";
    $results = $connection2->query($sqls);
    $rowdatastd = $results->fetchAll();
    $student = array();
    $student1 = array(''=>'Select Student');
    $student2 = array();
 
    if(!empty($rowdatastd)){
      
        foreach ($rowdatastd as $st) {
            $student2[$st['pupilsightPersonID']] = $st['officialName'];
        }
    }
    $student = $student1 + $student2;


    $pupilsightSchoolYearID = '';
    if (isset($_GET['pupilsightSchoolYearID'])) {
        $pupilsightSchoolYearID = $_GET['pupilsightSchoolYearID'];
    }
    if ($pupilsightSchoolYearID == '' or $pupilsightSchoolYearID == $_SESSION[$guid]['pupilsightSchoolYearID']) {
        $pupilsightSchoolYearID = $_SESSION[$guid]['pupilsightSchoolYearID'];
        $pupilsightSchoolYearName = $_SESSION[$guid]['pupilsightSchoolYearName'];
    }

    //Generate choose student form
    $form = Form::create('futureAttendance', $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.$_SESSION[$guid]['module'].'/attendance_future_byPerson.php')->addClass('newform');
    $form->setFactory(DatabaseFormFactory::create($pdo));
    $form->setClass('noIntBorder fullWidth');

    $form->addHiddenValue('q','/modules/'.$_SESSION[$guid]['module'].'/attendance_future_byPerson.php');
    echo '<input type="hidden" name="pupilsightSchoolYearID" id="pupilsightSchoolYearID" value="'.$pupilsightSchoolYearID.'"> ';  

    $availableScopes = array(
        'single' => __('Single Student'),
        'multiple' => __('Multiple Students'),
    );
    $row = $form->addRow();
        // $row->addLabel('scope', __('Scope'));
        // $row->addSelect('scope')->fromArray($availableScopes)->selected($scope);
        $col = $row->addColumn()->setClass('newdes');
        $col->addLabel('scope', __('Scope'));
        $col->addSelect('scope')->fromArray($availableScopes)->selected($scope)->required();
    $form->toggleVisibilityByClass('student')->onSelect('scope')->when('single');
    $form->toggleVisibilityByClass('students')->onSelect('scope')->when('multiple');
        
    $col = $row->addColumn()->setClass('newdes');
    $col->addLabel('pupilsightProgramID', __('Program'));
    $col->addSelect('pupilsightProgramID')->setId('getMultiClassByProg')->selected($pupilsightProgramID)->fromArray($program);
    
    $col = $row->addColumn()->setClass('newdes');
    $col->addLabel('pupilsightYearGroupID', __('Class'))->addClass('dte');
    $col->addSelect('pupilsightYearGroupID')->setId('showMultiClassByProg')->fromArray($classes)->selected($pupilsightYearGroupID)->selectMultiple();

    $col = $row->addColumn()->setClass('newdes');
    $col->addLabel('[pupilsightRollGroupID', __('Section'))->addClass('dte');
    $col->addSelect('pupilsightRollGroupID')->setId('showMultiSecByProgCls')->fromArray($sections)->selected($pupilsightRollGroupID)->selectMultiple();

    $col = $row->addColumn()->addClass('student');    
    $col->addLabel('pupilsightPersonID', __('Students'));
    $col->addSelect('pupilsightPersonID')->fromArray($student)->selected($pupilsightPersonID)->required();
    

    $col = $row->addColumn()->addClass('students');    
    $col->addLabel('pupilsightPersonIDs', __('Students'));
    $col->addSelect('pupilsightPersonID')->fromArray($student)->selected($pupilsightPersonID)->required()->selectMultiple();

    // $col = $row->addColumn()->setClass('newdes');    
    // $col->addLabel('pupilsightPersonID', __('Students'));
    // $col->addSelect('pupilsightPersonID', $_SESSION[$guid]['pupilsightSchoolYearID'])->setID('pupilsightPersonIDSingle')->required()->placeholder()->selected($pupilsightPersonID[0] ?? '');


    // $col = $row->addColumn()->setClass('newdes');
    //     $col->addLabel('pupilsightPersonID', __('Student'));
    //     $col->addSelectStudent('pupilsightPersonID', $_SESSION[$guid]['pupilsightSchoolYearID'])->setID('pupilsightPersonIDSingle')->required()->placeholder()->selected($pupilsightPersonID[0] ?? '');

    // $row = $form->addRow()->addClass('students');
    //     $row->addLabel('pupilsightPersonID', __('Studentss'));
    //     $row->addSelect('pupilsightPersonID', $_SESSION[$guid]['pupilsightSchoolYearID'], array('allstudents' => true, 'byRoll' => true))->setID('pupilsightPersonIDMultiple')->required()->selectMultiple()->selected($pupilsightPersonID);


    if (isActionAccessible($guid, $connection2, '/modules/Attendance/attendance_take_byCourseClass.php')) {
        $availableAbsenceTypes = array(
            'full' => __('Full Day'),
            'partial' => __('Partial'),
        );

        $row = $form->addRow()->addClass('student');
            $row->addLabel('absenceType', __('Absence Type'));
            $row->addSelect('absenceType')->fromArray($availableAbsenceTypes)->selected($absenceType);

        $form->toggleVisibilityByClass('partialDateRow')->onSelect('absenceType')->when('partial');
        $row = $form->addRow()->addClass('partialDateRow');
            $row->addLabel('date', __('Date'));
            $row->addDate('date')->required()->setValue($date);
    }

    $form->addRow()->addSearchSubmit($pupilsight->session);

    echo $form->getOutput();

    if(!empty($pupilsightPersonID)) {
        $today = date('Y-m-d');
        $attendanceLog = '';

        if ($scope == 'single' || $scope == 'multiple') {
            $attendanceLog .= "<div id='attendanceLog'>";
                //Get attendance log
                try {
                    $dataLog = array('pupilsightPersonID' => $pupilsightPersonID[0], 'date' => "$today-0-0-0"); //"$today-23-59-59"
                    $sqlLog = "SELECT pupilsightAttendanceLogPersonID, date, direction, type, context, reason, comment, timestampTaken, pupilsightAttendanceLogPerson.pupilsightCourseClassID, preferredName, surname, pupilsightCourseClass.nameShort as className, pupilsightCourse.nameShort as courseName FROM pupilsightAttendanceLogPerson JOIN pupilsightPerson ON (pupilsightAttendanceLogPerson.pupilsightPersonIDTaker=pupilsightPerson.pupilsightPersonID) LEFT JOIN pupilsightCourseClass ON (pupilsightAttendanceLogPerson.pupilsightCourseClassID=pupilsightCourseClass.pupilsightCourseClassID) LEFT JOIN pupilsightCourse ON (pupilsightCourse.pupilsightCourseID=pupilsightCourseClass.pupilsightCourseID) WHERE pupilsightAttendanceLogPerson.pupilsightPersonIDTaker=pupilsightPerson.pupilsightPersonID AND pupilsightAttendanceLogPerson.pupilsightPersonID=:pupilsightPersonID AND date>=:date ORDER BY date";
                    $resultLog = $connection2->prepare($sqlLog);
                    $resultLog->execute($dataLog);
                } catch (PDOException $e) {
                    echo "<div class='alert alert-danger'>".$e->getMessage().'</div>';
                }

                //Get classes for partial attendance
                try {
                    $dataClasses = array('pupilsightSchoolYearID' => $_SESSION[$guid]['pupilsightSchoolYearID'], 'pupilsightPersonID' => $pupilsightPersonID[0], 'date' => dateConvert($guid, $date));
                    $sqlClasses = "SELECT DISTINCT pupilsightTT.pupilsightTTID, pupilsightTT.name, pupilsightCourseClass.pupilsightCourseClassID, pupilsightCourseClass.nameShort as classNameShort, pupilsightTTColumnRow.name as columnName, pupilsightTTColumnRow.timeStart, pupilsightTTColumnRow.timeEnd, pupilsightCourse.name as courseName, pupilsightCourse.nameShort as courseNameShort FROM pupilsightTT JOIN pupilsightTTDay ON (pupilsightTT.pupilsightTTID=pupilsightTTDay.pupilsightTTID) JOIN pupilsightTTDayRowClass ON (pupilsightTTDayRowClass.pupilsightTTDayID=pupilsightTTDay.pupilsightTTDayID) JOIN pupilsightTTDayDate ON (pupilsightTTDay.pupilsightTTDayID=pupilsightTTDayDate.pupilsightTTDayID)  JOIN pupilsightCourseClass ON (pupilsightTTDayRowClass.pupilsightCourseClassID=pupilsightCourseClass.pupilsightCourseClassID) JOIN pupilsightTTColumnRow ON (pupilsightTTColumnRow.pupilsightTTColumnRowID=pupilsightTTDayRowClass.pupilsightTTColumnRowID) JOIN pupilsightCourseClassPerson ON (pupilsightCourseClassPerson.pupilsightCourseClassID=pupilsightCourseClass.pupilsightCourseClassID) JOIN pupilsightCourse ON (pupilsightCourse.pupilsightCourseID=pupilsightCourseClass.pupilsightCourseID) WHERE pupilsightPersonID=:pupilsightPersonID AND pupilsightCourse.pupilsightSchoolYearID=:pupilsightSchoolYearID AND active='Y' AND pupilsightTTDayDate.date=:date ORDER BY pupilsightTTColumnRow.timeStart ASC";
                    $resultClasses = $connection2->prepare($sqlClasses);
                    $resultClasses->execute($dataClasses);
                } catch (PDOException $e) {
                    echo "<div class='alert alert-danger'>" . $e->getMessage() . '</div>';
                }

                if ($absenceType == 'partial' && $resultClasses->rowCount() == 0) {
                    echo '<div class="alert alert-danger">';
                    echo __('Cannot record a partial absence. This student does not have timetabled classes for this day.');
                    echo '</div>';
                    return;
                }

                //Construct attendance log
                if ($resultLog->rowCount() > 0) {
                    $attendanceLog .= '<h4>';
                        $attendanceLog .= __('Attendance Log');
                    $attendanceLog .= '</h4>';

                    $attendanceLog .= "<p><span class='emphasis small'>";
                        $attendanceLog .= __('The following future absences have been set for the selected student.');
                    $attendanceLog .= '</span></p>';

                    $attendanceLog .= '<table class="mini smallIntBorder fullWidth colorOddEven" cellspacing=0>';
                    $attendanceLog .= '<tr class="head">';
                        $attendanceLog .= '<th>'.__('Date').'</th>';
                        $attendanceLog .= '<th>'.__('Attendance').'</th>';
                        $attendanceLog .= '<th>'.__('Where').'</th>';
                        $attendanceLog .= '<th>'.__('Recorded By').'</th>';
                        $attendanceLog .= '<th>'.__('On').'</th>';
                        $attendanceLog .= '<th style="width: 50px;">'.__('Actions').'</th>';
                    $attendanceLog .= '</tr>';

                    while ($rowLog = $resultLog->fetch()) {
                        $attendanceLog .= '<tr class="'.( $rowLog['direction'] == 'Out'? 'error' : 'current').'">';

                        $attendanceLog .= '<td>'.date("M j", strtotime($rowLog['date']) ).'</td>';

                        $attendanceLog .= '<td>';
                        $attendanceLog .= '<b>'.$rowLog['direction'].'</b> ('.$rowLog['type']. ( !empty($rowLog['reason'])? ', '.$rowLog['reason'] : '') .')';
                        if ( !empty($rowLog['comment']) ) {
                            $attendanceLog .= '&nbsp;<img title="'.$rowLog['comment'].'" src="./themes/'.$_SESSION[$guid]['pupilsightThemeName'].'/img/messageWall.png" width=16 height=16/>';
                        }
                        $attendanceLog .= '</td>';

                        if (($rowLog['context'] == 'Future' || $rowLog['context'] == 'Class') && $rowLog['pupilsightCourseClassID'] > 0) {
                            $attendanceLog .= '<td>'.__($rowLog['context']).' ('.$rowLog['courseName'].'.'.$rowLog['className'].')</td>';
                        } else {
                            $attendanceLog .= '<td>'.__($rowLog['context']).'</td>';
                        }

                        $attendanceLog .= '<td>';
                            $attendanceLog .= formatName('', $rowLog['preferredName'], $rowLog['surname'], 'Staff', false, true);
                        $attendanceLog .= '</td>';

                        $attendanceLog .= '<td>'.date("g:i a, M j", strtotime($rowLog['timestampTaken']) ).'</td>';

                        $attendanceLog .= '<td>';
                            $attendanceLog .= "<a href='".$_SESSION[$guid]['absoluteURL']."/modules/Attendance/attendance_future_byPersonDeleteProcess.php?pupilsightPersonID=$pupilsightPersonID[0]&pupilsightAttendanceLogPersonID=".$rowLog['pupilsightAttendanceLogPersonID']."' onclick='confirm(\"Are you sure you want to delete this record? Unsaved changes will be lost.\")'><img title='".__('Delete')."' src='./themes/".$_SESSION[$guid]['pupilsightThemeName']."/img/garbage.png'/></a> ";
                        $attendanceLog .= '</td>';
                        $attendanceLog .= '</tr>';
                    }
                    $attendanceLog .= '</table><br/>';
                }
            $attendanceLog .= '</div>';
        }

        $form = Form::create('attendanceSet',$_SESSION[$guid]['absoluteURL'] . '/modules/' . $_SESSION[$guid]['module'] . '/attendance_future_byPersonProcess.php');

        $form->addHiddenValue('address', $_SESSION[$guid]['address']);
        $form->addHiddenValue('scope', $scope);
        $form->addHiddenValue('absenceType', $absenceType);
        $form->addHiddenValue('pupilsightPersonID', implode(",", $pupilsightPersonID));

        $form->addRow()->addHeading(__('Set Future Attendance'));

        if ($absenceType == 'full') {
            $row = $form->addRow();
                $row->addLabel('dateStart', __('Start Date'));
                $row->addDate('dateStart')->required();

            $row = $form->addRow();
                $row->addLabel('dateEnd', __('End Date'));
                $row->addDate('dateEnd');
        } else {
            $form->addHiddenValue('dateStart', $date);
            $form->addHiddenValue('dateEnd', $date);

            $row = $form->addRow();
                $row->addLabel('periodSelectContainer', __('Periods Absent'));

                $table = $row->addTable('periodSelectContainer')->setClass('standardWidth');
                $table->addHeaderRow()->addHeading(date('F j, Y', strtotime(dateConvert($guid, $date))));

                while ($class = $resultClasses->fetch()) {
                    $row = $table->addRow();
                    $row->addCheckbox('courses[]')
                        ->description($class['columnName'] . ' - ' . $class['courseNameShort'] . '.' . $class['classNameShort'])
                        ->setValue($class['pupilsightCourseClassID'])
                        ->inline()
                        ->setClass('');
                }
        }

        // Filter only attendance types with future = 'Y'
        $attendanceTypes = array_reduce($attendance->getAttendanceTypes(), function ($group, $item) {
            if ($item['future'] == 'Y') $group[] = $item['name'];
            return $group;
        }, array());

        $row = $form->addRow();
            $row->addLabel('type', __('Type'));
            $row->addSelect('type')->fromArray($attendanceTypes)->required()->selected('Absent');

        $row = $form->addRow();
            $row->addLabel('reason', __('Reason'));
            $row->addSelect('reason')->fromArray($attendance->getAttendanceReasons());

        $row = $form->addRow();
            $row->addLabel('comment', __('Comment'))->description('255 character limit');
            $row->addTextArea('comment')->setRows(3)->maxLength(255);

        $form->addRow()->addSubmit();

        echo $attendanceLog;
        echo $form->getOutput();
    }
}
?>

<script type='text/javascript'>
    $("#absenceType").change(function(){
        if ($("#scope").val() != 'multiple') {
            $("#attendanceLog").css("display","none");
            $("#attendanceSet").css("display","none");
        }
    });
    $("#scope").change(function(){
        $("#attendanceLog").css("display","none");
        $("#attendanceSet").css("display","none");
    });

    $(document).ready(function () {
      	$('#showMultiClassByProg').selectize({
      		plugins: ['remove_button'],
      	});
    });
    
    $(document).ready(function () {
      	$('#showMultiSecByProgCls').selectize({
      		plugins: ['remove_button'],
        });      
    });
</script>
    <style>
    
    .text-xxs {
        display:none
    }

    
</style>