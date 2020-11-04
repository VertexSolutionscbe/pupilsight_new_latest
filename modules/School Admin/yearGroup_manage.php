<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Tables\DataTable;
use Pupilsight\Services\Format;
use Pupilsight\Domain\School\YearGroupGateway;

if (isActionAccessible($guid, $connection2, '/modules/School Admin/yearGroup_manage.php') == false) {
    //Acess denied
    echo "<div class='alert alert-danger'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    //Proceed!
    $page->breadcrumbs->add(__('Manage Year Groups'));

    if (isset($_GET['return'])) {
        returnProcess($guid, $_GET['return'], null, null);
    }

    $yearGroupGateway = $container->get(YearGroupGateway::class);

    // QUERY
    $criteria = $yearGroupGateway->newQueryCriteria()
        ->pageSize(1000)
        ->sortBy(['sequenceNumber'])
        ->fromPOST();

    $yearGroups = $yearGroupGateway->queryYearGroups($criteria);

    // DATA TABLE
    $table = DataTable::createPaginated('yearGroupManage', $criteria);

    $table->addHeaderAction('add', __('Add'))
        ->setID('btnRight')
        ->setURL('/modules/School Admin/yearGroup_manage_add.php')
        ->displayLabel();

    $table->addColumn('sequenceNumber', __('sequenceNumber'));
    $table->addColumn('name', __('Name'));
    $table->addColumn('nameShort', __('Short Name'));
    // $table->addColumn('pupilsightPersonIDHOY', __('Head of Year'))
    //     ->format(function($values) {
    //         if (!empty($values['preferredName']) && !empty($values['surname'])) {
    //             return Format::name('', $values['preferredName'], $values['surname'], 'Staff', false, true);
    //         }
    //     });
        
    // ACTIONS
    $table->addActionColumn()
        ->addParam('pupilsightYearGroupID')
        ->format(function ($facilities, $actions) use ($guid) {
            $actions->addAction('edit', __('Edit'))
                    ->setURL('/modules/School Admin/yearGroup_manage_edit.php');

            $actions->addAction('delete', __('Delete'))
                    ->setURL('/modules/School Admin/yearGroup_manage_delete.php');
        });

    echo $table->render($yearGroups);

    //echo formatName('', $row['preferredName'], $row['surname'], 'Staff', false, true);
}
