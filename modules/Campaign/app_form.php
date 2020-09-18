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
use Pupilsight\Domain\Admission\AdmissionGateway;


// Module includes
require_once __DIR__ . '/moduleFunctions.php';


if (isActionAccessible($guid, $connection2, '/modules/Campaign/app_form.php') != false) {
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

    $page->breadcrumbs->add(__('Campaign List'));

 
     if (isset($_GET['return'])) {
         returnProcess($guid, $_GET['return'], null, null);
     }
     
    
    $search = isset($_GET['search'])? $_GET['search'] : '';
    
    $admissionGateway = $container->get(AdmissionGateway::class);
     $criteria = $admissionGateway->newQueryCriteria()
        ->searchBy($admissionGateway->getSearchableColumns(), $search)
        ->sortBy(['id'])
        ->fromPOST();
 
   

    $form = Form::create('filter', $_SESSION[$guid]['absoluteURL'].'/index.php', 'get');
    $form->setClass('noIntBorder fullWidth');

    $form->addHiddenValue('q', '/modules/'.$_SESSION[$guid]['module'].'/index.php');

    $row = $form->addRow();
        $row->addLabel('search', __('Search For'))->description(__(' name, Academic Year'));
        $row->addTextField('search')->setValue($criteria->getSearchText());

    $row = $form->addRow()->addClass('right_align');
        $row->addSearchSubmit($pupilsight->session, __('Clear Search'));

    echo $form->getOutput();
 
     // QUERY
     echo '<h2>';
     echo __('Campaign List');
     echo '</h2>';
    //  print_r($criteria);
    //  die();
     $dataSet = $admissionGateway->getAllCampaign($criteria);
 
     // Join a set of family data per user
     //$people = $dataSet->getColumn('pupilsightPersonID');
    
     // DATA TABLE
     $table = DataTable::createPaginated('userManage', $criteria);
	$table->addHeaderAction('Application Form', __('Application Form'))
         ->setURL('/modules/Campaign/app_form.php')
         //->addParam('search', $search)
         ->displayLabel();			
     $table->addHeaderAction('add', __('Add'))
         ->setURL('/modules/Campaign/add.php')
         //->addParam('search', $search)
         ->displayLabel();
	$table->addHeaderAction('Work Flow', __('Work Flow'))
         ->setURL('/modules/Campaign/wf_manage.php')
         //->addParam('search', $search)
         ->displayLabel();
 
 
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
    
   
    $table->addColumn('name', __('Name'))
         ->width('10%')
         ->translatable();    
 
     $table->addColumn('academic_year', __('Academic Year'))
         ->width('10%')
         ->translatable();

    $table->addColumn('seats', __('Seat'))
         ->width('10%')
         ->translatable();    
 
     $table->addColumn('start_date', __('Start Date'))
         ->context('secondary')
         ->width('16%')
         ->translatable();

    $table->addColumn('end_date', __('End Date'))
         ->context('secondary')
         ->width('16%')
         ->translatable();    
    
   

    $table->addColumn('status', __('Status'))
         ->format(function ($dataSet) {
             if ($dataSet['status'] == '1') {
                 return 'Draft';
             } else if ($dataSet['status'] == '2' ) {
                 return 'Published';
             } else {
                return 'Stoped';
             }
             return $dataSet['status'];
    });     
 
   
 
     // ACTIONS
     $table->addActionColumn()
         ->addParam('id')
         ->addParam('search', $criteria->getSearchText(true))
         ->format(function ($person, $actions) use ($guid) {
             $actions->addAction('edit', __('Edit'))
                     ->setURL('/modules/Campaign/edit.php');
 
             
            $actions->addAction('delete', __('Delete'))
                    ->setURL('/modules/Campaign/delete.php');
            
            
         });
		 $table->addmultiActionColumn()
         ->addParam('id')
		 ->addParam('name')
		 ->addParam('academic_year')
         ->addParam('search', $criteria->getSearchText(true))
         ->format(function ($person, $actions) use ($guid) {
             $actions->addmultiAction('add', __('Add Work Flow'))
                     ->setURL('/modules/Campaign/wf_add.php')
					 ->setTitle('Add Work Flow');
 
            
            
         });
	     
 
     echo $table->render($dataSet);
 
}
