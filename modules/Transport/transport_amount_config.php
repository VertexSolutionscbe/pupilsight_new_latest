<?php
/*
Pupilsight, Flexible & Open School System
*/
use Pupilsight\Forms\Form;
use Pupilsight\Tables\DataTable;
use Pupilsight\Services\Format;
use Pupilsight\Domain\Transport\TransportGateway;
use Pupilsight\Forms\DatabaseFormFactory;
if (isActionAccessible($guid, $connection2, '/modules/Transport/transport_amount_config.php') == false) {
    //Acess denied
    echo "<div class='error'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {

    $id = $_GET['id'];


    $data = array('id' => $id);
    $sql = 'SELECT * FROM trans_schedule WHERE id=:id';
    $result = $connection2->prepare($sql);
    $result->execute($data);
    $values = $result->fetch();
    //Proceed!
    $page->breadcrumbs->add(__('Assign route'));
    if (isset($_GET['return'])) {
        returnProcess($guid, $_GET['return'], null, null);
    }
       
    $trans_type = array();
    $trans_type =  array(''=>'Select  Transport',
        'Route'=>'Route',
        'Stops'=>'Stops',
);   
    $type = array();
    $type =  array(''=>'Select  Type',
        
        '1'=>'Monthly',
        '2'=>'Bimonthly',
        '3'=>'Quarterly',
        '6'=>'Half Yearly',
        '12' =>'yearly'
);

    $sqlrt = 'SELECT id, route_name FROM trans_routes';

    $resultrt = $connection2->query($sqlrt);
    $routesData = $resultrt->fetchAll();
    $routes = array();
    $routes1 = array(''=>'Select Route');
    $routes2 = array();

    foreach ($routesData as $rt) {
        $routes2[$rt['id']] = $rt['route_name'];
    }
    $routes = $routes1+$routes2;
    //$routes = $routes2;

if($_POST){
    $inputdata =  $_POST['fee_type1'];
     $trans = $_POST['amt_config'];
}else{
    $inputdata =  '' ;
    $trans = ''; 
    
}
//getting route amount 
    $searchform = Form::create('frmTransportAmountConfig', $_SESSION[$guid]['absoluteURL'].'/modules/'.$_SESSION[$guid]['module'].'/transport_amount_configProcess.php');
    $searchform->setFactory(DatabaseFormFactory::create($pdo));
    $searchform->addHiddenValue('schedule_id', $id);
    $row = $searchform->addRow();

    $col = $row->addColumn()->setClass('newdes');
    $col->addLabel('schedule_name', __('Schedule Name'))->addClass('dte');
    $col->addTextField('schedule_name')->setValue($values['schedule_name'])->readonly();

    $col = $row->addColumn()->setClass('newdes');
    $col->addLabel('trans_type', __('Transport Type'));
    $col->addSelect('trans_type')->fromArray($trans_type)->required()->placeholder();

    $col = $row->addColumn()->setClass('newdes stopClass hiddencol');
    $col->addLabel('stop_route_id', __('Route'));
    $col->addSelect('stop_route_id')->fromArray($routes);

    $col = $row->addColumn()->setClass('newdes');
    $col->addLabel('type', __('Transport Fee Type'))->addClass('dte');
    $col->addSelect('type')->fromArray($type)->required()->readonly()->selected($values['type'])->placeholder();
    
    
    
    
    $row = $searchform->addRow()->setClass('routeClass hiddencol');
    $col = $row->addColumn()->setClass('newdes');   
    $col->addLabel('', __(''));

    $col->addContent('<a id="transportFeeAdd" data-type="route" data-id="1" class=" btn btn-primary">Add More</a>');

    $row = $searchform->addRow()->setClass('routeClass hiddencol');

    $col = $row->addColumn()->setClass('newdes nobrdbtm1');
    $col->addLabel('route_id', __('Route'))->addClass('dte');
    $col->addSelect('route_id[1]')->fromArray($routes)->addClass('routeid');

    $col = $row->addColumn()->setClass('newdes nobrdbtm1');
    $col->addLabel('oneway_price', __('One way Price'))->addClass('dte');
    $col->addTextField('oneway_price[1]')->addClass('numfield txtfield kountseat onewayprice');

    $col = $row->addColumn()->setClass('newdes nobrdbtm1');
    $col->addLabel('twoway_price', __('Two way Price'))->addClass('dte');
    $col->addTextField('twoway_price[1]')->addClass('numfield txtfield twowayprice');   

    $col = $row->addColumn()->setClass('newdes nobrdbtm1');
    $col->addLabel('tax', __('Tax (%)'))->addClass('dte');
    $col->addTextField('tax[1]')->addClass('numfield txtfield kountseat szewdt stoptax');

    $row = $searchform->addRow()->setID('lastseatdiv')->setClass('showStopData stopClass hiddencol');


    $row = $searchform->addRow()->setClass('submtc hiddencol');
    $row->addFooter();
            //$row->addSubmit();
    $row->addContent('<button id="btnTransportAmountConfig" type="button" class="btn btn-primary" style="position:absolute; right:0; margin-top: -17px;">Submit</button>');
    echo $searchform->getOutput();

    //table
    $TransportGateway = $container->get(TransportGateway::class);
    $criteria = $TransportGateway->newQueryCriteria()
    ->sortBy(['id'])
    
     ->fromPOST();
     
     $route = $TransportGateway->getroute($criteria );
        if(!empty($trans == 'Route')){
            $table = DataTable::createPaginated('FeeStructureManage', $criteria);
            $table->addColumn('route_name', __('Route Name'));
            $table->addColumn('oneway_price',__('One Way'));
            $table->addColumn('twoway_price',__('Two Way'));
            $table->addColumn('tax',__('tax'));
        echo $table->render($route);
    }
    $stops = $TransportGateway->getstops($criteria , $inputdata);
        if(!empty($trans == 'Stops')){
            $table = DataTable::createPaginated('FeeStructureManage', $criteria);
            $table->addColumn('stop_name', __('Stop Name'));
            $table->addColumn('onway',__('One Way'));
            $table->addColumn('twoway',__('Two Way'));
            $table->addColumn('tax',__('tax'));
        echo $table->render($stops);
        }
    }
