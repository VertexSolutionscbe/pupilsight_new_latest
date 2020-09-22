<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Forms\Form;
use Pupilsight\Forms\DatabaseFormFactory;
$session = $container->get('session');
$studentids = $session->get('student_ids');

if (isActionAccessible($guid, $connection2, '/modules/Finance/fee_structure_assign_student_manage_add.php') == false) {
    //Acess denied
    echo "<div class='error'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    $academicId = $_POST['val']; 
    //Proceed!
    $page->breadcrumbs
        ->add(__('Manage Fee Structure Assign'), 'fee_structure_assign_student_manage.php')
        ->add(__('Add Fee Structure Assign'));

    $sqlp = 'SELECT a.id, a.name, b.name as academic_year, SUM(c.total_amount) as totalamount FROM fn_fee_structure AS a LEFT JOIN pupilsightSchoolYear AS b ON a.pupilsightSchoolYearID = b.pupilsightSchoolYearID LEFT JOIN fn_fee_structure_item AS c ON a.id=c.fn_fee_structure_id WHERE b.pupilsightSchoolYearID = '.$academicId.' GROUP BY a.id';
    $resultp = $connection2->query($sqlp);
    $feestructure = $resultp->fetchAll();

    $form = Form::create('assignFeeStructure', $_SESSION[$guid]['absoluteURL'].'/modules/'.$_SESSION[$guid]['module'].'/fee_structure_assign_student_manage_addProcess.php');
    $form->setFactory(DatabaseFormFactory::create($pdo));

    $form->addHiddenValue('address', $_SESSION[$guid]['address']);
    $form->addHiddenValue('stu_id', $studentids);
    //$tab = '';

    $row = $form->addRow()->setID('seatdiv')->addClass('seatdiv');
        $col = $row->addColumn()->setClass('newdes');
        $col->addCheckbox('select')->setId('checkall')->setClass('fee_id checkall');   

        $col = $row->addColumn()->setClass('newdes');
        $col->addLabel('Fee Structure', __('Fee Structure'))->addClass('dte');

        $col = $row->addColumn()->setClass('newdes');
        $col->addLabel('Amount', __('Amount'))->addClass('dte');
        
        $col = $row->addColumn()->setClass('newdes');
        $col->addLabel('Academic Year', __('Academic Year'))->addClass('dte');

    foreach($feestructure as $fee){
        //$tab .= '';
        // $row = $form->addRow();
        //     $col->addLabel('pupilsightProgramID', __($fee['name']));
        //     $col->addLabel('pupilsightYearGroupID', __($fee['academic_year']));
        $row = $form->addRow()->setID('seatdiv')->addClass('seatdiv');
            $col = $row->addColumn()->setClass('newdes');
            $col->addCheckbox('fee_id[]')->setValue($fee['id'])->setClass('fee_id'); 

            $col = $row->addColumn()->setClass('newdes');
            $t="<a title='".$fee['name']."'>".substr($fee['name'],0,10)."...</a>";
            $col->addLabel($fee['name'], __($t))->addClass('dte');

            $col = $row->addColumn()->setClass('newdes');
            $col->addLabel('Amount', __($fee['totalamount']))->addClass('dte');
            
            $col = $row->addColumn()->setClass('newdes');
            $col->addLabel($fee['academic_year'], __($fee['academic_year']))->addClass('dte');
    }
    

        
    $row = $form->addRow();
        $row->addFooter();
        $row->addSubmit();

    echo $form->getOutput();

}
