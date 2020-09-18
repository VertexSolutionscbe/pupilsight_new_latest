<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Forms\Form;
use Pupilsight\Forms\DatabaseFormFactory;
$session = $container->get('session');
$transids = $session->get('transaction_ids');

if (isActionAccessible($guid, $connection2, '/modules/Finance/fee_cancel_receipts.php') != false) {
    //Acess denied
    echo "<div class='error'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    //Proceed!
    $page->breadcrumbs
        ->add(__('Manage Fee Cancel Transaction'), 'fee_transaction_manage.php')
        ->add(__('Add Fee Cancel Transaction'));

    $editLink = '';
    if (isset($_GET['editID'])) {
        $editLink = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/Finance/fee_item_manage_edit.php&id='.$_GET['editID'];
    }
    if (isset($_GET['return'])) {
        returnProcess($guid, $_GET['return'], $editLink, null);
    }
    echo '<h2>';
    echo __('Cancel Fee Receipt');
    echo '</h2>';

    echo '<h4>';
    echo __('Are you sure want to Cancel selected Records?');
    echo '</h4>';

     

    $form = Form::create('cancel_receipt_form','');
    $form->setFactory(DatabaseFormFactory::create($pdo));

    $form->addHiddenValue('address', $_SESSION[$guid]['address']);
    $form->addHiddenValue('type','cancel_receipt_request');
    $form->addHiddenValue('trans_id', $transids);

    $row = $form->addRow();
        $row->addLabel('name', __('Remarks : (Mandatory)'));
        $row->addTextArea('remarks')->setRows(4)->required()->setClass('remarks1');

   
        
    $row = $form->addRow();
        $row->addFooter();
        $row->addContent('<a class="btn btn-primary cancel_receipt">Submit</a>'); 
        //$row->addButton('Submit')->setClass('btn btn-primary cancel_receipt');

    echo $form->getOutput();

}