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

if (isActionAccessible($guid, $connection2, '/modules/Campaign/wf_trsnsition_list.php') != false) {
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

    $page->breadcrumbs->add(__('Work Flow Transition'));

 
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

    $form->addHiddenValue('q', '/modules/'.$_SESSION[$guid]['module'].'/add_wf_transitionsList.php');
     
    $row = $form->addRow();
        $row->addLabel('search', __('Search For'))->description(__('From State,To State'));
        $row->addTextField('search')->setValue($criteria->getSearchText());

    $row = $form->addRow()->addClass('right_align');
        $row->addSearchSubmit($pupilsight->session, __('Clear Search'));

    echo $form->getOutput();
 
     // QUERY
     echo '<h2>';
     echo __('Work Flow State List');
     echo '</h2>';
    
     $dataSet = $admissionGateway->getAllWorkflowTransition($criteria);
    
     /// print_r($dataSet>from_state);
    
     // DATA TABLE
     $table = DataTable::createPaginated('userManage', $criteria);
     $table->addHeaderAction('add', __('Add'))
     ->setURL('/modules/Campaign/add_wf_transitions.php')
     //->addParam('search', $search)
     ->displayLabel();
  // `id``from_state``to_state``transition_display_name``tansition_action``cuid``auto_gen_INV``tansition_action``cuid`
    
   $table->addColumn('id', __('SI No'))
         ->width('0.1%')
         ->translatable();    
    $table->addColumn('from_state', __('From State'))
         ->width('10%')
         ->translatable();    
 
     $table->addColumn('to_state', __('To State'))
         ->width('10%')
         ->translatable();

   
		
		
     // ACTIONS
     $table->addActionColumn()
         ->addParam('id')
         ->addParam('search', $criteria->getSearchText(true))
         ->format(function ($person, $actions) use ($guid) {
             $actions->addAction('edit', __('Edit'))
             ->setURL('/modules/Campaign/edit_wf_transition.php');
             
            $actions->addAction('delete', __('Delete'))
                    ->setURL('/modules/Campaign/delete_wf_transition.php');
            
            
         });
		
 
     echo $table->render($dataSet);

 
}
