<?php
/*
Pupilsight, Flexible & Open School System
*/
use Pupilsight\Forms\Form;
use Pupilsight\Tables\DataTable;
use Pupilsight\Services\Format;
use Pupilsight\Domain\Transport\TransportGateway;
use Pupilsight\Forms\DatabaseFormFactory;

if (isActionAccessible($guid, $connection2, '/modules/Transport/transport_fee.php') == false) {
    //Acess denied
    echo "<div class='error'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    //Proceed!
    $page->breadcrumbs->add(__('Transport Fee'));
    if (isset($_GET['return'])) {
        returnProcess($guid, $_GET['return'], null, null);
    }

    $TransportGateway = $container->get(TransportGateway::class);
    $criteria = $TransportGateway->newQueryCriteria()
        ->pageSize(5000)    
        ->sortBy(['id'])
        ->fromPOST();
         $viewMember = $TransportGateway->getTransSchedule($criteria);
        //  echo '<pre>';
        // print_r($viewMember);
        //  echo '</pre>';

         echo "<div style='height:50px;'><div class='float-right mb-2'><a href='fullscreen.php?q=/modules/Transport/transport_invoice_assign_manage_add.php' class='thickbox btn btn-primary'>Generate Invoice By Class</a>";  
         echo "&nbsp;&nbsp;<a href='fullscreen.php?q=/modules/Transport/transport_invoice_assign_student_manage_add.php' class='thickbox btn btn-primary'>Generate Invoice By Student</a>";  
         echo "&nbsp;&nbsp;<a href='index.php?q=/modules/Transport/transport_fee_add.php' class='btn btn-primary'>Add</a>";  
         echo "&nbsp;&nbsp;</div><div class='float-none'></div></div>"; 


        $table = DataTable::createPaginated('FeeStructureManage', $criteria);

        //$table->addCheckboxColumn('id',__(''))->notSortable();
        $table->addColumn('serial_number', __('Sl No'));
        $table->addColumn('schedule_name', __('Schedule Name'));
        $table->addColumn('route_name', __('Route'));
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
        $table->addColumn('feeitemname', __('Fee Item'));
        $table->addColumn('academic_year', __('Academic Year'));
        $table->addColumn('feeheadname', __('Account Head'));
        $table->addColumn('invoicename', __('Invoice Series'));
        $table->addColumn('receiptname', __('Receipt Series'));
        // $table->addColumn('class', __('Classes'));
        // $table->addColumn('assigned', __('Assigned'));
        // ACTIONS
        $table->addActionColumn()
        ->addParam('id')
        ->format(function ($facilities, $actions) use ($guid) {
            $actions->addAction('copynew', __('Copy'))
            ->setURL('/modules/Transport/transport_fee_copy.php');

            $actions->addAction('amountconfig', __('Amount Config'))
            ->setURL('/modules/Transport/transport_amount_manage.php');

            $actions->addAction('edit', __('Edit'))
                    ->setURL('/modules/Transport/transport_fee_edit.php');

            $actions->addAction('delete', __('Delete'))
                    ->setURL('/modules/Transport/transport_fee_delete.php');

            $actions->addAction('assign', __('Assign to Class'))
                    ->setURL('/modules/Transport/transport_fee_assign_manage.php');         
        });
        echo $table->render($viewMember);

        }

?>

    