<?php
/*
Pupilsight, Flexible & Open School System
*/

$id = $_GET['st_id'];
$mid =  $_GET['pupilsightMappingID'];
// print_r($id);die();


use Pupilsight\Forms\Form;
use Pupilsight\Forms\DatabaseFormFactory;

$id = $_GET['st_id'];

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
    echo __('Remove Staff');
    echo '</h2>';
    if ($id == '') {
        echo "<div class='alert alert-danger'>";
        echo __('You have not specified one or more required parameters.');
        echo '</div>';
    } else {
        //$sqlp = 'SELECT a.pupilsightdepartmentID, b.name  FROM assign_elective_subjects_tostudents AS a LEFT JOIN pupilsightDepartment AS b ON a.pupilsightdepartmentID = b.pupilsightDepartmentID WHERE  a.pupilsightPersonID ="'.$pid.'" ';
        $sqlp = 'SELECT a.id,  b.officialName AS name FROM assignstaff_toclasssection AS a LEFT JOIN pupilsightPerson AS b ON a.pupilsightPersonID = b.pupilsightPersonID   WHERE a.pupilsightMappingID = "' . $mid . '"';

        $resultp = $connection2->query($sqlp);
        $getstaff = $resultp->fetchAll();
        /*  echo "<pre>";
    print_r($getstaff);die();*/

        $form = Form::create('program', $_SESSION[$guid]['absoluteURL'] . '/modules/' . $_SESSION[$guid]['module'] . '/remove_assined_staffProcess.php');
        $form->setFactory(DatabaseFormFactory::create($pdo));
        $row = $form->addRow();

        $row->addSubmit(__('Remove'))->addClass('sub_margn');
        $form->addHiddenValue('address', $_SESSION[$guid]['address']);
        $form->addHiddenValue('id', $id);
        //$tab = '';

        $row = $form->addRow()->setID('seatdiv')->addClass('seatdiv');
        $col = $row->addColumn()->setClass('newdes');
        $col->addCheckbox('select')->setId('checkall')->setClass('checkall');

        $col = $row->addColumn()->setClass('newdes');
        $col->addLabel('Name', __('Name'))->addClass('dte');


        $col = $row->addColumn()->setClass('newdes');
        $col->addLabel('', __(''))->addClass('dte');

        foreach ($getstaff as $staff) {

            $row = $form->addRow()->setID('seatdiv')->addClass('seatdiv');

            $col = $row->addColumn()->setClass('newdes');
            $col->addCheckbox('staff_id[]')->setValue($staff['id'])->setClass('fee_id');
            $col = $row->addColumn()->setClass('newdes');
            $col->addLabel($staff['name'], __($staff['name']))->addClass('dte');

            $col = $row->addColumn()->setClass('newdes');
            // $col->addContent(' <button id="simplesubmitInvoice" ><i  class="mdi mdi-close mdi-24px px-4 x_icon"></i></button>'); 


        }



        echo $form->getOutput();
    }
}

echo "<style>
.sub_margn 

{
    margin-left: 528px !important;
    border-bottom: none;
}

</style>";