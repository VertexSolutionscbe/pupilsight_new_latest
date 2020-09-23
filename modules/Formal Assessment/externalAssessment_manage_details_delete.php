<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Forms\Prefab\DeleteForm;

if (isActionAccessible($guid, $connection2, '/modules/Formal Assessment/externalAssessment_manage_details_delete.php') == false) {
    //Acess denied
    echo "<div class='alert alert-danger'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    //Proceed!
    $pupilsightExternalAssessmentStudentID = $_GET['pupilsightExternalAssessmentStudentID'] ?? '';
    $pupilsightPersonID = $_GET['pupilsightPersonID'] ?? '';
    $search = $_GET['search'] ?? '';
    $allStudents = $_GET['allStudents'] ?? '';

    if (isset($_GET['return'])) {
        returnProcess($guid, $_GET['return'], null, null);
    }

    //Check if school year specified
    if ($pupilsightExternalAssessmentStudentID == '' or $pupilsightPersonID == '') {
        echo "<div class='alert alert-danger'>";
        echo __('You have not specified one or more required parameters.');
        echo '</div>';
    } else {
        try {
            $data = array('pupilsightExternalAssessmentStudentID' => $pupilsightExternalAssessmentStudentID);
            $sql = 'SELECT * FROM pupilsightExternalAssessmentStudent WHERE pupilsightExternalAssessmentStudentID=:pupilsightExternalAssessmentStudentID';
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
            $row = $result->fetch();

            if ($search != '') {
                echo "<div class='linkTop'>";
                echo "<a href='".$_SESSION[$guid]['absoluteURL']."/index.php?q=/modules/Formal Assessment/externalAssessment_details.php&pupilsightPersonID=$pupilsightPersonID&search=$search&allStudents=$allStudents'>".__('Back').'</a>';
                echo '</div>';
            }

            $form = DeleteForm::createForm($_SESSION[$guid]['absoluteURL'].'/modules/'.$_SESSION[$guid]['module']."/externalAssessment_manage_details_deleteProcess.php?pupilsightExternalAssessmentStudentID=$pupilsightExternalAssessmentStudentID&pupilsightPersonID=$pupilsightPersonID&search=$search&allStudents=$allStudents");
            echo $form->getOutput();
        }
    }
}
