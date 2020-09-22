<?php
/*
Pupilsight, Flexible & Open School System
*/

include '../../pupilsight.php';

$pupilsightDistrictID = $_GET['pupilsightDistrictID'];
$URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_POST['address']).'/district_manage_edit.php&pupilsightDistrictID='.$pupilsightDistrictID;

if (isActionAccessible($guid, $connection2, '/modules/User Admin/district_manage_edit.php') == false) {
    $URL .= '&return=error0';
    header("Location: {$URL}");
} else {
    //Proceed!
    //Check if districts specified
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
            //Validate Inputs
            $name = $_POST['name'];

            if ($name == '') {
                $URL .= '&return=error3';
                header("Location: {$URL}");
            } else {
                //Check unique inputs for uniquness
                try {
                    $data = array('name' => $name, 'pupilsightDistrictID' => $pupilsightDistrictID);
                    $sql = 'SELECT * FROM pupilsightDistrict WHERE name=:name AND NOT pupilsightDistrictID=:pupilsightDistrictID';
                    $result = $connection2->prepare($sql);
                    $result->execute($data);
                } catch (PDOException $e) {
                    $URL .= '&return=error2';
                    header("Location: {$URL}");
                    exit();
                }

                if ($result->rowCount() > 0) {
                    $URL .= '&return=error3';
                    header("Location: {$URL}");
                } else {
                    //Write to database
                    try {
                        $data = array('name' => $name, 'pupilsightDistrictID' => $pupilsightDistrictID);
                        $sql = 'UPDATE pupilsightDistrict SET name=:name WHERE pupilsightDistrictID=:pupilsightDistrictID';
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
}
