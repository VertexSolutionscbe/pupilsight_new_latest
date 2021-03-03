<?php
/*
Pupilsight, Flexible & Open School System
*/
use Pupilsight\Forms\Form;
use Pupilsight\Tables\DataTable;
use Pupilsight\Services\Format;
use Pupilsight\Domain\Transport\TransportGateway;
use Pupilsight\Forms\DatabaseFormFactory;

if (isActionAccessible($guid, $connection2, '/modules/Transport/transport_amount_manage.php') == false) {
    //Acess denied
    echo "<div class='error'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    //Proceed!
    $id = $_GET['id'];
    $page->breadcrumbs
        ->add(__('Transport Fee'), 'transport_fee.php')
        ->add(__('Amount Config'));
    if (isset($_GET['return'])) {
        returnProcess($guid, $_GET['return'], null, null);
    }

    $TransportGateway = $container->get(TransportGateway::class);
    $criteria = $TransportGateway->newQueryCriteria()
    ->sortBy(['id'])
        ->fromPOST();
         $viewMember = $TransportGateway->getTransSchedulePrice($criteria);

         echo "<div style='height:50px;'><div class='float-right mb-2'><a href='index.php?q=/modules/Transport/transport_amount_config.php&id=".$id."' class='btn btn-primary'>Add</a>";  
         echo "&nbsp;&nbsp;</div><div class='float-none'></div></div>"; 


        $table = DataTable::createPaginated('FeeStructureManage', $criteria);

        //$table->addCheckboxColumn('id',__(''))->notSortable();
        $table->addColumn('serial_number', __('Sl No'));
        $table->addColumn('schedule_name', __('Schedule Name'));
        $table->addColumn('route_name', __('Route Name'));
        $table->addColumn('stop_name', __('Stop Name'));
        $table->addColumn('type', __('Type'))
            ->format(function ($dataSet) {
                if ($dataSet['type'] == '1') {
                    return 'Monthly';
                } else if ($dataSet['type'] == '2' ) {
                    return 'Bimonthly';
                } else if ($dataSet['type'] == '3' ) {
                    return 'Quarterly';
                } else if ($dataSet['type'] == '6' ) {
                    return 'Half Yearly';
                } else if ($dataSet['type'] == '12' ) {
                    return 'Yearly';
                } else {
                    return '';
                }
                return $dataSet['status'];
        }); 
        $table->addColumn('oneway_price', __('One Way Price'));
        $table->addColumn('twoway_price', __('Two Way Price'));
        $table->addColumn('tax', __('Tax'));
        $table->addActionColumn()
        ->addParam('id')
        ->addParam('backid',$id)
        ->format(function ($facilities, $actions) use ($guid) {
            $actions->addAction('edit', __('Edit'))
                ->setURL('/modules/Transport/transport_amount_edit.php');

            $actions->addAction('delete', __('Delete'))
                    ->setURL('/modules/Transport/transport_amount_delete.php');

        });
        echo $table->render($viewMember);

        }
