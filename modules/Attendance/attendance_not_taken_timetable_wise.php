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
$page->breadcrumbs->add(__('Attendance Not Taken Period Wise'));

if (isActionAccessible($guid, $connection2, '/modules/Attendance/attendance_not_taken_timetable_wise.php') == false) {
    //Acess denied
    echo "<div class='alert alert-danger'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    //Proceed!
    echo '<h2>';
    echo __('Attendance Not Taken');
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
        $pupilsightYearGroupID = (isset($_REQUEST["pupilsightYearGroupID"]))? $_REQUEST["pupilsightYearGroupID"] : 0;
      
        


        
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
    $form->addHiddenValue('q', "/modules/".$_SESSION[$guid]['module']."/attendance_not_taken_timetable_wise.php");

  
    //$form->addHiddenValue('ayear', $ayear); 
        
    $row = $form->addRow();
        $col = $row->addColumn()->setClass('newdes');
            $col->addLabel('start_date', __('Start Date'))->addClass('dte');
            $col->addDate('start_date')->addClass('txtfield')->readonly()->required()->setValue(dateConvertBack($guid, $dateStart));   

        $col = $row->addColumn()->setClass('newdes');
            $col->addLabel('end_date', __('End Date'))->addClass('dte');
            $col->addDate('end_date')->addClass('txtfield')->readonly()->setValue(dateConvertBack($guid, $dateEnd))->required();


            $col = $row->addColumn()->setClass('newdes');
            $col->addLabel('pupilsightYearGroupID', __('Class'));
            $col->addSelectYearGroup('pupilsightYearGroupID')->selected($pupilsightYearGroupID)->required();
        
        $col = $row->addColumn()->setClass('newdes');
            $col->addLabel('pupilsightRollGroupID', __('Section'));
            $col->addSelectRollGroup('pupilsightRollGroupID', $pupilsightSchoolYearID)->required()->selected($pupilsightRollGroupID);
          
        $col = $row->addColumn()->setClass('newdes');
        $col->addLabel('pupilsightRollGroupID', __(''));
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



    //Produce array of attendance data
    try {
    
      $data = array('dateStart' => $dateStart, 'dateEnd' => $dateEnd,'pupilsightRollGroupID'=>$pupilsightRollGroupID,'pupilsightSchoolYearID' => $_SESSION[$guid]['pupilsightSchoolYearID']);
            $sql = "SELECT * FROM  pupilsightAttendanceLogPerson JOIN pupilsightAttendanceCode ON (pupilsightAttendanceLogPerson.type=pupilsightAttendanceCode.name) JOIN pupilsightPerson ON (pupilsightAttendanceLogPerson.pupilsightPersonID=pupilsightPerson.pupilsightPersonID) JOIN pupilsightStudentEnrolment ON (pupilsightStudentEnrolment.pupilsightPersonID=pupilsightPerson.pupilsightPersonID) JOIN pupilsightRollGroup ON (pupilsightStudentEnrolment.pupilsightRollGroupID=pupilsightRollGroup.pupilsightRollGroupID) WHERE date>=:dateStart AND date<=:dateEnd AND pupilsightStudentEnrolment.pupilsightSchoolYearID=:pupilsightSchoolYearID  AND pupilsightStudentEnrolment.pupilsightRollGroupID =:pupilsightRollGroupID";

        $result = $connection2->prepare($sql);
        $result->execute($data); 
        // print_r($result->fetchAll());
        // print_r(array_unique($result->fetchAll()));
    } catch (PDOException $e) {
        echo "<div class='alert alert-danger'>".$e->getMessage().'</div>';
    }

    $myYearMonth = '2020-04-02';
    $start = new DateTime(date($dateStart, strtotime($myYearMonth)));
    $end = new DateTime(date($dateEnd, strtotime($myYearMonth)));
    
    $diff = DateInterval::createFromDateString('1 day');
    $periodStart = new DatePeriod($start, $diff, $end);
  if($result->rowCount() >= 1) {
    echo '<h2>';
    echo __('Report Data').': '. Format::dateRangeReadable($dateStart, $dateEnd);        
    echo '</h2>';

    echo '<p style="color:#666;">';
    echo '<strong>' . __('Total number of school days to date:').' '.$schoolDayCounts['total'].'</strong><br/>';
    echo __('Total number of school days in date range:').' '.$schoolDayCounts['dateRange'];
echo '</p>';
         echo '<table id="attendance_tbl" class="table colorOddEven" >';
  
        echo "<tr class='head'>";
       
        echo '<th style="width:80px" >';
        echo  'Date';
        echo '</th>';
        echo '<th style="width:80px" >';
        echo __('Period 1');
        echo '</th>';
        echo '<th style="width:80px" >';
        echo __('Period 2');
        echo '</th>';
        echo '<th style="width:80px" >';
        echo __('Period 3');
        echo '</th>';
        echo '<th style="width:80px" >';
        echo __('Period 4');
        echo '</th>';
        echo '<th style="width:80px" >';
        echo __('Period 5');
        echo '</th>';
        echo '<th style="width:80px" >';
        echo __('Period 6');
        echo '</th>';
       
       
        echo '<tr class="head" style="min-height:80px;">';
      
        $row = $result->fetchAll();       
        $datevalue = array();
        foreach ($periodStart as $dayDate ){          
            $datevalue[] = $dayDate->format( "Y-m-d" );
        }
        $datar = array();
        $data_array = array();
        foreach ($row as $dt ){
            $datar[] = $dt['date']  ;
              }
    
             $data_array =  array_unique($datar);
    
        $nottaken = array_diff($datevalue, $data_array); 
    
            $datevalue = $dayDate->format( "Y-m-d" );
    
            foreach($nottaken as $not) {
                // ROW                    
                    echo "<tr>";echo '<td>';
                    echo $not;
                    echo '</td>';
                    echo '<td>';
                  
                    echo '</td>';
                    echo '</td>';
                    echo '<td>';
                    
                    echo '</td>';   
                    echo '<td>';
                  
                    echo '</td>';
                    echo '</td>';
                    echo '<td>';
                    
                    echo '</td>'; 
                    echo '<td>';
                  
                    echo '</td>';
                    echo '</td>';
                    echo '<td>';
                    
                    echo '</td>';       
            
                        echo '</tr>';
                }
           
                echo '</tr>';
    
            }
        echo '</table>';

    }

   
}


