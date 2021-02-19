<?php
/*
Pupilsight, Flexible & Open School System
*/
$session = $container->get('session');
$studentids = $session->get('student_ids');
use Pupilsight\Forms\Form;
use Pupilsight\Forms\DatabaseFormFactory;

if (isActionAccessible($guid, $connection2, '/modules/Transport/assign_route_change_student_add.php') == false) {
    //Acess denied
    echo "<div class='error'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    //Proceed!
    $page->breadcrumbs
        ->add(__('Transport'), 'assign_route.php')
        ->add(__('Assign Route'));

    $editLink = '';
    // if (isset($_GET['editID'])) {
    //     $editLink = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/Transport/transport_route_edit.php&id='.$_GET['editID'];
    // }
    if (isset($_GET['return'])) {
        returnProcess($guid, $_GET['return'], $editLink, null);
    }
    echo '<h2>';
    echo __('Transport Routes Assign');
    echo '</h2>';
    $pupilsightSchoolYearID = $_SESSION[$guid]['pupilsightSchoolYearID'];
    $sqla = 'SELECT pupilsightSchoolYearID, name FROM pupilsightSchoolYear ';
    $resulta = $connection2->query($sqla);
    $academic = $resulta->fetchAll();
    $academicData = array();
    foreach ($academic as $dt) {
        $academicData[$dt['pupilsightSchoolYearID']] = $dt['name'];
    }


    $sqlr = 'SELECT id, name FROM trans_bus_details ';
    $resultr = $connection2->query($sqlr);
    $bus_name = $resultr->fetchAll();
    $bus_id = array();
    $bus_name1 = array(''=>'Select bus name');
    $bus_name2 = array();
   
    foreach ($bus_name as $dt) {
        $bus_name2[$dt['id']] = $dt['name'];
    }


    $bus_id = $bus_name1 + $bus_name2;

   
    $type = array();
    $type =  array(''=>'Select  Type',
        'pickup' =>'Pick Up',
        'drop'=>'Drop',
        'both'=>'Both'
);

// $transport_for = array();
// $type =  array(''=>'Select  Transport For',
//     'oneway' =>'Oneway',
//     'twoway'=>'Two way',
    
// );
$transport_for = array();
$transport_for =  array(''=>'Select  Transport For',
    'oneway' =>'Oneway',
    'twoway'=>'Two way',
    
);
$select_route = array();
$select_route =  array(''=>'Select  route',
    'onward' =>'Onward',
    'return'=>'Return',
    
);
$onward_rt = array();
$onward_rt =  array(''=>'Select  route',
    'onward' =>'Onward',
    'return'=>'Return',
    
);
$onward_sp = array();
$onward_sp =  array(''=>'Select  route',
    'onward' =>'Onward',
    'return'=>'Return',
    
);


$sqlr = 'SELECT route_name, id FROM  trans_routes ';
$resultr = $connection2->query($sqlr);
$onwardroute_l = $resultr->fetchAll();
$onwardroute_list = array();
$onwardroute_list1 = array(''=>'Select Route');
$onwardroute_list2 = array();
foreach ($onwardroute_l as $dt) {
    $onwardroute_list2[$dt['id']] = $dt['route_name'];
}
$onwardroute_list = $onwardroute_list1 + $onwardroute_list2;

$sqls = 'SELECT stop_name, id FROM  trans_route_stops ';
$results = $connection2->query($sqls);
$onwardroute_l = $results->fetchAll();
$onwardsp_list = array();
foreach ($onwardroute_l as $dt) {
    $onwardsp_list[$dt['id']] = $dt['stop_name'];
}



    
    $form = Form::create('program', $_SESSION[$guid]['absoluteURL'].'/modules/'.$_SESSION[$guid]['module'].'/assign_route_change_student_addProcess.php');
    $form->setFactory(DatabaseFormFactory::create($pdo));

    $form->addHiddenValue('address', $_SESSION[$guid]['address']);
  
    $form->addHiddenValue('stu_id', $studentids);

   
    $row = $form->addRow();
            $col = $row->addColumn()->setClass('newdes');
            $col->addLabel('pupilsightSchoolYearID', __('Academic Year'));
            $col->addSelect('pupilsightSchoolYearID')->fromArray($academicData)->selected($pupilsightSchoolYearID);    

            $col = $row->addColumn()->setClass('newdes')->setID('transport_for');
            $col->addLabel('transport_for', __('Transport For '));
            $col->addSelect('transport_for')->fromArray($transport_for);

            $col = $row->addColumn()->setClass('hiddencol nobrdbtm');
            $col->addLabel('', __(''));
            $col->addTextField('');    
    
        $row = $form->addRow()->setID('oneway_bl');
        $col = $row->addColumn()->setClass('newdes');
        
        $col->addLabel('select_route', __('Routes'));
        $col->addSelect('select_route')->fromArray($select_route);

        $row = $form->addRow()->setID('oneway_bl1');
            $col = $row->addColumn()->setClass('newdes');
            $col->addLabel('onwardroute', __('Onward route'));
            $col->addSelect('onwardroute')->fromArray($onwardroute_list);

           
            $col = $row->addColumn()->setClass('newdes');
            $col->addLabel('onwardsp', __('Onward stop'));
            $col->addSelect('onwardsp')->setId('onward_stops');

            $col = $row->addColumn()->setClass('hiddencol nobrdbtm');
            $col->addLabel('', __(''));
            $col->addTextField('');  
           
        $row = $form->addRow()->setID('oneway_bl2'); 
           
            $col = $row->addColumn()->setClass('newdes');
            $col->addLabel('return_rt', __('Return route'));
            $col->addSelect('return_rt')->fromArray($onwardroute_list);

            $col = $row->addColumn()->setClass('newdes');
            $col->addLabel('return_sp', __('Return Stop'));
            $col->addSelect('return_sp')->setId('toward_stops');
            // $col->addLabel('end_time', __('End Time'));
            // $col->addTextField('end_time')->addClass('txtfield');

            $col = $row->addColumn()->setClass('hiddencol nobrdbtm');
            $col->addLabel('', __(''));
            $col->addTextField('');  

        $row = $form->addRow()->setID('twoway_bl1'); 
           
            $col = $row->addColumn()->setClass('newdes');
            $col->addLabel('onward_rt_tway', __('Onward route'));
            $col->addSelect('onward_rt_tway')->setId('onward_rt_new')->fromArray($onwardroute_list);

            $col = $row->addColumn()->setClass('newdes');
            $col->addLabel('onward_sp_tway', __('Onward Stop'));
            $col->addSelect('onward_sp_tway')->setId('onward_sp_new');
            // $col->addLabel('end_time', __('End Time'));
            // $col->addTextField('end_time')->addClass('txtfield');

            $col = $row->addColumn()->setClass('hiddencol nobrdbtm');
            $col->addLabel('', __(''));
            $col->addTextField('');  

             $row = $form->addRow()->setID('twoway_bl3');
    
             $col = $row->addColumn();
             $col->addLabel('', __(''));
             $col->addCheckbox('same_as_onward')->setId('addReturnRoute')->description(__('Return route and stop same as onward.'));
           
            $row = $form->addRow()->setID('twoway_bl2'); 
           
            $col = $row->addColumn()->setClass('newdes');
            $col->addLabel('return_rt_tway', __('Return route'));
            $col->addSelect('return_rt_tway')->setId('return_rt_new')->fromArray($onwardroute_list);

            $col = $row->addColumn()->setClass('newdes');
            $col->addLabel('return_sp_tway', __('Return Stop'));
            $col->addSelect('return_sp_tway')->setId('return_sp_new');
            // $col->addLabel('end_time', __('End Time'));
            // $col->addTextField('end_time')->addClass('txtfield');

            $col = $row->addColumn()->setClass('hiddencol nobrdbtm');
            $col->addLabel('', __(''));
            $col->addTextField('');  


        
           
            
        $row = $form->addRow()->setID('route_stops');
            $row->addFooter();
            $row->addSubmit();
    

    echo $form->getOutput();
    echo '<script>$("#oneway_bl,#oneway_bl1,#oneway_bl2,#twoway_bl1,#twoway_bl2,#twoway_bl3").hide();</script>';
}