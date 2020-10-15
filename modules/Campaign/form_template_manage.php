<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Tables\DataTable;
use Pupilsight\Services\Format;
use Pupilsight\Domain\Admission\AdmissionGateway;

if (isActionAccessible($guid, $connection2, '/modules/Campaign/index.php') == false) {
    //Acess denied
    echo "<div class='error'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    //Proceed!
   $page->breadcrumbs->add(__('Application Form Template'));

   if (isset($_GET['return'])) {
      returnProcess($guid, $_GET['return'], null, null);
   }

   $AdmissionGateway = $container->get(AdmissionGateway::class);

   // QUERY
   $criteria = $AdmissionGateway->newQueryCriteria()
         ->sortBy(['id'])
         ->fromPOST();

    $id = $_GET['id'];

   $templates = $AdmissionGateway->getApplicationTemplate($criteria, $id);
//    echo '<pre>';
//    print_r($templates);
//    echo '</pre>';
   // DATA TABLE
   $table = DataTable::createPaginated('FeeItemTypeManage', $criteria);

   // $table->addHeaderAction('add', __('Add'))
   //     ->setURL('/modules/Finance/program_manage_add.php')
   //     ->displayLabel();
   
    echo "<div style='height:50px;'><div class='float-right mb-2'>";
    if(empty($templates->data)){
        echo "<a href='fullscreen.php?q=/modules/Campaign/form_template_add.php&id=".$id."' class='thickbox btn btn-primary'>Add</a>&nbsp;&nbsp;";
    }
    echo "<a href='index.php?q=/modules/Campaign/form_template_fields.php&id=".$id."'  class='btn btn-primary'>Template Fields</a></div><div class='float-none'></div></div>";  

   
   
   //$table->addColumn('sequenceNumber', __('sequenceNumber'));
   $table->addColumn('serial_number', __('SI No'));
   $table->addColumn('template_name', __('Template Name'));
  
   $table->addColumn('template_filename', __('Template File'))
   ->format(function ($dataSet) {
       if($dataSet['template_filename'] != '') {
           return '<a href="public/application_template/'.$dataSet['template_filename'].'" download>'.$dataSet['template_filename'].'</a>';
       } 
       return $dataSet['template_filename'];
   });  
   
         
   // ACTIONS
   $table->addActionColumn()
         ->addParam('id')
         ->format(function ($facilities, $actions) use ($guid) {
            // $actions->addAction('editnew', __('Edit'))
            //          ->setURL('/modules/Finance/fee_item_type_manage_edit.php');

            $actions->addAction('delete', __('Delete'))
                     ->setURL('/modules/Campaign/form_template_delete.php');
         });

   echo $table->render($templates);

  //echo formatName('', $row['preferredName'], $row['surname'], 'Staff', false, true);
}
