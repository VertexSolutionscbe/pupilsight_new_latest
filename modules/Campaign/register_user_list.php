<?php
 
 
/*
Gibbon, Flexible & Open School System
Copyright (C) 2010, Ross Parker

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program. If not, see <http://www.gnu.org/licenses/>.
*/
//echo '<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/1.3.4/jspdf.min.js"></script>';

use Pupilsight\Services\Format;

use Pupilsight\Forms\Form;
use Pupilsight\Tables\DataTable;
use Pupilsight\Domain\Admission\AdmissionGateway;


// Module includes
require_once __DIR__ . '/moduleFunctions.php';


if (isActionAccessible($guid, $connection2, '/modules/Campaign/campaignFormList.php') == false) {
    // Access denied
    $page->addError(__('You do not have access to this action.'));
} else {
    
    $page->breadcrumbs->add(__('Register User List'));

 
     if (isset($_GET['return'])) {
         returnProcess($guid, $_GET['return'], null, null);
     }

     $id="";
     if(isset($_REQUEST['id'])?$id=$_REQUEST['id']:$id="" );
    
     $search = isset($_GET['search'])? $_GET['search'] : '';
     $admissionGateway = $container->get(AdmissionGateway::class);
     $criteria = $admissionGateway->newQueryCriteria()
        ->searchBy($admissionGateway->getSearchableColumns(), $search)
        ->sortBy(['id'])
        ->fromPOST();
 
   

    // $form = Form::create('filter', $_SESSION[$guid]['absoluteURL'].'/index.php', 'get');
    // $form->setClass('noIntBorder fullWidth');

    // // $form->addHiddenValue('q', '/modules/'.$_SESSION[$guid]['module'].'/index.php');

    //  $row = $form->addRow();
    // //     $row->addLabel('search', __('Search For'))->description(__(' name, Academic Year'));
    // //     $row->addTextField('search')->setValue($criteria->getSearchText());

    // // $row = $form->addRow()->addClass('right_align');
    // //     $row->addSearchSubmit($pupilsight->session, __('Clear Search'));
    // $row->addTextField('searchRegisterUser')->placeholder('Search');

    // echo $form->getOutput();

   // echo '<input type="text" id="searchRegisterUser" placeholder="Search">';

    echo "<div style='height:25px; margin-top:5px;'>
    <div class='float-left mb-2'>
    <input type='text' id='searchRegisterUser' placeholder='Search'>
     </div>
      <div class='float-right mb-2'>
      <a style=' margin-bottom:10px;' href=''  data-toggle='modal' data-target='#large-modal-register_list' data-noti='2'  class='sendButton_campaign_listNew btn btn-primary' >Send SMS</a>";  
    echo "&nbsp;&nbsp;<a style=' margin-bottom:10px;' href='' data-toggle='modal' data-noti='1' data-target='#large-modal-register_list' class='sendButton_campaign_listNew btn btn-primary' >Send Email</a></div></div>";
   
     // QUERY
   
    //  print_r($criteria);
    //  die();
     $dataSet = $admissionGateway->getAllRegisterUserCampaign($criteria, $id);
     
 
     // Join a set of family data per user
     //$people = $dataSet->getColumn('pupilsightPersonID');
    
     // DATA TABLE
     $table = DataTable::createPaginated('userManage', $criteria);
   
    // echo "<div style='height:50px;'><div class='float-right mb-2'><a href='index.php?q=/modules/Campaign/campaign_series_manage.php' class='btn btn-primary'>Master Campaign Series</a>"; 
    // echo "&nbsp;&nbsp;<a href='index.php?q=%2Fmodules%2FCampaign%2Fadd.php' class='btn btn-primary'>Add Campaign</a>";  
    // echo "&nbsp;&nbsp;<a href='index.php?q=%2Fmodules%2FCampaign%2FtransitionsList.php' class='btn btn-primary'>Transition</a></div><div class='float-none'></div></div>";     
	
    $table->addCheckboxColumn('id',__(''))
        ->addClass('chkbox')
        ->context('Select')
        ->notSortable()
        ->width('10%');
    $table->addColumn('serial_number', __('SI No'));  
    $table->addColumn('name', __('Name'))
         ->width('10%')
         ->translatable();    
 
     $table->addColumn('email', __('Email'))
         ->width('10%')
         ->translatable();

    $table->addColumn('mobile', __('Mobile'))
         ->width('10%')
         ->translatable(); 
         
    $table->addColumn('status', __('Status'))
         ->width('10%')
         ->translatable(); 
 
     $table->addColumn('cdt', __('Registration Date'))
        ->width('10%')
        ->translatable(); 
     
    // $table->addColumn('status', __('Status'))
    //      ->format(function ($dataSet) {
    //          if ($dataSet['status'] == '1') {
    //              return 'Draft';
    //          } else if ($dataSet['status'] == '2' ) {
    //              return 'Published';
    //          } else {
    //             return 'Stoped';
    //          }
    //          return $dataSet['status'];
    // });  
     // ACTIONS
     
     echo $table->render($dataSet);
 
}

?>
<script>
    
    $("#searchRegisterUser").on("keyup", function() {
        var value = $(this).val().toLowerCase();
        $("#expore_tbl tbody tr").filter(function() {
        $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
        });
    });

    
</script>    
