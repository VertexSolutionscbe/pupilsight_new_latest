<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Forms\Prefab\DeleteForm;

if (isActionAccessible($guid, $connection2, '/modules/Staff/staff_manage_edit_facility_delete.php') == false) {
    //Acess denied
    echo "<div class='alert alert-danger'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    //Proceed!
    $pupilsightStaffID = $_GET['pupilsightStaffID'] ?? '';
    $pupilsightSpacePersonID = $_GET['pupilsightSpacePersonID'] ?? '';

    $allStaff = '';
    if (isset($_GET['allStaff'])) {
        $allStaff = $_GET['allStaff'];
    }
    $search = '';
    if (isset($_GET['search'])) {
        $search = $_GET['search'];
    }

    if (isset($_GET['return'])) {
        returnProcess($guid, $_GET['return'], null, null);
    }

    //Check if school year specified
    if ($pupilsightSpacePersonID == '' or $pupilsightStaffID =='') {
        echo "<div class='alert alert-danger'>";
        echo __('You have not specified one or more required parameters.');
        echo '</div>';
    } else {
        try {
            $data = array('pupilsightSpacePersonID' => $pupilsightSpacePersonID);
            $sql = 'SELECT pupilsightSpacePerson.* FROM pupilsightSpacePerson WHERE pupilsightSpacePersonID=:pupilsightSpacePersonID';
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
            $form = DeleteForm::createForm($_SESSION[$guid]['absoluteURL'].'/modules/'.$_SESSION[$guid]['module']."/staff_manage_edit_facility_deleteProcess.php?pupilsightStaffID=$pupilsightStaffID&pupilsightSpacePersonID=$pupilsightSpacePersonID&search=$search&allStaff=$allStaff");
            echo $form->getOutput();
        }
    }
}
