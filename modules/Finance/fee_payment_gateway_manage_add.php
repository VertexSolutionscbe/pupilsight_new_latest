<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Forms\Form;
use Pupilsight\Forms\DatabaseFormFactory;

if (isActionAccessible($guid, $connection2, '/modules/Finance/fee_payment_gateway_manage_add.php') == false) {
    //Acess denied
    echo "<div class='error'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    //Proceed!
    $page->breadcrumbs
        ->add(__('Manage Fee Payment Gateway'), 'fee_payment_gateway_manage.php')
        ->add(__('Add Fee Payment Gateway'));

    $editLink = '';
    if (isset($_GET['editID'])) {
        $editLink = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/Finance/fee_payment_gateway_manage_edit.php&id='.$_GET['editID'];
    }
    if (isset($_GET['return'])) {
        returnProcess($guid, $_GET['return'], $editLink, null);
    }
    echo '<h2>';
    echo __('Add Fee Payment Gateway');
    echo '</h2>';

    $gateway = array('' => 'Select Gateway', 'RAZORPAY' => 'RAZORPAY', 'WORLDLINE' => 'WORLDLINE', 'PAYU' => 'PAYU', 'AIRPAY' => 'AIRPAY');

    $form = Form::create('program', $_SESSION[$guid]['absoluteURL'].'/modules/'.$_SESSION[$guid]['module'].'/fee_payment_gateway_manage_addProcess.php');
    $form->setFactory(DatabaseFormFactory::create($pdo));

    $form->addHiddenValue('address', $_SESSION[$guid]['address']);

    $row = $form->addRow();
        $row->addLabel('gateway_name', __('Name'))->description(__('Must be unique.'));
        $row->addTextField('gateway_name')->required();

    $row = $form->addRow();
        $row->addLabel('name', __('Gateway Name'))->description(__('Must be unique.'));
        $row->addSelect('name')->fromArray($gateway)->required();

    $row = $form->addRow();
        $row->addLabel('username', __('UserName'));
        $row->addTextField('username')->setValue($values['username']);

    $row = $form->addRow();
        $row->addLabel('password', __('Password'));
        $row->addTextField('password')->setValue($values['password']);

    $row = $form->addRow();
        $row->addLabel('mid', __('Merchant ID'))->description(__('Must be unique.'));
        $row->addTextField('mid');

    $row = $form->addRow();
        $row->addLabel('key_id', __('Key ID'))->description(__('Must be unique.'));
        $row->addTextField('key_id');

    $row = $form->addRow();
        $row->addLabel('key_secret', __('key Secret'))->description(__('Must be unique.'));
        $row->addTextField('key_secret'); 

    $row = $form->addRow();
        $row->addLabel('terms_and_conditions', __('Terms & Conditions'));
        $row->addTextArea('terms_and_conditions');

    // $row = $form->addRow();
    //     $row->addLabel('sequenceNumber', __('Sequence Number'))->description(__('Must be unique. Controls chronological ordering.'));
    //     $row->addSequenceNumber('sequenceNumber', 'pupilsightProgram')->required()->maxLength(3);

        
    $row = $form->addRow();
        $row->addFooter();
        $row->addSubmit();

    echo $form->getOutput();

}
