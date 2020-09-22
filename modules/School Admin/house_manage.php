<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Tables\DataTable;
use Pupilsight\Services\Format;
use Pupilsight\Domain\School\HouseGateway;

if (isActionAccessible($guid, $connection2, '/modules/School Admin/house_manage.php') == false) {
    //Acess denied
    echo "<div class='alert alert-danger'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    //Proceed!
    $page->breadcrumbs->add(__('Manage Houses'));

    if (isset($_GET['return'])) {
        returnProcess($guid, $_GET['return'], null, null);
    }

    $houseGateway = $container->get(HouseGateway::class);

    // QUERY
    $criteria = $houseGateway->newQueryCriteria()
        ->sortBy(['name'])
        ->fromPOST();

    $houses = $houseGateway->queryHouses($criteria);

    // DATA TABLE
    $table = DataTable::createPaginated('houseManage', $criteria);

    // $table->addHeaderAction('assign', __('Assign Houses'))
    //     ->setIcon('attendance')
    //     ->setURL('/modules/School Admin/house_manage_assign.php')
    //     ->displayLabel(__('Assign Houses'))
    //     ->append('&nbsp|&nbsp');

    // $table->addHeaderAction('add', __('Add'))
    //     ->setURL('/modules/School Admin/house_manage_add.php')
    //     ->displayLabel();

    echo "<div style='height:50px;'><div class='float-right mb-2'><a href='".$_SESSION[$guid]['absoluteURL'].'/fullscreen.php?q=/modules/'.$_SESSION[$guid]['module']."/house_manage_assign.php' class='thickbox btn btn-primary'>Assign Houses</a>";  

    echo "&nbsp;&nbsp;<a href='".$_SESSION[$guid]['absoluteURL'].'/fullscreen.php?q=/modules/'.$_SESSION[$guid]['module']."/house_manage_add.php' class='thickbox btn btn-primary'>Add</a></div><div class='float-none'></div></div>"; 


    $table->addColumn('logo', __('Logo'))
    ->notSortable()
    ->format(function($values) use ($guid) { 
        $return = null;
        $return .= ($values['logo'] != '') ? "<img class='user' style='max-width: 75px' src='".$_SESSION[$guid]['absoluteURL'].'/'.$values['logo']."'/>":"<img class='user' style='max-width: 75px' src='".$_SESSION[$guid]['absoluteURL'].'/themes/'.$_SESSION[$guid]['pupilsightThemeName']."/img/anonymous_240_square.jpg'/>";
        return $return;
    });
    $table->addColumn('name', __('Name'));
    $table->addColumn('nameShort', __('Short Name'));

    // ACTIONS
    $table->addActionColumn()
        ->addParam('pupilsightHouseID')
        ->format(function ($facilities, $actions) use ($guid) {
            $actions->addAction('edit', __('Edit'))
                    ->setURL('/modules/School Admin/house_manage_edit.php');

            $actions->addAction('delete', __('Delete'))
                    ->setURL('/modules/School Admin/house_manage_delete.php');
        });

    echo $table->render($houses);
}
