<?php
/*
Pupilsight, Flexible & Open School System
*/
$session = $container->get('session');
$student_id = $session->get('staff_id');

use Pupilsight\Forms\Form;
use Pupilsight\Forms\DatabaseFormFactory;


if (isActionAccessible($guid, $connection2, '/modules/Staff/assigned_student_toStaff_add.php') != false) {
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
    echo __('Select Staff');
    echo '</h2>';

    $sqlp = 'SELECT  b.pupilsightPersonID AS staff_id , b.officialName AS name FROM pupilsightStaff AS a INNER JOIN pupilsightPerson AS b ON a.pupilsightPersonID = b.pupilsightPersonID';
    $resultp = $connection2->query($sqlp);
    $getstaffs= $resultp->fetchAll();
    //$rowdataprog = $resultp->fetchAll();

    $getstaff=array();  
    $getstaff2=array();  
    $getstaff1=array(''=>'Select staff');
    foreach ($getstaffs as $dt) {
        $getstaff2[$dt['staff_id']] = $dt['name'];
    }
    $getstaff= $getstaff1 + $getstaff2;  
        
            $form = Form::create('program', $_SESSION[$guid]['absoluteURL'].'/modules/'.$_SESSION[$guid]['module'].'/assigned_staff_toStudent_addProcess.php');
            $form->setFactory(DatabaseFormFactory::create($pdo));
        
            $form->addHiddenValue('address', $_SESSION[$guid]['address']);
            $form->addHiddenValue('student_id', $student_id);
           
                $row = $form->addRow();
                    $col = $row->addColumn()->setClass('hiddencol nobrdbtm');
                    
                    $col->addTextField('');  

                    $col = $row->addColumn()->setClass('newdes')->setID('staffstatus');
                    $col->addLabel('staff_id', __('List of  Staff'));
                    $col->addSelect('staff_id')->fromArray($getstaff)->required(); 
                    
               
                    $col = $row->addColumn()->setClass('newdes');   
                    
                    $col->addLabel('', __(''));
                    $col->addContent(' <button id="simplesubmitInvoice" style=""class=" btn btn-primary">Assign</button>');  
                    
                    $col = $row->addColumn()->setClass('newdes')->setID('TB_closeAjaxWindow');   
                    
                    $col->addLabel('', __(''));
                    $col->addContent('<a  href="#" id="TB_closeWindowButton"  <button style="margin: 0 0 0 -175px;" class=" btn btn-primary" onclick="history.go(0);" >Cancel</button></a>');  
                    $col = $row->addColumn()->setClass('hiddencol nobrdbtm');
                    
                    $col->addTextField(''); 

echo $form->getOutput();
            




    
}