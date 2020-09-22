<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Forms\Form;
use Pupilsight\Forms\DatabaseFormFactory;
    $session = $container->get('session');
    $a_stuid = $session->get(['a_stuid']);
    $a_yid = $session->get(['a_yid']);
    $a_invoices_ids = $session->get(['a_invoices_ids']);
if (isActionAccessible($guid, $connection2, '/modules/Finance/apply_discount.php') != false) {
    //Acess denied
    echo "<div class='error'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    //Proceed!
    $page->breadcrumbs
        ->add(__('Apply Discount'), 'fee_transaction_manage.php')
        ->add(__('Apply Discount'));

    $editLink = '';
    if (isset($_GET['editID'])) {
        $editLink = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/Finance/fee_item_manage_edit.php&id='.$_GET['editID'];
    }
    if (isset($_GET['return'])) {
        returnProcess($guid, $_GET['return'], $editLink, null);
    }
    echo '<h2>';
    echo __('Apply Discount');
    echo '</h2>';
    $discount_array=array(" "=>'Choose discount', "2"=>'AT FEE ITEM LEVEL');
    $form = Form::create('apply_discount_form','');
    $form->setFactory(DatabaseFormFactory::create($pdo));

    $form->addHiddenValue('a_stuid',$a_stuid);
    $form->addHiddenValue('a_yid',$a_yid);
    $form->addHiddenValue('a_invoices_ids',$a_invoices_ids);
    $form->addHiddenValue('type','apply_discount_request');
    $row = $form->addRow();
    $row->addLabel('discount_type_change', __('Apply Discount : (Mandatory)'));
    $row->addSelect('discount_type_change')->fromArray($discount_array);
    $row = $form->addRow();
    $row->addContent('<div class="discount_type_change_results" style="width: 124%;"></div>');

    /*$row = $form->addRow();
        $row->addFooter();
        $row->addButton('Submit')->setClass('btn btn-primary cancel_receipt');*/

    echo $form->getOutput();

}
?>