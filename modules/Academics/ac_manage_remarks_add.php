<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Forms\Form;
use Pupilsight\Forms\DatabaseFormFactory;

//Module includes
require_once __DIR__ . '/moduleFunctions.php';

if (isActionAccessible($guid, $connection2, '/modules/Academics/ac_manage_remarks_add.php') == false) {
    //Acess denied
    echo "<div class='error'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    $page->breadcrumbs
        ->add(__('Manage Remarks'), 'ac_manage_remarks.php')
        ->add(__('Add Remarks'));

    
    if (isset($_GET['return'])) {
        returnProcess($guid, $_GET['return'], null, null);
    }

    
//select subjects from department
$sqld = 'SELECT pupilsightDepartmentID, name FROM pupilsightDepartment ';
$resultd = $connection2->query($sqld);
$rowdatadept = $resultd->fetchAll();


$subjects=array();  
$subject2=array();  
// $subject1=array(''=>'Select Subjects');
foreach ($rowdatadept as $dt) {
    $subject2[$dt['pupilsightDepartmentID']] = $dt['name'];
}
$subjects=  $subject2;  



// `ac_manage_skill` WHERE 1, `ID`,`name`,`scode`,`description`,

$sqlsk = 'SELECT ID, name FROM ac_manage_skill ';
$resultsk = $connection2->query($sqlsk);
$skilldata = $resultsk->fetchAll();


$skills=array();  
$skills2=array();  

foreach ($skilldata as $sk) {
    $skills2[$sk['ID']] = $sk['name'];
}
$skills=  $skills2;  

    $form = Form::create('AcRemarksManage', $_SESSION[$guid]['absoluteURL'].'/modules/'.$_SESSION[$guid]['module'].'/ac_remarks_manage_addProcess.php?address='.$_SESSION[$guid]['address']);

    $form->setFactory(DatabaseFormFactory::create($pdo));
    $form->addHiddenValue('address', $_SESSION[$guid]['address']);
    $row = $form->addRow();
        $row->addLabel('rcode', __('Remark Code'));
        $row->addTextField('rcode')->maxLength(30)->required();
    $row = $form->addRow();
        $row->addLabel('description', __('Description'));
        $row->addTextArea('description')->setRows(4)->required();


        $row = $form->addRow()->setClass('');
        $row->addLabel('pupilsightDepartmentID', 'Subjects');
        $row->addSelect('pupilsightDepartmentID')->fromArray($subjects)->placeholder();   

  $row = $form->addRow()->setClass('');
        $row->addLabel('skill', 'Skills');
        $row->addSelect('skill')->fromArray($skills)->placeholder();        

     

    $row = $form->addRow();
        $row->addFooter();
        $row->addSubmit();

    echo $form->getOutput();
}
