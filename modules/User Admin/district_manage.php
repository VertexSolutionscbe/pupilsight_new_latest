<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Tables\DataTable;
use Pupilsight\Services\Format;
use Pupilsight\Domain\User\DistrictGateway;

if (isActionAccessible($guid, $connection2, '/modules/User Admin/district_manage.php') == false) {
    //Acess denied
    echo "<div class='alert alert-danger'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    //Proceed!
    $page->breadcrumbs->add(__('Manage Districts'));

    if (isset($_GET['return'])) {
        returnProcess($guid, $_GET['return'], null, null);
    }

    $districtGateway = $container->get(DistrictGateway::class);

    // QUERY
    $criteria = $districtGateway->newQueryCriteria()
        ->sortBy('name')
        ->fromPOST();

    $districts = $districtGateway->queryDistricts($criteria);

    // DATA TABLE
    $table = DataTable::createPaginated('districtManage', $criteria);

    $table->addHeaderAction('add', __('Add'))
        ->setURL('/modules/User Admin/district_manage_add.php')
        ->displayLabel();

    $table->addColumn('name', __('Name'));

    $table->addActionColumn()
        ->addParam('pupilsightDistrictID')
        ->format(function ($row, $actions) {
            $actions->addAction('edit', __('Edit'))
                ->setURL('/modules/User Admin/district_manage_edit.php');

            $actions->addAction('delete', __('Delete'))
                ->setURL('/modules/User Admin/district_manage_delete.php');
        });

    echo $table->render($districts);
}
