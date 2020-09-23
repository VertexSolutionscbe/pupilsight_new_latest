<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Forms\Prefab\DeleteForm;

if (isActionAccessible($guid, $connection2, '/modules/School Admin/attendanceSettings_manage_delete.php') == false) {
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
    $pupilsightAttendanceCodeID = (isset($_GET['pupilsightAttendanceCodeID']))? $_GET['pupilsightAttendanceCodeID'] : NULL;
    if ($pupilsightAttendanceCodeID == '') {
        echo "<div class='alert alert-danger'>";
        echo __('You have not specified one or more required parameters.');
        echo '</div>';
    } else {
        try {
            $data = array('pupilsightAttendanceCodeID' => $pupilsightAttendanceCodeID);
            $sql = 'SELECT pupilsightAttendanceCodeID FROM pupilsightAttendanceCode WHERE pupilsightAttendanceCodeID=:pupilsightAttendanceCodeID';
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
            $form = DeleteForm::createForm($_SESSION[$guid]['absoluteURL'].'/modules/'.$_SESSION[$guid]['module']."/attendanceSettings_manage_deleteProcess.php?pupilsightAttendanceCodeID=$pupilsightAttendanceCodeID", false, false);
            $form->addRow()->addContent(__('These codes should not be changed during an active school year. Removing an attendace code after attendance has been recorded can result in lost information.'));
            $form->addRow()->addConfirmSubmit();

            echo $form->getOutput();
        }
    }
}
