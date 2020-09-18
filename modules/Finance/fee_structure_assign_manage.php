<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Tables\DataTable;
use Pupilsight\Services\Format;
use Pupilsight\Domain\Finance\FeesGateway;

if (isActionAccessible($guid, $connection2, '/modules/Finance/fee_structure_assign_manage.php') == false) {
    //Acess denied
    echo "<div class='error'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    //Proceed!
    $page->breadcrumbs->add(__('Manage Fee Structure Assign'));

    if (isset($_GET['return'])) {
        returnProcess($guid, $_GET['return'], null, null);
    }

    $FeesGateway = $container->get(FeesGateway::class);

    // QUERY
    $id = $_GET['id'];
   
    $criteria = $FeesGateway->newQueryCriteria()
        ->sortBy(['id'])
        ->fromPOST();

    $yearGroups = $FeesGateway->getFeesStructureAssignItem($criteria);

    // DATA TABLE
    $table = DataTable::createPaginated('FeeStructureAssignManage', $criteria);

    // $table->addHeaderAction('add', __('Add'))
    //     ->setURL('/modules/Finance/program_manage_add.php')
    //     ->displayLabel();
    
    echo "<div style='height:50px;'><div class='float-right mb-2'><a href='fullscreen.php?q=/modules/Finance/fee_structure_assign_manage_add.php&sid=".$id."' class='thickbox btn btn-primary'>Add</a></div><div class='float-none'></div></div>";  

    
    if(!empty($yearGroups->data)){
        $table->addColumn('structure_name', __('Fee Structure'));
        $table->addColumn('program_name', __('Organisation'));
        $table->addColumn('class', __('Class'));
        $table->addActionColumn()
            ->addParam('id')
            ->format(function ($facilities, $actions) use ($guid) {
                $actions->addAction('editnew', __('Edit'))
                        ->setURL('/modules/Finance/fee_structure_assign_manage_edit.php');

                $actions->addAction('delete', __('Delete'))
                        ->setURL('/modules/Finance/fee_structure_assign_manage_delete.php');
            });

        echo $table->render($yearGroups);
    } else {
        echo '<table class="table display data-table text-nowrap dataTable no-footer">
            <thead>
                <tr>
                    <th>Fee Structure</th>
                    <th>Organisation</th>
                    <th>Class</th>
                    <th>Action</th>
                </tr>
            </thead>  
            <tbody> 
            <tr>
            <th colspan="4" style="text-align: center;background-color: white;">No classes are assigned, please click on Add to assign</th>
            </tr>     
            </tbody>
        
        </table>';
    }
    

    //echo formatName('', $row['preferredName'], $row['surname'], 'Staff', false, true);
}
