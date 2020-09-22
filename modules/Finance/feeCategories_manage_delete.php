<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Forms\Prefab\DeleteForm;

//Module includes
require_once __DIR__ . '/moduleFunctions.php';

if (isActionAccessible($guid, $connection2, '/modules/Finance/feeCategories_manage_delete.php') == false) {
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
    $pupilsightFinanceFeeCategoryID = $_GET['pupilsightFinanceFeeCategoryID'];
    if ($pupilsightFinanceFeeCategoryID == '') {
        echo "<div class='error'>";
        echo __('You have not specified one or more required parameters.');
        echo '</div>';
    } else {
        try {
            $data = array('pupilsightFinanceFeeCategoryID' => $pupilsightFinanceFeeCategoryID);
            $sql = 'SELECT * FROM pupilsightFinanceFeeCategory WHERE pupilsightFinanceFeeCategoryID=:pupilsightFinanceFeeCategoryID';
            $result = $connection2->prepare($sql);
            $result->execute($data);
        } catch (PDOException $e) {
            echo "<div class='error'>".$e->getMessage().'</div>';
        }

        if ($result->rowCount() != 1) {
            echo "<div class='error'>";
            echo __('The selected record does not exist, or you do not have access to it.');
            echo '</div>';
        } else {
            $form = DeleteForm::createForm($_SESSION[$guid]['absoluteURL'].'/modules/'.$_SESSION[$guid]['module']."/feeCategories_manage_deleteProcess.php?pupilsightFinanceFeeCategoryID=$pupilsightFinanceFeeCategoryID");
            echo $form->getOutput();
        }
    }
}
?>
