<?php
/*
Pupilsight, Flexible & Open School System
*/
$session = $container->get('session');
$id = $session->get('staff_id');
// print_r($assign_id);die();
use Pupilsight\Forms\Form;
use Pupilsight\Forms\DatabaseFormFactory;
 //print_r($id);die();

if (isActionAccessible($guid, $connection2, '/modules/Staff/change_staff_status.php') == false) {
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
    echo __('Change Status');
    echo '</h2>';
   
 
    if ($id == '') {
        echo "<div class='alert alert-danger'>";
        echo __('You have not specified one or more required parameters.');
        echo '</div>';
    } else {
        $status = array();
        $status =  array(''=>'Select  status',
            'active' =>'Active',
            'inactive'=>'Inactive');

        $reasoninactive = array(''=>'select Reason',
            'Resigned' => 'Resigned',
            'Change department' =>'Change department'  
        );

        
            $form = Form::create('program', $_SESSION[$guid]['absoluteURL'].'/modules/'.$_SESSION[$guid]['module'].'/change_staff_statusProcess.php');
            $form->setFactory(DatabaseFormFactory::create($pdo));
        
            $form->addHiddenValue('address', $_SESSION[$guid]['address']);
          
            $form->addHiddenValue('id', $id);
        
           
            $row = $form->addRow();
                    $col = $row->addColumn()->setClass('newdes')->setID('staffstatus');
                    $col->addLabel('staffstatus', __('change Status'));
                    $col->addSelect('staffstatus')->fromArray($status)->required();  

            $row = $form->addRow();
                    $col = $row->addColumn()->setClass('newdes');
                    $col->addLabel('reasoninactive', __('Reason For Inactive'));
                    $col->addSelect('reasoninactive')->fromArray($reasoninactive);   
                 
                  
                               
        $row = $form->addRow()->setID('route_stops');
        $row->addFooter();
        $row->addSubmit();


echo $form->getOutput();
            




    }

}