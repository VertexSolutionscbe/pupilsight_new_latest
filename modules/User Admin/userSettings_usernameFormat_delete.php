<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Forms\Prefab\DeleteForm;

if (isActionAccessible($guid, $connection2, '/modules/User Admin/userSettings.php') == false) {
    //Acess denied
    echo "<div class='alert alert-danger'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    //Proceed! 
    if (isset($_GET['return'])) {
        returnProcess($guid, $_GET['return']);
    }

    $pupilsightUsernameFormatID = isset($_GET['pupilsightUsernameFormatID'])? $_GET['pupilsightUsernameFormatID'] : '';

    if ($pupilsightUsernameFormatID == '') {
        echo "<div class='alert alert-danger'>";
        echo __('You have not specified one or more required parameters.');
        echo '</div>';
    } else {
        $form = DeleteForm::createForm($_SESSION[$guid]['absoluteURL'].'/modules/'.$_SESSION[$guid]['module']."/userSettings_usernameFormat_deleteProcess.php");
        echo $form->getOutput();
    }
}
?>
