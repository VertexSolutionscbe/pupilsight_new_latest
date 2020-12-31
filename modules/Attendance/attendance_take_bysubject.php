<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Forms\Form;
use Pupilsight\Forms\DatabaseFormFactory;
use Pupilsight\Module\Attendance\AttendanceView;
use Pupilsight\Domain\Timetable\TimetableGateway;
use Pupilsight\Domain\Timetable\TimetableDayGateway;
use Pupilsight\Services\Format;
use Pupilsight\Domain\Helper\HelperGateway;

//Module includes
require_once __DIR__ . '/moduleFunctions.php';
require_once __DIR__ . '/src/AttendanceView.php';

if (isActionAccessible($guid, $connection2, '/modules/Attendance/attendance_take_bysubject.php') == false) {
    //Acess denied
    echo "<div class='alert alert-danger'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    //Get action with highest precendence
    $highestAction = getHighestGroupedAction($guid, $_GET['q'], $connection2);
    if ($highestAction == false) {
        echo "<div class='alert alert-danger'>";
        echo __('The highest grouped action cannot be determined.');
        echo '</div>';
    } else {
        $pupilsightSchoolYearID = '';
        if (isset($_GET['pupilsightSchoolYearID'])) {
            $pupilsightSchoolYearID = $_GET['pupilsightSchoolYearID'];
        }
        echo '<h3>';
        if(isset($_GET['timetableattendance'])) {    //checking for extra parameter sent by module function for attendance by timetable
            echo __('Attendance By TimeTable');
        }else {
            echo __('Attendance By Subject');
        }
        echo '</h3>';

        $HelperGateway = $container->get(HelperGateway::class);   
       
        $pupilsightPersonID =   $_SESSION[$guid]['pupilsightPersonID'];
        $pupilsightRoleIDPrimary =$_SESSION[$guid]['pupilsightRoleIDPrimary'];
        $program = array();
        $program2 = array();
        $program1 = array('' => 'Select Program');
        if( $pupilsightRoleIDPrimary !='001')//for staff login
        {
        $staff_person_id=$pupilsightPersonID;
        $sql1 = "SELECT p.pupilsightProgramID,p.name AS program,a.pupilsightYearGroupID FROM pupilsightProgramClassSectionMapping AS a LEFT JOIN assignstaff_toclasssection b ON(a.pupilsightMappingID =b.pupilsightMappingID) LEFT JOIN pupilsightProgram AS p
        ON(p.pupilsightProgramID =a.pupilsightProgramID) WHERE b.pupilsightPersonID=".$pupilsightPersonID."  GROUP By a.pupilsightYearGroupID ";//except Admin //0000002962
        $result1 = $connection2->query($sql1);
        $row1 = $result1->fetchAll();
        /* echo "<pre>";
        print_r($row1);*/

        $progrm_id="Staff_program";
        $class_id="Staff_class";
        $section_id= "Staff_section";
        foreach ($row1 as $dt) {
            $program2[$dt['pupilsightProgramID']] = $dt['program'];
        }
        $program = $program1 + $program2;            
        $disable_cls= 'dsble_attr';        
        }
        else
        {
            $staff_person_id= Null;
            $disable_cls= '';
            $progrm_id="pupilsightProgramID";
            $class_id="pupilsightYearGroupID";
            $section_id= "pupilsightRollGroupID";
            $sqlp = 'SELECT pupilsightProgramID, name FROM pupilsightProgram ';
            $resultp = $connection2->query($sqlp);
            $rowdataprog = $resultp->fetchAll();         
        
            foreach ($rowdataprog as $dt) {
                $program2[$dt['pupilsightProgramID']] = $dt['name'];
            }
            $program = $program1 + $program2;            
        }
        if(isset($_GET)){
        
            $pupilsightProgramID =  isset($_GET['pupilsightProgramID'])? $_GET['pupilsightProgramID'] : '';
            $pupilsightYearGroupID = isset($_GET['pupilsightYearGroupID'])? $_GET['pupilsightYearGroupID'] : '';
            $pupilsightRollGroupID = isset($_GET['pupilsightRollGroupID'])? $_GET['pupilsightRollGroupID'] : '';
            $pupilsightDepartmentID = isset($_GET['pupilsightDepartmentID'])? $_GET['pupilsightDepartmentID'] : '';
            $time_slot  = isset($_GET['time_slot'])? $_GET['time_slot'] : '';

            $stuId = isset($_GET['studentId'])? $_GET['studentId'] : '';

            $classes =  $HelperGateway->getClassByProgram_staff($connection2, $pupilsightProgramID,$staff_person_id);
            $sections =  $HelperGateway->getSectionByProgram_staff($connection2, $pupilsightYearGroupID,  $pupilsightProgramID,$staff_person_id);
        } else {
            $pupilsightProgramID =  '';
            $pupilsightYearGroupID =  '';
            $pupilsightRollGroupID =  '';
            $pupilsightDepartmentID = '';
          
            $stuId = '0';
            $classes = array('');
            $sections = array('');    
        }

    $sqlq = 'SELECT pupilsightSchoolYearID, name FROM pupilsightSchoolYear ';
    $resultval = $connection2->query($sqlq);
         $rowdata = $resultval->fetchAll();
         $academic=array();
         $ayear = '';
        if(!empty($rowdata)){
            $ayear = $rowdata[0]['name'];
            foreach ($rowdata as $dt) {
                $academic[$dt['pupilsightSchoolYearID']] = $dt['name'];
            }
        }

//select subjects from department
$sqld = 'SELECT pupilsightDepartmentID, name FROM pupilsightDepartment ';
$resultd = $connection2->query($sqld);
$rowdatadept = $resultd->fetchAll();


$subjects=array();  
$subject2=array();  
// $subject1=array(''=>'Select Subjects');
foreach ($rowdatadept as $dt) {
    $subject2[$dt['pupilsightDepartmentID']] = $dt['name'];
}
$subjects=  $subject2;
        if(isset($_GET['timetableattendance'])) {    //checking for extra parameter sent by module function for attendance by timetable
            $page->breadcrumbs->add(__('Attendance By TimeTable'));
        }else {
            $page->breadcrumbs->add(__('Attendance By Subject'));
        }

    }
    
    $sql = "SELECT pupilsightSchoolYear.name as groupedBy, pupilsightTTID as value, pupilsightTT.name AS name FROM pupilsightTT JOIN pupilsightSchoolYear ON (pupilsightTT.pupilsightSchoolYearID=pupilsightSchoolYear.pupilsightSchoolYearID) ORDER BY pupilsightSchoolYear.sequenceNumber, pupilsightTT.name";
    $result = $pdo->executeQuery(array(), $sql);

    // Transform into an option list grouped by Year
    $ttList = ($result && $result->rowCount() > 0)? $result->fetchAll() : array();
    $ttList = array_reduce($ttList, function($list, $item) {
        $list[$item['groupedBy']][$item['value']] = $item['name'];
        return $list;
    }, array());

    $timetableGateway = $container->get(TimetableGateway::class);
    $timetableDayGateway = $container->get(TimetableDayGateway::class);
    $pupilsightTTID='00000002';
    
    $values = $timetableGateway->getTTByID($pupilsightTTID);
    $ttDays = $timetableDayGateway->selectTTDaysByID($pupilsightTTID)->fetchAll();
    foreach ($ttDays as $ttDay) {
    $ttDayRows = $timetableDayGateway->selectTTDayRowsByID($ttDay['pupilsightTTDayID'])->fetchAll();
    }

   
    /*foreach ($ttDayRows as $ttDayRow) {

        echo $ttDayRow['nameShort'].Format::timeRange($ttDayRow['timeStart'], $ttDayRow['timeEnd'])."<br/>";
    }
*/
    $timeslots=array();  
    $timeslots2=array();  
   // $subject1=array(''=>'Select Subjects');
    foreach ($ttDayRows as $ttDayRow) {
        $timeslots2[$ttDayRow['pupilsightTTColumnRowID']] = $ttDayRow['nameShort']."/".Format::timeRange($ttDayRow['timeStart'], $ttDayRow['timeEnd']);
    }
    $timeslots=  $timeslots2;  


    $attendance = new AttendanceView($pupilsight, $pdo);

    $pupilsightRollGroupID = '';
    if (isset($_GET['pupilsightRollGroupID']) == false) {
        try {
            $data = array('pupilsightPersonIDTutor1' => $_SESSION[$guid]['pupilsightPersonID'], 'pupilsightPersonIDTutor2' => $_SESSION[$guid]['pupilsightPersonID'], 'pupilsightPersonIDTutor3' => $_SESSION[$guid]['pupilsightPersonID'], 'pupilsightSchoolYearID' => $_SESSION[$guid]['pupilsightSchoolYearID']);
            $sql = "SELECT pupilsightRollGroup.*, firstDay, lastDay FROM pupilsightRollGroup JOIN pupilsightSchoolYear ON (pupilsightRollGroup.pupilsightSchoolYearID=pupilsightSchoolYear.pupilsightSchoolYearID) WHERE (pupilsightPersonIDTutor=:pupilsightPersonIDTutor1 OR pupilsightPersonIDTutor2=:pupilsightPersonIDTutor2 OR pupilsightPersonIDTutor3=:pupilsightPersonIDTutor3) AND pupilsightRollGroup.pupilsightSchoolYearID=:pupilsightSchoolYearID";
            $result = $connection2->prepare($sql);
            $result->execute($data);
        } catch (PDOException $e) {
            echo "<div class='alert alert-danger'>".$e->getMessage().'</div>';
        }
        if ($result->rowCount() > 0) {
            $row = $result->fetch();
            $pupilsightRollGroupID = $row['pupilsightRollGroupID'];
        }
    } else {
        $pupilsightRollGroupID = $_GET['pupilsightRollGroupID'];
    }

    $today = date('Y-m-d');
    $currentDate = isset($_GET['currentDate'])? dateConvert($guid, $_GET['currentDate']) : $today;



/*
    echo "<pre>";
    print_r($timeslots);
    */
    // echo "<a style='display:none' id='clickStudentPage' href='fullscreen.php?q=/modules/Staff/select_staff_toAssign.php&width=800'  class='thickbox '>Change Route</a>";   
    // echo "<div style='height:50px;'><div class='float-left mb-2'><a  id='assignStudentPage' data-type='staff' class='btn btn-primary'>Assign</a>&nbsp;&nbsp;";  
    // echo "</div><div class='float-none'></div></div>";
    $searchform = Form::create('filter', $_SESSION[$guid]['absoluteURL'] . '/index.php', 'get');
    $searchform->setFactory(DatabaseFormFactory::create($pdo));
    $searchform->addHiddenValue('q', '/modules/' . $_SESSION[$guid]['module'] . '/attendance_take_bysubject.php');
    $searchform->addHiddenValue('studentId', '0');
    $row = $searchform->addRow();
    echo '<input type="hidden" data-pid="'.$_SESSION[$guid]['pupilsightPersonID'].'" value="'.$_SESSION[$guid]['pupilsightRoleIDPrimary'].'" name="roleid" id="roleid"> ';//002 for teacher
    $col = $row->addColumn()->setClass('newdes');
    $col->addLabel('pupilsightProgramID', __('Program'));
    $col->addSelect('pupilsightProgramID')->fromArray($program)->setId($progrm_id)->required()->selected($pupilsightProgramID)->placeholder();

    $col = $row->addColumn()->setClass('newdes');
    $col->addLabel('pupilsightYearGroupID', __('Class'));
    $col->addSelect('pupilsightYearGroupID')->setId($class_id)->fromArray($classes)->selected($pupilsightYearGroupID)->required()->addClass('getSubjectsFromPeriod');
    $col->addTextField('pupilsightPersonID')->setId('staff_id')->addClass('nodisply')->setValue($pupilsightPersonID);
    $col = $row->addColumn()->setClass('newdes');
    $col->addLabel('pupilsightRollGroupID', __('Section'));
    $col->addSelect('pupilsightRollGroupID')->required()->fromArray($sections)->setId($section_id)->selected($pupilsightRollGroupID)->placeholder()->addClass('getSubjectsFromPeriod');

    $col = $row->addColumn()->setClass('newdes');
    $col->addLabel('pupilsightDepartmentID', __('Subjects'));
    $col->addSelect('pupilsightDepartmentID')->fromArray($subjects)->required()->selected($pupilsightDepartmentID)->placeholder();
    $col = $row->addColumn()->setClass('newdes');
    $col->addLabel('time_slot', __('Slots'));   
    $col->addSelect('time_slot')->fromArray($timeslots)->addClass('txtfield')->required()->selected($time_slot)->placeholder();
    $col->addSelect('time_slot_default')->fromArray($timeslots)->addClass('nodsply'); 
    

    $col = $row->addColumn()->setClass('newdes');
    $col->addLabel('currentDate', __('Date'))->addClass('dte');
    $col->addDate('currentDate')->setId('dueDate')->required()->setValue(dateConvertBack($guid, $currentDate));

    $col = $row->addColumn()->setClass('newdes');   
    
    $row = $searchform->addRow();
    $col = $row->addColumn()->setClass('newdes ');
    
    $col->addTextField('')->addClass('txtfield nodsply');

    $row->addSearchSubmit($pupilsight->session);
   // $col->addContent('<button id="submitInvoice"  class=" btn btn-primary">Search</button>');  
    echo $searchform->getOutput();


/*
$form = Form::create('program','');
$form->setFactory(DatabaseFormFactory::create($pdo));

$form->addHiddenValue('address', $_SESSION[$guid]['address']);
//  $form->addHiddenValue('id', $id);
//$tab = '';

$row = $form->addRow()->setID('seatdiv')->addClass('seatdiv');

$col = $row->addColumn()->setClass('newdes');
$col->addLabel('time_slot', __('Slots'));   
$col->addSelect('time_slot')->fromArray($timeslots)->addClass('txtfield')->required();

$col = $row->addColumn()->setClass('newdes');
//$col->addSubmit()->setClass('margin_top1 ');  

$col = $row->addColumn()->setClass('newdes ');

$col->addTextField('')->addClass('txtfield nodsply');

 


$row = $form->addRow();


echo $form->getOutput();

*/
if ($pupilsightRollGroupID != '') {
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
        if (isSchoolOpen($guid, $currentDate, $connection2) == false AND empty($specialDaysCounts)) {
            echo "<div class='alert alert-danger'>";
            echo __('School is closed on the specified date, and so attendance information cannot be recorded.');
            echo '</div>';
        } else {
            $countClassAsSchool = getSettingByScope($connection2, 'Attendance', 'countClassAsSchool');
            $defaultAttendanceType = getSettingByScope($connection2, 'Attendance', 'defaultRollGroupAttendanceType');
            //check auto lock
           $sqlt_autolock="SELECT *FROM attn_settings WHERE pupilsightProgramID='".$pupilsightProgramID."' AND auto_lock_attendance='1' AND attn_type IN(2,3) AND  FIND_IN_SET('".$pupilsightYearGroupID."',pupilsightYearGroupID) > 0";
            $autolockStatus = $connection2->query($sqlt_autolock);
            $auto_lock = $autolockStatus->fetch();
            if(!empty($auto_lock)){
            echo "<div class='alert alert-danger'>";
            echo __('This Class Attendance Locked by admin. Please Contact admin');
            echo '</div>';
            return;
            }
            //ends auto lock
            //check marking lock
            $m_sql="SELECT *FROM attn_settings WHERE pupilsightProgramID='".$pupilsightProgramID."' AND lock_attendance_marking='1' AND FIND_IN_SET('".$pupilsightYearGroupID."',pupilsightYearGroupID) > 0";
            $marking_Status = $connection2->query($m_sql);
            $marking_lock = $marking_Status->fetch();
            if(!empty($marking_lock)){
            if($marking_lock['fromDate']<=$currentDate AND $marking_lock['toDate']>=$currentDate){
            echo "<div class='alert alert-danger'>";
            echo __('This Class Attendance Locked by admin (between '.date('d/m/Y',strtotime($marking_lock['fromDate'])).' To '.date('d/m/Y',strtotime($marking_lock['toDate'])).' dates). Please Contact admin');
            echo '</div>';
            return;
            }
            }
            //ends marking lock
            //Check roll group
            try {
                $data = array('pupilsightRollGroupID' => $pupilsightRollGroupID, 'pupilsightSchoolYearID' => $_SESSION[$guid]['pupilsightSchoolYearID']);
                $sql = 'SELECT pupilsightRollGroup.*, firstDay, lastDay FROM pupilsightRollGroup JOIN pupilsightSchoolYear ON (pupilsightRollGroup.pupilsightSchoolYearID=pupilsightSchoolYear.pupilsightSchoolYearID) WHERE pupilsightRollGroupID=:pupilsightRollGroupID AND pupilsightRollGroup.pupilsightSchoolYearID=:pupilsightSchoolYearID';
                $result = $connection2->prepare($sql);
                $result->execute($data);
            } catch (PDOException $e) {
                echo "<div class='alert alert-danger'>".$e->getMessage().'</div>';
            }

            if ($result->rowCount() == 0) {
                echo '<div class="alert alert-danger">';
                echo __('There are no records to display.');
                echo '</div>';
                return;
            }

            $rollGroup = $result->fetch();


            $sqldp = 'SELECT pupilsightDepartmentID, name FROM pupilsightDepartment WHERE pupilsightDepartmentID="'.$pupilsightDepartmentID.'" ';
            $resultdp = $connection2->query($sqldp);
            $rowdatadeptnt = $resultdp->fetch();
//check attendance is blocked for this date
            $block_attend_cnt=0;
            try {    
              $date=  dateConvert($guid, $currentDate);
            $data_block = array('pupilsightYearGroupID'=>$pupilsightYearGroupID,'pupilsightRollGroupID'=>$pupilsightRollGroupID);
            $sql_block = 'SELECT * FROM pupilsightAttendanceBlocked WHERE pupilsightYearGroupID=:pupilsightYearGroupID AND pupilsightRollGroupID=:pupilsightRollGroupID AND "'.$date.'" BETWEEN  `start_date` AND `end_date`   ';
            $resultblock = $connection2->prepare($sql_block);
            $resultblock->execute($data_block);
           $row_block= $resultblock->fetchAll();
            } catch (PDOException $e) {
             echo "<div class='alert alert-danger'>".$e->getMessage().'</div>';
             }
    //    echo "<pre>";print_r($row_block);
           $block_attend_cnt= count($row_block);

            /* echo "<pre>";
             print_r( $row_block);
*/
             if ($block_attend_cnt > 0) {
                echo "<div class='alert alert-danger'>";
                echo __('Attendance taking has been blocked for this date');
                echo '</div>';
            } 

            else
            {
            if ($rollGroup['attendance'] == 'N') {
                print "<div class='alert alert-danger'>" ;
                    print __("Attendance taking has been disabled for this roll group.") ;
                print "</div>" ;
            } else {

                //Show attendance log for the current day
                try {

                    //  //SELECT * FROM `pupilsightAttendanceLogDepartment` WHERE 1,`pupilsightAttendanceLogDepartmentID`,`pupilsightRollGroupID`,`pupilsightDepartmentID`,`pupilsightTTColumnRowID`,`pupilsightPersonIDTaker`,`date`,`timestampTaken`
                    $dataLog = array('pupilsightRollGroupID' => $pupilsightRollGroupID,'pupilsightDepartmentID' => $pupilsightDepartmentID ,'date' => $currentDate.'%');
                    $sqlLog = 'SELECT * FROM pupilsightAttendanceLogDepartment, pupilsightPerson WHERE pupilsightAttendanceLogDepartment.pupilsightPersonIDTaker=pupilsightPerson.pupilsightPersonID AND pupilsightRollGroupID=:pupilsightRollGroupID AND pupilsightDepartmentID=:pupilsightDepartmentID AND   date LIKE :date ORDER BY timestampTaken';
                    $resultLog = $connection2->prepare($sqlLog);
                    $resultLog->execute($dataLog);
                } catch (PDOException $e) {
                    echo "<div class='alert alert-danger'>".$e->getMessage().'</div>';
                }

                if ($resultLog->rowCount() < 1) {
                    echo "<div class='alert alert-danger'>";
                    echo __('Attendance has not been taken for this group yet for the specified date. The entries below are a best-guess based on defaults and information put into the system in advance, not actual data.');
                    echo '</div>';
                } 
                else if($_REQUEST['return']=='error11')
                {
                    echo "<div class='alert alert-danger'>";
                    echo __('Attendance has been taken at the following times for the specified date for this Subject with Same Time slot:.');
                    echo '</div>';
                }
                else {
                    echo "<div class='alert alert-sucess'>";
                    echo __('Attendance has been taken at the following times for the specified date for this group:');
                    echo '<ul>';
                    while ($rowLog = $resultLog->fetch()) {
                        echo '<li>'.sprintf(__('Recorded at %1$s on %2$s by %3$s.'), substr($rowLog['timestampTaken'], 11), dateConvertBack($guid, substr($rowLog['timestampTaken'], 0, 10)), formatName('', $rowLog['preferredName'], $rowLog['surname'], 'Staff', false, true)).'</li>';
                    }
                    echo '</ul>';
                    echo '</div>';
                }
              if(!empty($resultLog->rowCount())){
                    if($resultLog->rowCount()<=1){
                    //check auto lock
                    $sqlt_autolock="SELECT *FROM attn_settings WHERE pupilsightProgramID='".$pupilsightProgramID."' AND auto_lock_attendance='1' AND attn_type IN(1,3) AND  FIND_IN_SET('".$pupilsightYearGroupID."',pupilsightYearGroupID) > 0";
                    $autolockStatus = $connection2->query($sqlt_autolock);
                    $auto_lock = $autolockStatus->fetch();
                    if(!empty($auto_lock)){
                    echo "<div class='alert alert-danger'>";
                    echo __('This Class Attendance Locked by admin. Please Contact admin');
                    echo '</div>';
                    return;
                    }
                    }
                    }
                //Show roll group grid
                try {
                    $dataRollGroup = array('pupilsightProgramID'=>$pupilsightProgramID,'pupilsightYearGroupID'=>$pupilsightYearGroupID,'pupilsightRollGroupID' => $pupilsightRollGroupID, 'date' => $currentDate);
                  
                   $sqlRollGroup = "SELECT pupilsightPerson.image_240, pupilsightPerson.preferredName, pupilsightPerson.surname, pupilsightPerson.pupilsightPersonID FROM pupilsightStudentEnrolment INNER JOIN pupilsightPerson ON pupilsightStudentEnrolment.pupilsightPersonID=pupilsightPerson.pupilsightPersonID WHERE pupilsightRollGroupID=:pupilsightRollGroupID AND  pupilsightProgramID=:pupilsightProgramID AND  pupilsightYearGroupID=:pupilsightYearGroupID AND status='Full' AND (dateStart IS NULL OR dateStart<=:date) AND (dateEnd IS NULL  OR dateEnd>=:date) AND pupilsightPerson.pupilsightRoleIDPrimary=003  ORDER BY rollOrder, surname, preferredName";
                    $resultRollGroup = $connection2->prepare($sqlRollGroup);
                    $resultRollGroup->execute($dataRollGroup);
                } catch (PDOException $e) {
                    echo "<div class='alert alert-danger'>".$e->getMessage().'</div>';
                }

                if ($resultRollGroup->rowCount() < 1) {
                    echo "<div class='alert alert-danger'>";
                    echo __('There are no records to display.');
                    echo '</div>';
                } else {
                    $count = 0;
                    $countPresent = 0;
                    $columns = 4;

                    $defaults = array('type' => $defaultAttendanceType, 'reason' => '', 'comment' => '', 'context' => '');
                    $students = $resultRollGroup->fetchAll();

                    // Build the attendance log data per student
                    foreach ($students as $key => $student) {
                        $data = array('pupilsightPersonID' => $student['pupilsightPersonID'], 'date' => $currentDate.'%');
                        $sql = "SELECT type, reason, comment, context, timestampTaken FROM pupilsightAttendanceLogPerson
                                JOIN pupilsightPerson ON (pupilsightAttendanceLogPerson.pupilsightPersonID=pupilsightPerson.pupilsightPersonID)
                                WHERE pupilsightAttendanceLogPerson.pupilsightPersonID=:pupilsightPersonID
                                AND date LIKE :date";

                        if ($countClassAsSchool == 'N') {
                            $sql .= " AND NOT context='Class'";
                        }
                        $sql .= " ORDER BY timestampTaken DESC";
                        $result = $pdo->executeQuery($data, $sql);

                        $log = ($result->rowCount() > 0)? $result->fetch() : $defaults;

                        $students[$key]['cellHighlight'] = '';
                        if ($attendance->isTypeAbsent($log['type'])) {
                            $students[$key]['cellHighlight'] = 'dayAbsent';
                        } elseif ($attendance->isTypeOffsite($log['type'])) {
                            $students[$key]['cellHighlight'] = 'dayMessage';
                        }

                        $students[$key]['absenceCount'] = '';
                        $absenceCount = getAbsenceCount($guid, $student['pupilsightPersonID'], $connection2, $rollGroup['firstDay'], $rollGroup['lastDay']);
                        if ($absenceCount !== false) {
                            $absenceText = ($absenceCount == 1)? __('%1$s Day Absent') : __('%1$s Days Absent');
                            $students[$key]['absenceCount'] = sprintf($absenceText, $absenceCount);
                        }

                        if ($attendance->isTypePresent($log['type']) && $attendance->isTypeOnsite($log['type'])) {
                            $countPresent++;
                        }

                        $students[$key]['log'] = $log;
                    }

                    $form = Form::create('attendanceBySubject', $_SESSION[$guid]['absoluteURL'].'/modules/'.$_SESSION[$guid]['module']. '/attendance_by_subject_Process.php');
                    $form->setAutocomplete('off');

                    $form->addHiddenValue('address', $_SESSION[$guid]['address']);
                    $form->addHiddenValue('pupilsightRollGroupID', $pupilsightRollGroupID);
                    $form->addHiddenValue('currentDate', $currentDate);
                    $form->addHiddenValue('count', count($students));
                    $form->addHiddenValue('pupilsightYearGroupID', $pupilsightYearGroupID);
                    $form->addHiddenValue('pupilsightDepartmentID', $pupilsightDepartmentID);
                    $form->addHiddenValue('pupilsightProgramID', $pupilsightProgramID);
                    $form->addHiddenValue('time_slot', $time_slot);
                    //pupilsightYearGroupID,pupilsightDepartmentID,pupilsightProgramID

                    $row = $form->addRow()->setID('seatdiv')->addClass('seatdiv');

                  
                    $col = $row->addColumn()->setClass('newdes');
                    //$col->addSubmit()->setClass('margin_top1 ');  

                    $col = $row->addColumn()->setClass('newdes ');
                    
                    $col->addTextField('')->addClass('txtfield nodsply');

                    $form->addRow()->addHeading(__('Take Attendance') . ': '. htmlPrep($rollGroup['name']).":-". ($rowdatadeptnt['name']));

                    $grid = $form->addRow()->addGrid('attendance')->setBreakpoints('w-1/2 sm:w-1/4 md:w-1/5 lg:w-1/4');

                    foreach ($students as $student) {
                        $form->addHiddenValue($count . '-pupilsightPersonID', $student['pupilsightPersonID']);

                        $cell = $grid->addCell()
                            ->setClass('text-center py-2 px-1 -mr-px -mb-px flex flex-col justify-between')
                            ->addClass($student['cellHighlight']);

                        $cell->addContent(getUserPhoto($guid, $student['image_240'], 75));
                        $cell->addWebLink(formatName('', htmlPrep($student['preferredName']), htmlPrep($student['surname']), 'Student', false))
                             ->setURL('index.php?q=/modules/Students/student_view_details.php')
                             ->addParam('pupilsightPersonID', $student['pupilsightPersonID'])
                             ->addParam('subpage', 'Attendance')
                             ->setClass('pt-2 font-bold underline');
                        $cell->addContent($student['absenceCount'])->wrap('<div class="text-xxs italic py-2">', '</div>');
                        $cell->addSelect($count.'-type')
                             ->fromArray(array_keys($attendance->getAttendanceTypes()))
                             ->selected($student['log']['type'])
                             ->setClass('mx-auto float-none w-32 m-0 mb-px');
                             $reason=array ('' =>('Reason')); 
                             $reason+=$attendance->getAttendanceReasons();
                        $cell->addSelect($count.'-reason')
                             ->fromArray($reason)
                             ->selected($student['log']['reason'])
                             ->setClass('mx-auto float-none w-32 m-0 mb-px');
                        $cell->addTextField($count.'-comment')
                             ->maxLength(255)
                             ->setValue($student['log']['comment'])->placeholder('comment')
                             ->setClass('mx-auto float-none w-32 m-0 mb-2');
                        $cell->addContent($attendance->renderMiniHistory($student['pupilsightPersonID'], 'Roll Group'));

                        $count++;
                    }

                    $form->addRow()->addAlert(__('Total students:').' '. $count, 'success')->setClass('right')
                        ->append('<br/><span title="'.__('e.g. Present or Present - Late').'">'.__('Total students present in room:').'</span>&nbsp;<span id="presentsTotal">'. $countPresent.'</span>')
                        ->append('<br/><span title="'.__('e.g. not Present and not Present - Late').'">'.__('Total students absent from room:').'</span>&nbsp;<span id="absentsTotal">'. ($count-$countPresent).'</span> <i id="absentsNames" class="fa fa-eye" title="Total absents :" aria-hidden="true"></i>')
                        ->wrap('<b>', '</b>');

                    $row = $form->addRow();

                    // Drop-downs to change the whole group at once
                    $row->addButton(__('Change All').'?')->addData('toggle', '.change-all')->addClass('w-32 m-px sm:self-center');

                    $col = $row->addColumn()->setClass('change-all hidden flex flex-col sm:flex-row items-stretch sm:items-center');
                        $col->addSelect('set-all-type')->fromArray(array_keys($attendance->getAttendanceTypes()))->addClass('m-px');
                        $col->addSelect('set-all-reason')->fromArray($attendance->getAttendanceReasons())->addClass('m-px');
                        $col->addTextField('set-all-comment')->maxLength(255)->addClass('m-px');
                    $col->addButton(__('Apply'))->setID('set-all');

                    $row->addSubmit();

                    echo $form->getOutput();
                }
            }
        }
        }
    }
}

    
}
?>
<script>
   var reloadCall = false;
        var _pupilsightDepartmentID = "";
        var roleid= $("#roleid").val();
        var pupilsightPersonID = $("#roleid").attr('data-pid');
        $("#pupilsightDepartmentID").change(function() {
           
           
            var pupilsightYearGroupID = $('select[name="pupilsightYearGroupID"] option:selected').val();
            var pupilsightDepartmentID = $("#pupilsightDepartmentID").val();
         //   alert(pupilsightYearGroupID);
            
            if (pupilsightYearGroupID && pupilsightDepartmentID) {
                $allslote= $("#time_slot_default").html();  
                    var id = pupilsightDepartmentID;
                    var type = "getSubjectTimesloteNew";
                    $.ajax({
                        url: 'ajax_data.php',
                        type: 'post',
                        data: { val: id, type: type ,class_id:pupilsightYearGroupID},
                        async: true,
                        success: function(response) {

                          //  alert(response);
                          if(response=='<option value="">Select Timeslot</option>')
                          {
                           
                            $("#time_slot").html($allslote);
                          }
                          else
                          {
                            $("#time_slot").html();
                            $("#time_slot").html(response);

                          }
                           
                        }
                    });
            
              }

                            
        });
        $(document).on('change','.getSubjectsFromPeriod',function(){
        var val = $('#pupilsightProgramID ').val();
        var cls = $('#pupilsightYearGroupID').val();   
        var sec = $('#pupilsightRollGroupID').val();
        var type ="getSubjectsFromPeriod";
        if(cls != '' && val != '' ){
            $.ajax({
            url: 'attendanceSwitch.php',
            type: 'post',
            data: {
            val:val,
            cls: cls,
            sec:sec,
            type: type
            },
            async: true,
            success: function(response) {                          
            $("#pupilsightDepartmentID").html('');
            $("#pupilsightDepartmentID").html(response);
            }
            });
        }
        });
       /* function loadSubjects() {
            var pupilsightYearGroupID = $('select[name="pupilsightYearGroupID"] option:selected').val();
            if (pupilsightYearGroupID) {
                var type = "getSubjectbasedonclass";
                try {
                    $.ajax({
                        url: 'ajax_data.php',
                        type: 'post',
                        data: {
                            val: pupilsightYearGroupID,
                            type: type,
                            roleid:roleid,
                            pupilsightPersonID:pupilsightPersonID

                        },
                        async: true,
                        success: function(response) {
                            $("#pupilsightDepartmentID").html(response);
                            if (reloadCall) {
                                $("#pupilsightDepartmentID").val(_pupilsightDepartmentID);
                              
                            }
                        }
                    });
                } catch (ex) {
                    reloadCall = false;
                }
            }
        }*/
   function showCount(){
       var presents=0;
       var absents_name="Total absents : ";
       var absents=0;
       $(".slt_att").each(function() {
           var name = $(this).attr('data-id');
           if ($(this).val() != '') {
               var val = $(this).val();
               if(val=="Present" || val=="Present - Late"){
                   presents++;
               } else {
                   absents++;
                   absents_name+=name+",";
               }
           }
       });

       if(absents==0){
           $(".savePopUp").attr('id','attendanceSave');
           $(".savePopUp").text('Save Attendance');
       } else {
           $(".savePopUp").attr('id','savePopUp');
           $(".savePopUp").text('Save Attendance');
       }
       $("#presentsTotal").html(presents);
       $("#absentsTotal").html(absents);
       absents_name  = absents_name.replace(/,\s*$/, "");
       $("#absentsNames").attr("title",absents_name);
   }
       
      
    </script>
