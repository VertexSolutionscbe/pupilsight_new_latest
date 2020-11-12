<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Forms\Form;
use Pupilsight\Services\Format;
use Pupilsight\Forms\DatabaseFormFactory;
use Pupilsight\Domain\Students\StudentGateway;
use Pupilsight\Tables\DataTable;


//Module includes
require_once __DIR__ . '/moduleFunctions.php';

// set page breadcrumb
$page->breadcrumbs->add(__('Attendance Summary by Date'));

if (isActionAccessible($guid, $connection2, '/modules/Attendance/attendance_report_by_date.php') == false) {
    //Acess denied
    echo "<div class='alert alert-danger'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    //Proceed!
    echo '<h2>';
    echo __('Attendance Summary By Date');
    echo '</h2>';


  
  
    $pupilsightSchoolYearID = '';
    $pupilsightSchoolYearID = $_SESSION[$guid]['pupilsightSchoolYearID'];
    $pupilsightPersonID = $_SESSION[$guid]['pupilsightPersonID'];

        if($_POST){
        
         /*   $pupilsightProgramID =  $_POST['pupilsightProgramID'];
            $pupilsightYearGroupID =  $_POST['pupilsightYearGroupID'];
            $pupilsightRollGroupID =  $_POST['pupilsightRollGroupID'];
           */
          
            $stuId = $_POST['studentId'];
        } else {
            $pupilsightProgramID =  '';
            $pupilsightYearGroupID =  '';
            $pupilsightRollGroupID =  '';
          
            $stuId = '0';
        }


        $today = date('Y-m-d');

        $countClassAsSchool = getSettingByScope($connection2, 'Attendance', 'countClassAsSchool');
        $dateEnd = (isset($_REQUEST['end_date']))? dateConvert($guid, $_REQUEST['end_date']) : date('Y-m-d');
        $dateStart = (isset($_REQUEST['start_date']))? dateConvert($guid, $_REQUEST['start_date']) : date('Y-m-d', strtotime( $dateEnd.' -1 month') );
    
        // Correct inverse date ranges rather than generating an error
        if ($dateStart > $dateEnd) {
            $swapDates = $dateStart;
            $dateStart = $dateEnd;
            $dateEnd = $swapDates;
        }
    
        // Limit date range to the current school year
        if ($dateStart < $_SESSION[$guid]['pupilsightSchoolYearFirstDay']) {
            $dateStart = $_SESSION[$guid]['pupilsightSchoolYearFirstDay'];
        }
    
        if ($dateEnd > $_SESSION[$guid]['pupilsightSchoolYearLastDay']) {
            $dateEnd = $_SESSION[$guid]['pupilsightSchoolYearLastDay'];
        }
        $group = 'rollGroup';
       // $group = !empty($_REQUEST['group'])? $_REQUEST['group'] : '';
        $sort = !empty($_REQUEST['sort'])? $_REQUEST['sort'] : 'surname';
    
        $pupilsightCourseClassID = (isset($_REQUEST["pupilsightCourseClassID"]))? $_REQUEST["pupilsightCourseClassID"] : 0;
        $pupilsightRollGroupID = (isset($_REQUEST["pupilsightRollGroupID"]))? $_REQUEST["pupilsightRollGroupID"] : 0;
         $pupilsightProgramID = (isset($_REQUEST["pupilsightProgramID"]))? $_REQUEST["pupilsightProgramID"] : 0;
         $pupilsightYearGroupID = (isset($_REQUEST["pupilsightYearGroupID"]))? $_REQUEST["pupilsightYearGroupID"] : 0;
        $pupilsightYearGroupID = (isset($_REQUEST["pupilsightYearGroupID"]))? $_REQUEST["pupilsightYearGroupID"] : 0;
        $pupilsightDepartmentID = (isset($_REQUEST["pupilsightDepartmentID"]))? $_REQUEST["pupilsightDepartmentID"] : 0;
        


        
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

 
      $pupilsightAttendanceCodeID = (isset($_REQUEST["pupilsightAttendanceCodeID"]))? $_REQUEST["pupilsightAttendanceCodeID"] : 0;
        $reportType = (empty($pupilsightAttendanceCodeID))? 'types' : 'reasons';
  
    $form = Form::create('action', $_SESSION[$guid]['absoluteURL'].'/index.php','get');
    $form->setFactory(DatabaseFormFactory::create($pdo));

    $form->addHiddenValue('address', $_SESSION[$guid]['address']);
    $form->addHiddenValue('q', "/modules/".$_SESSION[$guid]['module']."/attendance_report_by_date.php");

  
    //$form->addHiddenValue('ayear', $ayear);    

        
    $row = $form->addRow();
      
  

        $col = $row->addColumn()->setClass('newdes');
            $col->addLabel('start_date', __('Start Date'))->addClass('dte');
            $col->addDate('start_date')->addClass('txtfield')->readonly()->required()->setValue(dateConvertBack($guid, $dateStart));   

        $col = $row->addColumn()->setClass('newdes');
            $col->addLabel('end_date', __('End Date'))->addClass('dte');
            $col->addDate('end_date')->addClass('txtfield')->readonly()->setValue(dateConvertBack($guid, $dateEnd))->required();
         
            $col = $row->addColumn()->setClass('newdes');
            $col->addLabel('pupilsightYearGroupID', __('Program *'));
            $col->addSelect('pupilsightProgramID')->fromArray($program)->selected($pupilsightProgramID)->required();
        

            $col = $row->addColumn()->setClass('newdes');
            $col->addLabel('pupilsightYearGroupID', __('Class'));
            $col->addSelectYearGroup('pupilsightYearGroupID')->selected($pupilsightYearGroupID)->required()->setClass('subPeriodWise');
        
        $col = $row->addColumn()->setClass('newdes');
            $col->addLabel('pupilsightRollGroupID', __('Section'));
            $col->addSelectRollGroup('pupilsightRollGroupID', $pupilsightSchoolYearID)->selected($pupilsightRollGroupID)->fromArray(array(''=>"Select Section"))->setClass('subPeriodWise');
  //  $col = $row->addColumn()->setClass('newdes');     
    $pupilsightDepartmentID = (isset($_REQUEST["pupilsightDepartmentID"]))? $_REQUEST["pupilsightDepartmentID"] : 0;
    $col = $row->addColumn()->setClass('newdes');
  
   $col-> addLabel('pupilsightDepartmentID', __('Subjects'))->setId($pupilsightDepartmentID);
    $col->addSelect('pupilsightDepartmentID')->fromArray($subjects)->required()->selected('pupilsightDepartmentID');
    
    $col = $row->addColumn()->setClass('newdes ');
    
   
    $col->addSubmit(__('Go'))->setClass('mrgin_tp ');   

    $row = $form->addRow();
    $col = $row->addColumn()->setClass('newdes');
   

  //      $row = $form->addRow()->setID('attendance_rep');
        $row->addFooter();
      

    echo $form->getOutput();

// Get attendance codes
try {
    if (!empty($pupilsightAttendanceCodeID)) {
        $dataCodes = array( 'pupilsightAttendanceCodeID' => $pupilsightAttendanceCodeID);
        $sqlCodes = "SELECT * FROM pupilsightAttendanceCode WHERE pupilsightAttendanceCodeID=:pupilsightAttendanceCodeID";
    } else {
        $dataCodes = array();
        $sqlCodes = "SELECT * FROM pupilsightAttendanceCode WHERE active = 'Y' AND reportable='Y' ORDER BY sequenceNumber ASC, name";
    }

    $resultCodes = $pdo->executeQuery($dataCodes, $sqlCodes);
} catch (PDOException $e) {
    echo "<div class='alert alert-danger'>".$e->getMessage().'</div>';
}

if ($resultCodes->rowCount() == 0) {
    echo "<div class='alert alert-danger'>";
    echo __('There are no attendance codes defined.');
    echo '</div>';
}
else if ( empty($dateStart) || empty($group)) {
    echo "<div class='alert alert-danger'>";
    echo __('There are no records to display.');
    echo '</div>';
} else if ($dateStart > $today || $dateEnd > $today) {
        echo "<div class='alert alert-danger'>";
        echo __('The specified date is in the future: it must be today or earlier.');
        echo '</div>';
} else {


    try {
        $dataSchoolDays = array( 'dateStart' => $dateStart, 'dateEnd' => $dateEnd, 'pupilsightSchoolYearID' => $_SESSION[$guid]['pupilsightSchoolYearID']);
        $sqlSchoolDays = "SELECT COUNT(DISTINCT CASE WHEN date>=pupilsightSchoolYear.firstDay AND date<=pupilsightSchoolYear.lastDay THEN date END) as total, COUNT(DISTINCT CASE WHEN date>=:dateStart AND date <=:dateEnd THEN date END) as dateRange FROM pupilsightAttendanceLogPerson, pupilsightSchoolYearTerm, pupilsightSchoolYear WHERE date>=pupilsightSchoolYearTerm.firstDay AND date <= pupilsightSchoolYearTerm.lastDay AND date <= NOW() AND pupilsightSchoolYear.pupilsightSchoolYearID=:pupilsightSchoolYearID";

        $resultSchoolDays = $connection2->prepare($sqlSchoolDays);
        $resultSchoolDays->execute($dataSchoolDays);
    } catch (PDOException $e) {
        echo "<div class='alert alert-danger'>".$e->getMessage().'</div>';
    }
    $schoolDayCounts = $resultSchoolDays->fetch();



    $data = array('dateStart' => $dateStart, 'dateEnd' => $dateEnd, 'pupilsightRollGroupID' => $pupilsightRollGroupID,
     'pupilsightSchoolYearID' => $_SESSION[$guid]['pupilsightSchoolYearID']);
    $sqlPieces = array();

    if ($reportType == 'types') {
        $attendanceCodes = array();

        $i = 0;
        
        while( $type = $resultCodes->fetch() ) {
            $typeIdentifier = "`".str_replace("`","``",$type['nameShort'])."`";
            
            $data['type'.$i] = $type['name'];
            $sqlPieces[] = "COUNT(DISTINCT CASE WHEN pupilsightAttendanceCode.name=:type".$i." THEN date END) AS ".$typeIdentifier;
            $attendanceCodes[ $type['direction'] ][] = $type;
            $i++;
        }
       // print_r($sqlPieces);
    }
    else if ($reportType == 'reasons') {
        $attendanceCodeInfo = $resultCodes->fetch();
        $attendanceReasons = explode(',', getSettingByScope($connection2, 'Attendance', 'attendanceReasons') );

        for($i = 0; $i < count($attendanceReasons); $i++) {
            $reasonIdentifier = "`".str_replace("`","``",$attendanceReasons[$i])."`";
            $data['reason'.$i] = $attendanceReasons[$i];
            $sqlPieces[] = "COUNT(DISTINCT CASE WHEN pupilsightAttendanceLogPerson.reason=:reason".$i." THEN date END) AS ".$reasonIdentifier;
        }

        $sqlPieces[] = "COUNT(DISTINCT CASE WHEN pupilsightAttendanceLogPerson.reason='' THEN date END) AS `No Reason`";
        $attendanceReasons[] = 'No Reason';
    }

    $sqlSelect = implode( ',', $sqlPieces );
// print_r($sqlSelect);
    //Produce array of attendance data
    try {
        $groupBy = 'GROUP BY pupilsightAttendanceLogPerson.pupilsightPersonID';
        $orderBy = 'ORDER BY surname, preferredName';
        if ($sort == 'preferredName')
            $orderBy = 'ORDER BY preferredName, surname';
        if ($sort == 'rollGroup')
            $orderBy = ' ORDER BY LENGTH(rollGroup), rollGroup, surname, preferredName';
     
        //     $data['pupilsightRollGroupID'] = $pupilsightRollGroupID;
        //     $data['pupilsightYearGroupID'] = $pupilsightYearGroupID;
        //     $data['pupilsightDepartmentID'] = $pupilsightDepartmentID;
        // //    echo $data;
     //print_r($pupilsightRollGroupID);die();

      // $sql = "SELECT * FROM pupilsightAttendanceLogPerson WHERE date = '".$dateStart."'";
      $sql = "SELECT pupilsightPerson.pupilsightPersonID AS stuid,pupilsightPerson.pupilsightPersonID, pupilsightRollGroup.nameShort AS rollGroup, surname, preferredName, $sqlSelect FROM pupilsightAttendanceLogPerson JOIN pupilsightAttendanceCode ON (pupilsightAttendanceLogPerson.type=pupilsightAttendanceCode.name) JOIN pupilsightPerson ON (pupilsightAttendanceLogPerson.pupilsightPersonID=pupilsightPerson.pupilsightPersonID) JOIN pupilsightStudentEnrolment ON (pupilsightStudentEnrolment.pupilsightPersonID=pupilsightPerson.pupilsightPersonID) JOIN pupilsightRollGroup ON (pupilsightStudentEnrolment.pupilsightRollGroupID=pupilsightRollGroup.pupilsightRollGroupID) WHERE date>=:dateStart AND date<=:dateEnd AND pupilsightStudentEnrolment.pupilsightSchoolYearID=:pupilsightSchoolYearID AND pupilsightStudentEnrolment.pupilsightRollGroupID =:pupilsightRollGroupID";


        if ( !empty($pupilsightAttendanceCodeID) ) {
            $data['pupilsightAttendanceCodeID'] = $pupilsightAttendanceCodeID;
            $sql .= ' AND pupilsightAttendanceCode.pupilsightAttendanceCodeID=:pupilsightAttendanceCodeID';
        }

        if ($countClassAsSchool == 'N' && $group != 'class') {
            $sql .= " AND NOT context='Class'";
        }

       $sql .= ' '. $groupBy . ' '. $orderBy;

        $result = $connection2->prepare($sql);
        $result->execute($data);
    } catch (PDOException $e) {
        echo "<div class='alert alert-danger'>".$e->getMessage().'</div>';
    }
//print_r($result->fetch());die();
    if ($result->rowCount() >=1) {
    //     echo "<div class='alert alert-danger'>";
    //     echo __('There are no records to display.');
    //     echo '</div>';
    // } else {

        echo '<h2>';
        echo __('Report Data').': '. Format::dateRangeReadable($dateStart, $dateEnd);        
        echo '</h2>';
        echo '<p style="color:#666;">';
        echo '<strong>' . __('Total number of school days to date:').' '.$schoolDayCounts['total'].'</strong><br/>';
        echo __('Total number of school days in date range:').' '.$schoolDayCounts['dateRange'];
    echo '</p>';

        echo "<div class='linkTop'>";

        echo "<a  id='attend_report_sms_send' data-type='atten_by_date' class=' sms_btn  btn btn-primary'>Send SMS</a>&nbsp;&nbsp; ";
        echo "<a target='_blank' href='".$_SESSION[$guid]['absoluteURL'].'/report.php?q=/modules/'.$_SESSION[$guid]['module'].'/report_summary_byDate_print.php&dateStart='.dateConvertBack($guid, $dateStart).'&dateEnd='.dateConvertBack($guid, $dateEnd).'&pupilsightCourseClassID='.$pupilsightCourseClassID.'&pupilsightRollGroupID='.$pupilsightRollGroupID.'&pupilsightAttendanceCodeID='. $pupilsightAttendanceCodeID .'&group=' . $group . '&sort=' . $sort . "'>".__('Print')."<img style='margin-left: 5px' title='".__('Print')."' src='./themes/".$_SESSION[$guid]['pupilsightThemeName']."/img/print.png'/></a>";
        echo '</div>';

        echo '<table id="attendance_tbl" class="table colorOddEven" >';

        echo "<tr class='head'>";
       
        echo '<th style="width:80px" rowspan=2>';
        echo  ' <input type="checkbox" name="checkall" id="checkall" value="on" class="floatNone checkall">';
        echo '</th>';
        echo '<th style="width:80px" rowspan=2>';
        echo __('SI No');
        echo '</th>';
        echo '<th style="width:80px" rowspan=2>';
        echo __('Roll Group');
        echo '</th>';
        echo '<th rowspan=2>';
        echo __('Name');
        echo '</th>';
        echo '<th rowspan=2>';
        echo __('ID');
        echo '</th>';
// print_r(count($attendanceCodes));
        if ($reportType == 'types') {
            echo '<th colspan='.count($attendanceCodes['In']).' class="columnDivider" style="text-align:center;">';
            echo __('IN');
            echo '</th>';
            echo '<th colspan='.count($attendanceCodes['Out']).' class="columnDivider" style="text-align:center;">';
            echo __('OUT');
            echo '</th>';
        } else if ($reportType == 'reasons') {
            echo '<th colspan='.count($attendanceReasons).' class="columnDivider" style="text-align:center;">';
            echo __($attendanceCodeInfo['name'] );
            echo '</th>';
        }
      

        echo '</tr>';


        echo '<tr class="head" style="min-height:80px;">';

        if ($reportType == 'types') {

            $href= $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.$_SESSION[$guid]['module'].'/report_summary_byDate.php&dateStart='.dateConvertBack($guid, $dateStart).'&dateEnd='.dateConvertBack($guid, $dateEnd).'&pupilsightCourseClassID='.$pupilsightCourseClassID.'&pupilsightRollGroupID='.$pupilsightRollGroupID.'&group=' . $group . '&sort=' . $sort;

            for( $i = 0; $i < count($attendanceCodes['In']); $i++ ) {
                echo '<th class="'.( $i == 0? 'verticalHeader columnDivider' : 'verticalHeader').'" title="'.$attendanceCodes['In'][$i]['scope'].'">';
                    echo '<a class="verticalText" href="'.$href.'&pupilsightAttendanceCodeID='.$attendanceCodes['In'][$i]['pupilsightAttendanceCodeID'].'">';
                    echo __($attendanceCodes['In'][$i]['name']);
                    echo '</a>';
                echo '</th>';
            }

            for( $i = 0; $i < count($attendanceCodes['Out']); $i++ ) {
                echo '<th class="'.( $i == 0? 'verticalHeader columnDivider' : 'verticalHeader').'" title="'.$attendanceCodes['Out'][$i]['scope'].'">';
                    echo '<a class="verticalText" href="'.$href.'&pupilsightAttendanceCodeID='.$attendanceCodes['Out'][$i]['pupilsightAttendanceCodeID'].'">';
                    echo __($attendanceCodes['Out'][$i]['name']);
                    echo '</a>';
                echo '</th>';
            }
        } else if ($reportType == 'reasons') {
            for( $i = 0; $i < count($attendanceReasons); $i++ ) {
                echo '<th class="'.( $i == 0? 'verticalHeader columnDivider' : 'verticalHeader').'">';
                    echo '<div class="verticalText">';
                    echo $attendanceReasons[$i];
                    echo '</div>';
                echo '</th>';
            }
        }
        // echo '<th rowspan=1>';
        // echo __('Present');
        // echo '</th>';
        // echo '<th rowspan=1>';
        // echo __('Absent');
        // echo '</th>';
        echo '</tr>';

$cnt=1;
/*echo "<pre>";
print_r($result->fetch());*/
        while ($row = $result->fetch()) {
           
          
            // ROW
            echo "<tr>";

            echo '<td>';
            echo  '<input type="checkbox" name="student_id[]" id="student_id[' . $row['pupilsightPersonID'] . ']" value="'.$row['pupilsightPersonID'].'" >';
        echo '</td>';
            echo '<td>';
            echo $cnt;
        echo '</td>';
            echo '<td>';
                echo $row['rollGroup'];
            echo '</td>';
            echo '<td>';
                echo '<a href="index.php?q=/modules/Attendance/report_studentHistory.php&pupilsightPersonID='.$row['pupilsightPersonID'].'" target="_blank">';
                echo formatName('', $row['preferredName'], $row['surname'], 'Student', ($sort != 'preferredName') );
                echo '</a>';
            echo '</td>';
            echo '<td>';
            echo $row['pupilsightPersonID'];
              echo '</td>';
            if ($reportType == 'types') {
                
                for( $i = 0; $i < count($attendanceCodes['In']); $i++ ) {
                 
                    
                    echo '<td data-present='.$row[ $attendanceCodes['In'][$i]['nameShort'] ].' class=" center '.( $row[ $attendanceCodes['In'][$i]['nameShort'] ] == 0? 'absent' : 'present').' '.( $i == 0? 'columnDivider' : '').'">';
                        echo $row[ $attendanceCodes['In'][$i]['nameShort'] ];
                    echo '</td>';
                }

                for( $i = 0; $i < count($attendanceCodes['Out']); $i++ ) {
                    echo '<td  data-absent='.$row[ $attendanceCodes['Out'][$i]['nameShort'] ].' class=" center '.( $row[ $attendanceCodes['Out'][$i]['nameShort'] ] == 0? 'absent' : 'present').' '.( $i == 0? 'columnDivider' : '').'">';
                        echo $row[ $attendanceCodes['Out'][$i]['nameShort'] ];
                    echo '</td>';
                }
            } else if ($reportType == 'reasons') {
                for( $i = 0; $i < count($attendanceReasons); $i++ ) {
                    echo '<td class="center '.( $i == 0? 'columnDivider' : '').'">';
                        echo $row[ $attendanceReasons[$i] ];
                    echo '</td>';
                }
            }
            // echo '<td class="pr_cnt" colspan=1>';
           
            //   echo '</td>';

            //   echo '<td class="ab_cnt" colspan=1>';
           
            //   echo '</td>';

            echo '</tr>';
            $cnt++;

        }
        if ($result->rowCount() == 0) {
            echo "<tr>";
            echo '<td colspan=5>';
            echo __('All students are present.');
            echo '</td>';
            echo '</tr>';
        }
        echo '</table>';


    }
}


//change gateway
/*
    $highestAction='Student Profile_full';
    $canViewFullProfile = ($highestAction == 'Student Profile_full' or $highestAction == 'View Student Profile_fullNoNotes');
    $canViewBriefProfile = isActionAccessible($guid, $connection2, '/modules/Students/student_view_details.php', 'View Student Profile_brief');

    $studentGateway = $container->get(StudentGateway::class);
    if ($canViewBriefProfile || $canViewFullProfile) {
        //Proceed!
        $search = isset($_GET['search'])? $_GET['search'] : '';
        $sort = isset($_GET['sort'])? $_GET['sort'] : 'surname,preferredName';
        $allStudents = isset($_GET['allStudents'])? $_GET['allStudents'] : '';
        
        $studentGateway = $container->get(StudentGateway::class);

     
        $criteria = $studentGateway->newQueryCriteria()
          //  ->searchBy($searchColumns, $search)
            ->sortBy(array_filter(explode(',', $sort)))
            ->filterBy('all', $canViewFullProfile ? $allStudents : '')
            ->fromPOST();

    $students = $studentGateway->queryStudentsBySchoolYear($criteria, $pupilsightSchoolYearID, $canViewFullProfile);
    echo "<div class='linkTop'>";
    echo "<a  id='attend_report_sms_send' data-type='atten_by_date' class=' sms_btn  btn btn-primary'>Send SMS</a>&nbsp;&nbsp; ";
    echo "<a target='_blank' href='".$_SESSION[$guid]['absoluteURL'].'/report.php?q=/modules/'.$_SESSION[$guid]['module'].'/'."'>".__('Print')."<img style='margin-left: 5px' title='".__('Print')."' src='./themes/".$_SESSION[$guid]['pupilsightThemeName']."/img/print.png'/></a>";
    echo '</div>';
    // DATA TABLE
    $table = DataTable::createPaginated('students_attendance', $criteria);

    $table->modifyRows($studentGateway->getSharedUserRowHighlighter());
    $canViewFullProfile ="";
   
   

    // COLUMNS
    $table->addCheckboxColumn('student_id',__(''))
    ->setClass('chkbox')
    ->notSortable();
    $table->addColumn('serial_number', __(' SI No'));    
      
    $table->addColumn('student', __('Name'))
    
        ->sortable(['surname', 'preferredName'])
        ->format(function ($person) {
            return Format::name('', $person['preferredName'], $person['surname'], 'Student', true, true) . '<br/><small><i>'.Format::userStatusInfo($person).'</i></small>';
        });
    $table->addColumn('pupilsightPersonID', __(' ID'));    
    // $table->addColumn('pupilsightStudentEnrolmentID', __('Enrolment Id')); 
    $table->addColumn('1', __('Date'));
    $table->addColumn('2', __('D1'));
    $table->addColumn('3', __('D2'));
    $table->addColumn('4', __('D3'));
    $table->addColumn('5', __('Present'));   
    $table->addColumn('6', __('Absent'));
    $table->addColumn('7', __('%'));
   

    

    echo $table->render($students);

  

    }  */
   
}
echo "<style>
.newdes
{
border: 1px #ffff !important;
}
</style>";
?>

<script type='text/javascript'>
   $(document).ready(function() {
    var  pr_cnt=0;
    var  pr = 0;
    var colCount = 0;
   
        $('#attendance_tbl ').find('tr:has(td)').find(' .present').each(function() {
        var present = $(this).attr('data-present');

        if (present == '1') {
            var pr = 1;
                
            } else {
                var pr = 0;
   
            }
            pr = + parseInt(pr);

            $(this).parent().find(".pr_cnt").html(pr);
            var absent = $(this).attr('data-absent');
            if (absent == '1') {
                var ab = 1;      
                    
                } else {
                    var ab = 0;                                
                }
                ab = + parseInt(ab);
               $(this).parent().find(".ab_cnt").html(ab);
        
    });

});
$(document).on('change','.subPeriodWise',function(){
    var val = $('#pupilsightProgramID ').val();
    var cls = $('#pupilsightYearGroupID').val();   
    var sec = $('#pupilsightRollGroupID').val();
    if(cls != '' && val != '' ){
        $.ajax({
            url: 'ajax_data.php',
            type: 'post',
            data: {
                val:val,
                cls: cls,
                sec:sec,
                type: "subPeriodWise"
            },
            async: true,
            success: function(response) {                             
                $("#pupilsightDepartmentID").html();
            $("#pupilsightDepartmentID").html(response);
            }
        });

    }
   

})
</script>
