<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Forms\Prefab\DeleteForm;

if (isActionAccessible($guid, $connection2, '/modules/Students/medicalForm_manage_condition_delete.php') == false) {
    //Acess denied
    echo "<div class='alert alert-danger'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    //Proceed!
    $pupilsightPersonMedicalID = $_GET['pupilsightPersonMedicalID'] ?? '';
    $pupilsightPersonMedicalConditionID = $_GET['pupilsightPersonMedicalConditionID'] ?? '';
    $search = $_GET['search'] ?? '';

    if (isset($_GET['return'])) {
        returnProcess($guid, $_GET['return'], null, null);
    }

    //Check if school year specified
    if ($pupilsightPersonMedicalID == '' or $pupilsightPersonMedicalConditionID == '') {
        echo "<div class='alert alert-danger'>";
        echo __('You have not specified one or more required parameters.');
        echo '</div>';
    } else {
        try {
            $data = array('pupilsightPersonMedicalConditionID' => $pupilsightPersonMedicalConditionID);
            $sql = 'SELECT * FROM pupilsightPersonMedicalCondition WHERE pupilsightPersonMedicalConditionID=:pupilsightPersonMedicalConditionID';
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
            if ($search != '') {
                echo "<div class='linkTop'>";
                echo "<a href='".$_SESSION[$guid]['absoluteURL']."/index.php?q=/modules/Students/medicalForm_manage_edit.php&search=$search&pupilsightPersonMedicalID=$pupilsightPersonMedicalID'>".__('Back').'</a>';
                echo '</div>';
            }

            $form = DeleteForm::createForm($_SESSION[$guid]['absoluteURL'].'/modules/'.$_SESSION[$guid]['module']."/medicalForm_manage_condition_deleteProcess.php?pupilsightPersonMedicalID=$pupilsightPersonMedicalID&pupilsightPersonMedicalConditionID=$pupilsightPersonMedicalConditionID&search=$search");
            echo $form->getOutput();
        }
    }
}
