<?php
/*
Pupilsight, Flexible & Open School System
*/

//Pupilsight system-wide includes
include '../../pupilsight.php';

//Module includes
include './moduleFunctions.php';

$pupilsightHouseID = $_GET['pupilsightHouseID'];
$URL = $_SESSION[$guid]['absoluteURL']."/index.php?q=/modules/School Admin/house_manage_edit.php&pupilsightHouseID=$pupilsightHouseID";

if (isActionAccessible($guid, $connection2, '/modules/School Admin/house_manage_edit.php') == false) {
    $URL .= '&return=error0';
    header("Location: {$URL}");
} else {
    //Proceed!
    //Check if planner specified
    if ($pupilsightHouseID == '') {
        $URL .= '&return=error1';
        header("Location: {$URL}");
    } else {
        try {
            $data = array('pupilsightHouseID' => $pupilsightHouseID);
            $sql = 'SELECT * FROM pupilsightHouse WHERE pupilsightHouseID=:pupilsightHouseID';
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
            //UPDATE
            try {
                $data = array('pupilsightHouseID' => $pupilsightHouseID);
                $sql = "UPDATE pupilsightHouse SET logo='' WHERE pupilsightHouseID=:pupilsightHouseID";
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
