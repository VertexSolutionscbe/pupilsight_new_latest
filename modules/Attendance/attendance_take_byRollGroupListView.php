<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Forms\Form;
use Pupilsight\Forms\DatabaseFormFactory;
use Pupilsight\Module\Attendance\AttendanceView;
use Pupilsight\Domain\Helper\HelperGateway;


//Module includes
require_once __DIR__ . '/moduleFunctions.php';
require_once __DIR__ . '/src/AttendanceView.php';
// set page breadcrumb
$page->breadcrumbs->add(__('Take Attendance By Section'));

if (isActionAccessible($guid, $connection2, '/modules/Attendance/attendance_take_byRollGroupListView.php') == false) {
    //Acess denied
    echo "<div class='alert alert-danger'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    $highestAction = getHighestGroupedAction($guid, $_GET['q'], $connection2);
    if ($highestAction == false) {
        echo "<div class='alert alert-danger'>";
        echo __('The highest grouped action cannot be determined.');
        echo '</div>';
    } else {
        if (isset($_GET['pupilsightYearGroupID'])) {
            $pupilsightYearGroupID = $_GET['pupilsightYearGroupID'];
        }
        if (isset($_GET['pupilsightRollGroupID'])) {
            $pupilsightRollGroupID = $_GET['pupilsightRollGroupID'];
        }
        if (isset($_GET['session'])) {
            $session= $_GET['session'];
        }
        if (isset($_GET['currentDate'])) {
            $currentDate = $_GET['currentDate'];
        }
        if (isset($_GET['return'])) {
            returnProcess($guid, $_GET['return'], null, array('error3' => __('Your request failed because the specified date is in the future, or is not a school day.')));
        }

        $attendance = new AttendanceView($pupilsight, $pdo);
        $HelperGateway = $container->get(HelperGateway::class);   
        $pupilsightProgramID='';
        $pupilsightYearGroupID = '';
        $pupilsightRollGroupID = '';
        $selsession = '';
        if (isset($_GET['pupilsightRollGroupID']) == false) {
            try {
                $data = array('pupilsightPersonIDTutor1' => $_SESSION[$guid]['pupilsightPersonID'], 'pupilsightPersonIDTutor2' => $_SESSION[$guid]['pupilsightPersonID'], 'pupilsightPersonIDTutor3' => $_SESSION[$guid]['pupilsightPersonID'], 'pupilsightSchoolYearID' => $_SESSION[$guid]['pupilsightSchoolYearID']);
                $sql = "SELECT pupilsightRollGroup.*, firstDay, lastDay, pupilsightProgramClassSectionMapping.pupilsightYearGroupID FROM pupilsightRollGroup JOIN pupilsightSchoolYear ON (pupilsightRollGroup.pupilsightSchoolYearID=pupilsightSchoolYear.pupilsightSchoolYearID) JOIN pupilsightProgramClassSectionMapping ON pupilsightRollGroup.pupilsightRollGroupID = pupilsightProgramClassSectionMapping.pupilsightRollGroupID WHERE (pupilsightPersonIDTutor=:pupilsightPersonIDTutor1 OR pupilsightPersonIDTutor2=:pupilsightPersonIDTutor2 OR pupilsightPersonIDTutor3=:pupilsightPersonIDTutor3) AND pupilsightRollGroup.pupilsightSchoolYearID=:pupilsightSchoolYearID";
                $result = $connection2->prepare($sql);
                $result->execute($data);
            } catch (PDOException $e) {
                echo "<div class='alert alert-danger'>".$e->getMessage().'</div>';
            }
            if ($result->rowCount() > 0) {
                $row = $result->fetch();
                $pupilsightProgramID = $row['pupilsightProgramID'];
                $pupilsightRollGroupID = $row['pupilsightRollGroupID'];
                $pupilsightYearGroupID = $row['pupilsightYearGroupID'];
                $selsession = $firstsession;
            }
        } else {
           
            $pupilsightProgramID =  isset($_GET['pupilsightProgramID'])?$_GET['pupilsightProgramID']:"";
            $pupilsightRollGroupID = isset($_GET['pupilsightRollGroupID'])?$_GET['pupilsightRollGroupID']:"";
            $pupilsightYearGroupID = isset($_GET['pupilsightYearGroupID'])?$_GET['pupilsightYearGroupID']:"";
            $selsession = isset($_GET['session'])?$_GET['session']:"";
        }

        $sqlp='SELECT a.session_no, a.session_name FROM attn_session_settings AS a LEFT JOIN attn_settings AS b ON(a.attn_settings_id =b.id) WHERE b.pupilsightProgramID="'.$pupilsightProgramID.'" AND  FIND_IN_SET("'.$pupilsightYearGroupID.'",b.pupilsightYearGroupID) > 0';
        $resultp = $connection2->query($sqlp);
        $rowdatasession = $resultp->fetchAll();

        $session=array();  
        $session2=array();  
        $session1=array(''=>'Select Session');
        if(!empty($rowdatasession)){
            $i = 1;
            $firstsession = '';
            foreach ($rowdatasession as $dt) {
                if($i == 1){
                    $firstsession = $dt['session_no'];
                }
                $session2[$dt['session_no']] = $dt['session_name'];
                $i++;
            }
             
        }
        $session= $session1 + $session2; 
        $pupilsightPersonID =   $_SESSION[$guid]['pupilsightPersonID'];
        $pupilsightRoleIDPrimary =$_SESSION[$guid]['pupilsightRoleIDPrimary'];
        $program = array();
        $program2 = array();
        $program1 = array('' => 'Select Program');
        //get subject class wise 
     
        $sqlt = 'SELECT a.pupilsightDepartmentID, b.name FROM assign_core_subjects_toclass AS a LEFT JOIN pupilsightDepartment as b ON a.pupilsightDepartmentID = b.pupilsightDepartmentID WHERE a.pupilsightProgramID = "'.$pupilsightProgramID.'" AND  a.pupilsightYearGroupID = "'.$pupilsightYearGroupID.'"';
        $resultt = $connection2->query($sqlt);  
        $subjects1 = $resultt->fetchAll();
          //check subject field
        $subject_mandatory="0";
        $sqlp_sub = 'SELECT att.*
        FROM attn_settings as att
        WHERE pupilsightProgramID="'.$pupilsightProgramID.'"  AND  FIND_IN_SET("'.$pupilsightYearGroupID.'",pupilsightYearGroupID) > 0';
        $result_sub = $connection2->query($sqlp_sub);
        $subj_mandatory = $result_sub->fetch();
        if(!empty($subj_mandatory['select_sub_mandatory'])){
           $subject_mandatory="1";
        }
        // ends subject check
        //close 
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
                
            $staff_person_id= Null;
            $disable_cls= '';
            $progrm_id="pupilsightProgramID";
            $class_id="pupilsightYearGroupIDA";
            $section_id= "pupilsightRollGroupID";
            $sqlp = 'SELECT p.pupilsightProgramID, p.name FROM pupilsightProgram AS p RIGHT JOIN attn_settings AS a ON(p.pupilsightProgramID =a.pupilsightProgramID) ';
            $resultp = $connection2->query($sqlp);
            $rowdataprog = $resultp->fetchAll();

            foreach ($rowdataprog as $dt) {
                $program2[$dt['pupilsightProgramID']] = $dt['name'];
            }
            $program = $program1 + $program2;

            $pupilsightProgramID =  isset($_GET['pupilsightProgramID'])?$_GET['pupilsightProgramID']:"";
            $pupilsightRollGroupID = isset($_GET['pupilsightRollGroupID'])?$_GET['pupilsightRollGroupID']:"";
            $pupilsightYearGroupID = isset($_GET['pupilsightYearGroupID'])?$_GET['pupilsightYearGroupID']:"";
       }
        if($_GET){             
            $pupilsightSchoolYearID = $_SESSION[$guid]['pupilsightSchoolYearID'];
            $classes =  $HelperGateway->getClassByProgram_Attconfig($connection2, $pupilsightProgramID);
            $sections =  $HelperGateway->getSectionByProgram_attConfig($connection2, $pupilsightYearGroupID,  $pupilsightProgramID, $pupilsightSchoolYearID);
            
        } else {      
            $classes = array('');
            $sections = array('');           
            $search = '';  
        }   
        $today = date('Y-m-d');
        $currentDate = isset($_GET['currentDate'])? dateConvert($guid, $_GET['currentDate']) : $today;

        echo '<h2>'.__('Choose Roll Group')."</h2>";

        $searchform = Form::create('filter', '', 'get');
        $searchform->setFactory(DatabaseFormFactory::create($pdo));
        $searchform->setClass('noIntBorder fullWidth');

        $searchform->addHiddenValue('q', '/modules/' . $_SESSION[$guid]['module'] . '/attendance_take_byRollGroupListView.php');

        $row = $searchform->addRow();

        //  $col = $row->addColumn()->setClass('newdes');
        //  $col->addLabel('pupilsightProgramID', __('Program'));
        //  $col->addSelect('pupilsightProgramID')->setId($progrm_id)->fromArray
        //  ($program)->required()->selected($pupilsightProgramID)->placeholder();
         
         $col = $row->addColumn()->setClass('newdes');
         $col->addLabel('pupilsightProgramID', __('Program'));
         $col->addSelect('pupilsightProgramID')->setId($progrm_id)->fromArray($program)->required()->selected($pupilsightProgramID)->placeholder();

        $col = $row->addColumn()->setClass('newdes');
        $col->addLabel('pupilsightYearGroupID', __('Class'));
        $col->addSelect('pupilsightYearGroupID')->setId($class_id)->fromArray($classes)->selected($pupilsightYearGroupID)->required()->addClass('load_configSession');
        $col->addTextField('pupilsightPersonID')->setId('staff_id')->addClass('nodisply')->setValue($pupilsightPersonID);

        $col = $row->addColumn()->setClass('newdes');
        $col->addLabel('pupilsightRollGroupID', __('Section'));
        $col->addSelect('pupilsightRollGroupID')->fromArray($sections)->required()->setId($section_id)->selected($pupilsightRollGroupID)->placeholder();

        $col = $row->addColumn()->setClass('newdes');
        $col->addLabel('currentDate', __('Date'))->addClass('dte');
        $col->addDate('currentDate')->required()->setValue(dateConvertBack($guid, $currentDate));

        if(!empty($session)){
            $col = $row->addColumn()->setClass('newdes');
            $col->addLabel('session', __('Session'));
            $col->addSelect('session')->fromArray($session)->selected($selsession)->required();
        } else {
            $form->addHiddenValue('session', '0');

        }
        

        // $col = $row->addColumn()->setClass('newdes');   
        // 

        // $row = $form->addRow();
        //     $row->addLabel('pupilsightRollGroupID', __('Roll Group'));
        //     $row->addSelectRollGroup('pupilsightRollGroupID', $_SESSION[$guid]['pupilsightSchoolYearID'])->required()->selected($pupilsightRollGroupID)->placeholder();

        // $row = $form->addRow();
        //     $row->addLabel('currentDate', __('Date'));
        //     $row->addDate('currentDate')->required()->setValue(dateConvertBack($guid, $currentDate));

       // $row = $form->addRow();
        $col = $row->addColumn()->setClass('newdes');
        
            $col->addSearchSubmit($pupilsight->session);

        echo $searchform->getOutput();
/////////////////////////////////////////////////////////////////////////////
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

                    if ($rollGroup['attendance'] == 'N') {
                        print "<div class='alert alert-danger'>" ;
                            print __("Attendance taking has been disabled for this roll group.") ;
                        print "</div>" ;
                    } else {

                        //Show attendance log for the current day
                        try {
                             if(!empty($session)){
                                $dataLog = array('pupilsightRollGroupID' => $pupilsightRollGroupID, 'session_no' => $selsession, 'pupilsightYearGroupID'=>$pupilsightYearGroupID,'date' => $currentDate.'%');
                                $sqlLog = 'SELECT * FROM pupilsightAttendanceLogRollGroup, pupilsightPerson WHERE pupilsightAttendanceLogRollGroup.pupilsightPersonIDTaker=pupilsightPerson.pupilsightPersonID AND pupilsightRollGroupID=:pupilsightRollGroupID AND pupilsightYearGroupID=:pupilsightYearGroupID AND session_no=:session_no AND date LIKE :date ORDER BY timestampTaken';
                                /*$dataLog = array('pupilsightRollGroupID' => $pupilsightRollGroupID, 'session_no' => $selsession,'pupilsightYearGroupID'=>$pupilsightYearGroupID, 'date' => $currentDate.'%');
                                 $sqlLog = 'SELECT * FROM pupilsightAttendanceLogRollGroup, pupilsightPerson ,pupilsightDepartment WHERE pupilsightAttendanceLogRollGroup.pupilsightPersonIDTaker=pupilsightPerson.pupilsightPersonID AND pupilsightAttendanceLogRollGroup.pupilsightDepartmentID=pupilsightDepartment.pupilsightDepartmentID AND pupilsightRollGroupID=:pupilsightRollGroupID AND pupilsightYearGroupID=:pupilsightYearGroupID AND  session_no=:session_no AND date LIKE :date ORDER BY timestampTaken';*/
                            } else {
                                $dataLog = array('pupilsightRollGroupID' => $pupilsightRollGroupID,'pupilsightYearGroupID'=>$pupilsightYearGroupID, 'date' => $currentDate.'%');
                                $sqlLog = 'SELECT * FROM pupilsightAttendanceLogRollGroup, pupilsightPerson WHERE pupilsightAttendanceLogRollGroup.pupilsightPersonIDTaker=pupilsightPerson.pupilsightPersonID AND pupilsightRollGroupID=:pupilsightRollGroupID AND  pupilsightYearGroupID=:pupilsightYearGroupID AND  date LIKE :date ORDER BY timestampTaken';
                            }
                            $resultLog = $connection2->prepare($sqlLog);
                            $resultLog->execute($dataLog);
                        } catch (PDOException $e) {
                            echo "<div class='alert alert-danger'>".$e->getMessage().'</div>';
                        }
                        
                        if ($resultLog->rowCount() < 1) {
                            echo "<div class='alert alert-danger'>";
                            //commented the below line on saying of anand and added alternat echo below it. commented by preetam
                            /*echo __('Attendance has not been taken for this group yet for the specified date. The entries below are a best-guess based on defaults and information put into the system in advance, not actual data.');*/
                            echo __('Attendance has not been taken for this group yet for the specified date.');
                            echo '</div>';
                        } else {
                            $check_role='SELECT role.name FROM pupilsightPerson as p LEFT JOIN pupilsightRole as role ON p.pupilsightRoleIDAll = role.pupilsightRoleID 
                            WHERE p.pupilsightPersonID ="'.$_SESSION[$guid]['pupilsightPersonID'].'" AND role.name="Administrator"';
                            $check_role= $connection2->query($check_role);
                            $role = $check_role->fetch();
                            $del="";
                            if(!empty($role['name'])){
                                $del='<a href="javascript:void(0)" style="color:red;float:right" id="clearAttendance" data-s="'.$selsession.'" data-g="'.$pupilsightRollGroupID.'" data-date="'.$currentDate.'"><i title="Clear the attendance" class="mdi mdi-trash-can-outline mdi-24px px-2"></i></a>';
                            }
                            echo "<div class='alert alert-sucess'>";
                            echo __('Attendance has been taken at the following times for the specified date for this group: '.$del);
                            echo '<ul>';
                            while ($rowLog = $resultLog->fetch()) {
                                echo '<li>'.sprintf(__('Recorded at %1$s on %2$s by %3$s.'), substr($rowLog['timestampTaken'], 11), dateConvertBack($guid, substr($rowLog['timestampTaken'], 0, 10)), formatName('', $rowLog['preferredName'], $rowLog['surname'], 'Staff', false, true));
                                if(!empty($rowLog['name'])){
                                    $subId = $rowLog['pupilsightDepartmentID'];
                                   echo 'subject  '.$rowLog['name'];
                                }
                                echo '</li>';
                            }
                            echo '</ul>';
                            echo '</div>';
                        }

?>

<script type="text/javascript">

$(document).on('click','#clearAttendance',function(){
var section_id = $(this).attr('data-s');
var group_id = $(this).attr('data-g');
var date = $(this).attr('data-date');
var type ="clearAttendance";
var r = confirm("Are you sure want to clear attendance ?");
if (r == true) {
$.ajax({
url: 'attendanceSwitch.php',
type: 'post',
data: {section_id:section_id,group_id:group_id,date:date,type:type},
async: true,
success: function(response) {
alert(response); 
window.location.reload();   
}
});
}
});

</script>

<?php 
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
                        //ends auto lock

                        //preetam
                        $sqlpr = "SELECT p.pupilsightProgramID, p.name, a.enable_sort_display_field_1, a.enable_sort_display_field_2,a.display_field_1,a.display_field_2 FROM pupilsightProgram AS p RIGHT JOIN attn_settings AS a ON(p.pupilsightProgramID =a.pupilsightProgramID) WHERE a.pupilsightProgramID=$pupilsightProgramID AND a.pupilsightYearGroupID=$pupilsightYearGroupID";
                        //print_r($sqlpr);
                        $resultpr = $connection2->query($sqlpr);
                        $rowdataprogr = $resultpr->fetchAll();
                        $enable_sort_display_field_1='';
                        $enable_sort_display_field_2='';

                        foreach ($rowdataprogr as $dtr) {
                             $enable_sort_display_field_1=$dtr['enable_sort_display_field_1'];
                             $enable_sort_display_field_2=$dtr['enable_sort_display_field_2'];
                             $display_field_1=$dtr['display_field_1'];
                             $display_field_2=$dtr['display_field_2'];
                        }
                        if($display_field_1=='Admission No'){
                            $display_field_1='admission_no';
                        }
                        if($display_field_2=='Admission No'){
                            $display_field_2='admission_no';
                        }
                        if($display_field_1=='Student ID'){
                            $display_field_1='pupilsightPersonID';
                        }
                        if($display_field_2=='Student ID'){
                            $display_field_2='pupilsightPersonID';
                        }
                        if($display_field_1=='Mother Name' || $display_field_1=='Father Name' || $display_field_1=='Class' || $display_field_1=='Date OF Birth'){
                            $display_field_1='';
                        }
                        if($display_field_2=='Mother Name' || $display_field_2=='Father Name' || $display_field_2=='Class' || $display_field_2=='Date OF Birth'){
                            $display_field_2='';
                        }

                        //Show roll group grid
                        try {
                            if($enable_sort_display_field_1=='1' AND $enable_sort_display_field_2=='1'){
                                $dataRollGroup = array('pupilsightProgramID'=>$pupilsightProgramID,'pupilsightYearGroupID'=>$pupilsightYearGroupID,'pupilsightRollGroupID' => $pupilsightRollGroupID, 'date' => $currentDate);
                                $sqlRollGroup = "SELECT pupilsightPerson.image_240,pupilsightPerson.gender,pupilsightPerson.dob,pupilsightPerson.preferredName, pupilsightPerson.officialName,pupilsightPerson.phone1, pupilsightPerson.pupilsightPersonID,pupilsightPerson.admission_no as admno,pupilsightYearGroup.name as classname FROM pupilsightStudentEnrolment INNER JOIN pupilsightPerson ON pupilsightStudentEnrolment.pupilsightPersonID=pupilsightPerson.pupilsightPersonID LEFT JOIN pupilsightYearGroup ON pupilsightStudentEnrolment.pupilsightYearGroupID=pupilsightYearGroup.pupilsightYearGroupID WHERE pupilsightRollGroupID=:pupilsightRollGroupID AND  pupilsightStudentEnrolment.pupilsightProgramID=:pupilsightProgramID AND  pupilsightStudentEnrolment.pupilsightYearGroupID=:pupilsightYearGroupID AND status='Full' AND (dateStart IS NULL OR dateStart<=:date) AND (dateEnd IS NULL  OR dateEnd>=:date)AND pupilsightPerson.pupilsightRoleIDPrimary=003 ORDER BY  $display_field_1, $display_field_2";
                            }elseif ($enable_sort_display_field_1=='1' AND $display_field_1!=''){
                                $dataRollGroup = array('pupilsightProgramID'=>$pupilsightProgramID,'pupilsightYearGroupID'=>$pupilsightYearGroupID,'pupilsightRollGroupID' => $pupilsightRollGroupID, 'date' => $currentDate);
                                $sqlRollGroup = "SELECT pupilsightPerson.image_240,pupilsightPerson.gender,pupilsightPerson.dob,pupilsightPerson.preferredName, pupilsightPerson.officialName,pupilsightPerson.phone1, pupilsightPerson.pupilsightPersonID,pupilsightPerson.admission_no as admno,pupilsightYearGroup.name as classname FROM pupilsightStudentEnrolment INNER JOIN pupilsightPerson ON pupilsightStudentEnrolment.pupilsightPersonID=pupilsightPerson.pupilsightPersonID LEFT JOIN pupilsightYearGroup ON pupilsightStudentEnrolment.pupilsightYearGroupID=pupilsightYearGroup.pupilsightYearGroupID WHERE pupilsightRollGroupID=:pupilsightRollGroupID AND  pupilsightStudentEnrolment.pupilsightProgramID=:pupilsightProgramID AND  pupilsightStudentEnrolment.pupilsightYearGroupID=:pupilsightYearGroupID AND status='Full' AND (dateStart IS NULL OR dateStart<=:date) AND (dateEnd IS NULL  OR dateEnd>=:date)AND pupilsightPerson.pupilsightRoleIDPrimary=003 ORDER BY $display_field_1";
                            }elseif ($enable_sort_display_field_2=='1' AND $display_field_2!=''){
                                $dataRollGroup = array('pupilsightProgramID'=>$pupilsightProgramID,'pupilsightYearGroupID'=>$pupilsightYearGroupID,'pupilsightRollGroupID' => $pupilsightRollGroupID, 'date' => $currentDate);
                                $sqlRollGroup = "SELECT pupilsightPerson.image_240,pupilsightPerson.gender,pupilsightPerson.dob,pupilsightPerson.preferredName, pupilsightPerson.officialName,pupilsightPerson.phone1, pupilsightPerson.pupilsightPersonID,pupilsightPerson.admission_no as admno,pupilsightYearGroup.name as classname FROM pupilsightStudentEnrolment INNER JOIN pupilsightPerson ON pupilsightStudentEnrolment.pupilsightPersonID=pupilsightPerson.pupilsightPersonID LEFT JOIN pupilsightYearGroup ON pupilsightStudentEnrolment.pupilsightYearGroupID=pupilsightYearGroup.pupilsightYearGroupID WHERE pupilsightRollGroupID=:pupilsightRollGroupID AND  pupilsightStudentEnrolment.pupilsightProgramID=:pupilsightProgramID AND  pupilsightStudentEnrolment.pupilsightYearGroupID=:pupilsightYearGroupID AND status='Full' AND (dateStart IS NULL OR dateStart<=:date) AND (dateEnd IS NULL  OR dateEnd>=:date)AND pupilsightPerson.pupilsightRoleIDPrimary=003 ORDER BY $display_field_2";
                            }else {
                                $dataRollGroup = array('pupilsightProgramID' => $pupilsightProgramID, 'pupilsightYearGroupID' => $pupilsightYearGroupID, 'pupilsightRollGroupID' => $pupilsightRollGroupID, 'date' => $currentDate);
                                $sqlRollGroup = "SELECT pupilsightPerson.image_240,pupilsightPerson.gender,pupilsightPerson.dob,pupilsightPerson.preferredName, pupilsightPerson.officialName,pupilsightPerson.phone1, pupilsightPerson.pupilsightPersonID,pupilsightPerson.admission_no as admno,pupilsightYearGroup.name as classname FROM pupilsightStudentEnrolment INNER JOIN pupilsightPerson ON pupilsightStudentEnrolment.pupilsightPersonID=pupilsightPerson.pupilsightPersonID LEFT JOIN pupilsightYearGroup ON pupilsightStudentEnrolment.pupilsightYearGroupID=pupilsightYearGroup.pupilsightYearGroupID WHERE pupilsightRollGroupID=:pupilsightRollGroupID AND  pupilsightStudentEnrolment.pupilsightProgramID=:pupilsightProgramID AND  pupilsightStudentEnrolment.pupilsightYearGroupID=:pupilsightYearGroupID AND status='Full' AND (dateStart IS NULL OR dateStart<=:date) AND (dateEnd IS NULL  OR dateEnd>=:date)AND pupilsightPerson.pupilsightRoleIDPrimary=003 ORDER BY rollOrder, officialName, preferredName";
                            }
                            //print_r($dataRollGroup);
                           // print_r($sqlRollGroup);
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
                             //get configuration details
                            $att_configuration="SELECT *FROM attn_settings WHERE pupilsightProgramID='".$pupilsightProgramID."' AND  FIND_IN_SET('".$pupilsightYearGroupID."',pupilsightYearGroupID) > 0";
                            $att_configuration = $connection2->query($att_configuration);
                            $att_configuration = $att_configuration->fetch();
                            //ends here configruration details
                            // Build the attendance log data per student
                            foreach ($students as $key => $student) {
                                
                                if(!empty($session)){
                                    
                                    $data = array('pupilsightPersonID' => $student['pupilsightPersonID'],'session_no' => $selsession, 'date' => $currentDate.'%');
                                    $sql = "SELECT type, reason, comment, context, timestampTaken FROM pupilsightAttendanceLogPerson
                                        JOIN pupilsightPerson ON (pupilsightAttendanceLogPerson.pupilsightPersonID=pupilsightPerson.pupilsightPersonID)
                                        WHERE pupilsightAttendanceLogPerson.pupilsightPersonID=:pupilsightPersonID AND session_no=:session_no
                                        AND date LIKE :date";
                                } else {
                                    $data = array('pupilsightPersonID' => $student['pupilsightPersonID'], 'date' => $currentDate.'%');
                                    $sql = "SELECT type, reason, comment, context, timestampTaken FROM pupilsightAttendanceLogPerson
                                        JOIN pupilsightPerson ON (pupilsightAttendanceLogPerson.pupilsightPersonID=pupilsightPerson.pupilsightPersonID)
                                        WHERE pupilsightAttendanceLogPerson.pupilsightPersonID=:pupilsightPersonID
                                        AND date LIKE :date";
                                }
                                

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

                            $form = Form::create('attendanceByRollGroup', $_SESSION[$guid]['absoluteURL'].'/modules/'.$_SESSION[$guid]['module']. '/attendance_take_byRollGroupProcess.php');
                            $form->setAutocomplete('off');

                            $form->addHiddenValue('q', '/modules/' . $_SESSION[$guid]['module'] . '/attendance_take_byRollGroupListView.php');
                            $form->addHiddenValue('address', $_SESSION[$guid]['address']);
                            $form->addHiddenValue('ProgramID', $pupilsightProgramID);
                            $form->addHiddenValue('pupilsightYearGroupID', $pupilsightYearGroupID);
                            $form->addHiddenValue('pupilsightRollGroupID', $pupilsightRollGroupID);
                            $form->addHiddenValue('session', $selsession);
                            $form->addHiddenValue('currentDate', $currentDate);
                            $form->addHiddenValue('count', count($students));
                            $form->addHiddenValue('type','attendanceByRollGroupFormData');
                            $form->addHiddenValue('sendSmsUser','');
                            $form->addHiddenValue('subject_mandatory_status',$subject_mandatory);
                              echo '
                           <select  required  class="btn-fill-md  bg-dodger-blue" style="background:#C5E3F1; ,color:#0f0f0f ; margin:10px"  name="pupilsightDepartmentID" id="pupilsightDepartmentID">
                               <option value="">--Please choose Subject--</option>';
                               foreach($subjects1 as $sub){
                                   if(!empty($sub['name'])){
                                       echo   '<option value='.$sub['pupilsightDepartmentID'];
                                       if($subId == $sub['pupilsightDepartmentID']){
                                           echo ' selected = "selected"';
                                       }

                                       
                                       echo '>' .$sub['name']. '</option> ';
                                   }
                           
                               } 
                           echo '</select><br>
                           ';
                            $form->addRow()->addHeading(__('Take Attendance') . ': '. htmlPrep($rollGroup['name']). '<span style="position: absolute;right: 30px;"><a >List View</a> | <a href="index.php?q=/modules/'.$_SESSION[$guid]['module']. '/attendance_take_byRollGroup.php&pupilsightProgramID='.$pupilsightProgramID.'&pupilsightYearGroupID='.$pupilsightYearGroupID.'&pupilsightRollGroupID='.$pupilsightRollGroupID.'&session='.$selsession.'&currentDate='.$currentDate.'">Grid View</a></span>');

                            $grid = $form->addRow()->addGrid('attendance')->setBreakpoints('w-1/2 sm:w-1/4 md:w-1/5 lg:w-1/4');
                            $sl = 1;
                                $row = $form->addRow()->setID('seatdiv')->addClass('seatdiv'); 

                                    $col = $row->addColumn()->setClass('newdes');
                                    $col->addLabel('sl_no', __('Sl No'))->addClass('dte'); 

                                    $col = $row->addColumn()->setClass('newdes');
                                    $col->addLabel('name', __('Name'))->addClass('dte'); 
                                    //culcum 1
                                    if(!empty($att_configuration['display_field_1'])){
                                       if($att_configuration['display_field_1']=="Admission No"){
                                            $col = $row->addColumn()->setClass('newdes');
                                            $col->addLabel('id', __('Admission no'))->addClass('dte');
                                       } else if($att_configuration['display_field_1']=="Student ID"){
                                            $col = $row->addColumn()->setClass('newdes');
                                            $col->addLabel('Student ID', __('Student ID'))->addClass('dte');
                                       } else if($att_configuration['display_field_1']=="gender"){
                                            $col = $row->addColumn()->setClass('newdes');
                                            $col->addLabel('Gender', __('Gender'))->addClass('dte');
                                       } else if($att_configuration['display_field_1']=="Mother Name"){
                                            $col = $row->addColumn()->setClass('newdes');
                                            $col->addLabel('Mother Name', __('Mother Name'))->addClass('dte');
                                       } else if($att_configuration['display_field_1']=="Father Name"){
                                            $col = $row->addColumn()->setClass('newdes');
                                            $col->addLabel('Father Name', __('Father Name'))->addClass('dte');
                                       } else if($att_configuration['display_field_1']=="Date OF Birth"){

                                            $col = $row->addColumn()->setClass('newdes');
                                            $col->addLabel('Father Name', __('Date OF Birth'))->addClass('dte');

                                       } else if($att_configuration['display_field_1']=="Class"){

                                            $col = $row->addColumn()->setClass('newdes');
                                            $col->addLabel('Class', __('Class'))->addClass('dte');

                                       } else if($att_configuration['display_field_1']=="Section"){

                                            $col = $row->addColumn()->setClass('newdes');
                                            $col->addLabel('Section', __('Section'))->addClass('dte');

                                       }

                                    }
                                    //culume1 end

                                    //culcum 2
                                    if(!empty($att_configuration['display_field_2'])){
                                       if($att_configuration['display_field_2']=="Admission No"){

                                            $col = $row->addColumn()->setClass('newdes');
                                            $col->addLabel('id', __('Admission no'))->addClass('dte');

                                       } else if($att_configuration['display_field_2']=="Student ID"){

                                            $col = $row->addColumn()->setClass('newdes');
                                            $col->addLabel('Student ID', __('Student ID'))->addClass('dte');

                                       } else if($att_configuration['display_field_2']=="gender"){

                                            $col = $row->addColumn()->setClass('newdes');
                                            $col->addLabel('Gender', __('Gender'))->addClass('dte');

                                       } else if($att_configuration['display_field_2']=="Mother Name"){

                                            $col = $row->addColumn()->setClass('newdes');
                                            $col->addLabel('Mother Name', __('Mother Name'))->addClass('dte');

                                       } else if($att_configuration['display_field_2']=="Father Name"){

                                            $col = $row->addColumn()->setClass('newdes');
                                            $col->addLabel('Father Name', __('Father Name'))->addClass('dte');

                                       } else if($att_configuration['display_field_2']=="Date OF Birth"){

                                            $col = $row->addColumn()->setClass('newdes');
                                            $col->addLabel('Date OF Birth', __('Date OF Birth'))->addClass('dte');

                                       } else if($att_configuration['display_field_2']=="Class"){

                                            $col = $row->addColumn()->setClass('newdes');
                                            $col->addLabel('Class', __('Class'))->addClass('dte');

                                       } else if($att_configuration['display_field_2']=="Section"){

                                            $col = $row->addColumn()->setClass('newdes');
                                            $col->addLabel('Section', __('Section'))->addClass('dte');

                                       }

                                    }
                                    //culume2 end
                                   /* $col = $row->addColumn()->setClass('newdes');
                                    $col->addLabel('id', __('Admission no'))->addClass('dte'); 

                                    $col = $row->addColumn()->setClass('newdes');
                                    $col->addLabel('class', __('Class'))->addClass('dte'); 

                                    $col = $row->addColumn()->setClass('newdes');
                                    $col->addLabel('section', __('Section'))->addClass('dte'); 
                                     */
                                    /*if(!empty($session)){
                                        $col = $row->addColumn()->setClass('newdes');
                                        $col->addLabel('sessionname', __('Session'))->addClass('dte');
                                    }*/

                                    $col = $row->addColumn()->setClass('newdes');
                                    $col->addLabel('attendance', __('Attendance'))->addClass('dte'); 
                                    
                                    $col = $row->addColumn()->setClass('newdes');
                                    $col->addLabel('reason', __('Reason'))->addClass('dte'); 

                                    $col = $row->addColumn()->setClass('newdes');
                                    $col->addLabel('remark', __('Remark'))->addClass('dte'); 
                     
                            foreach ($students as $student) {
                                $form->addHiddenValue($count . '-pupilsightPersonID', $student['pupilsightPersonID']);
                                $form->addHiddenValue($count . '-admno', $student['admno']);
                                $form->addHiddenValue($count . '-phone1', $student['phone1']);
                                $form->addHiddenValue($count . '-pupilsightPersonName', $student['officialName']);
                                $form->addHiddenValue($count . '-class', $student['classname']);
                                $form->addHiddenValue($count . '-section', $rollGroup['name']);
                                    $row = $form->addRow()->setID('seatdiv')->addClass('seatdiv'); 

                                    $col = $row->addColumn()->setClass('newdes customize_input');
                                    $col->addTextField('sl_No')->required()->readonly()->setValue($count+1);

                                    $col = $row->addColumn()->setClass('newdes customize_input');
                                    $col->addWebLink(formatName('', htmlPrep($student['officialName']), htmlPrep(''), 'Student', false))
                                         ->setURL('index.php?q=/modules/Students/student_view_details.php')
                                         ->addParam('pupilsightPersonID', $student['pupilsightPersonID'])
                                         ->addParam('subpage', 'Attendance')
                                         ->setClass('pt-2 font-bold underline');
                                   //col 1 start
                                    if(!empty($att_configuration['display_field_1'])){
                                       if($att_configuration['display_field_1']=="Admission No"){
                                            $col = $row->addColumn()->setClass('newdes');
                                       $col->addTextField('id')->readonly()->setValue($student['admno']);
                                       } else if($att_configuration['display_field_1']=="Student ID"){
                                             $col = $row->addColumn()->setClass('newdes');
                                            $col->addTextField('Student ID')->readonly()->setValue($student['pupilsightPersonID']);
                                       } else if($att_configuration['display_field_1']=="gender"){
                                            $col = $row->addColumn()->setClass('newdes');
                                            $col->addTextField('gender')->readonly()->setValue($student['gender']);
                                       } else if($att_configuration['display_field_1']=="Mother Name"){
                                            //geting mother name 
                                         $name=$HelperGateway->getParentNameByPupilsightPersonID($connection2, $student['pupilsightPersonID'],'Mother');
                                           $col = $row->addColumn()->setClass('newdes');
                                            $col->addTextField('Father Name')->readonly()->setValue($name);
                                       } else if($att_configuration['display_field_1']=="Father Name"){
                                        //geting father name
                                       $name=$HelperGateway->getParentNameByPupilsightPersonID($connection2, $student['pupilsightPersonID'],'Father');
                                           $col = $row->addColumn()->setClass('newdes');
                                            $col->addTextField('Father Name')->readonly()->setValue($name);
                                       } else if($att_configuration['display_field_1']=="Date OF Birth"){
                                          $col = $row->addColumn()->setClass('newdes');
                                          $dob=$student['dob'];
                                          /*if(!empty($student['dob'])){
                                            $dob=date($dob,'d/m/Y');
                                          }*/
                                          $col->addTextField('class')->readonly()->setValue($dob);
                                       } else if($att_configuration['display_field_1']=="Class"){

                                      $col = $row->addColumn()->setClass('newdes');
                                    $col->addTextField('class')->readonly()->setValue($student['classname']);

                                       } else if($att_configuration['display_field_1']=="Section"){

                                    $col = $row->addColumn()->setClass('newdes');
                                    $col->addTextField('section')->readonly()->setValue($rollGroup['name']);

                                       }

                                    } 
                                    //ends col 1
                                    //col 2 start 
                                    if(!empty($att_configuration['display_field_2'])){
                                       if($att_configuration['display_field_2']=="Admission No"){

                                            $col = $row->addColumn()->setClass('newdes');
                                       $col->addTextField('id')->readonly()->setValue($student['admno']);

                                       } else if($att_configuration['display_field_2']=="Student ID"){

                                             $col = $row->addColumn()->setClass('newdes');
                                            $col->addTextField('Student ID')->readonly()->setValue($student['pupilsightPersonID']);


                                       } else if($att_configuration['display_field_2']=="gender"){

                                            $col = $row->addColumn()->setClass('newdes');
                                            $col->addTextField('gender')->readonly()->setValue($student['gender']);

                                       } else if($att_configuration['display_field_2']=="Mother Name"){

                                        //geting Mother name
                                           $name=$HelperGateway->getParentNameByPupilsightPersonID($connection2, $student['pupilsightPersonID'],'Mother');
                                           $col = $row->addColumn()->setClass('newdes');
                                            $col->addTextField('Father Name')->readonly()->setValue($name);

                                       } else if($att_configuration['display_field_2']=="Father Name"){
                                        //geting father name
                                             $name=$HelperGateway->getParentNameByPupilsightPersonID($connection2, $student['pupilsightPersonID'],'Father');
                                            $col = $row->addColumn()->setClass('newdes');
                                            $col->addTextField('Father Name')->readonly()->setValue($name);

                                       } else if($att_configuration['display_field_2']=="Date OF Birth"){

                                          $col = $row->addColumn()->setClass('newdes');
                                          $dob=$student['dob'];
                                          /*if(!empty($student['dob'])){
                                            echo $dob=date($dob,'d/m/Y');
                                          }*/
                                       $col->addTextField('class')->readonly()->setValue($dob);

                                       } else if($att_configuration['display_field_2']=="Class"){

                                     $col = $row->addColumn()->setClass('newdes');
                                    $col->addTextField('class')->readonly()->setValue($student['classname']);

                                       } else if($att_configuration['display_field_2']=="Section"){

                                    $col = $row->addColumn()->setClass('newdes');
                                    $col->addTextField('section')->readonly()->setValue($rollGroup['name']);

                                       }

                                    } 
                                    //ends col 2 
                                      /*
                                    $col = $row->addColumn()->setClass('newdes');
                                    $col->addTextField('id')->readonly()->setValue($student['admno']);

                                    $col = $row->addColumn()->setClass('newdes');
                                    $col->addTextField('class')->readonly()->setValue($student['classname']);

                                    $col = $row->addColumn()->setClass('newdes');
                                    $col->addTextField('section')->readonly()->setValue($rollGroup['name']);*/

                                  /*  if(!empty($session)){
                                        $sqlse = 'SELECT session_name FROM attn_session_settings WHERE session_no = '.$selsession.' ';
                                        $resultse = $connection2->query($sqlse);
                                        $sessname = $resultse->fetch();
                                        $col = $row->addColumn()->setClass('newdes');
                                        $col->addTextField('sessionname')->readonly()->setValue($sessname['session_name']);
                                    }*/
                                    //remove quotes 
                                    $stdName=htmlentities($student['officialName'], ENT_QUOTES);
                                    $col = $row->addColumn()->setClass('newdes');
                                    $col->addSelect($count.'-type')->addClass('txtfield slt_att')->fromArray(array_keys($attendance->getAttendanceTypes()))->selected($student['log']['type'])->addData('id',$stdName);
                                    
                                    $col = $row->addColumn()->setClass('newdes');
                                    $col->addSelect($count.'-reason')->fromArray($attendance->getAttendanceReasons())->selected($student['log']['reason']);

                                    $col = $row->addColumn()->setClass('newdes');
                                    $col->addTextField($count.'-comment')->maxLength(255)->setValue($student['log']['comment']);
                               

                                // $cell = $grid->addCell()
                                //     ->setClass('text-center py-2 px-1 -mr-px -mb-px flex flex-col justify-between')
                                //     ->addClass($student['cellHighlight']);

                                // $cell->addContent(getUserPhoto($guid, $student['image_240'], 75));
                                // $cell->addWebLink(formatName('', htmlPrep($student['preferredName']), htmlPrep($student['surname']), 'Student', false))
                                //      ->setURL('index.php?q=/modules/Students/student_view_details.php')
                                //      ->addParam('pupilsightPersonID', $student['pupilsightPersonID'])
                                //      ->addParam('subpage', 'Attendance')
                                //      ->setClass('pt-2 font-bold underline');
                                // $cell->addContent($student['absenceCount'])->wrap('<div class="text-xxs italic py-2">', '</div>');
                                // $cell->addSelect($count.'-type')
                                //      ->fromArray(array_keys($attendance->getAttendanceTypes()))
                                //      ->selected($student['log']['type'])
                                //      ->setClass('mx-auto float-none w-32 m-0 mb-px');
                                // $cell->addSelect($count.'-reason')
                                //      ->fromArray($attendance->getAttendanceReasons())
                                //      ->selected($student['log']['reason'])
                                //      ->setClass('mx-auto float-none w-32 m-0 mb-px');
                                // $cell->addTextField($count.'-comment')
                                //      ->maxLength(255)
                                //      ->setValue($student['log']['comment'])
                                //      ->setClass('mx-auto float-none w-32 m-0 mb-2');
                                // $cell->addContent($attendance->renderMiniHistory($student['pupilsightPersonID'], 'Roll Group'));

                                $sl++;
                                $count++;
                            }

                            $form->addRow()->addAlert(__('Total students:').' '. $count, 'success')->setClass('right')
                                ->append('<br/><span title="'.__('e.g. Present or Present - Late').'">'.__('Total students present in room:').'</span>&nbsp;<span id="presentsTotal">'. $countPresent.'</span>')
                                ->append('<br/><span title="'.__('e.g. not Present and not Present - Late').'">'.__('Total students absent from room:').'</span>&nbsp;<span id="absentsTotal">'. ($count-$countPresent).'</span> <i id="absentsNames" class="fa fa-eye" title="Total absents :" aria-hidden="true"></i>')
                                ->wrap('<b>', '</b>');

                                $sql1 = 'SELECT a.session_no, a.session_name FROM attn_session_settings AS a LEFT JOIN attn_settings AS b ON(a.attn_settings_id =b.id) WHERE b.pupilsightProgramID="'.$pupilsightProgramID.'" AND a.session_no!="'.$selsession.'" AND  FIND_IN_SET("'.$pupilsightYearGroupID.'",b.pupilsightYearGroupID) > 0';
                                $sen = $connection2->query($sql1);
                                $copy_this_too = $sen->fetchAll();
                                if(!empty($copy_this_too)){
                                $row=$form->addRow();
                                $row->addLabel('copy_this_too', __('Copy this too'));
                                $opt="";
                                foreach ($copy_this_too as $val) {
                                $opt.='<label><input type="checkbox" name="capy_to[]" value="'.$val['session_no'].'"> '.$val['session_name'].' </label> &nbsp;&nbsp;';
                                }
                                $row->addContent($opt);
                                }
                                $row = $form->addRow();
                            // Drop-downs to change the whole group at once
                            $row->addButton(__('Change All').'?')->addData('toggle', '.change-all')->addClass('w-32 m-px sm:self-center');

                            $col = $row->addColumn()->setClass('change-all hidden flex flex-col sm:flex-row items-stretch sm:items-center');
                                $col->addSelect('set-all-type')->fromArray(array_keys($attendance->getAttendanceTypes()))->addClass('m-px');
                                $col->addSelect('set-all-reason')->fromArray($attendance->getAttendanceReasons())->addClass('m-px');
                                $col->addTextField('set-all-comment')->maxLength(255)->addClass('m-px');
                            $col->addButton(__('Apply'))->setID('set-all');
                            //$col->addContent('<a href="javascript:void(0)" class="thickbox btn btn-primary">Submit</a>');
                            //$row->addSubmit();
                        if(!empty($subj_mandatory['enable_sms_absent'])){
                        $row->addContent('<a href="javascript:void(0)" id="savePopUp" class="btn btn-primary savePopUp">Submit</a><a id="saveFormModel" href="fullscreen.php?q=/modules/Attendance/model_for_save_attendance.php" style="display:none" class="thickbox"></a>');
                        } else {
                             $row->addContent('<a href="javascript:void(0)" id="attendanceSave" class="btn btn-primary">Save Attendance</a>');
                        }
                            echo $form->getOutput();
                        }
                    }
                }
            }
        }
    }
}
?>
<style>
.dsble_attr
{
    pointer-events: none;
}
.nodisply 
{
    display: none;
}
</style>
<script type="text/javascript">
$(document).on('change','#pupilsightProgramID',function(){
  var val = $(this).val();
  var type = "attendanceConfigCls";
  if(val != ""){
    $.ajax({
             url: 'ajax_data.php',
                type: 'post',
                data: { val: val,type:type },
                async: true,
                success: function(response)
                {     
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
            data: { val: id, type: type, pid: pid },
            async: true,
            success: function(response) {
                $("#pupilsightRollGroupID").html();
                $("#pupilsightRollGroupID").html(response);
            }
        })
    });
    //get session based on program and class
$(document).on('change', '.by_class', function(){
    var id = $('.program_class option:selected').val();
    var cid = $('.by_class option:selected').val();
    
   // alert(cid);
    $.ajax({
        url: 'ajax_data.php',
        type: 'post',
        data: { val: id, type: "getsessionbyclass" ,cid:cid},
        async: true,
        success: function(response) {
            $("#session").html();
            $("#session").html(response);
        }
    })
});

 $(document).on('change','.slt_att',function(){
      showCount();
    });
$(document).on('click','#savePopUp',function(){
  var formData = $("#attendanceByRollGroup").serialize();
  var subject_mandatory_status= $("input[name=subject_mandatory_status]").val();
  var pupilsightDepartmentID=$("#pupilsightDepartmentID").val();
  if(pupilsightDepartmentID!="" || subject_mandatory_status=="0"){
    $.ajax({
            url: 'attendanceSwitch.php',
            type: 'post',
            data: formData,
            async: true,
            success: function(response) {
                $("#saveFormModel").click();
            }
        });
} else {
    alert("Please select subject");
}
});
$(document).on('click','#attendanceSave',function(){
   var type="saveAttendance";
   var pupilsightDepartmentID=$("#pupilsightDepartmentID").val();
   var url=$('#attendanceByRollGroup').attr('action');
   var sms_usrs = [];
   var formData="type="+type+"&pupilsightDepartmentID="+pupilsightDepartmentID+"&sms_usrs="+sms_usrs+"&"+$("#attendanceByRollGroup").serialize();
//find("input[name!=type]")
$("#preloader").show();
setTimeout(function(){
        $.ajax({
        url: url,
        type: 'post',
        data: formData,
        async: true,
        success: function(response) {
        $("#preloader").hide();
        var obj=JSON.parse(response);
        if(obj.status=="success"){
        alert(obj.msg);
        window.location.reload();
        } else {
        alert(obj.msg);
        window.location.reload();
        }
        }
        });
    },2000);
});
$(document).on('click','#widthSmsAttendanceSave',function(){
   var type="saveAttendance";
   var url=$('#attendanceByRollGroup').attr('action');
   var pupilsightDepartmentID=$("#pupilsightDepartmentID").val();
   var sms_usrs = [];
    $.each($("input[name='sms_users']:checked"), function() {
    sms_usrs.push($(this).val());
    });
    var sms_usrs = sms_usrs.join(", ");
    if(sms_usrs!=""){
   var formData="type="+type+"&pupilsightDepartmentID="+pupilsightDepartmentID+"&sms_usrs="+sms_usrs+"&"+$("#attendanceByRollGroup").serialize();
$("#preloader").show();
setTimeout(function(){
    $.ajax({
        url: url,
        type: 'post',
        data: formData,
        async: true,
        success: function(response) {
            alert(response);
            $("#preloader").hide();
            var obj=JSON.parse(response);
            if(obj.status=="success"){
            alert(obj.msg);
            window.location.reload();
            } else {
            alert(obj.msg);
            window.location.reload();
            }     
        }
    });
    },1000);
    } else {
    alert("Please select absent list");
    }
});
$(document).on('click','#set-all',function(){
       showCount();
});

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
    setTimeout(function(){
        showCount();
    },2000);

$(document).on('change','.load_configSession',function(){
var id = $('#pupilsightProgramID').val();
var pupilsightYearGroupID = $('#pupilsightYearGroupIDA').val();
var type = 'getsessionConfigured';
$.ajax({
    url: 'ajax_data.php',
    type: 'post',
    data: { val: id,pupilsightYearGroupID:pupilsightYearGroupID, type: type },
    async: true,
    success: function(response) {
        $("#session").html();
        $("#session").html(response);
    }
});
});
</script>