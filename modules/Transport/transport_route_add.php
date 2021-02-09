<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Forms\Form;
use Pupilsight\Forms\DatabaseFormFactory;

if (isActionAccessible($guid, $connection2, '/modules/Transport/transport_route_add.php') == false) {
    //Acess denied
    echo "<div class='error'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    //Proceed!
    $page->breadcrumbs
        ->add(__('Transport'), 'routes.php')
        ->add(__('Add Route'));

    $editLink = '';
    if (isset($_GET['editID'])) {
        $editLink = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/Transport/transport_route_edit.php&id='.$_GET['editID'];
    }
    if (isset($_GET['return'])) {
        returnProcess($guid, $_GET['return'], $editLink, null);
    }
    echo '<h2>';
    echo __('Add Transport Routes Structure');
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


    
    $form = Form::create('program', $_SESSION[$guid]['absoluteURL'].'/modules/'.$_SESSION[$guid]['module'].'/transport_route_addProcess.php');
    $form->setFactory(DatabaseFormFactory::create($pdo));

    $form->addHiddenValue('address', $_SESSION[$guid]['address']);

   
    $row = $form->addRow();
        $col = $row->addColumn()->setClass('newdes');
            $col->addLabel('route_name', __('Route Name'));
            $col->addTextField('route_name')->addClass('txtfield')->required();

      
            $col = $row->addColumn()->setClass('newdes');
            $col->addLabel('pupilsightSchoolYearID', __('Academic Year'));
            $col->addSelect('pupilsightSchoolYearID')->fromArray($academicData)->required()->selected($pupilsightSchoolYearID);    

            $col = $row->addColumn()->setClass('newdes');
            $col->addLabel('bus_id', __('Bus name '));
            $col->addSelect('bus_id')->fromArray($bus_id)->required();

            $col = $row->addColumn()->setClass('newdes');
            $col->addLabel('type', __('Type'));
            $col->addSelect('type')-> fromArray($type)->required();
    
        $row = $form->addRow();
            $col = $row->addColumn()->setClass('newdes');
            $col->addLabel('start_point', __('Start Point'));
            $col->addTextField('start_point')->addClass('txtfield')->required();

            $col = $row->addColumn()->setClass('newdes');
            $col->addLabel('start_time', __('Start Time'))->addClass('dte');
            $col->addTime('start_time')->addClass('txtfield')->setId('timefield')->maxLength(8)->required()->placeholder('24 hours format');

            $col = $row->addColumn()->setClass('newdes');
            $col->addLabel('end_point', __('End Point'));
            $col->addTextField('end_point')->addClass('txtfield')->required();
                
           
         
            $col = $row->addColumn()->setClass('newdes');
            $col->addLabel('end_time', __('End Time'))->addClass('dte');
            $col->addTime('end_time')->addClass('txtfield')->maxLength(8)->required()->placeholder('24 hours format');


            $row = $form->addRow();
            $col = $row->addColumn()->setClass('newdes nobrdbtm');
                $col->addLabel('fixed_fine_type', __('Stops'));
                $col->addTextField('')->setClass('hiddencol');

            $col = $row->addColumn()->setClass('hiddencol nobrdbtm');
                $col->addLabel('', __(''));
                $col->addTextField('');
            
            $col = $row->addColumn()->setClass('newdes nobrdbtm catbutt');
                 $col->addButton(__('Add'))->setID('addTransportStops')->addData('cid', '1')->addData('disid', 'nodata')->addClass('btn btn-primary');
    
           
    
        $row = $form->addRow()->setID('seatdiv')->addClass('seatdiv fixedfine mt-3');
            
            $col = $row->addColumn()->setClass('newdes nobrdbtm');
            $col->addLabel('stop_no', __('Stop Number'))->addClass('dte');
            $col->addTextField('stop_no[1]')->addClass('txtfield kountseat numfield')->required();

            $col = $row->addColumn()->setClass('newdes nobrdbtm');
            $col->addLabel('stop_name', __('Stop Name'))->addClass('dte');
            $col->addTextField('stop_name[1]')->addClass('txtfield kountseat')->required();
    
            $col = $row->addColumn()->setClass('newdes nobrdbtm');
            $col->addLabel('pickup_time', __('Pick Up Time'))->addClass('dte');
            $col->addTime('pickup_time[1]')->addClass('txtfield')->maxLength(8)->required();

            $col = $row->addColumn()->setClass('newdes nobrdbtm');
            $col->addLabel('drop_time', __('Drop Time'))->addClass('dte');
            $col->addTime('drop_time[1]')->addClass('txtfield kountseat')->maxLength(8)->required();
            $col->addLabel('', __(''))->addClass('dte');
            
            // $col = $row->addColumn()->setClass('newdes nobrdbtm');
            // $col->addLabel('oneway_price', __('One way Price'))->addClass('dte');
            // $col->addTextField('oneway_price[1]')->addClass('txtfield kountseat')->required();

            // $col = $row->addColumn()->setClass('newdes nobrdbtm');
            // $col->addLabel('twoway_price', __('Two way Price'))->addClass('dte');
            // $col->addTextField('twoway_price[1]')->addClass('txtfield')->required();   

            // $col = $row->addColumn()->setClass('newdes nobrdbtm');
            // $col->addLabel('tax', __('Tax'))->addClass('dte');
            // $col->addTextField('tax[1]')->addClass('txtfield kountseat szewdt_trans');
            // $col->addLabel('', __(''))->addClass('dte'); 
           
            
        $row = $form->addRow()->setID('route_stops')->addClass('mt-3');
            $row->addFooter();
            $row->addSubmit();
    

    echo $form->getOutput();

}