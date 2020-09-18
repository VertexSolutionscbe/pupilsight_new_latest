<?php
/*
Pupilsight, Flexible & Open School System
*/

$id = $_GET['id'];
// print_r($id);die();
use Pupilsight\Forms\Form;
use Pupilsight\Forms\DatabaseFormFactory;


if (isActionAccessible($guid, $connection2, '/modules/Academics/remove_assigned_subject_from_class.php') != false) {
    //Acess denied
    echo "<div class='error'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    //Proceed!
    $pupilsightSchoolYearID = $_GET['pupilsightSchoolYearID'] ?? '';
    $search = $_GET['search'] ?? '';

    $page->breadcrumbs
        ->add(__('Assign subjects to class'), 'assign_subjects_class_add.php', ['pupilsightSchoolYearID' => $pupilsightSchoolYearID])
        ->add(__('Assign subjects to class'));

  
    if (isset($_GET['return'])) {
        returnProcess($guid, $_GET['return'], $editLink, null);
    }
    echo '<h2>';
    echo __('Remove Assigned Subjects From Class');
    echo '</h2>';
    if ($id == '') {
        echo "<div class='error'>";
        echo __('You have not specified one or more required parameters.');
        echo '</div>';
    } else {

        $pupilsightProgramID=$_REQUEST['pupilsightProgramID'];
        $pupilsightYearGroupID=$_REQUEST['pupilsightYearGroupID'];
        $pupilsightDepartmentID=$_REQUEST['pupilsightDepartmentID'];
        //SELECT * FROM `assign_core_subjects_toclass` ,`id`,`pupilsightProgramID`,`pupilsightYearGroupID`,`pupilsightDepartmentID`
     $sqlp = 'SELECT a.pupilsightdepartmentID, b.name  FROM assign_core_subjects_toclass AS a LEFT JOIN pupilsightDepartment AS b ON a.pupilsightdepartmentID = b.pupilsightDepartmentID WHERE  a.pupilsightYearGroupID ="'.$pupilsightYearGroupID.'" AND  a.pupilsightProgramID = "'.$pupilsightProgramID.'"   ';
  
    $resultp = $connection2->query($sqlp);
    $getsub_assig= $resultp->fetchAll();

   /* echo "<pre>";
    print_r($getsub_assig);die();*/

    $form = Form::create('program', $_SESSION[$guid]['absoluteURL'].'/modules/'.$_SESSION[$guid]['module'].'/remove_assigned_subject_from_class_process.php');
    $form->setFactory(DatabaseFormFactory::create($pdo));
    $row = $form->addRow();
    $col = $row->addColumn()->setClass('newdes');
    $col->addSubmit(__('Remove'))->addClass('submit_align submt');
    $form->addHiddenValue('address', $_SESSION[$guid]['address']);
    $form->addHiddenValue('id', $id);
    $form->addHiddenValue('pupilsightProgramID', $pupilsightProgramID);
    $form->addHiddenValue('pupilsightYearGroupID', $pupilsightYearGroupID);
    $form->addHiddenValue('pupilsightDepartmentID', $pupilsightDepartmentID);
    $row = $form->addRow()->setID('seatdiv')->addClass('seatdiv');
    $col = $row->addColumn()->setClass('newdes');
    $col->addCheckbox('select')->setId('checkall')->setClass('checkall'); 
    $col = $row->addColumn()->setClass('newdes');
    $col->addLabel('Subject Name', __('Subject Name'))->addClass('dte bold_text');
    $col = $row->addColumn()->setClass('newdes');

foreach($getsub_assig as $g_sub){
     
        $row = $form->addRow()->setID('seatdiv')->addClass('seatdiv');
        $col = $row->addColumn()->setClass('newdes');    
        $col->addCheckbox('id_sub[]')->setValue($g_sub['pupilsightdepartmentID'])->setClass('fee_id'); 
        $col = $row->addColumn()->setClass('newdes');
        $col->addLabel($g_sub['name'], __($g_sub['name']))->addClass('dte');

        $col = $row->addColumn()->setClass('newdes');
     
    }

       
        echo $form->getOutput();

}
}
?>

<style>
#TB_window
{
    margin-top: -232px !important;
}
.bold_text 
{
    font-weight: bold!important;
    font-size: 14px!important;
}
</style>