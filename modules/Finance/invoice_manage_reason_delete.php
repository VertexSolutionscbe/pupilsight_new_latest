<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Forms\Form;
use Pupilsight\Forms\DatabaseFormFactory;

if (isActionAccessible($guid, $connection2, '/modules/Finance/invoice_manage_reason_delete.php') == false) {
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
    $id = $_GET['invid'];
    if ($id == '') {
        echo "<div class='error'>";
        echo __('You have not specified one or more required parameters.');
        echo '</div>';
    } else {
        try {
            $data = array('id' => $id);
            $sql = 'SELECT a.reason_for_cancel, b.officialName FROM fn_fee_invoice_student_assign AS a LEFT JOIN pupilsightPerson AS b ON a.cancel_user_id = b.pupilsightPersonID WHERE id=:id';
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
            echo __('Reason for Invoice Cancel');
            echo '</h2>';
            $values = $result->fetch();

            $type = array('1' => 'No', '2' => 'Yes');
            $form = Form::create('program', $_SESSION[$guid]['absoluteURL'].'/modules/'.$_SESSION[$guid]['module'].'/invoice_manage_deleteProcess.php?id='.$id);
            $form->setFactory(DatabaseFormFactory::create($pdo));

            $row = $form->addRow();
            $row->addLabel('reason_for_cancel', __('Cancel By : '.$values['officialName']));
            
            $row = $form->addRow();
                $row->addContent('<b>Reason for Cancel : </b>&nbsp; '.$values['reason_for_cancel']);
                //$row->addTextArea('reason_for_cancel')->setRows(3)->required();

            // $row = $form->addRow();
            //     $row->addLabel('fee_delete', __('Do You also Want to Remove the Associated Fee Structure'));
            //     $row->addSelect('fee_delete')->fromArray($type)->required();
        
           

            echo $form->getOutput();    
        }
    }
}
