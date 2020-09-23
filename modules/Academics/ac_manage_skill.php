<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Tables\DataTable;
use Pupilsight\Services\Format;
use Pupilsight\Domain\School\SchoolYearGateway;

if (isActionAccessible($guid, $connection2, '/modules/Academics/ac_manage_skill.php') == false) {
    //Acess denied
    echo "<div class='error'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    //Proceed!
    $page->breadcrumbs->add(__('Manage Skills'));

    if (isset($_GET['return'])) {
        returnProcess($guid, $_GET['return'], null, null);
    }

    $schoolYearGateway = $container->get(SchoolYearGateway::class);

    // QUERY
    $criteria = $schoolYearGateway->newQueryCriteria()
        ->sortBy(['id'])
        ->fromPOST();

    $skills = $schoolYearGateway->getSkill($criteria);

    // DATA TABLE
    $table = DataTable::createPaginated('schoolYearManage', $criteria);

    echo "<div style='height:50px;'><div class='float-right mb-2'><a href='fullscreen.php?q=/modules/Academics/ac_manage_skill_add.php' class='thickbox btn btn-primary'>Add</a><div class='float-none'></div></div></div>";  
    
    // $table->addHeaderAction('add', __('Add'))
    //     ->setURL('/modules/Academics/schoolYear_manage_add.php')
    //     ->displayLabel();
    $table->addColumn('serial_number', __('SI No'));
    $table->addColumn('name', __('Name'));
    $table->addColumn('code', __('Code'));
   
    $table->addColumn('description', __('Description'))->translatable();
        
    // ACTIONS
    $table->addActionColumn()
        ->addParam('id')
        ->format(function ($skills, $actions) {
            $actions->addAction('editnew', __('Edit'))
                    ->setURL('/modules/Academics/ac_manage_skill_edit.php');
                    
                    

            // if ($schoolYear['status'] != 'Current') {
                $actions->addAction('delete', __('Delete'))
                       ->setURL('/modules/Academics/ac_manage_skill_delete.php');
            // }
        });

    echo $table->render($skills);
}
