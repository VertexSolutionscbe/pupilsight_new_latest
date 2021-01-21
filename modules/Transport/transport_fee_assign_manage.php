<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Tables\DataTable;
use Pupilsight\Services\Format;
use Pupilsight\Domain\Transport\TransportGateway;

if (isActionAccessible($guid, $connection2, '/modules/Transport/transport_fee_assign_manage.php') == false) {
    //Acess denied
    echo "<div class='error'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    //Proceed!
    $page->breadcrumbs
        ->add(__('Transport'), 'transport_fee.php')
    ->add(__('Manage Transport Fee Assign'));

    if (isset($_GET['return'])) {
        returnProcess($guid, $_GET['return'], null, null);
    }

    $TransportGateway = $container->get(TransportGateway::class);

    // QUERY
    $id = $_GET['id'];
   
    $criteria = $TransportGateway->newQueryCriteria()
        ->sortBy(['id'])
        ->fromPOST();
    
    $yearGroups = $TransportGateway->getTransportAssignItem($criteria);
    
    // DATA TABLE
    $table = DataTable::createPaginated('FeeStructureAssignManage', $criteria);

    // $table->addHeaderAction('add', __('Add'))
    //     ->setURL('/modules/Transport/program_manage_add.php')
    //     ->displayLabel();
    
    echo "<div style='height:50px;'><div class='float-right mb-2'><a href='fullscreen.php?q=/modules/Transport/transport_fee_assign_manage_add.php&sid=".$id."' class='thickbox btn btn-primary'>Add</a></div><div class='float-none'></div></div>";  

    
    
    //$table->addColumn('sequenceNumber', __('sequenceNumber'));
    $table->addColumn('schedule_name', __('Schedule Name'));
    $table->addColumn('program_name', __('Organisation'));
    $table->addColumn('class', __('Class'));
    //$table->addColumn('section', __('Section'));
   
        
    // ACTIONS
    $table->addActionColumn()
        ->addParam('id')
        ->format(function ($facilities, $actions) use ($guid) {
            $actions->addAction('editnew', __('Edit'))
                    ->setURL('/modules/Transport/transport_fee_assign_manage_edit.php');

            $actions->addAction('delete', __('Delete'))
                    ->setURL('/modules/Transport/transport_fee_assign_manage_delete.php');
        });

    echo $table->render($yearGroups);

    //echo formatName('', $row['preferredName'], $row['surname'], 'Staff', false, true);
}
