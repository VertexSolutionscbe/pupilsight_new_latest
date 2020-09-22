<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Forms\Form;
use Pupilsight\Forms\DatabaseFormFactory;
$session = $container->get('session');
$inv_id = $session->get('inovice_ids');
$pupilsightPersonID = $session->get('can_stu_id');
if (isActionAccessible($guid, $connection2, '/modules/Finance/invoice_manage_delete.php') == false) {
    //Acess denied
    echo "<div class='error'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    //Proceed!
    if (isset($_GET['return'])) {
        returnProcess($guid, $_GET['return'], null, null);
    }

    //Check if school year specified
    $id = $inv_id;
    if ($id == '') {
        echo "<div class='error'>";
        echo __('You have not specified one or more required parameters.');
        echo '</div>';
    } else {
        try {
            // $data = array('id' => $id);
            // $sql = 'SELECT * FROM fn_fee_invoice WHERE id=:id';
            // $result = $connection2->prepare($sql);
            // $result->execute($data);

            $data = array('id' => $id);
            $sql = 'SELECT * FROM fn_fee_invoice_student_assign WHERE id=:id';
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
            echo '<h2>';
            echo __('Are You Sure Want to Cancel Selected Invoice?');
            echo '</h2>';
            $values = $result->fetch();

            $type = array('1' => 'No', '2' => 'Yes');
            $form = Form::create('delect_invoice_collection_form', $_SESSION[$guid]['absoluteURL'].'/modules/'.$_SESSION[$guid]['module'].'/del_invoice_collection_process.php');
            $form->setFactory(DatabaseFormFactory::create($pdo));
            $form->addHiddenValue('address', $_SESSION[$guid]['address']);
            $form->addHiddenValue('invoice_id', $id);
            $form->addHiddenValue('fn_fee_structure_id', $values['fn_fee_structure_id']);
            $form->addHiddenValue('pupilsightPersonID', $pupilsightPersonID);
            
            $row = $form->addRow();
                $row->addLabel('reason_for_cancel', __('Reason For Cancellation'));
                $row->addTextArea('reason_for_cancel')->setRows(3)->required();

            $row = $form->addRow();
                $row->addLabel('fee_delete', __('Do You also Want to Remove the Associated Fee Structure'));
                $row->addSelect('fee_delete')->fromArray($type)->required();
        
            $row = $form->addRow();
                $row->addFooter();
                 $row->addContent('<a id="cancel_invoice" class=" btn btn-primary">Submit</a>');

            echo $form->getOutput();    
        }
    }
}
