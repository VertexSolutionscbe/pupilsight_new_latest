<?php
/*
Pupilsight, Flexible & Open School System
*/

include '../../pupilsight.php';

$pupilsightDistrictID = $_GET['pupilsightDistrictID'];
$URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_POST['address']).'/district_manage_delete.php&pupilsightDistrictID='.$pupilsightDistrictID;
$URLDelete = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_POST['address']).'/district_manage.php';

if (isActionAccessible($guid, $connection2, '/modules/User Admin/district_manage_delete.php') == false) {
    $URL .= '&return=error0';
    header("Location: {$URL}");
} else {
    //Proceed!
    //Check if district specified
    if ($pupilsightDistrictID == '') {
        $URL .= '&return=error1';
        header("Location: {$URL}");
    } else {
        try {
            $data = array('pupilsightDistrictID' => $pupilsightDistrictID);
            $sql = 'SELECT * FROM pupilsightDistrict WHERE pupilsightDistrictID=:pupilsightDistrictID';
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
                $data = array('pupilsightDistrictID' => $pupilsightDistrictID);
                $sql = 'DELETE FROM pupilsightDistrict WHERE pupilsightDistrictID=:pupilsightDistrictID';
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
