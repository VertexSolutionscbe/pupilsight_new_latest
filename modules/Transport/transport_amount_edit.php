<?php
/*
Pupilsight, Flexible & Open School System
*/
use Pupilsight\Forms\Form;
use Pupilsight\Tables\DataTable;
use Pupilsight\Services\Format;
use Pupilsight\Domain\Transport\TransportGateway;
use Pupilsight\Forms\DatabaseFormFactory;

if (isActionAccessible($guid, $connection2, '/modules/Transport/transport_amount_edit.php') == false) {
    //Acess denied
    echo "<div class='error'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    //Proceed!
    $id = $_GET['id'];
    $backid = $_GET['backid'];
    $page->breadcrumbs
    ->add(__('Transport Fee'), 'transport_fee.php')
    ->add(__('Amount Config'), 'transport_amount_manage.php&id='.$backid)
    ->add(__('Amount Config Edit'));

    if (isset($_GET['return'])) {
        returnProcess($guid, $_GET['return'], null, null);
    }

    $data = array('id' => $id);
    $sql = 'SELECT trans_schedule.schedule_name,trans_route_price.*,trans_routes.route_name,trans_route_stops.stop_name FROM trans_route_price 
        LEFT JOIN trans_routes ON trans_route_price.route_id=trans_routes.id
        LEFT JOIN trans_schedule ON trans_route_price.schedule_id=trans_schedule.id
        LEFT JOIN trans_route_stops ON trans_route_price.stop_id=trans_route_stops.id
        WHERE trans_route_price.id = :id';
    $result = $connection2->prepare($sql);
    $result->execute($data);
    $values = $result->fetch();
  //print_r($values);die();

        if ($values['type'] == '1') {
            $type= 'Monthly';
        } else if ($values['type'] == '2' ) {
            $type= 'Bimonthly';
        } else if ($values['type'] == '3' ) {
            $type= 'Quarterly';
        } else if ($values['type'] == '6' ) {
            $type= 'Half Yearly';
        } else if ($values['type'] == '12' ) {
            $type= 'Yearly';
        } else {
            $type= '';
        }


    $form = Form::create('program', $_SESSION[$guid]['absoluteURL'].'/modules/'.$_SESSION[$guid]['module'].'/transport_amount_editProcess.php?id='.$id);
    $form->setFactory(DatabaseFormFactory::create($pdo));

    $form->addHiddenValue('address', $_SESSION[$guid]['address']);

    $row = $form->addRow();
    $col = $row->addColumn()->setClass('newdes');
    $col->addLabel('schedule_name', __('Schedule Name'))->addClass('dte');
    $col->addTextField('schedule_name')->addClass('txtfield')->readonly()->setValue($values['schedule_name']);

    $col = $row->addColumn()->setClass('newdes');
    $col->addLabel('route_name', __('Route Name'))->addClass('dte');
    $col->addTextField('route_name')->readonly()->setValue($values['route_name']);

    $col = $row->addColumn()->setClass('newdes');
    $col->addLabel('stop_name', __('Stop Name'))->addClass('dte');
    $col->addTextField('stop_name')->readonly()->setValue($values['stop_name']);

    $col = $row->addColumn()->setClass('newdes');
    $col->addLabel('type', __('Transport Fee Type'))->addClass('dte');
    $col->addTextField('type')->readonly()->setValue($type);

    //  $date = isset($_GET['date']) ? Format::dateConvert($_GET['date']) : date('Y-m-d');

    $row = $form->addRow();
    $col = $row->addColumn()->setClass('newdes');
    $col->addLabel('oneway_price', __('Oneway Price'));
    $col->addTextField('oneway_price')->addClass('numfield')->required()->setValue($values['oneway_price']);

    $col = $row->addColumn()->setClass('newdes');
    $col->addLabel('twoway_price', __('Twoway Price'));
    $col->addTextField('twoway_price')->addClass('numfield')->required()->setValue($values['twoway_price']);

    $col = $row->addColumn()->setClass('newdes');
    $col->addLabel('tax', __('Tax'));
    $col->addTextField('tax')->addClass('numfield')->required()->setValue($values['tax']);


    $row = $form->addRow();
    $row->addFooter();
    $row->addSubmit();

    echo $form->getOutput();

}
