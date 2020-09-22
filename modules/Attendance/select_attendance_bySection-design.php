<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Forms\Form;
use Pupilsight\Forms\DatabaseFormFactory;
use Pupilsight\Module\Attendance\AttendanceView;

if (isActionAccessible($guid, $connection2, '/modules/Attendance/attendance_take_bySection.php') == false) {
    //Acess denied
    echo "<div class='alert alert-danger'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    $courseId = $_GET['courseid'];
    $courseClsId = $_GET['courseclsid'];
    $periodId = $_GET['periodid'];
    $attendate = $_GET['attndate'];

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

        $page->breadcrumbs->add(__('Attendance Take By Section'));
    }
   
    // $attendance=array();  
    // $attendance2=array();  
    $attendance=array(''=>'Select Program',
'present'=>'Present','Absent' =>'absent');


$reason_absent=array( ''=>'Select Program','1'=>'not Well');
$sqld = 'SELECT name, pupilsightDepartmentID AS sub_id FROM pupilsightDepartment';
$resultd = $connection2->query($sqld);
$getsub= $resultd->fetchAll();
//print_r($getsub);die();
$getsubject = array();
$getsubject1 = array(''=>'select Subject');
$getsubject2 = array();
    foreach ($getsub as $dt) {
        $getsubject2[$dt['sub_id']] = $dt['name'];
    }

    $getsubject= $getsubject1 + $getsubject2;  
//     $attendence_dtl = array('sl_no'=>'1',
// 'name'=>'name','id'=>'id','class'=>'class','section'=>'section','attendence'=>'attendence'
// reason);

    $form = Form::create('program', $_SESSION[$guid]['absoluteURL'].'/modules/'.$_SESSION[$guid]['module'].'/remove_assined_staffSubProcess.php');
    $form->setFactory(DatabaseFormFactory::create($pdo));

    $form->addHiddenValue('address', $_SESSION[$guid]['address']);
    //  $form->addHiddenValue('id', $id);
    //$tab = '';

$row = $form->addRow()->setID('seatdiv')->addClass('seatdiv');
  
    $col = $row->addColumn()->setClass('newdes');
    $col->addLabel('subject_name', __('Subject Name'));   
    $col->addSelect('subject_name')->fromArray($getsubject)->addClass('txtfield')->required();
    

    $col = $row->addColumn()->setClass('newdes');
    $col->addLabel('date', __('Date'))->addClass('dte');
    $col->addDate('date')->setId('dueDate')->required();

    $col = $row->addColumn()->setClass('newdes');
    $col->addLabel('time_Period', __('Time/Period'));
    $col->addTextField('time_Period')->addClass('txtfield')->setId('timefield')->maxLength(5)->required();

    $col = $row->addColumn()->setClass('newdes');
    $col->addLabel('teacher_name', __('Teacher Name'));
    $col->addTextField('teacher_name')->addClass('txtfield')->setId('teacher_name')->required();

    $row = $form->addRow()->setID('seatdiv')->addClass('seatdiv'); 

        $col = $row->addColumn()->setClass('newdes');
        $col->addLabel('sl_no', __('Sl No'))->addClass('dte'); 

        $col = $row->addColumn()->setClass('newdes');
        $col->addLabel('name', __('Name'))->addClass('dte'); 

        $col = $row->addColumn()->setClass('newdes');
        $col->addLabel('id', __('ID'))->addClass('dte'); 

        $col = $row->addColumn()->setClass('newdes');
        $col->addLabel('class', __('Class'))->addClass('dte'); 

        $col = $row->addColumn()->setClass('newdes');
        $col->addLabel('section', __('Section'))->addClass('dte'); 

        $col = $row->addColumn()->setClass('newdes');
        $col->addLabel('attendance', __('Attendance'))->addClass('dte'); 
        
        $col = $row->addColumn()->setClass('newdes');
        $col->addLabel('reason', __('Reason'))->addClass('dte'); 

        $col = $row->addColumn()->setClass('newdes');
        $col->addLabel('remark', __('Remark'))->addClass('dte'); 
        //here im giving detail as a dummy value   ..u need to write  foreah array value here.
    $row = $form->addRow()->setID('seatdiv')->addClass('seatdiv'); 

        $col = $row->addColumn()->setClass('newdes customize_input');
        $col->addTextField('sl_No')->required()->readonly()->setValue('detail');

        $col = $row->addColumn()->setClass('newdes customize_input');
        $col->addTextField('name')->required()->readonly()->setValue('detail');

        $col = $row->addColumn()->setClass('newdes');
        $col->addTextField('id')->required()->readonly()->setValue('detail');

        $col = $row->addColumn()->setClass('newdes');
        $col->addTextField('class')->required()->readonly()->setValue('detail');

        $col = $row->addColumn()->setClass('newdes');
        $col->addTextField('section')->required()->readonly()->setValue('detail');

        $col = $row->addColumn()->setClass('newdes');
        
        $col->addSelect('attendance')->addClass('txtfield')->fromArray($attendance)->required();
        
        $col = $row->addColumn()->setClass('newdes');
        $col->addSelect('reason_absent')->addClass('txtfield')->fromArray($reason_absent)->required();

        $col = $row->addColumn()->setClass('newdes');
        $col->addTextField('remark')->addClass('txtfield')->required();
        $row = $form->addRow()->setID('seatdiv')->addClass('seatdiv'); 

        $col = $row->addColumn()->setClass('newdes customize_input');
        $col->addTextField('sl_No')->required()->readonly()->setValue('detail');

        $col = $row->addColumn()->setClass('newdes customize_input');
        $col->addTextField('name')->required()->readonly()->setValue('detail');

        $col = $row->addColumn()->setClass('newdes');
        $col->addTextField('id')->required()->readonly()->setValue('detail');

        $col = $row->addColumn()->setClass('newdes');
        $col->addTextField('class')->required()->readonly()->setValue('detail');

        $col = $row->addColumn()->setClass('newdes');
        $col->addTextField('section')->required()->readonly()->setValue('detail');

        $col = $row->addColumn()->setClass('newdes');
        
        $col->addSelect('attendance')->addClass('txtfield')->fromArray($attendance)->required();
        
        $col = $row->addColumn()->setClass('newdes');
        $col->addSelect('reason_absent')->addClass('txtfield')->fromArray($reason_absent)->required();

        $col = $row->addColumn()->setClass('newdes');
        $col->addTextField('remark')->addClass('txtfield')->required();
      // foreach($attendence_dtl as $dtl){        
    // }

    $row = $form->addRow();
        $col = $row->addColumn()->setClass('newdes');
        $col->addContent("<button style='padding: 10px;' class='btn btn-primary' >Change All</button>");
        $col = $row->addColumn()->setClass('newdes');
        $col->addContent("<p style='padding: 10px;'>Tottal Present : 10</p>");
        $col = $row->addColumn()->setClass('newdes');
        $col->addContent("<p style='padding: 10px;'>Tottal Absent : 1</p>");
    
    $row = $form->addRow();

    $row->addSubmit();
    echo $form->getOutput();

    }


