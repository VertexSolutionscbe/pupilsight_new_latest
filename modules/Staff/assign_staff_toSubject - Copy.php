<?php
/*
Pupilsight, Flexible & Open School System
*/

$session = $container->get('session');
$id = $session->get('staffs_id');

use Pupilsight\Domain\Staff\StaffGateway;
use Pupilsight\Forms\Form;
use Pupilsight\Forms\DatabaseFormFactory;


if (isActionAccessible($guid, $connection2, '/modules/Staff/assign_staff_toSubject.php') == false) {
    //Acess denied
    echo "<div class='alert alert-danger'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    //Proceed!
    $page->breadcrumbs
        ->add(__('View Staff Profiles'));
    $editLink = '';
    // if (isset($_GET['editID'])) {
    //     $editLink = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/Finance/fee_structure_assign_student_manage_edit.php&id='.$_GET['editID'];
    // }
    if (isset($_GET['return'])) {
        returnProcess($guid, $_GET['return'], $editLink, null);
    }
    // echo '<h2>';
    // echo __('Choose A Staff Member');
    // echo '</h2>';
   
$StaffGateway = $container->get(StaffGateway::class);
$criteria = $StaffGateway->newQueryCriteria()
        //->sortBy(['id'])
        ->fromPOST();
        
$getselstaff = $StaffGateway->getselectedStaff($criteria);
    //print_r($getselstaff);die();
    $sqlp = 'SELECT a.pupilsightStaffID,a.staff_status AS stat,b.*, b.pupilsightPersonID AS stu_id , a.type, b.firstName AS name FROM pupilsightStaff AS a INNER JOIN pupilsightPerson AS b ON a.pupilsightPersonID = b.pupilsightPersonID';
    $resultp = $connection2->query($sqlp);
    $getstaff= $resultp->fetchAll();
    
  
    $sqld = 'SELECT name, pupilsightDepartmentID AS sub_id FROM pupilsightDepartment';
    $resultd = $connection2->query($sqld);
    $getsub= $resultd->fetchAll();

    echo "<a style='display:none' id='clickstaffunassign' href='fullscreen.php?q=/modules/Staff/remove_assigned_staffSub.php&width=800'  class='thickbox '> unassign staff</a>"; 
   
    $getselectedstaff = [];
    $form = Form::create('program', $_SESSION[$guid]['absoluteURL'].'/modules/'.$_SESSION[$guid]['module'].'/assign_staff_toSubjectProcess.php');
    $form->setFactory(DatabaseFormFactory::create($pdo));
     
    $form->addHiddenValue('address', $_SESSION[$guid]['address']);
    // $form->addHiddenValue('stu_id', $studentids);
    //$tab = '';
    $row = $form->addRow()->setID('seatdiv')->addClass('seatdiv');
    $col = $row->addColumn()->setClass('newdes');   
    
    $col->addContent('');  
    
    
    $col = $row->addColumn()->setClass('newdes');   
    
    $col->addContent('<div style="position: relative;left:300px" ><a id="unassignsubj" style="height: 34px;  margin-left: 10px; float: right;"class=" btn btn-primary">Unassign</a>&nbsp;&nbsp;<button id="simplesubmitInvoice" style="height: 34px; float: right;"class=" btn btn-primary">Assign</button></div>');  
    
    $col = $row->addColumn()->setClass('newdes');   
    
    $col->addContent(' ');  
    
    
    $row = $form->addRow()->setID('seatdiv')->addClass('seatdiv'); 

    $col = $row->addColumn()->setClass('newdes');
    $col->addLabel('Staff', __('Staff'))->addClass('dte'); 

    $col = $row->addColumn()->setClass('newdes');
    $col->addLabel('subject', __('Subject'))->addClass('dte');  

    $col = $row->addColumn()->setClass('newdes');
    //$col->addCheckbox('select')->setId('checkall')->setClass('checkall dte');   

    $col = $row->addColumn()->setClass('newdes');
    $col->addLabel('Staff', __('Staff List'))->addClass('dte');
    
    $col = $row->addColumn()->setClass('newdes');
   // $col->addCheckbox('select')->setId('checkall')->setClass('checkall dte');   

    $col = $row->addColumn()->setClass('newdes');
    $col->addLabel('Subject', __('Subject List'))->addClass('dte');

   
    $row = $form->addRow()->setID('seatdiv')->addClass('seatdiv');
        $col = $row->addColumn()->setClass('newdes staffborder');
        foreach($getselstaff as $sel_staff){
            // $col->addCheckbox($sel_staff['pupilsightStaffID'])->setname('sele_staff_check[]')->addClass('dte margintop'); 
            $col->addContent('<div style="width:450px ;max-height:50px;  min-height: 58px;
            border-bottom: 1px solid #d2d0d0;"><input type="checkbox"  id="'.$sel_staff['pupilsightStaffID'].'"  name="sele_staff_check[]" class="dte margintop"> <input type="text" style="background:transparent;    max-width: 200px;
            min-width: 100px;width: 10px;" id="'.$sel_staff['pupilsightStaffID'].'" value="'.$sel_staff['fname'].'" class=""><textarea readonly style="background:#fff !important;    width: 300px;float: right; " id="'.$sel_staff['pupilsightStaffID'].'"> '.$sel_staff['dep_name'].' </textarea></div> ');
        }
    
        $col = $row->addColumn()->setClass('newdes border_left check_width');
        foreach($getstaff as $staff){            
            $col->addCheckbox($staff['pupilsightStaffID'])->setname('selected_sstaff[]')->addClass('dte select_sstaff margintop'); 
        }

        $col = $row->addColumn()->setClass('newdes');
        foreach($getstaff as $staff){
            $col->addLabel($staff['name'], __($staff['name']))->addClass('dte ');
            } 
   
        $col = $row->addColumn()->setClass('newdes border_left check_width');
        foreach($getsub as $sub){
        $col->addCheckbox($sub['sub_id'])->setname('selected_sub[]')->addClass('dte select_sub margintop');  
        }

        $col = $row->addColumn()->setClass('newdes');
        foreach($getsub as $sub){
        $col->addLabel($sub['name'], __($sub['name']))->addClass('dte');
        }       
    echo $form->getOutput();

}
