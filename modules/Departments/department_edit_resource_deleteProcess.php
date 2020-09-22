<?php
/*
Pupilsight, Flexible & Open School System
*/

include '../../pupilsight.php';

//Module includes
include './moduleFunctions.php';

$pupilsightDepartmentID = $_GET['pupilsightDepartmentID'];
$pupilsightDepartmentResourceID = $_GET['pupilsightDepartmentResourceID'];
$URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_GET['address'])."/department_edit.php&pupilsightDepartmentID=$pupilsightDepartmentID";

if (isActionAccessible($guid, $connection2, '/modules/Departments/department_edit.php') == false) {
    $URL .= '&return=error0';
    header("Location: {$URL}");
} else {
    //Proceed!

    //Check if school year specified
    if ($pupilsightDepartmentID == '' or $pupilsightDepartmentResourceID == '') {
        $URL .= '&return=error1';
        header("Location: {$URL}");
    } else {
        try {
            $data = array('pupilsightDepartmentResourceID' => $pupilsightDepartmentResourceID, 'pupilsightDepartmentID' => $pupilsightDepartmentID);
            $sql = 'SELECT * FROM pupilsightDepartmentResource WHERE pupilsightDepartmentResourceID=:pupilsightDepartmentResourceID AND pupilsightDepartmentID=:pupilsightDepartmentID';
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
            //Get role within learning area
            $role = getRole($_SESSION[$guid]['pupilsightPersonID'], $pupilsightDepartmentID, $connection2);

            if ($role != 'Coordinator' and $role != 'Assistant Coordinator' and $role != 'Teacher (Curriculum)' and $role != 'Director' and $role != 'Manager') {
                $URL .= '&return=error0';
                header("Location: {$URL}");
            } else {
                //Write to database
                try {
                    $data = array('pupilsightDepartmentResourceID' => $pupilsightDepartmentResourceID);
                    $sql = 'DELETE FROM pupilsightDepartmentResource WHERE pupilsightDepartmentResourceID=:pupilsightDepartmentResourceID';
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
}
