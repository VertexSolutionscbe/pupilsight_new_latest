<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Forms\Form;
use Pupilsight\Forms\DatabaseFormFactory;

//Module includes
require_once __DIR__ . '/moduleFunctions.php';

if (isActionAccessible($guid, $connection2, '/modules/Academics/test_home_general_add.php') == false) {
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
    if(isset($_SESSION['error'])){
        echo "<div class='error'>";
        echo $_SESSION['error'];
        echo '</div>';
    }
   unset($_SESSION['error']);
    $pupilsightSchoolYearID = $_SESSION[$guid]['pupilsightSchoolYearID'];

        $sqlq = 'SELECT * FROM pupilsightSchoolYear ORDER BY sequenceNumber';
        $resultval = $connection2->query($sqlq);
        $rowdata = $resultval->fetchAll();
        $academic = array();
        $ayear = '';
        if (!empty($rowdata)) {
            $ayear = $rowdata[0]['name'];
            foreach ($rowdata as $dt) {
                $academic[$dt['pupilsightSchoolYearID']] = $dt['name'];
            }
        }

       echo ' <a href="'.$_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/Academics/test_home_general_add.php" class=" btn btn-primary active">General</a>
        <a id="noAddGeneralTest" class=" btn btn-primary">Create Tests</a>';
    $form = Form::create('testhomeManage', $_SESSION[$guid]['absoluteURL'].'/modules/'.$_SESSION[$guid]['module'].'/test_home_general_addProcess.php?address='.$_SESSION[$guid]['address']);

    $form->setFactory(DatabaseFormFactory::create($pdo));
    $form->addHiddenValue('address', $_SESSION[$guid]['address']);

    $row = $form->addRow();
        $row->addLabel('testname', __('Name'));
        $row->addTextField('testname')->maxLength(40)->required();
    $row = $form->addRow();
        $row->addLabel('testcode', __('Test Code'));
        $row->addTextField('testcode')->maxLength(30)->required();

        $row = $form->addRow();
        $row->addLabel('Academic Year', __('Academic Year'));
        $row->addSelect('pupilsightSchoolYearID')->setId('academic_year')->fromArray($academic)->required()->selected($pupilsightSchoolYearID);

        $URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/Academics'."/test_home.php";  
     

    $row = $form->addRow();
        $row->addFooter();
        $row->addLabel('', __(''));
        $row->addContent(' ');  
        $row->addContent('<a  href="'.$URL.'" id="" class=" btn btn-primary" style=" font-size: 13px !important;padding: 5px 9px 2px 7px;">Close</a>')->setClass('exm_test_close');  
        $row->addSubmit(__('Save'));

    echo $form->getOutput();
}
