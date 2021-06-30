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
    echo __('Unassign Staff To Subjects');
    echo '</h2>';
    if ($id == '') {
        echo "<div class='alert alert-danger'>";
        echo __('You have not specified one or more required parameters.');
        echo '</div>';
    } else {
    $sqlp = 'SELECT a.id, a.pupilsightdepartmentID, b.name, p.name as program , y.name as class, c.name as section FROM assignstaff_tosubject AS a LEFT JOIN pupilsightDepartment AS b ON a.pupilsightdepartmentID = b.pupilsightDepartmentID 
    LEFT JOIN pupilsightProgram AS p ON a.pupilsightProgramID = p.pupilsightProgramID 
    LEFT JOIN pupilsightYearGroup AS y ON a.pupilsightYearGroupID = y.pupilsightYearGroupID 
    LEFT JOIN pupilsightRollGroup AS c ON a.pupilsightRollGroupID = c.pupilsightRollGroupID WHERE a.pupilsightStaffID = "'.$id.'" AND a.pupilsightRollGroupID IS NOT NULL ';
    
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
    $col->addCheckbox('select')->setId('checkall')->setClass('chkAll'); 
    $col = $row->addColumn()->setClass('newdes');
    $col->addLabel('Program', __('Program'))->addClass('dte subName');
    $col = $row->addColumn()->setClass('newdes');
    $col->addLabel('Class', __('Class'))->addClass('dte subName');
    $col = $row->addColumn()->setClass('newdes');
    $col->addLabel('Section', __('Section'))->addClass('dte subName');
    $col = $row->addColumn()->setClass('newdes');
    $col->addLabel('Name', __('Subject Name'))->addClass('dte subName');


foreach($getdep as $dep){
    $row = $form->addRow()->setID('seatdiv')->addClass('seatdiv');
    $col = $row->addColumn()->setClass('newdes');
    $col->addCheckbox('dep[]')->setValue($dep['id'])->setClass('chkChild'); 
    $col = $row->addColumn()->setClass('newdes');
    $col->addLabel($dep['program'], __($dep['program']))->addClass('dte');
    $col = $row->addColumn()->setClass('newdes');
    $col->addLabel($dep['class'], __($dep['class']))->addClass('dte');
    $col = $row->addColumn()->setClass('newdes');
    $col->addLabel($dep['section'], __($dep['section']))->addClass('dte');
    $col = $row->addColumn()->setClass('newdes');
    $col->addLabel($dep['name'], __($dep['name']))->addClass('dte');
 
     
}
$row = $form->addRow();

$row->addSubmit();
 

    echo $form->getOutput();

}
}


?>

<style>
    .subName {
        font-size : 20px !important;
    }
</style>

<script>
    $(document).on('change', '.chkAll', function () {
        if ($(".chkAll:checkbox").is(':checked')) {
            $(".chkChild:checkbox").prop("checked", true);
        } else {
            $(".chkChild:checkbox").prop("checked", false);
        }
    });

    $(document).on('change', '.chkChild', function () {
        if ($(this).is(':checked')) {
            //$(".chkChild"+id).prop("checked", true);
        } else {
            $(".chkAll:checkbox").prop("checked", false);
        }
    });
</script>