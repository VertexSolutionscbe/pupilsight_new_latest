<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Tables\DataTable;
use Pupilsight\Services\Format;
use Pupilsight\Domain\Finance\FeesGateway;

if (isActionAccessible($guid, $connection2, '/modules/Finance/fee_receipts_manage.php') == false) {
    //Acess denied
    echo "<div class='error'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    //Proceed!
   $page->breadcrumbs->add(__('Fee Receipts Templates'));

   if (isset($_GET['return'])) {
      returnProcess($guid, $_GET['return'], null, null);
   }

   $FeesGateway = $container->get(FeesGateway::class);

   // QUERY
   $criteria = $FeesGateway->newQueryCriteria()
         ->sortBy(['id'])
         ->fromPOST();

   $templates = $FeesGateway->getReceiptTemplate($criteria);

   // DATA TABLE
   $table = DataTable::createPaginated('FeeItemTypeManage', $criteria);

   // $table->addHeaderAction('add', __('Add'))
   //     ->setURL('/modules/Finance/program_manage_add.php')
   //     ->displayLabel();
   
   echo "<div style='height:50px;'><div class='float-right mb-2'><a href='fullscreen.php?q=/modules/Finance/fee_receipt_template_add.php' class='thickbox btn btn-primary'>Add</a>
   <a href='thirdparty/phpword/templates/receipt_1.docx'  class='btn btn-primary' download>Deafult Template <i class='fas fa-download'></i></a></div><div class='float-none'></div></div>";  

   
   
   //$table->addColumn('sequenceNumber', __('sequenceNumber'));
   $table->addColumn('serial_number', __('SI No'));
   $table->addColumn('name', __('Template Name'));
  
   $table->addColumn('filename', __('Template File'))
   ->format(function ($dataSet) {
       if($dataSet['filename'] != '') {
           return '<a href="public/receipt_template/'.$dataSet['filename'].'" download>'.$dataSet['filename'].'</a>';
       } 
       return $dataSet['filename'];
   });  
   
         
   // ACTIONS
   $table->addActionColumn()
         ->addParam('id')
         ->format(function ($facilities, $actions) use ($guid) {
            // $actions->addAction('editnew', __('Edit'))
            //          ->setURL('/modules/Finance/fee_item_type_manage_edit.php');

            $actions->addAction('delete', __('Delete'))
                     ->setURL('/modules/Finance/fee_receipt_template_delete.php');
         });

   echo $table->render($templates);

  //echo formatName('', $row['preferredName'], $row['surname'], 'Staff', false, true);
}
