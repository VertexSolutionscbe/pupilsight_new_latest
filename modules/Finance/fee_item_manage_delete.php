<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Forms\Prefab\DeleteForm;

if (isActionAccessible($guid, $connection2, '/modules/Finance/fee_item_manage_delete.php') == false) {
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
        try {
            $data = array('id' => $id);
            $sql = 'SELECT * FROM fn_fee_items WHERE id=:id';
            $result = $connection2->prepare($sql);
            $result->execute($data);
        } catch (PDOException $e) {
            echo "<div class='error'>".$e->getMessage().'</div>';
        }
        try {
            $datai = array('fn_fee_item_id' => $id);
            $sqli = 'SELECT * FROM fn_fee_structure_item WHERE fn_fee_item_id=:fn_fee_item_id';
            $resulti = $connection2->prepare($sqli);
            $resulti->execute($datai);
        } catch (PDOException $e) {
            echo "<div class='error'>".$e->getMessage().'</div>';
        }
        if($resulti->rowCount()>0){
            echo "<div class='error'>";
            echo __('The specified record you cannot delete.');
            echo '</div>';
        } else if ($result->rowCount() != 1) {
            echo "<div class='error'>";
            echo __('The specified record cannot be found.');
            echo '</div>';
        }  else {
            $form = DeleteForm::createForm($_SESSION[$guid]['absoluteURL'].'/modules/'.$_SESSION[$guid]['module']."/fee_item_manage_deleteProcess.php?id=$id", true);
            echo $form->getOutput();
        }
    }
}
