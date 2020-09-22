<?php
/*
Pupilsight, Flexible & Open School System
*/
use Pupilsight\Forms\Form;
use Pupilsight\Forms\DatabaseFormFactory;



if (isActionAccessible($guid, $connection2, '/modules/Academics/ac_manage_skill_add.php') == false) {
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


    echo '<h3>';
    echo __('Add Skill');
    echo '</h3>';

    $form = Form::create('specialDayAdd', $_SESSION[$guid]['absoluteURL'].'/modules/'.$_SESSION[$guid]['module'].'/ac_manage_skill_addProcess.php');
    $form->setFactory(DatabaseFormFactory::create($pdo));

    $form->addHiddenValue('address', $_SESSION[$guid]['address']); 
 
    $row = $form->addRow();        
        $col = $row->addColumn()->setClass('newdes');
        $col->addLabel('name', __('Name'));
        $col->addTextField('name')->addClass('txtfield')->required();

        $col = $row->addColumn()->setClass('newdes');
        $col->addLabel('code', __('Skill Code'));
        $col->addTextField('code')->addClass('txtfield')->required();
        
    $row = $form->addRow();
        
        $col = $row->addColumn()->setClass('newdes');
        $col->addLabel('description', __('Description'));
        $col->addTextArea('description')->addClass('txtfield')->setRows(4); 
        
        // $row->addButton('Add Seats')->addData('class', 'addSeats')->addClass('submt');
        $col->addLabel('', __(''))->addClass('dte');
        $row = $form->addRow()->setID('lastseatdiv');
        $row->addFooter();
        $row->addSubmit();

        echo $form->getOutput();
  
}
