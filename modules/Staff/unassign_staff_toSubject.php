<?php
/*
Pupilsight, Flexible & Open School System
*/
$session = $container->get('session');
// $id = $session->get('staff_id');
$id = $_GET['st_id'];
// print_r($id);die();
use Pupilsight\Forms\Form;
use Pupilsight\Forms\DatabaseFormFactory;


if (isActionAccessible($guid, $connection2, '/modules/Staff/remove_assined_staff.php') == false) {
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
    echo __('Assigned Staff');
    echo '</h2>';
   
 
    if ($id == '') {
        echo "<div class='alert alert-danger'>";
        echo __('You have not specified one or more required parameters.');
        echo '</div>';
    } else {
    
        
    $sqlp = 'SELECT a.id,  b.firstName AS name FROM assignstaff_toclasssection AS a LEFT JOIN pupilsightPerson AS b ON a.pupilsightPersonID = b.pupilsightPersonID';
    
    $resultp = $connection2->query($sqlp);
    $getstaff= $resultp->fetchAll();
   // print_r($getstaff);die();

    $form = Form::create('program', $_SESSION[$guid]['absoluteURL'].'/modules/'.$_SESSION[$guid]['module'].'/assign_staff_toClassSectionProcess.php');
    $form->setFactory(DatabaseFormFactory::create($pdo));

    $form->addHiddenValue('address', $_SESSION[$guid]['address']);
    // $form->addHiddenValue('stu_id', $studentids);
    //$tab = '';

    $row = $form->addRow()->setID('seatdiv')->addClass('seatdiv');
   

    $col = $row->addColumn()->setClass('newdes');
    $col->addLabel('Name', __('Name'))->addClass('dte');

    
    $col = $row->addColumn()->setClass('newdes');
    $col->addLabel('remove', __('Remove'))->addClass('dte');

foreach($getstaff as $staff){
    //$tab .= '';
    // $row = $form->addRow();
    //     $col->addLabel('pupilsightProgramID', __($fee['name']));
    //     $col->addLabel('pupilsightYearGroupID', __($fee['academic_year']));
    $row = $form->addRow()->setID('seatdiv')->addClass('seatdiv');
   
        $col = $row->addColumn()->setClass('newdes');
        $col->addLabel($staff['name'], __($staff['name']))->addClass('dte');

        $col = $row->addColumn()->setClass('newdes');
        $col->addContent("<i id='removestaf' class='mdi mdi-close mdi-24px px-4 x_icon'></i>");
     
}
   
 

    echo $form->getOutput();

}
}