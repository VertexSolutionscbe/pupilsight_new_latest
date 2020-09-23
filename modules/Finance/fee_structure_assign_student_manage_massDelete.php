<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Forms\Form;
use Pupilsight\Forms\DatabaseFormFactory;
$session = $container->get('session');
$studentids = $session->get('student_ids');

if (isActionAccessible($guid, $connection2, '/modules/Finance/fee_structure_assign_student_manage_massDelete.php') == false) {
    //Acess denied
    echo "<div class='error'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    
    //Proceed!
    $page->breadcrumbs
        ->add(__('Manage Fee Structure Assign Mass Delete'), 'fee_structure_assign_student_manage.php')
        ->add(__('Add Fee Structure Assign Mass Delete'));

    $editLink = '';
    if (isset($_GET['editID'])) {
        $editLink = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/Finance/fee_structure_assign_student_manage_edit.php&id='.$_GET['editID'];
    }
    if (isset($_GET['return'])) {
        returnProcess($guid, $_GET['return'], $editLink, null);
    }
    echo '<h2>';
    echo __('List of Assinged Fee Structures');
    echo '</h2>';
    if(isset($_REQUEST['sid'])?$id=$_REQUEST['sid']:$id="" );
    $pupilsightSchoolYearID = '';
    if (isset($_GET['pupilsightSchoolYearID'])) {
        $pupilsightSchoolYearID = $_GET['pupilsightSchoolYearID'];
    }
    if ($pupilsightSchoolYearID == '' or $pupilsightSchoolYearID == $_SESSION[$guid]['pupilsightSchoolYearID']) {
        $pupilsightSchoolYearID = $_SESSION[$guid]['pupilsightSchoolYearID'];
        $pupilsightSchoolYearName = $_SESSION[$guid]['pupilsightSchoolYearName'];
    }

    $data = array('pupilsightSchoolYearID' => $pupilsightSchoolYearID);
    $sql = 'SELECT name FROM pupilsightSchoolYear WHERE pupilsightSchoolYearID=:pupilsightSchoolYearID';
    $result = $pdo->executeQuery($data, $sql);

    $sqlp = 'SELECT a.id, a.name FROM fn_fee_structure AS a RIGHT JOIN fn_fees_student_assign AS b ON a.id = b.fn_fee_structure_id WHERE b.pupilsightPersonID IN ('.$studentids.') GROUP BY a.id';
    $resultp = $connection2->query($sqlp);
    $feestructure = $resultp->fetchAll();

    

    $form = Form::create('program', $_SESSION[$guid]['absoluteURL'].'/modules/'.$_SESSION[$guid]['module'].'/fee_structure_assign_student_manage_massDeleteProcess.php');
    $form->setFactory(DatabaseFormFactory::create($pdo));

    $form->addHiddenValue('address', $_SESSION[$guid]['address']);
    $form->addHiddenValue('stu_id', $studentids);
    //$tab = '';

    $row = $form->addRow()->setID('seatdiv')->addClass('seatdiv');
        $col = $row->addColumn()->setClass('newdes');
        $col->addCheckbox('select')->setId('checkall')->setClass('fee_id checkall');   

        $col = $row->addColumn()->setClass('newdes');
        $col->addLabel('Fee Structure', __('Fee Structure'))->addClass('dte');

        // $col = $row->addColumn()->setClass('newdes');
        // $col->addLabel('Invoice Status', __('Invoice Status'))->addClass('dte');
        
        
    foreach($feestructure as $fee){
        //$tab .= '';
        // $row = $form->addRow();
        //     $col->addLabel('pupilsightProgramID', __($fee['name']));
        //     $col->addLabel('pupilsightYearGroupID', __($fee['academic_year']));
        $row = $form->addRow()->setID('seatdiv')->addClass('seatdiv');
            $col = $row->addColumn()->setClass('newdes');
            $col->addCheckbox('fee_id[]')->setValue($fee['id'])->setClass('fee_id'); 

            $col = $row->addColumn()->setClass('newdes');
            $col->addLabel($fee['name'], __($fee['name']))->addClass('dte');

            // $col = $row->addColumn()->setClass('newdes');
            // $col->addLabel('invoice', __('Not Generated'))->addClass('dte');
            
    }
    

        
    $row = $form->addRow();
        $row->addFooter();
        $row->addSubmit();

    echo $form->getOutput();

}
