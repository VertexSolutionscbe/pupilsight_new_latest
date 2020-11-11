<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Forms\Prefab\DeleteForm;

if (isActionAccessible($guid, $connection2, '/modules/School Admin/mapping_manage_delete.php') == false) {
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
    $pupilsightMappingID = $_GET['pupilsightMappingID'];
    if ($pupilsightMappingID == '') {
        echo "<div class='alert alert-danger'>";
        echo __('You have not specified one or more required parameters.');
        echo '</div>';
    } else {
        try {
            $data = array('pupilsightMappingID' => $pupilsightMappingID);
            $sql = 'SELECT * FROM assign_class_teacher_section WHERE pupilsightMappingID=:pupilsightMappingID';
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
            $form = DeleteForm::createForm($_SESSION[$guid]['absoluteURL'].'/modules/'.$_SESSION[$guid]['module']."/mapping_manage_delete_class_teacherProcess.php?pupilsightMappingID=$pupilsightMappingID", true);
            echo $form->getOutput();
        }
    }
}
