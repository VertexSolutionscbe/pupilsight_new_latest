<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Forms\Prefab\DeleteForm;

if (isActionAccessible($guid, $connection2, '/modules/User Admin/family_manage_edit_deleteAdult.php') == false) {
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
    $pupilsightFamilyID = $_GET['pupilsightFamilyID'];
    $pupilsightPersonID = $_GET['pupilsightPersonID'];
    $search = $_GET['search'];
    if ($pupilsightPersonID == '' or $pupilsightFamilyID == '') {
        echo "<div class='alert alert-danger'>";
        echo __('You have not specified one or more required parameters.');
        echo '</div>';
    } else {
        try {
            $data = array('pupilsightFamilyID' => $pupilsightFamilyID, 'pupilsightPersonID' => $pupilsightPersonID);
            $sql = 'SELECT * FROM pupilsightPerson, pupilsightFamily, pupilsightFamilyAdult WHERE pupilsightFamily.pupilsightFamilyID=pupilsightFamilyAdult.pupilsightFamilyID AND pupilsightFamilyAdult.pupilsightPersonID=pupilsightPerson.pupilsightPersonID AND pupilsightFamily.pupilsightFamilyID=:pupilsightFamilyID AND pupilsightFamilyAdult.pupilsightPersonID=:pupilsightPersonID';
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
            $form = DeleteForm::createForm($_SESSION[$guid]['absoluteURL'].'/modules/'.$_SESSION[$guid]['module']."/family_manage_edit_deleteAdultProcess.php?pupilsightFamilyID=$pupilsightFamilyID&pupilsightPersonID=$pupilsightPersonID&search=$search");
            echo $form->getOutput();
        }
    }
}
?>
