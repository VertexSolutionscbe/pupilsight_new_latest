<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Forms\Prefab\DeleteForm;

if (isActionAccessible($guid, $connection2, '/modules/Staff/staff_manage_delete.php') == false) {
    //Acess denied
    echo "<div class='alert alert-danger'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    //Proceed!
    $allStaff = $_GET['allStaff'] ?? '';
    $search = $_GET['search'] ?? '' ;

    if (isset($_GET['return'])) {
        returnProcess($guid, $_GET['return'], null, null);
    }

    //Check if school year specified
    $pupilsightStaffID = $_GET['pupilsightStaffID'];
    if ($pupilsightStaffID == '') {
        echo "<div class='alert alert-danger'>";
        echo __('You have not specified one or more required parameters.');
        echo '</div>';
    } else {
        try {
            $data = array('pupilsightStaffID' => $pupilsightStaffID);
            $sql = 'SELECT pupilsightStaff.*, surname, preferredName FROM pupilsightStaff JOIN pupilsightPerson ON (pupilsightStaff.pupilsightPersonID=pupilsightPerson.pupilsightPersonID) WHERE pupilsightStaffID=:pupilsightStaffID';
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
            $form = DeleteForm::createForm($_SESSION[$guid]['absoluteURL'].'/modules/'.$_SESSION[$guid]['module']."/staff_manage_deleteProcess.php?pupilsightStaffID=$pupilsightStaffID&search=$search&allStaff=$allStaff");
            echo $form->getOutput();
        }
    }
}
