<?php
/*
Pupilsight, Flexible & Open School System
*/
$session = $container->get('session');
$assign_id = $session->get('changeRoute_id');
use Pupilsight\Forms\Form;
use Pupilsight\Forms\DatabaseFormFactory;
 //print_r($id);die();

if (isActionAccessible($guid, $connection2, '/modules/Transport/change_transport_route_edit.php') != false) {
    //Acess denied
    echo "<div class='error'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    //Proceed!
    $page->breadcrumbs
        ->add(__('Transport'), 'assign_route.php')
        ->add(__('Change Route'));

  
    if (isset($_GET['return'])) {
        returnProcess($guid, $_GET['return'], $editLink, null);
    }
    echo '<h2>';
    echo __('Transport Routes Change');
    echo '</h2>';
    $id =  implode(',', $assign_id);
 
    if ($id == '') {
        echo "<div class='error'>";
        echo __('You have not specified one or more required parameters.');
        echo '</div>';
    } else {
            try {
                $data = array('id' => $id);
                $sql = 'SELECT * FROM trans_route_assign WHERE id=:id';
                $result = $connection2->prepare($sql);
                $result->execute($data);
            } catch (PDOException $e) {
                echo "<div class='error'>".$e->getMessage().'</div>';
            }
            if ($result->rowCount() != 1) {
                echo "<div class='error'>";
                echo __('The specified record cannot be found.');
                echo '</div>';
            } else {
                //Let's go!
                $values = $result->fetch();
                // print_r($values);die();
                $sqla = 'SELECT pupilsightSchoolYearID, name FROM pupilsightSchoolYear ';
                $resulta = $connection2->query($sqla);
                $academic = $resulta->fetchAll();
            
                $academicData = array();
                foreach ($academic as $dt) {
                    $academicData[$dt['pupilsightSchoolYearID']] = $dt['name'];
                }
                $transport_for = array();
                $transport_for =  array(''=>'Select  Transport For',
                    'oneway' =>'Oneway',
                    'twoway'=>'Two way' );  
                }

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
            foreach ($onwardroute_l as $dt) {
            $onwardroute_list[$dt['id']] = $dt['route_name'];
        }

    }
    
    $form = Form::create('program', $_SESSION[$guid]['absoluteURL'].'/modules/'.$_SESSION[$guid]['module'].'/assign_route_student_addProcess.php');
    $form->setFactory(DatabaseFormFactory::create($pdo));

    $form->addHiddenValue('address', $_SESSION[$guid]['address']);
  
    $form->addHiddenValue('assign_id', $id);

   
    $row = $form->addRow();
            $col = $row->addColumn()->setClass('newdes');
            $col->addLabel('pupilsightSchoolYearID', __('Academic Year'));
            $col->addSelect('pupilsightSchoolYearID')->fromArray($academicData)->selected($values['pupilsightSchoolYearID']);    

            $col = $row->addColumn()->setClass('newdes')->setID('transport_for');
            $col->addLabel('transport_for', __('Transport For '));
            $col->addSelect('transport_for')->fromArray($transport_for)->selected($values['type']);

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
            $col->addLabel('onwardsp', __('Onward route'));
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
            $col->addLabel('onward_rt', __('Onward route'));
            $col->addSelect('onward_rt')->fromArray($onward_rt);

            $col = $row->addColumn()->setClass('newdes');
            $col->addLabel('onward_sp', __('Onward Stop'));
            $col->addSelect('onward_sp')->fromArray($onward_sp);
            // $col->addLabel('end_time', __('End Time'));
            // $col->addTextField('end_time')->addClass('txtfield');

            $col = $row->addColumn()->setClass('hiddencol nobrdbtm');
            $col->addLabel('', __(''));
            $col->addTextField('');  

            $row = $form->addRow()->setID('twoway_bl3');
    
            $col = $row->addColumn();
            $col->addLabel('', __(''));
            $col->addCheckbox('same_as_onward')->description(__('Return route and stop same as onward.'));
           
            $row = $form->addRow()->setID('twoway_bl2'); 
           
            $col = $row->addColumn()->setClass('newdes');
            $col->addLabel('onward_rt', __('Return route'));
            $col->addSelect('onward_rt')->fromArray($onward_rt);

            $col = $row->addColumn()->setClass('newdes');
            $col->addLabel('onward_sp', __('Return Stop'));
            $col->addSelect('onward_sp')->fromArray($onward_sp);
            // $col->addLabel('end_time', __('End Time'));
            // $col->addTextField('end_time')->addClass('txtfield');

            $col = $row->addColumn()->setClass('hiddencol nobrdbtm');
            $col->addLabel('', __(''));
            $col->addTextField('');  


        
           
            
        $row = $form->addRow()->setID('route_stops');
            $row->addFooter();
            $row->addSubmit();
    

    echo $form->getOutput();

}