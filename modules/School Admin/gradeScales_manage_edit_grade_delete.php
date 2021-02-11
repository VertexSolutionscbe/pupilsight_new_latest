<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Forms\Prefab\DeleteForm;

if (isActionAccessible($guid, $connection2, '/modules/School Admin/gradeScales_manage_edit_grade_delete.php') == false) {
    //Acess denied
    echo "<div class='alert alert-danger'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    //Check if school year specified
    $pupilsightScaleGradeID = $_GET['pupilsightScaleGradeID'] ?? '';
    $pupilsightScaleID = $_GET['pupilsightScaleID'] ?? '';
    if ($pupilsightScaleGradeID == '' or $pupilsightScaleID == '') {
        echo "<div class='alert alert-danger'>";
        echo __('You have not specified one or more required parameters.');
        echo '</div>';
    } else {
        try {
            $data = array('pupilsightScaleGradeID' => $pupilsightScaleGradeID);
            $sql = 'SELECT * FROM pupilsightScaleGrade WHERE pupilsightScaleGradeID=:pupilsightScaleGradeID';
            $result = $connection2->prepare($sql);
            $result->execute($data);
        } catch (PDOException $e) {
            echo "<div class='alert alert-danger'>" . $e->getMessage() . '</div>';
        }

        if ($result->rowCount() != 1) {
            echo "<div class='alert alert-danger'>";
            echo __('The specified record cannot be found.');
            echo '</div>';
        } else {
            //Let's go!
            $row = $result->fetch();

            if (isset($_GET['return'])) {
                returnProcess($guid, $_GET['return'], null, null);
            }

            $form = DeleteForm::createForm($_SESSION[$guid]['absoluteURL'] . '/modules/' . $_SESSION[$guid]['module'] . "/gradeScales_manage_edit_grade_deleteProcess.php?pupilsightScaleGradeID=$pupilsightScaleGradeID&pupilsightScaleID=$pupilsightScaleID");
            echo $form->getOutput();
        }
    }
}
