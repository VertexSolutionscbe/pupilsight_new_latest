<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Tables\DataTable;
use Pupilsight\Services\Format;
use Pupilsight\Domain\Finance\FeesGateway;


if (isActionAccessible($guid, $connection2, '/modules/Finance/fees_invoices_list_manage.php') == false) {
    //Acess denied
    echo "<div class='error'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    //Proceed!
    $page->breadcrumbs->add(__('Manage Fee Structure Student Assign'));

    if (isset($_GET['return'])) {
        returnProcess($guid, $_GET['return'], null, null);
    }

    $FeesGateway = $container->get(FeesGateway::class);

    // QUERY
    //$id = $_GET['id'];
   
    $criteria = $FeesGateway->newQueryCriteria()
        ->sortBy(['id'])
        ->fromPOST();

    $yearGroups = $FeesGateway->getFeesStructureAssignStudent($criteria);

    // DATA TABLE
    $table = DataTable::createPaginated('FeeInvoiceListManage', $criteria);

    // $table->addHeaderAction('add', __('Add'))
    //     ->setURL('/modules/Finance/program_manage_add.php')
    //     ->displayLabel();
    
    echo "<a style='display:none' id='clickStudentPage' href='fullscreen.php?q=/modules/Finance/fee_structure_assign_student_manage_add.php'  class='thickbox '>Assign Fee Structure</a>";  
    echo "<a style='display:none' id='deleteStudentFeeStructure' href='fullscreen.php?q=/modules/Finance/fee_structure_assign_student_manage_delete.php'  class='thickbox '>Delete Fee Structure</a>";  
    echo "<a style='display:none' id='massDeleteStudentFeeStructure' href='fullscreen.php?q=/modules/Finance/fee_structure_assign_student_manage_massDelete.php'  class='thickbox '>Delete Fee Structure</a>";  


   // echo "<div style='height:50px;'><div class='float-right mb-2'><a id='assignStudentPage' class=' btn btn-primary'>Assign Fee Structure</a>";  

   // echo "&nbsp;&nbsp;<a id='deleteStudentPage' class=' btn btn-primary'>Delete Fee Structure</a>";

   // echo "&nbsp;&nbsp;<a  id='massDeleteStudentPage' class=' btn btn-primary'>Mass Delete</a></div><div class='float-none'></div></div>";  

    
  /*  $table->addCheckboxColumn('stuid',__(''))
    ->setClass('chkbox')
        ->context('Select')
        ->notSortable();*/
    $table->addColumn('1', __('Transaction Id'));
    $table->addColumn('2', __('Receipt No'));
    $table->addColumn('3', __('Total Amount'));
    $table->addColumn('4', __('Receipt'));
    //$table->addColumn('section', __('Section'));
   
        
    // ACTIONS
    // $table->addActionColumn()
    //     ->addParam('id')
    //     ->format(function ($facilities, $actions) use ($guid) {
    //         $actions->addAction('editnew', __('Edit'))
    //                 ->setURL('/modules/Finance/fee_structure_assign_student_manage_edit.php');

    //         $actions->addAction('delete', __('Delete'))
    //                 ->setURL('/modules/Finance/fee_structure_assign_student_manage_delete.php');
    //     });

    echo $table->render($yearGroups);

    //echo formatName('', $row['preferredName'], $row['surname'], 'Staff', false, true);
}
