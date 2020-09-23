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

if (isActionAccessible($guid, $connection2, '/modules/Campaign/wf_state_list.php') != false) {
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

    $page->breadcrumbs->add(__('Work Flow State List'));

 
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

    $form->addHiddenValue('q', '/modules/'.$_SESSION[$guid]['module'].'/wf_state_list.php');

    $row = $form->addRow();
        $row->addLabel('search', __('Search For'))->description(__(' State Name, Code'));
        $row->addTextField('search')->setValue($criteria->getSearchText());

    $row = $form->addRow()->addClass('right_align');
        $row->addSearchSubmit($pupilsight->session, __('Clear Search'));

    echo $form->getOutput();
 
     // QUERY
     echo '<h2>';
     echo __('Work Flow State List');
     echo '</h2>';
    //  print_r($criteria);
    //  die();
     $dataSet = $admissionGateway->getAllWorkflowstate($criteria);
 
     // Join a set of family data per user
     //$people = $dataSet->getColumn('pupilsightPersonID');
    
     // DATA TABLE
     $table = DataTable::createPaginated('userManage', $criteria);
 
 
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
    
   $table->addColumn('id', __('SI No'))
         ->width('10%')
         ->translatable();    
    $table->addColumn('name', __('State Name'))
         ->width('10%')
         ->translatable();    
 
     $table->addColumn('display_name', __('Display Name'))
         ->width('10%')
         ->translatable();

    $table->addColumn('code', __('Code'))
         ->width('10%')
         ->translatable(); 
 		 
		
		
     // ACTIONS
     $table->addActionColumn()
         ->addParam('id')
         ->addParam('search', $criteria->getSearchText(true))
         ->format(function ($person, $actions) use ($guid) {
             $actions->addAction('edit', __('Edit'))
                     ->setURL('/modules/Campaign/wf_state_edit.php');
 
             
            $actions->addAction('delete', __('Delete'))
                    ->setURL('/modules/Campaign/wf_state_delete.php');
            
            
         });
		
 
     echo $table->render($dataSet);
 
}
