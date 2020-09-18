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
use Pupilsight\Domain\School\SchoolYearGateway;

// Module includes
require_once __DIR__ . '/moduleFunctions.php';

if (isActionAccessible($guid, $connection2, '/modules/School Admin/email_template_manage.php') == false) {
    // Access denied
    $page->addError(__('You do not have access to this action.'));
} else {
    $pupilsightSchoolYearID = $_SESSION[$guid]['pupilsightSchoolYearID'];
    
    $page->breadcrumbs->add(__('Email Templates'));

 
     if (isset($_GET['return'])) {
         returnProcess($guid, $_GET['return'], null, null);
     }
     
    
    $search = isset($_GET['search'])? $_GET['search'] : '';
    
    $schoolYearGateway = $container->get(SchoolYearGateway::class);
     $criteria = $schoolYearGateway->newQueryCriteria()
        ->sortBy(['pupilsightTemplateID'])
        ->fromPOST();

    echo "<div style='height:50px;border-bottom: 1px solid rgba(0, 0, 0, 0.5);'><div class=' mb-2'><a href='index.php?q=/modules/School Admin/email_template_manage.php' class='btn btn-primary active'>Email</a>";  
    echo "&nbsp;&nbsp;<a href='index.php?q=/modules/School Admin/sms_template_manage.php' class='btn btn-primary'>SMS</a></div><div class='float-none'></div></div>";  
 
   

    // $form = Form::create('filter', $_SESSION[$guid]['absoluteURL'].'/index.php', 'get');
    // $form->setClass('noIntBorder fullWidth');

    // $form->addHiddenValue('q', '/modules/'.$_SESSION[$guid]['module'].'/index.php');

    // $row = $form->addRow();
    //     $row->addLabel('search', __('Search For'))->description(__(' name, Academic Year'));
    //     $row->addTextField('search')->setValue($criteria->getSearchText());

    // $row = $form->addRow()->addClass('right_align');
    //     $row->addSearchSubmit($pupilsight->session, __('Clear Search'));

    // echo $form->getOutput();
 
     // QUERY
    
    //  print_r($criteria);
    //  die();
     $dataSet = $schoolYearGateway->getAllEmailTemplate($criteria);
    //      print_r($dataSet);
    //   die();
 
     // DATA TABLE
     $table = DataTable::createPaginated('userManage', $criteria);

     echo '<h2>';
     echo __('Email Templates');
     echo '</h2>';
    
    echo "<div style='height:50px;'><div class='float-right mb-2'><a href='index.php?q=/modules/School Admin/email_template_manage_add.php' class='btn btn-primary'>Add</a></div><div class='float-none'></div></div>";  
    //echo "&nbsp;&nbsp;<a href='index.php?q=%2Fmodules%2FCampaign%2FtransitionsList.php' class='btn btn-primary'>Transition</a></div><div class='float-none'></div></div>";     
	
    $table->addColumn('serial_number', __('SI No'));  
    $table->addColumn('name', __('Template Name'));    
 
     
    $table->addColumn('status', __('Status'))
        ->format(function ($dataSet) {
            if ($dataSet['status'] == '1') {
                return 'Active';
            } else {
            return 'In Active';
            }
            return $dataSet['status'];
    });    
 
     $table->addColumn('createdBy', __('Created by'));

    $table->addColumn('entities', __('Entities'));    

    $table->addColumn('state', __('State'))
         ->format(function ($dataSet) {
             if ($dataSet['state'] == '1') {
                 return 'In Use';
             } else {
                return 'Not Use';
             }
             return $dataSet['state'];
    });  
     // ACTIONS
     $table->addActionColumn()
         ->addParam('pupilsightTemplateID')
         ->format(function ($person, $actions) use ($guid) {
             $actions->addAction('edit', __('Edit'))
                ->setURL('/modules/School Admin/email_template_manage_edit.php');
            $actions->addAction('delete', __('Delete'))
                ->setURL('/modules/School Admin/email_template_manage_delete.php');
            
         });

         
     echo $table->render($dataSet);
 
}