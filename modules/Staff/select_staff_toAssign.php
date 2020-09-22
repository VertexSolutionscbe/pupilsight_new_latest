<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Forms\Form;
use Pupilsight\Forms\DatabaseFormFactory;
$session = $container->get('session');
$studentids = $session->get('student_ids');


if (isActionAccessible($guid, $connection2, '/modules/Staff/select_staff_toAssign.php') == false) {
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
    echo '<h2>';
    echo __('Choose A Staff Member');
    echo '</h2>';


    $sqlp = 'SELECT a.pupilsightStaffID,a.staff_status AS stat,b.*, b.pupilsightPersonID AS stu_id , a.type, b.officialName AS name FROM pupilsightStaff AS a INNER JOIN pupilsightPerson AS b ON a.pupilsightPersonID = b.pupilsightPersonID';
    $resultp = $connection2->query($sqlp);
    $getstaff= $resultp->fetchAll();
    

    $form = Form::create('program', $_SESSION[$guid]['absoluteURL'].'/modules/'.$_SESSION[$guid]['module'].'/assign_staff_toClassSectionProcess.php');
    $form->setFactory(DatabaseFormFactory::create($pdo));

    $form->addHiddenValue('address', $_SESSION[$guid]['address']);
    $form->addHiddenValue('stu_id', $studentids);
    //$tab = '';

   $row = $form->addRow()->setID('seatdiv')->addClass('seatdiv');
    $col = $row->addColumn()->setClass('min_width_check margin_check');
    $col->addCheckbox('select')->setId('checkall')->setClass('checkall ');   

    $col = $row->addColumn()->setClass('');
    $col->addLabel('Name', __('Name'))->addClass('dte');

    $col = $row->addColumn()->setClass('');
    $col->addLabel('Email', __('Email'))->addClass('dte');
    
    $col = $row->addColumn()->setClass('');
    $col->addLabel('Phone', __('Phone'))->addClass('dte mrlft');
    
    $col = $row->addColumn()->setClass('');
    $col->addLabel('department', __('department'))->addClass('dte');
    
    $col = $row->addColumn()->setClass('');
    $col->addLabel('Status', __('Status'))->addClass('dte');
    

foreach($getstaff as $staff){
    //$tab .= '';
    // $row = $form->addRow();
    //     $col->addLabel('pupilsightProgramID', __($fee['name']));
    //     $col->addLabel('pupilsightYearGroupID', __($fee['academic_year']));
    $row = $form->addRow()->setID('seatdiv')->addClass('seatdiv');
        $col = $row->addColumn()->setClass('min_width_check');
        $col->addCheckbox('staff[]')->setValue($staff['stu_id'])->setClass('fee_id margin_check'); 

        $col = $row->addColumn()->setClass('');
        $col->addLabel($staff['name'], __($staff['name']))->addClass('dte');

        $col = $row->addColumn()->setClass('');
        $col->addLabel($staff['email'], __($staff['email']))->addClass('dte');
        $col = $row->addColumn()->setClass('');

        $col->addLabel($staff['phone1'], __($staff['phone1']))->addClass('dte mrlft');
        $col = $row->addColumn()->setClass('');

        $col->addLabel($staff['type'], __($staff['type']))->addClass('dte');
        $col = $row->addColumn()->setClass('');
        $col->addLabel($staff['stat'], __($staff['stat']))->addClass('dte');


     
}
   
    $row = $form->addRow();
        $row->addFooter();
        $row->addSubmit();

    echo $form->getOutput();

}

echo "<style>
#program table.smallIntBorder td {
   
   
     min-width: 142px;
     width: auto !important;
}
.min_width_check
{
    min-width: 40px!important;

}
.margin_check
{
    margin-top:2px;
}
.mrlft 
{
    margin-left: 18px;
}

</style>";
