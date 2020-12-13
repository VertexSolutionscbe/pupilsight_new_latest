<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Forms\Form;
use Pupilsight\Forms\DatabaseFormFactory;

if (isActionAccessible($guid, $connection2, '/modules/Finance/fee_counter_check_add.php') == false) {
    //Acess denied
    echo "<div class='alert alert-danger'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    //Proceed!
    

    $page->breadcrumbs->add(__('Assign Counter'));

    $editLink = '';
    if (isset($_GET['editID'])) {
        $editLink = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/Finance/fee_item_type_manage_edit.php&id='.$_GET['editID'];
    }
    if (isset($_GET['return'])) {
        returnProcess($guid, $_GET['return'], $editLink, null);
    }

    $sqlp = 'SELECT id, name FROM fn_fees_counter WHERE status != "1" ';
    $resultp = $connection2->query($sqlp);
    $counterdata = $resultp->fetchAll();

    $counter = array();
    $counter1 = array('' => 'Select Counter');
    $counter2 = array();
    foreach ($counterdata as $dt) {
        $counter2[$dt['id']] = $dt['name'];
    }
    $counter = $counter1 + $counter2;

    echo '<h2>';
    echo __('Assign Counter');
    echo '</h2>';

    $form = Form::create('program', $_SESSION[$guid]['absoluteURL'].'/modules/'.$_SESSION[$guid]['module'].'/fee_counter_check_addProcess.php');
    $form->setFactory(DatabaseFormFactory::create($pdo));

    $form->addHiddenValue('address', $_SESSION[$guid]['address']);

    $row = $form->addRow();
        $row->addLabel('fn_fees_counter_id', __('Counter'))->description(__('Must be unique.'));
        $row->addSelect('fn_fees_counter_id')->fromArray($counter)->required();

    // $row = $form->addRow();
    //     $row->addLabel('nameShort', __('Short Name'))->description(__('Must be unique.'));
    //     $row->addTextField('nameShort')->required()->maxLength(4);

    // $row = $form->addRow();
    //     $row->addLabel('sequenceNumber', __('Sequence Number'))->description(__('Must be unique. Controls chronological ordering.'));
    //     $row->addSequenceNumber('sequenceNumber', 'pupilsightProgram')->required()->maxLength(3);

        
    $row = $form->addRow();
        $row->addFooter();
        $row->addSubmit();

    echo $form->getOutput();

}
