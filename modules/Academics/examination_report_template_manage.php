<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Tables\DataTable;
use Pupilsight\Services\Format;
use Pupilsight\Domain\Curriculum\CurriculamGateway;

if (isActionAccessible($guid, $connection2, '/modules/Academics/examination_report_template_manage.php') == false) {
    //Acess denied
    echo "<div class='error'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    //Proceed!
   $page->breadcrumbs->add(__('Report Templates'));

   if (isset($_GET['return'])) {
      returnProcess($guid, $_GET['return'], null, null);
   }

   $CurriculumGateway = $container->get(CurriculamGateway::class);

   // QUERY
   $criteria = $CurriculumGateway->newQueryCriteria()
         ->sortBy(['id'])
         ->fromPOST();

   $templates = $CurriculumGateway->getReportTemplate($criteria);

   // DATA TABLE
   $table = DataTable::createPaginated('FeeItemTypeManage', $criteria);

   // $table->addHeaderAction('add', __('Add'))
   //     ->setURL('/modules/Finance/program_manage_add.php')
   //     ->displayLabel();
   
   echo "<div style='height:50px;'><div class='float-right mb-2'><a href='fullscreen.php?q=/modules/Academics/examination_report_template_add.php' class='thickbox btn btn-primary'>Add</a>
   <a href='public/report_template/Default_Report_Template.docx'  class='btn btn-primary' download>Deafult Template <i class='fas fa-download'></i></a></div><div class='float-none'></div></div>";  

   
   
   //$table->addColumn('sequenceNumber', __('sequenceNumber'));
   $table->addColumn('serial_number', __('SI No'));
   $table->addColumn('name', __('Template Name'));
  
   $table->addColumn('filename', __('Template File'))
   ->format(function ($dataSet) {
       if($dataSet['filename'] != '') {
           return '<a href="public/report_template/'.$dataSet['filename'].'" download>'.$dataSet['filename'].'</a>';
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
                     ->setURL('/modules/Academics/examination_report_template_delete.php');
         });

   echo $table->render($templates);

  //echo formatName('', $row['preferredName'], $row['surname'], 'Staff', false, true);
}
