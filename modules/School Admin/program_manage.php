<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Tables\DataTable;
use Pupilsight\Services\Format;
use Pupilsight\Domain\School\ProgramGateway;

if (isActionAccessible($guid, $connection2, '/modules/School Admin/program_manage.php') == false) {
    //Acess denied
    echo "<div class='alert alert-danger'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    //Proceed!
    $page->breadcrumbs->add(__('Manage Program'));

    if (isset($_GET['return'])) {
        returnProcess($guid, $_GET['return'], null, null);
    }

    $ProgramGateway = $container->get(ProgramGateway::class);

    // QUERY
    $criteria = $ProgramGateway->newQueryCriteria()
        ->sortBy(['sequenceNumber'])
        ->fromPOST();

    $yearGroups = $ProgramGateway->queryYearGroups($criteria);

    // DATA TABLE
    $table = DataTable::createPaginated('programManage', $criteria);

    // $table->addHeaderAction('add', __('Add'))
    //     ->setURL('/modules/School Admin/program_manage_add.php')
    //     ->displayLabel();
    
    echo "<div style='height:50px;'><div class='float-right mb-2'><a href='fullscreen.php?q=/modules/School Admin/program_manage_add.php' class='thickbox btn btn-primary'>Add</a></div><div class='float-none'></div></div>";  

    
    
    //$table->addColumn('sequenceNumber', __('sequenceNumber'));
    $table->addColumn('name', __('Name'));
    //$table->addColumn('nameShort', __('Short Name'));
   
        
    // ACTIONS
    $table->addActionColumn()
        ->addParam('pupilsightProgramID')
        ->format(function ($facilities, $actions) use ($guid) {
            $actions->addAction('edit', __('Edit'))
                    ->setURL('/modules/School Admin/program_manage_edit.php');

            $actions->addAction('delete', __('Delete'))
                    ->setURL('/modules/School Admin/program_manage_delete.php');
        });

    echo $table->render($yearGroups);

    //echo formatName('', $row['preferredName'], $row['surname'], 'Staff', false, true);
}
