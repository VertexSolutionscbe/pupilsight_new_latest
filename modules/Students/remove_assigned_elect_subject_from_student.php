<?php
/*
Pupilsight, Flexible & Open School System
*/

$pid = $_GET['pupilsightPersonID'];
//$id = $_GET['id'];
// print_r($id);die();
use Pupilsight\Forms\Form;
use Pupilsight\Forms\DatabaseFormFactory;


if (isActionAccessible($guid, $connection2, '/modules/Students/remove_assigned_elect_subject_from_student.php') != false) {
    //Acess denied
    echo "<div class='alert alert-danger'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    //Proceed!
    $pupilsightSchoolYearID = $_GET['pupilsightSchoolYearID'] ?? '';
    $search = $_GET['search'] ?? '';

    $page->breadcrumbs
    ->add(__('Students'), 'student_view.php')
    ->add(__('Remove Elective Subject  From Students'));

  
    if (isset($_GET['return'])) {
        returnProcess($guid, $_GET['return'], $editLink, null);
    }
    echo '<h2>';
    echo __('Remove Assigned Elective Subjects From Student');
    echo '</h2>';
    if ($pid == '') {
        echo "<div class='alert alert-danger'>";
        echo __('You have not specified one or more required parameters.');
        echo '</div>';
    } else {

     //   $pupilsightProgramID=$_REQUEST['pupilsightProgramID'];
     //   $pupilsightYearGroupID=$_REQUEST['pupilsightYearGroupID'];
      //  $pupilsightDepartmentID=$_REQUEST['pupilsightDepartmentID'];
        //SELECT * FROM `assign_elective_subjects_tostudents` ,`id`,`pupilsightProgramID`,pupilsightPersonID
        //SELECT * FROM `assign_core_subjects_toclass` ,`id`,`pupilsightProgramID`,`pupilsightYearGroupID`,`pupilsightDepartmentID`
     $sqlp = 'SELECT a.pupilsightdepartmentID, b.name  FROM assign_elective_subjects_tostudents AS a LEFT JOIN pupilsightDepartment AS b ON a.pupilsightdepartmentID = b.pupilsightDepartmentID WHERE  a.pupilsightPersonID ="'.$pid.'" ';
    
    
  

   // $sqlp = 'SELECT a.id,  b.firstName AS name FROM assignstaff_toclasssection AS a LEFT JOIN pupilsightPerson AS b ON a.pupilsightPersonID = b.pupilsightPersonID   WHERE a.id = "'.$id.'"';
    
    $resultp = $connection2->query($sqlp);
    $getsub_assig= $resultp->fetchAll();
/*
    echo "<pre>";
    print_r($getsub_assig);die();*/

    $form = Form::create('program', $_SESSION[$guid]['absoluteURL'].'/modules/'.$_SESSION[$guid]['module'].'/remove_assigned_elect_subject_student_process.php');
    $form->setFactory(DatabaseFormFactory::create($pdo));

    $form->addHiddenValue('address', $_SESSION[$guid]['address']);
   //  $form->addHiddenValue('id', $id);
     $form->addHiddenValue('pid', $pid);
     
     //$form->addHiddenValue('pupilsightProgramID', $pupilsightProgramID);
     //$form->addHiddenValue('pupilsightYearGroupID', $pupilsightYearGroupID);
    // $form->addHiddenValue('pupilsightDepartmentID', $pupilsightDepartmentID);
    //$tab = '';

    $row = $form->addRow()->setID('seatdiv')->addClass('seatdiv');
   
    $col = $row->addColumn()->setClass('newdes');
    $col->addCheckbox('select')->setId('checkall')->setClass('checkall'); 
    $col = $row->addColumn()->setClass('newdes');
    $col->addLabel('Subject Name', __('Subject Name'))->addClass('dte');
    $col = $row->addColumn()->setClass('newdes');
    
   // $col = $row->addColumn()->setClass('newdes');
  //  $col->addLabel('remove', __('Remove'))->addClass('dte');

foreach($getsub_assig as $g_sub){
    //$tab .= '';
    // $row = $form->addRow();
    //     $col->addLabel('pupilsightProgramID', __($fee['name']));
    //     $col->addLabel('pupilsightYearGroupID', __($fee['academic_year']));
    $row = $form->addRow()->setID('seatdiv')->addClass('seatdiv');
        $col = $row->addColumn()->setClass('newdes');

      
        $col->addCheckbox('id_sub[]')->setValue($g_sub['pupilsightdepartmentID'])->setClass('fee_id'); 
        $col = $row->addColumn()->setClass('newdes');
        $col->addLabel($g_sub['name'], __($g_sub['name']))->addClass('dte');

        $col = $row->addColumn()->setClass('newdes');
       // $col->addContent(' <button id="simplesubmitInvoice" ><i  class="mdi mdi-close mdi-24px px-4 x_icon"></i></button>'); 
    
     
}
   
$row = $form->addRow();

$row->addSubmit();

    echo $form->getOutput();

}
}