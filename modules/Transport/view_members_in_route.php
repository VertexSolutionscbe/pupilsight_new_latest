<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Forms\Form;
use Pupilsight\Tables\DataTable;
use Pupilsight\Services\Format;
use Pupilsight\Domain\Transport\TransportGateway;
use Pupilsight\Forms\DatabaseFormFactory;

if (isActionAccessible($guid, $connection2, '/modules/Transport/view_members_in_route.php') == false) {
    //Acess denied
    echo "<div class='error'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    //Proceed!
    $pupilsightSchoolYearID = $_SESSION[$guid]['pupilsightSchoolYearID'];
    $page->breadcrumbs->add(__('Assign route'));
    if (isset($_GET['return'])) {
        returnProcess($guid, $_GET['return'], null, null);
    }
    echo "<div style='height:50px;'><div class='float-left mb-2'><a  id=''  data-toggle='modal' data-target='#large-modal-new' data-noti='2'  class='sendbtn btn btn-primary'>Send SMS</a>&nbsp;&nbsp;";
    echo "<a  id='' data-toggle='modal' data-noti='1' data-target='#large-modal-new' class='sendbtn btn btn-primary'>Send Email</a></div><div class='float-none'></div></div>";

    $sql = 'SELECT id,route_name FROM trans_routes ';
    $result = $connection2->query($sql);
    // $routename = $resultr->fetchAll();
    $route_name = $result->fetchAll();
    $routes = array();
    foreach ($route_name as $prg) {
        $id = 'type:' . $prg['id'];
        $routes[$id] = $prg['route_name'];
    }
    $TransportGateway = $container->get(TransportGateway::class);
    $criteria = $TransportGateway->newQueryCriteria()
        //->sortBy(['id'])
        ->fromPOST();
    $viewMember = $TransportGateway->getViewMember($criteria, $pupilsightSchoolYearID);
    

    $table = DataTable::createPaginated('FeeStructureManage', $criteria);

    $table->addMetaData('filterOptions', $routes);
    $table->addCheckboxColumn('stuid', __(''))
        ->setClass('chkbox')
        ->notSortable();
    $table->addColumn('route_name', __('Route Name'));

    $table->addColumn('bus_name', __('Bus name'));
    $table->addColumn('student_name', __('Name'));
    $table->addColumn('category', __('Category'));
    $table->addColumn('class', __('Class'));
    $table->addColumn('section', __('Section'));
    //$table->addColumn('onward_route', __('onward Route'));
    $table->addColumn('onward_stop_name', __('Onward stop'));
    //$table->addColumn('return_name', __('return Route'));
    $table->addColumn('return_stop_name', __('Return Stop'));
    $table->addColumn('route_type', __('Type'));
    $table->addColumn('academic_year', __('Academic Year'));


    echo $table->render($viewMember);
}