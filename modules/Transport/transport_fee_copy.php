<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Forms\Form;
use Pupilsight\Forms\DatabaseFormFactory;

if (isActionAccessible($guid, $connection2, '/modules/Transport/transport_fee_copy.php') == false) {
    //Acess denied
    echo "<div class='error'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    $id = $_GET['id'];
    //Proceed!
    $page->breadcrumbs
        ->add(__('Transport Fee Copy'), 'transport_fee.php')
        ->add(__('Add Transport Fee Copy'));

    $editLink = '';
    if (isset($_GET['editID'])) {
        $editLink = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/Finance/fee_item_type_manage_edit.php&id='.$_GET['editID'];
    }
    if (isset($_GET['return'])) {
        returnProcess($guid, $_GET['return'], $editLink, null);
    }
    echo '<h2>';
    echo __('Copy Transport Fee');
    echo '</h2>';

    $pupilsightSchoolYearID = $_SESSION[$guid]['pupilsightSchoolYearID'];
    $sqla = 'SELECT pupilsightSchoolYearID, name FROM pupilsightSchoolYear ';
    $resulta = $connection2->query($sqla);
    $academic = $resulta->fetchAll();

    $academicData = array();
    foreach ($academic as $dt) {
        $academicData[$dt['pupilsightSchoolYearID']] = $dt['name'];
    }

    $sqlrt = 'SELECT id, route_name FROM trans_routes';

    $resultrt = $connection2->query($sqlrt);
    $routesData = $resultrt->fetchAll();
    $routes = array();
    $routes1 = array(''=>'Select Route');
    $routes2 = array();

    foreach ($routesData as $rt) {
        $routes2[$rt['id']] = $rt['route_name'];
    }
    $routes = $routes1+$routes2;

    $form = Form::create('program', $_SESSION[$guid]['absoluteURL'].'/modules/'.$_SESSION[$guid]['module'].'/transport_fee_copyProcess.php');
    $form->setFactory(DatabaseFormFactory::create($pdo));

    $form->addHiddenValue('address', $_SESSION[$guid]['address']);

    $form->addHiddenValue('id', $id);
    $row = $form->addRow();
        $row->addLabel('schedule_name', __('Schedule Name'))->description(__('Must be unique.'));
        $row->addTextField('schedule_name')->required();

    $row = $form->addRow();
        $row->addLabel('name', __('Academic Year'));
        $row->addSelect('pupilsightSchoolYearID')->fromArray($academicData)->selected($pupilsightSchoolYearID)->required();
       
    $row = $form->addRow();
        $row->addLabel('route_id', __('Route'));
        $row->addSelect('route_id')->fromArray($routes)->required(); 
     
    $row = $form->addRow();
        $row->addFooter();
        $row->addSubmit();

    echo $form->getOutput();

}
