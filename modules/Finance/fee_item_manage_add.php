<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Forms\Form;
use Pupilsight\Forms\DatabaseFormFactory;

if (isActionAccessible($guid, $connection2, '/modules/Finance/fee_item_manage_add.php') == false) {
    //Acess denied
    echo "<div class='error'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    //Proceed!
    $page->breadcrumbs
        ->add(__('Manage Fee Item'), 'fee_item_manage.php')
        ->add(__('Add Fee Item'));

    $editLink = '';
    if (isset($_GET['editID'])) {
        $editLink = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/Finance/fee_item_manage_edit.php&id='.$_GET['editID'];
    }
    if (isset($_GET['return'])) {
        returnProcess($guid, $_GET['return'], $editLink, null);
    }
    echo '<h2>';
    echo __('Add Fee Item');
    echo '</h2>';

    $pupilsightSchoolYearID = $_SESSION[$guid]['pupilsightSchoolYearID'];
    $sqla = 'SELECT pupilsightSchoolYearID, name FROM pupilsightSchoolYear ';
    $resulta = $connection2->query($sqla);
    $academic = $resulta->fetchAll();

    $academicData = array();
    foreach ($academic as $dt) {
        $academicData[$dt['pupilsightSchoolYearID']] = $dt['name'];
    }
    

    $sqlp = 'SELECT id, name FROM fn_fee_item_type ';
    $resultp = $connection2->query($sqlp);
    $feeType = $resultp->fetchAll();

    $feeItemType = array();
    foreach ($feeType as $dt) {
        $feeItemType[$dt['id']] = $dt['name'];
    }
     

    $form = Form::create('program', $_SESSION[$guid]['absoluteURL'].'/modules/'.$_SESSION[$guid]['module'].'/fee_item_manage_addProcess.php');
    $form->setFactory(DatabaseFormFactory::create($pdo));

    $form->addHiddenValue('address', $_SESSION[$guid]['address']);

    $row = $form->addRow();
        $row->addLabel('name', __('Fee Item Name'))->description(__('Must be unique.'));
        $row->addTextField('name')->required();

    $row = $form->addRow();
        $row->addLabel('code', __('Fee Item Code'))->description(__('Must be unique.'));
        $row->addTextField('code')->required();

    $row = $form->addRow();
        $row->addLabel('name', __('Academic Year'));
        $row->addSelect('pupilsightSchoolYearID')->fromArray($academicData)->selected($pupilsightSchoolYearID)->required();

    $row = $form->addRow();
        $row->addLabel('code', __('Item Type'));
        $row->addSelect('fn_fee_item_type_id')->fromArray($feeItemType)->required();

        
    $row = $form->addRow();
        $row->addFooter();
        $row->addSubmit();

    echo $form->getOutput();

}
