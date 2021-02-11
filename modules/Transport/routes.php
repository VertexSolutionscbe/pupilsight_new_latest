<?php
/*
Pupilsight, Flexible & Open School System
*/
use Pupilsight\Forms\Form;
use Pupilsight\Tables\DataTable;
use Pupilsight\Services\Format;
use Pupilsight\Domain\Transport\TransportGateway;
use Pupilsight\Forms\DatabaseFormFactory;

if (isActionAccessible($guid, $connection2, '/modules/Transport/routes.php') == false) {
    //Acess denied
    echo "<div class='error'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    //Proceed!
    $page->breadcrumbs->add(__('Manage Route Structure'));

    if (isset($_GET['return'])) {
        returnProcess($guid, $_GET['return'], null, null);
    }

    $TransportGateway = $container->get(TransportGateway::class);
    $criteria = $TransportGateway->newQueryCriteria()
        ->sortBy(['id'])
        ->fromPOST();

    $yearGroups = $TransportGateway->getRouteStructure($criteria);

    if (isset($_GET['return'])) {

        returnProcess(
            $guid,
            $_GET['return'],
            null,
            array('success0' => __('Request completed Sucessfully'),)
        );
    }

    // if (isset($_GET['return'])) {

    //     returnProcess(
    //         $guid,
    //         $_GET['return'],
    //         null,
    //         array('error1' => __('Route Name for School already Exist'),)
    //     );
    // }

    
    
    $table = DataTable::createPaginated('FeeStructureManage', $criteria);

    // $table->addHeaderAction('add', __('Add'))
    //     ->setURL('/modules/Finance/program_manage_add.php')
    //     ->displayLabel();
    
    echo "<div style='height:50px;'><div class='float-right mb-2'><a href='index.php?q=/modules/Transport/transport_route_add.php' class='btn btn-primary'>Add</a>";  
    echo "&nbsp;&nbsp;</div><div class='float-none'></div></div>";  

    
    
    $table->addColumn('serial_number', __('SI No'));  
    $table->addColumn('route_name', __('Rout Name'));
    $table->addColumn('year', __('Academic Year'));
    $table->addColumn('busname', __('Bus Name'));
    $table->addColumn('start_point', __('Start Point'));

    $table->addColumn('start_time', __('Start Time'))
         ->format(function ($dataSet) {
             if (!empty($dataSet['start_time'])) {
                 return date("H:i",strtotime($dataSet['start_time']));
             }
             return $dataSet['start_time'];
    });

    //$table->addColumn('start_time', __('Start Time'));
    $table->addColumn('end_point', __('End Point'));
    //$table->addColumn('end_time', __('End Time'));

    $table->addColumn('end_time', __('End Time'))
         ->format(function ($dataSet) {
             if (!empty($dataSet['end_time'])) {
                 return date("H:i",strtotime($dataSet['end_time']));
             }
             return $dataSet['end_time'];
    });

    $table->addColumn('totalstops', __('No of Stops'));

    
        
    // ACTIONS
    $table->addActionColumn()
        ->addParam('id')
        ->format(function ($facilities, $actions) use ($guid) {
            // $actions->addAction('copynew', __('Copy'))
            //         ->setURL('/modules/Transport/transport_route_copy.php');

            $actions->addAction('edit', __('Edit'))
                    ->setURL('/modules/Transport/transport_route_edit.php');

            $actions->addAction('delete', __('Delete'))
                    ->setURL('/modules/Transport/transport_route_delete.php');
            
            $actions->addAction('copy', __('Copy'))
                    ->setURL('/modules/Transport/transport_route_copies.php');

            
        });

    echo $table->render($yearGroups);

    //echo formatName('', $row['preferredName'], $row['surname'], 'Staff', false, true);
}
