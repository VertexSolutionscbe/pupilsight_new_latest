<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Forms\Prefab\DeleteForm;

if (isActionAccessible($guid, $connection2, '/modules/Attendance/manange_sms_template_delete.php') == false) {
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
    $pupilsightTemplateID = $_GET['id'];
    if ($pupilsightTemplateID == '') {
        echo "<div class='alert alert-danger'>";
        echo __('You have not specified one or more required parameters.');
        echo '</div>';
    } else {
        try {
            $data = array('id' => $pupilsightTemplateID);
            $sql = 'SELECT * FROM pupilsightTemplateForAttendance WHERE id=:id';
            $result = $connection2->prepare($sql);
            $result->execute($data);
        } catch (PDOException $e) {
            echo 'not work';
            die(0);
            echo "<div class='alert alert-danger'>".$e->getMessage().'</div>';
        }

        if ($result->rowCount() != 1) {
            echo "<div class='alert alert-danger'>";
            echo __('The specified record cannot be found.');
            echo '</div>';
        } else {
        $check_tmp = 'SELECT * FROM `attn_settings` WHERE  sms_template_id="'.$pupilsightTemplateID.'"';
        $check_t = $connection2->query($check_tmp);
        $res_status = $check_t->fetch();
        if(empty($res_status)){
        $form = DeleteForm::createForm($_SESSION[$guid]['absoluteURL'].'/modules/'.$_SESSION[$guid]['module']."/manange_sms_template_deleteProcess.php?pupilsightTemplateID=$pupilsightTemplateID", true);
        echo $form->getOutput();
        } else {
            echo "<div class='alert alert-danger'>";
            echo __('You can not delete this template because this template already used.');
            echo '</div>';
        }
        }
    }
}
?>
