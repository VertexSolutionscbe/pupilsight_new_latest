<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Forms\Form;
use Pupilsight\Forms\DatabaseFormFactory;

if (isActionAccessible($guid, $connection2, '/modules/Finance/fee_payment_gateway_manage_edit.php') == false) {
    //Acess denied
    echo "<div class='error'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    //Proceed!
    $page->breadcrumbs
    ->add(__('Manage Fee Payment Gateway'), 'fee_payment_gateway_manage.php')
    ->add(__('Edit Fee Payment Gateway'));

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
            $sql = 'SELECT * FROM fn_fee_payment_gateway WHERE id=:id';
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
            echo __('Edit Fee Payment Gateway');
            echo '</h2>';

            $gateway = array('' => 'Select Gateway', 'RAZORPAY' => 'RAZORPAY', 'WORLDLINE' => 'WORLDLINE', 'PAYU' => 'PAYU', 'AIRPAY' => 'AIRPAY');

            $form = Form::create('program', $_SESSION[$guid]['absoluteURL'].'/modules/'.$_SESSION[$guid]['module'].'/fee_payment_gateway_manage_editProcess.php?id='.$id);
            $form->setFactory(DatabaseFormFactory::create($pdo));

            $form->addHiddenValue('address', $_SESSION[$guid]['address']);

            $row = $form->addRow();
                $row->addLabel('gateway_name', __('Name'))->description(__('Must be unique.'));
                $row->addTextField('gateway_name')->setValue($values['gateway_name'])->required();

            $row = $form->addRow();
                $row->addLabel('name', __('Gateway Name'))->description(__('Must be unique.'));
                $row->addSelect('name')->fromArray($gateway)->required()->selected($values['name']);

            $row = $form->addRow();
                $row->addLabel('username', __('UserName'));
                $row->addTextField('username')->setValue($values['username']);
        
            $row = $form->addRow();
                $row->addLabel('password', __('Password'));
                $row->addTextField('password')->setValue($values['password']);

            $row = $form->addRow();
                $row->addLabel('mid', __('Merchant ID'))->description(__('Must be unique.'));
                $row->addTextField('mid')->setValue($values['mid']);
        
            $row = $form->addRow();
                $row->addLabel('key_id', __('Key ID'))->description(__('Must be unique.'));
                $row->addTextField('key_id')->setValue($values['key_id']);
        
            $row = $form->addRow();
                $row->addLabel('key_secret', __('key Secret'))->description(__('Must be unique.'));
                $row->addTextField('key_secret')->setValue($values['key_secret']);    

            $row = $form->addRow();
                $row->addLabel('terms_and_conditions', __('Terms & Conditions'));
                $row->addTextArea('terms_and_conditions')->setValue($values['terms_and_conditions']);

            // $row = $form->addRow();
            //     $row->addLabel('sequenceNumber', __('Sequence Number'))->description(__('Must be unique. Controls chronological ordering.'));
            //     $row->addSequenceNumber('sequenceNumber', 'fn_fee_payment_gateway', $values['sequenceNumber'])
            //         ->required()
            //         ->maxLength(3)
            //         ->setValue($values['sequenceNumber']);
            
           
            $row = $form->addRow();
                $row->addFooter();
                $row->addSubmit();

            echo $form->getOutput();
        }
    }
}
