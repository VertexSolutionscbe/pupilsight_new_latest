<?php
/*
Pupilsight, Flexible & Open School System
*/
$session = $container->get('session');
$id = $session->get('staff_id');
use Pupilsight\Forms\Form;
use Pupilsight\Forms\DatabaseFormFactory;
// print_r($id);die();

if (isActionAccessible($guid, $connection2, '/modules/Staff/remove_assigned_staffSub.php') == false) {
    //Acess denied
    echo "<div class='alert alert-danger'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    //Proceed!
    $page->breadcrumbs
        ->add(__('Staff'), 'staff_view.php')
        ->add(__('Change Staff Status'));

  
    if (isset($_GET['return'])) {
        returnProcess($guid, $_GET['return'], $editLink, null);
    }
    echo '<h2>';
    echo __('Remove Staff');
    echo '</h2>';
    if ($id == '') {
        echo "<div class='alert alert-danger'>";
        echo __('You have not specified one or more required parameters.');
        echo '</div>';
    } else {
    $sqlp = 'SELECT a.pupilsightdepartmentID, b.name  FROM assignstaff_tosubject AS a LEFT JOIN pupilsightDepartment AS b ON a.pupilsightdepartmentID = b.pupilsightDepartmentID   WHERE a.pupilsightStaffID = "'.$id.'"';
    
    $resultp = $connection2->query($sqlp);
    $getdep= $resultp->fetchAll();
   // print_r($getdep);die();

    $form = Form::create('program', $_SESSION[$guid]['absoluteURL'].'/modules/'.$_SESSION[$guid]['module'].'/remove_assined_staffSubProcess.php');
    $form->setFactory(DatabaseFormFactory::create($pdo));

    $form->addHiddenValue('address', $_SESSION[$guid]['address']);
     $form->addHiddenValue('id', $id);
    //$tab = '';

    $row = $form->addRow()->setID('seatdiv')->addClass('seatdiv');
    $col = $row->addColumn()->setClass('newdes');
    $col->addCheckbox('select')->setId('checkall')->setClass('checkall'); 
    $col = $row->addColumn()->setClass('newdes');
    $col->addLabel('Name', __('Subject Name'))->addClass('dte');


foreach($getdep as $dep){
    $row = $form->addRow()->setID('seatdiv')->addClass('seatdiv');
    $col = $row->addColumn()->setClass('newdes');
    $col->addCheckbox('dep[]')->setValue($dep['pupilsightdepartmentID'])->setClass('fee_id'); 
    $col = $row->addColumn()->setClass('newdes');
    $col->addLabel($dep['name'], __($dep['name']))->addClass('dte');
 
     
}
$row = $form->addRow();

$row->addSubmit();
 

    echo $form->getOutput();

}
}