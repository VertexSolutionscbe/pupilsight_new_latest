<?php
/*
Pupilsight, Flexible & Open School System
*/

include '../../pupilsight.php';

$pupilsightDepartmentID = $_GET['pupilsightDepartmentID'];
$pupilsightDepartmentStaffID = $_GET['pupilsightDepartmentStaffID'];
$URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_GET['address'])."/department_manage_edit.php&pupilsightDepartmentID=$pupilsightDepartmentID";

if (isActionAccessible($guid, $connection2, '/modules/Academics/department_manage_edit.php') == false) {
    $URL .= '&return=error0';
    header("Location: {$URL}");
} else {
    //Proceed!

    //Check if school year specified
    if ($pupilsightDepartmentID == '' or $pupilsightDepartmentStaffID == '') {
        $URL .= '&return=error1';
        header("Location: {$URL}");
    } else {
        try {
            $data = array('pupilsightDepartmentStaffID' => $pupilsightDepartmentStaffID, 'pupilsightDepartmentID' => $pupilsightDepartmentID);
            $sql = 'SELECT * FROM pupilsightDepartmentStaff WHERE pupilsightDepartmentStaffID=:pupilsightDepartmentStaffID AND pupilsightDepartmentID=:pupilsightDepartmentID';
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
                $data = array('pupilsightDepartmentStaffID' => $pupilsightDepartmentStaffID);
                $sql = 'DELETE FROM pupilsightDepartmentStaff WHERE pupilsightDepartmentStaffID=:pupilsightDepartmentStaffID';
                $result = $connection2->prepare($sql);
                $result->execute($data);
            } catch (PDOException $e) {
                $URL .= '&return=error2';
                header("Location: {$URL}");
                exit();
            }

            $URL .= '&return=success0';
            header("Location: {$URL}");
        }
    }
}
