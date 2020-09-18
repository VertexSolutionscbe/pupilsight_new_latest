<?php
/*
Pupilsight, Flexible & Open School System
*/

include '../../pupilsight.php';

$pupilsightStaffID = $_GET['pupilsightStaffID'];
$pupilsightSpacePersonID = $_GET['pupilsightSpacePersonID'];
$allStaff = '';
if (isset($_GET['allStaff'])) {
    $allStaff = $_GET['allStaff'];
}
$search = '';
if (isset($_GET['search'])) {
    $search = $_GET['search'];
}
$URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_POST['address'])."/staff_manage_edit_facility_delete.php&pupilsightSpacePersonID=$pupilsightSpacePersonID&search=$search&allStaff=$allStaff";
$URLDelete = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_POST['address'])."/staff_manage_edit.php&pupilsightStaffID=$pupilsightStaffID&search=$search&allStaff=$allStaff";

if (isActionAccessible($guid, $connection2, '/modules/Staff/staff_manage_edit_facility_delete.php') == false) {
    $URL .= '&return=error0';
    header("Location: {$URL}");
} else {
    //Proceed!
    //Check if school year specified
    if ($pupilsightSpacePersonID == '' and $pupilsightStaffID == '') {
        $URL .= '&return=error1';
        header("Location: {$URL}");
    } else {
        try {
            $data = array('pupilsightSpacePersonID' => $pupilsightSpacePersonID);
            $sql = 'SELECT * FROM pupilsightSpacePerson WHERE pupilsightSpacePersonID=:pupilsightSpacePersonID';
            $result = $connection2->prepare($sql);
            $result->execute($data);
        } catch (PDOException $e) {
            $URL .= '&return=error2';
            header("Location: {$URL}");
            exit();
        }

        if ($result->rowCount() != 1) {
            $URL .= '&return=error2';
            header("Location: {$URL}");
        } else {
            //Write to database
            try {
                $data = array('pupilsightSpacePersonID' => $pupilsightSpacePersonID);
                $sql = 'DELETE FROM pupilsightSpacePerson WHERE pupilsightSpacePersonID=:pupilsightSpacePersonID';
                $result = $connection2->prepare($sql);
                $result->execute($data);
            } catch (PDOException $e) {
                $URL .= '&return=error2';
                header("Location: {$URL}");
                exit();
            }

            $URLDelete = $URLDelete.'&return=success0';
            header("Location: {$URLDelete}");
        }
    }
}
