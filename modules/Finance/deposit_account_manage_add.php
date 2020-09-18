<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Forms\Form;
use Pupilsight\Forms\DatabaseFormFactory;

if (isActionAccessible($guid, $connection2, '/modules/Finance/deposit_account_manage_add.php') == false) {
    //Acess denied
    echo "<div class='error'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    //Proceed!
    $page->breadcrumbs
        ->add(__('Manage Deposit Account'), 'deposit_account_manage.php')
        ->add(__('Add Deposit Account'));

    $editLink = '';
    if (isset($_GET['editID'])) {
        $editLink = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/Finance/deposit_account_manage_edit.php&id='.$_GET['editID'];
    }
    if (isset($_GET['return'])) {
        returnProcess($guid, $_GET['return'], $editLink, null);
    }
    echo '<h2>';
    echo __('Add Deposit Account');
    echo '</h2>';

    $sqli = 'SELECT id, name FROM fn_fee_items ';
    $resulti = $connection2->query($sqli);
    $feeItem = $resulti->fetchAll();

    $feeItemData = array();
    $feeItemData1 = array(''=>'Select Fee Item');
    $feeItemData2 = array();
    foreach ($feeItem as $dt) {
        $feeItemData2[$dt['id']] = $dt['name'];
    }
    $feeItemData = $feeItemData1 + $feeItemData2;

    $form = Form::create('program', $_SESSION[$guid]['absoluteURL'].'/modules/'.$_SESSION[$guid]['module'].'/deposit_account_manage_addProcess.php');
    $form->setFactory(DatabaseFormFactory::create($pdo));
    
    $form->addHiddenValue('address', $_SESSION[$guid]['address']);

    $row = $form->addRow();
        $row->addLabel('fn_fee_item_id', __('Fee Item'))->description(__('Must be unique.'));
        $row->addSelect('fn_fee_item_id')->fromArray($feeItemData)->addClass('txtfield allFeeItemId')->required();
        
    $row = $form->addRow();
        $row->addLabel('ac_name', __('Account Name'))->description(__('Must be unique.'));
        $row->addTextField('ac_name')->required();

    $row = $form->addRow();
        $row->addLabel('ac_code', __('Account Code'))->description(__('Must be unique.'));
        $row->addTextField('ac_code')->required();

    $row = $form->addRow();
        $row->addLabel('overpayment_account', __('Over Payment Account'));
        $row->addCheckbox('overpayment_account')->setValue('1');    

    
        
    $row = $form->addRow();
        $row->addFooter();
        $row->addSubmit();

    echo $form->getOutput();

}
