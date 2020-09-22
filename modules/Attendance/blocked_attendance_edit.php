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
    
    if (isset($_GET['pupilsightAttendanceBlockID'])) {
        $pupilsightAttendanceBlockID = $_GET['pupilsightAttendanceBlockID'];
    }
  
    $page->breadcrumbs
        ->add(__(' Manage Blocked Attendance'), 'blocked_attendance.php')
        ->add(__('Edit Blocked Attendance'));

        if ($pupilsightAttendanceBlockID == '') {
            echo "<div class='alert alert-danger'>";
            echo __('You have not specified one or more required parameters.');
            echo '</div>';
        } else {
            try {
               

                $data = array('pupilsightAttendanceBlockID' => $pupilsightAttendanceBlockID);
                $sql = 'SELECT * FROM pupilsightAttendanceBlocked WHERE pupilsightAttendanceBlockID=:pupilsightAttendanceBlockID'; 

                $result = $connection2->prepare($sql);
                $result->execute($data);

                
            } catch (PDOException $e) {
                echo "<div class='alert alert-danger'>".$e->getMessage().'</div>';
            }

            if ($result->rowCount() != 1) {
                echo "<div class='alert alert-danger'>";
                echo __('The specified record does not exist.');
                echo '</div>';
            } else {
                //Let's go!
                $values = $result->fetch();
                $sql2 = 'SELECT pupilsightRollGroupID FROM pupilsightAttendanceBlocked WHERE pupilsightYearGroupID ="'.$values['pupilsightYearGroupID'].'" AND start_date="'.$values['start_date'].'" AND end_date ="'.$values['end_date'].'"'; 
                $result2 = $connection2->query($sql2);
                $sections_sel = $result2->fetchAll();
              
                $sql = 'SELECT a.*, b.name FROM pupilsightProgramClassSectionMapping AS a LEFT JOIN pupilsightRollGroup AS b ON a.pupilsightRollGroupID = b.pupilsightRollGroupID WHERE a.pupilsightYearGroupID = "' . $values['pupilsightYearGroupID'] . '" GROUP BY a.pupilsightRollGroupID';
                $result = $connection2->query($sql);
                $sections = $result->fetchAll();

                $data = '';
               
                $cnt=0;
                if (count($sections)!=0 &&  $sections_sel[0]['pupilsightRollGroupID'] !='000000') {
                    foreach ($sections as $k => $cl) {

                        if($cl['pupilsightRollGroupID']==$sections_sel[$cnt]['pupilsightRollGroupID'])
                           {
                               $checked='checked';
                           }
                           else
                           {
                            $checked='';
                           }
                           $data .= '<input class="check_mrgin" type ="checkbox" name="pupilsightRollGroupID[]" '.$checked.' value="' . $cl['pupilsightRollGroupID'] . '">' . $cl['name']." " ;
                     //   $data .= '<input class="check_mrgin" type ="checkbox" name="pupilsightRollGroupID[]"  value="' . $cl['pupilsightRollGroupID'] . '">' . $cl['name']." " ;
                       
                     $cnt++;  
                    }
                    
                }
                
               
                $pupilsightSchoolYearID = '';
                if (isset($_GET['pupilsightSchoolYearID'])) {
                    $pupilsightSchoolYearID = $_GET['pupilsightSchoolYearID'];
                }

                $form = Form::create('Attendance', $_SESSION[$guid]['absoluteURL'].'/modules/'.$_SESSION[$guid]['module'].'/blocked_attendance_editProcess.php')->addClass('newform');
                $form->setFactory(DatabaseFormFactory::create($pdo));
            
                $form->addHiddenValue('address', $_SESSION[$guid]['address']);
                $form->addHiddenValue('pupilsightAttendanceBlockID', $pupilsightAttendanceBlockID);
               
            
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


                    $Type = array(
                        ''    => __('select Type'),
                        '1' => __('No Attendance'),
                        '2'  => __('Closure'),
                       
                    );
                $form->addHiddenValue('ayear', $ayear);    
            
             
               
                echo '<h2>';
                echo __('Blocked Attedance');
                echo '</h2>';
                $row = $form->addRow();
                
                    $col = $row->addColumn()->setClass('newdes');
                            $col->addLabel('name', __('Name'));
                            $col->addTextField('name')->addClass('txtfield')->required()->setValue($values['name']);


                            $col = $row->addColumn()->setClass('newdes');
                            $col->addLabel('pupilsightYearGroupID_check', __('Class'));
                            $col->addSelectYearGroup('pupilsightYearGroupID_check')->selected($values['pupilsightYearGroupID']);
                
                            
                            $col = $row->addColumn()->setClass('newdes');
                            $col->addLabel('pupilsightRollGroupID_check', __('Section'));
                            $col->addContent('<div id="pupilsightRollGroupID_check" class="section_div w-full txtfield">'.$data.' </div> ');
                        
                          
                
                 //echo $values['pupilsightRollGroupID'];
                // echo $values['type'];
                          
                    $row = $form->addRow();
                       $col = $row->addColumn()->setClass('newdes');
                        $col->addLabel('type', __('Type'));
                        $col->addSelect('type')->addClass('txtfield')->fromArray($Type)->selected($values['type'])->required();
            
                        $col = $row->addColumn()->setClass('newdes');
                        $col->addLabel('start_date', __('Start Date'))->addClass('dte');
                        $col->addDate('start_date')->addClass('txtfield')->readonly()->required()->setValue(dateConvertBack($guid, $values['start_date']));   
            
                        $col = $row->addColumn()->setClass('newdes');
                        $col->addLabel('end_date', __('End Date'))->addClass('dte');
                        $col->addDate('end_date')->addClass('txtfield')->readonly()->required()->setValue(dateConvertBack($guid, $values['end_date']));
            
                    $row = $form->addRow();              
                        $col = $row->addColumn()->setClass('newdes');
                        $col->addLabel('remark', __('Remark'));
                        $col->addTextArea('remark')->addClass('txtfield')->setRows(4)->setValue($values['remark']); 
               
                    $row = $form->addRow()->setID('lastseatdiv');
                    $row->addFooter();
                    $row->addSubmit();
            
                echo $form->getOutput();

            }
        }

   
  
}
?>
<style>

 .mt_align 
 {
    margin-top: 21px;
 }
 .sectionmultiple 
 {
    height: 60px !important;
    min-height: px!important;
 }

 .section_div 

 {
    height: 36px;
    background-color: #f0f1f3;
    border-radius: 4px;
    overflow-y:scroll!important;
 }
 .check_mrgin 
{
    margin-top: 11px!important;
    margin-left:4px;
}

</style>