<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Forms\Form;
use Pupilsight\Forms\DatabaseFormFactory;
use Pupilsight\Services\Format;

if (isActionAccessible($guid, $connection2, '/modules/Transport/bus_manage_add.php') == false) {
    //Acess denied
    echo "<div class='error'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    //Proceed!
    $page->breadcrumbs
        ->add(__('Bus Details'), 'bus_manage.php')
        ->add(__('Bus Bulk Details'));

 
    echo '<h2>';
    echo __('Bus Bulk Details');
    echo '</h2>';


    
    echo "<div style='height:50px;'><div class='float-left mb-2'>";
    //echo "<a href='index.php?q=/modules/Transport/bus_bulk_template.php' class='btn btn-primary'>Download Template</a>&nbsp;&nbsp;";  
    echo "<a href='#' class='btn btn-primary'>Download Template</a>&nbsp;&nbsp;";  
    echo "</div></div>";
    
    //echo "<div style='height:50px;'><div class='float-right mb-2'>";
    //echo "&nbsp;<a href='index.php?q=/modules/Transport/bus_manage_add_upload.php' class='btn btn-primary'><i class='mdi mdi-cloud-upload-outline mdi-24px mdi-24px'> Import </i></a></div><div class='float-none'></div></div>";
    
    $pupilsightSchoolYearID = $_SESSION[$guid]['pupilsightSchoolYearID'];
    $sqla = 'SELECT pupilsightSchoolYearID, name FROM pupilsightSchoolYear ';
    $resulta = $connection2->query($sqla);
    $academic = $resulta->fetchAll();

    $form = Form::create('importbusdetails', $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.$_SESSION[$guid]['module'].'/bus_manage_addimportProcess.php');
    $form->setFactory(DatabaseFormFactory::create($pdo));

    $form->addHiddenValue('address', $_SESSION[$guid]['address']);


    $row = $form->addRow();
    $col = $row->addColumn()->setClass('newdes');
    $col->addLabel('file', __('Bus Bulk Details(CSV File)'));
    $col->addFileUpload('file')->accepts('.csv')->setMaxUpload(false);

    $row = $form->addRow();
    $row->addFooter();
    $row->addSubmit();
    echo $form->getOutput();
}
