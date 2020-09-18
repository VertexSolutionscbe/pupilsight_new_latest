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
use Pupilsight\Services\Format;

use Pupilsight\Forms\Form;
use Pupilsight\Tables\DataTable;
use Pupilsight\Domain\Admission\WorkFlowGateway;


// Module includes
require_once __DIR__ . '/moduleFunctions.php';

if (isActionAccessible($guid, $connection2, '/modules/Campaign/wf_manage.php') != false) {
    // Access denied
    $page->addError(__('You do not have access to this action.'));
} else {
    // Proceed!
    //echo 'wdcwc';die();
    // $highestAction = getHighestGroupedAction($guid, $_GET['q'], $connection2);
    // if ($highestAction == false) {
    //     echo "<div class='error'>";
    //     echo __('The highest grouped action cannot be determined.');
    //     echo '</div>';
    //     return;
    // }

    $page->breadcrumbs->add(__('Work Flow List'));

 
     if (isset($_GET['return'])) {
         returnProcess($guid, $_GET['return'], null, null);
     }
     
    
    $search = isset($_GET['search'])? $_GET['search'] : '';
    
    $WorkFlowGateway = $container->get(WorkFlowGateway::class);
     $criteria = $WorkFlowGateway->newQueryCriteria()
        ->searchBy($WorkFlowGateway->getSearchableColumns(), $search)
        ->sortBy(['id'])
        ->fromPOST();
 

    $form = Form::create('filter', $_SESSION[$guid]['absoluteURL'].'/index.php', 'get');
    $form->setClass('noIntBorder fullWidth');

    $form->addHiddenValue('q', '/modules/'.$_SESSION[$guid]['module'].'/wf_manage.php');
    $id="";
	$ac_y="";
	$name="";
	if(isset($_REQUEST['id'])?$id=$_REQUEST['id']:$id="" );
	if(isset($_REQUEST['academic_year'])?$ac_y=$_REQUEST['academic_year']:$ac_y="" );
	if(isset($_REQUEST['name'])?$name=$_REQUEST['name']:$name="" );

	$_SESSION['tb_name']='workflow';
	
	$camp_id=isset($_REQUEST['id'])? $_REQUEST['id'] : '';
	$_SESSION['academic_year']=isset($_REQUEST['academic_year'])? $_REQUEST['academic_year'] : '';
	$_SESSION['name']=isset($_REQUEST['name'])? $_REQUEST['name'] : '';
	
	$form->addHiddenValue('tb_name', $_SESSION['tb_name']);
	$form->addHiddenValue('campaign_id', $camp_id);
	$form->addHiddenValue('academic_year', $_SESSION['academic_year']);
	$form->addHiddenValue('name', $_SESSION['name']);

    $row = $form->addRow();
        $row->addLabel('search', __('Search For'))->description(__('Academic Year'));
        $row->addTextField('search')->setValue($criteria->getSearchText());

    $row = $form->addRow()->addClass('right_align');
        $row->addSearchSubmit($pupilsight->session, __('Clear Search'));

    echo $form->getOutput();
 
     // QUERY
     echo '<h2>';
     echo __('Work Flow List');
     echo '</h2>';
    //  print_r($criteria);
    //  die();
     //$dataSet1 = $admissionGateway->getAllWorkflow($criteria);
	 $dataSet = $WorkFlowGateway->getAllWorkflow($criteria);
	 
	// echo "<pre>"; 	 
	// print_r($dataSet);
	
     // Join a set of family data per user
     //$people = $dataSet->getColumn('pupilsightPersonID');
    
     // DATA TABLE
      $table = DataTable::createPaginated('userManage', $criteria);
	// 	$table->addHeaderAction('work flow state', __('Work Flow State'))
    //      ->setURL('/modules/Campaign/wf_state_list.php')
    //      //->addParam('search', $search)
    //      ->displayLabel();	
    if(count($dataSet) == '0'){ 
    echo "<div style='height:50px;'><div class='float-right mb-2'><a href='index.php?q=modules/Campaign/wf_add.php&id=".$id."&name=".$name."&academic_year=".$ac_y."&search=' class='btn btn-primary'>Add Work Flow</a></div></div>";     
   }  
 
    //  $table->addMetaData('filterOptions', [
    //      'role:student'    => __('Role').': '.__('Student'),
    //      'role:parent'     => __('Role').': '.__('Parent'),
    //      'role:staff'      => __('Role').': '.__('Staff'),
    //      'status:full'     => __('Status').': '.__('Full'),
    //      'status:left'     => __('Status').': '.__('Left'),
    //      'status:expected' => __('Status').': '.__('Expected'),
    //      'date:starting'   => __('Before Start Date'),
    //      'date:ended'      => __('After End Date'),
    //  ]);
 
     // COLUMNS
    
//    $table->addColumn('id', __('SI No'))
//          ->width('10%')
//          ->translatable();    
    $table->addColumn('wfname', __('WF Name'))
         ->width('10%')
         ->translatable();    
 
     $table->addColumn('code', __('WF Code'))
         ->width('10%')
         ->translatable();

    $table->addColumn('description', __('Description'))
         ->width('10%')
         ->translatable(); 
 		 
		$table->addColumn('academic_year', __('Academic Year'))
         ->width('10%')
         ->translatable();
		 $table->addColumn('state', __('Work Flow State'))
         ->width('10%')
         ->translatable();
		
     // ACTIONS
     $table->addActionColumn()
         ->addParam('academic_year',$_SESSION['academic_year'])
		 ->addParam('name',$_SESSION['name'])
		 ->addParam('id')
         ->addParam('search', $criteria->getSearchText(true))
         ->format(function ($person, $actions) use ($guid) {
            $actions->addAction('edit', __('Edit'))
                     ->setURL('/modules/Campaign/wf_edit.php');
            // $actions->addAction('delete', __('Delete'))
            //         ->setURL('/modules/Campaign/wf_delete.php');
            
            
         });
		
     echo $table->render($dataSet);
 
}
