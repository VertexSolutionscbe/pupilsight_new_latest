<?php
/*
Pupilsight, Flexible & Open School System
*/
use Pupilsight\Forms\Form;
use Pupilsight\Forms\DatabaseFormFactory;



if (isActionAccessible($guid, $connection2, '/modules/Staff/feedback_category_manage_add.php') == false) {
    //Acess denied
    echo "<div class='error'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    //Proceed!

    $page->breadcrumbs->add(__('Manage Feedback Category'));

    if (isset($_GET['return'])) {
        returnProcess($guid, $_GET['return'], null, null);
    }


    echo '<h3>';
    echo __('Add Feedback Category');
    echo '</h3>';

    $form = Form::create('specialDayAdd', $_SESSION[$guid]['absoluteURL'].'/modules/'.$_SESSION[$guid]['module'].'/feedback_category_manage_addProcess.php');
    $form->setFactory(DatabaseFormFactory::create($pdo));

    $form->addHiddenValue('address', $_SESSION[$guid]['address']); 
 
    $row = $form->addRow();        
        $col = $row->addColumn()->setClass('newdes');
        $col->addLabel('name', __('Name'));
        $col->addTextField('name')->addClass('txtfield')->required();

       
    $row = $form->addRow();
        
        $col->addLabel('', __(''))->addClass('dte');
        $row = $form->addRow()->setID('lastseatdiv');
        $row->addFooter();
        $row->addSubmit();

        echo $form->getOutput();
  
}
