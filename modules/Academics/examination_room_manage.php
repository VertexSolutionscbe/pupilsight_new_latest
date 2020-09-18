<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Tables\DataTable;
use Pupilsight\Services\Format;
use Pupilsight\Domain\Curriculum\CurriculamGateway;

if (isActionAccessible($guid, $connection2, '/modules/Academics/examination_room_manage.php') == false) {
    //Acess denied
    echo "<div class='error'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    //Proceed!
    $page->breadcrumbs->add(__('Manage Test Room'));

    if (isset($_GET['return'])) {
        returnProcess($guid, $_GET['return'], null, null);
    }

    $CurriculumGateway = $container->get(CurriculamGateway::class);

    // QUERY
    $criteria = $CurriculumGateway->newQueryCriteria()
        ->sortBy(['id'])
        ->fromPOST();

    $rooms = $CurriculumGateway->getTestRoom($criteria);

    // DATA TABLE
    $table = DataTable::createPaginated('schoolYearManage', $criteria);

    echo "<div style='height:50px;'><div class='float-right mb-2'><a href='fullscreen.php?q=/modules/Academics/examination_room_manage_add.php' class='thickbox btn btn-primary'>Add</a><div class='float-none'></div></div></div>";  
    
    // $table->addHeaderAction('add', __('Add'))
    //     ->setURL('/modules/Academics/schoolYear_manage_add.php')
    //     ->displayLabel();
    $table->addColumn('serial_number', __('SI No'));
    $table->addColumn('name', __('Name'));
    $table->addColumn('code', __('Code'));
   
    //$table->addColumn('description', __('Description'))->translatable();
        
    // ACTIONS
    $table->addActionColumn()
        ->addParam('id')
        ->format(function ($rooms, $actions) {
            $actions->addAction('editnew', __('Edit'))
                    ->setURL('/modules/Academics/examination_room_manage_edit.php');
                    
                    

            // if ($schoolYear['status'] != 'Current') {
                $actions->addAction('delete', __('Delete'))
                       ->setURL('/modules/Academics/examination_room_manage_delete.php');
            // }
        });

    echo $table->render($rooms);
}
