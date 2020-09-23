<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Tables\DataTable;
use Pupilsight\Domain\User\RoleGateway;

if (isActionAccessible($guid, $connection2, '/modules/User Admin/role_manage.php') == false) {
    //Acess denied
    echo "<div class='alert alert-danger'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    //Proceed!
    $page->breadcrumbs->add(__('Manage Roles'));  

    if (isset($_GET['return'])) {
        returnProcess($guid, $_GET['return'], null, null);
    }

    $roleGateway = $container->get(RoleGateway::class);
    
    // QUERY
    $criteria = $roleGateway->newQueryCriteria()
        ->sortBy(['type', 'name'])
        ->fromPOST();

    $roles = $roleGateway->queryRoles($criteria);

    // DATA TABLE
    $table = DataTable::createPaginated('roleManage', $criteria);

    $table->addHeaderAction('add', __('Add'))
        ->setURL('/modules/User Admin/role_manage_add.php')
        ->displayLabel();

    $table->addColumn('category', __('Category'))->translatable();
    $table->addColumn('name', __('Name'))->translatable();
    $table->addColumn('nameShort', __('Short Name'));
    $table->addColumn('description', __('Description'))->translatable();
    $table->addColumn('type', __('Type'))->translatable();
    $table->addColumn('loginYear', __('Login Years'))
        ->notSortable()
        ->format(function ($row) {
            if ($row['canLoginRole'] == 'N') {
                return __('None');
            } else if ($row['futureYearsLogin'] == 'Y' and $row['pastYearsLogin'] == 'Y') {
                return __('All years');
            } elseif ($row['futureYearsLogin'] == 'N' and $row['pastYearsLogin'] == 'N') {
                return __('Current year only');
            } elseif ($row['futureYearsLogin'] == 'N') {
                return __('Current/past years only');
            } elseif ($row['pastYearsLogin'] == 'N') {
                return __('Current/future years only');
            }
        });

    $table->addActionColumn()
        ->addParam('pupilsightRoleID')
        ->format(function ($row, $actions) {
            $actions->addAction('edit', __('Edit'))
                ->setURL('/modules/User Admin/role_manage_edit.php');

            if ($row['type'] == 'Additional') {
                $actions->addAction('delete', __('Delete'))
                    ->setURL('/modules/User Admin/role_manage_delete.php');
            }

            $actions->addAction('duplciate', __('Duplicate'))
                ->setIcon('copy')
                ->setURL('/modules/User Admin/role_manage_duplicate.php');
        });

    echo $table->render($roles);
}
