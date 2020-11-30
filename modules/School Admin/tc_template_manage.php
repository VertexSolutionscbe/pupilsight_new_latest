<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Tables\DataTable;
use Pupilsight\Services\Format;
use Pupilsight\Domain\School\SchoolYearGateway;

if (isActionAccessible($guid, $connection2, '/modules/School Admin/tc_template_manage.php') == false) {
    //Acess denied
    echo "<div class='error'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    //Proceed!
   $page->breadcrumbs->add(__('Fee Receipts Templates'));
   $pupilsightSchoolYearID = $_SESSION[$guid]['pupilsightSchoolYearID'];

   if (isset($_GET['return'])) {
      returnProcess($guid, $_GET['return'], null, null);
   }

   $SchoolYearGateway = $container->get(SchoolYearGateway::class);

   // QUERY
   $criteria = $SchoolYearGateway->newQueryCriteria()
         ->sortBy(['id'])
         ->fromPOST();

   $type = 'tc';
   $templates = $SchoolYearGateway->getTCTemplate($criteria, $pupilsightSchoolYearID);

   // DATA TABLE
   $table = DataTable::createPaginated('FeeItemTypeManage', $criteria);

   // $table->addHeaderAction('add', __('Add'))
   //     ->setURL('/modules/School Admin/program_manage_add.php')
   //     ->displayLabel();
   
   echo "<div style='height:50px;'><div class='float-right mb-2'><a href='fullscreen.php?q=/modules/School Admin/tc_template_add.php' class='thickbox btn btn-primary'>Add</a>
   <a href='public/doc_template/default/tc_template.docx'  class='btn btn-primary' download>Deafult Template</a>
   <a href='index.php?q=/modules/School Admin/form_template_fields.php'  class='btn btn-primary'>Template Fields</a>
   </div><div class='float-none'></div></div>";  

   
   
   //$table->addColumn('sequenceNumber', __('sequenceNumber'));
   $table->addColumn('serial_number', __('SI No'));
   $table->addColumn('progname', __('Program'));
   $table->addColumn('classes', __('Class'));
   $table->addColumn('type', __('Type'));
   $table->addColumn('name', __('Template Name'));
  
   $table->addColumn('filename', __('Template File'))
   ->format(function ($dataSet) {
       if($dataSet['filename'] != '') {
           return '<a href="public/doc_template/'.$dataSet['filename'].'" download>'.$dataSet['filename'].'</a>';
       } 
       return $dataSet['filename'];
   }); 
   
         
   // ACTIONS
   $table->addActionColumn()
         ->addParam('id')
         ->format(function ($facilities, $actions) use ($guid) {
            // $actions->addAction('editnew', __('Edit'))
            //          ->setURL('/modules/School Admin/fee_item_type_manage_edit.php');

            $actions->addAction('delete', __('Delete'))
                     ->setURL('/modules/School Admin/tc_template_delete.php');
         });

   echo $table->render($templates);

  //echo formatName('', $row['preferredName'], $row['surname'], 'Staff', false, true);
}
