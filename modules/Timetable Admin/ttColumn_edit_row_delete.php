<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Forms\Prefab\DeleteForm;

if (isActionAccessible($guid, $connection2, '/modules/Timetable Admin/ttColumn_edit_row_delete.php') == false) {
    //Acess denied
    echo "<div class='alert alert-danger'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    //Check if school year specified
    $pupilsightTTColumnRowID = $_GET['pupilsightTTColumnRowID'] ?? '';
    $pupilsightTTColumnID = $_GET['pupilsightTTColumnID'] ?? '';
    if ($pupilsightTTColumnRowID == '' or $pupilsightTTColumnID == '') {
        echo "<div class='alert alert-danger'>";
        echo __('You have not specified one or more required parameters.');
        echo '</div>';
    } else {
        try {
            $data = array('pupilsightTTColumnRowID' => $pupilsightTTColumnRowID);
            $sql = 'SELECT * FROM pupilsightTTColumnRow WHERE pupilsightTTColumnRowID=:pupilsightTTColumnRowID';
            $result = $connection2->prepare($sql);
            $result->execute($data);
        } catch (PDOException $e) {
            echo "<div class='alert alert-danger'>".$e->getMessage().'</div>';
        }

        if ($result->rowCount() != 1) {
            echo "<div class='alert alert-danger'>";
            echo __('The specified record cannot be found.');
            echo '</div>';
        } else {
            //Let's go!
            if (isset($_GET['return'])) {
                returnProcess($guid, $_GET['return'], null, null);
            }

            $form = DeleteForm::createForm($_SESSION[$guid]['absoluteURL'].'/modules/'.$_SESSION[$guid]['module']."/ttColumn_edit_row_deleteProcess.php?pupilsightTTColumnRowID=$pupilsightTTColumnRowID&pupilsightTTColumnID=$pupilsightTTColumnID", true);
            echo $form->getOutput();
        }
    }
}
