<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Forms\Form;
use Pupilsight\Forms\DatabaseFormFactory;

if (isActionAccessible($guid, $connection2, '/modules/Finance/transport_route_copy.php') != false) {
    //Acess denied
    echo "<div class='error'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    $id = $_GET['id'];
    //Proceed!
    $page->breadcrumbs
        ->add(__('Transport route copy'), 'routes.php')
        ->add(__('Add Transport Route Copy'));

    $editLink = '';
    if (isset($_GET['editID'])) {
        $editLink = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/Transport/transport_route_edit.php&id='.$_GET['editID'];
    }
    if (isset($_GET['return'])) {
        returnProcess($guid, $_GET['return'], $editLink, null);
    }
    echo '<h2>';
    echo __('Copy Route ');
    echo '</h2>';

    $pupilsightSchoolYearID = $_SESSION[$guid]['pupilsightSchoolYearID'];
    $sqla = 'SELECT pupilsightSchoolYearID, name FROM pupilsightSchoolYear ';
    $resulta = $connection2->query($sqla);
    $academic = $resulta->fetchAll();

    $academicData = array();
    foreach ($academic as $dt) {
        $academicData[$dt['pupilsightSchoolYearID']] = $dt['name'];
    }

    $form = Form::create('program', $_SESSION[$guid]['absoluteURL'].'/modules/'.$_SESSION[$guid]['module'].'/transport_route_copyProcess.php');
    $form->setFactory(DatabaseFormFactory::create($pdo));

    $form->addHiddenValue('address', $_SESSION[$guid]['address']);

    $form->addHiddenValue('id', $id);
    $row = $form->addRow();
        $row->addLabel('route_name', __('Route Name'))->description(__('Must be unique.'));
        $row->addTextField('route_name')->required();

    $row = $form->addRow();
        $row->addLabel('name', __('Academic Year'));
        $row->addSelect('pupilsightSchoolYearID')->fromArray($academicData)->selected($pupilsightSchoolYearID)->required();
       
   
     
    $row = $form->addRow();
        $row->addFooter();
        $row->addSubmit();

    echo $form->getOutput();

}
