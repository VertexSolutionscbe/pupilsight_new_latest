

<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Forms\Form;
use Pupilsight\Forms\DatabaseFormFactory;
$session = $container->get('session');
$id = $session->get('student_ids');




if (isActionAccessible($guid, $connection2, '/modules/Academics/select_student_to_addsubjects.php') == false) {
    //Acess denied
    echo "<div class='error'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    //Proceed!
    $page->breadcrumbs
        ->add(__('Assign Subjects'));
    $editLink = '';
    // if (isset($_GET['editID'])) {
    //     $editLink = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/Finance/fee_structure_assign_student_manage_edit.php&id='.$_GET['editID'];
    // }
    if (isset($_GET['return'])) {
        returnProcess($guid, $_GET['return'], $editLink, null);
    }
    echo '<h2>';
    echo __('Assign Subjects');
    echo '</h2>';



    $sqlp = 'SELECT a.pupilsightStaffID,a.staff_status AS stat,b.*, b.pupilsightPersonID AS stu_id , a.type, b.firstName AS name FROM pupilsightStaff AS a INNER JOIN pupilsightPerson AS b ON a.pupilsightPersonID = b.pupilsightPersonID';
    $resultp = $connection2->query($sqlp);
    $getstaff= $resultp->fetchAll();
    
  
    $sqld = 'SELECT name, pupilsightDepartmentID AS sub_id FROM pupilsightDepartment';
    $resultd = $connection2->query($sqld);
    $getsub= $resultd->fetchAll();

    
   
    $getselectedstaff = [];
    $form = Form::create('program', $_SESSION[$guid]['absoluteURL'].'/modules/'.$_SESSION[$guid]['module'].'/assign_student_to_selected_SubjectProcess.php');
    $form->setFactory(DatabaseFormFactory::create($pdo));
     
    $form->addHiddenValue('address', $_SESSION[$guid]['address']);
    // $form->addHiddenValue('stu_id', $studentids);
    //$tab = '';
    $row = $form->addRow()->setID('seatdiv')->addClass('seatdiv');
    $col = $row->addColumn()->setClass('newdes');   
    $col->addLabel('', __(''));
    $col->addContent('');  
    
    
    $col = $row->addColumn()->setClass('newdes');   
    $col->addLabel('', __(''));
    $col->addContent('<div style="position: relative;left:200px" ><button id="simplesubmitInvoice" style="height: 34px; float: right;"class=" btn btn-primary">Assign</button></div>');  
    
    $col = $row->addColumn()->setClass('newdes');   
    $col->addLabel('', __(''));
    $col->addContent(' ');  
    
    
    $row = $form->addRow()->setID('seatdiv')->addClass('seatdiv'); 
    $col = $row->addColumn()->setClass('newdes border_left check_width');
    $col = $row->addColumn()->setClass('newdes');
    $col->addLabel('Subject', __('Subjects'))->addClass('dte');


    $col = $row->addColumn()->setClass('newdes border_left check_width');
    $col = $row->addColumn()->setClass('newdes');
    $col->addLabel('select_sub', __('Select'))->addClass('dte');
   

   
    $row = $form->addRow()->setID('seatdiv')->addClass('seatdiv');
        
        $col = $row->addColumn()->setClass('newdes border_left check_width');
      
        $col = $row->addColumn()->setClass('newdes');
        foreach($getsub as $sub){
        $col->addLabel($sub['name'], __($sub['name']))->addClass('dte');
        }    
        $col = $row->addColumn()->setClass('newdes');
        foreach($getsub as $sub){
            $col->addCheckbox($sub['sub_id'])->setname('selected_sub[]')->addClass('dte mrgn_right select_sub margintop');  
            }
   
        $col = $row->addColumn()->setClass('newdes border_left check_width');
      

          
    echo $form->getOutput();

}
echo "<style>
.mrgn_right 
{
    margin-right: 92px !important;
}
</style>";

