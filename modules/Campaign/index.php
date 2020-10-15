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
    if ($role == '004' || $role == '033') {
        $URL = $_SESSION[$guid]['absoluteURL'] . '/index.php?q=/modules/Campaign/check_status.php';
        header("Location: {$URL}");
    }

    $pupilsightSchoolYearID = $_SESSION[$guid]['pupilsightSchoolYearID'];

    // Proceed!
    //echo 'wdcwc';die();
    // $highestAction = getHighestGroupedAction($guid, $_GET['q'], $connection2);
    // if ($highestAction == false) {
    //     echo "<div class='error'>";
    //     echo __('The highest grouped action cannot be determined.');
    //     echo '</div>';
    //     return;
    // }

    $page->breadcrumbs->add(__('Application List'));


    if (isset($_GET['return'])) {
        returnProcess($guid, $_GET['return'], null, null);
    }


    $search = isset($_GET['search']) ? $_GET['search'] : '';

    $admissionGateway = $container->get(AdmissionGateway::class);
    $criteria = $admissionGateway->newQueryCriteria()
        ->searchBy($admissionGateway->getSearchableColumns(), $search)
        ->sortBy(['id'])
        ->fromPOST();

    $form = Form::create('filter', $_SESSION[$guid]['absoluteURL'] . '/index.php', 'get');
    //$form->setClass('mb-4');

    $form->addHiddenValue('q', '/modules/' . $_SESSION[$guid]['module'] . '/index.php');

    $row = $form->addRow();
    $row->addLabel('search', __('Search For (name, Academic Year)'))->setClass('mb-2');
    $row->addTextField('search')->setValue($criteria->getSearchText())->setClass('mb-2');

    //$row = $form->addRow()->addClass('right_align');
    $row->addSearchSubmit($pupilsight->session, __('Clear Search'))->setClass('mb-2');

    echo $form->getOutput();

    // QUERY
    //echo '<h2>';
    //echo __('Application List');
    //echo '</h2>';
    //  print_r($criteria);
    //  die();
    $dataSet = $admissionGateway->getAllCampaign($criteria, $pupilsightSchoolYearID);


    // Join a set of family data per user
    //$people = $dataSet->getColumn('pupilsightPersonID');

    // DATA TABLE
    $table = DataTable::createPaginated('userManage', $criteria);
    //  $table->addHeaderAction('Work Flow Transitions List', __('Work Flow Transitions List'))
    //  ->setURL('/modules/Campaign/add_wf_transitionsList.php')
    //  //->addParam('search', $search)
    //  ->displayLabel();
    //  $table->addHeaderAction('Work Flow Transitions List', __('Apply here'))
    //  ->setURL('/modules/Campaign/active_campaign.php')
    //  //->addParam('search', $search)
    //  ->displayLabel();	


    //  $table->addHeaderAction('add', __('Add'))
    //      ->setURL('/modules/Campaign/add.php')
    //      //->addParam('search', $search)
    //      ->displayLabel();
    echo "<div style='height:50px;'><div class='float-right'><a href='index.php?q=/modules/Campaign/campaign_series_manage.php' class='btn btn-secondary mb-2 mr-2'>Master Campaign Series</a>";
    echo "<a href='index.php?q=%2Fmodules%2FCampaign%2Fadd.php' class='btn btn-secondary mb-2 mr-2'>Add Campaign</a>";
    echo "<a href='index.php?q=%2Fmodules%2FCampaign%2FtransitionsList.php' class='btn btn-secondary mb-2'>Transition</a></div><div class='float-none'></div></div>";
    // $table->addHeaderAction('Transition', __('Transition'))
    //      ->setURL('/modules/Campaign/transitions.php')
    //      //->addParam('search', $search)
    //      ->displayLabel();

    // $table->addHeaderAction('Fluent', __('Fluent'))
    //      ->setURL('/modules/Campaign/fluent.php')
    //      ->displayLabel();     


    // $table->addHeaderAction('Work Flow', __('Work Flow'))
    //      ->setURL('/modules/Campaign/wf_manage.php')     
    //      ->displayLabel();	 
    // $table->addHeaderAction('checkstatus', __('Check Status'))
    //  ->setURL('/modules/Campaign/check_status.php')
    //  ->addParam('search', $search)
    //  ->displayLabel();
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

    $table->addColumn('serial_number', __('SI No'));
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
        ->translatable()
        ->format(function ($person) {
            $dt = new DateTime($person['start_date']);
            $st_date = $dt->format('d-m-Y');
            return $st_date;
        });


    $table->addColumn('end_date', __('End Date'))
        ->context('secondary')
        ->width('16%')
        ->translatable()
        ->format(function ($person) {
            $dt = new DateTime($person['end_date']);
            $end_date = $dt->format('d-m-Y');
            return $end_date;
        });


    $table->addColumn('status', __('Status'))
        ->format(function ($dataSet) {
            if ($dataSet['status'] == '1') {
                return 'Draft';
            } else if ($dataSet['status'] == '2') {
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

            $actions->addAction('list', __('Submitted Form'))
                ->setURL('/modules/Campaign/campaignFormList.php');

            $actions->addAction('View', __('View Application Form'))
                ->setTitle('form')
                ->setIcon('eye')
                ->setURL('/modules/Campaign/view_selected_campaign_form.php')
                ->modalWindow(1100, 550);

            $actions->addAction('registereduser', __('Registered User'))
                ->setURL('/modules/Campaign/register_user_list.php');

            $actions->addAction('applicationtemplate', __('Upload Template'))
                ->setURL('/modules/Campaign/form_template_manage.php');
        });

    $table->addmultiActionColumn()
        ->addParam('academic_year')
        ->addParam('name')
        ->addParam('id')
        ->format(function ($person, $actions) use ($guid) {
            $actions->addmultiAction('view', __('Work Flow'))
                ->setURL('/modules/Campaign/wf_manage.php')
                ->setClass('center_algn');
        });



    echo $table->render($dataSet);
}

?>

<script>
    $(document).ready(function() {
        $('#expore_tbl tr:eq(0) th:last-child').text("Workflow Actions");
    });
</script>
<style>
    .center_algn {
        margin-left: 43px;
    }
</style>