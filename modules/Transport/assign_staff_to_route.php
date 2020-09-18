<?php
/*
Pupilsight, Flexible & Open School System
*/
use Pupilsight\Forms\Form;
use Pupilsight\Tables\DataTable;
use Pupilsight\Services\Format;
use Pupilsight\Domain\Transport\TransportGateway;
use Pupilsight\Forms\DatabaseFormFactory;

if (isActionAccessible($guid, $connection2, '/modules/Transport/assign_staff_to_route.php') != false) {
    //Acess denied
    echo "<div class='error'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    //Proceed!
    $page->breadcrumbs->add(__('Assign route'));
    if (isset($_GET['return'])) {
        returnProcess($guid, $_GET['return'], null, null);
    }

   
    // echo '<pre>';
    // print_r($_POST);
    // echo '</pre>';
    //die();

    echo "<a style='display:none' id='clickStaffroute' href='fullscreen.php?q=/modules/Transport/assign_route_staff_add.php'  class='thickbox '>Assign Route</a>"; 
    echo "<a style='display:none' id='clk_unassign' href='fullscreen.php?q=/modules/Transport/unassign_route_student.php'  class='thickbox '>unAssign Route</a>";   
    echo "<a style='display:none' id='clk_changeroute' href='fullscreen.php?q=/modules/Transport/change_staff_transportRoute_edit.php'  class='thickbox '>change Route</a>";   
    echo "<div style='height:50px;'><div class='float-left mb-2'><a  id='assignStaffroute'  class='btn btn-primary'>Assign Route</a>&nbsp;&nbsp;";  
    echo "<a  id='unassignStudentroute' class='btn btn-primary'>new UnAssign Route</a>&nbsp;&nbsp;";
    
    echo "<a  id='changeStudentroute' class='btn btn-primary'>Change Route</a>&nbsp;&nbsp;</div><div class='float-none'></div></div>";  
  
    $TransportGateway = $container->get(TransportGateway::class);
    $criteria = $TransportGateway->newQueryCriteria()
    ->sortBy(['pupilsightPersonID'])
    ->sortBy(['surname'])
    
        ->fromPOST();

         $staff = $TransportGateway->getStaff($criteria);
//print_r($staff);die();
        $table = DataTable::createPaginated('FeeStructureManage', $criteria);

        // $table->addColumn('serial_number', __('SL No'));
        $table->addCheckboxColumn('stuid',__(''))
        ->setClass('chkbox')
        ->notSortable();
        $table->addColumn('surname', __('Name'));
        // $table->addColumn('pupilsightPersonID', __('ID'));
        // $table->addColumn('class', __('Class'));
        // $table->addColumn('section', __('Section'));
        $table->addColumn('route_name', __('onward route'));
        $table->addColumn('stop_name', __('onward stop'));
        $table->addColumn('return_name', __('return route'));
        $table->addColumn('return_stop', __('return stop'));
        $table->addColumn('academic_year', __('Academic Year'));


        echo $table->render($staff);

        }
