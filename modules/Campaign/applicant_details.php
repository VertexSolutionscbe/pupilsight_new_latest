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

if (isActionAccessible($guid, $connection2, '/modules/Campaign/index.php') == false) {
    // Access denied
    $page->addError(__('You do not have access to this action.'));
} else {
    $role = $_SESSION[$guid]['pupilsightRoleIDPrimary'];
    

    $page->breadcrumbs->add(__('Applicant Details'));



    if (isset($_GET['return'])) {
        returnProcess($guid, $_GET['return'], null, null);
    }


    $search = isset($_GET['search']) ? $_GET['search'] : '';

    $admissionGateway = $container->get(AdmissionGateway::class);
    $criteria = $admissionGateway->newQueryCriteria()
        ->pageSize(5000)
        //->sortBy(['id'])
        ->fromPOST();

    // $form = Form::create('filter', $_SESSION[$guid]['absoluteURL'] . '/index.php', 'get');
    // //$form->setClass('mb-4');

    // $form->addHiddenValue('q', '/modules/' . $_SESSION[$guid]['module'] . '/index.php');

    // $row = $form->addRow();
    // $row->addLabel('search', __('Search For (name, Academic Year)'))->setClass('mb-2');
    // $row->addTextField('search')->setValue($criteria->getSearchText())->setClass('mb-2');

    // //$row = $form->addRow()->addClass('right_align');
    // $row->addSearchSubmit($pupilsight->session, __('Clear Search'))->setClass('mb-2');

    // echo $form->getOutput();

    // QUERY
    //echo '<h2>';
    //echo __('Application List');
    //echo '</h2>';
    //  print_r($criteria);
    //  die();
    $dataSet = $admissionGateway->getAllApplicantData($criteria);

    
    $table = DataTable::createPaginated('userManage', $criteria);
    


    $table->addColumn('serial_number', __('SI No'));
    $table->addColumn('campaign_name', __('Campaign Name'))
        ->width('10%')
        ->translatable();

    $table->addColumn('id', __('Submission ID'))
        ->width('10%')
        ->translatable();

    $table->addColumn('pupilsightProgramID', __('Program ID'))
        ->width('10%')
        ->translatable();

    $table->addColumn('program', __('Program Name'))
        ->width('10%')
        ->translatable();

    $table->addColumn('pupilsightYearGroupID', __('Class ID'))
        ->width('10%')
        ->translatable();

    $table->addColumn('class', __('Class Name'))
        ->width('10%')
        ->translatable();

    $table->addColumn('application_id', __('Application ID'))
        ->width('10%')
        ->translatable();

    $table->addColumn('created_at', __('Date & Time'))
        ->width('10%')
        ->translatable();

    
    echo $table->render($dataSet);
}

?>
