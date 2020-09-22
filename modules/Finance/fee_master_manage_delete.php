<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Forms\Prefab\DeleteForm;

if (isActionAccessible($guid, $connection2, '/modules/Finance/fee_master_manage_delete.php') == false) {
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
    $id = $_GET['id'];
    if ($id == '') {
        echo "<div class='error'>";
        echo __('You have not specified one or more required parameters.');
        echo '</div>';
    } else {

         //check payment done or not
            $data1 = array('id' => $id);
            $sql1 = 'SELECT * FROM fn_fees_collection WHERE payment_mode_id=:id OR bank_id =:id';
            $result1 = $connection2->prepare($sql1);
            $result1->execute($data1);
            $check=$result1->rowCount();
         //ends
         if($check==0){
        try {
            $data = array('id' => $id);
            $sql = 'SELECT * FROM fn_masters WHERE id=:id';
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
            $form = DeleteForm::createForm($_SESSION[$guid]['absoluteURL'].'/modules/'.$_SESSION[$guid]['module']."/fee_master_manage_deleteProcess.php?id=$id", true);
            echo $form->getOutput();
        }
    } else {
            echo "<div class='error'>";
            echo __('The specified record cannot be deleted.');
            echo '</div>';
    }
    }
}
