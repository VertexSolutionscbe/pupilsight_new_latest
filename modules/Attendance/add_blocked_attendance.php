<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Forms\Form;
use Pupilsight\Forms\DatabaseFormFactory;
use Pupilsight\Tables\DataTable;
use Pupilsight\Module\Attendance\AttendanceView;
use Pupilsight\Domain\Attendance\AttendanceCodeGateway;
// use Pupilsight\Domain\Staff\StaffGateway;

if (isActionAccessible($guid, $connection2, '/modules/Attendance/add_blocked_attendance.php') == false) {
    //Acess denied
    echo "<div class='alert alert-danger'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    //Proceed!
    $pupilsightSchoolYearID = '';
    if (isset($_GET['pupilsightSchoolYearID'])) {
        $pupilsightSchoolYearID = $_GET['pupilsightSchoolYearID'];
    }
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

    if($_POST){
        
        $pupilsightProgramID =  $_POST['pupilsightProgramID'];
        $pupilsightYearGroupID =  $_POST['pupilsightYearGroupID'];
        $pupilsightRollGroupID =  $_POST['pupilsightRollGroupID'];
       
      
        $stuId = $_POST['studentId'];
    } else {
        $pupilsightProgramID =  '';
        $pupilsightYearGroupID =  '';
        $pupilsightRollGroupID =  '';
      
        $stuId = '0';
    }
    
    $page->breadcrumbs
        ->add(__('Blocked Attendance'), 'blocked_attendance.php')
        ->add(__('Add Blocked Attendance'));

    $editLink = '';
    if (isset($_GET['editID'])) {
        $editLink = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/Attendance/edit.php&id='.$_GET['editID'];
    }

// Commented to resolve error8 Issue

    // if (isset($_GET['return'])) {
       
    //     if($_REQUEST['return']=='error8')
    //             {
    //                 echo "<div class='alert alert-danger'>";
    //                 echo __('Invalid Date Input !');
    //                 echo '</div>';
    //             }
    //             else
    //             {
    //                 returnProcess($guid, $_GET['return'], $editLink, null);
    //             }
               
    // }

    
    
    $Type = array(
        ''    => __('select Type'),
        '1' => __('No Attendance'),
        '2'  => __('Closure'),
       
    );

    $form = Form::create('Attendance', $_SESSION[$guid]['absoluteURL'].'/modules/'.$_SESSION[$guid]['module'].'/add_blocked_attendanceProcess.php')->addClass('newform');
    $form->setFactory(DatabaseFormFactory::create($pdo));

    $form->addHiddenValue('address', $_SESSION[$guid]['address']);
   

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
    $form->addHiddenValue('ayear', $ayear);    

    
    
    if (isset($_GET['return'])) {

        returnProcess(
            $guid,
            $_GET['return'],
            null,
            array('error8' => __('Your request failed because the start date should be less than or equal to end date'),)
        );
    }


 //selectMultiple()   
    echo '<h2>';
    echo __('Blocked Attedance');
    echo '</h2>';
   
    


    $row = $form->addRow();
    
        $col = $row->addColumn()->setClass('newdes');
                $col->addLabel('name', __('Name'));
                $col->addTextField('name')->addClass('txtfield')->required();
            
                // $col = $row->addColumn()->setClass('newdes');
                // $col->addLabel('pupilsightYearGroupID_check', __('Class'));
                // $col->addSelectYearGroup('pupilsightYearGroupID_check')->selected($pupilsightYearGroupID);

                $col = $row->addColumn()->setClass('newdes');
                $col->addLabel('pupilsightProgramID', __('Program'));
                $col->addSelect('pupilsightProgramID')->setId('getMultiClassByProg')->selected($pupilsightProgramID)->fromArray($program);
                
                $col = $row->addColumn()->setClass('newdes');
                $col->addLabel('pupilsightYearGroupID', __('Class'))->addClass('dte');
                $col->addSelect('pupilsightYearGroupID')->setId('showMultiClassByProg')->fromArray($classes)->selected($pupilsightYearGroupID)->selectMultiple();

                $col = $row->addColumn()->setClass('newdes');
                $col->addLabel('[pupilsightRollGroupID', __('Section'))->addClass('dte');
                $col->addSelect('pupilsightRollGroupID')->setId('showMultiSecByProgCls')->fromArray($sections)->selected($pupilsightRollGroupID)->selectMultiple();
              
        
        $row = $form->addRow();
           $col = $row->addColumn()->setClass('newdes');
            $col->addLabel('type', __('Type'));
            $col->addSelect('type')->addClass('txtfield')->fromArray($Type)->required();

            $col = $row->addColumn()->setClass('newdes');
            $col->addLabel('start_date', __('Start Date'))->addClass('dte');
            $col->addDate('start_date')->addClass('txtfield')->required();   

            $col = $row->addColumn()->setClass('newdes');
            $col->addLabel('end_date', __('End Date'))->addClass('dte');
            $col->addDate('end_date')->addClass('txtfield')->required();

        $row = $form->addRow();              
            $col = $row->addColumn()->setClass('newdes');
            $col->addLabel('remark', __('Remark'));
            $col->addTextArea('remark')->addClass('txtfield')->setRows(4); 
   
        $row = $form->addRow()->setID('lastseatdiv');
        $row->addFooter();
        $row->addSubmit();

    echo $form->getOutput();
  
}
?>
<script>
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

