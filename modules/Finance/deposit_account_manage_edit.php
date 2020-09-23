<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Forms\Form;
use Pupilsight\Forms\DatabaseFormFactory;

if (isActionAccessible($guid, $connection2, '/modules/Finance/deposit_account_manage_edit.php') == false) {
    //Acess denied
    echo "<div class='error'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    //Proceed!
    $page->breadcrumbs
        ->add(__('Manage Deposit Account'), 'deposit_account_manage.php')
        ->add(__('Edit Deposit Account'));

    if (isset($_GET['return'])) {
        returnProcess($guid, $_GET['return'], null, null);
    }

    //Check if school year specified
    $id = $_GET['id'];
    if ($id == '') {
        echo "<div class='error'>";
        echo __('You have not specified one or more required parameters.');
        echo '</div>';
    } else {
        try {
            $data = array('id' => $id);
            $sql = 'SELECT * FROM fn_fees_deposit_account WHERE id=:id';
            $result = $connection2->prepare($sql);
            $result->execute($data);
        } catch (PDOException $e) {
            echo "<div class='error'>".$e->getMessage().'</div>';
        }

        if ($result->rowCount() != 1) {
            echo "<div class='error'>";
            echo __('The specified record cannot be found.');
            echo '</div>';
        } else {
            //Let's go!
            $values = $result->fetch();

            echo '<h2>';
            echo __('Edit Deposit Account');
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
           
            $form = Form::create('program', $_SESSION[$guid]['absoluteURL'].'/modules/'.$_SESSION[$guid]['module'].'/deposit_account_manage_editProcess.php?id='.$id);
            $form->setFactory(DatabaseFormFactory::create($pdo));

            $form->addHiddenValue('address', $_SESSION[$guid]['address']);

            $row = $form->addRow();
                $row->addLabel('fn_fee_item_id', __('Fee Item'))->description(__('Must be unique.'));
                $row->addSelect('fn_fee_item_id')->fromArray($feeItemData)->addClass('txtfield allFeeItemId')->selected($values['fn_fee_item_id'])->required();

            $row = $form->addRow();
                $row->addLabel('ac_name', __('Account Name'))->description(__('Must be unique.'));
                $row->addTextField('ac_name')->required()->setValue($values['ac_name']);

            $row = $form->addRow();
                $row->addLabel('ac_code', __('Account Code'))->description(__('Must be unique.'));
                $row->addTextField('ac_code')->required()->setValue($values['ac_code']);
        
            $row = $form->addRow();
                $row->addLabel('overpayment_account', __('Over Payment Account'));
                $row->addCheckbox('overpayment_account')->setValue('1')->checked($values['overpayment_account']);;   
           
            $row = $form->addRow();
                $row->addFooter();
                $row->addSubmit();

            echo $form->getOutput();
        }
    }
}
