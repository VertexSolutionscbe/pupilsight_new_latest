<?php
/*
Pupilsight, Flexible & Open School System
*/

include '../../pupilsight.php';

$pupilsightStaffApplicationFormID = $_POST['pupilsightStaffApplicationFormID'];
$search = $_GET['search'];
$URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_POST['address'])."/applicationForm_manage_delete.php&pupilsightStaffApplicationFormID=$pupilsightStaffApplicationFormID&search=$search";
$URLDelete = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_POST['address'])."/applicationForm_manage.php&search=$search";

if (isActionAccessible($guid, $connection2, '/modules/Staff/applicationForm_manage_delete.php') == false) {
    $URL .= '&return=error0';
    header("Location: {$URL}");
} else {
    //Proceed!
    if ($pupilsightStaffApplicationFormID == '') {
        $URL .= '&return=error1';
        header("Location: {$URL}");
    } else {
        try {
            $data = array('pupilsightStaffApplicationFormID' => $pupilsightStaffApplicationFormID);
            $sql = 'SELECT * FROM pupilsightStaffApplicationForm WHERE pupilsightStaffApplicationFormID=:pupilsightStaffApplicationFormID';
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
                $data = array('pupilsightStaffApplicationFormID' => $pupilsightStaffApplicationFormID);
                $sql = 'DELETE FROM pupilsightStaffApplicationForm WHERE pupilsightStaffApplicationFormID=:pupilsightStaffApplicationFormID';
                $result = $connection2->prepare($sql);
                $result->execute($data);
            } catch (PDOException $e) {
                $URL .= '&return=error2';
                header("Location: {$URL}");
                exit();
            }

            //Delete files, but don't return error if it fails
            try {
                $data = array('pupilsightStaffApplicationFormID' => $pupilsightStaffApplicationFormID);
                $sql = 'DELETE FROM pupilsightStaffApplicationFormFile WHERE pupilsightStaffApplicationFormID=:pupilsightStaffApplicationFormID';
                $result = $connection2->prepare($sql);
                $result->execute($data);
            } catch (PDOException $e) {
            }

            $URLDelete = $URLDelete.'&return=success0';
            header("Location: {$URLDelete}");
        }
    }
}
