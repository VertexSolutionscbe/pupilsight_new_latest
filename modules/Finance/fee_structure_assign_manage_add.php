<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Forms\Form;
use Pupilsight\Forms\DatabaseFormFactory;


if (isActionAccessible($guid, $connection2, '/modules/Finance/fee_structure_assign_manage_add.php') == false) {
    //Acess denied
    echo "<div class='error'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    //Proceed!
    $page->breadcrumbs
        ->add(__('Manage Fee Structure Assign'), 'fee_structure_assign_manage.php')
        ->add(__('Add Fee Structure Assign'));

    $editLink = '';
    if (isset($_GET['editID'])) {
        $editLink = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/Finance/fee_structure_assign_manage_edit.php&id='.$_GET['editID'];
    }
    if (isset($_GET['return'])) {
        returnProcess($guid, $_GET['return'], $editLink, null);
    }
    echo '<h2>';
    echo __('FEE STRUCTURE ASSIGN TO CLASS');
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

    $sqlp = 'SELECT pupilsightProgramID, name FROM pupilsightProgram ';
    $resultp = $connection2->query($sqlp);
    $rowdataprog = $resultp->fetchAll();

    $program=array();  
    $program2=array();  
    $program1=array(''=>'Select Program');
    foreach ($rowdataprog as $dt) {
        $program2[$dt['pupilsightProgramID']] = $dt['name'];
    }
    $program= $program1 + $program2;  

    
    $sqlchk = 'SELECT GROUP_CONCAT(DISTINCT pupilsightYearGroupID) AS pupilsightYearGroupID FROM fn_fees_class_assign WHERE fn_fee_structure_id = "'.$id.'" ';
    $resultchk = $connection2->query($sqlchk);
    $structurechk = $resultchk->fetch();
    if(!empty($structurechk['pupilsightYearGroupID'])){
        $sqlclasses = 'SELECT pupilsightYearGroupID, name FROM pupilsightYearGroup WHERE pupilsightYearGroupID NOT IN ('.$structurechk['pupilsightYearGroupID'].') ORDER BY pupilsightYearGroupID ASC';
    } else {
        $sqlclasses = 'SELECT pupilsightYearGroupID, name FROM pupilsightYearGroup ORDER BY pupilsightYearGroupID ASC';
    }
    $resultcl = $connection2->query($sqlclasses);
    $allclasses = $resultcl->fetchAll();
    $classes = array();
    foreach ($allclasses as $cl) {
        $classes[$cl['pupilsightYearGroupID']] = $cl['name'];
    }
    // echo '<pre>';
    // print_r($allclasses);
    // echo '</pre>';
    // die();
    // print_r($structurechk);
    // die();
     

    $form = Form::create('program', $_SESSION[$guid]['absoluteURL'].'/modules/'.$_SESSION[$guid]['module'].'/fee_structure_assign_manage_addProcess.php');
    $form->setFactory(DatabaseFormFactory::create($pdo));

    $form->addHiddenValue('address', $_SESSION[$guid]['address']);
    $form->addHiddenValue('fn_fee_structure_id', $id);

    $row = $form->addRow();
            $row->addLabel('pupilsightProgramID', __('Program'));
            $row->addSelect('pupilsightProgramID')->setId('program_class')->fromArray($program)->required()->placeholder();

            $row = $form->addRow()->setId('changeFeeStructure');

    // $row = $form->addRow();
    //     $row->addLabel('pupilsightRollGroupID', __('Section'));
    //     $row->addSelectRollGroup('pupilsightRollGroupID', $pupilsightSchoolYearID)->required();

    // $row = $form->addRow();
    // $row->addLabel('pupilsightYearGroupID', __('Class'));
    // $row->addCheckbox('pupilsightYearGroupID')->fromArray($classes)->addCheckAllNone()->required();
    $row = $form->addRow();
        $row->addFooter();
        $row->addSubmit();

    echo $form->getOutput();

}
