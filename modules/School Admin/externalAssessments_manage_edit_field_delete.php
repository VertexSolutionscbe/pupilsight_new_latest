<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Forms\Prefab\DeleteForm;

if (isActionAccessible($guid, $connection2, '/modules/School Admin/externalAssessments_manage_edit_field_delete.php') == false) {
    //Acess denied
    echo "<div class='alert alert-danger'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    //Check if school year specified
    $pupilsightExternalAssessmentFieldID = $_GET['pupilsightExternalAssessmentFieldID'] ?? '';
    $pupilsightExternalAssessmentID = $_GET['pupilsightExternalAssessmentID'] ?? '';
    if ($pupilsightExternalAssessmentFieldID == '' or $pupilsightExternalAssessmentID == '') {
        echo "<div class='alert alert-danger'>";
        echo __('You have not specified one or more required parameters.');
        echo '</div>';
    } else {
        try {
            $data = array('pupilsightExternalAssessmentFieldID' => $pupilsightExternalAssessmentFieldID);
            $sql = 'SELECT * FROM pupilsightExternalAssessmentField WHERE pupilsightExternalAssessmentFieldID=:pupilsightExternalAssessmentFieldID';
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
            if (isset($_GET['return'])) {
                returnProcess($guid, $_GET['return'], null, null);
            }

            $form = DeleteForm::createForm($_SESSION[$guid]['absoluteURL'].'/modules/'.$_SESSION[$guid]['module']."/externalAssessments_manage_edit_field_deleteProcess.php?pupilsightExternalAssessmentFieldID=$pupilsightExternalAssessmentFieldID&pupilsightExternalAssessmentID=$pupilsightExternalAssessmentID");
            echo $form->getOutput();
        }
    }
}
