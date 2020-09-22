<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Forms\Prefab\DeleteForm;

//Module includes
require_once __DIR__ . '/moduleFunctions.php';

if (isActionAccessible($guid, $connection2, '/modules/Data Updater/data_finance_manage_delete.php') == false) {
    //Acess denied
    echo "<div class='alert alert-danger'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    //Proceed!
    if (isset($_GET['return'])) {
        returnProcess($guid, $_GET['return'], null, null);
    }

    //Check if school year specified
    $pupilsightFinanceInvoiceeUpdateID = $_GET['pupilsightFinanceInvoiceeUpdateID'];
    if ($pupilsightFinanceInvoiceeUpdateID == '') {
        echo "<div class='alert alert-danger'>";
        echo __('You have not specified one or more required parameters.');
        echo '</div>';
    } else {
        try {
            $data = array('pupilsightFinanceInvoiceeUpdateID' => $pupilsightFinanceInvoiceeUpdateID);
            $sql = 'SELECT * FROM pupilsightFinanceInvoiceeUpdate WHERE pupilsightFinanceInvoiceeUpdateID=:pupilsightFinanceInvoiceeUpdateID';
            $result = $connection2->prepare($sql);
            $result->execute($data);
        } catch (PDOException $e) {
            echo "<div class='alert alert-danger'>".$e->getMessage().'</div>';
        }

        if ($result->rowCount() != 1) {
            echo "<div class='alert alert-danger'>";
            echo __('The selected record does not exist, or you do not have access to it.');
            echo '</div>';
        } else {
            //Let's go!

            $form = DeleteForm::createForm($_SESSION[$guid]['absoluteURL'].'/modules/'.$_SESSION[$guid]['module']."/data_finance_manage_deleteProcess.php?pupilsightFinanceInvoiceeUpdateID=".$pupilsightFinanceInvoiceeUpdateID);
            echo $form->getOutput();
        }
    }
}
